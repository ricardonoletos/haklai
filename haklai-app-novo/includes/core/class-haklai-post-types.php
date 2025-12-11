<?php
/**
 * Classe responsável pelos Custom Post Types do plugin
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
 * Classe Haklai_Post_Types
 */
class Haklai_Post_Types {
    
    /**
     * Construtor
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_types'));
        add_action('admin_init', array($this, 'register_post_statuses'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_boxes'));
        // FASE 4: Hook para garantir tenant_id em novos posts
        add_action('wp_insert_post', array($this, 'ensure_tenant_id_on_insert'), 10, 3);
    }
    
    /**
     * Registra Custom Post Types
     */
    public function register_post_types() {
        $this->register_member_post_type();
        $this->register_cell_post_type();
        $this->register_network_post_type();
        $this->register_meeting_post_type();
        $this->register_attendance_post_type();
        $this->register_report_post_type();
    }
    
    /**
     * Registra Custom Post Type para Membros
     */
    private function register_member_post_type() {
        $labels = array(
            'name' => __('Membros', 'haklai-app'),
            'singular_name' => __('Membro', 'haklai-app'),
            'menu_name' => __('Membros', 'haklai-app'),
            'add_new' => __('Adicionar Novo', 'haklai-app'),
            'add_new_item' => __('Adicionar Novo Membro', 'haklai-app'),
            'edit_item' => __('Editar Membro', 'haklai-app'),
            'new_item' => __('Novo Membro', 'haklai-app'),
            'view_item' => __('Ver Membro', 'haklai-app'),
            'search_items' => __('Buscar Membros', 'haklai-app'),
            'not_found' => __('Nenhum membro encontrado', 'haklai-app'),
            'not_found_in_trash' => __('Nenhum membro encontrado na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Removido do menu automático
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_member', $args);
    }
    
    /**
     * Registra Custom Post Type para Células
     */
    private function register_cell_post_type() {
        $labels = array(
            'name' => __('Células', 'haklai-app'),
            'singular_name' => __('Célula', 'haklai-app'),
            'menu_name' => __('Células', 'haklai-app'),
            'add_new' => __('Adicionar Nova', 'haklai-app'),
            'add_new_item' => __('Adicionar Nova Célula', 'haklai-app'),
            'edit_item' => __('Editar Célula', 'haklai-app'),
            'new_item' => __('Nova Célula', 'haklai-app'),
            'view_item' => __('Ver Célula', 'haklai-app'),
            'search_items' => __('Buscar Células', 'haklai-app'),
            'not_found' => __('Nenhuma célula encontrada', 'haklai-app'),
            'not_found_in_trash' => __('Nenhuma célula encontrada na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Removido do menu automático
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_cell', $args);
    }
    
    /**
     * Registra Custom Post Type para Redes
     */
    private function register_network_post_type() {
        $labels = array(
            'name' => __('Redes', 'haklai-app'),
            'singular_name' => __('Rede', 'haklai-app'),
            'menu_name' => __('Redes', 'haklai-app'),
            'add_new' => __('Adicionar Nova', 'haklai-app'),
            'add_new_item' => __('Adicionar Nova Rede', 'haklai-app'),
            'edit_item' => __('Editar Rede', 'haklai-app'),
            'new_item' => __('Nova Rede', 'haklai-app'),
            'view_item' => __('Ver Rede', 'haklai-app'),
            'search_items' => __('Buscar Redes', 'haklai-app'),
            'not_found' => __('Nenhuma rede encontrada', 'haklai-app'),
            'not_found_in_trash' => __('Nenhuma rede encontrada na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Removido do menu automático
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_network', $args);
    }
    
    /**
     * Registra Custom Post Type para Reuniões
     */
    private function register_meeting_post_type() {
        $labels = array(
            'name' => __('Reuniões', 'haklai-app'),
            'singular_name' => __('Reunião', 'haklai-app'),
            'menu_name' => __('Reuniões', 'haklai-app'),
            'add_new' => __('Adicionar Nova', 'haklai-app'),
            'add_new_item' => __('Adicionar Nova Reunião', 'haklai-app'),
            'edit_item' => __('Editar Reunião', 'haklai-app'),
            'new_item' => __('Nova Reunião', 'haklai-app'),
            'view_item' => __('Ver Reunião', 'haklai-app'),
            'search_items' => __('Buscar Reuniões', 'haklai-app'),
            'not_found' => __('Nenhuma reunião encontrada', 'haklai-app'),
            'not_found_in_trash' => __('Nenhuma reunião encontrada na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => false, // Removido do menu automático
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_meeting', $args);
    }
    
    /**
     * Registra Custom Post Type para Presença
     */
    private function register_attendance_post_type() {
        $labels = array(
            'name' => __('Presenças', 'haklai-app'),
            'singular_name' => __('Presença', 'haklai-app'),
            'menu_name' => __('Presenças', 'haklai-app'),
            'add_new' => __('Adicionar Nova', 'haklai-app'),
            'add_new_item' => __('Adicionar Nova Presença', 'haklai-app'),
            'edit_item' => __('Editar Presença', 'haklai-app'),
            'new_item' => __('Nova Presença', 'haklai-app'),
            'view_item' => __('Ver Presença', 'haklai-app'),
            'search_items' => __('Buscar Presenças', 'haklai-app'),
            'not_found' => __('Nenhuma presença encontrada', 'haklai-app'),
            'not_found_in_trash' => __('Nenhuma presença encontrada na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => false, // Não mostra no admin
            'show_in_menu' => false,
            'query_var' => true,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'supports' => array('title'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_attendance', $args);
    }
    
    /**
     * Registra Custom Post Type para Relatórios
     */
    private function register_report_post_type() {
        $labels = array(
            'name' => __('Relatórios', 'haklai-app'),
            'singular_name' => __('Relatório', 'haklai-app'),
            'menu_name' => __('Relatórios', 'haklai-app'),
            'add_new' => __('Gerar Novo', 'haklai-app'),
            'add_new_item' => __('Gerar Novo Relatório', 'haklai-app'),
            'edit_item' => __('Editar Relatório', 'haklai-app'),
            'new_item' => __('Novo Relatório', 'haklai-app'),
            'view_item' => __('Ver Relatório', 'haklai-app'),
            'search_items' => __('Buscar Relatórios', 'haklai-app'),
            'not_found' => __('Nenhum relatório encontrado', 'haklai-app'),
            'not_found_in_trash' => __('Nenhum relatório encontrado na lixeira', 'haklai-app'),
        );
        
        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => false, // Removido do menu automático
            'query_var' => true,
            'rewrite' => array('slug' => 'relatorio'),
            'capability_type' => 'post',
            'has_archive' => true,
            'hierarchical' => false,
            'supports' => array('title', 'editor'),
            'show_in_rest' => false,
        );
        
        register_post_type('haklai_report', $args);
    }
    
    /**
     * Registra filtros para remover status inválidos das views
     * Corrige warnings de propriedades indefinidas no WordPress
     */
    public function register_post_statuses() {
        // Filtra as views de status para evitar warnings
        add_filter('views_edit-haklai_meeting', array($this, 'filter_meeting_status_views'));
        add_filter('views_edit-haklai_report', array($this, 'filter_report_status_views'));
    }
    
    /**
     * Filtra as views de status para haklai_meeting
     * Remove status inválidos que causam warnings
     */
    public function filter_meeting_status_views($views) {
        if (!is_array($views)) {
            return $views;
        }
        
        // Remove status inválidos que causam warnings
        $invalid_statuses = array('auto-draft', 'inherit', 'request-pending', 'request-confirmed', 'request-failed', 'request-completed');
        
        foreach ($invalid_statuses as $status) {
            if (isset($views[$status])) {
                unset($views[$status]);
            }
        }
        
        return $views;
    }
    
    /**
     * Filtra as views de status para haklai_report
     * Remove status inválidos que causam warnings
     */
    public function filter_report_status_views($views) {
        if (!is_array($views)) {
            return $views;
        }
        
        // Remove status inválidos que causam warnings
        $invalid_statuses = array('auto-draft', 'inherit', 'request-pending', 'request-confirmed', 'request-failed', 'request-completed');
        
        foreach ($invalid_statuses as $status) {
            if (isset($views[$status])) {
                unset($views[$status]);
            }
        }
        
        return $views;
    }
    
    /**
     * Adiciona meta boxes
     */
    public function add_meta_boxes() {
        // Meta box para Membros
        add_meta_box(
            'haklai_member_details',
            __('Detalhes do Membro', 'haklai-app'),
            array($this, 'member_meta_box'),
            'haklai_member',
            'normal',
            'high'
        );
        
        // Meta box para Células
        add_meta_box(
            'haklai_cell_details',
            __('Detalhes da Célula', 'haklai-app'),
            array($this, 'cell_meta_box'),
            'haklai_cell',
            'normal',
            'high'
        );
        
        // Meta box para Reuniões
        add_meta_box(
            'haklai_meeting_details',
            __('Detalhes da Reunião', 'haklai-app'),
            array($this, 'meeting_meta_box'),
            'haklai_meeting',
            'normal',
            'high'
        );
    }
    
    /**
     * Meta box para Membros
     */
    public function member_meta_box($post) {
        wp_nonce_field('haklai_member_meta_box', 'haklai_member_meta_box_nonce');
        
        $email = get_post_meta($post->ID, '_haklai_email', true);
        $phone = get_post_meta($post->ID, '_haklai_phone', true);
        $photo = get_post_meta($post->ID, '_haklai_photo', true);
        $skill = get_post_meta($post->ID, '_haklai_skill', true);
        $leader_id = get_post_meta($post->ID, '_haklai_leader_id', true);
        $baptism_status = get_post_meta($post->ID, '_haklai_baptism_status', true);
        $baptism_date = get_post_meta($post->ID, '_haklai_baptism_date', true);
        $is_visitor = get_post_meta($post->ID, '_haklai_is_visitor', true);
        $role_level = get_post_meta($post->ID, '_haklai_role_level', true);
        $cell_id = get_post_meta($post->ID, '_haklai_cell_id', true);
        $status = get_post_meta($post->ID, '_haklai_status', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="haklai_email"><?php _e('E-mail', 'haklai-app'); ?></label></th>
                <td><input type="email" id="haklai_email" name="haklai_email" value="<?php echo esc_attr($email); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_phone"><?php _e('Telefone', 'haklai-app'); ?></label></th>
                <td><input type="tel" id="haklai_phone" name="haklai_phone" value="<?php echo esc_attr($phone); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_photo"><?php _e('Foto', 'haklai-app'); ?></label></th>
                <td>
                    <input type="url" id="haklai_photo" name="haklai_photo" value="<?php echo esc_attr($photo); ?>" class="regular-text" />
                    <button type="button" class="button" onclick="haklaiSelectImage('haklai_photo')"><?php _e('Selecionar Imagem', 'haklai-app'); ?></button>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_skill"><?php _e('Habilidade', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_skill" name="haklai_skill">
                        <option value=""><?php _e('Selecione uma habilidade', 'haklai-app'); ?></option>
                        <option value="musica" <?php selected($skill, 'musica'); ?>><?php _e('Música', 'haklai-app'); ?></option>
                        <option value="foto" <?php selected($skill, 'foto'); ?>><?php _e('Foto', 'haklai-app'); ?></option>
                        <option value="filmagem" <?php selected($skill, 'filmagem'); ?>><?php _e('Filmagem', 'haklai-app'); ?></option>
                        <option value="teatro" <?php selected($skill, 'teatro'); ?>><?php _e('Teatro', 'haklai-app'); ?></option>
                        <option value="gastronomia" <?php selected($skill, 'gastronomia'); ?>><?php _e('Gastronomia', 'haklai-app'); ?></option>
                        <option value="tecnologia" <?php selected($skill, 'tecnologia'); ?>><?php _e('Tecnologia', 'haklai-app'); ?></option>
                        <option value="recepcao" <?php selected($skill, 'recepcao'); ?>><?php _e('Recepção', 'haklai-app'); ?></option>
                        <option value="som" <?php selected($skill, 'som'); ?>><?php _e('Som e Projeção', 'haklai-app'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_cell_id"><?php _e('Célula', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_cell_id" name="haklai_cell_id">
                        <option value=""><?php _e('Selecione uma célula', 'haklai-app'); ?></option>
                        <?php
                        $cells = get_posts(array(
                            'post_type' => 'haklai_cell',
                            'post_status' => 'publish',
                            'posts_per_page' => -1
                        ));
                        
                        foreach ($cells as $cell) {
                            echo '<option value="' . $cell->ID . '" ' . selected($cell_id, $cell->ID, false) . '>' . esc_html($cell->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr style="background-color: #f0f9ff; border-left: 4px solid #667eea;">
                <th scope="row">
                    <label for="haklai_encontro_deus">
                        <span style="color: #4c51bf; font-weight: 600;">
                            ⭐ <?php _e('Encontro com Deus', 'haklai-app'); ?>
                        </span>
                    </label>
                </th>
                <td>
                    <?php
                    $encontro_deus = get_post_meta($post->ID, '_haklai_encontro_deus', true);
                    $checked = ($encontro_deus === 'sim' || $encontro_deus === '1') ? 'checked' : '';
                    ?>
                    <label class="haklai-toggle-switch">
                        <input type="checkbox" 
                               id="haklai_encontro_deus" 
                               name="haklai_encontro_deus" 
                               value="sim"
                               <?php echo $checked; ?>>
                        <span class="haklai-toggle-slider"></span>
                        <span class="haklai-toggle-label">
                            <span class="toggle-yes" style="<?php echo $checked ? '' : 'display:none;'; ?>">✅ <?php _e('Sim, participou', 'haklai-app'); ?></span>
                            <span class="toggle-no" style="<?php echo $checked ? 'display:none;' : ''; ?>">❌ <?php _e('Ainda não participou', 'haklai-app'); ?></span>
                        </span>
                    </label>
                    <p class="description">
                        <?php _e('Indica se o membro já participou do Encontro com Deus', 'haklai-app'); ?>
                    </p>
                    <script>
                    jQuery(document).ready(function($) {
                        $('#haklai_encontro_deus').on('change', function() {
                            if ($(this).is(':checked')) {
                                $('.toggle-yes').show();
                                $('.toggle-no').hide();
                            } else {
                                $('.toggle-yes').hide();
                                $('.toggle-no').show();
                            }
                        });
                    });
                    </script>
                </td>
            </tr>
            <tr style="background-color: #fffbeb; border-left: 4px solid #f59e0b;">
                <th scope="row">
                    <label>
                        <span style="color: #92400e; font-weight: 600;">
                            🎓 <?php _e('Formações', 'haklai-app'); ?>
                        </span>
                    </label>
                </th>
                <td>
                    <?php
                    $formacoes = get_post_meta($post->ID, '_haklai_formacoes', true);
                    if (!is_array($formacoes)) {
                        $formacoes = array();
                    }
                    
                    $formacoes_list = array(
                        'ctl' => __('Curso de Treinamento de Líderes (CTL)', 'haklai-app'),
                        'cme' => __('Curso de Maturidade Espiritual (CME)', 'haklai-app'),
                        'stp' => __('Seminário Teológico Pastoral (STP)', 'haklai-app')
                    );
                    ?>
                    <fieldset class="haklai-formacoes-fieldset">
                        <legend class="screen-reader-text"><?php _e('Selecione as formações concluídas', 'haklai-app'); ?></legend>
                        
                        <?php foreach ($formacoes_list as $key => $label): ?>
                            <label class="haklai-checkbox-label">
                                <input type="checkbox" 
                                       name="haklai_formacoes[]" 
                                       value="<?php echo esc_attr($key); ?>"
                                       <?php checked(in_array($key, $formacoes)); ?>>
                                <span class="haklai-checkbox-text"><?php echo esc_html($label); ?></span>
                            </label>
                            <br>
                        <?php endforeach; ?>
                    </fieldset>
                    
                    <p class="description" style="margin-top: 10px;">
                        <?php _e('Marque todas as formações que o membro já concluiu', 'haklai-app'); ?>
                    </p>
                    
                    <?php if (!empty($formacoes)): ?>
                    <div style="margin-top: 15px; padding: 10px; background: #f0fdf4; border-radius: 4px; border-left: 3px solid #10b981;">
                        <strong style="color: #047857;">📚 <?php _e('Formações Concluídas:', 'haklai-app'); ?></strong>
                        <ul style="margin: 5px 0 0 20px; color: #065f46;">
                            <?php foreach ($formacoes as $formacao): ?>
                                <?php if (isset($formacoes_list[$formacao])): ?>
                                    <li><?php echo esc_html($formacoes_list[$formacao]); ?></li>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_role_level"><?php _e('Nível Hierárquico', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_role_level" name="haklai_role_level">
                        <option value="0" <?php selected($role_level, '0'); ?>><?php _e('Membro', 'haklai-app'); ?></option>
                        <option value="1" <?php selected($role_level, '1'); ?>><?php _e('Líder de Célula', 'haklai-app'); ?></option>
                        <option value="2" <?php selected($role_level, '2'); ?>><?php _e('Discipulador', 'haklai-app'); ?></option>
                        <option value="3" <?php selected($role_level, '3'); ?>><?php _e('Pastor de Rede', 'haklai-app'); ?></option>
                        <option value="4" <?php selected($role_level, '4'); ?>><?php _e('Pastor Senior', 'haklai-app'); ?></option>
                        <option value="5" <?php selected($role_level, '5'); ?>><?php _e('Pastor Supervisor', 'haklai-app'); ?></option>
                    </select>
                    <p class="description">
                        <?php _e('Membros com nível 1 ou superior terão conta de usuário WordPress criada automaticamente.', 'haklai-app'); ?>
                    </p>
                </td>
            </tr>
            <?php
            // Informações sobre vínculo com usuário WordPress
            $user_id = get_post_meta($post->ID, '_haklai_user_id', true);
            if ($user_id && get_userdata($user_id)) {
                $user = get_userdata($user_id);
                $last_sync = get_post_meta($post->ID, '_haklai_last_sync', true);
                ?>
                <tr style="background-color: #f0f9ff; border-left: 4px solid #3b82f6;">
                    <th scope="row">
                        <span style="color: #1e40af; font-weight: 600;">
                            <?php _e('🔗 Usuário WordPress', 'haklai-app'); ?>
                        </span>
                    </th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span style="display: inline-block; background: #10b981; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                ✓ VINCULADO
                            </span>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #e5e7eb;">
                            <p style="margin: 5px 0;">
                                <strong><?php _e('Usuário:', 'haklai-app'); ?></strong> 
                                <code><?php echo esc_html($user->user_login); ?></code>
                            </p>
                            <p style="margin: 5px 0;">
                                <strong><?php _e('Email:', 'haklai-app'); ?></strong> 
                                <?php echo esc_html($user->user_email); ?>
                            </p>
                            <p style="margin: 5px 0;">
                                <strong><?php _e('Role:', 'haklai-app'); ?></strong> 
                                <span style="background: #dbeafe; color: #1e40af; padding: 2px 8px; border-radius: 4px; font-size: 12px;">
                                    <?php echo esc_html(implode(', ', $user->roles)); ?>
                                </span>
                            </p>
                            <?php if ($last_sync): ?>
                            <p style="margin: 5px 0; color: #6b7280; font-size: 12px;">
                                <strong><?php _e('Última sincronização:', 'haklai-app'); ?></strong> 
                                <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($last_sync))); ?>
                            </p>
                            <?php endif; ?>
                            <p style="margin-top: 10px;">
                                <a href="<?php echo esc_url(get_edit_user_link($user_id)); ?>" class="button button-small" target="_blank">
                                    <?php _e('Editar Usuário', 'haklai-app'); ?>
                                </a>
                            </p>
                        </div>
                    </td>
                </tr>
                <?php
            } elseif ($role_level >= 1) {
                ?>
                <tr style="background-color: #fffbeb; border-left: 4px solid #f59e0b;">
                    <th scope="row">
                        <span style="color: #92400e; font-weight: 600;">
                            <?php _e('⚠️ Usuário WordPress', 'haklai-app'); ?>
                        </span>
                    </th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                            <span style="display: inline-block; background: #f59e0b; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                SERÁ CRIADO
                            </span>
                        </div>
                        <div style="background: white; padding: 15px; border-radius: 4px; border: 1px solid #fbbf24;">
                            <p style="margin: 0; color: #92400e;">
                                <?php _e('Este membro tem nível de liderança. Ao salvar, um usuário WordPress será criado automaticamente e as credenciais serão enviadas por email.', 'haklai-app'); ?>
                            </p>
                            <?php if (empty($email)): ?>
                            <p style="margin: 10px 0 0 0; color: #dc2626; font-weight: 600;">
                                ⚠️ <?php _e('Atenção: É necessário preencher o email para criar o usuário!', 'haklai-app'); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php
            }
            ?>
            <tr>
                <th scope="row"><label for="haklai_baptism_status"><?php _e('Status de Batismo', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_baptism_status" name="haklai_baptism_status">
                        <option value="visitor" <?php selected($baptism_status, 'visitor'); ?>><?php _e('Visitante', 'haklai-app'); ?></option>
                        <option value="frequent_visitor" <?php selected($baptism_status, 'frequent_visitor'); ?>><?php _e('Frequentador Assíduo', 'haklai-app'); ?></option>
                        <option value="member" <?php selected($baptism_status, 'member'); ?>><?php _e('Membro Batizado', 'haklai-app'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_baptism_date"><?php _e('Data do Batismo', 'haklai-app'); ?></label></th>
                <td><input type="date" id="haklai_baptism_date" name="haklai_baptism_date" value="<?php echo esc_attr($baptism_date); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_status"><?php _e('Status', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_status" name="haklai_status">
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('Ativo', 'haklai-app'); ?></option>
                        <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inativo', 'haklai-app'); ?></option>
                        <option value="transferred" <?php selected($status, 'transferred'); ?>><?php _e('Transferido', 'haklai-app'); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><?php _e('É Visitante', 'haklai-app'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="haklai_is_visitor" value="1" <?php checked($is_visitor, '1'); ?> />
                        <?php _e('Marcar como visitante', 'haklai-app'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Meta box para Células
     */
    public function cell_meta_box($post) {
        wp_nonce_field('haklai_cell_meta_box', 'haklai_cell_meta_box_nonce');
        
        $address = get_post_meta($post->ID, '_haklai_address', true);
        $discipler_id = get_post_meta($post->ID, '_haklai_discipulador_id', true);
        $network_id = get_post_meta($post->ID, '_haklai_network_id', true);
        $status = get_post_meta($post->ID, '_haklai_status', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="haklai_address"><?php _e('Endereço', 'haklai-app'); ?></label></th>
                <td><textarea id="haklai_address" name="haklai_address" rows="3" cols="50"><?php echo esc_textarea($address); ?></textarea></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_discipulador_id"><?php _e('Discipulador', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_discipulador_id" name="haklai_discipulador_id">
                        <option value=""><?php _e('Selecione um discipulador', 'haklai-app'); ?></option>
                        <?php
                        $discipuladores = get_posts(array(
                            'post_type' => 'haklai_member',
                            'post_status' => 'publish',
                            'meta_query' => array(
                                array(
                                    'key' => '_haklai_role_level',
                                    'value' => '2',
                                    'compare' => '>='
                                )
                            ),
                            'posts_per_page' => -1
                        ));
                        
                        foreach ($discipuladores as $discipulador) {
                            echo '<option value="' . $discipulador->ID . '" ' . selected($discipler_id, $discipulador->ID, false) . '>' . esc_html($discipulador->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_network_id"><?php _e('ID da Rede', 'haklai-app'); ?></label></th>
                <td><input type="number" id="haklai_network_id" name="haklai_network_id" value="<?php echo esc_attr($network_id); ?>" class="small-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_status"><?php _e('Status', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_status" name="haklai_status">
                        <option value="active" <?php selected($status, 'active'); ?>><?php _e('Ativa', 'haklai-app'); ?></option>
                        <option value="inactive" <?php selected($status, 'inactive'); ?>><?php _e('Inativa', 'haklai-app'); ?></option>
                    </select>
                </td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Meta box para Reuniões
     */
    public function meeting_meta_box($post) {
        wp_nonce_field('haklai_meeting_meta_box', 'haklai_meeting_meta_box_nonce');
        
        $cell_id = get_post_meta($post->ID, '_haklai_cell_id', true);
        $meeting_date = get_post_meta($post->ID, '_haklai_meeting_date', true);
        $host = get_post_meta($post->ID, '_haklai_host', true);
        $address = get_post_meta($post->ID, '_haklai_address', true);
        
        ?>
        <table class="form-table">
            <tr>
                <th scope="row"><label for="haklai_cell_id"><?php _e('Célula', 'haklai-app'); ?></label></th>
                <td>
                    <select id="haklai_cell_id" name="haklai_cell_id">
                        <option value=""><?php _e('Selecione uma célula', 'haklai-app'); ?></option>
                        <?php
                        $cells = get_posts(array(
                            'post_type' => 'haklai_cell',
                            'post_status' => 'publish',
                            'posts_per_page' => -1
                        ));
                        
                        foreach ($cells as $cell) {
                            echo '<option value="' . $cell->ID . '" ' . selected($cell_id, $cell->ID, false) . '>' . esc_html($cell->post_title) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_meeting_date"><?php _e('Data da Reunião', 'haklai-app'); ?></label></th>
                <td><input type="date" id="haklai_meeting_date" name="haklai_meeting_date" value="<?php echo esc_attr($meeting_date); ?>" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_host"><?php _e('Anfitrião', 'haklai-app'); ?></label></th>
                <td><input type="text" id="haklai_host" name="haklai_host" value="<?php echo esc_attr($host); ?>" class="regular-text" /></td>
            </tr>
            <tr>
                <th scope="row"><label for="haklai_address"><?php _e('Endereço', 'haklai-app'); ?></label></th>
                <td><textarea id="haklai_address" name="haklai_address" rows="3" cols="50"><?php echo esc_textarea($address); ?></textarea></td>
            </tr>
        </table>
        <?php
    }
    
    /**
     * Salva meta boxes
     */
    public function save_meta_boxes($post_id) {
        // Verifica nonce e permissões
        if (!isset($_POST['post_type'])) {
            return;
        }
        
        $post_type = $_POST['post_type'];
        
        if (!in_array($post_type, array('haklai_member', 'haklai_cell', 'haklai_meeting'))) {
            return;
        }
        
        $nonce_name = $post_type . '_meta_box_nonce';
        $nonce_action = $post_type . '_meta_box';
        
        if (!isset($_POST[$nonce_name]) || !wp_verify_nonce($_POST[$nonce_name], $nonce_action)) {
            return;
        }
        
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Salva campos específicos por tipo
        switch ($post_type) {
            case 'haklai_member':
                $this->save_member_meta($post_id);
                break;
            case 'haklai_cell':
                $this->save_cell_meta($post_id);
                break;
            case 'haklai_meeting':
                $this->save_meeting_meta($post_id);
                break;
        }
    }
    
    /**
     * Salva meta dados do membro
     */
    private function save_member_meta($post_id) {
        $fields = array(
            '_haklai_email' => 'email',
            '_haklai_phone' => 'text',
            '_haklai_photo' => 'url',
            '_haklai_skill' => 'text',
            '_haklai_leader_id' => 'int',
            '_haklai_baptism_status' => 'text',
            '_haklai_baptism_date' => 'date',
            '_haklai_role_level' => 'int',
            '_haklai_cell_id' => 'int',
            '_haklai_status' => 'text',
            '_haklai_is_visitor' => 'checkbox'
        );
        
        foreach ($fields as $meta_key => $type) {
            if (isset($_POST[str_replace('_haklai_', 'haklai_', $meta_key)])) {
                $value = $_POST[str_replace('_haklai_', 'haklai_', $meta_key)];
                
                switch ($type) {
                    case 'email':
                        $value = sanitize_email($value);
                        break;
                    case 'url':
                        $value = esc_url_raw($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'date':
                        $value = sanitize_text_field($value);
                        break;
                    case 'text':
                        $value = sanitize_text_field($value);
                        break;
                    case 'checkbox':
                        $value = isset($_POST[str_replace('_haklai_', 'haklai_', $meta_key)]) ? '1' : '0';
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            }
        }
        
        // Salva Encontro com Deus
        if (isset($_POST['haklai_encontro_deus'])) {
            update_post_meta($post_id, '_haklai_encontro_deus', 'sim');
        } else {
            update_post_meta($post_id, '_haklai_encontro_deus', 'nao');
        }
        
        // Salva Formações
        if (isset($_POST['haklai_formacoes']) && is_array($_POST['haklai_formacoes'])) {
            $formacoes = array_map('sanitize_text_field', $_POST['haklai_formacoes']);
            update_post_meta($post_id, '_haklai_formacoes', $formacoes);
        } else {
            delete_post_meta($post_id, '_haklai_formacoes');
        }
        
        // Atualiza timestamp de modificação
        update_post_meta($post_id, '_haklai_updated_at', current_time('mysql'));
    }
    
    /**
     * Salva meta dados da célula
     */
    private function save_cell_meta($post_id) {
        $fields = array(
            '_haklai_address' => 'textarea',
            '_haklai_discipulador_id' => 'int',
            '_haklai_network_id' => 'int',
            '_haklai_status' => 'text'
        );
        
        foreach ($fields as $meta_key => $type) {
            if (isset($_POST[str_replace('_haklai_', 'haklai_', $meta_key)])) {
                $value = $_POST[str_replace('_haklai_', 'haklai_', $meta_key)];
                
                switch ($type) {
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'text':
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
    
    /**
     * Salva meta dados da reunião
     */
    private function save_meeting_meta($post_id) {
        $fields = array(
            '_haklai_cell_id' => 'int',
            '_haklai_meeting_date' => 'date',
            '_haklai_host' => 'text',
            '_haklai_address' => 'textarea'
        );
        
        foreach ($fields as $meta_key => $type) {
            if (isset($_POST[str_replace('_haklai_', 'haklai_', $meta_key)])) {
                $value = $_POST[str_replace('_haklai_', 'haklai_', $meta_key)];
                
                switch ($type) {
                    case 'textarea':
                        $value = sanitize_textarea_field($value);
                        break;
                    case 'int':
                        $value = intval($value);
                        break;
                    case 'date':
                        $value = sanitize_text_field($value);
                        break;
                    case 'text':
                        $value = sanitize_text_field($value);
                        break;
                }
                
                update_post_meta($post_id, $meta_key, $value);
            }
        }
    }
    
    /**
     * FASE 4: Garante que novos posts tenham tenant_id
     * 
     * @param int $post_id ID do post
     * @param WP_Post $post Objeto do post
     * @param bool $update Se é atualização
     */
    public function ensure_tenant_id_on_insert($post_id, $post, $update) {
        // Apenas para CPTs do Haklai
        if (!in_array($post->post_type, array('haklai_member', 'haklai_cell', 'haklai_network', 'haklai_meeting'))) {
            return;
        }
        
        // Previne loop
        if (defined('HAKLAI_SETTING_TENANT_ID')) {
            return;
        }
        
        define('HAKLAI_SETTING_TENANT_ID', true);
        
        // Verifica se já tem tenant_id válido
        $existing_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
        if ($existing_tenant_id && intval($existing_tenant_id) > 0) {
            return; // Já tem tenant_id válido
        }
        
        // Obtém tenant atual
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $current_tenant_id = $tenant_manager->get_current_tenant_id();
            
            if ($current_tenant_id) {
                update_post_meta($post_id, '_haklai_tenant_id', $current_tenant_id);
            } else {
                // Se não houver tenant atual, cria/usa o padrão
                $default_tenant_id = $tenant_manager->create_default_tenant();
                if ($default_tenant_id) {
                    update_post_meta($post_id, '_haklai_tenant_id', $default_tenant_id);
                }
            }
        }
    }
}

