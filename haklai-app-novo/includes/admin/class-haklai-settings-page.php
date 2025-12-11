<?php
/**
 * Página de Configurações do Plugin Haklai
 * Inclui Importação e Exportação de Membros
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Settings_Page {
    
    /**
     * Instâncias de classes auxiliares
     */
    private $email_templates;
    private $capabilities_manager;
    
    public function __construct() {
        // Carrega classes auxiliares
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-email-templates.php';
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-capabilities-manager.php';
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-import-history.php';
        
        $this->email_templates = new Haklai_Email_Templates();
        $this->capabilities_manager = new Haklai_Capabilities_Manager();
        
        // Registra menu com prioridade 20 (depois do menu principal)
        add_action('admin_menu', [$this, 'add_settings_page'], 20);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX Handlers - Importação/Exportação
        add_action('wp_ajax_haklai_import_process', [$this, 'ajax_import_process']);
        add_action('wp_ajax_haklai_export_process', [$this, 'ajax_export_process']);
        add_action('wp_ajax_haklai_download_template', [$this, 'ajax_download_template']);
        
        // AJAX Handlers - Novas Abas
        add_action('wp_ajax_haklai_save_license', [$this, 'ajax_save_license']);
        add_action('wp_ajax_haklai_save_email_template', [$this, 'ajax_save_email_template']);
        add_action('wp_ajax_haklai_test_email', [$this, 'ajax_test_email']);
        add_action('wp_ajax_haklai_save_capabilities', [$this, 'ajax_save_capabilities']);
        add_action('wp_ajax_haklai_save_general', [$this, 'ajax_save_general']);
        add_action('wp_ajax_haklai_get_import_log', [$this, 'ajax_get_import_log']);
        
        // AJAX Handlers - Sincronização
        add_action('wp_ajax_haklai_sync_validate_integrity', [$this, 'ajax_sync_validate_integrity']);
        add_action('wp_ajax_haklai_sync_repair_mappings', [$this, 'ajax_sync_repair_mappings']);
        add_action('wp_ajax_haklai_sync_get_status', [$this, 'ajax_sync_get_status']);
    }
    
    /**
     * Adiciona página de configurações
     * FASE 5: Só adiciona se usuário tiver tenant
     */
    public function add_settings_page() {
        // FASE 5: Só adiciona menu de configurações se usuário tiver tenant
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            // Se não tiver tenant, não mostra menu de configurações
            if (!$current_tenant_id) {
                return;
            }
        }
        
        add_submenu_page(
            'haklai',
            __('Configurações', 'haklai-app'),
            __('Configurações', 'haklai-app'),
            'manage_options',
            'haklai-settings',
            [$this, 'render_settings_page']
        );
    }
    
    /**
     * Registra configurações
     */
    public function register_settings() {
        // Licença
        register_setting('haklai_settings', 'haklai_license_key');
        register_setting('haklai_settings', 'haklai_license_status');
        register_setting('haklai_settings', 'haklai_license_expires');
        
        // Igreja e personalização
        register_setting('haklai_settings', 'haklai_church_name');
        register_setting('haklai_settings', 'haklai_church_logo');
        register_setting('haklai_settings', 'haklai_primary_color');
        register_setting('haklai_settings', 'haklai_secondary_color');
        register_setting('haklai_settings', 'haklai_background_color');
        register_setting('haklai_settings', 'haklai_theme_mode');
        
        // Backup
        register_setting('haklai_settings', 'haklai_auto_backup_enabled');
        
        // Histórico
        register_setting('haklai_settings', 'haklai_import_history');
        
        // Capabilities customizadas
        register_setting('haklai_settings', 'haklai_custom_capabilities');
    }
    
    /**
     * Enfileira scripts
     */
    public function enqueue_scripts($hook) {
        if ($hook !== 'haklai_page_haklai-settings') {
            return;
        }
        
        // WordPress Media Uploader (para upload de logo)
        wp_enqueue_media();
        
        // CSS da página
        wp_enqueue_style(
            'haklai-settings',
            HAKLAI_PLUGIN_URL . 'assets/css/haklai-settings.css',
            [],
            HAKLAI_VERSION
        );
        
        // JavaScript da página
        wp_enqueue_script(
            'haklai-settings',
            HAKLAI_PLUGIN_URL . 'assets/js/haklai-settings.js',
            ['jquery', 'wp-util'],
            HAKLAI_VERSION,
            true
        );
        
        wp_localize_script('haklai-settings', 'haklaiSettings', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('haklai_nonce'),
            'i18n' => [
                'importing' => __('Importando...', 'haklai-app'),
                'exporting' => __('Exportando...', 'haklai-app'),
                'success' => __('Sucesso!', 'haklai-app'),
                'error' => __('Erro', 'haklai-app'),
                'confirm_import' => __('Confirma a importação deste arquivo?', 'haklai-app'),
                'saved' => __('Salvo com sucesso!', 'haklai-app'),
            ]
        ]);
    }
    
    /**
     * Renderiza página
     */
    public function render_settings_page() {
        include HAKLAI_PLUGIN_PATH . 'includes/templates/admin-settings-page.php';
    }
    
    /**
     * Processa importação via AJAX
     */
    public function ajax_import_process() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        if (empty($_FILES['import_file'])) {
            wp_send_json_error(['message' => __('Nenhum arquivo enviado', 'haklai-app')]);
        }
        
        $file = $_FILES['import_file'];
        $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if (!in_array($file_ext, ['csv', 'xlsx', 'xls'])) {
            wp_send_json_error(['message' => __('Formato de arquivo não suportado. Use CSV ou Excel.', 'haklai-app')]);
        }
        
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-importer.php';
        $importer = new Haklai_Importer();
        
        $result = $importer->process_import($file['tmp_name'], $file_ext);
        
        if ($result['success']) {
            // Adiciona ao histórico
            Haklai_Import_History::add_entry([
                'filename' => $file['name'],
                'records' => $result['imported'],
                'errors' => $result['errors'],
                'warnings' => $result['warnings'],
                'log' => $result['log']
            ]);
            
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Processa exportação via AJAX
     */
    public function ajax_export_process() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $format = sanitize_text_field($_POST['format'] ?? 'csv');
        $filters = isset($_POST['filters']) ? $_POST['filters'] : [];
        
        $sanitized_filters = [
            'cell_id' => !empty($filters['cell_id']) ? intval($filters['cell_id']) : '',
            'status' => !empty($filters['status']) ? sanitize_text_field($filters['status']) : '',
            'role_level' => isset($filters['role_level']) && $filters['role_level'] !== '' ? intval($filters['role_level']) : '',
            'baptism_status' => !empty($filters['baptism_status']) ? sanitize_text_field($filters['baptism_status']) : '',
            'date_from' => !empty($filters['date_from']) ? sanitize_text_field($filters['date_from']) : '',
            'date_to' => !empty($filters['date_to']) ? sanitize_text_field($filters['date_to']) : '',
        ];
        
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-importer.php';
        $importer = new Haklai_Importer();
        
        $result = $importer->export_members($sanitized_filters, $format);
        
        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }
    
    /**
     * Download de template
     */
    public function ajax_download_template() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(__('Permissão negada', 'haklai-app'));
        }
        
        require_once HAKLAI_PLUGIN_PATH . 'includes/admin/class-haklai-importer.php';
        $template_path = Haklai_Importer::generate_template();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="haklai-import-template.csv"');
        readfile($template_path);
        exit;
    }
    
    /**
     * Salva configurações de licença
     */
    public function ajax_save_license() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $license_key = sanitize_text_field($_POST['license_key'] ?? '');
        $license_expires = sanitize_text_field($_POST['license_expires'] ?? '');
        
        update_option('haklai_license_key', $license_key);
        update_option('haklai_license_expires', $license_expires);
        update_option('haklai_license_status', 'active');
        
        wp_send_json_success(['message' => __('Licença salva com sucesso', 'haklai-app')]);
    }
    
    /**
     * Salva template de email
     */
    public function ajax_save_email_template() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $template_key = sanitize_text_field($_POST['template_key'] ?? '');
        $subject = sanitize_text_field($_POST['subject'] ?? '');
        $body = wp_kses_post($_POST['body'] ?? '');
        
        $this->email_templates->save_template_config($template_key, $subject, $body);
        
        wp_send_json_success(['message' => __('Template salvo com sucesso', 'haklai-app')]);
    }
    
    /**
     * Envia email de teste
     */
    public function ajax_test_email() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $template_key = sanitize_text_field($_POST['template_key'] ?? '');
        $test_email = sanitize_email($_POST['test_email'] ?? '');
        
        if (!$test_email) {
            wp_send_json_error(['message' => __('Email inválido', 'haklai-app')]);
        }
        
        $config = $this->email_templates->get_template_config($template_key);
        
        $variables = [
            'nome' => 'Usuário Teste',
            'email' => $test_email,
            'usuario' => 'usuario.teste',
            'senha' => '********',
            'celula' => 'Célula Exemplo',
            'nivel' => 'Líder de Célula',
            'site_url' => home_url()
        ];
        
        $subject = Haklai_Email_Templates::process_variables($config['subject'], $variables);
        $body = Haklai_Email_Templates::process_variables($config['body'], $variables);
        
        $sent = wp_mail($test_email, $subject, $body, ['Content-Type: text/html; charset=UTF-8']);
        
        if ($sent) {
            wp_send_json_success(['message' => __('Email de teste enviado!', 'haklai-app')]);
        } else {
            wp_send_json_error(['message' => __('Erro ao enviar email', 'haklai-app')]);
        }
    }
    
    /**
     * Salva capabilities
     */
    public function ajax_save_capabilities() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $role_slug = sanitize_text_field($_POST['role_slug'] ?? '');
        $capabilities = $_POST['capabilities'] ?? [];
        
        // Sanitiza capabilities
        $sanitized_caps = [];
        foreach ($capabilities as $cap => $value) {
            $sanitized_caps[sanitize_text_field($cap)] = (bool)$value;
        }
        
        $this->capabilities_manager->save_role_capabilities($role_slug, $sanitized_caps);
        
        wp_send_json_success(['message' => __('Permissões salvas com sucesso', 'haklai-app')]);
    }
    
    /**
     * Salva configurações gerais
     */
    public function ajax_save_general() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $church_name = sanitize_text_field($_POST['church_name'] ?? '');
        $church_logo = esc_url_raw($_POST['church_logo'] ?? '');
        $primary_color = sanitize_hex_color($_POST['primary_color'] ?? '#667eea');
        $secondary_color = sanitize_hex_color($_POST['secondary_color'] ?? '#764ba2');
        $background_color = sanitize_hex_color($_POST['background_color'] ?? '#f7fafc');
        $theme_mode = sanitize_text_field($_POST['theme_mode'] ?? 'light');
        
        // Valida se o tema é válido
        if (!in_array($theme_mode, ['light', 'dark'])) {
            $theme_mode = 'light';
        }
        
        update_option('haklai_church_name', $church_name);
        update_option('haklai_church_logo', $church_logo);
        update_option('haklai_primary_color', $primary_color);
        update_option('haklai_secondary_color', $secondary_color);
        update_option('haklai_background_color', $background_color);
        update_option('haklai_theme_mode', $theme_mode);
        
        wp_send_json_success(['message' => __('Configurações salvas com sucesso', 'haklai-app')]);
    }
    
    /**
     * Obtém log de importação
     */
    public function ajax_get_import_log() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        $entry_id = sanitize_text_field($_POST['entry_id'] ?? '');
        $entry = Haklai_Import_History::get_entry($entry_id);
        
        if ($entry) {
            wp_send_json_success(['entry' => $entry]);
        } else {
            wp_send_json_error(['message' => __('Log não encontrado', 'haklai-app')]);
        }
    }
    
    /**
     * AJAX: Valida integridade dos mapeamentos (aba Sincronização)
     */
    public function ajax_sync_validate_integrity() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
        $validation = Haklai_Sync_Manager::validate_integrity();
        
        wp_send_json_success($validation);
    }
    
    /**
     * AJAX: Repara mapeamentos (aba Sincronização)
     */
    public function ajax_sync_repair_mappings() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
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
     * AJAX: Obtém status de sincronização (aba Sincronização)
     */
    public function ajax_sync_get_status() {
        check_ajax_referer('haklai_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Permissão negada', 'haklai-app')]);
        }
        
        if (!class_exists('Haklai_Sync_Manager')) {
            wp_send_json_error(['message' => __('Sistema de sincronização não disponível', 'haklai-app')]);
        }
        
        $status = Haklai_Sync_Manager::get_sync_status();
        
        wp_send_json_success($status);
    }
}

// Inicializa apenas se for admin e após 'plugins_loaded'
if (is_admin()) {
    add_action('plugins_loaded', function() {
        new Haklai_Settings_Page();
    });
}

