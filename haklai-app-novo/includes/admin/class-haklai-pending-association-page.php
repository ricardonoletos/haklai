<?php
/**
 * Página "Aguardando Associação" do Plugin Haklai
 * Exibida para usuários sem tenant que não são Super Admin
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Pending_Association_Page {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Registra menu com prioridade 16 (após onboarding)
        add_action('admin_menu', [$this, 'add_pending_page'], 16);
    }
    
    /**
     * Adiciona página "Aguardando Associação" no menu
     * Só aparece para usuários sem tenant que NÃO são Super Admin
     */
    public function add_pending_page() {
        // Verifica se usuário tem tenant
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            // Se já tem tenant, não mostra página
            if ($current_tenant_id) {
                return;
            }
        }
        
        // Só mostra se NÃO for Super Admin (Super Admin vê onboarding)
        if (current_user_can('manage_options')) {
            return; // Super Admin vê onboarding, não esta página
        }
        
        // Remove submenu padrão "Haklai" (duplicado) quando não tem tenant
        global $submenu;
        if (isset($submenu['haklai'])) {
            foreach ($submenu['haklai'] as $key => $item) {
                if (isset($item[2]) && $item[2] === 'haklai' && $key > 0) {
                    unset($submenu['haklai'][$key]);
                }
            }
        }
        
        // Adiciona submenu "Aguardando Associação"
        add_submenu_page(
            'haklai',
            __('Aguardando Associação', 'haklai-app'),
            __('Aguardando Associação', 'haklai-app'),
            'haklai_access_dashboard',
            'haklai-pending-association',
            [$this, 'render_pending_page']
        );
    }
    
    /**
     * Renderiza página "Aguardando Associação"
     */
    public function render_pending_page() {
        // Verifica permissões
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
        
        // Obtém informações do administrador do sistema
        $admin_users = get_users([
            'role' => 'administrator',
            'number' => 1,
            'orderby' => 'ID',
            'order' => 'ASC'
        ]);
        
        $admin_email = '';
        $admin_name = '';
        if (!empty($admin_users)) {
            $admin = $admin_users[0];
            $admin_email = $admin->user_email;
            $admin_name = $admin->display_name ? $admin->display_name : $admin->user_login;
        }
        
        // Inclui o template
        include HAKLAI_TEMPLATES_PATH . 'admin-pending-association-page.php';
    }
}

