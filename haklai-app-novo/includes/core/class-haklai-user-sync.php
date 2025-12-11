<?php
/**
 * Classe responsável pela sincronização entre Membros e Usuários WordPress
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
 * Classe Haklai_User_Sync
 * 
 * Funções principais:
 * - Sincronização automática Membro → Usuário WP quando role_level >= 1
 * - Sincronização reversa Usuário WP → Membro quando criado com role haklai_*
 * - Geração de credenciais e envio de email
 * - Manutenção de vínculos bidirecionais
 */
class Haklai_User_Sync {
    
    /**
     * Construtor
     */
    public function __construct() {
        // Hook quando salvar membro
        add_action('save_post_haklai_member', array($this, 'sync_member_on_save'), 20, 3);
        
        // Hook quando salvar célula - sincroniza com tabelas
        add_action('save_post_haklai_cell', array($this, 'sync_cell_on_save'), 20, 3);
        
        // Hook quando salvar reunião - sincroniza com tabelas
        add_action('save_post_haklai_meeting', array($this, 'sync_meeting_on_save'), 20, 3);
        
        // Hook quando salvar presença - sincroniza com tabelas
        add_action('save_post_haklai_attendance', array($this, 'sync_attendance_on_save'), 20, 3);
        
        // Hook quando criar usuário
        add_action('user_register', array($this, 'sync_user_on_register'), 10, 1);
        
        // Hook quando atualizar usuário
        add_action('profile_update', array($this, 'sync_user_on_update'), 10, 2);
        
        // Hook quando deletar usuário
        add_action('delete_user', array($this, 'unlink_on_user_delete'), 10, 1);
        
        // Hook quando deletar membro
        add_action('before_delete_post', array($this, 'unlink_on_member_delete'), 10, 1);
    }
    
    /**
     * Sincroniza membro quando salvo
     * 
     * @param int $post_id ID do post
     * @param WP_Post $post Objeto do post
     * @param bool $update Se é atualização ou novo post
     */
    public function sync_member_on_save($post_id, $post, $update) {
        // Previne loop infinito
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        // Ignora autosave e revisões
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Verifica se é publicado
        if ($post->post_status !== 'publish') {
            return;
        }
        
        // Obtém role_level
        $role_level = intval(get_post_meta($post_id, '_haklai_role_level', true));
        
        // Sincroniza com tabelas otimizadas (se existirem)
        if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
            Haklai_DB::sync_member_from_cpt($post_id);
        }
        
        // Se for líder ou superior (role_level >= 1), sincroniza
        if ($role_level >= 1) {
            define('HAKLAI_SYNCING', true);
            $this->sync_member_to_user($post_id, $role_level, $update);
        } else {
            // Se era líder e agora não é mais, desvincula usuário
            $this->check_and_downgrade_user($post_id);
        }
    }
    
    /**
     * Sincroniza membro para usuário WordPress
     * 
     * @param int $member_id ID do membro
     * @param int $role_level Nível hierárquico
     * @param bool $is_update Se é atualização
     * @return int|WP_Error ID do usuário ou erro
     */
    private function sync_member_to_user($member_id, $role_level, $is_update = false) {
        // CAMADA 1: Verifica se já tem usuário vinculado
        $existing_user_id = get_post_meta($member_id, '_haklai_user_id', true);
        
        if ($existing_user_id && get_userdata($existing_user_id)) {
            // Usuário já existe - atualiza
            $this->full_sync_member_to_user($member_id, $existing_user_id);
            return $existing_user_id;
        }
        
        // CAMADA 2: Busca por email
        $member_email = get_post_meta($member_id, '_haklai_email', true);
        
        if (empty($member_email)) {
            // Log do erro
            $this->log_sync_error($member_id, 'Membro sem email - não pode criar usuário');
            return new WP_Error('no_email', __('Membro precisa ter email para criar usuário WordPress', 'haklai-app'));
        }
        
        // Verifica se já existe usuário com esse email
        $user = get_user_by('email', $member_email);
        
        if ($user) {
            // Usuário existe mas não estava vinculado - criar vínculo
            update_post_meta($member_id, '_haklai_user_id', $user->ID);
            update_user_meta($user->ID, '_haklai_member_id', $member_id);
            $this->full_sync_member_to_user($member_id, $user->ID);
            $this->log_sync_action($member_id, $user->ID, 'linked_existing');
            return $user->ID;
        }
        
        // CAMADA 3: Criar novo usuário WordPress
        return $this->create_wordpress_user($member_id, $member_email, $role_level);
    }
    
    /**
     * Cria novo usuário WordPress
     * 
     * @param int $member_id ID do membro
     * @param string $email Email do membro
     * @param int $role_level Nível hierárquico
     * @return int|WP_Error ID do usuário criado ou erro
     */
    private function create_wordpress_user($member_id, $email, $role_level) {
        $member_name = get_the_title($member_id);
        
        // Gera username único
        $username = $this->generate_unique_username($member_name);
        
        // Gera senha forte
        $password = wp_generate_password(12, true, true);
        
        // Obtém role do WordPress baseado no nível
        $wp_role = $this->get_wordpress_role_by_level($role_level);
        
        // Cria o usuário
        $user_id = wp_create_user($username, $password, $email);
        
        if (is_wp_error($user_id)) {
            $this->log_sync_error($member_id, 'Erro ao criar usuário: ' . $user_id->get_error_message());
            return $user_id;
        }
        
        // Atualiza role
        $user = new WP_User($user_id);
        $user->set_role($wp_role);
        
        // Atualiza dados adicionais
        $name_parts = explode(' ', $member_name, 2);
        wp_update_user(array(
            'ID' => $user_id,
            'display_name' => $member_name,
            'first_name' => $name_parts[0],
            'last_name' => isset($name_parts[1]) ? $name_parts[1] : '',
        ));
        
        // Sincronização completa
        $this->full_sync_member_to_user($member_id, $user_id);
        
        // Envia email com credenciais
        $this->send_credentials_email($user_id, $password, $member_id);
        
        // Log da criação
        $this->log_sync_action($member_id, $user_id, 'created', array(
            'username' => $username,
            'role' => $wp_role,
        ));
        
        return $user_id;
    }
    
    /**
     * Sincronização COMPLETA de todos os dados do membro para o usuário
     * 
     * @param int $member_id ID do membro
     * @param int $user_id ID do usuário
     * @return bool
     */
    private function full_sync_member_to_user($member_id, $user_id) {
        // Coleta todos os dados do membro
        $member_data = array(
            'email'          => get_post_meta($member_id, '_haklai_email', true),
            'phone'          => get_post_meta($member_id, '_haklai_phone', true),
            'photo'          => get_post_meta($member_id, '_haklai_photo', true),
            'cell_id'        => get_post_meta($member_id, '_haklai_cell_id', true),
            'role_level'     => get_post_meta($member_id, '_haklai_role_level', true),
            'skill'          => get_post_meta($member_id, '_haklai_skill', true),
            'baptism_status' => get_post_meta($member_id, '_haklai_baptism_status', true),
            'baptism_date'   => get_post_meta($member_id, '_haklai_baptism_date', true),
            'status'         => get_post_meta($member_id, '_haklai_status', true),
            'leader_id'      => get_post_meta($member_id, '_haklai_leader_id', true),
        );
        
        // Atualiza dados do usuário WP
        $member_name = get_the_title($member_id);
        $name_parts = explode(' ', $member_name, 2);
        
        wp_update_user(array(
            'ID'           => $user_id,
            'user_email'   => $member_data['email'],
            'display_name' => $member_name,
            'first_name'   => $name_parts[0],
            'last_name'    => isset($name_parts[1]) ? $name_parts[1] : '',
        ));
        
        // SINCRONIZA TODOS OS METAS NO USUÁRIO (CRUCIAL!)
        update_user_meta($user_id, '_haklai_member_id', $member_id);
        update_user_meta($user_id, '_haklai_cell_id', $member_data['cell_id']); // ⭐ CÉLULA
        update_user_meta($user_id, '_haklai_role_level', $member_data['role_level']); // ⭐ NÍVEL
        update_user_meta($user_id, '_haklai_phone', $member_data['phone']);
        update_user_meta($user_id, '_haklai_photo', $member_data['photo']);
        update_user_meta($user_id, '_haklai_skill', $member_data['skill']);
        update_user_meta($user_id, '_haklai_baptism_status', $member_data['baptism_status']);
        update_user_meta($user_id, '_haklai_baptism_date', $member_data['baptism_date']);
        update_user_meta($user_id, '_haklai_status', $member_data['status']);
        update_user_meta($user_id, '_haklai_leader_id', $member_data['leader_id']);
        
        // Atualiza role do WordPress
        $wp_role = $this->get_wordpress_role_by_level($member_data['role_level']);
        $user = new WP_User($user_id);
        $user->set_role($wp_role);
        
        // Vínculo reverso
        update_post_meta($member_id, '_haklai_user_id', $user_id);
        
        // Timestamp de sincronização
        $sync_time = current_time('mysql');
        update_user_meta($user_id, '_haklai_last_sync', $sync_time);
        update_post_meta($member_id, '_haklai_last_sync', $sync_time);
        
        return true;
    }
    
    /**
     * Gera username único baseado no nome
     * 
     * @param string $name Nome completo
     * @return string Username único
     */
    private function generate_unique_username($name) {
        // Remove acentos e caracteres especiais
        $name = remove_accents($name);
        $name = sanitize_title($name);
        $name = str_replace('-', '.', $name);
        
        // Gera username base (primeiro.sobrenome)
        $parts = explode('.', $name);
        if (count($parts) > 1) {
            $username = $parts[0] . '.' . end($parts);
        } else {
            $username = $name;
        }
        
        // Verifica se já existe
        if (!username_exists($username)) {
            return $username;
        }
        
        // Se existe, adiciona número sequencial
        $counter = 1;
        $new_username = $username;
        
        while (username_exists($new_username)) {
            $new_username = $username . $counter;
            $counter++;
        }
        
        return $new_username;
    }
    
    /**
     * Mapeia nível hierárquico para role do WordPress
     * 
     * @param int $level Nível (0-5)
     * @return string Nome do role
     */
    private function get_wordpress_role_by_level($level) {
        $role_map = array(
            5 => 'haklai_pastor_supervisor',
            4 => 'haklai_pastor_senior',
            3 => 'haklai_pastor_rede',
            2 => 'haklai_discipulador',
            1 => 'haklai_lider_celula',
            0 => 'subscriber', // Membro comum vira subscriber
        );
        
        return isset($role_map[$level]) ? $role_map[$level] : 'subscriber';
    }
    
    /**
     * Envia email com credenciais
     * 
     * @param int $user_id ID do usuário
     * @param string $password Senha gerada
     * @param int $member_id ID do membro
     * @return bool
     */
    private function send_credentials_email($user_id, $password, $member_id) {
        $user = get_userdata($user_id);
        $member_name = get_the_title($member_id);
        $member_cell = get_post_meta($member_id, '_haklai_cell_id', true);
        $cell_name = $member_cell ? get_the_title($member_cell) : __('Nenhuma', 'haklai-app');
        $role_level = get_post_meta($member_id, '_haklai_role_level', true);
        $role_name = Haklai_Permissions::get_role_name($role_level);
        
        // Permite customização via filtro
        $subject = apply_filters('haklai_credentials_email_subject', 
            sprintf(__('Bem-vindo ao Sistema Haklai - %s', 'haklai-app'), get_bloginfo('name'))
        );
        
        // Template HTML
        ob_start();
        include $this->get_email_template_path();
        $message = ob_get_clean();
        
        // Permite customização total via filtro
        $message = apply_filters('haklai_credentials_email_message', $message, $user, $member_id, $password);
        
        // Configura para enviar HTML
        add_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        
        // Envia email (plugins SMTP interceptam aqui automaticamente)
        $sent = wp_mail($user->user_email, $subject, $message);
        
        // Remove filtro
        remove_filter('wp_mail_content_type', array($this, 'set_html_content_type'));
        
        // Log do envio
        if ($sent) {
            update_post_meta($member_id, '_haklai_credentials_sent', current_time('mysql'));
            update_post_meta($member_id, '_haklai_credentials_sent_count', 
                intval(get_post_meta($member_id, '_haklai_credentials_sent_count', true)) + 1
            );
        }
        
        return $sent;
    }
    
    /**
     * Retorna caminho do template de email
     * 
     * @return string
     */
    private function get_email_template_path() {
        // Permite customização via tema
        $custom_template = locate_template('haklai/email-credentials.php');
        
        if ($custom_template) {
            return $custom_template;
        }
        
        // Template padrão
        return HAKLAI_PLUGIN_PATH . 'includes/templates/email-credentials.php';
    }
    
    /**
     * Define content type como HTML
     * 
     * @return string
     */
    public function set_html_content_type() {
        return 'text/html';
    }
    
    /**
     * Sincroniza quando usuário é criado no WordPress
     * 
     * @param int $user_id ID do usuário
     */
    public function sync_user_on_register($user_id) {
        // Previne loop
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        define('HAKLAI_SYNCING', true);
        
        $user = get_userdata($user_id);
        
        // Verifica se tem role haklai_*
        $haklai_role = $this->get_haklai_role_from_user($user);
        
        if (!$haklai_role) {
            return; // Não é role do Haklai
        }
        
        // Busca membro com mesmo email
        $member = $this->get_member_by_email($user->user_email);
        
        if ($member) {
            // Membro já existe - vincula
            update_post_meta($member->ID, '_haklai_user_id', $user_id);
            update_user_meta($user_id, '_haklai_member_id', $member->ID);
            $this->sync_user_to_member($user_id, $member->ID);
        } else {
            // Cria membro automaticamente
            $member_id = $this->create_member_from_user($user_id);
            if (!is_wp_error($member_id)) {
                update_user_meta($user_id, '_haklai_member_id', $member_id);
            }
        }
    }
    
    /**
     * Sincroniza quando usuário é atualizado
     * 
     * @param int $user_id ID do usuário
     * @param WP_User $old_user_data Dados antigos
     */
    public function sync_user_on_update($user_id, $old_user_data) {
        // Previne loop
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        $member_id = get_user_meta($user_id, '_haklai_member_id', true);
        
        if ($member_id && get_post($member_id)) {
            define('HAKLAI_SYNCING', true);
            $this->sync_user_to_member($user_id, $member_id);
        }
    }
    
    /**
     * Sincroniza dados do usuário para o membro
     * 
     * @param int $user_id ID do usuário
     * @param int $member_id ID do membro
     */
    private function sync_user_to_member($user_id, $member_id) {
        $user = get_userdata($user_id);
        
        // Atualiza título do membro
        wp_update_post(array(
            'ID' => $member_id,
            'post_title' => $user->display_name,
        ));
        
        // Sincroniza metas
        $role_level = get_user_meta($user_id, '_haklai_role_level', true);
        if ($role_level) {
            update_post_meta($member_id, '_haklai_role_level', $role_level);
        }
        
        $cell_id = get_user_meta($user_id, '_haklai_cell_id', true);
        if ($cell_id) {
            update_post_meta($member_id, '_haklai_cell_id', $cell_id);
        }
        
        // Outros metas
        update_post_meta($member_id, '_haklai_email', $user->user_email);
        
        $phone = get_user_meta($user_id, '_haklai_phone', true);
        if ($phone) {
            update_post_meta($member_id, '_haklai_phone', $phone);
        }
    }
    
    /**
     * Cria membro a partir de usuário WordPress
     * 
     * @param int $user_id ID do usuário
     * @return int|WP_Error ID do membro criado
     */
    private function create_member_from_user($user_id) {
        $user = get_userdata($user_id);
        
        $member_id = wp_insert_post(array(
            'post_type' => 'haklai_member',
            'post_title' => $user->display_name,
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_email' => $user->user_email,
                '_haklai_user_id' => $user_id,
                '_haklai_role_level' => $this->get_level_from_user_role($user),
                '_haklai_status' => 'active',
                '_haklai_created_at' => current_time('mysql'),
            ),
        ));
        
        if (!is_wp_error($member_id)) {
            $this->log_sync_action($member_id, $user_id, 'member_created_from_user');
        }
        
        return $member_id;
    }
    
    /**
     * Obtém role haklai do usuário
     * 
     * @param WP_User $user
     * @return string|false
     */
    private function get_haklai_role_from_user($user) {
        $haklai_roles = array(
            'haklai_pastor_supervisor',
            'haklai_pastor_senior',
            'haklai_pastor_rede',
            'haklai_discipulador',
            'haklai_lider_celula',
        );
        
        foreach ($user->roles as $role) {
            if (in_array($role, $haklai_roles)) {
                return $role;
            }
        }
        
        return false;
    }
    
    /**
     * Obtém nível baseado no role do usuário
     * 
     * @param WP_User $user
     * @return int
     */
    private function get_level_from_user_role($user) {
        $role_level_map = array(
            'haklai_pastor_supervisor' => 5,
            'haklai_pastor_senior' => 4,
            'haklai_pastor_rede' => 3,
            'haklai_discipulador' => 2,
            'haklai_lider_celula' => 1,
        );
        
        foreach ($user->roles as $role) {
            if (isset($role_level_map[$role])) {
                return $role_level_map[$role];
            }
        }
        
        return 0;
    }
    
    /**
     * Busca membro por email
     * 
     * @param string $email
     * @return WP_Post|false
     */
    private function get_member_by_email($email) {
        $members = get_posts(array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'meta_query' => array(
                array(
                    'key' => '_haklai_email',
                    'value' => $email,
                    'compare' => '='
                ),
            ),
        ));
        
        return !empty($members) ? $members[0] : false;
    }
    
    /**
     * Verifica se deve rebaixar usuário quando membro deixa de ser líder
     * 
     * @param int $member_id ID do membro
     */
    private function check_and_downgrade_user($member_id) {
        $user_id = get_post_meta($member_id, '_haklai_user_id', true);
        
        if (!$user_id || !get_userdata($user_id)) {
            return;
        }
        
        // Membro não é mais líder - muda role para subscriber
        $user = new WP_User($user_id);
        $user->set_role('subscriber');
        
        // Atualiza metas
        update_user_meta($user_id, '_haklai_role_level', 0);
        
        // Pode opcionalmente desvincular
        // delete_post_meta($member_id, '_haklai_user_id');
        // delete_user_meta($user_id, '_haklai_member_id');
    }
    
    /**
     * Remove vínculo quando usuário é deletado
     * 
     * @param int $user_id ID do usuário
     */
    public function unlink_on_user_delete($user_id) {
        $member_id = get_user_meta($user_id, '_haklai_member_id', true);
        
        if ($member_id) {
            delete_post_meta($member_id, '_haklai_user_id');
        }
    }
    
    /**
     * Remove vínculo quando membro é deletado
     * 
     * @param int $post_id ID do post
     */
    public function unlink_on_member_delete($post_id) {
        if (get_post_type($post_id) !== 'haklai_member') {
            return;
        }
        
        $user_id = get_post_meta($post_id, '_haklai_user_id', true);
        
        if ($user_id) {
            delete_user_meta($user_id, '_haklai_member_id');
            // Opcional: mudar role para subscriber
            $user = new WP_User($user_id);
            $user->set_role('subscriber');
        }
    }
    
    /**
     * Registra ação de sincronização
     * 
     * @param int $member_id ID do membro
     * @param int $user_id ID do usuário
     * @param string $action Tipo de ação
     * @param array $data Dados adicionais
     */
    private function log_sync_action($member_id, $user_id, $action, $data = array()) {
        $log = array(
            'timestamp' => current_time('mysql'),
            'action' => $action,
            'member_id' => $member_id,
            'user_id' => $user_id,
            'data' => $data,
        );
        
        // Salva em transient para debug (expira em 30 dias)
        $logs = get_transient('haklai_sync_logs') ?: array();
        $logs[] = $log;
        
        // Mantém apenas últimos 100 logs
        if (count($logs) > 100) {
            $logs = array_slice($logs, -100);
        }
        
        set_transient('haklai_sync_logs', $logs, 30 * DAY_IN_SECONDS);
    }
    
    /**
     * Registra erro de sincronização
     * 
     * @param int $member_id ID do membro
     * @param string $error_message Mensagem de erro
     */
    private function log_sync_error($member_id, $error_message) {
        $error = array(
            'timestamp' => current_time('mysql'),
            'member_id' => $member_id,
            'error' => $error_message,
        );
        
        $errors = get_transient('haklai_sync_errors') ?: array();
        $errors[] = $error;
        
        if (count($errors) > 50) {
            $errors = array_slice($errors, -50);
        }
        
        set_transient('haklai_sync_errors', $errors, 30 * DAY_IN_SECONDS);
    }
    
    /**
     * Sincroniza célula quando salva
     * 
     * @param int $post_id ID do post
     * @param WP_Post $post Objeto do post
     * @param bool $update Se é atualização ou novo post
     */
    public function sync_cell_on_save($post_id, $post, $update) {
        // Previne loop infinito
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        // Ignora autosave e revisões
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Sincroniza com tabelas otimizadas (se existirem)
        if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
            Haklai_DB::sync_cell_from_cpt($post_id);
        }
    }
    
    /**
     * Sincroniza reunião quando salva
     * 
     * @param int $post_id ID do post
     * @param WP_Post $post Objeto do post
     * @param bool $update Se é atualização ou novo post
     */
    public function sync_meeting_on_save($post_id, $post, $update) {
        // Previne loop infinito
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        // Ignora autosave e revisões
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Sincroniza com tabelas otimizadas (se existirem)
        if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
            Haklai_DB::sync_meeting_from_cpt($post_id);
        }
    }
    
    /**
     * Sincroniza presença quando salva
     * 
     * @param int $post_id ID do post
     * @param WP_Post $post Objeto do post
     * @param bool $update Se é atualização ou novo post
     */
    public function sync_attendance_on_save($post_id, $post, $update) {
        // Previne loop infinito
        if (defined('HAKLAI_SYNCING')) {
            return;
        }
        
        // Ignora autosave e revisões
        if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        
        // Sincroniza com tabelas otimizadas (se existirem)
        if (class_exists('Haklai_DB') && Haklai_DB::tables_exist()) {
            Haklai_DB::sync_attendance_from_cpt($post_id);
        }
    }
}




