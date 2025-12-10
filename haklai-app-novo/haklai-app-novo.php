<?php
/**
 * Plugin Name: Haklai Church Management System
 * Plugin URI: https://linx.pt
 * Description: Sistema completo de gerenciamento de igrejas e membros com controle de presença, relatórios e dashboard avançado. Multi-tenant com Custom Post Types.
 * Version: 2.0.0
 * Author: Ricardo Sarmento
 * Author URI: https://linx.pt
 * License: GPL-2.0-or-later
 * Text Domain: haklai-app
 * Domain Path: /languages
 * Requires at least: 6.0
 * Tested up to: 6.4
 * Requires PHP: 8.1
 * Network: false
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Define constantes do plugin
define('HAKLAI_PLUGIN_FILE', __FILE__);
define('HAKLAI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('HAKLAI_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('HAKLAI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('HAKLAI_VERSION', '2.0.0');
define('HAKLAI_DB_VERSION', '1.0.0');
define('HAKLAI_DB_PREFIX', 'hklapp_'); // Prefixo para tabelas do Haklai

/**
 * Classe principal do plugin Haklai App
 * Sistema Multi-tenant com Custom Post Types
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */
class Haklai_App {
    
    /**
     * Instância única da classe
     * 
     * @var Haklai_App
     */
    private static $instance = null;
    
    /**
     * Versão do plugin
     * 
     * @var string
     */
    public $version = HAKLAI_VERSION;
    
    /**
     * Instância da classe de Custom Post Types
     * 
     * @var Haklai_Post_Types
     */
    public $post_types;
    
    /**
     * Instância da classe de shortcodes
     * 
     * @var Haklai_Shortcodes
     */
    public $shortcodes;
    
    /**
     * Instância da classe de permissões
     * 
     * @var Haklai_Permissions
     */
    public $permissions;
    
    /**
     * Instância da classe de sincronização de usuários
     * 
     * @var Haklai_User_Sync
     */
    public $user_sync;
    
    /**
     * Construtor privado para implementar Singleton
     */
    private function __construct() {
        $this->define_constants();
        $this->includes();
        $this->init_hooks();
    }
    
    /**
     * Retorna a instância única da classe
     * 
     * @return Haklai_App
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Define constantes adicionais
     */
    private function define_constants() {
        define('HAKLAI_ASSETS_URL', HAKLAI_PLUGIN_URL . 'assets/');
        define('HAKLAI_INCLUDES_PATH', HAKLAI_PLUGIN_PATH . 'includes/');
        define('HAKLAI_TEMPLATES_PATH', HAKLAI_PLUGIN_PATH . 'includes/templates/');
        define('HAKLAI_UPLOAD_DIR', wp_upload_dir()['basedir'] . '/haklai-app/');
        define('HAKLAI_UPLOAD_URL', wp_upload_dir()['baseurl'] . '/haklai-app/');
    }
    
    /**
     * Inclui arquivos necessários
     */
    private function includes() {
        // Classes core
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-activator.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-deactivator.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-post-types.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-permissions.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-sync-manager.php'; // Gerenciador de sincronização CPT ↔ MySQL
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-db.php'; // Classe de gerenciamento de banco de dados
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-user-sync.php';
        
        // Classes admin (importação/exportação e configurações)
        if (is_admin()) {
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-importer.php';
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-settings-page.php';
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-migration-page.php';
            // FASE 5: Onboarding (SaaS) - Apenas Super Admin pode criar tenant
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-onboarding-page.php';
            // FASE 5: Página "Aguardando Associação" - Usuários sem tenant
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-pending-association-page.php';
            // FASE 5: Gerenciamento de Tenants - Apenas Super Admin
            require_once HAKLAI_INCLUDES_PATH . 'admin/class-haklai-tenants-management-page.php';
        }
        
        // Funções utilitárias
        require_once HAKLAI_INCLUDES_PATH . 'haklai-functions.php';
        
        // FASE 3: Entities e Repositories
        require_once HAKLAI_INCLUDES_PATH . 'entities/Member.php';
        require_once HAKLAI_INCLUDES_PATH . 'entities/Formation.php';
        require_once HAKLAI_INCLUDES_PATH . 'entities/Role.php';
        require_once HAKLAI_INCLUDES_PATH . 'entities/Tenant.php';
        require_once HAKLAI_INCLUDES_PATH . 'repositories/MemberRepository.php';
        require_once HAKLAI_INCLUDES_PATH . 'repositories/FormationRepository.php';
        require_once HAKLAI_INCLUDES_PATH . 'repositories/RoleRepository.php';
        require_once HAKLAI_INCLUDES_PATH . 'repositories/StatisticsRepository.php';
        
        // FASE 4: Multi-Tenancy
        require_once HAKLAI_INCLUDES_PATH . 'repositories/TenantRepository.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-tenant-manager.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-db-migration.php';
        require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-tenant-query-filter.php'; // FASE 4: Filtro de isolamento de tenants
        
        // Sistema de shortcodes
        require_once HAKLAI_INCLUDES_PATH . 'shortcodes/class-haklai-shortcodes.php';
        
        // Inicializa classes
        $this->post_types = new Haklai_Post_Types();
        $this->shortcodes = new Haklai_Shortcodes();
        $this->permissions = new Haklai_Permissions();
        $this->user_sync = new Haklai_User_Sync();
        
        // FASE 4: Inicializa filtro de isolamento de tenants (sempre ativo)
        new Haklai_Tenant_Query_Filter();
        
        // FASE 5: Inicializa páginas de onboarding e gerenciamento (se estiver no admin)
        if (is_admin()) {
            new Haklai_Onboarding_Page();
            new Haklai_Pending_Association_Page();
            new Haklai_Tenants_Management_Page();
        }
    }
    
    /**
     * Inicializa hooks do WordPress
     */
    private function init_hooks() {
        // Hooks de ativação e desativação
        register_activation_hook(__FILE__, array('Haklai_Activator', 'activate'));
        register_deactivation_hook(__FILE__, array('Haklai_Deactivator', 'deactivate'));
        
        // Hooks de inicialização
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('admin_menu', array($this, 'register_admin_menu'));
        
        // Hooks AJAX
        add_action('wp_ajax_haklai_ajax_handler', array($this, 'handle_ajax'));
        add_action('wp_ajax_nopriv_haklai_ajax_handler', array($this, 'handle_ajax'));
        add_action('wp_ajax_haklai_set_theme_mode', array($this, 'ajax_set_theme_mode'));
        // Upload de imagem (AJAX dedicado)
        add_action('wp_ajax_haklai_upload_image', array($this, 'upload_image'));
        add_action('wp_ajax_nopriv_haklai_upload_image', array($this, 'upload_image'));
        
        // Hooks de upgrade
        add_action('upgrader_process_complete', array($this, 'upgrade_check'), 10, 2);
        
        // Hooks de backup
        add_action('haklai_daily_backup', array($this, 'run_daily_backup'));
        add_action('haklai_cleanup_logs', array($this, 'cleanup_old_logs'));
        
        // Hooks de limpeza
        add_action('wp_ajax_haklai_cleanup', array($this, 'cleanup_data'));
        
        // Hook para interceptar visualização de relatórios
        add_action('template_redirect', array($this, 'handle_report_view'));
        
        // Hook para buscar dados filtrados de relatórios
        add_action('wp_ajax_haklai_get_filtered_reports', array($this, 'get_filtered_reports'));
        add_action('wp_ajax_nopriv_haklai_get_filtered_reports', array($this, 'get_filtered_reports'));
    }

    /**
     * Registra menu do WP-Admin
     * FASE 5: Menu condicional baseado em tenant
     */
    public function register_admin_menu() {
        // Verifica se usuário tem tenant associado
        $has_tenant = false;
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            $has_tenant = !empty($current_tenant_id);
        }
        
        // Menu principal "Haklai" (sempre aparece)
        add_menu_page(
            __('Haklai', 'haklai-app'),
            __('Haklai', 'haklai-app'),
            'haklai_access_dashboard',
            'haklai',
            array($this, 'admin_menu_redirect_callback'),
            'dashicons-admin-multisite',
            28
        );
        
        // FASE 5: Se NÃO tem tenant, não adiciona outros submenus
        // (Menu de onboarding será adicionado pela classe Haklai_Onboarding_Page)
        if (!$has_tenant) {
            return;
        }
        
        // Se TEM tenant, adiciona todos os submenus normalmente
        // Renomeia o primeiro submenu para "Página Inicial"
        add_submenu_page(
            'haklai',
            __('Página Inicial', 'haklai-app'),
            __('Página Inicial', 'haklai-app'),
            'haklai_access_dashboard',
            'haklai',
            array($this, 'admin_menu_redirect_callback')
        );

        // Submenus para os CPTs
        add_submenu_page(
            'haklai',
            __('Membros', 'haklai-app'),
            __('Membros', 'haklai-app'),
            'haklai_manage_members',
            'edit.php?post_type=haklai_member'
        );

        add_submenu_page(
            'haklai',
            __('Células', 'haklai-app'),
            __('Células', 'haklai-app'),
            'haklai_manage_cells',
            'edit.php?post_type=haklai_cell'
        );

        add_submenu_page(
            'haklai',
            __('Reuniões', 'haklai-app'),
            __('Reuniões', 'haklai-app'),
            'haklai_access_checkpoint',
            'edit.php?post_type=haklai_meeting'
        );

        add_submenu_page(
            'haklai',
            __('Relatórios', 'haklai-app'),
            __('Relatórios', 'haklai-app'),
            'haklai_access_reports',
            'edit.php?post_type=haklai_report'
        );

        // Configurações (Importação/Exportação) - Adicionado por classe separada
        // add_submenu_page registrado em: includes/admin/class-haklai-settings-page.php
    }

    /**
     * Callback do menu principal - exibe página de boas-vindas
     * FASE 5: Redireciona se não tiver tenant ANTES de incluir template (evita erro "headers already sent")
     */
    public function admin_menu_redirect_callback() {
        if (!current_user_can('haklai_access_dashboard')) {
            wp_die(__('Acesso negado', 'haklai-app'));
        }
        
        // FASE 5: Verifica se usuário tem tenant associado ANTES de incluir template
        // Se não tiver, redireciona conforme role (Super Admin → onboarding, outros → aguardando)
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            if (!$current_tenant_id) {
                // Usuário não tem tenant
                if (current_user_can('manage_options')) {
                    // Super Admin → Redireciona para onboarding
                    wp_redirect(admin_url('admin.php?page=haklai-onboarding'));
                } else {
                    // Usuário comum → Redireciona para "Aguardando Associação"
                    wp_redirect(admin_url('admin.php?page=haklai-pending-association'));
                }
                exit;
            }
        }
        
        // Só inclui o template se tiver tenant (ou se não houver sistema de tenants)
        include HAKLAI_TEMPLATES_PATH . 'admin-welcome-page.php';
    }
    
    /**
     * Inicializa o plugin
     */
    public function init() {
        // Carrega traduções
        load_plugin_textdomain('haklai-app', false, dirname(plugin_basename(__FILE__)) . '/languages/');
        
        // Verifica se precisa fazer upgrade
        $this->check_upgrade();
        
        // Inicializa componentes
        $this->init_components();
        
        // Hook personalizado para extensões
        do_action('haklai_init');
    }
    
    /**
     * Inicializa componentes do sistema
     */
    private function init_components() {
        // Sistema de permissões
        $this->init_capabilities();
        
        // Sistema de notificações
        $this->init_notifications();
        
        // Sistema de logs
        $this->init_logging();
        
        // Sistema de backup
        $this->init_backup_system();
    }
    
    /**
     * Inicializa sistema de capabilities
     */
    private function init_capabilities() {
        // Verifica se as capabilities já foram criadas
        if (!get_option('haklai_capabilities_created')) {
            $this->permissions->create_capabilities();
            update_option('haklai_capabilities_created', true);
        }
    }
    
    /**
     * Inicializa sistema de notificações
     */
    private function init_notifications() {
        // Cria diretório de uploads se não existir
        $upload_dir = HAKLAI_UPLOAD_DIR;
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }
    }
    
    /**
     * Inicializa sistema de logging
     */
    private function init_logging() {
        // Cria diretório de logs se não existir
        $log_dir = HAKLAI_UPLOAD_DIR . 'logs/';
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
    }
    
    /**
     * Inicializa sistema de backup
     */
    private function init_backup_system() {
        // Agenda backup diário se não estiver agendado
        if (!wp_next_scheduled('haklai_daily_backup')) {
            wp_schedule_event(time(), 'daily', 'haklai_daily_backup');
        }
        
        // Agenda limpeza de logs se não estiver agendada
        if (!wp_next_scheduled('haklai_cleanup_logs')) {
            wp_schedule_event(time(), 'weekly', 'haklai_cleanup_logs');
        }
    }
    
    /**
     * Enfileira assets do frontend
     */
    public function enqueue_assets() {
        // CSS
        wp_enqueue_style(
            'haklai-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
            array(),
            '5.3.0'
        );
        
        wp_enqueue_style(
            'haklai-fontawesome',
            'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
            array(),
            '6.0.0'
        );
        
        wp_enqueue_style(
            'haklai-google-fonts',
            'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap',
            array(),
            null
        );
        
        // REMOVIDO: haklai-main.css (agora unificado em haklai-design-system.css)
        // O CSS será carregado apenas quando houver shortcodes na página
        // via class-haklai-shortcodes.php -> enqueue_shortcode_assets()
        /*
        wp_enqueue_style(
            'haklai-main',
            HAKLAI_ASSETS_URL . 'css/haklai-main.css',
            array('haklai-bootstrap', 'haklai-fontawesome'),
            $this->version
        );
        */
        
        // JavaScript
        wp_enqueue_script(
            'haklai-bootstrap',
            'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js',
            array('jquery'),
            '5.3.0',
            true
        );
        
        wp_enqueue_script(
            'haklai-main',
            HAKLAI_ASSETS_URL . 'js/haklai-main.js',
            array('jquery', 'haklai-bootstrap'),
            $this->version,
            true
        );
        
        // Aplica tema dark/light baseado na configuração (script inline para evitar flash)
        $theme_mode = get_option('haklai_theme_mode', 'light');
        wp_add_inline_script('haklai-main', "
            (function() {
                var optionValue = '" . esc_js($theme_mode) . "';
                var storedValue = null;
                try {
                    if (window.localStorage) {
                        storedValue = localStorage.getItem('haklai-theme-mode');
                    }
                } catch (error) {
                    console.warn('HaklaiTheme: localStorage indisponível no boot do script.', error);
                }

                var themeMode = (storedValue === 'dark' || storedValue === 'light') ? storedValue : optionValue;

                if (themeMode === 'dark') {
                    document.documentElement.classList.add('haklai-theme-dark');
                } else {
                    document.documentElement.classList.remove('haklai-theme-dark');
                }
            })();
        ", 'before');
        
        // Localiza script com dados AJAX
        wp_localize_script('haklai-main', 'haklai_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('haklai_nonce'),
            'user_id' => get_current_user_id(),
            'user_level' => haklai_get_user_level(),
            'theme_mode' => $theme_mode,
            'translations' => array(
                'loading' => __('Carregando...', 'haklai-app'),
                'error' => __('Erro ao processar solicitação', 'haklai-app'),
                'success' => __('Operação realizada com sucesso', 'haklai-app'),
                'confirm' => __('Tem certeza?', 'haklai-app'),
                'theme_dark' => __('Tema Escuro', 'haklai-app'),
                'theme_light' => __('Tema Claro', 'haklai-app'),
            ),
        ));
    }
    
    /**
     * Enfileira assets do admin
     */
    public function enqueue_admin_assets($hook) {
        // Carrega media uploader nas telas de membros, células e reuniões
        $screen = get_current_screen();
        if ($screen && in_array($screen->post_type, ['haklai_member', 'haklai_cell', 'haklai_meeting'])) {
            wp_enqueue_media();
            wp_enqueue_script('haklai-admin', HAKLAI_ASSETS_URL . 'js/haklai-admin.js', array('jquery'), $this->version, true);
        }
        
        // Carrega apenas nas páginas do plugin Haklai
        if (strpos($hook, 'haklai') !== false) {
            wp_enqueue_style('haklai-admin', HAKLAI_ASSETS_URL . 'css/haklai-admin.css', array(), $this->version);
            wp_enqueue_script('haklai-admin', HAKLAI_ASSETS_URL . 'js/haklai-admin.js', array('jquery'), $this->version, true);
        }
    }
    
    /**
     * Manipula requisições AJAX
     */
    public function handle_ajax() {
        // Verifica nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'haklai_nonce')) {
            wp_die(__('Acesso negado', 'haklai-app'));
        }
        
        // Verifica permissões
        if (!current_user_can('read')) {
            wp_die(__('Permissão insuficiente', 'haklai-app'));
        }
        
        $action = sanitize_text_field($_POST['action_type'] ?? '');
        $data = $_POST['data'] ?? array();
        
        // Sanitiza dados
        $data = $this->sanitize_ajax_data($data);
        
        // Processa ação
        $result = $this->process_ajax_action($action, $data);
        
        // Retorna resultado
        wp_send_json($result);
    }
    
    /**
     * Atualiza o tema global (light/dark) via AJAX
     */
    public function ajax_set_theme_mode() {
        $nonce = $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce, 'haklai_nonce')) {
            wp_send_json_error(__('Sessão expirada. Atualize a página e tente novamente.', 'haklai-app'), 403);
        }

        if (!current_user_can('haklai_access_dashboard')) {
            wp_send_json_error(__('Você não tem permissão para alterar o tema.', 'haklai-app'), 403);
        }

        $theme_mode = isset($_POST['theme_mode']) ? sanitize_text_field(wp_unslash($_POST['theme_mode'])) : 'light';
        $theme_mode = ($theme_mode === 'dark') ? 'dark' : 'light';

        update_option('haklai_theme_mode', $theme_mode);

        wp_send_json_success(array(
            'theme_mode' => $theme_mode,
        ));
    }
    
    /**
     * Sanitiza dados AJAX
     */
    private function sanitize_ajax_data($data) {
        if (is_array($data)) {
            $sanitized = array();
            foreach ($data as $key => $value) {
                // Preserva arrays aninhados (ex: formacoes[])
                if (is_array($value)) {
                    $sanitized[$key] = $this->sanitize_ajax_data($value);
                } else {
                    // Sanitiza valores escalares baseado no tipo
                    if (is_bool($value)) {
                        $sanitized[$key] = $value;
                    } elseif (is_numeric($value)) {
                        $sanitized[$key] = is_float($value) ? floatval($value) : intval($value);
                    } elseif (filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $sanitized[$key] = sanitize_email($value);
                    } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                        $sanitized[$key] = esc_url_raw($value);
                    } else {
                        // Para strings, usa sanitize_text_field mas preserva valores vazios
                        $sanitized[$key] = $value !== null ? sanitize_text_field($value) : '';
                    }
                }
            }
            return $sanitized;
        }
        
        // Para valores não-array
        if (is_bool($data)) {
            return $data;
        } elseif (is_numeric($data)) {
            return is_float($data) ? floatval($data) : intval($data);
        }
        
        return sanitize_text_field($data);
    }
    
    /**
     * Processa ações AJAX
     */
    private function process_ajax_action($action, $data) {
        switch ($action) {
            case 'toggle_presence':
                return $this->toggle_member_presence($data);
                
            case 'add_member':
                return $this->add_member($data);
                
            case 'add_visitor':
                return $this->add_visitor($data);
                
            case 'edit_member':
                return $this->edit_member($data);
                
            case 'get_member_data':
                return $this->get_member_data($data);
                
            case 'download_report':
                return $this->download_report($data);
                
            case 'send_report':
                return $this->send_meeting_report($data);
                
            case 'get_members':
                return $this->get_members($data);
                
            case 'get_statistics':
                return $this->get_statistics($data);
                
            case 'get_attendance_history':
                return $this->get_attendance_history($data);
                
            case 'generate_report_html':
                return $this->generate_report_html($data);
                
            case 'generate_report':
                return $this->generate_report($data);
                
            case 'get_filter_options':
                return $this->get_filter_options($data);
                
            default:
                return array(
                    'success' => false,
                    'message' => __('Ação não reconhecida', 'haklai-app')
                );
        }
    }
    
    /**
     * Alterna presença de membro
     */
    private function toggle_member_presence($data) {
        $member_id = intval($data['member_id'] ?? 0);
        $meeting_id = intval($data['meeting_id'] ?? 0);
        $is_present = $data['is_present'] === 'true';
        
        if (!$member_id || !$meeting_id) {
            return array(
                'success' => false,
                'message' => __('Dados inválidos', 'haklai-app')
            );
        }
        
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_checkpoint')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        // Salva presença usando Custom Post Types
        $result = $this->save_attendance($meeting_id, $member_id, $is_present);
        
        if ($result) {
            return array(
                'success' => true,
                'message' => __('Presença atualizada', 'haklai-app'),
                'data' => array(
                    'member_id' => $member_id,
                    'is_present' => $is_present
                )
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Erro ao atualizar presença', 'haklai-app')
        );
    }
    
    /**
     * Salva presença usando tabelas otimizadas (com fallback para CPT)
     */
    private function save_attendance($meeting_id, $member_id, $is_present) {
        // Tenta usar tabelas otimizadas primeiro
        if (Haklai_DB::tables_exist()) {
            return Haklai_DB::save_attendance($meeting_id, $member_id, $is_present);
        }
        
        // Fallback para CPT (compatibilidade)
        $existing_attendance = get_posts(array(
            'post_type' => 'haklai_attendance',
            'meta_query' => array(
                array(
                    'key' => '_haklai_meeting_id',
                    'value' => $meeting_id
                ),
                array(
                    'key' => '_haklai_member_id',
                    'value' => $member_id
                )
            ),
            'posts_per_page' => 1
        ));
        
        $attendance_data = array(
            'post_type' => 'haklai_attendance',
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_meeting_id' => $meeting_id,
                '_haklai_member_id' => $member_id,
                '_haklai_is_present' => $is_present ? '1' : '0',
                '_haklai_attendance_date' => current_time('mysql')
            )
        );
        
        if (!empty($existing_attendance)) {
            $attendance_data['ID'] = $existing_attendance[0]->ID;
            return wp_update_post($attendance_data);
        } else {
            return wp_insert_post($attendance_data);
        }
    }
    
    /**
     * Adiciona novo membro
     */
    private function add_member($data) {
        // Verifica permissões (admin sempre pode)
        if (!current_user_can('manage_options') && !haklai_user_can_access('haklai_manage_members')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        // Valida dados obrigatórios
        $required_fields = array('name');
        foreach ($required_fields as $field) {
            if (empty($data[$field])) {
                return array(
                    'success' => false,
                    'message' => sprintf(__('Campo %s é obrigatório', 'haklai-app'), $field)
                );
            }
        }
        
        // Cria membro usando Custom Post Type
        $member_data = array(
            'post_type' => 'haklai_member',
            'post_title' => sanitize_text_field($data['name']),
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_email' => sanitize_email($data['email'] ?? ''),
                '_haklai_phone' => sanitize_text_field($data['phone'] ?? ''),
                '_haklai_photo' => sanitize_url($data['photo'] ?? ''),
                '_haklai_skill' => sanitize_text_field($data['skill'] ?? ''),
                '_haklai_leader_id' => intval($data['leader_id'] ?? 0),
                '_haklai_baptism_status' => sanitize_text_field($data['baptism_status'] ?? 'visitor'),
                '_haklai_is_visitor' => isset($data['is_visitor']) ? '1' : '0',
                '_haklai_role_level' => intval($data['role_level'] ?? 0),
                '_haklai_cell_id' => intval($data['cell_id'] ?? 0),
                '_haklai_status' => 'active',
                '_haklai_encontro_deus' => sanitize_text_field($data['encontro_deus'] ?? 'nao'),
                '_haklai_created_at' => current_time('mysql'),
                '_haklai_updated_at' => current_time('mysql')
            )
        );
        
        $member_id = wp_insert_post($member_data);
        
        if ($member_id) {
            // Salva formações separadamente (não pode ir em meta_input array)
            if (isset($data['formacoes']) && is_array($data['formacoes'])) {
                $formacoes_sanitized = array_map('sanitize_text_field', $data['formacoes']);
                update_post_meta($member_id, '_haklai_formacoes', $formacoes_sanitized);
            }
            
            // Sincroniza com tabelas otimizadas (se existirem)
            if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
                Haklai_DB::sync_member_from_cpt($member_id);
            }
            
            return array(
                'success' => true,
                'message' => __('Membro adicionado com sucesso', 'haklai-app'),
                'data' => array('member_id' => $member_id)
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Erro ao adicionar membro', 'haklai-app')
        );
    }
    
    /**
     * Adiciona visitante
     */
    private function add_visitor($data) {
        // Similar ao add_member, mas com status de visitante
        $data['baptism_status'] = 'visitor';
        $data['is_visitor'] = true;
        
        return $this->add_member($data);
    }

    /**
     * Obtém dados de um membro para edição
     */
    private function get_member_data($data) {
        // Verifica permissões
        if (!haklai_user_can_access('haklai_manage_members')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        $member_id = intval($data['member_id'] ?? 0);
        if (!$member_id) {
            return array(
                'success' => false,
                'message' => __('ID do membro inválido', 'haklai-app')
            );
        }
        
        // Verifica se o membro existe
        $member = get_post($member_id);
        if (!$member || $member->post_type !== 'haklai_member') {
            return array(
                'success' => false,
                'message' => __('Membro não encontrado', 'haklai-app')
            );
        }
        
        // Verifica se o líder pode editar este membro (mesma célula)
        $user_id = get_current_user_id();
        $user_level = Haklai_Permissions::get_user_level($user_id);
        
        if ($user_level === 1) { // Líder de Célula
            $member_cell_id = get_post_meta($member_id, '_haklai_cell_id', true);
            $user_cells = Haklai_Permissions::get_user_cells($user_id);
            $user_cell_ids = array_map(function($cell) { return $cell->ID; }, $user_cells);
            
            if (!in_array($member_cell_id, $user_cell_ids)) {
                return array(
                    'success' => false,
                    'message' => __('Você não tem permissão para editar este membro', 'haklai-app')
                );
            }
        }
        
        // Obtém dados do membro
        $cell_id = get_post_meta($member_id, '_haklai_cell_id', true);
        $cell = $cell_id ? get_post($cell_id) : null;
        
        $member_data = array(
            'id' => $member_id,
            'name' => $member->post_title,
            'email' => get_post_meta($member_id, '_haklai_email', true),
            'phone' => get_post_meta($member_id, '_haklai_phone', true),
            'photo' => get_post_meta($member_id, '_haklai_photo', true),
            'skill' => get_post_meta($member_id, '_haklai_skill', true),
            'baptism_status' => get_post_meta($member_id, '_haklai_baptism_status', true),
            'baptism_date' => get_post_meta($member_id, '_haklai_baptism_date', true),
            'status' => get_post_meta($member_id, '_haklai_status', true),
            'cell_id' => $cell_id,
            'cell_name' => $cell ? $cell->post_title : '',
            'encontro_deus' => get_post_meta($member_id, '_haklai_encontro_deus', true),
            'formacoes' => get_post_meta($member_id, '_haklai_formacoes', true) ?: array()
        );
        
        return array(
            'success' => true,
            'data' => $member_data
        );
    }

    /**
     * Edita um membro existente
     */
    private function edit_member($data) {
        // Log para debug (captura todos os dados recebidos)
        error_log('Haklai - Edit Member - Dados recebidos: ' . print_r($data, true));
        
        // Verifica permissões
        if (!haklai_user_can_access('haklai_manage_members')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        $member_id = intval($data['member_id'] ?? 0);
        if (!$member_id) {
            return array(
                'success' => false,
                'message' => __('ID do membro inválido', 'haklai-app')
            );
        }
        
        // Verifica se o membro existe
        $member = get_post($member_id);
        if (!$member || $member->post_type !== 'haklai_member') {
            return array(
                'success' => false,
                'message' => __('Membro não encontrado', 'haklai-app')
            );
        }
        
        // Verifica se o líder pode editar este membro (mesma célula)
        $user_id = get_current_user_id();
        $user_level = Haklai_Permissions::get_user_level($user_id);
        
        if ($user_level === 1) { // Líder de Célula
            $member_cell_id = get_post_meta($member_id, '_haklai_cell_id', true);
            $user_cells = Haklai_Permissions::get_user_cells($user_id);
            $user_cell_ids = array_map(function($cell) { return $cell->ID; }, $user_cells);
            
            if (!in_array($member_cell_id, $user_cell_ids)) {
                return array(
                    'success' => false,
                    'message' => __('Você não tem permissão para editar este membro', 'haklai-app')
                );
            }
        }
        
        // Atualiza nome
        if (!empty($data['name'])) {
            wp_update_post(array(
                'ID' => $member_id,
                'post_title' => sanitize_text_field($data['name'])
            ));
        }
        
        // Atualiza metadados
        if (isset($data['email'])) {
            update_post_meta($member_id, '_haklai_email', sanitize_email($data['email']));
        }
        
        if (isset($data['phone'])) {
            update_post_meta($member_id, '_haklai_phone', sanitize_text_field($data['phone']));
        }
        
        // Atualiza foto apenas se for uma URL válida (não objeto vazio)
        if (isset($data['photo']) && !empty($data['photo']) && is_string($data['photo'])) {
            update_post_meta($member_id, '_haklai_photo', esc_url_raw($data['photo']));
            error_log('Haklai - Edit Member - Foto salva: ' . $data['photo'] . ' para membro ID: ' . $member_id);
        } else {
            // Se não vier foto válida, mantém a atual
            error_log('Haklai - Edit Member - Foto não atualizada (mantida atual) para membro ID: ' . $member_id);
        }
        
        if (isset($data['skill'])) {
            update_post_meta($member_id, '_haklai_skill', sanitize_text_field($data['skill']));
        }
        
        if (isset($data['baptism_status'])) {
            update_post_meta($member_id, '_haklai_baptism_status', sanitize_text_field($data['baptism_status']));
        }
        
        if (isset($data['baptism_date'])) {
            update_post_meta($member_id, '_haklai_baptism_date', sanitize_text_field($data['baptism_date']));
        }
        
        if (isset($data['status'])) {
            update_post_meta($member_id, '_haklai_status', sanitize_text_field($data['status']));
        }
        
        // Sempre atualiza encontro_deus (mesmo se não vier no POST, garante que está salvo)
        if (isset($data['encontro_deus'])) {
            $encontro_value = sanitize_text_field($data['encontro_deus']);
        } else {
            // Se não vier, mantém o valor atual ou define como 'nao'
            $encontro_value = get_post_meta($member_id, '_haklai_encontro_deus', true) ?: 'nao';
        }
        update_post_meta($member_id, '_haklai_encontro_deus', $encontro_value);
        // Log para debug (remover em produção se necessário)
        error_log('Haklai - Edit Member - Encontro Deus salvo: ' . $encontro_value . ' para membro ID: ' . $member_id);
        
        if (isset($data['formacoes'])) {
            if (is_array($data['formacoes']) && !empty($data['formacoes'])) {
                $formacoes_sanitized = array_map('sanitize_text_field', $data['formacoes']);
                update_post_meta($member_id, '_haklai_formacoes', $formacoes_sanitized);
            } else {
                // Se vier vazio, limpa
                delete_post_meta($member_id, '_haklai_formacoes');
            }
        }
        
        // Sincroniza com tabelas otimizadas (se existirem)
        if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
            Haklai_DB::sync_member_from_cpt($member_id);
        }
        
        return array(
            'success' => true,
            'message' => __('Membro atualizado com sucesso', 'haklai-app'),
            'data' => array(
                'member_id' => $member_id
            )
        );
    }

    /**
     * Baixa relatório para impressão
     */
    private function download_report($data) {
        // Verifica permissões
        if (!current_user_can('manage_options') && !haklai_user_can_access('haklai_view_reports')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        $report_id = intval($data['report_id'] ?? 0);
        if (!$report_id) {
            return array(
                'success' => false,
                'message' => __('ID do relatório inválido', 'haklai-app')
            );
        }
        
        // Busca o relatório
        $report = get_post($report_id);
        if (!$report || $report->post_type !== 'haklai_report') {
            return array(
                'success' => false,
                'message' => __('Relatório não encontrado', 'haklai-app')
            );
        }
        
        // Inclui o template de impressão
        include plugin_dir_path(__FILE__) . 'includes/templates/relatorio-print-template.php';
        
        // Este método não retorna, ele exibe o HTML diretamente
        return array(
            'success' => true,
            'message' => __('Relatório carregado', 'haklai-app')
        );
    }

    /**
     * Upload de imagem otimizado via AJAX (admin-ajax.php?action=haklai_upload_image)
     * Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
     */
    public function upload_image() {
        // Aumentar limite de memória temporariamente
        $old_memory_limit = ini_get('memory_limit');
        ini_set('memory_limit', '256M'); // Reduzido para 256M
        
        // Aumentar tempo de execução
        set_time_limit(120); // Reduzido para 2 minutos
        
        try {
            // Verifica nonce
            $nonce = $_POST['nonce'] ?? '';
            if (!wp_verify_nonce($nonce, 'haklai_nonce')) {
                wp_send_json_error(array('message' => __('Acesso negado', 'haklai-app')));
            }

            // Verifica arquivo
            if (empty($_FILES['image']) || !isset($_FILES['image']['tmp_name'])) {
                wp_send_json_error(array('message' => __('Nenhuma imagem enviada', 'haklai-app')));
            }

            // Validação de tamanho
            $max_size = 10 * 1024 * 1024; // 10MB
            if ($_FILES['image']['size'] > $max_size) {
                wp_send_json_error(array('message' => __('Imagem muito grande. Máximo 10MB.', 'haklai-app')));
            }

            // Validação de tipo SIMPLIFICADA
            $file_type = wp_check_filetype($_FILES['image']['name']);
            $allowed = array(
                'jpg' => 'image/jpeg', 
                'jpeg' => 'image/jpeg', 
                'png' => 'image/png', 
                'gif' => 'image/gif', 
                'webp' => 'image/webp'
            );
            
            if (empty($file_type['ext']) || !isset($allowed[$file_type['ext']])) {
                wp_send_json_error(array('message' => __('Tipo de arquivo não suportado. Use JPG, PNG, GIF ou WebP.', 'haklai-app')));
            }

            // Otimização de imagem após upload (FASE 2)
            $this->optimize_uploaded_image($_FILES['image']['tmp_name']);

            // Upload usando WordPress nativo (sem overrides complexos)
            require_once ABSPATH . 'wp-admin/includes/file.php';
            $overrides = array(
                'test_form' => false,
                'mimes' => $allowed
            );
            
            $movefile = wp_handle_upload($_FILES['image'], $overrides);

            if ($movefile && !isset($movefile['error'])) {
                // Criar attachment na biblioteca de mídia
                $attachment_id = 0;
                if (function_exists('wp_insert_attachment')) {
                    $attachment = array(
                        'post_mime_type' => $movefile['type'],
                        'post_title' => sanitize_file_name(basename($movefile['file'])),
                        'post_content' => '',
                        'post_status' => 'inherit',
                    );
                    $attachment_id = wp_insert_attachment($attachment, $movefile['file']);
                    if (!is_wp_error($attachment_id)) {
                        require_once ABSPATH . 'wp-admin/includes/image.php';
                        wp_update_attachment_metadata($attachment_id, wp_generate_attachment_metadata($attachment_id, $movefile['file']));
                    }
                }

                // Restaurar limite de memória
                ini_set('memory_limit', $old_memory_limit);
                
                wp_send_json_success(array(
                    'url' => esc_url_raw($movefile['url']),
                    'attachment_id' => intval($attachment_id),
                    'size' => filesize($movefile['file']),
                    'original_name' => $_FILES['image']['name']
                ));
            } else {
                // Restaurar limite de memória
                ini_set('memory_limit', $old_memory_limit);
                wp_send_json_error(array('message' => $movefile['error'] ?? __('Erro ao fazer upload', 'haklai-app')));
            }
            
        } catch (Exception $e) {
            // Restaurar limite de memória em caso de erro
            ini_set('memory_limit', $old_memory_limit);
            error_log('Haklai Upload Error: ' . $e->getMessage());
            wp_send_json_error(array('message' => __('Erro interno do servidor', 'haklai-app')));
        }
    }

    /**
     * Otimiza imagem após upload (FASE 2)
     * Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
     */
    private function optimize_uploaded_image($file_path) {
        if (!file_exists($file_path)) {
            return false;
        }

        $image_info = getimagesize($file_path);
        if (!$image_info) {
            return false;
        }

        $width = $image_info[0];
        $height = $image_info[1];
        $mime = $image_info['mime'];

        // Redimensiona se muito grande (máximo 1920px)
        if ($width > 1920 || $height > 1920) {
            $ratio = min(1920 / $width, 1920 / $height);
            $new_width = intval($width * $ratio);
            $new_height = intval($height * $ratio);
            
            $this->resize_image($file_path, $new_width, $new_height, $mime);
        }

        // Compressão adicional para JPEG
        if ($mime === 'image/jpeg') {
            $this->compress_jpeg($file_path);
        }

        return true;
    }

    /**
     * Compressão específica para JPEG
     */
    private function compress_jpeg($file_path, $quality = 85) {
        $image = imagecreatefromjpeg($file_path);
        if (!$image) {
            return false;
        }
        
        // Salva com compressão
        imagejpeg($image, $file_path, $quality);
        imagedestroy($image);
        
        return true;
    }

    /**
     * Redimensiona imagem mantendo proporção (MELHORADA)
     * Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
     */
    private function resize_image($file_path, $new_width, $new_height, $mime) {
        // Carrega imagem original
        switch ($mime) {
            case 'image/jpeg':
                $source = imagecreatefromjpeg($file_path);
                break;
            case 'image/png':
                $source = imagecreatefrompng($file_path);
                break;
            case 'image/gif':
                $source = imagecreatefromgif($file_path);
                break;
            case 'image/webp':
                if (function_exists('imagecreatefromwebp')) {
                    $source = imagecreatefromwebp($file_path);
                } else {
                    return false;
                }
                break;
            default:
                return false;
        }

        if (!$source) {
            return false;
        }

        // Cria nova imagem redimensionada
        $resized = imagecreatetruecolor($new_width, $new_height);
        
        // Preserva transparência para PNG e GIF
        if ($mime === 'image/png' || $mime === 'image/gif') {
            imagealphablending($resized, false);
            imagesavealpha($resized, true);
            $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
            imagefilledrectangle($resized, 0, 0, $new_width, $new_height, $transparent);
        }

        // Redimensiona com qualidade
        imagecopyresampled($resized, $source, 0, 0, 0, 0, $new_width, $new_height, imagesx($source), imagesy($source));

        // Salva otimizada
        $quality = 85; // 85% qualidade
        switch ($mime) {
            case 'image/jpeg':
                imagejpeg($resized, $file_path, $quality);
                break;
            case 'image/png':
                imagepng($resized, $file_path, 8); // 0-9, 8 é bom equilíbrio
                break;
            case 'image/gif':
                imagegif($resized, $file_path);
                break;
            case 'image/webp':
                if (function_exists('imagewebp')) {
                    imagewebp($resized, $file_path, $quality);
                }
                break;
        }

        // Libera memória
        imagedestroy($source);
        imagedestroy($resized);

        return true;
    }

    /**
     * Gera nome único para arquivo
     * Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
     */
    public function generate_unique_filename($dir, $filename, $ext) {
        $base_name = sanitize_file_name(pathinfo($filename, PATHINFO_FILENAME));
        $timestamp = date('Y-m-d_H-i-s');
        $random = wp_generate_password(6, false);
        
        return $base_name . '_' . $timestamp . '_' . $random . $ext;
    }
    
    /**
     * Envia relatório de reunião
     */
    private function send_meeting_report($data) {
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_checkpoint')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        $meeting_id = intval($data['meeting_id'] ?? 0);
        if (!$meeting_id) {
            return array(
                'success' => false,
                'message' => __('ID da reunião inválido', 'haklai-app')
            );
        }
        
        // Gera relatório HTML
        $report_html = $this->generate_meeting_report_html($meeting_id);
        
        // Salva relatório como Custom Post Type
        $report_data = array(
            'post_type' => 'haklai_report',
            'post_title' => sprintf(__('Relatório de Reunião - %s', 'haklai-app'), date('d/m/Y')),
            'post_content' => $report_html,
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_report_type' => 'meeting',
                '_haklai_meeting_id' => $meeting_id,
                '_haklai_report_date' => current_time('mysql'),
                '_haklai_created_by' => get_current_user_id()
            )
        );
        
        $report_id = wp_insert_post($report_data);
        
        if ($report_id) {
            return array(
                'success' => true,
                'message' => __('Relatório enviado com sucesso', 'haklai-app'),
                'data' => array(
                    'report_id' => $report_id
                )
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Erro ao gerar relatório', 'haklai-app')
        );
    }
    
    /**
     * Gera relatório HTML da reunião (OTIMIZADO)
     * Versão 2.0 - Corrige conversão de ID CPT para ID de tabela
     */
    private function generate_meeting_report_html($meeting_id) {
        $meeting_table_id = $meeting_id;
        
        // Verifica se o ID recebido é de CPT (haklai_meeting)
        $meeting_cpt = get_post($meeting_id);
        if ($meeting_cpt && $meeting_cpt->post_type === 'haklai_meeting') {
            // Converte ID de CPT para ID de tabela MySQL
            if (class_exists('Haklai_Sync_Manager')) {
                $meeting_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                    $meeting_id, 
                    'haklai_meeting', 
                    true // create_if_missing = true para sincronizar automaticamente se não existir
                );
                
                // Se ainda não encontrou, tenta sincronizar explicitamente
                if (!$meeting_table_id && class_exists('Haklai_DB')) {
                    Haklai_DB::sync_meeting_from_cpt($meeting_id);
                    $meeting_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                        $meeting_id, 
                        'haklai_meeting', 
                        false
                    );
                }
            }
        }
        
        // Usa query otimizada se tabelas existirem
        if (Haklai_DB::tables_exist() && $meeting_table_id) {
            $report_data = Haklai_DB::get_meeting_report_data($meeting_table_id);
            
            if (!$report_data) {
                // Se não encontrou na tabela, usa fallback para CPT
                $meeting = get_post($meeting_id);
                if (!$meeting || $meeting->post_type !== 'haklai_meeting') {
                    return '<p>Reunião não encontrada.</p>';
                }
                
                // Fallback para CPT (compatibilidade)
                $cell_id = get_post_meta($meeting_id, '_haklai_cell_id', true);
                $cell = get_post($cell_id);
                
                $attendances = get_posts(array(
                    'post_type' => 'haklai_attendance',
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_meeting_id',
                            'value' => $meeting_id
                        )
                    ),
                    'posts_per_page' => -1
                ));
                
                $members = get_posts(array(
                    'post_type' => 'haklai_member',
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_cell_id',
                            'value' => $cell_id
                        )
                    ),
                    'posts_per_page' => -1
                ));
                
                $total_members = count($members);
                $present_count = 0;
                
                foreach ($attendances as $attendance) {
                    if (get_post_meta($attendance->ID, '_haklai_is_present', true) === '1') {
                        $present_count++;
                    }
                }
                
                $absent_count = $total_members - $present_count;
                $presence_rate = $total_members > 0 ? round(($present_count / $total_members) * 100, 1) : 0;
                $cell_name = $cell ? $cell->post_title : 'N/A';
                $meeting_date = get_post_meta($meeting_id, '_haklai_meeting_date', true) ?: $meeting->post_date;
            } else {
                // Dados encontrados na tabela MySQL
                $total_members = intval($report_data->total_members_in_cell);
                $present_count = intval($report_data->total_present);
                $absent_count = intval($report_data->total_absent);
                $presence_rate = floatval($report_data->attendance_rate);
                $members = $report_data->members ?? array();
                $cell_name = $report_data->cell_name ?? 'N/A';
                $meeting_date = $report_data->meeting_date;
                $attendances = array(); // Inicializa para compatibilidade no loop de membros
            }
        } else {
            // Fallback para CPT (compatibilidade)
            $meeting = get_post($meeting_id);
            $cell_id = get_post_meta($meeting_id, '_haklai_cell_id', true);
            $cell = get_post($cell_id);
            
            $attendances = get_posts(array(
                'post_type' => 'haklai_attendance',
                'meta_query' => array(
                    array(
                        'key' => '_haklai_meeting_id',
                        'value' => $meeting_id
                    )
                ),
                'posts_per_page' => -1
            ));
            
            $members = get_posts(array(
                'post_type' => 'haklai_member',
                'meta_query' => array(
                    array(
                        'key' => '_haklai_cell_id',
                        'value' => $cell_id
                    )
                ),
                'posts_per_page' => -1
            ));
            
            $total_members = count($members);
            $present_count = 0;
            
            foreach ($attendances as $attendance) {
                if (get_post_meta($attendance->ID, '_haklai_is_present', true) === '1') {
                    $present_count++;
                }
            }
            
            $absent_count = $total_members - $present_count;
            $presence_rate = $total_members > 0 ? round(($present_count / $total_members) * 100, 1) : 0;
            $cell_name = $cell ? $cell->post_title : 'N/A';
            $meeting_date = $meeting->post_date;
        }
        
        // Gera HTML do relatório
        ob_start();
        ?>
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Relatório de Reunião - <?php echo esc_html($cell_name); ?></title>
            <style>
                body { font-family: Arial, sans-serif; margin: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .stats { display: flex; justify-content: space-around; margin: 20px 0; }
                .stat-item { text-align: center; }
                .stat-number { font-size: 24px; font-weight: bold; }
                .members-list { margin-top: 30px; }
                .member-item { padding: 10px; border-bottom: 1px solid #ccc; }
                .present { color: green; }
                .absent { color: red; }
                @media print { body { margin: 0; } }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Relatório de Reunião</h1>
                <h2><?php echo esc_html($cell_name); ?></h2>
                <p>Data: <?php echo date('d/m/Y', strtotime($meeting_date)); ?></p>
            </div>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number"><?php echo $total_members; ?></div>
                    <div>Total de Membros</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number present"><?php echo $present_count; ?></div>
                    <div>Presentes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number absent"><?php echo $absent_count; ?></div>
                    <div>Ausentes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number"><?php echo number_format($presence_rate, 1); ?>%</div>
                    <div>Taxa de Presença</div>
                </div>
            </div>
            
            <div class="members-list">
                <h3>Lista de Membros</h3>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <?php
                        // Compatibilidade: verifica se é objeto de tabela ou CPT
                        if (is_object($member) && isset($member->is_present)) {
                            // Formato de tabela MySQL
                            $is_present = (bool) $member->is_present;
                            $member_name = $member->name;
                        } else {
                            // Fallback CPT
                            $is_present = false;
                            $member_id = is_object($member) ? $member->ID : $member['id'];
                            $member_name = is_object($member) ? $member->post_title : $member['name'];
                            
                            if (isset($attendances)) {
                                foreach ($attendances as $attendance) {
                                    $att_member_id = get_post_meta($attendance->ID, '_haklai_member_id', true);
                                    if ($att_member_id == $member_id && get_post_meta($attendance->ID, '_haklai_is_present', true) === '1') {
                                        $is_present = true;
                                        break;
                                    }
                                }
                            }
                        }
                        ?>
                        <div class="member-item">
                            <strong><?php echo esc_html($member_name); ?></strong>
                            <span class="<?php echo $is_present ? 'present' : 'absent'; ?>">
                                - <?php echo $is_present ? 'Presente' : 'Ausente'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Nenhum membro registrado nesta reunião.</p>
                <?php endif; ?>
            </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Obtém lista de membros (OTIMIZADO)
     */
    private function get_members($data) {
        // Usa tabelas otimizadas se existirem
        if (Haklai_DB::tables_exist()) {
            $db_args = array(
                'cell_id' => !empty($data['cell_id']) ? intval($data['cell_id']) : null,
                'status' => !empty($data['status']) ? sanitize_text_field($data['status']) : 'active',
                'limit' => intval($data['limit'] ?? 50),
                'offset' => intval($data['offset'] ?? 0),
            );
            
            $members = Haklai_DB::get_members($db_args);
            
            $formatted_members = array();
            foreach ($members as $member) {
                $formatted_members[] = array(
                    'id' => intval($member->id),
                    'name' => $member->name,
                    'email' => $member->email,
                    'phone' => $member->phone,
                    'photo' => $member->photo_url,
                    'skill' => $member->skill,
                    'role_level' => intval($member->role_level),
                    'baptism_status' => $member->baptism_status,
                    'status' => $member->status,
                    'cell_id' => intval($member->cell_id)
                );
            }
            
            return array(
                'success' => true,
                'data' => $formatted_members
            );
        }
        
        // Fallback para CPT (compatibilidade)
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => intval($data['limit'] ?? 50),
            'meta_query' => array()
        );
        
        if (!empty($data['cell_id'])) {
            $args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => intval($data['cell_id']),
                'compare' => '='
            );
        }
        
        if (!empty($data['status'])) {
            $args['meta_query'][] = array(
                'key' => '_haklai_status',
                'value' => sanitize_text_field($data['status']),
                'compare' => '='
            );
        }
        
        $members = get_posts($args);
        $formatted_members = array();
        
        foreach ($members as $member) {
            $formatted_members[] = array(
                'id' => $member->ID,
                'name' => $member->post_title,
                'email' => get_post_meta($member->ID, '_haklai_email', true),
                'phone' => get_post_meta($member->ID, '_haklai_phone', true),
                'photo' => get_post_meta($member->ID, '_haklai_photo', true),
                'skill' => get_post_meta($member->ID, '_haklai_skill', true),
                'role_level' => intval(get_post_meta($member->ID, '_haklai_role_level', true)),
                'baptism_status' => get_post_meta($member->ID, '_haklai_baptism_status', true),
                'status' => get_post_meta($member->ID, '_haklai_status', true),
                'cell_id' => intval(get_post_meta($member->ID, '_haklai_cell_id', true))
            );
        }
        
        return array(
            'success' => true,
            'data' => $formatted_members
        );
    }
    
    /**
     * Obtém estatísticas do sistema (OTIMIZADO)
     */
    private function get_statistics($data) {
        $cell_id = intval($data['cell_id'] ?? 0);
        
        // Usa tabelas otimizadas se existirem
        if (Haklai_DB::tables_exist()) {
            $filters = array();
            if ($cell_id) {
                $filters['cell_id'] = $cell_id;
            }
            
            $stats = Haklai_DB::get_statistics($filters);
            
            return array(
                'success' => true,
                'data' => array(
                    'total_members' => intval($stats->total_active_members ?? 0),
                    'total_cells' => intval($stats->total_active_cells ?? 0),
                    'total_meetings' => intval($stats->meetings_last_month ?? 0),
                    'avg_attendance_rate' => floatval($stats->avg_attendance_rate ?? 0),
                    'total_visitors' => intval($stats->total_visitors ?? 0),
                )
            );
        }
        
        // Fallback para CPT (compatibilidade)
        $members_args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        );
        
        if ($cell_id) {
            $members_args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => $cell_id,
                'compare' => '='
            );
        }
        
        $total_members = count(get_posts($members_args));
        
        $cells_args = array(
            'post_type' => 'haklai_cell',
            'post_status' => 'publish',
            'posts_per_page' => -1
        );
        
        $total_cells = count(get_posts($cells_args));
        
        $meetings_args = array(
            'post_type' => 'haklai_meeting',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'date_query' => array(
                array(
                    'after' => '1 month ago'
                )
            )
        );
        
        $total_meetings = count(get_posts($meetings_args));
        
        $baptisms_args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_baptism_status',
                    'value' => 'member',
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_baptism_date',
                    'value' => date('Y-m-d', strtotime('1 month ago')),
                    'compare' => '>='
                )
            )
        );
        
        $total_baptisms = count(get_posts($baptisms_args));
        
        return array(
            'success' => true,
            'data' => array(
                'total_members' => $total_members,
                'total_cells' => $total_cells,
                'total_meetings' => $total_meetings,
                'total_baptisms' => $total_baptisms
            )
        );
    }
    
    /**
     * Obtém histórico de presenças (últimas 6 reuniões)
     */
    private function get_attendance_history($data) {
        $member_id = intval($data['member_id'] ?? 0);
        $cell_id = intval($data['cell_id'] ?? 0);
        
        if (!$member_id && !$cell_id) {
            return array(
                'success' => false,
                'message' => __('ID do membro ou célula é obrigatório', 'haklai-app')
            );
        }
        
        // Obtém últimas 6 reuniões
        $meetings_args = array(
            'post_type' => 'haklai_meeting',
            'post_status' => 'publish',
            'posts_per_page' => 6,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array()
        );
        
        if ($cell_id) {
            $meetings_args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => $cell_id,
                'compare' => '='
            );
        }
        
        $meetings = get_posts($meetings_args);
        $history = array();
        
        foreach ($meetings as $meeting) {
            $attendance_args = array(
                'post_type' => 'haklai_attendance',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_meeting_id',
                        'value' => $meeting->ID,
                        'compare' => '='
                    )
                )
            );
            
            if ($member_id) {
                $attendance_args['meta_query'][] = array(
                    'key' => '_haklai_member_id',
                    'value' => $member_id,
                    'compare' => '='
                );
            }
            
            $attendance = get_posts($attendance_args);
            $is_present = !empty($attendance) && get_post_meta($attendance[0]->ID, '_haklai_is_present', true) === '1';
            
            $history[] = array(
                'meeting_id' => $meeting->ID,
                'meeting_date' => $meeting->post_date,
                'meeting_title' => $meeting->post_title,
                'is_present' => $is_present
            );
        }
        
        return array(
            'success' => true,
            'data' => $history
        );
    }
    
    /**
     * Gera relatório
     */
    private function generate_report($data) {
        $report_type = sanitize_text_field($data['report_type'] ?? '');
        $format = sanitize_text_field($data['format'] ?? 'pdf');
        $filters = $data['filters'] ?? array();
        
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_reports')) {
            return array(
                'success' => false,
                'message' => __('Permissão insuficiente', 'haklai-app')
            );
        }
        
        // Processa relatório de formações
        if ($report_type === 'formacoes') {
            return $this->generate_formacoes_report($format, $filters);
        }
        
        // Outros tipos de relatório podem ser adicionados aqui
        return array(
            'success' => false,
            'message' => __('Tipo de relatório não implementado', 'haklai-app')
        );
    }
    
    /**
     * Gera relatório de formações com porcentagens
     */
    private function generate_formacoes_report($format, $filters) {
        // Busca todos os membros ativos
        $args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        );
        
        // Aplica filtro de célula se especificado
        if (!empty($filters['cell_id'])) {
            $args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => intval($filters['cell_id']),
                'compare' => '='
            );
        }
        
        $members = get_posts($args);
        $total_members = count($members);
        
        // Contadores de formações
        $ctl_count = 0;
        $cme_count = 0;
        $stp_count = 0;
        $total_with_formations = 0;
        
        $formacoes_list = array(
            'ctl' => __('Curso de Treinamento de Líderes (CTL)', 'haklai-app'),
            'cme' => __('Curso de Maturidade Espiritual (CME)', 'haklai-app'),
            'stp' => __('Seminário Teológico Pastoral (STP)', 'haklai-app')
        );
        
        // Conta formações
        foreach ($members as $member) {
            $formacoes = get_post_meta($member->ID, '_haklai_formacoes', true);
            if (!is_array($formacoes)) {
                $formacoes = array();
            }
            
            if (!empty($formacoes)) {
                $total_with_formations++;
            }
            
            if (in_array('ctl', $formacoes)) {
                $ctl_count++;
            }
            if (in_array('cme', $formacoes)) {
                $cme_count++;
            }
            if (in_array('stp', $formacoes)) {
                $stp_count++;
            }
        }
        
        // Calcula porcentagens
        $ctl_percentage = $total_members > 0 ? round(($ctl_count / $total_members) * 100, 2) : 0;
        $cme_percentage = $total_members > 0 ? round(($cme_count / $total_members) * 100, 2) : 0;
        $stp_percentage = $total_members > 0 ? round(($stp_count / $total_members) * 100, 2) : 0;
        $with_formations_percentage = $total_members > 0 ? round(($total_with_formations / $total_members) * 100, 2) : 0;
        
        // Prepara dados do relatório
        $report_data = array(
            'total_members' => $total_members,
            'formations' => array(
                'ctl' => array(
                    'name' => $formacoes_list['ctl'],
                    'count' => $ctl_count,
                    'percentage' => $ctl_percentage
                ),
                'cme' => array(
                    'name' => $formacoes_list['cme'],
                    'count' => $cme_count,
                    'percentage' => $cme_percentage
                ),
                'stp' => array(
                    'name' => $formacoes_list['stp'],
                    'count' => $stp_count,
                    'percentage' => $stp_percentage
                )
            ),
            'summary' => array(
                'total_with_formations' => $total_with_formations,
                'percentage_with_formations' => $with_formations_percentage,
                'total_without_formations' => $total_members - $total_with_formations,
                'percentage_without_formations' => round((($total_members - $total_with_formations) / $total_members) * 100, 2)
            ),
            'generated_at' => current_time('mysql'),
            'generated_by' => get_current_user_id()
        );
        
        // Salva relatório
        $report_post = array(
            'post_type' => 'haklai_report',
            'post_title' => sprintf(__('Relatório de Formações - %s', 'haklai-app'), date_i18n('d/m/Y')),
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_report_type' => 'formacoes',
                '_haklai_report_data' => $report_data,
                '_haklai_report_format' => $format,
                '_haklai_report_date' => current_time('mysql'),
                '_haklai_created_by' => get_current_user_id()
            )
        );
        
        $report_id = wp_insert_post($report_post);
        
        if ($report_id) {
            return array(
                'success' => true,
                'message' => __('Relatório gerado com sucesso', 'haklai-app'),
                'data' => array(
                    'report_id' => $report_id,
                    'report_url' => get_permalink($report_id),
                    'report_data' => $report_data
                )
            );
        }
        
        return array(
            'success' => false,
            'message' => __('Erro ao gerar relatório', 'haklai-app')
        );
    }
    
    /**
     * Executa backup diário
     */
    public function run_daily_backup() {
        // Implementa sistema de backup
        $this->create_backup();
    }
    
    /**
     * Cria backup dos dados
     */
    private function create_backup() {
        $backup_dir = HAKLAI_UPLOAD_DIR . 'backups/';
        if (!file_exists($backup_dir)) {
            wp_mkdir_p($backup_dir);
        }
        
        $backup_file = $backup_dir . 'backup-' . date('Y-m-d-H-i-s') . '.json';
        
        // Coleta dados para backup
        $backup_data = array(
            'members' => get_posts(array(
                'post_type' => 'haklai_member',
                'post_status' => 'publish',
                'posts_per_page' => -1
            )),
            'cells' => get_posts(array(
                'post_type' => 'haklai_cell',
                'post_status' => 'publish',
                'posts_per_page' => -1
            )),
            'meetings' => get_posts(array(
                'post_type' => 'haklai_meeting',
                'post_status' => 'publish',
                'posts_per_page' => -1
            )),
            'attendance' => get_posts(array(
                'post_type' => 'haklai_attendance',
                'post_status' => 'publish',
                'posts_per_page' => -1
            )),
            'backup_date' => current_time('mysql'),
            'plugin_version' => HAKLAI_VERSION
        );
        
        // Salva backup
        file_put_contents($backup_file, json_encode($backup_data, JSON_PRETTY_PRINT));
        
        // Remove backups antigos (mantém apenas os últimos 30 dias)
        $this->cleanup_old_backups();
    }
    
    /**
     * Remove backups antigos
     */
    private function cleanup_old_backups() {
        $backup_dir = HAKLAI_UPLOAD_DIR . 'backups/';
        $files = glob($backup_dir . 'backup-*.json');
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-30 days')) {
                unlink($file);
            }
        }
    }
    
    /**
     * Limpa logs antigos
     */
    public function cleanup_old_logs() {
        $log_dir = HAKLAI_UPLOAD_DIR . 'logs/';
        $files = glob($log_dir . '*.log');
        
        foreach ($files as $file) {
            if (filemtime($file) < strtotime('-90 days')) {
                unlink($file);
            }
        }
    }
    
    /**
     * Verifica se precisa fazer upgrade
     */
    public function check_upgrade() {
        $current_version = get_option('haklai_version', '1.0.0');
        
        if (version_compare($current_version, $this->version, '<')) {
            $this->upgrade($current_version, $this->version);
            update_option('haklai_version', $this->version);
        }
    }
    
    /**
     * Executa upgrade do plugin
     */
    private function upgrade($from_version, $to_version) {
        // Aqui você implementaria a lógica de upgrade
        // Por exemplo: criar novos Custom Post Types, migrar dados, etc.
        
        // Log do upgrade
        error_log("Haklai App: Upgrade from {$from_version} to {$to_version}");
    }
    
    /**
     * Hook para verificar upgrade após atualização
     */
    public function upgrade_check($upgrader_object, $options) {
        if ($options['action'] === 'update' && $options['type'] === 'plugin') {
            if (isset($options['plugins']) && is_array($options['plugins'])) {
                foreach ($options['plugins'] as $plugin) {
                    if ($plugin === HAKLAI_PLUGIN_BASENAME) {
                        $this->check_upgrade();
                        break;
                    }
                }
            }
        }
    }
    
    /**
     * Limpeza de dados (para AJAX)
     */
    public function cleanup_data() {
        // Verifica permissões de administrador
        if (!current_user_can('manage_options')) {
            wp_die(__('Acesso negado', 'haklai-app'));
        }
        
        // Implementa lógica de limpeza
        // Por exemplo: logs antigos, dados temporários, etc.
        
        wp_send_json(array(
            'success' => true,
            'message' => __('Limpeza concluída', 'haklai-app')
        ));
    }
    
    /**
     * Intercepta visualização de relatórios
     * Processa URLs como /relatorio/?report_type=attendance&start_date=...
     */
    public function handle_report_view() {
        // Verifica se é uma requisição para visualizar relatório
        $report_type = sanitize_text_field($_GET['report_type'] ?? '');
        
        if (empty($report_type)) {
            return; // Não é uma requisição de relatório
        }
        
        // Verifica se o usuário está logado e tem permissão
        if (!is_user_logged_in()) {
            auth_redirect();
            return;
        }
        
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_reports')) {
            wp_die(__('Você não tem permissão para acessar este relatório.', 'haklai-app'));
        }
        
        // Carrega o template de visualização
        $template_path = HAKLAI_TEMPLATES_PATH . 'relatorio-view-template.php';
        
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        } else {
            wp_die(__('Template de relatório não encontrado.', 'haklai-app'));
        }
    }
    
    /**
     * Busca dados filtrados de relatórios (AJAX)
     */
    public function get_filtered_reports() {
        // Verifica nonce
        check_ajax_referer('haklai_nonce', 'nonce');
        
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_reports')) {
            wp_send_json_error(array('message' => __('Acesso negado', 'haklai-app')));
        }
        
        // Obtém parâmetros
        $filter_level = sanitize_text_field($_POST['filter_level'] ?? '');
        $filter_id = intval($_POST['filter_id'] ?? 0);
        $start_date = sanitize_text_field($_POST['start_date'] ?? '');
        $end_date = sanitize_text_field($_POST['end_date'] ?? '');
        
        // Valida datas
        if (empty($start_date) || empty($end_date)) {
            wp_send_json_error(array('message' => __('Datas inválidas', 'haklai-app')));
        }
        
        // Determina cell_ids baseado no nível hierárquico
        $cell_ids = null;
        
        if (!empty($filter_level) && !empty($filter_id)) {
            switch ($filter_level) {
                case 'leader':
                    // Líder de Célula (nível 1)
                    $role_level = 1;
                    $cell_ids = Haklai_DB::get_cells_by_hierarchy_level($filter_id, $role_level);
                    break;
                    
                case 'discipler':
                    // Discipulador (nível 2)
                    $role_level = 2;
                    $cell_ids = Haklai_DB::get_cells_by_hierarchy_level($filter_id, $role_level);
                    break;
                    
                case 'network_pastor':
                    // Pastor de Rede (nível 3)
                    $role_level = 3;
                    $cell_ids = Haklai_DB::get_cells_by_hierarchy_level($filter_id, $role_level);
                    break;
                    
                case 'senior_pastor':
                    // Pastor Senior (nível 4)
                    $role_level = 4;
                    $cell_ids = Haklai_DB::get_cells_by_hierarchy_level($filter_id, $role_level);
                    break;
                    
                case 'supervisor_pastor':
                    // Pastor Supervisor (nível 5) - todas as células
                    $role_level = 5;
                    $cell_ids = Haklai_DB::get_cells_by_hierarchy_level($filter_id, $role_level);
                    break;
                    
                case 'cell':
                    // Célula específica
                    $cell_table_id = Haklai_DB::get_table_id_from_cpt($filter_id, 'haklai_cell');
                    if ($cell_table_id) {
                        $cell_ids = array($cell_table_id);
                    }
                    break;
                    
                case 'network':
                    // Rede específica
                    $network_table_id = Haklai_DB::get_table_id_from_cpt($filter_id, 'haklai_network');
                    if ($network_table_id) {
                        $cells = Haklai_DB::get_cells(array('network_id' => $network_table_id, 'status' => 'active', 'limit' => 1000));
                        $cell_ids = array();
                        foreach ($cells as $cell) {
                            $cell_ids[] = intval($cell->id);
                        }
                    }
                    break;
            }
        }
        
        // Busca dados do relatório
        $reports = Haklai_DB::get_attendance_report($start_date, $end_date, null, $cell_ids);
        
        // Formata dados para resposta
        $formatted_reports = array();
        foreach ($reports as $report) {
            $formatted_reports[] = array(
                'meeting_id' => $report->meeting_id,
                'meeting_date' => $report->meeting_date,
                'cell_name' => $report->cell_name,
                'cell_id' => $report->cell_id,
                'present_count' => intval($report->present_count),
                'absent_count' => intval($report->absent_count),
                'total_members' => intval($report->total_members),
                'attendance_rate' => floatval($report->attendance_rate),
                'formatted_date' => date_i18n('d/m/Y', strtotime($report->meeting_date)),
            );
        }
        
        // Calcula estatísticas gerais
        $total_present = array_sum(array_column($formatted_reports, 'present_count'));
        $total_absent = array_sum(array_column($formatted_reports, 'absent_count'));
        $total_meetings = count($formatted_reports);
        $avg_attendance = $total_meetings > 0 ? array_sum(array_column($formatted_reports, 'attendance_rate')) / $total_meetings : 0;
        
        wp_send_json_success(array(
            'reports' => $formatted_reports,
            'stats' => array(
                'total_present' => $total_present,
                'total_absent' => $total_absent,
                'total_meetings' => $total_meetings,
                'avg_attendance' => round($avg_attendance, 2),
            ),
        ));
    }
    
    /**
     * Busca opções de filtro baseado no nível (AJAX)
     */
    private function get_filter_options($data) {
        // Verifica permissões
        if (!haklai_user_can_access('haklai_access_reports')) {
            return array(
                'success' => false,
                'message' => __('Acesso negado', 'haklai-app')
            );
        }
        
        $filter_level = sanitize_text_field($_POST['filter_level'] ?? $data['filter_level'] ?? '');
        
        if (empty($filter_level)) {
            return array(
                'success' => false,
                'message' => __('Nível de filtro não especificado', 'haklai-app')
            );
        }
        
        $options = array();
        
        switch ($filter_level) {
            case 'cell':
                // Busca células
                $cells = get_posts(array(
                    'post_type' => 'haklai_cell',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($cells as $cell) {
                    $options[] = array(
                        'id' => $cell->ID,
                        'name' => $cell->post_title
                    );
                }
                break;
                
            case 'leader':
                // Busca líderes de célula (role_level = 1)
                $leaders = get_posts(array(
                    'post_type' => 'haklai_member',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_role_level',
                            'value' => '1',
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($leaders as $leader) {
                    $options[] = array(
                        'id' => $leader->ID,
                        'name' => $leader->post_title
                    );
                }
                break;
                
            case 'discipler':
                // Busca discipuladores (role_level = 2)
                $discipuladores = get_posts(array(
                    'post_type' => 'haklai_member',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_role_level',
                            'value' => '2',
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($discipuladores as $discipulador) {
                    $options[] = array(
                        'id' => $discipulador->ID,
                        'name' => $discipulador->post_title
                    );
                }
                break;
                
            case 'network_pastor':
                // Busca pastores de rede (role_level = 3)
                $pastores = get_posts(array(
                    'post_type' => 'haklai_member',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_role_level',
                            'value' => '3',
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($pastores as $pastor) {
                    $options[] = array(
                        'id' => $pastor->ID,
                        'name' => $pastor->post_title
                    );
                }
                break;
                
            case 'senior_pastor':
                // Busca pastores senior (role_level = 4)
                $pastores = get_posts(array(
                    'post_type' => 'haklai_member',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_role_level',
                            'value' => '4',
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($pastores as $pastor) {
                    $options[] = array(
                        'id' => $pastor->ID,
                        'name' => $pastor->post_title
                    );
                }
                break;
                
            case 'supervisor_pastor':
                // Busca pastores supervisor (role_level = 5)
                $pastores = get_posts(array(
                    'post_type' => 'haklai_member',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'meta_query' => array(
                        array(
                            'key' => '_haklai_role_level',
                            'value' => '5',
                            'compare' => '='
                        )
                    ),
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($pastores as $pastor) {
                    $options[] = array(
                        'id' => $pastor->ID,
                        'name' => $pastor->post_title
                    );
                }
                break;
                
            case 'network':
                // Busca redes
                $networks = get_posts(array(
                    'post_type' => 'haklai_network',
                    'post_status' => 'publish',
                    'posts_per_page' => -1,
                    'orderby' => 'title',
                    'order' => 'ASC'
                ));
                foreach ($networks as $network) {
                    $options[] = array(
                        'id' => $network->ID,
                        'name' => $network->post_title
                    );
                }
                break;
        }
        
        return array(
            'success' => true,
            'data' => array(
                'options' => $options
            )
        );
    }
    
    /**
     * Retorna informações do plugin
     */
    public function get_plugin_info() {
        return array(
            'name' => 'Haklai Church Management System',
            'version' => $this->version,
            'author' => 'Ricardo Sarmento',
            'author_uri' => 'https://linx.pt',
            'plugin_uri' => 'https://linx.pt',
            'description' => __('Sistema completo de gerenciamento de igrejas e membros', 'haklai-app'),
        );
    }
}

/**
 * Função para obter a instância principal do plugin
 * 
 * @return Haklai_App
 */
function haklai_app() {
    return Haklai_App::get_instance();
}

// Inicializa o plugin
haklai_app();

