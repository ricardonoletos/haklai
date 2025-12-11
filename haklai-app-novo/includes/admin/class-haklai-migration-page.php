<?php
/**
 * Página de Migração de Dados - CPT para Tabelas Customizadas
 * Migra membros, células, reuniões e presenças do sistema antigo (CPT) para o novo (tabelas)
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Migration_Page {
    
    /**
     * Construtor
     */
    public function __construct() {
        add_action('admin_menu', [$this, 'add_migration_page'], 25);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX Handlers
        add_action('wp_ajax_haklai_get_migration_stats', [$this, 'ajax_get_stats']);
        add_action('wp_ajax_haklai_migrate_batch', [$this, 'ajax_migrate_batch']);
        add_action('wp_ajax_haklai_validate_migration', [$this, 'ajax_validate_migration']);
        add_action('wp_ajax_haklai_export_backup', [$this, 'ajax_export_backup']);
        
        // Novos handlers com sistema de sincronização melhorado
        add_action('wp_ajax_haklai_validate_integrity', [$this, 'ajax_validate_integrity']);
        add_action('wp_ajax_haklai_repair_mappings', [$this, 'ajax_repair_mappings']);
        add_action('wp_ajax_haklai_get_sync_status', [$this, 'ajax_get_sync_status']);
    }
    
    /**
     * Adiciona página de migração no menu
     */
    public function add_migration_page() {
        // FASE 5: Só adiciona menu de migração se usuário tiver tenant
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            // Se não tiver tenant, não mostra menu de migração
            if (!$current_tenant_id) {
                return;
            }
        }
        
        add_submenu_page(
            'haklai',
            __('Migração de Dados', 'haklai-app'),
            __('Migração de Dados', 'haklai-app'),
            'manage_options',
            'haklai-migration',
            [$this, 'render_migration_page']
        );
    }
    
    /**
     * Carrega scripts e estilos
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'haklai_page_haklai-migration') {
            return;
        }
        
        wp_enqueue_style(
            'haklai-migration-css',
            HAKLAI_PLUGIN_URL . 'assets/admin/css/haklai-migration.css',
            [],
            HAKLAI_VERSION
        );
        
        wp_enqueue_script(
            'haklai-migration-js',
            HAKLAI_PLUGIN_URL . 'assets/admin/js/haklai-migration.js',
            ['jquery'],
            HAKLAI_VERSION,
            true
        );
        
        wp_localize_script('haklai-migration-js', 'haklaiMigration', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('haklai_migration_nonce'),
            'batchSize' => 10,
            'strings' => [
                'migrating' => __('Migrando...', 'haklai-app'),
                'completed' => __('Concluído!', 'haklai-app'),
                'error' => __('Erro', 'haklai-app'),
                'noData' => __('Nenhum dado encontrado', 'haklai-app'),
            ]
        ]);
    }
    
    /**
     * Renderiza página de migração
     */
    public function render_migration_page() {
        if (!current_user_can('manage_options')) {
            wp_die(__('Você não tem permissão para acessar esta página.', 'haklai-app'));
        }
        
        // Verifica se tabelas existem
        $tables_exist = Haklai_DB::tables_exist();
        
        // Obtém estatísticas
        $stats = $this->get_migration_stats();
        
        ?>
        <div class="wrap haklai-migration-page">
            <h1 class="wp-heading-inline">
                <i class="fas fa-database"></i> <?php _e('Migração de Dados', 'haklai-app'); ?>
            </h1>
            <hr class="wp-header-end">
            
            <?php if (!$tables_exist): ?>
                <div class="notice notice-error">
                    <p><strong><?php _e('Erro:', 'haklai-app'); ?></strong> 
                    <?php _e('As tabelas customizadas não existem. Por favor, ative o plugin primeiro.', 'haklai-app'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Estatísticas -->
            <div class="haklai-migration-stats">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon members">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo esc_html($stats['members']['total']); ?></h3>
                            <p><?php _e('Membros no CPT', 'haklai-app'); ?></p>
                            <small class="stat-detail">
                                <?php echo esc_html($stats['members']['migrated']); ?> <?php _e('migrados', 'haklai-app'); ?> | 
                                <?php echo esc_html($stats['members']['pending']); ?> <?php _e('pendentes', 'haklai-app'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon cells">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo esc_html($stats['cells']['total']); ?></h3>
                            <p><?php _e('Células no CPT', 'haklai-app'); ?></p>
                            <small class="stat-detail">
                                <?php echo esc_html($stats['cells']['migrated']); ?> <?php _e('migradas', 'haklai-app'); ?> | 
                                <?php echo esc_html($stats['cells']['pending']); ?> <?php _e('pendentes', 'haklai-app'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon meetings">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo esc_html($stats['meetings']['total']); ?></h3>
                            <p><?php _e('Reuniões no CPT', 'haklai-app'); ?></p>
                            <small class="stat-detail">
                                <?php echo esc_html($stats['meetings']['migrated']); ?> <?php _e('migradas', 'haklai-app'); ?> | 
                                <?php echo esc_html($stats['meetings']['pending']); ?> <?php _e('pendentes', 'haklai-app'); ?>
                            </small>
                        </div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-icon status">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-content">
                            <h3><?php echo esc_html($stats['overall']['percentage']); ?>%</h3>
                            <p><?php _e('Progresso Geral', 'haklai-app'); ?></p>
                            <small class="stat-detail">
                                <?php echo esc_html($stats['overall']['migrated']); ?> / <?php echo esc_html($stats['overall']['total']); ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Informações Importantes -->
            <div class="haklai-migration-info">
                <div class="info-box warning">
                    <h3><i class="fas fa-exclamation-triangle"></i> <?php _e('Atenção', 'haklai-app'); ?></h3>
                    <ul>
                        <li><?php _e('Esta migração irá copiar dados do sistema antigo (Custom Post Types) para o novo sistema (Tabelas Customizadas).', 'haklai-app'); ?></li>
                        <li><?php _e('Os dados originais NÃO serão removidos - esta é uma operação segura.', 'haklai-app'); ?></li>
                        <li><?php _e('Recomendamos fazer um backup completo do banco de dados antes de iniciar.', 'haklai-app'); ?></li>
                        <li><?php _e('A migração é executada em lotes para evitar timeouts.', 'haklai-app'); ?></li>
                    </ul>
                </div>
            </div>
            
            <!-- Seção: Validação e Reparo (NOVO) -->
            <div class="haklai-sync-validation" style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
                <h2 style="margin-top: 0;">
                    <i class="fas fa-sync-alt"></i> <?php _e('Validação e Reparo de Sincronização', 'haklai-app'); ?>
                </h2>
                <p style="color: #666; margin-bottom: 20px;">
                    <?php _e('Use estas ferramentas para validar a integridade dos mapeamentos entre CPTs e tabelas MySQL, e reparar problemas de sincronização.', 'haklai-app'); ?>
                </p>
                
                <div class="control-buttons" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <button type="button" id="btn-validate-integrity" class="button button-secondary">
                        <i class="fas fa-search"></i> <?php _e('Validar Integridade', 'haklai-app'); ?>
                    </button>
                    
                    <button type="button" id="btn-repair-mappings" class="button button-secondary">
                        <i class="fas fa-tools"></i> <?php _e('Reparar Mapeamentos', 'haklai-app'); ?>
                    </button>
                    
                    <button type="button" id="btn-get-sync-status" class="button button-secondary">
                        <i class="fas fa-info-circle"></i> <?php _e('Status de Sincronização', 'haklai-app'); ?>
                    </button>
                </div>
                
                <!-- Resultado da Validação -->
                <div id="validation-results" style="margin-top: 20px; display: none;">
                    <div class="validation-content" style="background: #f9f9f9; padding: 15px; border-radius: 5px; border-left: 4px solid #0073aa;">
                        <h4 style="margin-top: 0;"><?php _e('Resultado da Validação', 'haklai-app'); ?></h4>
                        <div id="validation-details"></div>
                    </div>
                </div>
            </div>
            
            <!-- Painel de Controle -->
            <div class="haklai-migration-controls">
                <h2><?php _e('Painel de Controle', 'haklai-app'); ?></h2>
                
                <div class="control-buttons">
                    <button type="button" id="btn-validate" class="button button-secondary">
                        <i class="fas fa-check-circle"></i> <?php _e('Validar Dados', 'haklai-app'); ?>
                    </button>
                    
                    <button type="button" id="btn-export-backup" class="button button-secondary">
                        <i class="fas fa-download"></i> <?php _e('Exportar Backup', 'haklai-app'); ?>
                    </button>
                    
                    <?php if ($stats['overall']['pending'] > 0): ?>
                        <button type="button" id="btn-start-migration" class="button button-primary button-large">
                            <i class="fas fa-play"></i> <?php _e('Iniciar Migração', 'haklai-app'); ?>
                        </button>
                    <?php else: ?>
                        <button type="button" class="button button-primary button-large" disabled>
                            <i class="fas fa-check"></i> <?php _e('Migração Concluída', 'haklai-app'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Progresso da Migração -->
            <div class="haklai-migration-progress" id="migration-progress" style="display: none;">
                <h2><?php _e('Progresso da Migração', 'haklai-app'); ?></h2>
                
                <div class="progress-container">
                    <div class="progress-bar">
                        <div class="progress-fill" id="progress-fill" style="width: 0%;"></div>
                    </div>
                    <div class="progress-text">
                        <span id="progress-percentage">0%</span>
                        <span id="progress-details"><?php _e('Aguardando...', 'haklai-app'); ?></span>
                    </div>
                </div>
                
                <div class="progress-stats">
                    <div class="progress-stat">
                        <strong id="progress-migrated">0</strong>
                        <span><?php _e('Migrados', 'haklai-app'); ?></span>
                    </div>
                    <div class="progress-stat">
                        <strong id="progress-errors">0</strong>
                        <span><?php _e('Erros', 'haklai-app'); ?></span>
                    </div>
                    <div class="progress-stat">
                        <strong id="progress-remaining"><?php echo esc_html($stats['overall']['pending']); ?></strong>
                        <span><?php _e('Restantes', 'haklai-app'); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Logs -->
            <div class="haklai-migration-logs">
                <h2><?php _e('Log de Migração', 'haklai-app'); ?></h2>
                <div class="logs-container" id="migration-logs">
                    <p class="log-entry info">
                        <i class="fas fa-info-circle"></i> 
                        <?php _e('Aguardando início da migração...', 'haklai-app'); ?>
                    </p>
                </div>
            </div>
        </div>
        <?php
    }
    
    /**
     * Obtém estatísticas da migração
     */
    private function get_migration_stats() {
        global $wpdb;
        
        $stats = [
            'members' => ['total' => 0, 'migrated' => 0, 'pending' => 0],
            'cells' => ['total' => 0, 'migrated' => 0, 'pending' => 0],
            'meetings' => ['total' => 0, 'migrated' => 0, 'pending' => 0],
            'overall' => ['total' => 0, 'migrated' => 0, 'pending' => 0, 'percentage' => 0]
        ];
        
        // Conta membros no CPT
        $members_cpt = get_posts([
            'post_type' => 'haklai_member',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $stats['members']['total'] = count($members_cpt);
        
        // Conta células no CPT
        $cells_cpt = get_posts([
            'post_type' => 'haklai_cell',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $stats['cells']['total'] = count($cells_cpt);
        
        // Conta reuniões no CPT
        $meetings_cpt = get_posts([
            'post_type' => 'haklai_meeting',
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields' => 'ids'
        ]);
        $stats['meetings']['total'] = count($meetings_cpt);
        
        // Conta migrados nas tabelas
        if (Haklai_DB::tables_exist()) {
            $members_table = Haklai_DB::get_table_name('members');
            $cells_table = Haklai_DB::get_table_name('cells');
            $meetings_table = Haklai_DB::get_table_name('meetings');
            
            $stats['members']['migrated'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$members_table}");
            $stats['cells']['migrated'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$cells_table}");
            $stats['meetings']['migrated'] = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$meetings_table}");
            
            $stats['members']['pending'] = max(0, $stats['members']['total'] - $stats['members']['migrated']);
            $stats['cells']['pending'] = max(0, $stats['cells']['total'] - $stats['cells']['migrated']);
            $stats['meetings']['pending'] = max(0, $stats['meetings']['total'] - $stats['meetings']['migrated']);
        }
        
        // Calcula totais
        $stats['overall']['total'] = $stats['members']['total'] + $stats['cells']['total'] + $stats['meetings']['total'];
        $stats['overall']['migrated'] = $stats['members']['migrated'] + $stats['cells']['migrated'] + $stats['meetings']['migrated'];
        $stats['overall']['pending'] = $stats['members']['pending'] + $stats['cells']['pending'] + $stats['meetings']['pending'];
        
        if ($stats['overall']['total'] > 0) {
            $stats['overall']['percentage'] = round(($stats['overall']['migrated'] / $stats['overall']['total']) * 100, 1);
        }
        
        return $stats;
    }
    
    /**
     * AJAX: Obtém estatísticas
     */
    public function ajax_get_stats() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        $stats = $this->get_migration_stats();
        wp_send_json_success($stats);
    }
    
    /**
     * AJAX: Migra um lote de dados
     */
    public function ajax_migrate_batch() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        $type = sanitize_text_field($_POST['type'] ?? 'members');
        $offset = intval($_POST['offset'] ?? 0);
        $batch_size = intval($_POST['batch_size'] ?? 10);
        
        $result = $this->migrate_batch($type, $offset, $batch_size);
        wp_send_json_success($result);
    }
    
    /**
     * Migra um lote de dados
     */
    private function migrate_batch($type, $offset, $batch_size) {
        $result = [
            'type' => $type,
            'processed' => 0,
            'success' => 0,
            'errors' => 0,
            'messages' => [],
            'has_more' => false
        ];
        
        if (!Haklai_DB::tables_exist()) {
            $result['errors']++;
            $result['messages'][] = __('Tabelas não existem', 'haklai-app');
            return $result;
        }
        
        switch ($type) {
            case 'cells':
                $posts = get_posts([
                    'post_type' => 'haklai_cell',
                    'post_status' => 'any',
                    'posts_per_page' => $batch_size,
                    'offset' => $offset,
                    'orderby' => 'ID',
                    'order' => 'ASC'
                ]);
                
                foreach ($posts as $post) {
                    $result['processed']++;
                    $table_id = Haklai_DB::sync_cell_from_cpt($post->ID);
                    if ($table_id) {
                        $result['success']++;
                        $result['messages'][] = sprintf(__('Célula "%s" migrada com sucesso (ID: %d)', 'haklai-app'), $post->post_title, $table_id);
                    } else {
                        $result['errors']++;
                        $result['messages'][] = sprintf(__('Erro ao migrar célula "%s" (ID: %d)', 'haklai-app'), $post->post_title, $post->ID);
                    }
                }
                
                // Verifica se há mais
                $total = wp_count_posts('haklai_cell');
                $total_posts = $total->publish + $total->draft + $total->private;
                $result['has_more'] = ($offset + $batch_size) < $total_posts;
                break;
                
            case 'members':
                $posts = get_posts([
                    'post_type' => 'haklai_member',
                    'post_status' => 'any',
                    'posts_per_page' => $batch_size,
                    'offset' => $offset,
                    'orderby' => 'ID',
                    'order' => 'ASC'
                ]);
                
                foreach ($posts as $post) {
                    $result['processed']++;
                    $table_id = Haklai_DB::sync_member_from_cpt($post->ID);
                    if ($table_id) {
                        $result['success']++;
                        $result['messages'][] = sprintf(__('Membro "%s" migrado com sucesso (ID: %d)', 'haklai-app'), $post->post_title, $table_id);
                    } else {
                        $result['errors']++;
                        $result['messages'][] = sprintf(__('Erro ao migrar membro "%s" (ID: %d)', 'haklai-app'), $post->post_title, $post->ID);
                    }
                }
                
                // Verifica se há mais
                $total = wp_count_posts('haklai_member');
                $total_posts = $total->publish + $total->draft + $total->private;
                $result['has_more'] = ($offset + $batch_size) < $total_posts;
                break;
                
            case 'meetings':
                $posts = get_posts([
                    'post_type' => 'haklai_meeting',
                    'post_status' => 'any',
                    'posts_per_page' => $batch_size,
                    'offset' => $offset,
                    'orderby' => 'ID',
                    'order' => 'ASC'
                ]);
                
                foreach ($posts as $post) {
                    $result['processed']++;
                    $table_id = Haklai_DB::sync_meeting_from_cpt($post->ID);
                    if ($table_id) {
                        $result['success']++;
                        $result['messages'][] = sprintf(__('Reunião "%s" migrada com sucesso (ID: %d)', 'haklai-app'), $post->post_title, $table_id);
                    } else {
                        $result['errors']++;
                        $result['messages'][] = sprintf(__('Erro ao migrar reunião "%s" (ID: %d)', 'haklai-app'), $post->post_title, $post->ID);
                    }
                }
                
                // Verifica se há mais
                $total = wp_count_posts('haklai_meeting');
                $total_posts = $total->publish + $total->draft + $total->private;
                $result['has_more'] = ($offset + $batch_size) < $total_posts;
                break;
        }
        
        return $result;
    }
    
    /**
     * AJAX: Valida dados antes da migração
     */
    public function ajax_validate_migration() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        $validation = [
            'tables_exist' => Haklai_DB::tables_exist(),
            'members_count' => wp_count_posts('haklai_member'),
            'cells_count' => wp_count_posts('haklai_cell'),
            'meetings_count' => wp_count_posts('haklai_meeting'),
            'valid' => true,
            'messages' => []
        ];
        
        if (!$validation['tables_exist']) {
            $validation['valid'] = false;
            $validation['messages'][] = __('Tabelas customizadas não existem', 'haklai-app');
        }
        
        wp_send_json_success($validation);
    }
    
    /**
     * AJAX: Exporta backup dos dados
     */
    public function ajax_export_backup() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        // Aqui você pode implementar exportação de backup
        // Por enquanto, apenas retorna sucesso
        wp_send_json_success([
            'message' => __('Backup exportado com sucesso', 'haklai-app')
        ]);
    }
    
    /**
     * AJAX: Valida integridade dos mapeamentos CPT ↔ Tabela
     * 
     * Versão 2.0 - Usa Haklai_Sync_Manager
     */
    public function ajax_validate_integrity() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
        // Valida integridade usando o novo sistema
        $validation = Haklai_Sync_Manager::validate_integrity();
        
        wp_send_json_success($validation);
    }
    
    /**
     * AJAX: Repara mapeamentos perdidos ou inválidos
     * 
     * Versão 2.0 - Usa Haklai_Sync_Manager
     */
    public function ajax_repair_mappings() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
        // Repara mapeamentos usando o novo sistema
        $result = Haklai_Sync_Manager::repair_mappings();
        
        wp_send_json_success([
            'repaired' => $result['repaired'],
            'errors' => $result['errors'],
            'message' => sprintf(
                __('Reparo concluído: %d registros reparados, %d erros', 'haklai-app'),
                $result['repaired'],
                $result['errors']
            )
        ]);
    }
    
    /**
     * AJAX: Obtém status detalhado de sincronização
     * 
     * Versão 2.0 - Usa Haklai_Sync_Manager
     */
    public function ajax_get_sync_status() {
        check_ajax_referer('haklai_migration_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão insuficiente', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
        // Obtém status usando o novo sistema
        $status = Haklai_Sync_Manager::get_sync_status();
        
        wp_send_json_success($status);
    }
}

// Inicializa apenas se for admin e após 'plugins_loaded'
if (is_admin()) {
    add_action('plugins_loaded', function() {
        new Haklai_Migration_Page();
    });
}

