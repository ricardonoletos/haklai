<?php
/**
 * Classe responsável pelos shortcodes do plugin
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
 * Classe Haklai_Shortcodes
 */
class Haklai_Shortcodes {
    
    /**
     * Instância da classe de permissões
     * 
     * @var Haklai_Permissions
     */
    private $permissions;
    
    /**
     * Construtor
     */
    public function __construct() {
        $this->permissions = new Haklai_Permissions();
        $this->init_hooks();
    }
    
    /**
     * Inicializa hooks
     */
    private function init_hooks() {
        // Registra shortcodes
        add_shortcode('Haklai_checkpoint', array($this, 'checkpoint_shortcode'));
        add_shortcode('Haklai_dashboard', array($this, 'dashboard_shortcode'));
        
        /**
         * NOTA IMPORTANTE - SHORTCODE DE CONFIGURAÇÕES
         * 
         * ⚠️ Este shortcode está TEMPORARIAMENTE sem template associado.
         * 
         * DECISÃO ESTRATÉGICA (13/10/2025):
         * - As configurações estão disponíveis em: WordPress Admin → Haklai → Configurações
         * - Interface completa com Importação/Exportação de membros já implementada
         * - Template shortcode [Haklai_configuracoes] será desenvolvido na FASE FINAL DO PROJETO
         * 
         * RESPONSÁVEL PELA IMPLEMENTAÇÃO FUTURA:
         * - RICARDO NOLETO SARMENTO
         * 
         * USAR ENQUANTO ISSO:
         * - WordPress Admin → Haklai → Configurações (totalmente funcional)
         * 
         * STATUS: PENDENTE PARA FASE FINAL
         */
        add_shortcode('Haklai_configuracoes', array($this, 'configuracoes_shortcode'));
        
        add_shortcode('Haklai_relatorios', array($this, 'relatorios_shortcode'));
        
        // Hooks para assets
        add_action('wp_enqueue_scripts', array($this, 'enqueue_shortcode_assets'));
    }
    
    /**
     * Shortcode do Checkpoint (Baseado no HTML fornecido)
     * 
     * @param array $atts Atributos do shortcode
     * @return string HTML do checkpoint
     */
    public function checkpoint_shortcode($atts) {
        // Verifica permissões (apenas Líderes de Célula e superiores)
        if (!Haklai_Permissions::user_can_access('haklai_access_checkpoint')) {
            return $this->render_access_denied();
        }
        
        // Atributos padrão
        $atts = shortcode_atts(array(
            'cell_id' => 0,
            'meeting_date' => date('Y-m-d'),
            'auto_create_meeting' => 'true',
        ), $atts);
        
        // Obtém dados
        $data = $this->get_checkpoint_data($atts);
        
        // Renderiza template
        return $this->render_template('checkpoint', $data);
    }
    
    /**
     * Shortcode do Dashboard
     * 
     * @param array $atts Atributos do shortcode
     * @return string HTML do dashboard
     */
    public function dashboard_shortcode($atts) {
        // Verifica permissões
        if (!Haklai_Permissions::user_can_access('haklai_access_dashboard')) {
            return $this->render_access_denied();
        }
        
        // Atributos padrão
        $atts = shortcode_atts(array(
            'cell_id' => 0,
            'show_stats' => 'true',
            'show_members' => 'true',
            'show_notifications' => 'true',
        ), $atts);
        
        // Obtém dados
        $data = $this->get_dashboard_data($atts);
        
        // Renderiza template
        return $this->render_template('dashboard', $data);
    }
    
    /**
     * Shortcode de Configurações
     * 
     * @param array $atts Atributos do shortcode
     * @return string HTML das configurações
     */
    public function configuracoes_shortcode($atts) {
        // Verifica permissões (apenas Pastores Senior/Supervisor)
        if (!Haklai_Permissions::user_can_access('haklai_access_settings')) {
            return $this->render_access_denied();
        }
        
        // Atributos padrão
        $atts = shortcode_atts(array(
            'show_license' => 'true',
            'show_system' => 'true',
            'show_permissions' => 'true',
        ), $atts);
        
        // Obtém dados
        $data = $this->get_configuracoes_data($atts);
        
        // Renderiza template
        return $this->render_template('configuracoes', $data);
    }
    
    /**
     * Shortcode de Relatórios
     * 
     * @param array $atts Atributos do shortcode
     * @return string HTML dos relatórios
     */
    public function relatorios_shortcode($atts) {
        // Verifica permissões
        if (!Haklai_Permissions::user_can_access('haklai_access_reports')) {
            return $this->render_access_denied();
        }
        
        // Atributos padrão
        $atts = shortcode_atts(array(
            'default_period' => '30',
            'show_filters' => 'true',
            'show_history' => 'true',
        ), $atts);
        
        // Obtém dados
        $data = $this->get_relatorios_data($atts);
        
        // Renderiza template
        return $this->render_template('relatorios', $data);
    }
    
    /**
     * Obtém dados para o checkpoint (baseado no HTML fornecido)
     */
    private function get_checkpoint_data($atts) {
        $user_id = get_current_user_id();
        $user_level = Haklai_Permissions::get_user_level($user_id);
        $user_cells = Haklai_Permissions::get_user_cells($user_id);
        
        // Determina célula
        $cell_id = $atts['cell_id'] ?: (isset($user_cells[0]) ? $user_cells[0]->ID : 0);
        
        if (!$cell_id) {
            return array(
                'error' => __('Nenhuma célula encontrada para este usuário.', 'haklai-app')
            );
        }
        
        // Obtém dados da célula
        $cell = get_post($cell_id);
        $cell_address = get_post_meta($cell_id, '_haklai_address', true);
        
        // Dados da reunião atual
        $meeting = $this->get_or_create_meeting($cell_id, $atts['meeting_date']);
        
        // Membros da célula
        $members = $this->get_cell_members($cell_id);
        
        // Presença da reunião
        $attendance = $this->get_meeting_attendance($meeting['id']);
        
        // Estatísticas de presença
        $presence_stats = $this->calculate_presence_stats($members, $attendance);
        
        return array(
            'meeting' => $meeting,
            'members' => $members,
            'attendance' => $attendance,
            'stats' => $presence_stats,
            'cell_info' => array(
                'id' => $cell_id,
                'name' => $cell->post_title,
                'address' => $cell_address
            ),
            'cell_id' => $cell_id,
            'user_level' => $user_level,
        );
    }
    
    /**
     * Obtém ou cria reunião para o checkpoint
     */
    private function get_or_create_meeting($cell_id, $meeting_date) {
        // Tenta encontrar reunião existente
        $existing_meetings = get_posts(array(
            'post_type' => 'haklai_meeting',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_cell_id',
                    'value' => $cell_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_meeting_date',
                    'value' => $meeting_date,
                    'compare' => '='
                )
            )
        ));
        
        if (!empty($existing_meetings)) {
            $meeting = $existing_meetings[0];
            return array(
                'id' => $meeting->ID,
                'title' => $meeting->post_title,
                'meeting_date' => get_post_meta($meeting->ID, '_haklai_meeting_date', true),
                'host' => get_post_meta($meeting->ID, '_haklai_host', true),
                'address' => get_post_meta($meeting->ID, '_haklai_address', true),
            );
        }
        
        // Cria nova reunião se não existir
        $meeting_data = array(
            'post_type' => 'haklai_meeting',
            'post_title' => sprintf(__('Reunião - %s', 'haklai-app'), date('d/m/Y', strtotime($meeting_date))),
            'post_content' => '',
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_cell_id' => $cell_id,
                '_haklai_meeting_date' => $meeting_date,
                '_haklai_host' => '',
                '_haklai_address' => '',
                '_haklai_created_by' => get_current_user_id(),
                '_haklai_created_at' => current_time('mysql'),
            )
        );
        
        $meeting_id = wp_insert_post($meeting_data);
        
        if ($meeting_id) {
            return array(
                'id' => $meeting_id,
                'title' => $meeting_data['post_title'],
                'meeting_date' => $meeting_date,
                'host' => '',
                'address' => '',
            );
        }
        
        return null;
    }
    
    /**
     * Obtém membros da célula
     */
    private function get_cell_members($cell_id) {
        $members = get_posts(array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_cell_id',
                    'value' => $cell_id,
                    'compare' => '='
                ),
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                )
            ),
            'orderby' => 'title',
            'order' => 'ASC'
        ));
        
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
                'cell_id' => $cell_id,
            );
        }
        
        return $formatted_members;
    }
    
    /**
     * Obtém presença da reunião
     */
    private function get_meeting_attendance($meeting_id) {
        $attendances = get_posts(array(
            'post_type' => 'haklai_attendance',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_meeting_id',
                    'value' => $meeting_id,
                    'compare' => '='
                )
            )
        ));
        
        $formatted_attendance = array();
        foreach ($attendances as $attendance) {
            $formatted_attendance[] = array(
                'id' => $attendance->ID,
                'meeting_id' => $meeting_id,
                'member_id' => get_post_meta($attendance->ID, '_haklai_member_id', true),
                'is_present' => get_post_meta($attendance->ID, '_haklai_is_present', true) === '1',
                'notes' => get_post_meta($attendance->ID, '_haklai_notes', true),
                'attendance_date' => get_post_meta($attendance->ID, '_haklai_attendance_date', true),
            );
        }
        
        return $formatted_attendance;
    }
    
    /**
     * Calcula estatísticas de presença
     */
    private function calculate_presence_stats($members, $attendance) {
        $total_members = count($members);
        $present_members = 0;
        $frequent_visitors = 0;
        $last_meeting_present = 0;
        
        // Conta presentes na reunião atual
        foreach ($attendance as $att) {
            if ($att['is_present']) {
                $present_members++;
            }
        }
        
        // Conta frequentadores assíduos
        foreach ($members as $member) {
            if ($member['baptism_status'] === 'frequent_visitor') {
                $frequent_visitors++;
            }
        }
        
        // Conta presença na última célula (reunião atual)
        $last_meeting_present = $present_members;
        
        $presence_rate = $total_members > 0 ? round(($present_members / $total_members) * 100, 1) : 0;
        
        return array(
            'total_members' => $total_members,
            'frequent_visitors' => $frequent_visitors,
            'last_meeting_present' => $last_meeting_present,
            'presence_rate' => $presence_rate,
        );
    }
    
    /**
     * Obtém dados para o dashboard
     */
    private function get_dashboard_data($atts) {
        $user_id = get_current_user_id();
        $user_level = Haklai_Permissions::get_user_level($user_id);
        $user_cells = Haklai_Permissions::get_user_cells($user_id);
        
        // Estatísticas gerais
        $stats = $this->get_dashboard_statistics($user_cells);
        
        // Membros recentes
        $recent_members = $this->get_recent_members($user_cells);
        
        // Notificações
        $notifications = $this->get_user_notifications($user_id);
        
        return array(
            'stats' => $stats,
            'recent_members' => $recent_members,
            'notifications' => $notifications,
            'user_level' => $user_level,
            'user_cells' => $user_cells,
        );
    }
    
    /**
     * Obtém estatísticas do dashboard
     * FASE 3: Refatorado para usar Repository Pattern
     */
    private function get_dashboard_statistics($user_cells) {
        // FASE 3: Usa StatisticsRepository para obter estatísticas
        $statistics_repository = new StatisticsRepository();
        $stats = $statistics_repository->getGeneralStats($user_cells);
        
        // Adiciona total_cells para compatibilidade (se necessário)
        $stats['total_cells'] = count($user_cells);
        
        return $stats;
    }
    
    /**
     * Obtém membros recentes
     */
    private function get_recent_members($user_cells) {
        $cell_ids = array();
        foreach ($user_cells as $cell) {
            $cell_ids[] = $cell->ID;
        }
        
        $members_args = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC',
            'meta_query' => array(
                array(
                    'key' => '_haklai_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        );
        
        if (!empty($cell_ids)) {
            $members_args['meta_query'][] = array(
                'key' => '_haklai_cell_id',
                'value' => $cell_ids,
                'compare' => 'IN'
            );
        }
        
        $members = get_posts($members_args);
        $formatted_members = array();
        
        foreach ($members as $member) {
            $formatted_members[] = array(
                'id' => $member->ID,
                'name' => $member->post_title,
                'email' => get_post_meta($member->ID, '_haklai_email', true),
                'phone' => get_post_meta($member->ID, '_haklai_phone', true),
                'skill' => get_post_meta($member->ID, '_haklai_skill', true),
                'role_level' => intval(get_post_meta($member->ID, '_haklai_role_level', true)),
                'baptism_status' => get_post_meta($member->ID, '_haklai_baptism_status', true),
                'created_at' => $member->post_date,
            );
        }
        
        return $formatted_members;
    }
    
    /**
     * Obtém notificações do usuário
     */
    private function get_user_notifications($user_id) {
        // Implementar sistema de notificações
        return array();
    }
    
    /**
     * Obtém dados para configurações
     */
    private function get_configuracoes_data($atts) {
        // Informações da licença
        $license_info = array(
            'status' => 'active',
            'expires' => '2025-12-31',
            'type' => 'premium',
        );
        
        // Informações do sistema
        $system_info = array(
            'php_version' => PHP_VERSION,
            'wordpress_version' => get_bloginfo('version'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
        );
        
        // Permissões por nível
        $permissions = $this->get_permissions_by_level();
        
        return array(
            'license' => $license_info,
            'system' => $system_info,
            'permissions' => $permissions,
        );
    }
    
    /**
     * Obtém dados para relatórios
     */
    private function get_relatorios_data($atts) {
        $user_cells = Haklai_Permissions::get_user_cells();
        
        // Estatísticas gerais
        $stats = $this->get_dashboard_statistics($user_cells);
        
        // Relatórios disponíveis
        $available_reports = array(
            'attendance' => __('Relatório de Presença', 'haklai-app'),
            'members' => __('Relatório de Membros', 'haklai-app'),
            'cells' => __('Relatório de Células', 'haklai-app'),
            'baptisms' => __('Relatório de Batismos', 'haklai-app'),
        );
        
        // Histórico de relatórios
        $reports_history = $this->get_reports_history();
        
        return array(
            'stats' => $stats,
            'available_reports' => $available_reports,
            'reports_history' => $reports_history,
            'default_period' => $atts['default_period'],
        );
    }
    
    /**
     * Obtém histórico de relatórios
     */
    private function get_reports_history() {
        $reports = get_posts(array(
            'post_type' => 'haklai_report',
            'post_status' => 'publish',
            'posts_per_page' => 10,
            'orderby' => 'date',
            'order' => 'DESC'
        ));
        
        $formatted_reports = array();
        foreach ($reports as $report) {
            // Busca dados da reunião associada
            $meeting_id = get_post_meta($report->ID, '_haklai_meeting_id', true);
            $meeting = $meeting_id ? get_post($meeting_id) : null;
            $meeting_date = $meeting ? get_post_meta($meeting_id, '_haklai_meeting_date', true) : $report->post_date;
            
            // Busca dados da célula
            $cell_id = $meeting ? get_post_meta($meeting_id, '_haklai_cell_id', true) : null;
            $cell = $cell_id ? get_post($cell_id) : null;
            
            // Busca dados do líder
            $leader_id = $cell ? get_post_meta($cell_id, '_haklai_leader_id', true) : null;
            $leader = $leader_id ? get_userdata($leader_id) : null;
            
            // Busca estatísticas
            $members = $cell_id ? get_posts(array(
                'post_type' => 'haklai_member',
                'meta_query' => array(array('key' => '_haklai_cell_id', 'value' => $cell_id)),
                'posts_per_page' => -1
            )) : array();
            
            $attendance = $meeting_id ? get_posts(array(
                'post_type' => 'haklai_attendance',
                'meta_query' => array(array('key' => '_haklai_meeting_id', 'value' => $meeting_id)),
                'posts_per_page' => -1
            )) : array();
            
            // Calcula estatísticas
            $total_members = count($members);
            $present_members = 0;
            $total_visitors = 0;
            
            foreach ($attendance as $att) {
                $is_present = get_post_meta($att->ID, '_haklai_is_present', true) === '1';
                if ($is_present) {
                    $present_members++;
                }
            }
            
            $presence_percentage = $total_members > 0 ? round(($present_members / $total_members) * 100, 1) : 0;
            
            $formatted_reports[] = array(
                'id' => $report->ID,
                'title' => $report->post_title,
                'type' => get_post_meta($report->ID, '_haklai_report_type', true),
                'created_at' => $report->post_date,
                'meeting_date' => $meeting_date,
                'created_by' => get_post_meta($report->ID, '_haklai_created_by', true),
                'url' => get_permalink($report->ID),
                'leader_name' => $leader ? $leader->display_name : 'N/A',
                'cell_name' => $cell ? $cell->post_title : 'N/A',
                'total_members' => $total_members,
                'total_visitors' => $total_visitors,
                'presence_percentage' => $presence_percentage,
            );
        }
        
        return $formatted_reports;
    }
    
    /**
     * Obtém permissões por nível
     */
    private function get_permissions_by_level() {
        return array(
            5 => array(
                'name' => __('Pastor Supervisor', 'haklai-app'),
                'capabilities' => array(
                    'haklai_access_dashboard' => true,
                    'haklai_access_settings' => true,
                    'haklai_access_reports' => true,
                    'haklai_access_checkpoint' => true,
                    'haklai_manage_members' => true,
                    'haklai_manage_cells' => true,
                    'haklai_manage_users' => true,
                    'haklai_view_all_data' => true,
                ),
            ),
            4 => array(
                'name' => __('Pastor Senior', 'haklai-app'),
                'capabilities' => array(
                    'haklai_access_dashboard' => true,
                    'haklai_access_reports' => true,
                    'haklai_manage_members' => true,
                    'haklai_manage_cells' => true,
                    'haklai_view_all_data' => true,
                ),
            ),
            3 => array(
                'name' => __('Pastor de Rede', 'haklai-app'),
                'capabilities' => array(
                    'haklai_access_dashboard' => true,
                    'haklai_access_reports' => true,
                    'haklai_manage_members' => true,
                    'haklai_manage_cells' => true,
                ),
            ),
            2 => array(
                'name' => __('Discipulador', 'haklai-app'),
                'capabilities' => array(
                    'haklai_access_dashboard' => true,
                    'haklai_access_reports' => true,
                    'haklai_manage_members' => true,
                    'haklai_manage_cells' => true,
                ),
            ),
            1 => array(
                'name' => __('Líder de Célula', 'haklai-app'),
                'capabilities' => array(
                    'haklai_access_dashboard' => true,
                    'haklai_access_checkpoint' => true,
                    'haklai_manage_members' => true,
                ),
            ),
            0 => array(
                'name' => __('Membro', 'haklai-app'),
                'capabilities' => array(),
            ),
        );
    }
    
    /**
     * Renderiza template
     */
    private function render_template($template_name, $data) {
        $template_path = HAKLAI_TEMPLATES_PATH . $template_name . '-template.php';
        
        if (!file_exists($template_path)) {
            return '<div class="haklai-error">Template não encontrado: ' . $template_name . '</div>';
        }
        
        // Extrai dados para o template
        extract($data);
        
        // Captura output
        ob_start();
        include $template_path;
        return ob_get_clean();
    }
    
    /**
     * Renderiza mensagem de acesso negado ou formulário de login
     */
    private function render_access_denied() {
        // Verifica se o usuário está logado
        if (!is_user_logged_in()) {
            // Usuário NÃO está logado - Mostra formulário de login
            return $this->render_login_form();
        }
        
        // Usuário está logado mas SEM permissão
        return '
        <div class="haklai-access-denied">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-lock fa-3x text-muted mb-3"></i>
                                <h3 class="card-title">' . __('Acesso Negado', 'haklai-app') . '</h3>
                                <p class="card-text">' . __('Você não tem permissão para acessar esta funcionalidade.', 'haklai-app') . '</p>
                                <p class="text-muted">' . __('Entre em contato com o administrador do sistema.', 'haklai-app') . '</p>
                                <a href="' . wp_logout_url(get_permalink()) . '" class="btn btn-secondary mt-3">
                                    <i class="fas fa-sign-out-alt"></i> ' . __('Sair e fazer login com outra conta', 'haklai-app') . '
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
    }
    
    /**
     * Renderiza formulário de login do WordPress com redirect
     */
    private function render_login_form() {
        // URL atual para redirect após login
        $redirect_to = get_permalink();
        if (!$redirect_to) {
            $redirect_to = home_url($_SERVER['REQUEST_URI']);
        }
        
        // Args para o formulário de login do WordPress
        $args = array(
            'echo'           => false,
            'redirect'       => esc_url($redirect_to),
            'form_id'        => 'haklai_loginform',
            'label_username' => __('Usuário ou E-mail', 'haklai-app'),
            'label_password' => __('Senha', 'haklai-app'),
            'label_remember' => __('Lembrar-me', 'haklai-app'),
            'label_log_in'   => __('Entrar', 'haklai-app'),
            'remember'       => true,
            'value_username' => '',
            'value_remember' => true,
        );
        
        // Captura o formulário de login
        $login_form = wp_login_form($args);
        
        // Envolve em HTML estilizado
        $output = '
        <div class="haklai-login-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="card haklai-login-card">
                            <div class="card-body">
                                <div class="text-center mb-4">
                                    <i class="fas fa-user-circle fa-4x text-primary mb-3"></i>
                                    <h3 class="card-title">' . __('Área Restrita', 'haklai-app') . '</h3>
                                    <p class="text-muted">' . __('Faça login para acessar o Checkpoint', 'haklai-app') . '</p>
                                </div>
                                
                                ' . $login_form . '
                                
                                <div class="text-center mt-3">
                                    <a href="' . wp_lostpassword_url($redirect_to) . '" class="text-muted small">
                                        <i class="fas fa-question-circle"></i> ' . __('Esqueceu sua senha?', 'haklai-app') . '
                                    </a>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="alert alert-info small mb-0" role="alert">
                                    <i class="fas fa-info-circle"></i> 
                                    ' . __('Use suas credenciais de líder para acessar o sistema.', 'haklai-app') . '
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>';
        
        return $output;
    }
    
    /**
     * Enfileira assets específicos dos shortcodes
     */
    public function enqueue_shortcode_assets() {
        // Verifica se há shortcodes na página
        global $post;
        if (!$post) {
            return;
        }
        
        $shortcodes = array('Haklai_dashboard', 'Haklai_configuracoes', 'Haklai_relatorios', 'Haklai_checkpoint');
        $has_shortcode = false;
        
        foreach ($shortcodes as $shortcode) {
            if (has_shortcode($post->post_content, $shortcode)) {
                $has_shortcode = true;
                break;
            }
        }
        
        if (!$has_shortcode) {
            return;
        }
        
        // DESATIVA haklai-main.css quando há shortcodes (agora unificado)
        wp_dequeue_style('haklai-main');
        wp_deregister_style('haklai-main');
        
        // Carrega APENAS o CSS unificado (design system + shortcodes + utilitários)
        wp_enqueue_style(
            'haklai-design-system',
            HAKLAI_ASSETS_URL . 'css/haklai-design-system.css',
            array('haklai-fontawesome'),
            HAKLAI_VERSION,
            'all'
        );
        
        // JavaScript dos shortcodes
        wp_enqueue_script(
            'haklai-shortcodes',
            HAKLAI_ASSETS_URL . 'js/haklai-shortcodes.js',
            array('haklai-main'),
            HAKLAI_VERSION,
            true
        );
    }
    
}

