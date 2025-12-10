<?php
/**
 * Sistema de Importação e Exportação em Massa de Membros e Líderes
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

require_once HAKLAI_PLUGIN_PATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Haklai_Importer {
    
    /**
     * Mapas de validação
     */
    private $valid_types = ['lider', 'membro'];
    private $valid_skills = ['musica', 'foto', 'filmagem', 'teatro', 'gastronomia', 'tecnologia', 'recepcao', 'som'];
    private $valid_baptism_statuses = ['visitor', 'frequent_visitor', 'member'];
    private $valid_levels = [0, 1, 2, 3, 4, 5];
    
    /**
     * Cache de células e líderes para performance
     */
    private $cells_cache = [];
    private $leaders_cache = [];
    
    /**
     * Logs de importação
     */
    private $import_log = [
        'success' => [],
        'errors' => [],
        'warnings' => []
    ];
    
    /**
     * Colunas padrão para exportação/importação
     */
    private $standard_columns = [
        'tipo',
        'nome',
        'email',
        'telefone',
        'celula',
        'nivel_hierarquico',
        'habilidade',
        'status_batismo',
        'data_batismo',
        'lider_email',
        'encontro_deus',
        'formacoes',
        'status',
        'foto_url',
        'data_criacao'
    ];
    
    // ==========================================
    // MÉTODOS DE IMPORTAÇÃO
    // ==========================================
    
    /**
     * Processa arquivo de importação
     */
    public function process_import($file_path, $file_type = 'csv') {
        $this->import_log = ['success' => [], 'errors' => [], 'warnings' => []];
        
        $data = $this->load_file($file_path, $file_type);
        
        if (is_wp_error($data)) {
            return ['success' => false, 'message' => $data->get_error_message()];
        }
        
        $this->preload_caches();
        $validation = $this->validate_data($data);
        
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => __('Arquivo contém erros de validação', 'haklai-app'),
                'errors' => $validation['errors']
            ];
        }
        
        $this->import_data($data);
        
        return [
            'success' => true,
            'imported' => count($this->import_log['success']),
            'errors' => count($this->import_log['errors']),
            'warnings' => count($this->import_log['warnings']),
            'log' => $this->import_log
        ];
    }
    
    /**
     * Carrega dados do arquivo
     */
    private function load_file($file_path, $file_type) {
        try {
            if ($file_type === 'csv') {
                return $this->load_csv($file_path);
            } elseif ($file_type === 'xlsx' || $file_type === 'xls') {
                return $this->load_excel($file_path);
            }
            
            return new WP_Error('invalid_type', __('Tipo de arquivo não suportado', 'haklai-app'));
            
        } catch (Exception $e) {
            return new WP_Error('file_error', $e->getMessage());
        }
    }
    
    /**
     * Carrega CSV
     */
    private function load_csv($file_path) {
        $data = [];
        $header = [];
        
        if (($handle = fopen($file_path, 'r')) !== false) {
            $row = 0;
            while (($line = fgetcsv($handle, 0, ',')) !== false) {
                if ($row === 0) {
                    $header = array_map('trim', $line);
                } else {
                    if (count($header) === count($line)) {
                        $data[] = array_combine($header, $line);
                    }
                }
                $row++;
            }
            fclose($handle);
        }
        
        return $data;
    }
    
    /**
     * Carrega Excel
     */
    private function load_excel($file_path) {
        $spreadsheet = IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        $header = array_shift($rows);
        $data = [];
        
        foreach ($rows as $row) {
            if (!empty(array_filter($row))) {
                $data[] = array_combine($header, $row);
            }
        }
        
        return $data;
    }
    
    /**
     * Pre-carrega caches
     */
    private function preload_caches() {
        $cells = get_posts([
            'post_type' => 'haklai_cell',
            'post_status' => 'publish',
            'posts_per_page' => -1
        ]);
        
        foreach ($cells as $cell) {
            $this->cells_cache[strtolower($cell->post_title)] = $cell->ID;
        }
        
        $leaders = get_posts([
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => [
                [
                    'key' => '_haklai_role_level',
                    'value' => 1,
                    'compare' => '>='
                ]
            ]
        ]);
        
        foreach ($leaders as $leader) {
            $email = get_post_meta($leader->ID, '_haklai_email', true);
            if ($email) {
                $this->leaders_cache[strtolower($email)] = $leader->ID;
            }
        }
    }
    
    /**
     * Valida dados
     */
    private function validate_data($data) {
        $errors = [];
        
        foreach ($data as $index => $row) {
            $row_num = $index + 2;
            
            if (empty($row['nome'])) {
                $errors[] = sprintf(__('Linha %d: Nome é obrigatório', 'haklai-app'), $row_num);
            }
            
            if (!empty($row['tipo']) && !in_array(strtolower($row['tipo']), $this->valid_types)) {
                $errors[] = sprintf(__('Linha %d: Tipo inválido (%s)', 'haklai-app'), $row_num, $row['tipo']);
            }
            
            if (strtolower($row['tipo'] ?? '') === 'lider' && empty($row['email'])) {
                $errors[] = sprintf(__('Linha %d: Email é obrigatório para líderes', 'haklai-app'), $row_num);
            }
            
            if (!empty($row['email']) && !is_email($row['email'])) {
                $errors[] = sprintf(__('Linha %d: Email inválido (%s)', 'haklai-app'), $row_num, $row['email']);
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Importa dados
     */
    private function import_data($data) {
        foreach ($data as $index => $row) {
            try {
                $member_id = $this->create_member($row);
                
                if (is_wp_error($member_id)) {
                    $this->import_log['errors'][] = [
                        'row' => $index + 2,
                        'name' => $row['nome'],
                        'error' => $member_id->get_error_message()
                    ];
                } else {
                    $this->import_log['success'][] = [
                        'row' => $index + 2,
                        'name' => $row['nome'],
                        'id' => $member_id
                    ];
                }
                
            } catch (Exception $e) {
                $this->import_log['errors'][] = [
                    'row' => $index + 2,
                    'name' => $row['nome'],
                    'error' => $e->getMessage()
                ];
            }
        }
    }
    
    /**
     * Cria membro
     */
    private function create_member($row) {
        $cell_id = 0;
        if (!empty($row['celula'])) {
            $cell_id = $this->get_or_create_cell($row['celula']);
        }
        
        $leader_id = 0;
        if (!empty($row['lider_email'])) {
            $leader_id = $this->get_leader_by_email($row['lider_email']);
        }
        
        $role_level = !empty($row['nivel_hierarquico']) ? (int)$row['nivel_hierarquico'] : 
                      (strtolower($row['tipo'] ?? '') === 'lider' ? 1 : 0);
        
        $meta_input = [
            '_haklai_email' => sanitize_email($row['email'] ?? ''),
            '_haklai_phone' => sanitize_text_field($row['telefone'] ?? ''),
            '_haklai_photo' => esc_url_raw($row['foto_url'] ?? ''),
            '_haklai_skill' => sanitize_text_field($row['habilidade'] ?? ''),
            '_haklai_leader_id' => $leader_id,
            '_haklai_baptism_status' => sanitize_text_field($row['status_batismo'] ?? 'visitor'),
            '_haklai_baptism_date' => sanitize_text_field($row['data_batismo'] ?? ''),
            '_haklai_role_level' => $role_level,
            '_haklai_cell_id' => $cell_id,
            '_haklai_status' => sanitize_text_field($row['status'] ?? 'active'),
            '_haklai_encontro_deus' => strtolower($row['encontro_deus'] ?? 'nao') === 'sim' ? 'sim' : 'nao',
            '_haklai_created_at' => current_time('mysql'),
            '_haklai_updated_at' => current_time('mysql'),
            '_haklai_imported' => 'yes'
        ];
        
        $member_id = wp_insert_post([
            'post_type' => 'haklai_member',
            'post_title' => sanitize_text_field($row['nome']),
            'post_status' => 'publish',
            'meta_input' => $meta_input
        ]);
        
        if (is_wp_error($member_id)) {
            return $member_id;
        }
        
        if (!empty($row['formacoes'])) {
            $formacoes = array_map('trim', explode(',', $row['formacoes']));
            $formacoes = array_map('strtolower', $formacoes);
            update_post_meta($member_id, '_haklai_formacoes', $formacoes);
        }
        
        return $member_id;
    }
    
    /**
     * Obtém ou cria célula
     */
    private function get_or_create_cell($cell_name) {
        $cell_name_lower = strtolower(trim($cell_name));
        
        if (isset($this->cells_cache[$cell_name_lower])) {
            return $this->cells_cache[$cell_name_lower];
        }
        
        $cell_id = wp_insert_post([
            'post_type' => 'haklai_cell',
            'post_title' => sanitize_text_field($cell_name),
            'post_status' => 'publish',
            'meta_input' => [
                '_haklai_status' => 'active'
            ]
        ]);
        
        if (!is_wp_error($cell_id)) {
            $this->cells_cache[$cell_name_lower] = $cell_id;
            $this->import_log['warnings'][] = sprintf(
                __('Célula "%s" criada automaticamente', 'haklai-app'),
                $cell_name
            );
        }
        
        return $cell_id;
    }
    
    /**
     * Obtém líder por email
     */
    private function get_leader_by_email($email) {
        $email_lower = strtolower(trim($email));
        
        if (isset($this->leaders_cache[$email_lower])) {
            return $this->leaders_cache[$email_lower];
        }
        
        $this->import_log['warnings'][] = sprintf(
            __('Líder com email "%s" não encontrado', 'haklai-app'),
            $email
        );
        
        return 0;
    }
    
    // ==========================================
    // MÉTODOS DE EXPORTAÇÃO
    // ==========================================
    
    /**
     * Exporta membros para arquivo
     */
    public function export_members($filters = [], $format = 'csv') {
        try {
            $members = $this->get_members_for_export($filters);
            
            if (empty($members)) {
                return [
                    'success' => false,
                    'message' => __('Nenhum membro encontrado com os filtros aplicados', 'haklai-app')
                ];
            }
            
            $data = $this->prepare_export_data($members);
            $file_path = $this->generate_export_file($data, $format);
            
            if (is_wp_error($file_path)) {
                return [
                    'success' => false,
                    'message' => $file_path->get_error_message()
                ];
            }
            
            return [
                'success' => true,
                'file_path' => $file_path,
                'file_url' => $this->get_export_file_url($file_path),
                'total_records' => count($members),
                'format' => $format
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Busca membros para exportação com filtros
     */
    private function get_members_for_export($filters) {
        $args = [
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
            'meta_query' => []
        ];
        
        if (!empty($filters['cell_id'])) {
            $args['meta_query'][] = [
                'key' => '_haklai_cell_id',
                'value' => intval($filters['cell_id']),
                'compare' => '='
            ];
        }
        
        if (!empty($filters['status'])) {
            $args['meta_query'][] = [
                'key' => '_haklai_status',
                'value' => sanitize_text_field($filters['status']),
                'compare' => '='
            ];
        }
        
        if (isset($filters['role_level']) && $filters['role_level'] !== '') {
            $args['meta_query'][] = [
                'key' => '_haklai_role_level',
                'value' => intval($filters['role_level']),
                'compare' => '='
            ];
        }
        
        if (!empty($filters['baptism_status'])) {
            $args['meta_query'][] = [
                'key' => '_haklai_baptism_status',
                'value' => sanitize_text_field($filters['baptism_status']),
                'compare' => '='
            ];
        }
        
        if (!empty($filters['date_from']) || !empty($filters['date_to'])) {
            $date_query = [];
            
            if (!empty($filters['date_from'])) {
                $date_query['after'] = sanitize_text_field($filters['date_from']);
            }
            
            if (!empty($filters['date_to'])) {
                $date_query['before'] = sanitize_text_field($filters['date_to']);
            }
            
            $args['date_query'] = [$date_query];
        }
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            if ($current_tenant_id) {
                $args['meta_query'][] = [
                    'key' => '_haklai_tenant_id',
                    'value' => $current_tenant_id,
                    'compare' => '='
                ];
            }
        }
        
        return get_posts($args);
    }
    
    /**
     * Prepara dados para exportação
     */
    private function prepare_export_data($members) {
        $data = [];
        $data[] = $this->standard_columns;
        
        foreach ($members as $member) {
            $member_id = $member->ID;
            
            $email = get_post_meta($member_id, '_haklai_email', true);
            $phone = get_post_meta($member_id, '_haklai_phone', true);
            $cell_id = get_post_meta($member_id, '_haklai_cell_id', true);
            $role_level = get_post_meta($member_id, '_haklai_role_level', true);
            $skill = get_post_meta($member_id, '_haklai_skill', true);
            $baptism_status = get_post_meta($member_id, '_haklai_baptism_status', true);
            $baptism_date = get_post_meta($member_id, '_haklai_baptism_date', true);
            $leader_id = get_post_meta($member_id, '_haklai_leader_id', true);
            $encontro_deus = get_post_meta($member_id, '_haklai_encontro_deus', true);
            $formacoes = get_post_meta($member_id, '_haklai_formacoes', true);
            $status = get_post_meta($member_id, '_haklai_status', true);
            $photo = get_post_meta($member_id, '_haklai_photo', true);
            $created_at = get_post_meta($member_id, '_haklai_created_at', true);
            
            $cell_name = '';
            if ($cell_id) {
                $cell = get_post($cell_id);
                $cell_name = $cell ? $cell->post_title : '';
            }
            
            $leader_email = '';
            if ($leader_id) {
                $leader_email = get_post_meta($leader_id, '_haklai_email', true);
            }
            
            $tipo = (intval($role_level) >= 1) ? 'lider' : 'membro';
            
            $formacoes_str = '';
            if (is_array($formacoes) && !empty($formacoes)) {
                $formacoes_str = implode(',', $formacoes);
            }
            
            $data[] = [
                $tipo,
                $member->post_title,
                $email,
                $phone,
                $cell_name,
                $role_level,
                $skill,
                $baptism_status,
                $baptism_date,
                $leader_email,
                $encontro_deus === 'sim' ? 'sim' : 'nao',
                $formacoes_str,
                $status,
                $photo,
                $created_at ? date('Y-m-d H:i:s', strtotime($created_at)) : ''
            ];
        }
        
        return $data;
    }
    
    /**
     * Gera arquivo de exportação
     */
    private function generate_export_file($data, $format) {
        $upload_dir = wp_upload_dir();
        $export_dir = $upload_dir['basedir'] . '/haklai-app/exports/';
        
        if (!file_exists($export_dir)) {
            wp_mkdir_p($export_dir);
        }
        
        $filename = 'haklai-members-export-' . date('Y-m-d-His') . '.' . $format;
        $file_path = $export_dir . $filename;
        
        try {
            if ($format === 'csv') {
                $this->write_csv_file($file_path, $data);
            } elseif ($format === 'xlsx') {
                $this->write_excel_file($file_path, $data);
            } else {
                return new WP_Error('invalid_format', __('Formato de exportação inválido', 'haklai-app'));
            }
            
            return $file_path;
            
        } catch (Exception $e) {
            return new WP_Error('export_error', $e->getMessage());
        }
    }
    
    /**
     * Escreve arquivo CSV
     */
    private function write_csv_file($file_path, $data) {
        $fp = fopen($file_path, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        foreach ($data as $row) {
            fputcsv($fp, $row);
        }
        
        fclose($fp);
    }
    
    /**
     * Escreve arquivo Excel
     */
    private function write_excel_file($file_path, $data) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Membros Haklai');
        $sheet->fromArray($data, null, 'A1');
        $sheet->getStyle('A1:' . $sheet->getHighestColumn() . '1')->getFont()->setBold(true);
        
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new Xlsx($spreadsheet);
        $writer->save($file_path);
    }
    
    /**
     * Obtém URL do arquivo de exportação
     */
    private function get_export_file_url($file_path) {
        $upload_dir = wp_upload_dir();
        $file_url = str_replace($upload_dir['basedir'], $upload_dir['baseurl'], $file_path);
        return $file_url;
    }
    
    /**
     * Gera template CSV de exemplo
     */
    public static function generate_template() {
        $upload_dir = wp_upload_dir();
        $template_path = $upload_dir['basedir'] . '/haklai-app/haklai-import-template.csv';
        
        wp_mkdir_p(dirname($template_path));
        
        $header = [
            'tipo',
            'nome',
            'email',
            'telefone',
            'celula',
            'nivel_hierarquico',
            'habilidade',
            'status_batismo',
            'data_batismo',
            'lider_email',
            'encontro_deus',
            'formacoes',
            'status',
            'foto_url',
            'data_criacao'
        ];
        
        $examples = [
            [
                'lider',
                'João Silva',
                'joao.silva@email.com',
                '+351912345678',
                'Célula Alpha',
                '1',
                'musica',
                'member',
                '2024-01-15',
                '',
                'sim',
                'ctl,cme',
                'active',
                'https://example.com/photo.jpg',
                ''
            ],
            [
                'membro',
                'Maria Santos',
                'maria.santos@email.com',
                '+351923456789',
                'Célula Alpha',
                '0',
                'recepcao',
                'frequent_visitor',
                '',
                'joao.silva@email.com',
                'nao',
                '',
                'active',
                '',
                ''
            ]
        ];
        
        $fp = fopen($template_path, 'w');
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($fp, $header);
        foreach ($examples as $example) {
            fputcsv($fp, $example);
        }
        fclose($fp);
        
        return $template_path;
    }
}

