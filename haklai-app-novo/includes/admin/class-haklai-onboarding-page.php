<?php
/**
 * Página de Onboarding do Plugin Haklai
 * Fluxo de registro inicial para Tenant Owner (SaaS)
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Onboarding_Page {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Registra menu com prioridade 15 (antes de outros submenus)
        add_action('admin_menu', [$this, 'add_onboarding_page'], 15);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        
        // AJAX Handler para registro de tenant
        add_action('wp_ajax_haklai_register_tenant', [$this, 'ajax_register_tenant']);
    }
    
    /**
     * Adiciona página de onboarding no menu
     * FASE 5: Garante que seja o único submenu quando não há tenant
     * SEGURANÇA: Apenas Super Admin pode criar tenant
     */
    public function add_onboarding_page() {
        // SEGURANÇA: Só permite onboarding para Super Admin (administrator)
        if (!current_user_can('manage_options')) {
            // Não é Super Admin, não mostra menu de onboarding
            return;
        }
        
        // Verifica se usuário já tem tenant associado
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            // Se já tem tenant, não mostra página de onboarding
            if ($current_tenant_id) {
                return;
            }
        }
        
        // FASE 5: Remove o submenu padrão "Haklai" (duplicado) quando não tem tenant
        // Isso garante que "Configuração Inicial" seja o único submenu visível
        global $submenu;
        if (isset($submenu['haklai'])) {
            // Remove todos os submenus existentes que não sejam o onboarding
            foreach ($submenu['haklai'] as $key => $item) {
                // Remove apenas o submenu duplicado "Haklai" (que tem mesmo slug do menu principal)
                if (isset($item[2]) && $item[2] === 'haklai' && $key > 0) {
                    unset($submenu['haklai'][$key]);
                }
            }
        }
        
        // Adiciona submenu de onboarding (só aparece se não tiver tenant)
        add_submenu_page(
            'haklai',
            __('Configuração Inicial', 'haklai-app'),
            __('Configuração Inicial', 'haklai-app'),
            'haklai_access_dashboard',
            'haklai-onboarding',
            [$this, 'render_onboarding_page']
        );
    }
    
    /**
     * Carrega scripts e estilos apenas na página de onboarding
     */
    public function enqueue_scripts($hook) {
        // Verifica se está na página de onboarding
        if ($hook !== 'haklai_page_haklai-onboarding') {
            return;
        }
        
        // CSS da página
        wp_enqueue_style(
            'haklai-onboarding',
            HAKLAI_PLUGIN_URL . 'assets/css/haklai-onboarding.css',
            [],
            HAKLAI_VERSION
        );
        
        // JavaScript da página
        wp_enqueue_script(
            'haklai-onboarding',
            HAKLAI_PLUGIN_URL . 'assets/js/haklai-onboarding.js',
            ['jquery'],
            HAKLAI_VERSION,
            true
        );
        
        // Localiza script com dados necessários
        wp_localize_script('haklai-onboarding', 'haklaiOnboarding', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('haklai_onboarding_nonce'),
            'i18n' => [
                'loading' => __('Processando...', 'haklai-app'),
                'success' => __('Registro concluído com sucesso!', 'haklai-app'),
                'error' => __('Erro ao processar registro. Tente novamente.', 'haklai-app'),
                'required_fields' => __('Por favor, preencha todos os campos obrigatórios.', 'haklai-app'),
            ]
        ]);
    }
    
    /**
     * Renderiza página de onboarding
     * SEGURANÇA: Apenas Super Admin pode acessar
     */
    public function render_onboarding_page() {
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_die(__('Acesso negado. Apenas administradores podem criar tenants.', 'haklai-app'));
        }
        
        // Verifica permissões básicas
        if (!current_user_can('haklai_access_dashboard')) {
            wp_die(__('Acesso negado', 'haklai-app'));
        }
        
        // Verifica se já tem tenant (proteção adicional)
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            if ($current_tenant_id) {
                // Já tem tenant, redireciona para dashboard
                wp_redirect(admin_url('admin.php?page=haklai'));
                exit;
            }
        }
        
        // Inclui o template
        include HAKLAI_TEMPLATES_PATH . 'admin-onboarding-page.php';
    }
    
    /**
     * Handler AJAX para registro de tenant
     * SEGURANÇA: Apenas Super Admin pode criar tenant
     */
    public function ajax_register_tenant() {
        // Verifica nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'haklai_onboarding_nonce')) {
            wp_send_json_error([
                'message' => __('Erro de segurança. Recarregue a página e tente novamente.', 'haklai-app')
            ]);
        }
        
        // SEGURANÇA: Verifica se é Super Admin
        if (!current_user_can('manage_options')) {
            wp_send_json_error([
                'message' => __('Apenas administradores podem criar tenants. Entre em contato com o administrador do sistema.', 'haklai-app')
            ]);
        }
        
        // Verifica permissões básicas
        if (!current_user_can('haklai_access_dashboard')) {
            wp_send_json_error([
                'message' => __('Acesso negado.', 'haklai-app')
            ]);
        }
        
        // Valida campos obrigatórios
        $supervisor_name = isset($_POST['supervisor_name']) ? sanitize_text_field($_POST['supervisor_name']) : '';
        $church_name = isset($_POST['church_name']) ? sanitize_text_field($_POST['church_name']) : '';
        $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : '';
        $contact = isset($_POST['contact']) ? sanitize_text_field($_POST['contact']) : '';
        
        if (empty($supervisor_name) || empty($church_name) || empty($location) || empty($contact)) {
            wp_send_json_error([
                'message' => __('Por favor, preencha todos os campos obrigatórios.', 'haklai-app')
            ]);
        }
        
        // Verifica se classes necessárias existem
        if (!class_exists('TenantRepository') || !class_exists('Haklai_Tenant_Manager')) {
            wp_send_json_error([
                'message' => __('Erro interno. Classes necessárias não encontradas.', 'haklai-app')
            ]);
        }
        
        try {
            $tenant_repository = new TenantRepository();
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            
            // Verifica se usuário já tem tenant (proteção adicional)
            $existing_tenant_id = $tenant_manager->get_current_tenant_id();
            if ($existing_tenant_id) {
                wp_send_json_error([
                    'message' => __('Você já possui um tenant associado.', 'haklai-app')
                ]);
            }
            
            // Cria tenant
            $tenant_id = $tenant_repository->create([
                'name' => $church_name,
                'code' => '', // Será gerado automaticamente
                'location' => $location,
                'supervisor_contact' => $contact,
                'supervisor_pastor_id' => get_current_user_id(),
                'is_active' => 1
            ]);
            
            if (!$tenant_id) {
                wp_send_json_error([
                    'message' => __('Erro ao criar tenant. Verifique os logs para mais detalhes.', 'haklai-app')
                ]);
            }
            
            // Associa usuário ao tenant
            if (!$tenant_repository->setCurrentTenant($tenant_id, get_current_user_id())) {
                error_log("Haklai Onboarding: Erro ao associar usuário ao tenant ID: {$tenant_id}");
                // Não falha completamente, mas registra erro
            }
            
            // Atribui role de Pastor Supervisor
            $user = wp_get_current_user();
            if ($user && !in_array('haklai_pastor_supervisor', $user->roles)) {
                $user->add_role('haklai_pastor_supervisor');
                
                // Log da atribuição
                error_log("Haklai Onboarding: Role 'haklai_pastor_supervisor' atribuída ao usuário ID: {$user->ID}");
            }
            
            // Limpa cache do tenant manager
            $tenant_manager->clear_cache();
            
            // Log de sucesso
            error_log("Haklai Onboarding: Tenant criado com sucesso. ID: {$tenant_id}, Usuário: {$user->ID}");
            
            // Retorna sucesso com URL de redirecionamento
            wp_send_json_success([
                'message' => __('Registro concluído com sucesso! Redirecionando...', 'haklai-app'),
                'redirect_url' => admin_url('admin.php?page=haklai')
            ]);
            
        } catch (Exception $e) {
            error_log("Haklai Onboarding: Erro ao processar registro - " . $e->getMessage());
            wp_send_json_error([
                'message' => __('Erro ao processar registro. Tente novamente.', 'haklai-app')
            ]);
        }
    }
}

