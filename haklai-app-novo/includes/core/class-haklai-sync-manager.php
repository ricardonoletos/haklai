<?php
/**
 * Classe responsável pelo gerenciamento de sincronização CPT ↔ Tabelas MySQL
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Classe Haklai_Sync_Manager
 * 
 * Responsabilidades:
 * - Gerenciar mapeamento bidirecional CPT ↔ Tabela MySQL
 * - Converter IDs de relacionamento automaticamente
 * - Validar e reparar integridade referencial
 * - Garantir sincronização automática e confiável
 */
class Haklai_Sync_Manager {
    
    /**
     * Meta key usada para armazenar ID da tabela MySQL no CPT
     */
    const TABLE_ID_META_KEY = '_haklai_table_id';
    
    /**
     * Mapeamento de tipos de CPT para nomes de tabelas
     */
    private static $cpt_table_map = array(
        'haklai_member' => 'members',
        'haklai_cell' => 'cells',
        'haklai_network' => 'networks',
        'haklai_meeting' => 'meetings',
        'haklai_attendance' => 'attendance'
    );
    
    /**
     * Converte ID do CPT para ID da tabela MySQL
     * 
     * Fluxo:
     * 1. Busca meta key _haklai_table_id (O(1) - rápido)
     * 2. Se não existe, busca por nome e cria mapeamento (fallback)
     * 3. Retorna ID da tabela ou false
     * 
     * @param int $cpt_id ID do CPT (post ID)
     * @param string $cpt_type Tipo do CPT (haklai_member, haklai_cell, etc)
     * @return int|false ID da tabela MySQL ou false se não encontrado
     */
    public static function get_table_id_from_cpt($cpt_id, $cpt_type) {
        // Validação básica
        if (!$cpt_id || !$cpt_type) {
            return false;
        }
        
        // 1. Busca meta key direta (método rápido e confiável)
        $table_id = get_post_meta($cpt_id, self::TABLE_ID_META_KEY, true);
        
        if ($table_id) {
            // Valida que o registro ainda existe na tabela
            if (self::validate_table_id($table_id, $cpt_type)) {
                return intval($table_id);
            } else {
                // Mapeamento inválido, remove e tenta recriar
                delete_post_meta($cpt_id, self::TABLE_ID_META_KEY);
            }
        }
        
        // 2. Fallback: busca por nome e cria mapeamento
        return self::find_or_create_mapping($cpt_id, $cpt_type);
    }
    
    /**
     * Busca por nome e cria mapeamento se necessário
     * 
     * @param int $cpt_id ID do CPT
     * @param string $cpt_type Tipo do CPT
     * @return int|false ID da tabela ou false
     */
    private static function find_or_create_mapping($cpt_id, $cpt_type) {
        if (!isset(self::$cpt_table_map[$cpt_type])) {
            return false;
        }
        
        $post = get_post($cpt_id);
        if (!$post || $post->post_type !== $cpt_type) {
            return false;
        }
        
        $table_name = Haklai_DB::get_table_name(self::$cpt_table_map[$cpt_type]);
        $cpt_name = $post->post_title;
        
        if (empty($cpt_name)) {
            return false;
        }
        
        // Busca na tabela MySQL por nome
        global $wpdb;
        $table_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
            $cpt_name
        ));
        
        if ($table_id) {
            // Encontrou! Cria mapeamento para acelerar próximas buscas
            update_post_meta($cpt_id, self::TABLE_ID_META_KEY, intval($table_id));
            return intval($table_id);
        }
        
        // Não encontrou - pode ser que precise sincronizar primeiro
        // Retorna false para que a sincronização crie o registro
        return false;
    }
    
    /**
     * Valida se um ID de tabela ainda existe e está correto
     * 
     * @param int $table_id ID da tabela MySQL
     * @param string $cpt_type Tipo do CPT
     * @return bool True se válido
     */
    private static function validate_table_id($table_id, $cpt_type) {
        if (!isset(self::$cpt_table_map[$cpt_type])) {
            return false;
        }
        
        $table_name = Haklai_DB::get_table_name(self::$cpt_table_map[$cpt_type]);
        
        global $wpdb;
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE id = %d LIMIT 1",
            $table_id
        ));
        
        return !empty($exists);
    }
    
    /**
     * Define mapeamento CPT ↔ Tabela
     * 
     * @param int $cpt_id ID do CPT
     * @param int $table_id ID da tabela MySQL
     * @return bool True se salvo com sucesso
     */
    public static function set_table_id_mapping($cpt_id, $table_id) {
        if (!$cpt_id || !$table_id) {
            return false;
        }
        
        return update_post_meta($cpt_id, self::TABLE_ID_META_KEY, intval($table_id)) !== false;
    }
    
    /**
     * Remove mapeamento (quando CPT ou registro da tabela é deletado)
     * 
     * @param int $cpt_id ID do CPT
     * @return bool True se removido
     */
    public static function remove_table_id_mapping($cpt_id) {
        return delete_post_meta($cpt_id, self::TABLE_ID_META_KEY);
    }
    
    /**
     * Converte ID de CPT para ID de tabela (com criação automática se necessário)
     * 
     * Esta é a função principal para conversão de relacionamentos.
     * Se o CPT não existe na tabela, tenta sincronizar primeiro.
     * 
     * @param int $cpt_id ID do CPT
     * @param string $cpt_type Tipo do CPT
     * @param bool $create_if_missing Se true, sincroniza se não existir
     * @return int|false ID da tabela ou false
     */
    public static function convert_cpt_id_to_table_id($cpt_id, $cpt_type, $create_if_missing = false) {
        // Tenta buscar mapeamento existente
        $table_id = self::get_table_id_from_cpt($cpt_id, $cpt_type);
        
        if ($table_id) {
            return $table_id;
        }
        
        // Se não encontrou e create_if_missing = true, tenta sincronizar
        if ($create_if_missing) {
            $table_id = self::sync_cpt_to_table($cpt_id, $cpt_type);
            return $table_id ? intval($table_id) : false;
        }
        
        return false;
    }
    
    /**
     * Sincroniza CPT para tabela e retorna ID
     * 
     * @param int $cpt_id ID do CPT
     * @param string $cpt_type Tipo do CPT
     * @return int|false ID da tabela ou false
     */
    private static function sync_cpt_to_table($cpt_id, $cpt_type) {
        switch ($cpt_type) {
            case 'haklai_member':
                return Haklai_DB::sync_member_from_cpt($cpt_id);
                
            case 'haklai_cell':
                return Haklai_DB::sync_cell_from_cpt($cpt_id);
                
            case 'haklai_network':
                return Haklai_DB::sync_network_from_cpt($cpt_id);
                
            case 'haklai_meeting':
                return Haklai_DB::sync_meeting_from_cpt($cpt_id);
                
            default:
                return false;
        }
    }
    
    /**
     * Busca ID do CPT a partir do ID da tabela
     * 
     * @param int $table_id ID da tabela MySQL
     * @param string $cpt_type Tipo do CPT
     * @return int|false ID do CPT ou false
     */
    public static function get_cpt_id_from_table($table_id, $cpt_type) {
        if (!$table_id || !$cpt_type) {
            return false;
        }
        
        // Busca CPT que tenha esse table_id como meta
        $cpts = get_posts(array(
            'post_type' => $cpt_type,
            'post_status' => 'any',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => self::TABLE_ID_META_KEY,
                    'value' => $table_id,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($cpts)) {
            return $cpts[0]->ID;
        }
        
        return false;
    }
    
    /**
     * Valida integridade referencial entre CPTs e tabelas
     * 
     * @return array Relatório de validação
     */
    public static function validate_integrity() {
        $report = array(
            'total_checked' => 0,
            'errors' => array(),
            'fixed' => 0
        );
        
        // Valida cada tipo de CPT
        foreach (self::$cpt_table_map as $cpt_type => $table_name) {
            if ($cpt_type === 'haklai_attendance') {
                continue; // Attendance é tratado diferente
            }
            
            $posts = get_posts(array(
                'post_type' => $cpt_type,
                'post_status' => 'any',
                'posts_per_page' => -1
            ));
            
            foreach ($posts as $post) {
                $report['total_checked']++;
                
                $table_id = get_post_meta($post->ID, self::TABLE_ID_META_KEY, true);
                
                if ($table_id) {
                    // Valida se o registro na tabela ainda existe
                    if (!self::validate_table_id($table_id, $cpt_type)) {
                        $report['errors'][] = array(
                            'type' => 'invalid_mapping',
                            'cpt_type' => $cpt_type,
                            'cpt_id' => $post->ID,
                            'table_id' => $table_id,
                            'message' => sprintf(
                                __('Mapeamento inválido: CPT %s (ID: %d) → Tabela ID: %d não existe', 'haklai-app'),
                                $cpt_type,
                                $post->ID,
                                $table_id
                            )
                        );
                        
                        // Tenta reparar
                        if (self::find_or_create_mapping($post->ID, $cpt_type)) {
                            $report['fixed']++;
                        }
                    }
                } else {
                    // Sem mapeamento - pode ser normal (ainda não sincronizado)
                    // Mas vamos tentar criar se o post estiver publicado
                    if ($post->post_status === 'publish') {
                        $new_table_id = self::find_or_create_mapping($post->ID, $cpt_type);
                        if ($new_table_id) {
                            $report['fixed']++;
                        }
                    }
                }
            }
        }
        
        return $report;
    }
    
    /**
     * Repara mapeamentos perdidos ou inválidos
     * 
     * @return array Resultado do reparo
     */
    public static function repair_mappings() {
        $result = array(
            'repaired' => 0,
            'errors' => 0
        );
        
        foreach (self::$cpt_table_map as $cpt_type => $table_name) {
            if ($cpt_type === 'haklai_attendance') {
                continue;
            }
            
            $posts = get_posts(array(
                'post_type' => $cpt_type,
                'post_status' => 'publish',
                'posts_per_page' => -1
            ));
            
            foreach ($posts as $post) {
                $table_id = get_post_meta($post->ID, self::TABLE_ID_META_KEY, true);
                
                // Se não tem mapeamento ou é inválido, tenta criar/reparar
                if (!$table_id || !self::validate_table_id($table_id, $cpt_type)) {
                    $new_table_id = self::sync_cpt_to_table($post->ID, $cpt_type);
                    
                    if ($new_table_id) {
                        $result['repaired']++;
                    } else {
                        $result['errors']++;
                    }
                }
            }
        }
        
        return $result;
    }
    
    /**
     * Obtém status de sincronização do sistema
     * 
     * @return array Status detalhado
     */
    public static function get_sync_status() {
        $status = array(
            'total_cpts' => 0,
            'mapped_cpts' => 0,
            'unmapped_cpts' => 0,
            'invalid_mappings' => 0,
            'by_type' => array()
        );
        
        foreach (self::$cpt_table_map as $cpt_type => $table_name) {
            if ($cpt_type === 'haklai_attendance') {
                continue;
            }
            
            $posts = get_posts(array(
                'post_type' => $cpt_type,
                'post_status' => 'any',
                'posts_per_page' => -1
            ));
            
            $type_status = array(
                'total' => count($posts),
                'mapped' => 0,
                'unmapped' => 0,
                'invalid' => 0
            );
            
            foreach ($posts as $post) {
                $status['total_cpts']++;
                $type_status['total']++;
                
                $table_id = get_post_meta($post->ID, self::TABLE_ID_META_KEY, true);
                
                if ($table_id) {
                    if (self::validate_table_id($table_id, $cpt_type)) {
                        $status['mapped_cpts']++;
                        $type_status['mapped']++;
                    } else {
                        $status['invalid_mappings']++;
                        $type_status['invalid']++;
                    }
                } else {
                    $status['unmapped_cpts']++;
                    $type_status['unmapped']++;
                }
            }
            
            $status['by_type'][$cpt_type] = $type_status;
        }
        
        return $status;
    }
}

