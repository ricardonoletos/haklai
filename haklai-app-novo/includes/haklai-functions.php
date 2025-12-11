<?php
/**
 * Funções utilitárias do Haklai App
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
 * Verifica se o usuário tem acesso a uma funcionalidade
 * 
 * @param string $capability Capability a ser verificada
 * @param int $user_id ID do usuário (opcional)
 * @return bool
 */
function haklai_user_can_access($capability, $user_id = null) {
    return Haklai_Permissions::user_can_access($capability, $user_id);
}

/**
 * Obtém o nível hierárquico do usuário
 * 
 * @param int $user_id ID do usuário (opcional)
 * @return int
 */
function haklai_get_user_level($user_id = null) {
    return Haklai_Permissions::get_user_level($user_id);
}

/**
 * Obtém nome do role baseado no nível
 * 
 * @param int $level Nível hierárquico
 * @return string
 */
function haklai_get_role_name($level) {
    return Haklai_Permissions::get_role_name($level);
}

/**
 * Obtém células do usuário baseado no seu nível
 * 
 * @param int $user_id ID do usuário (opcional)
 * @return array
 */
function haklai_get_user_cells($user_id = null) {
    return Haklai_Permissions::get_user_cells($user_id);
}

/**
 * Verifica se o usuário pode gerenciar múltiplas células
 * 
 * @param int $user_id ID do usuário (opcional)
 * @return bool
 */
function haklai_can_manage_multiple_cells($user_id = null) {
    return Haklai_Permissions::can_manage_multiple_cells($user_id);
}

/**
 * Verifica se o usuário tem acesso a todas as células
 * 
 * @param int $user_id ID do usuário (opcional)
 * @return bool
 */
function haklai_can_access_all_cells($user_id = null) {
    return Haklai_Permissions::can_access_all_cells($user_id);
}

/**
 * Obtém membros de uma célula
 * 
 * @param int $cell_id ID da célula
 * @param string $status Status dos membros (opcional)
 * @return array
 */
function haklai_get_cell_members($cell_id, $status = 'active') {
    $args = array(
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
                'value' => $status,
                'compare' => '='
            )
        ),
        'orderby' => 'title',
        'order' => 'ASC'
    );
    
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
            'cell_id' => $cell_id,
        );
    }
    
    return $formatted_members;
}

/**
 * Obtém presença de uma reunião
 * 
 * @param int $meeting_id ID da reunião
 * @return array
 */
function haklai_get_meeting_attendance($meeting_id) {
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
 * 
 * @param array $members Lista de membros
 * @param array $attendance Lista de presenças
 * @return array
 */
function haklai_calculate_presence_stats($members, $attendance) {
    $total_members = count($members);
    $present_members = 0;
    
    foreach ($attendance as $att) {
        if ($att['is_present']) {
            $present_members++;
        }
    }
    
    $absent_members = $total_members - $present_members;
    $presence_rate = $total_members > 0 ? round(($present_members / $total_members) * 100, 1) : 0;
    
    return array(
        'total_members' => $total_members,
        'present_members' => $present_members,
        'absent_members' => $absent_members,
        'presence_rate' => $presence_rate,
    );
}

/**
 * Obtém histórico de presenças de um membro (últimas 6 reuniões)
 * 
 * @param int $member_id ID do membro
 * @param int $cell_id ID da célula (opcional)
 * @return array
 */
function haklai_get_member_attendance_history($member_id, $cell_id = null) {
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
                ),
                array(
                    'key' => '_haklai_member_id',
                    'value' => $member_id,
                    'compare' => '='
                )
            )
        );
        
        $attendance = get_posts($attendance_args);
        $is_present = !empty($attendance) && get_post_meta($attendance[0]->ID, '_haklai_is_present', true) === '1';
        
        $history[] = array(
            'meeting_id' => $meeting->ID,
            'meeting_date' => $meeting->post_date,
            'meeting_title' => $meeting->post_title,
            'is_present' => $is_present
        );
    }
    
    return $history;
}

/**
 * Obtém estatísticas gerais do sistema
 * 
 * @param array $cell_ids IDs das células (opcional)
 * @return array
 */
function haklai_get_system_statistics($cell_ids = null) {
    // Conta membros
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
    
    if ($cell_ids) {
        $members_args['meta_query'][] = array(
            'key' => '_haklai_cell_id',
            'value' => $cell_ids,
            'compare' => 'IN'
        );
    }
    
    $total_members = count(get_posts($members_args));
    
    // Conta células
    $cells_args = array(
        'post_type' => 'haklai_cell',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    
    if ($cell_ids) {
        $cells_args['post__in'] = $cell_ids;
    }
    
    $total_cells = count(get_posts($cells_args));
    
    // Conta reuniões do último mês
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
    
    if ($cell_ids) {
        $meetings_args['meta_query'] = array(
            array(
                'key' => '_haklai_cell_id',
                'value' => $cell_ids,
                'compare' => 'IN'
            )
        );
    }
    
    $total_meetings = count(get_posts($meetings_args));
    
    // Conta batismos do último mês
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
    
    if ($cell_ids) {
        $baptisms_args['meta_query'][] = array(
            'key' => '_haklai_cell_id',
            'value' => $cell_ids,
            'compare' => 'IN'
        );
    }
    
    $total_baptisms = count(get_posts($baptisms_args));
    
    return array(
        'total_members' => $total_members,
        'total_cells' => $total_cells,
        'total_meetings' => $total_meetings,
        'total_baptisms' => $total_baptisms,
    );
}

/**
 * Gera relatório HTML de uma reunião
 * 
 * @param int $meeting_id ID da reunião
 * @return string HTML do relatório
 */
function haklai_generate_meeting_report_html($meeting_id) {
    // Obtém dados da reunião
    $meeting = get_post($meeting_id);
    $cell_id = get_post_meta($meeting_id, '_haklai_cell_id', true);
    $cell = get_post($cell_id);
    
    // Obtém presenças
    $attendances = haklai_get_meeting_attendance($meeting_id);
    
    // Obtém membros da célula
    $members = haklai_get_cell_members($cell_id);
    
    // Calcula estatísticas
    $stats = haklai_calculate_presence_stats($members, $attendances);
    
    // Gera HTML do relatório
    ob_start();
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Relatório de Reunião - <?php echo esc_html($cell->post_title); ?></title>
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
            <h2><?php echo esc_html($cell->post_title); ?></h2>
            <p>Data: <?php echo date('d/m/Y', strtotime($meeting->post_date)); ?></p>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['total_members']; ?></div>
                <div>Total de Membros</div>
            </div>
            <div class="stat-item">
                <div class="stat-number present"><?php echo $stats['present_members']; ?></div>
                <div>Presentes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number absent"><?php echo $stats['absent_members']; ?></div>
                <div>Ausentes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $stats['presence_rate']; ?>%</div>
                <div>Taxa de Presença</div>
            </div>
        </div>
        
        <div class="members-list">
            <h3>Lista de Membros</h3>
            <?php foreach ($members as $member): ?>
                <?php
                $is_present = false;
                foreach ($attendances as $attendance) {
                    if ($attendance['member_id'] == $member['id']) {
                        $is_present = $attendance['is_present'];
                        break;
                    }
                }
                ?>
                <div class="member-item">
                    <strong><?php echo esc_html($member['name']); ?></strong>
                    <span class="<?php echo $is_present ? 'present' : 'absent'; ?>">
                        - <?php echo $is_present ? 'Presente' : 'Ausente'; ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

/**
 * Log de eventos do sistema
 * 
 * @param string $action Ação realizada
 * @param string $message Mensagem do log
 * @param array $data Dados adicionais (opcional)
 * @return void
 */
function haklai_log_event($action, $message, $data = array()) {
    $log_data = array(
        'action' => $action,
        'message' => $message,
        'timestamp' => current_time('mysql'),
        'user_id' => get_current_user_id(),
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        'data' => $data
    );
    
    // Salva no log do WordPress
    error_log('Haklai App: ' . $action . ' - ' . $message . ' - ' . json_encode($log_data));
    
    // Salva em arquivo de log
    $log_file = HAKLAI_UPLOAD_DIR . 'events.log';
    if (file_exists(HAKLAI_UPLOAD_DIR)) {
        file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
    }
}

/**
 * Obtém configurações do plugin
 * 
 * @param string $key Chave da configuração (opcional)
 * @return mixed
 */
function haklai_get_option($key = null) {
    $options = get_option('haklai_settings', array());
    
    if ($key === null) {
        return $options;
    }
    
    return isset($options[$key]) ? $options[$key] : null;
}

/**
 * Define configurações do plugin
 * 
 * @param string $key Chave da configuração
 * @param mixed $value Valor da configuração
 * @return bool
 */
function haklai_set_option($key, $value) {
    $options = haklai_get_option();
    $options[$key] = $value;
    
    return update_option('haklai_settings', $options);
}

/**
 * Obtém cores da igreja
 * 
 * @return array
 */
function haklai_get_church_colors() {
    return get_option('haklai_colors', array(
        'church_primary' => '#667eea',
        'church_secondary' => '#10b981',
        'church_accent' => '#f59e0b',
    ));
}

/**
 * Define cores da igreja
 * 
 * @param array $colors Array de cores
 * @return bool
 */
function haklai_set_church_colors($colors) {
    return update_option('haklai_colors', $colors);
}

/**
 * Verifica se o plugin está em modo de desenvolvimento
 * 
 * @return bool
 */
function haklai_is_development_mode() {
    return defined('WP_DEBUG') && WP_DEBUG;
}

/**
 * Obtém URL de um asset do plugin
 * 
 * @param string $path Caminho do asset
 * @return string
 */
function haklai_get_asset_url($path) {
    return HAKLAI_ASSETS_URL . ltrim($path, '/');
}

/**
 * Obtém caminho de um asset do plugin
 * 
 * @param string $path Caminho do asset
 * @return string
 */
function haklai_get_asset_path($path) {
    return HAKLAI_PLUGIN_PATH . 'assets/' . ltrim($path, '/');
}

/**
 * Formata telefone brasileiro
 * 
 * @param string $phone Telefone para formatar
 * @return string
 */
function haklai_format_phone($phone) {
    $phone = preg_replace('/\D/', '', $phone);
    
    if (strlen($phone) == 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone);
    } elseif (strlen($phone) == 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone);
    }
    
    return $phone;
}

/**
 * Valida email
 * 
 * @param string $email Email para validar
 * @return bool
 */
function haklai_validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Gera slug único para um post
 * 
 * @param string $title Título para gerar slug
 * @param string $post_type Tipo do post
 * @return string
 */
function haklai_generate_unique_slug($title, $post_type = 'post') {
    $slug = sanitize_title($title);
    $original_slug = $slug;
    $counter = 1;
    
    while (get_page_by_path($slug, OBJECT, $post_type)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }
    
    return $slug;
}

/**
 * Obtém informações de versão do plugin
 * 
 * @return array
 */
function haklai_get_version_info() {
    return array(
        'version' => HAKLAI_VERSION,
        'db_version' => HAKLAI_DB_VERSION,
        'plugin_file' => HAKLAI_PLUGIN_FILE,
        'plugin_url' => HAKLAI_PLUGIN_URL,
        'plugin_path' => HAKLAI_PLUGIN_PATH,
    );
}

/**
 * Verifica se uma funcionalidade está habilitada
 * 
 * @param string $feature Nome da funcionalidade
 * @return bool
 */
function haklai_is_feature_enabled($feature) {
    $enabled_features = haklai_get_option('enabled_features') ?: array();
    return in_array($feature, $enabled_features);
}

/**
 * Habilita uma funcionalidade
 * 
 * @param string $feature Nome da funcionalidade
 * @return bool
 */
function haklai_enable_feature($feature) {
    $enabled_features = haklai_get_option('enabled_features') ?: array();
    
    if (!in_array($feature, $enabled_features)) {
        $enabled_features[] = $feature;
        return haklai_set_option('enabled_features', $enabled_features);
    }
    
    return true;
}

/**
 * Desabilita uma funcionalidade
 * 
 * @param string $feature Nome da funcionalidade
 * @return bool
 */
function haklai_disable_feature($feature) {
    $enabled_features = haklai_get_option('enabled_features') ?: array();
    $key = array_search($feature, $enabled_features);
    
    if ($key !== false) {
        unset($enabled_features[$key]);
        return haklai_set_option('enabled_features', array_values($enabled_features));
    }
    
    return true;
}

