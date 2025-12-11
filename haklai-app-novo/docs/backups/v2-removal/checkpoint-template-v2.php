<?php
/**
 * Template V2 do Checkpoint - Design System Moderno com Tailwind CSS
 * Baseado em checkpoint2.html
 * 
 * Projeto: Haklai Church — Plugin WordPress Modular
 * Desenvolvido por: Ricardo Sarmento - https://linx.pt
 */

// Previne acesso direto
if (!defined('ABSPATH')) {
    exit;
}

// Verifica se há erro
if (isset($error)) {
    echo '<div class="haklai-card haklai-text-danger">' . esc_html($error) . '</div>';
    return;
}

// Prepara dados de presença
$attendance_map = array();
foreach ($attendance as $att) {
    $member_id = isset($att['member_id']) ? $att['member_id'] : 0;
    $is_present = isset($att['is_present']) ? $att['is_present'] : false;
    $attendance_map[$member_id] = $is_present;
}

// Enqueue Tailwind CSS e Material Symbols (se não estiverem já carregados)
if (!wp_script_is('tailwindcss', 'enqueued')) {
    wp_enqueue_script('tailwindcss', 'https://cdn.tailwindcss.com?plugins=forms,container-queries', array(), null, false);
}

if (!wp_style_is('material-symbols', 'enqueued')) {
    wp_enqueue_style('material-symbols', 'https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200', array(), null);
}

if (!wp_style_is('poppins', 'enqueued')) {
    wp_enqueue_style('poppins', 'https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap', array(), null);
}
?>

<script>
// Configuração do Tailwind CSS
if (typeof tailwind !== 'undefined') {
    tailwind.config = {
        darkMode: "class",
        theme: {
            extend: {
                colors: {
                    primary: "#2563EB",
                    secondary: "#3B82F6",
                    "background-light": "#F8FAFC",
                    "background-dark": "#18181B",
                    "text-primary-light": "#3F3F46",
                    "text-primary-dark": "#E4E4E7",
                    "text-secondary-light": "#71717A",
                    "text-secondary-dark": "#A1A1AA",
                    "border-light": "#E4E4E7",
                    "border-dark": "#3F3F46",
                    "card-light": "#FFFFFF",
                    "card-dark": "#27272A",
                    success: "#10B981",
                    danger: "#EF4444",
                    warning: "#F59E0B"
                },
                fontFamily: {
                    display: ["Poppins", "sans-serif"],
                },
                borderRadius: {
                    DEFAULT: "0.75rem",
                    lg: "1rem",
                    xl: "1.25rem",
                    full: "9999px"
                },
            },
        },
    };
}
</script>

<style>
.material-symbols-outlined {
    font-variation-settings:
    'FILL' 1,
    'wght' 400,
    'GRAD' 0,
    'opsz' 24
}

body.haklai-checkpoint-v2-body {
    min-height: max(884px, 100dvh);
}

.image-upload-container {
    position: relative;
}

.image-preview {
    position: relative;
    display: inline-block;
    margin-bottom: 10px;
}

.invalid-feedback {
    display: block;
    color: #EF4444;
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

.invalid-feedback.hidden {
    display: none;
}

.phone-input.is-invalid {
    border-color: #EF4444 !important;
}

.phone-input.is-valid {
    border-color: #10B981 !important;
}

.member-card {
    transition: all 0.2s ease;
    cursor: pointer;
}

.member-card:hover {
    transform: translateY(-2px);
}

#searchResults {
    z-index: 50;
}

#searchResults > div {
    border-bottom: 1px solid var(--border-light);
    transition: background-color 0.2s;
}

#searchResults > div:last-child {
    border-bottom: none;
}

.hidden {
    display: none !important;
}
</style>

<div class="relative flex min-h-screen w-full flex-col bg-background-light dark:bg-background-dark font-display text-text-primary-light dark:text-text-primary-dark">
    <!-- Header com fundo azul primário -->
    <header class="sticky top-0 z-10 bg-primary px-4 pb-4 pt-3 text-white shadow-md">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold"><?php _e('Checkpoint de Célula', 'haklai-app'); ?></h1>
                <p class="text-xs opacity-90 mt-0.5"><?php echo esc_html($cell_info['name'] ?? __('Célula', 'haklai-app')); ?></p>
            </div>
            <?php 
            $avatar_url = get_avatar_url(get_current_user_id(), array('size' => 40));
            if ($avatar_url) : ?>
                <img alt="<?php esc_attr_e('Avatar do usuário', 'haklai-app'); ?>" 
                     class="h-10 w-10 rounded-full object-cover border-2 border-white/30" 
                     src="<?php echo esc_url($avatar_url); ?>">
            <?php endif; ?>
        </div>
        <p class="mt-1 text-sm opacity-90"><?php _e('Resumo de atividade', 'haklai-app'); ?></p>
    </header>

    <main class="flex-1 pb-24">
        <!-- SEÇÃO: INÍCIO (Dashboard) -->
        <section id="section-inicio" class="page-section">
            <!-- Indicador de progresso -->
            <div class="flex w-full flex-row items-center justify-center gap-2 py-4">
                <div class="h-1.5 w-6 bg-border-light dark:bg-border-dark rounded-full"></div>
                <div class="h-1.5 w-6 bg-border-light dark:bg-border-dark rounded-full"></div>
                <div class="h-1.5 w-6 bg-primary rounded-full"></div>
            </div>

            <!-- Controles da Reunião -->
            <div class="p-4">
                <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4"><?php _e('Controles da Reunião', 'haklai-app'); ?></h2>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Data da Reunião', 'haklai-app'); ?></label>
                        <input type="date" 
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                               id="meetingDate" 
                               value="<?php echo esc_attr($meeting['meeting_date'] ?? date('Y-m-d')); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Anfitrião', 'haklai-app'); ?></label>
                        <div class="relative">
                            <input type="text" 
                                   class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                                   id="anfitriaoSearch" 
                                   placeholder="<?php esc_attr_e('Buscar membro...', 'haklai-app'); ?>" 
                                   value="<?php echo esc_attr($meeting['host'] ?? ''); ?>"
                                   autocomplete="off">
                            <div id="searchResults" class="hidden absolute z-50 w-full mt-1 bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark rounded-lg shadow-lg max-h-60 overflow-y-auto"></div>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Endereço da Célula', 'haklai-app'); ?></label>
                        <input type="text" 
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                               id="cellAddress" 
                               value="<?php echo esc_attr($cell_info['address'] ?? ''); ?>">
                    </div>
                </div>
            </div>

            <!-- Dashboard Overview -->
            <div class="p-4">
                <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark"><?php _e('Dashboard Overview', 'haklai-app'); ?></h2>
                <div class="mt-3 grid grid-cols-2 gap-4">
                    <div class="flex flex-col border border-border-light bg-card-light p-4 rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-primary text-2xl">groups</span>
                        <p class="mt-2 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark stat-total-members"><?php echo esc_html($stats['total_members'] ?? 0); ?></p>
                        <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Total de Membros', 'haklai-app'); ?></p>
                    </div>
                    <div class="flex flex-col border border-border-light bg-card-light p-4 rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-primary text-2xl">person_check</span>
                        <p class="mt-2 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark stat-frequent-visitors"><?php echo esc_html($stats['frequent_visitors'] ?? 0); ?></p>
                        <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark"><?php _e('F.A.', 'haklai-app'); ?></p>
                    </div>
                    <div class="flex flex-col border border-border-light bg-card-light p-4 rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-primary text-2xl">event_available</span>
                        <p class="mt-2 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark stat-last-meeting"><?php echo esc_html($stats['last_meeting_present'] ?? 0); ?></p>
                        <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Na Última Célula', 'haklai-app'); ?></p>
                    </div>
                    <div class="flex flex-col border border-border-light bg-card-light p-4 rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark hover:shadow-md transition-shadow">
                        <span class="material-symbols-outlined text-primary text-2xl">trending_up</span>
                        <p class="mt-2 text-3xl font-bold text-text-primary-light dark:text-text-primary-dark stat-presence-rate"><?php echo esc_html($stats['presence_rate'] ?? 0); ?>%</p>
                        <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Taxa de Presença', 'haklai-app'); ?></p>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="p-4 flex flex-wrap gap-3">
                <button onclick="openAddVisitorModal()" class="flex-1 min-w-[140px] px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors font-medium flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">person_add</span>
                    <span><?php _e('Adicionar Visitante', 'haklai-app'); ?></span>
                </button>
                <button onclick="openAddMemberModal()" class="flex-1 min-w-[140px] px-4 py-2 bg-secondary text-white rounded-lg hover:bg-primary transition-colors font-medium flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">person_add_alt</span>
                    <span><?php _e('Novo Membro', 'haklai-app'); ?></span>
                </button>
                <button onclick="sendReport()" class="flex-1 min-w-[140px] px-4 py-2 bg-success text-white rounded-lg hover:bg-green-600 transition-colors font-medium flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">send</span>
                    <span><?php _e('Enviar Relatório', 'haklai-app'); ?></span>
                </button>
            </div>
        </section>

        <!-- Presence Check-in -->
        <section class="px-4 pt-4">
            <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark"><?php _e('Membros da Célula', 'haklai-app'); ?></h2>
            <div class="mt-3 flex flex-col gap-3" id="membersList">
                <?php foreach ($members as $member): ?>
                    <?php 
                    $is_present = isset($attendance_map[$member['id']]) ? $attendance_map[$member['id']] : false;
                    $member_class = $is_present ? 'present' : 'absent';
                    $photo_url = !empty($member['photo']) ? $member['photo'] : 'https://via.placeholder.com/80x80/2563EB/ffffff?text=' . urlencode(mb_substr($member['name'], 0, 2));
                    ?>
                    <div class="flex items-center border border-border-light bg-card-light rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark hover:shadow-md transition-shadow cursor-pointer member-card <?php echo esc_attr($member_class); ?>" 
                         data-member-id="<?php echo esc_attr($member['id']); ?>"
                         data-baptism-status="<?php echo esc_attr($member['baptism_status'] ?? 'visitor'); ?>"
                         onclick="togglePresence(this)">
                        <img alt="<?php echo esc_attr($member['name']); ?>" 
                             class="h-20 w-20 flex-shrink-0 object-cover rounded-l-lg" 
                             src="<?php echo esc_url($photo_url); ?>">
                        <div class="flex-1 px-4">
                            <p class="font-semibold text-text-primary-light dark:text-text-primary-dark"><?php echo esc_html($member['name']); ?></p>
                            <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark mb-1"><?php echo esc_html(haklai_get_role_name($member['role_level'] ?? 0)); ?></p>
                            <p class="text-sm <?php echo $is_present ? 'text-success' : 'text-danger'; ?> font-medium">
                                <?php echo $is_present ? __('Presente', 'haklai-app') : __('Ausente', 'haklai-app'); ?>
                            </p>
                        </div>
                        <button type="button" 
                                class="mr-2 p-2 text-text-secondary-light dark:text-text-secondary-dark hover:text-primary transition-colors"
                                onclick="event.stopPropagation(); editMember(<?php echo esc_js($member['id']); ?>)"
                                title="<?php esc_attr_e('Editar Membro', 'haklai-app'); ?>">
                            <span class="material-symbols-outlined text-xl">edit</span>
                        </button>
                        <div class="w-2 self-stretch <?php echo $is_present ? 'bg-success' : 'bg-danger'; ?> rounded-r-lg"></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- SEÇÃO: MEMBROS -->
        <section id="section-membros" class="page-section hidden">
            <div class="p-4">
                <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4"><?php _e('Membros', 'haklai-app'); ?></h2>
                
                <!-- Submenu de Ações -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <button onclick="showAddMembro()" class="flex flex-col items-center justify-center bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark p-6 rounded-lg shadow-sm hover:shadow-md hover:bg-primary/5 dark:hover:bg-primary/10 transition-all">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2">person_add</span>
                        <span class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark"><?php _e('Adicionar Membro', 'haklai-app'); ?></span>
                    </button>
                    <button onclick="showAddVisitante()" class="flex flex-col items-center justify-center bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark p-6 rounded-lg shadow-sm hover:shadow-md hover:bg-primary/5 dark:hover:bg-primary/10 transition-all">
                        <span class="material-symbols-outlined text-primary text-4xl mb-2">person_add_alt</span>
                        <span class="text-sm font-semibold text-text-primary-light dark:text-text-primary-dark"><?php _e('Adicionar Visitante', 'haklai-app'); ?></span>
                    </button>
                </div>

                <!-- Lista de Membros -->
                <div class="space-y-3" id="membersListSection">
                    <?php foreach ($members as $member): ?>
                        <?php 
                        $photo_url = !empty($member['photo']) ? $member['photo'] : 'https://via.placeholder.com/80x80/2563EB/ffffff?text=' . urlencode(mb_substr($member['name'], 0, 2));
                        ?>
                        <div class="flex items-center border border-border-light bg-card-light rounded-lg shadow-sm dark:border-border-dark dark:bg-card-dark p-4 hover:shadow-md transition-shadow">
                            <img alt="<?php echo esc_attr($member['name']); ?>" 
                                 class="h-16 w-16 flex-shrink-0 object-cover rounded-lg" 
                                 src="<?php echo esc_url($photo_url); ?>">
                            <div class="flex-1 px-4">
                                <p class="font-semibold text-text-primary-light dark:text-text-primary-dark"><?php echo esc_html($member['name']); ?></p>
                                <p class="text-xs text-text-secondary-light dark:text-text-secondary-dark"><?php echo esc_html(haklai_get_role_name($member['role_level'] ?? 0)); ?></p>
                            </div>
                            <button type="button" 
                                    class="p-2 text-text-secondary-light dark:text-text-secondary-dark hover:text-primary transition-colors"
                                    onclick="editMember(<?php echo esc_js($member['id']); ?>)"
                                    title="<?php esc_attr_e('Editar Membro', 'haklai-app'); ?>">
                                <span class="material-symbols-outlined text-xl">edit</span>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: RELATÓRIOS -->
        <section id="section-relatorios" class="page-section hidden">
            <div class="p-4">
                <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4"><?php _e('Histórico de Reuniões', 'haklai-app'); ?></h2>
                
                <!-- Lista de Relatórios - Últimas 6 Reuniões -->
                <div class="space-y-4">
                    <?php
                    // Busca as últimas 6 reuniões
                    $recent_meetings = get_posts(array(
                        'post_type' => 'haklai_meeting',
                        'posts_per_page' => 6,
                        'orderby' => 'meta_value',
                        'order' => 'DESC',
                        'meta_key' => '_haklai_meeting_date',
                        'meta_query' => array(
                            array(
                                'key' => '_haklai_cell_id',
                                'value' => $cell_id,
                                'compare' => '='
                            )
                        )
                    ));
                    
                    if (empty($recent_meetings)) : ?>
                        <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark p-6 rounded-lg shadow-sm text-center">
                            <p class="text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Nenhuma reunião encontrada', 'haklai-app'); ?></p>
                        </div>
                    <?php else :
                        foreach ($recent_meetings as $meeting_post) :
                            $meeting_date = get_post_meta($meeting_post->ID, '_haklai_meeting_date', true);
                            $meeting_date_formatted = $meeting_date ? date_i18n('d/m/Y', strtotime($meeting_date)) : date_i18n('d/m/Y', strtotime($meeting_post->post_date));
                            
                            // Busca membros e presença desta reunião
                            $meeting_members = get_posts(array(
                                'post_type' => 'haklai_member',
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array('key' => '_haklai_cell_id', 'value' => $cell_id, 'compare' => '=')
                                )
                            ));
                            
                            $total_members = count($meeting_members);
                            $present_count = 0;
                            $visitors_count = 0;
                            
                            // Conta presentes
                            $attendance_records = get_posts(array(
                                'post_type' => 'haklai_attendance',
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array('key' => '_haklai_meeting_id', 'value' => $meeting_post->ID, 'compare' => '=')
                                )
                            ));
                            
                            foreach ($attendance_records as $att) {
                                if (get_post_meta($att->ID, '_haklai_is_present', true) === '1') {
                                    $present_count++;
                                }
                            }
                            
                            // Conta visitantes
                            $visitors = get_posts(array(
                                'post_type' => 'haklai_visitor',
                                'posts_per_page' => -1,
                                'meta_query' => array(
                                    array('key' => '_haklai_meeting_id', 'value' => $meeting_post->ID, 'compare' => '=')
                                )
                            ));
                            $visitors_count = count($visitors);
                    ?>
                        <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark p-4 rounded-lg shadow-sm">
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-text-primary-light dark:text-text-primary-dark"><?php printf(__('Reunião dia %s', 'haklai-app'), esc_html($meeting_date_formatted)); ?></h3>
                                </div>
                                <span class="material-symbols-outlined text-primary">event</span>
                            </div>
                            <div class="flex flex-wrap gap-4 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Total de membros:', 'haklai-app'); ?></span>
                                    <span class="font-semibold text-text-primary-light dark:text-text-primary-dark"><?php echo esc_html($total_members); ?></span>
                                </div>
                                <span class="text-border-light dark:text-border-dark">|</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Presentes:', 'haklai-app'); ?></span>
                                    <span class="font-semibold text-success"><?php echo esc_html($present_count); ?></span>
                                </div>
                                <span class="text-border-light dark:text-border-dark">|</span>
                                <div class="flex items-center gap-2">
                                    <span class="text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Visitantes:', 'haklai-app'); ?></span>
                                    <span class="font-semibold text-primary"><?php echo esc_html($visitors_count); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php 
                        endforeach;
                    endif; 
                    ?>
                </div>
            </div>
        </section>

        <!-- SEÇÃO: AJUSTES -->
        <section id="section-ajustes" class="page-section hidden">
            <div class="p-4">
                <h2 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark mb-4"><?php _e('Ajustes', 'haklai-app'); ?></h2>
                
                <!-- Comentário sobre funcionalidade futura -->
                <div class="bg-card-light dark:bg-card-dark border border-border-light dark:border-border-dark p-6 rounded-lg shadow-sm">
                    <div class="flex items-start gap-4">
                        <span class="material-symbols-outlined text-warning text-3xl">info</span>
                        <div>
                            <p class="font-semibold text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Funcionalidade em Desenvolvimento', 'haklai-app'); ?></p>
                            <p class="text-sm text-text-secondary-light dark:text-text-secondary-dark">
                                <?php _e('A seção de Ajustes estará disponível em breve. Aqui você poderá configurar preferências do sistema, notificações e outras opções personalizadas.', 'haklai-app'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Botão de chat flutuante -->
    <button class="fixed bottom-20 right-4 flex h-14 w-14 items-center justify-center bg-primary text-white shadow-lg rounded-full hover:bg-secondary hover:shadow-xl transition-all z-20" title="<?php esc_attr_e('Chat de Suporte', 'haklai-app'); ?>">
        <span class="material-symbols-outlined !text-3xl">chat</span>
    </button>

    <!-- Footer Navigation -->
    <footer class="fixed bottom-0 left-0 z-10 w-full border-t border-border-light bg-card-light dark:border-border-dark dark:bg-card-dark shadow-lg">
        <nav class="grid grid-cols-4">
            <button onclick="showSection('inicio', event)" class="nav-item flex flex-col items-center gap-1 p-2 text-primary hover:bg-primary/10 rounded-lg transition-colors active">
                <span class="material-symbols-outlined !text-2xl">home</span>
                <span class="text-xs font-semibold"><?php _e('Início', 'haklai-app'); ?></span>
            </button>
            <button onclick="showSection('membros', event)" class="nav-item flex flex-col items-center gap-1 p-2 text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-2xl">group</span>
                <span class="text-xs font-medium"><?php _e('Membros', 'haklai-app'); ?></span>
            </button>
            <button onclick="showSection('relatorios', event)" class="nav-item flex flex-col items-center gap-1 p-2 text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-2xl">assessment</span>
                <span class="text-xs font-medium"><?php _e('Relatórios', 'haklai-app'); ?></span>
            </button>
            <button onclick="showSection('ajustes', event)" class="nav-item flex flex-col items-center gap-1 p-2 text-text-secondary-light dark:text-text-secondary-dark hover:bg-primary/10 rounded-lg transition-colors">
                <span class="material-symbols-outlined !text-2xl">settings</span>
                <span class="text-xs font-medium"><?php _e('Ajustes', 'haklai-app'); ?></span>
            </button>
        </nav>
    </footer>
</div>

<!-- ========================================
     MODAIS
     ======================================== -->

<!-- Modal: Adicionar Visitante -->
<div id="addVisitorModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark"><?php _e('Adicionar Visitante', 'haklai-app'); ?></h3>
            <button onclick="closeAddVisitorModal()" class="text-text-secondary-light dark:text-text-secondary-dark hover:text-text-primary-light dark:hover:text-text-primary-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="addVisitorForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Foto', 'haklai-app'); ?></label>
                <div class="image-upload-container">
                    <div class="image-preview hidden relative inline-block" id="visitorImagePreview">
                        <img id="visitorImagePreviewImg" src="" alt="Preview" class="w-24 h-24 object-cover rounded-lg border-2 border-border-light dark:border-border-dark">
                        <button type="button" onclick="removeImage('visitor')" class="absolute -top-2 -right-2 bg-danger text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="image-upload-controls" id="visitorImageControls">
                        <input type="file" 
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                               name="photo" 
                               id="visitorPhotoInput"
                               accept="image/*" 
                               onchange="handleImageUpload(this, 'visitor')">
                        <small class="text-xs text-text-secondary-light dark:text-text-secondary-dark mt-1 block"><?php _e('Formatos: JPG, PNG, GIF, WebP. Máx: 10MB', 'haklai-app'); ?></small>
                        <div class="invalid-feedback text-danger text-sm mt-1 hidden" id="visitorImageError"></div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Nome', 'haklai-app'); ?> *</label>
                <input type="text" 
                       class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                       name="name" 
                       placeholder="<?php esc_attr_e('Nome completo', 'haklai-app'); ?>" 
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Telefone', 'haklai-app'); ?></label>
                <input type="tel" 
                       class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary phone-input" 
                       name="phone" 
                       id="visitorPhone"
                       placeholder="<?php esc_attr_e('Ex: +351 912 345 678 ou (11) 99999-9999', 'haklai-app'); ?>"
                       data-phone-validation>
                <small class="text-xs text-text-secondary-light dark:text-text-secondary-dark mt-1 block"><?php _e('Aceita formatos nacionais e internacionais', 'haklai-app'); ?></small>
                <div class="invalid-feedback phone-feedback text-sm mt-1"></div>
            </div>
        </form>
        <div class="sticky bottom-0 bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark px-6 py-4 flex gap-3">
            <button onclick="closeAddVisitorModal()" class="flex-1 px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-text-primary-light dark:text-text-primary-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                <?php _e('Cancelar', 'haklai-app'); ?>
            </button>
            <button onclick="addVisitor()" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                <?php _e('Adicionar Visitante', 'haklai-app'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Adicionar Membro -->
<div id="addMemberModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-xl max-w-md w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark"><?php _e('Registrar Novo Membro', 'haklai-app'); ?></h3>
            <button onclick="closeAddMemberModal()" class="text-text-secondary-light dark:text-text-secondary-dark hover:text-text-primary-light dark:hover:text-text-primary-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="addMemberForm" class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Foto', 'haklai-app'); ?></label>
                <div class="image-upload-container">
                    <div class="image-preview hidden relative inline-block" id="memberImagePreview">
                        <img id="memberImagePreviewImg" src="" alt="Preview" class="w-24 h-24 object-cover rounded-lg border-2 border-border-light dark:border-border-dark">
                        <button type="button" onclick="removeImage('member')" class="absolute -top-2 -right-2 bg-danger text-white rounded-full w-6 h-6 flex items-center justify-center text-xs">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="image-upload-controls" id="memberImageControls">
                        <input type="file" 
                               class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                               name="photo" 
                               id="memberPhotoInput"
                               accept="image/*" 
                               onchange="handleImageUpload(this, 'member')">
                        <small class="text-xs text-text-secondary-light dark:text-text-secondary-dark mt-1 block"><?php _e('Formatos: JPG, PNG, GIF, WebP. Máx: 10MB', 'haklai-app'); ?></small>
                        <div class="invalid-feedback text-danger text-sm mt-1 hidden" id="memberImageError"></div>
                    </div>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Nome', 'haklai-app'); ?> *</label>
                <input type="text" 
                       class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                       name="name" 
                       placeholder="<?php esc_attr_e('Nome completo', 'haklai-app'); ?>" 
                       required>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Telefone', 'haklai-app'); ?></label>
                <input type="tel" 
                       class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary phone-input" 
                       name="phone" 
                       id="memberPhone"
                       placeholder="<?php esc_attr_e('Ex: +351 912 345 678 ou (11) 99999-9999', 'haklai-app'); ?>"
                       data-phone-validation>
                <small class="text-xs text-text-secondary-light dark:text-text-secondary-dark mt-1 block"><?php _e('Aceita formatos nacionais e internacionais', 'haklai-app'); ?></small>
                <div class="invalid-feedback phone-feedback text-sm mt-1"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('E-mail', 'haklai-app'); ?></label>
                <input type="email" 
                       class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" 
                       name="email" 
                       placeholder="email@exemplo.com">
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Habilidade', 'haklai-app'); ?></label>
                <select class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" name="skill">
                    <option value=""><?php _e('Selecione uma habilidade', 'haklai-app'); ?></option>
                    <option value="musica"><?php _e('Música', 'haklai-app'); ?></option>
                    <option value="foto"><?php _e('Foto', 'haklai-app'); ?></option>
                    <option value="filmagem"><?php _e('Filmagem', 'haklai-app'); ?></option>
                    <option value="teatro"><?php _e('Teatro', 'haklai-app'); ?></option>
                    <option value="gastronomia"><?php _e('Gastronomia', 'haklai-app'); ?></option>
                    <option value="tecnologia"><?php _e('Tecnologia', 'haklai-app'); ?></option>
                    <option value="recepcao"><?php _e('Recepção', 'haklai-app'); ?></option>
                    <option value="som"><?php _e('Som e Projeção', 'haklai-app'); ?></option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Líder Associado', 'haklai-app'); ?></label>
                <select class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" name="leader_id">
                    <option value=""><?php _e('Selecione um líder', 'haklai-app'); ?></option>
                    <?php foreach ($members as $m): ?>
                        <?php if (($m['role_level'] ?? 0) >= 1): ?>
                            <option value="<?php echo esc_attr($m['id']); ?>"><?php echo esc_html($m['name']); ?></option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2">⭐ <?php _e('Encontro com Deus', 'haklai-app'); ?></label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" 
                           class="w-5 h-5 text-primary border-border-light dark:border-border-dark rounded focus:ring-primary" 
                           id="add_encontro_deus" 
                           name="encontro_deus"
                           value="sim">
                    <span class="text-sm text-text-primary-light dark:text-text-primary-dark"><?php _e('Participou do Encontro com Deus', 'haklai-app'); ?></span>
                </label>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2">🎓 <?php _e('Formações Concluídas', 'haklai-app'); ?></label>
                <div class="space-y-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-primary border-border-light dark:border-border-dark rounded focus:ring-primary" 
                               id="add_formacao_ctl" 
                               name="formacoes[]" 
                               value="ctl">
                        <span class="text-sm text-text-primary-light dark:text-text-primary-dark"><?php _e('CTL - Curso de Treinamento de Líderes', 'haklai-app'); ?></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-primary border-border-light dark:border-border-dark rounded focus:ring-primary" 
                               id="add_formacao_cme" 
                               name="formacoes[]" 
                               value="cme">
                        <span class="text-sm text-text-primary-light dark:text-text-primary-dark"><?php _e('CME - Curso de Maturidade Espiritual', 'haklai-app'); ?></span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" 
                               class="w-5 h-5 text-primary border-border-light dark:border-border-dark rounded focus:ring-primary" 
                               id="add_formacao_stp" 
                               name="formacoes[]" 
                               value="stp">
                        <span class="text-sm text-text-primary-light dark:text-text-primary-dark"><?php _e('STP - Seminário Teológico Pastoral', 'haklai-app'); ?></span>
                    </label>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Status de Batismo', 'haklai-app'); ?></label>
                <select class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark focus:outline-none focus:ring-2 focus:ring-primary" name="baptism_status">
                    <option value="visitor"><?php _e('Visitante', 'haklai-app'); ?></option>
                    <option value="frequent_visitor"><?php _e('Frequentador Assíduo', 'haklai-app'); ?></option>
                    <option value="member"><?php _e('Membro Batizado', 'haklai-app'); ?></option>
                </select>
            </div>
        </form>
        <div class="sticky bottom-0 bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark px-6 py-4 flex gap-3">
            <button onclick="closeAddMemberModal()" class="flex-1 px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-text-primary-light dark:text-text-primary-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                <?php _e('Cancelar', 'haklai-app'); ?>
            </button>
            <button onclick="addMember()" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                <?php _e('Registrar Membro', 'haklai-app'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Modal: Editar Membro (estrutura básica - será preenchido via AJAX) -->
<div id="editMemberModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-card-light dark:bg-card-dark rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="sticky top-0 bg-card-light dark:bg-card-dark border-b border-border-light dark:border-border-dark px-6 py-4 flex items-center justify-between">
            <h3 class="text-lg font-bold text-text-primary-light dark:text-text-primary-dark"><?php _e('Editar Membro', 'haklai-app'); ?></h3>
            <button onclick="closeEditMemberModal()" class="text-text-secondary-light dark:text-text-secondary-dark hover:text-text-primary-light dark:hover:text-text-primary-dark">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form id="editMemberForm" class="p-6 space-y-4">
            <input type="hidden" name="member_id" id="edit_member_id">
            <!-- Conteúdo será preenchido via JavaScript -->
            <div id="editMemberContent" class="space-y-4">
                <p class="text-text-secondary-light dark:text-text-secondary-dark"><?php _e('Carregando dados do membro...', 'haklai-app'); ?></p>
            </div>
        </form>
        <div class="sticky bottom-0 bg-card-light dark:bg-card-dark border-t border-border-light dark:border-border-dark px-6 py-4 flex gap-3">
            <button onclick="closeEditMemberModal()" class="flex-1 px-4 py-2 border border-border-light dark:border-border-dark rounded-lg text-text-primary-light dark:text-text-primary-dark hover:bg-background-light dark:hover:bg-background-dark transition-colors">
                <?php _e('Cancelar', 'haklai-app'); ?>
            </button>
            <button onclick="saveMemberEdit()" class="flex-1 px-4 py-2 bg-primary text-white rounded-lg hover:bg-secondary transition-colors">
                <?php _e('Salvar Alterações', 'haklai-app'); ?>
            </button>
        </div>
    </div>
</div>

<script>
// Dados para JavaScript (será preenchido pelo PHP)
window.haklaiCheckpoint = {
    meetingId: <?php echo esc_js($meeting['id'] ?? 0); ?>,
    cellId: <?php echo esc_js($cell_id ?? 0); ?>,
    nonce: '<?php echo wp_create_nonce('haklai_nonce'); ?>',
    ajaxUrl: '<?php echo esc_url(admin_url('admin-ajax.php')); ?>',
    members: <?php echo json_encode(array_map(function($member) {
        return array(
            'id' => $member['id'],
            'name' => $member['name'],
            'role_level' => $member['role_level'] ?? 0
        );
    }, $members)); ?>
};

// ========================================
// SISTEMA DE NAVEGAÇÃO
// ========================================
function showSection(sectionName, event) {
    document.querySelectorAll('.page-section').forEach(section => {
        section.classList.add('hidden');
    });
    
    const section = document.getElementById(`section-${sectionName}`);
    if (section) {
        section.classList.remove('hidden');
    }
    
    document.querySelectorAll('.nav-item').forEach(btn => {
        btn.classList.remove('text-primary', 'font-semibold');
        btn.classList.add('text-text-secondary-light', 'dark:text-text-secondary-dark', 'font-medium');
        const label = btn.querySelector('span:last-child');
        if (label) {
            label.classList.remove('font-semibold');
            label.classList.add('font-medium');
        }
    });
    
    if (event && event.currentTarget) {
        const activeBtn = event.currentTarget;
        activeBtn.classList.remove('text-text-secondary-light', 'dark:text-text-secondary-dark', 'font-medium');
        activeBtn.classList.add('text-primary', 'font-semibold');
        const label = activeBtn.querySelector('span:last-child');
        if (label) {
            label.classList.remove('font-medium');
            label.classList.add('font-semibold');
        }
    }
}

// ========================================
// FUNÇÕES DE MODAL
// ========================================
function openAddVisitorModal() {
    const modal = document.getElementById('addVisitorModal');
    if (modal) modal.classList.remove('hidden');
}

function closeAddVisitorModal() {
    const modal = document.getElementById('addVisitorModal');
    const form = document.getElementById('addVisitorForm');
    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
    if (typeof removeImage === 'function') removeImage('visitor');
}

function openAddMemberModal() {
    const modal = document.getElementById('addMemberModal');
    if (modal) modal.classList.remove('hidden');
}

function closeAddMemberModal() {
    const modal = document.getElementById('addMemberModal');
    const form = document.getElementById('addMemberForm');
    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
    if (typeof removeImage === 'function') removeImage('member');
}

function closeEditMemberModal() {
    const modal = document.getElementById('editMemberModal');
    const form = document.getElementById('editMemberForm');
    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
}

// ========================================
// FUNÇÕES PRINCIPAIS
// ========================================
function togglePresence(card) {
    if (window.HaklaiApp && typeof window.HaklaiApp.togglePresence === 'function') {
        window.HaklaiApp.togglePresence(card);
    } else {
        const memberId = card.getAttribute('data-member-id');
        const isPresent = card.classList.contains('present');
        const newStatus = !isPresent;
        
        // Atualiza visualmente
        if (newStatus) {
            card.classList.remove('absent');
            card.classList.add('present');
            const statusText = card.querySelector('p.text-danger, p.text-success');
            if (statusText) {
                statusText.textContent = '<?php esc_js_e('Presente', 'haklai-app'); ?>';
                statusText.classList.remove('text-danger');
                statusText.classList.add('text-success');
            }
            const indicator = card.querySelector('.w-2');
            if (indicator) {
                indicator.classList.remove('bg-danger');
                indicator.classList.add('bg-success');
            }
        } else {
            card.classList.remove('present');
            card.classList.add('absent');
            const statusText = card.querySelector('p.text-danger, p.text-success');
            if (statusText) {
                statusText.textContent = '<?php esc_js_e('Ausente', 'haklai-app'); ?>';
                statusText.classList.remove('text-success');
                statusText.classList.add('text-danger');
            }
            const indicator = card.querySelector('.w-2');
            if (indicator) {
                indicator.classList.remove('bg-success');
                indicator.classList.add('bg-danger');
            }
        }
        
        // Envia via AJAX
        if (window.haklaiCheckpoint) {
            fetch(window.haklaiCheckpoint.ajaxUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    action: 'haklai_ajax_handler',
                    action_type: 'toggle_presence',
                    data: JSON.stringify({ member_id: memberId, is_present: newStatus, meeting_id: window.haklaiCheckpoint.meetingId }),
                    nonce: window.haklaiCheckpoint.nonce
                })
            }).then(() => {
                if (typeof updateStats === 'function') updateStats();
            });
        }
    }
}

function addVisitor() {
    if (window.HaklaiApp && typeof window.HaklaiApp.addVisitor === 'function') {
        window.HaklaiApp.addVisitor();
    } else {
        const form = document.getElementById('addVisitorForm');
        if (!form || !window.haklaiCheckpoint) return;
        
        const formData = new FormData(form);
        formData.append('action', 'haklai_ajax_handler');
        formData.append('action_type', 'add_visitor');
        formData.append('nonce', window.haklaiCheckpoint.nonce);
        
        const phoneInput = form.querySelector('#visitorPhone');
        if (phoneInput && phoneInput.value && typeof validatePhoneNumber === 'function' && !validatePhoneNumber(phoneInput)) {
            return;
        }
        
        fetch(window.haklaiCheckpoint.ajaxUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  closeAddVisitorModal();
                  alert('<?php esc_js_e('Visitante adicionado com sucesso!', 'haklai-app'); ?>');
                  location.reload();
              } else {
                  alert('<?php esc_js_e('Erro:', 'haklai-app'); ?> ' + (data.data?.message || '<?php esc_js_e('Erro ao adicionar visitante', 'haklai-app'); ?>'));
              }
          });
    }
}

function addMember() {
    if (window.HaklaiApp && typeof window.HaklaiApp.addMember === 'function') {
        window.HaklaiApp.addMember();
    } else {
        const form = document.getElementById('addMemberForm');
        if (!form || !window.haklaiCheckpoint) return;
        
        const formData = new FormData(form);
        formData.append('action', 'haklai_ajax_handler');
        formData.append('action_type', 'add_member');
        formData.append('nonce', window.haklaiCheckpoint.nonce);
        formData.append('cell_id', window.haklaiCheckpoint.cellId);
        
        const phoneInput = form.querySelector('#memberPhone');
        if (phoneInput && phoneInput.value && typeof validatePhoneNumber === 'function' && !validatePhoneNumber(phoneInput)) {
            return;
        }
        
        fetch(window.haklaiCheckpoint.ajaxUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  closeAddMemberModal();
                  alert('<?php esc_js_e('Membro registrado com sucesso!', 'haklai-app'); ?>');
                  location.reload();
              } else {
                  alert('<?php esc_js_e('Erro:', 'haklai-app'); ?> ' + (data.data?.message || '<?php esc_js_e('Erro ao registrar membro', 'haklai-app'); ?>'));
              }
          });
    }
}

function editMember(memberId) {
    if (window.HaklaiApp && typeof window.HaklaiApp.editMember === 'function') {
        window.HaklaiApp.editMember(memberId);
    } else {
        if (!window.haklaiCheckpoint) return;
        
        fetch(window.haklaiCheckpoint.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'haklai_ajax_handler',
                action_type: 'get_member_data',
                data: JSON.stringify({ member_id: memberId }),
                nonce: window.haklaiCheckpoint.nonce
            })
        })
        .then(response => response.json())
        .then(response => {
            if (response.success && response.data) {
                if (typeof populateEditForm === 'function') {
                    populateEditForm(response.data);
                }
                const modal = document.getElementById('editMemberModal');
                if (modal) modal.classList.remove('hidden');
            } else {
                alert('<?php esc_js_e('Erro ao carregar dados do membro', 'haklai-app'); ?>');
            }
        });
    }
}

function saveMemberEdit() {
    if (window.HaklaiApp && typeof window.HaklaiApp.saveMemberEdit === 'function') {
        window.HaklaiApp.saveMemberEdit();
    } else {
        const form = document.getElementById('editMemberForm');
        if (!form || !window.haklaiCheckpoint) return;
        
        const formData = new FormData(form);
        formData.append('action', 'haklai_ajax_handler');
        formData.append('action_type', 'update_member');
        formData.append('nonce', window.haklaiCheckpoint.nonce);
        
        fetch(window.haklaiCheckpoint.ajaxUrl, {
            method: 'POST',
            body: formData
        }).then(response => response.json())
          .then(data => {
              if (data.success) {
                  closeEditMemberModal();
                  alert('<?php esc_js_e('Membro atualizado com sucesso!', 'haklai-app'); ?>');
                  location.reload();
              } else {
                  alert('<?php esc_js_e('Erro:', 'haklai-app'); ?> ' + (data.data?.message || '<?php esc_js_e('Erro ao salvar alterações', 'haklai-app'); ?>'));
              }
          });
    }
}

function sendReport() {
    if (window.HaklaiApp && typeof window.HaklaiApp.sendReport === 'function') {
        window.HaklaiApp.sendReport();
    } else {
        if (!confirm('<?php esc_js_e('Deseja enviar o relatório desta reunião?', 'haklai-app'); ?>')) return;
        
        if (!window.haklaiCheckpoint) return;
        
        fetch(window.haklaiCheckpoint.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'haklai_ajax_handler',
                action_type: 'send_report',
                data: JSON.stringify({ meeting_id: window.haklaiCheckpoint.meetingId }),
                nonce: window.haklaiCheckpoint.nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('<?php esc_js_e('Relatório enviado com sucesso!', 'haklai-app'); ?>');
            } else {
                alert('<?php esc_js_e('Erro:', 'haklai-app'); ?> ' + (data.data?.message || '<?php esc_js_e('Erro ao enviar relatório', 'haklai-app'); ?>'));
            }
        });
    }
}

function updateStats() {
    if (window.HaklaiApp && typeof window.HaklaiApp.updateStats === 'function') {
        window.HaklaiApp.updateStats();
    } else {
        const cards = document.querySelectorAll('.member-card');
        const totalMembers = cards.length;
        const presentMembers = document.querySelectorAll('.member-card.present').length;
        
        let frequentVisitors = 0;
        cards.forEach(card => {
            if (card.getAttribute('data-baptism-status') === 'frequent_visitor') {
                frequentVisitors++;
            }
        });
        
        const lastMeetingPresent = presentMembers;
        const presenceRate = totalMembers > 0 ? Math.round((presentMembers / totalMembers) * 100 * 10) / 10 : 0;
        
        const totalEl = document.querySelector('.stat-total-members');
        const faEl = document.querySelector('.stat-frequent-visitors');
        const lastEl = document.querySelector('.stat-last-meeting');
        const rateEl = document.querySelector('.stat-presence-rate');
        
        if (totalEl) totalEl.textContent = totalMembers;
        if (faEl) faEl.textContent = frequentVisitors;
        if (lastEl) lastEl.textContent = lastMeetingPresent;
        if (rateEl) rateEl.textContent = presenceRate + '%';
    }
}

// ========================================
// INICIALIZAÇÃO
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar apenas a seção início
    document.querySelectorAll('.page-section').forEach(section => {
        if (section.id === 'section-inicio') {
            section.classList.remove('hidden');
        } else {
            section.classList.add('hidden');
        }
    });
    
    // Validação de telefone em tempo real
    document.querySelectorAll('[data-phone-validation]').forEach(input => {
        input.addEventListener('input', () => validatePhoneNumber(input));
        input.addEventListener('blur', () => validatePhoneNumber(input));
    });
    
    // Busca de anfitrião
    const anfitriaoSearch = document.getElementById('anfitriaoSearch');
    const searchResults = document.getElementById('searchResults');
    
    if (anfitriaoSearch && searchResults && window.haklaiCheckpoint) {
        anfitriaoSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            if (searchTerm.length > 2) {
                const availableMembers = window.haklaiCheckpoint.members.filter(member => 
                    member.name.toLowerCase().includes(searchTerm)
                );
                
                if (availableMembers.length > 0) {
                    let html = '';
                    availableMembers.forEach(member => {
                        const safeName = member.name.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                        html += `<div class="px-4 py-2 hover:bg-background-light dark:hover:bg-background-dark cursor-pointer text-text-primary-light dark:text-text-primary-dark" onclick="selectAnfitriao('${safeName}')">${member.name}</div>`;
                    });
                    searchResults.innerHTML = html;
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.classList.add('hidden');
                }
            } else {
                searchResults.classList.add('hidden');
            }
        });
        
        // Fecha resultados ao clicar fora
        document.addEventListener('click', function(e) {
            if (!anfitriaoSearch.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.classList.add('hidden');
            }
        });
    }
    
    // Atualiza estatísticas iniciais
    if (typeof updateStats === 'function') updateStats();
    
    // Fecha modais ao clicar no backdrop
    document.querySelectorAll('[id$="Modal"]').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    });
});

// Funções para submenu de Membros (compatibilidade)
function showAddMembro() {
    openAddMemberModal();
}

function showAddVisitante() {
    openAddVisitorModal();
}

function selectAnfitriao(name) {
    const searchInput = document.getElementById('anfitriaoSearch');
    const results = document.getElementById('searchResults');
    
    if (searchInput) searchInput.value = name;
    if (results) results.classList.add('hidden');
}

// ========================================
// VALIDAÇÃO DE TELEFONE
// ========================================
function validatePhoneNumber(phoneInput) {
    const phone = phoneInput.value.trim();
    const feedback = phoneInput.parentElement.querySelector('.phone-feedback');
    
    if (phone === '') {
        phoneInput.classList.remove('is-invalid', 'is-valid');
        if (feedback) feedback.textContent = '';
        return true;
    }
    
    const digitsOnly = phone.replace(/\D/g, '');
    const validations = {
        minLength: digitsOnly.length >= 8,
        maxLength: digitsOnly.length <= 15,
        validChars: /^[\d\s\+\-\(\)]+$/.test(phone),
        validInternational: phone.startsWith('+') ? /^\+\d{1,3}[\s\-]?/.test(phone) : true
    };
    
    const isValid = Object.values(validations).every(v => v === true);
    
    if (isValid) {
        phoneInput.classList.remove('is-invalid');
        phoneInput.classList.add('is-valid');
        if (feedback) {
            feedback.textContent = '✓ <?php esc_js_e('Número válido', 'haklai-app'); ?>';
            feedback.classList.remove('text-danger');
            feedback.classList.add('text-success');
        }
        return true;
    } else {
        phoneInput.classList.remove('is-valid');
        phoneInput.classList.add('is-invalid');
        if (feedback) {
            feedback.textContent = '<?php esc_js_e('Número inválido', 'haklai-app'); ?>';
            feedback.classList.remove('text-success');
            feedback.classList.add('text-danger');
        }
        return false;
    }
}

// ========================================
// UPLOAD DE IMAGENS
// ========================================
function handleImageUpload(input, type) {
    const file = input.files[0];
    const previewId = type + 'ImagePreview';
    const previewImgId = type + 'ImagePreviewImg';
    const controlsId = type + 'ImageControls';
    const errorId = type + 'ImageError';
    
    const preview = document.getElementById(previewId);
    const previewImg = document.getElementById(previewImgId);
    const controls = document.getElementById(controlsId);
    const error = document.getElementById(errorId);
    
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
    input.classList.remove('is-invalid');
    
    if (!file) {
        hideImagePreview(type);
        return;
    }
    
    const maxSize = 10 * 1024 * 1024;
    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!allowedTypes.includes(file.type)) {
        showImageError(type, '<?php esc_js_e('Formato não suportado. Use JPG, PNG, GIF ou WebP.', 'haklai-app'); ?>');
        return;
    }
    
    if (file.size > maxSize) {
        showImageError(type, '<?php esc_js_e('Arquivo muito grande. Tamanho máximo: 10MB.', 'haklai-app'); ?>');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        if (previewImg) previewImg.src = e.target.result;
        if (preview) preview.classList.remove('hidden');
        if (controls) controls.classList.add('hidden');
    };
    reader.readAsDataURL(file);
}

function removeImage(type) {
    const inputId = type + 'PhotoInput';
    const previewId = type + 'ImagePreview';
    const controlsId = type + 'ImageControls';
    const errorId = type + 'ImageError';
    
    const input = document.getElementById(inputId);
    const preview = document.getElementById(previewId);
    const controls = document.getElementById(controlsId);
    const error = document.getElementById(errorId);
    
    if (input) input.value = '';
    if (preview) preview.classList.add('hidden');
    if (controls) controls.classList.remove('hidden');
    if (error) {
        error.textContent = '';
        error.classList.add('hidden');
    }
    if (input) input.classList.remove('is-invalid');
}

function showImageError(type, message) {
    const errorId = type + 'ImageError';
    const inputId = type + 'PhotoInput';
    
    const error = document.getElementById(errorId);
    const input = document.getElementById(inputId);
    
    if (error) {
        error.textContent = message;
        error.classList.remove('hidden');
    }
    if (input) input.classList.add('is-invalid');
}

function hideImagePreview(type) {
    const previewId = type + 'ImagePreview';
    const controlsId = type + 'ImageControls';
    
    const preview = document.getElementById(previewId);
    const controls = document.getElementById(controlsId);
    
    if (preview) preview.classList.add('hidden');
    if (controls) controls.classList.remove('hidden');
}

// ========================================
// POPULATE EDIT FORM
// ========================================
function populateEditForm(memberData) {
    document.getElementById('edit_member_id').value = memberData.id;
    
    const content = document.getElementById('editMemberContent');
    if (content) {
        const safeName = (memberData.name || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const safeEmail = (memberData.email || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const safePhone = (memberData.phone || '').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        const baptismStatus = memberData.baptism_status || 'visitor';
        
        content.innerHTML = `
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Nome Completo', 'haklai-app'); ?> *</label>
                <input type="text" class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark" name="name" id="edit_name" value="${safeName}" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('E-mail', 'haklai-app'); ?></label>
                <input type="email" class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark" name="email" id="edit_email" value="${safeEmail}">
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Telefone', 'haklai-app'); ?></label>
                <input type="tel" class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark phone-input" name="phone" id="edit_phone" value="${safePhone}" data-phone-validation>
                <div class="invalid-feedback phone-feedback text-sm mt-1"></div>
            </div>
            <div>
                <label class="block text-sm font-medium text-text-primary-light dark:text-text-primary-dark mb-2"><?php _e('Status de Batismo', 'haklai-app'); ?></label>
                <select class="w-full px-4 py-2 border border-border-light dark:border-border-dark bg-card-light dark:bg-card-dark rounded-lg text-text-primary-light dark:text-text-primary-dark" name="baptism_status" id="edit_baptism_status">
                    <option value="visitor" ${baptismStatus === 'visitor' ? 'selected' : ''}><?php _e('Visitante', 'haklai-app'); ?></option>
                    <option value="frequent_visitor" ${baptismStatus === 'frequent_visitor' ? 'selected' : ''}><?php _e('Frequentador Assíduo', 'haklai-app'); ?></option>
                    <option value="member" ${baptismStatus === 'member' ? 'selected' : ''}><?php _e('Membro Batizado', 'haklai-app'); ?></option>
                </select>
            </div>
        `;
        
        // Reaplica validação de telefone
        const phoneInput = document.getElementById('edit_phone');
        if (phoneInput) {
            phoneInput.addEventListener('input', () => validatePhoneNumber(phoneInput));
            phoneInput.addEventListener('blur', () => validatePhoneNumber(phoneInput));
        }
    }
}
</script>
