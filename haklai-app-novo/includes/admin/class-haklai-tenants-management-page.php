<?php
/**
 * Página de Gerenciamento de Tenants do Plugin Haklai
 * Apenas Super Admin pode acessar
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Tenants_Management_Page {
    
    /**
     * Construtor
     * IMPORTANTE: Não verifica permissões aqui, pois o sistema de usuários ainda não está inicializado.
     * A verificação será feita dentro dos métodos chamados pelos hooks.
     */
    public function __construct() {
        // Sempre registra os hooks (a verificação de permissões será feita dentro dos métodos)
        add_action('admin_menu', [$this, 'add_tenants_page'], 20);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX handlers (sempre registra, verificação dentro do método)
        add_action('wp_ajax_haklai_associate_user_to_tenant', [$this, 'ajax_associate_user']);
        add_action('wp_ajax_haklai_create_tenant_admin', [$this, 'ajax_create_tenant']);
        add_action('wp_ajax_haklai_get_users_without_tenant', [$this, 'ajax_get_users_without_tenant']);
    }
    
    /**
     * Adiciona página de gerenciamento de tenants no menu
     * SEGURANÇA: Verifica se é Super Admin (agora dentro do hook, quando o sistema de usuários já está inicializado)
     */
    public function add_tenants_page() {
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            return;
        }
        
        add_submenu_page(
            'haklai',
            __('Gerenciar Tenants', 'haklai-app'),
            __('Gerenciar Tenants', 'haklai-app'),
            'manage_options',
            'haklai-tenants',
            [$this, 'render_tenants_page']
        );
    }
    
    /**
     * Carrega scripts e estilos apenas na página de tenants
     */
    public function enqueue_scripts($hook) {
        // Verifica se está na página de tenants
        if ($hook !== 'haklai_page_haklai-tenants') {
            return;
        }
        
        // CSS da página
        wp_enqueue_style(
            'haklai-tenants-management',
            HAKLAI_PLUGIN_URL . 'assets/css/haklai-tenants-management.css',
            [],
            HAKLAI_VERSION
        );
        
        // JavaScript da página
        wp_enqueue_script(
            'haklai-tenants-management',
            HAKLAI_PLUGIN_URL . 'assets/js/haklai-tenants-management.js',
            ['jquery'],
            HAKLAI_VERSION,
            true
        );
        
        // Localiza script com dados necessários
        wp_localize_script('haklai-tenants-management', 'haklaiTenants', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('haklai_tenants_nonce'),
            'i18n' => [
                'loading' => __('Processando...', 'haklai-app'),
                'success' => __('Operação concluída com sucesso!', 'haklai-app'),
                'error' => __('Erro ao processar. Tente novamente.', 'haklai-app'),
                'confirm_associate' => __('Tem certeza que deseja associar "{user}" ao tenant "{tenant}"?', 'haklai-app'),
                'confirm_create' => __('Tem certeza que deseja criar este tenant?', 'haklai-app'),
            ]
        ]);
    }
    
    /**
     * Renderiza página de gerenciamento de tenants
     */
    public function render_tenants_page() {
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_die(__('Acesso negado. Apenas administradores podem gerenciar tenants.', 'haklai-app'));
        }
        
        // Verifica se classes necessárias existem
        if (!class_exists('TenantRepository')) {
            wp_die(__('Erro interno. Classes necessárias não encontradas.', 'haklai-app'));
        }
        
        $tenant_repository = new TenantRepository();
        
        // Lista todos os tenants
        $tenants = $tenant_repository->findAllActive();
        
        // Lista usuários sem tenant
        $users_without_tenant = $this->get_users_without_tenant();
        
        // Inclui o template
        include HAKLAI_TEMPLATES_PATH . 'admin-tenants-management-page.php';
    }
    
    /**
     * Obtém usuários sem tenant associado
     */
    private function get_users_without_tenant() {
        global $wpdb;
        
        $users = get_users([
            'role__in' => ['administrator', 'haklai_pastor_supervisor', 'haklai_pastor_senior', 
                          'haklai_pastor_rede', 'haklai_discipulador', 'haklai_lider_celula'],
            'number' => -1
        ]);
        
        $users_without_tenant = [];
        
        foreach ($users as $user) {
            $tenant_id = get_user_meta($user->ID, '_haklai_tenant_id', true);
            if (empty($tenant_id) || $tenant_id == 0) {
                $users_without_tenant[] = $user;
            }
        }
        
        return $users_without_tenant;
    }
    
    /**
     * Handler AJAX para associar usuário a tenant
     */
    public function ajax_associate_user() {
        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'haklai_tenants_nonce')) {
            wp_send_json_error([
                'message' => __('Erro de segurança. Recarregue a página e tente novamente.', 'haklai-app')
            ]);
        }
        
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Acesso negado. Apenas administradores podem associar usuários.', 'haklai-app')
            ]);
        }
        
        // Valida dados
        $user_id = isset($_POST['user_id']) ? intval($_POST['user_id']) : 0;
        $tenant_id = isset($_POST['tenant_id']) ? intval($_POST['tenant_id']) : 0;
        
        if (empty($user_id) || empty($tenant_id)) {
            wp_send_json_error([
                'message' => __('Dados inválidos.', 'haklai-app')
            ]);
        }
        
        // Verifica se classes necessárias existem
        if (!class_exists('TenantRepository')) {
            wp_send_json_error([
                'message' => __('Erro interno. Classes necessárias não encontradas.', 'haklai-app')
            ]);
        }
        
        try {
            $tenant_repository = new TenantRepository();
            
            // Associa usuário ao tenant
            if ($tenant_repository->setCurrentTenant($tenant_id, $user_id)) {
                // Limpa cache do tenant manager
                if (class_exists('Haklai_Tenant_Manager')) {
                    $tenant_manager = Haklai_Tenant_Manager::get_instance();
                    $tenant_manager->clear_cache();
                }
                
                wp_send_json_success([
                    'message' => __('Usuário associado ao tenant com sucesso!', 'haklai-app')
                ]);
            } else {
                wp_send_json_error([
                    'message' => __('Erro ao associar usuário ao tenant.', 'haklai-app')
                ]);
            }
            
        } catch (Exception $e) {
            error_log("Haklai Tenants Management: Erro ao associar usuário - " . $e->getMessage());
            wp_send_json_error([
                'message' => __('Erro ao processar. Tente novamente.', 'haklai-app')
            ]);
        }
    }
    
    /**
     * Handler AJAX para criar tenant (via gerenciamento)
     */
    public function ajax_create_tenant() {
        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'haklai_tenants_nonce')) {
            wp_send_json_error([
                'message' => __('Erro de segurança. Recarregue a página e tente novamente.', 'haklai-app')
            ]);
        }
        
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Acesso negado. Apenas administradores podem criar tenants.', 'haklai-app')
            ]);
        }
        
        // Valida campos obrigatórios
        $church_name = isset($_POST['church_name']) ? sanitize_text_field($_POST['church_name']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        $contact = isset($_POST['contact']) ? sanitize_text_field($_POST['contact']) : '';
        $supervisor_user_id = isset($_POST['supervisor_user_id']) ? intval($_POST['supervisor_user_id']) : 0;
        
        if (empty($church_name) || empty($location) || empty($contact)) {
            wp_send_json_error([
                'message' => __('Por favor, preencha todos os campos obrigatórios.', 'haklai-app')
            ]);
        }
        
        // Verifica se classes necessárias existem
        if (!class_exists('TenantRepository')) {
            wp_send_json_error([
                'message' => __('Erro interno. Classes necessárias não encontradas.', 'haklai-app')
            ]);
        }
        
        try {
            $tenant_repository = new TenantRepository();
            
            // Cria tenant
            $tenant_id = $tenant_repository->create([
                'name' => $church_name,
                'code' => '', // Será gerado automaticamente
                'location' => $location,
                'supervisor_contact' => $contact,
                'supervisor_pastor_id' => $supervisor_user_id > 0 ? $supervisor_user_id : get_current_user_id(),
                'is_active' => 1
            ]);
            
            if (!$tenant_id) {
                wp_send_json_error([
                    'message' => __('Erro ao criar tenant. Verifique os logs para mais detalhes.', 'haklai-app')
                ]);
            }
            
            // Se supervisor foi especificado, associa ao tenant
            if ($supervisor_user_id > 0) {
                $tenant_repository->setCurrentTenant($tenant_id, $supervisor_user_id);
                
                // Atribui role de Pastor Supervisor se não tiver
                $user = get_userdata($supervisor_user_id);
                if ($user && !in_array('haklai_pastor_supervisor', $user->roles)) {
                    $user->add_role('haklai_pastor_supervisor');
                }
            }
            
            // Limpa cache do tenant manager
            if (class_exists('Haklai_Tenant_Manager')) {
                $tenant_manager = Haklai_Tenant_Manager::get_instance();
                $tenant_manager->clear_cache();
            }
            
            wp_send_json_success([
                'message' => __('Tenant criado com sucesso!', 'haklai-app'),
                'tenant_id' => $tenant_id
            ]);
            
        } catch (Exception $e) {
            error_log("Haklai Tenants Management: Erro ao criar tenant - " . $e->getMessage());
            wp_send_json_error([
                'message' => __('Erro ao processar. Tente novamente.', 'haklai-app')
            ]);
        }
    }
    
    /**
     * Handler AJAX para obter usuários sem tenant
     */
    public function ajax_get_users_without_tenant() {
        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'haklai_tenants_nonce')) {
            wp_send_json_error([
                'message' => __('Erro de segurança.', 'haklai-app')
            ]);
        }
        
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Acesso negado.', 'haklai-app')
            ]);
        }
        
        $users = $this->get_users_without_tenant();
        $users_data = [];
        
        foreach ($users as $user) {
            $users_data[] = [
                'id' => $user->ID,
                'name' => $user->display_name ? $user->display_name : $user->user_login,
                'email' => $user->user_email,
                'role' => implode(', ', $user->roles)
            ];
        }
        
        wp_send_json_success([
            'users' => $users_data
        ]);
    }
}

