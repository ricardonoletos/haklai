<?php
/**
 * Gerenciador de Capabilities Customizadas
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Capabilities_Manager {
    
    /**
     * Roles do sistema
     */
    private $haklai_roles = [
        'haklai_pastor_supervisor' => [
            'name' => 'Pastor Supervisor',
            'level' => 5,
            'icon' => 'fa-crown'
        ],
        'haklai_pastor_senior' => [
            'name' => 'Pastor Senior',
            'level' => 4,
            'icon' => 'fa-user-tie'
        ],
        'haklai_pastor_rede' => [
            'name' => 'Pastor de Rede',
            'level' => 3,
            'icon' => 'fa-network-wired'
        ],
        'haklai_discipulador' => [
            'name' => 'Discipulador',
            'level' => 2,
            'icon' => 'fa-chalkboard-teacher'
        ],
        'haklai_lider_celula' => [
            'name' => 'Líder de Célula',
            'level' => 1,
            'icon' => 'fa-users'
        ]
    ];
    
    /**
     * Capabilities disponíveis
     */
    private $capabilities = [
        'haklai_access_dashboard' => 'Acesso ao Dashboard',
        'haklai_access_checkpoint' => 'Controle de Presença',
        'haklai_access_reports' => 'Ver Relatórios',
        'haklai_generate_reports' => 'Gerar Relatórios',
        'haklai_access_settings' => 'Acessar Configurações',
        'haklai_manage_members' => 'Gerenciar Membros',
        'haklai_manage_cells' => 'Gerenciar Células',
        'haklai_view_all_cells' => 'Ver Todas as Células',
        'haklai_export_data' => 'Exportar Dados'
    ];
    
    /**
     * Obtém configuração de capabilities
     */
    public function get_role_capabilities($role_slug) {
        $custom = get_option('haklai_custom_capabilities', []);
        
        if (isset($custom[$role_slug])) {
            return $custom[$role_slug];
        }
        
        return $this->get_default_capabilities($role_slug);
    }
    
    /**
     * Capabilities padrão por role
     */
    private function get_default_capabilities($role_slug) {
        $defaults = [
            'haklai_pastor_supervisor' => [
                'haklai_access_dashboard' => true,
                'haklai_access_checkpoint' => true,
                'haklai_access_reports' => true,
                'haklai_generate_reports' => true,
                'haklai_access_settings' => true,
                'haklai_manage_members' => true,
                'haklai_manage_cells' => true,
                'haklai_view_all_cells' => true,
                'haklai_export_data' => true
            ],
            'haklai_pastor_senior' => [
                'haklai_access_dashboard' => true,
                'haklai_access_checkpoint' => true,
                'haklai_access_reports' => true,
                'haklai_generate_reports' => true,
                'haklai_access_settings' => false,
                'haklai_manage_members' => true,
                'haklai_manage_cells' => true,
                'haklai_view_all_cells' => true,
                'haklai_export_data' => true
            ],
            'haklai_pastor_rede' => [
                'haklai_access_dashboard' => true,
                'haklai_access_checkpoint' => true,
                'haklai_access_reports' => true,
                'haklai_generate_reports' => true,
                'haklai_access_settings' => false,
                'haklai_manage_members' => true,
                'haklai_manage_cells' => true,
                'haklai_view_all_cells' => true,
                'haklai_export_data' => false
            ],
            'haklai_discipulador' => [
                'haklai_access_dashboard' => true,
                'haklai_access_checkpoint' => true,
                'haklai_access_reports' => true,
                'haklai_generate_reports' => false,
                'haklai_access_settings' => false,
                'haklai_manage_members' => true,
                'haklai_manage_cells' => false,
                'haklai_view_all_cells' => true,
                'haklai_export_data' => false
            ],
            'haklai_lider_celula' => [
                'haklai_access_dashboard' => true,
                'haklai_access_checkpoint' => true,
                'haklai_access_reports' => false,
                'haklai_generate_reports' => false,
                'haklai_access_settings' => false,
                'haklai_manage_members' => true,
                'haklai_manage_cells' => false,
                'haklai_view_all_cells' => false,
                'haklai_export_data' => false
            ]
        ];
        
        return $defaults[$role_slug] ?? [];
    }
    
    /**
     * Salva capabilities customizadas
     */
    public function save_role_capabilities($role_slug, $capabilities) {
        $custom = get_option('haklai_custom_capabilities', []);
        $custom[$role_slug] = $capabilities;
        
        update_option('haklai_custom_capabilities', $custom);
        
        // Atualiza role do WordPress
        $role = get_role($role_slug);
        if ($role) {
            foreach ($this->capabilities as $cap => $label) {
                if (isset($capabilities[$cap]) && $capabilities[$cap]) {
                    $role->add_cap($cap);
                } else {
                    $role->remove_cap($cap);
                }
            }
        }
        
        return true;
    }
    
    /**
     * Restaura capabilities padrão
     */
    public function restore_defaults($role_slug) {
        $custom = get_option('haklai_custom_capabilities', []);
        unset($custom[$role_slug]);
        update_option('haklai_custom_capabilities', $custom);
        
        // Restaura no role
        $defaults = $this->get_default_capabilities($role_slug);
        $this->save_role_capabilities($role_slug, $defaults);
        
        return true;
    }
    
    /**
     * Obtém todas as roles
     */
    public function get_all_roles() {
        return $this->haklai_roles;
    }
    
    /**
     * Obtém todas as capabilities
     */
    public function get_all_capabilities() {
        return $this->capabilities;
    }
}

