<?php
/**
 * Filtro de Queries para Isolamento de Tenants
 * Garante que todas as queries do WordPress Admin filtrem automaticamente por tenant_id
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
 * Classe Haklai_Tenant_Query_Filter
 * Filtra automaticamente queries do WordPress Admin para garantir isolamento de tenants
 */
class Haklai_Tenant_Query_Filter {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Filtra queries do admin para CPTs Haklai
        add_action('pre_get_posts', [$this, 'filter_admin_queries']);
        
        // Protege acesso direto a posts de outros tenants
        add_action('load-post.php', [$this, 'protect_post_edit']);
        add_action('load-post-new.php', [$this, 'protect_post_new']);
        
        // Filtra contadores de posts no admin
        add_filter('wp_count_posts', [$this, 'filter_post_counts'], 10, 3);
    }
    
    /**
     * Filtra queries do admin para CPTs Haklai
     * 
     * @param WP_Query $query Query do WordPress
     */
    public function filter_admin_queries($query) {
        // Só no admin e para queries principais
        if (!is_admin() || !$query->is_main_query()) {
            return;
        }
        
        // Lista de CPTs Haklai que precisam filtro
        $haklai_post_types = ['haklai_member', 'haklai_cell', 'haklai_network', 'haklai_meeting', 'haklai_report'];
        
        $post_type = $query->get('post_type');
        
        // Se for um CPT Haklai, adiciona filtro de tenant
        if (in_array($post_type, $haklai_post_types)) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            if ($current_tenant_id) {
                $meta_query = $query->get('meta_query');
                if (!is_array($meta_query)) {
                    $meta_query = [];
                }
                
                // Adiciona filtro de tenant (evita duplicação)
                $has_tenant_filter = false;
                foreach ($meta_query as $mq) {
                    if (isset($mq['key']) && $mq['key'] === '_haklai_tenant_id') {
                        $has_tenant_filter = true;
                        break;
                    }
                }
                
                if (!$has_tenant_filter) {
                    $meta_query[] = [
                        'key' => '_haklai_tenant_id',
                        'value' => $current_tenant_id,
                        'compare' => '='
                    ];
                    
                    $query->set('meta_query', $meta_query);
                }
            }
        }
    }
    
    /**
     * Protege edição de posts de outros tenants
     */
    public function protect_post_edit() {
        // Verifica se está editando um CPT Haklai
        $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
        if (!$post_id) {
            return;
        }
        
        $post = get_post($post_id);
        if (!$post) {
            return;
        }
        
        $haklai_post_types = ['haklai_member', 'haklai_cell', 'haklai_network', 'haklai_meeting', 'haklai_report'];
        
        if (in_array($post->post_type, $haklai_post_types)) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            $post_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
            
            if ($current_tenant_id && $post_tenant_id && $post_tenant_id != $current_tenant_id) {
                wp_die(
                    __('Acesso negado. Este item pertence a outro tenant.', 'haklai-app'),
                    __('Acesso Negado', 'haklai-app'),
                    ['response' => 403]
                );
            }
        }
    }
    
    /**
     * Protege criação de novos posts (garante tenant_id)
     * Nota: A proteção real é feita em class-haklai-post-types.php via ensure_tenant_id_on_insert
     */
    public function protect_post_new() {
        // Validação adicional pode ser adicionada aqui se necessário
        // Por enquanto, a proteção é feita no hook wp_insert_post
    }
    
    /**
     * Filtra contadores de posts no admin para mostrar apenas do tenant atual
     * 
     * @param object $counts Contadores de posts
     * @param string $type Tipo de post
     * @param string $perm Permissão
     * @return object Contadores filtrados
     */
    public function filter_post_counts($counts, $type, $perm) {
        $haklai_post_types = ['haklai_member', 'haklai_cell', 'haklai_network', 'haklai_meeting', 'haklai_report'];
        
        if (!in_array($type, $haklai_post_types)) {
            return $counts;
        }
        
        $tenant_manager = Haklai_Tenant_Manager::get_instance();
        $current_tenant_id = $tenant_manager->get_current_tenant_id();
        
        // PRESERVA o objeto original (já tem todas as propriedades que o WordPress espera)
        if (!is_object($counts)) {
            $counts = (object) [];
        }
        
        if (!$current_tenant_id) {
            // Zera apenas os status principais, mantendo a estrutura original
            $counts->publish = 0;
            $counts->draft = 0;
            $counts->trash = 0;
            $counts->private = 0;
            $counts->pending = 0;
            $counts->future = 0;
            return $counts; // Retorna objeto original com todas as propriedades
        }
        
        // Conta posts do tenant atual
        $args = [
            'post_type' => $type,
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_haklai_tenant_id',
                    'value' => $current_tenant_id,
                    'compare' => '='
                ]
            ]
        ];
        
        // Atualiza apenas os valores dos status principais
        // O objeto original já tem todas as outras propriedades (auto-draft, inherit, etc.)
        $counts->publish = count(get_posts(array_merge($args, ['post_status' => 'publish'])));
        $counts->draft = count(get_posts(array_merge($args, ['post_status' => 'draft'])));
        $counts->trash = count(get_posts(array_merge($args, ['post_status' => 'trash'])));
        $counts->private = count(get_posts(array_merge($args, ['post_status' => 'private'])));
        $counts->pending = count(get_posts(array_merge($args, ['post_status' => 'pending'])));
        $counts->future = count(get_posts(array_merge($args, ['post_status' => 'future'])));
        
        return $counts; // Retorna objeto original modificado (com todas as propriedades preservadas)
    }
}

