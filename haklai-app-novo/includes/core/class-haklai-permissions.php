<?php
/**
 * Classe responsável pelo sistema de permissões do plugin
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
 * Classe Haklai_Permissions
 */
class Haklai_Permissions {
    
    /**
     * Construtor
     */
    public function __construct() {
        add_action('init', array($this, 'init'));
    }
    
    /**
     * Inicializa o sistema de permissões
     */
    public function init() {
        // Cria capabilities se necessário
        if (!get_option('haklai_capabilities_created')) {
            $this->create_capabilities();
            update_option('haklai_capabilities_created', true);
        }
    }
    
    /**
     * Cria capabilities personalizadas
     */
    public function create_capabilities() {
        $capabilities = array(
            'haklai_access_dashboard' => __('Acessar Dashboard', 'haklai-app'),
            'haklai_access_settings' => __('Acessar Configurações', 'haklai-app'),
            'haklai_access_reports' => __('Acessar Relatórios', 'haklai-app'),
            'haklai_access_checkpoint' => __('Acessar Checkpoint', 'haklai-app'),
            'haklai_manage_members' => __('Gerenciar Membros', 'haklai-app'),
            'haklai_manage_cells' => __('Gerenciar Células', 'haklai-app'),
            'haklai_manage_users' => __('Gerenciar Usuários', 'haklai-app'),
            'haklai_view_all_data' => __('Visualizar Todos os Dados', 'haklai-app'),
        );
        
        // Adiciona capabilities aos roles apropriados
        $this->assign_capabilities_to_roles($capabilities);
    }
    
    /**
     * Atribui capabilities aos roles
     */
    private function assign_capabilities_to_roles($capabilities) {
        $role_capabilities = array(
            'administrator' => array_keys($capabilities),
            'haklai_pastor_supervisor' => array_keys($capabilities),
            'haklai_pastor_senior' => array(
                'haklai_access_dashboard',
                'haklai_access_reports',
                'haklai_manage_members',
                'haklai_manage_cells',
                'haklai_view_all_data',
            ),
            'haklai_pastor_rede' => array(
                'haklai_access_dashboard',
                'haklai_access_reports',
                'haklai_manage_members',
                'haklai_manage_cells',
            ),
            'haklai_discipulador' => array(
                'haklai_access_dashboard',
                'haklai_access_reports',
                'haklai_manage_members',
                'haklai_manage_cells',
            ),
            'haklai_lider_celula' => array(
                'haklai_access_dashboard',
                'haklai_access_checkpoint',
                'haklai_manage_members',
            ),
            'editor' => array(
                'haklai_access_dashboard',
                'haklai_access_reports',
                'haklai_manage_members',
                'haklai_manage_cells',
            ),
        );
        
        foreach ($role_capabilities as $role_name => $role_caps) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($role_caps as $cap) {
                    $role->add_cap($cap);
                }
            }
        }
    }
    
    /**
     * Verifica se o usuário tem acesso a uma funcionalidade
     * 
     * @param string $capability Capability a ser verificada
     * @param int $user_id ID do usuário (opcional)
     * @return bool
     */
    public static function user_can_access($capability, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Administradores sempre têm acesso
        if (user_can($user_id, 'manage_options')) {
            return true;
        }
        
        // Verifica capability específica
        return user_can($user_id, $capability);
    }
    
    /**
     * Obtém o nível hierárquico do usuário
     * 
     * @param int $user_id ID do usuário (opcional)
     * @return int
     */
    public static function get_user_level($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Verifica roles personalizados primeiro
        $user = get_userdata($user_id);
        if (!$user) {
            return 0;
        }
        
        $roles = $user->roles;
        
        // Mapeia roles para níveis
        $level_map = array(
            'haklai_pastor_supervisor' => 5,
            'haklai_pastor_senior' => 4,
            'haklai_pastor_rede' => 3,
            'haklai_discipulador' => 2,
            'haklai_lider_celula' => 1,
            'administrator' => 5,
            'editor' => 3,
            'author' => 2,
            'contributor' => 1,
            'subscriber' => 0,
        );
        
        $max_level = 0;
        foreach ($roles as $role) {
            if (isset($level_map[$role]) && $level_map[$role] > $max_level) {
                $max_level = $level_map[$role];
            }
        }
        
        return $max_level;
    }
    
    /**
     * Obtém células do usuário baseado no seu nível
     * 
     * @param int $user_id ID do usuário (opcional)
     * @return array
     */
    public static function get_user_cells($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user_level = self::get_user_level($user_id);
        
        // Pastores Supervisor e Senior veem todas as células
        if ($user_level >= 4) {
            return get_posts(array(
                'post_type' => 'haklai_cell',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_status',
                        'value' => 'active',
                        'compare' => '='
                    )
                )
            ));
        }
        
        // Pastores de Rede veem células de suas redes
        if ($user_level == 3) {
            // Implementar lógica de rede quando necessário
            return get_posts(array(
                'post_type' => 'haklai_cell',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_status',
                        'value' => 'active',
                        'compare' => '='
                    )
                )
            ));
        }
        
        // Discipuladores veem células que supervisionam
        if ($user_level == 2) {
            return get_posts(array(
                'post_type' => 'haklai_cell',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_discipulador_id',
                        'value' => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => '_haklai_status',
                        'value' => 'active',
                        'compare' => '='
                    )
                )
            ));
        }
        
        // Líderes de Célula veem apenas suas células
        if ($user_level == 1) {
            // Busca células onde o usuário é líder
            $user_members = get_posts(array(
                'post_type' => 'haklai_member',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_user_id',
                        'value' => $user_id,
                        'compare' => '='
                    ),
                    array(
                        'key' => '_haklai_role_level',
                        'value' => '1',
                        'compare' => '>='
                    )
                )
            ));
            
            $cell_ids = array();
            foreach ($user_members as $member) {
                $cell_id = get_post_meta($member->ID, '_haklai_cell_id', true);
                if ($cell_id) {
                    $cell_ids[] = $cell_id;
                }
            }
            
            if (empty($cell_ids)) {
                return array();
            }
            
            return get_posts(array(
                'post_type' => 'haklai_cell',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'post__in' => $cell_ids,
                'meta_query' => array(
                    array(
                        'key' => '_haklai_status',
                        'value' => 'active',
                        'compare' => '='
                    )
                )
            ));
        }
        
        // Membros (nível 0) não têm acesso
        return array();
    }
    
    /**
     * Verifica se o usuário pode gerenciar múltiplas células
     * 
     * @param int $user_id ID do usuário (opcional)
     * @return bool
     */
    public static function can_manage_multiple_cells($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user_level = self::get_user_level($user_id);
        
        // Líderes de célula (nível 1) gerenciam múltiplas células
        // Discipuladores (nível 2) e superiores também
        return $user_level >= 1;
    }
    
    /**
     * Verifica se o usuário tem acesso a todas as células
     * 
     * @param int $user_id ID do usuário (opcional)
     * @return bool
     */
    public static function can_access_all_cells($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user_level = self::get_user_level($user_id);
        
        // Apenas Pastores Senior (nível 4) e Supervisor (nível 5)
        return $user_level >= 4;
    }
    
    /**
     * Obtém nome do role baseado no nível
     * 
     * @param int $level Nível hierárquico
     * @return string
     */
    public static function get_role_name($level) {
        $role_names = array(
            5 => __('Pastor Supervisor', 'haklai-app'),
            4 => __('Pastor Senior', 'haklai-app'),
            3 => __('Pastor de Rede', 'haklai-app'),
            2 => __('Discipulador', 'haklai-app'),
            1 => __('Líder de Célula', 'haklai-app'),
            0 => __('Membro', 'haklai-app'),
        );
        
        return $role_names[$level] ?? __('Membro', 'haklai-app');
    }
    
    /**
     * Verifica se o usuário pode acessar dados de uma célula específica
     * 
     * @param int $cell_id ID da célula
     * @param int $user_id ID do usuário (opcional)
     * @return bool
     */
    public static function can_access_cell($cell_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Administradores e Pastores Senior/Supervisor podem acessar todas
        if (self::can_access_all_cells($user_id)) {
            return true;
        }
        
        // Verifica se o usuário tem acesso a esta célula específica
        $user_cells = self::get_user_cells($user_id);
        foreach ($user_cells as $cell) {
            if ($cell->ID == $cell_id) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Verifica se o usuário pode acessar dados de um membro específico
     * 
     * @param int $member_id ID do membro
     * @param int $user_id ID do usuário (opcional)
     * @return bool
     */
    public static function can_access_member($member_id, $user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        // Administradores e Pastores Senior/Supervisor podem acessar todos
        if (self::can_access_all_cells($user_id)) {
            return true;
        }
        
        // Obtém a célula do membro
        $member_cell_id = get_post_meta($member_id, '_haklai_cell_id', true);
        
        // Verifica se o usuário pode acessar a célula do membro
        return self::can_access_cell($member_cell_id, $user_id);
    }
    
    /**
     * Obtém filtros de acesso baseados no nível do usuário
     * 
     * @param int $user_id ID do usuário (opcional)
     * @return array
     */
    public static function get_access_filters($user_id = null) {
        if (!$user_id) {
            $user_id = get_current_user_id();
        }
        
        $user_level = self::get_user_level($user_id);
        $filters = array();
        
        // Pastores Senior e Supervisor não têm filtros
        if ($user_level >= 4) {
            return $filters;
        }
        
        // Outros níveis têm filtros baseados nas células que podem acessar
        $user_cells = self::get_user_cells($user_id);
        if (!empty($user_cells)) {
            $cell_ids = array();
            foreach ($user_cells as $cell) {
                $cell_ids[] = $cell->ID;
            }
            
            $filters['cell_ids'] = $cell_ids;
        }
        
        return $filters;
    }
    
    /**
     * Aplica filtros de acesso a uma query
     * 
     * @param array $query_args Argumentos da query
     * @param int $user_id ID do usuário (opcional)
     * @return array
     */
    public static function apply_access_filters($query_args, $user_id = null) {
        $filters = self::get_access_filters($user_id);
        
        if (empty($filters)) {
            return $query_args;
        }
        
        // Aplica filtros de células se existirem
        if (isset($filters['cell_ids'])) {
            if (!isset($query_args['meta_query'])) {
                $query_args['meta_query'] = array();
            }
            
            $query_args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => $filters['cell_ids'],
                'compare' => 'IN'
            );
        }
        
        return $query_args;
    }
}

