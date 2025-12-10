<?php
/**
 * Template de Relatórios
 * Baseado no arquivo test-relatorios-checkpoint-style.html
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

// Verifica se há erro
if (isset($error)) {
    echo '<div class="haklai-alert haklai-alert-danger"><i class="fas fa-exclamation-circle"></i> <div>' . esc_html($error) . '</div></div>';
    return;
}

// Obtém informações do usuário atual
$current_user = wp_get_current_user();
$user_avatar = get_avatar_url($current_user->ID, array('size' => 40));
$user_level = Haklai_Permissions::get_user_level(get_current_user_id());

// ===== CONTAGEM CONSISTENTE COM admin-welcome-page.php =====
// Total de membros publicados (contagem nativa do WP)
$total_members_count = wp_count_posts('haklai_member');
$total_members_consistent = intval($total_members_count->publish ?? 0);

// Para níveis abaixo de Pastor Senior/Supervisor, filtra por células do usuário
if ($user_level < 4) {
    $user_cells_for_count = Haklai_Permissions::get_user_cells(get_current_user_id());
    $cell_ids_for_count = array();
    foreach ($user_cells_for_count as $cell_item) {
        $cell_ids_for_count[] = $cell_item->ID;
    }

    if (!empty($cell_ids_for_count)) {
        $members_args_for_count = array(
            'post_type' => 'haklai_member',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'fields' => 'ids',
            'meta_query' => array(
                array(
                    'key' => '_haklai_cell_id',
                    'value' => $cell_ids_for_count,
                    'compare' => 'IN'
                )
            )
        );
        $total_members_consistent = count(get_posts($members_args_for_count));
    }
}
?>

<div class="reports-container">
    <div class="haklai-theme-toggle" data-haklai-theme-container>
        <label class="haklai-toggle-switch">
            <input type="checkbox" data-haklai-theme-toggle>
            <span class="haklai-toggle-slider"></span>
            <span class="haklai-toggle-label" data-haklai-theme-label>
                <?php _e('Tema Escuro', 'haklai-app'); ?>
            </span>
        </label>
    </div>
    <!-- Header -->
    <div class="reports-header">
        <div class="header-content">
            <h1><?php _e('Relatórios e Estatísticas', 'haklai-app'); ?></h1>
            <div class="subtitle"><?php _e('Gere relatórios detalhados e visualize estatísticas do sistema', 'haklai-app'); ?></div>
        </div>
        
        <div class="user-info">
            <img src="<?php echo esc_url($user_avatar); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" class="user-avatar">
            <div>
                <div style="font-weight: var(--haklai-font-weight-semibold);"><?php echo esc_html($current_user->display_name); ?></div>
                <div style="font-size: var(--haklai-text-sm); opacity: 0.8;"><?php echo esc_html(haklai_get_role_name($user_level)); ?></div>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="reports-content">
        <!-- Stats Dashboard -->
        <div class="stats-dashboard">
            <h3 class="section-title"><?php _e('Estatísticas Gerais', 'haklai-app'); ?></h3>
            <div class="stats-grid">
                <!-- Card 1: Total de Membros -->
                <div class="stat-card">
                    <div class="icon">👥</div>
                    <div class="stat-content">
                        <div class="number" data-stat="total-members"><?php echo esc_html($total_members_consistent); ?></div>
                        <div class="label"><?php _e('Total de Membros', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <!-- Card 2: F.A (Frequentador Assíduo) - FASE 2 -->
                <div class="stat-card">
                    <div class="icon">🔄</div>
                    <div class="stat-content">
                        <div class="number"><?php echo esc_html($stats['total_fa'] ?? 0); ?></div>
                        <div class="label"><?php _e('F.A (Frequentador Assíduo)', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <!-- Card 3: Formações (CME, CTL, STP) - FASE 2 -->
                <div class="stat-card stat-card-formations">
                    <div class="icon">📚</div>
                    <div class="stat-content">
                        <div class="formations-container">
                            <div class="formation-item">
                                <span class="formation-label"><?php _e('CME', 'haklai-app'); ?></span>
                                <span class="formation-percentage"><?php echo esc_html($stats['formations']['cme']['percentage'] ?? 0); ?>%</span>
                            </div>
                            <div class="formation-item">
                                <span class="formation-label"><?php _e('CTL', 'haklai-app'); ?></span>
                                <span class="formation-percentage"><?php echo esc_html($stats['formations']['ctl']['percentage'] ?? 0); ?>%</span>
                            </div>
                            <div class="formation-item">
                                <span class="formation-label"><?php _e('STP', 'haklai-app'); ?></span>
                                <span class="formation-percentage"><?php echo esc_html($stats['formations']['stp']['percentage'] ?? 0); ?>%</span>
                            </div>
                        </div>
                        <div class="label"><?php _e('Formações Concluídas', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <!-- Card 4: Novos Batismos -->
                <div class="stat-card">
                    <div class="icon">💧</div>
                    <div class="stat-content">
                        <div class="number"><?php echo esc_html($stats['total_baptisms'] ?? 0); ?></div>
                        <div class="label"><?php _e('Novos Batismos', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <!-- Card 5: Visitantes (30 dias) - FASE 2 -->
                <div class="stat-card">
                    <div class="icon">👋</div>
                    <div class="stat-content">
                        <div class="number"><?php echo esc_html($stats['total_visitors_30days'] ?? 0); ?></div>
                        <div class="label"><?php _e('Visitantes (30 dias)', 'haklai-app'); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Filters Panel -->
        <div class="filters-panel">
            <h3 class="section-title"><?php _e('Filtros de Relatório', 'haklai-app'); ?></h3>
            <div class="filters-grid">
                <div class="filter-group">
                    <label class="filter-label"><?php _e('Nível de Filtro', 'haklai-app'); ?></label>
                    <select class="haklai-select" id="reportFilterLevel">
                        <option value=""><?php _e('Selecione o nível', 'haklai-app'); ?></option>
                        <option value="cell"><?php _e('Célula', 'haklai-app'); ?></option>
                        <option value="leader"><?php _e('Líder de Célula', 'haklai-app'); ?></option>
                        <option value="discipler"><?php _e('Discipulador', 'haklai-app'); ?></option>
                        <option value="network_pastor"><?php _e('Pastor de Rede', 'haklai-app'); ?></option>
                        <option value="senior_pastor"><?php _e('Pastor Senior', 'haklai-app'); ?></option>
                        <option value="supervisor_pastor"><?php _e('Pastor Supervisor', 'haklai-app'); ?></option>
                        <option value="network"><?php _e('Rede', 'haklai-app'); ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><?php _e('Selecionar', 'haklai-app'); ?></label>
                    <select class="haklai-select" id="reportFilterId">
                        <option value=""><?php _e('Selecione primeiro o nível', 'haklai-app'); ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><?php _e('Período', 'haklai-app'); ?></label>
                    <select class="haklai-select" id="reportPeriod">
                        <option value="7"><?php _e('Última semana', 'haklai-app'); ?></option>
                        <option value="30" <?php selected($default_period, '30'); ?>><?php _e('Último mês', 'haklai-app'); ?></option>
                        <option value="90"><?php _e('Últimos 3 meses', 'haklai-app'); ?></option>
                        <option value="365"><?php _e('Último ano', 'haklai-app'); ?></option>
                        <option value="custom"><?php _e('Personalizado', 'haklai-app'); ?></option>
                    </select>
                </div>
                <div class="filter-group">
                    <label class="filter-label"><?php _e('Data Início', 'haklai-app'); ?></label>
                    <input type="date" class="haklai-input" id="reportStartDate" value="<?php echo date('Y-m-01'); ?>">
                </div>
                <div class="filter-group">
                    <label class="filter-label"><?php _e('Data Fim', 'haklai-app'); ?></label>
                    <input type="date" class="haklai-input" id="reportEndDate" value="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="d-flex gap-4 mt-4">
                <button type="button" class="haklai-btn haklai-btn-primary" id="btnFilterReports">
                    <i class="fas fa-search"></i> <?php _e('Filtrar Relatórios', 'haklai-app'); ?>
                </button>
                <button type="button" class="haklai-btn haklai-btn-secondary" id="btnResetFilters">
                    <i class="fas fa-redo"></i> <?php _e('Limpar Filtros', 'haklai-app'); ?>
                </button>
            </div>
        </div>


                <!-- Relatórios Recentes -->
                <div class="recent-reports">
            <h3 class="section-title"><?php _e('Relatórios de Presença', 'haklai-app'); ?></h3>
            
            <div class="table-responsive">
                <table class="haklai-table" id="reportsTable">
                            <thead>
                                <tr>
                                    <th><?php _e('Célula', 'haklai-app'); ?></th>
                                    <th><?php _e('Data', 'haklai-app'); ?></th>
                                    <th><?php _e('Nº Membros', 'haklai-app'); ?></th>
                                    <th><?php _e('Nº Visitantes', 'haklai-app'); ?></th>
                                    <th><?php _e('% Presentes', 'haklai-app'); ?></th>
                                    <th><?php _e('Status', 'haklai-app'); ?></th>
                                    <th><?php _e('Ações', 'haklai-app'); ?></th>
                                </tr>
                            </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center">
                                <?php _e('Selecione os filtros e clique em "Filtrar Relatórios" para visualizar os dados.', 'haklai-app'); ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        
        <!-- Relatórios Disponíveis -->
        <div class="reports-section">
            <h3 class="section-title"><?php _e('Relatórios Disponíveis', 'haklai-app'); ?></h3>
            
            <!-- Relatório de Presença -->
            <div class="report-item">
                <div class="report-header">
                    <div class="report-icon presence">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="report-content">
                        <h6 class="report-title"><?php _e('Relatório de Presença', 'haklai-app'); ?></h6>
                        <p class="report-description">
                            <?php _e('Análise detalhada da presença dos membros em reuniões de célula, eventos e atividades. Inclui gráficos de tendência, comparações mensais e identificação de padrões de frequência.', 'haklai-app'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="report-actions d-flex gap-2">
                    <button class="haklai-btn haklai-btn-primary" onclick="HaklaiReports.generateReport('attendance', 'pdf')">
                        <i class="fas fa-download"></i> <?php _e('Gerar PDF', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-outline" onclick="HaklaiReports.generateReport('attendance', 'excel')">
                        <i class="fas fa-file-excel"></i> <?php _e('Excel', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-secondary" onclick="HaklaiReports.viewReport('attendance')">
                        <i class="fas fa-eye"></i> <?php _e('Visualizar', 'haklai-app'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Relatório de Batismos -->
            <div class="report-item">
                <div class="report-header">
                    <div class="report-icon baptism">
                        <i class="fas fa-tint"></i>
                    </div>
                    <div class="report-content">
                        <h6 class="report-title"><?php _e('Relatório de Batismos', 'haklai-app'); ?></h6>
                        <p class="report-description">
                            <?php _e('Relatório completo dos batismos realizados, incluindo dados demográficos, localização, idade dos batizados e análise de crescimento espiritual da congregação.', 'haklai-app'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="report-actions d-flex gap-2">
                    <button class="haklai-btn haklai-btn-primary" onclick="HaklaiReports.generateReport('baptisms', 'pdf')">
                        <i class="fas fa-download"></i> <?php _e('Gerar PDF', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-outline" onclick="HaklaiReports.generateReport('baptisms', 'excel')">
                        <i class="fas fa-file-excel"></i> <?php _e('Excel', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-secondary" onclick="HaklaiReports.viewReport('baptisms')">
                        <i class="fas fa-eye"></i> <?php _e('Visualizar', 'haklai-app'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Relatório de Formações -->
            <div class="report-item">
                <div class="report-header">
                    <div class="report-icon formations">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <div class="report-content">
                        <h6 class="report-title"><?php _e('Relatório de Formações', 'haklai-app'); ?></h6>
                        <p class="report-description">
                            <?php _e('Análise das formações concluídas pelos membros. Mostra porcentagens de pessoas que possuem cada curso (CTL, CME, STP) e estatísticas de desenvolvimento e capacitação.', 'haklai-app'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="report-actions d-flex gap-2">
                    <button class="haklai-btn haklai-btn-primary" onclick="HaklaiReports.generateReport('formacoes', 'pdf')">
                        <i class="fas fa-download"></i> <?php _e('Gerar PDF', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-outline" onclick="HaklaiReports.generateReport('formacoes', 'excel')">
                        <i class="fas fa-file-excel"></i> <?php _e('Excel', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-secondary" onclick="HaklaiReports.viewReport('formacoes')">
                        <i class="fas fa-eye"></i> <?php _e('Visualizar', 'haklai-app'); ?>
                    </button>
                </div>
            </div>
            
            <!-- Relatório de Habilidades -->
            <div class="report-item">
                <div class="report-header">
                    <div class="report-icon skills">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="report-content">
                        <h6 class="report-title"><?php _e('Relatório de Habilidades', 'haklai-app'); ?></h6>
                        <p class="report-description">
                            <?php _e('Mapeamento das habilidades dos membros e análise de talentos disponíveis na igreja. Inclui categorização por áreas de ministério e identificação de necessidades de treinamento.', 'haklai-app'); ?>
                        </p>
                    </div>
                </div>
                
                <div class="report-actions d-flex gap-2">
                    <button class="haklai-btn haklai-btn-primary" onclick="HaklaiReports.generateReport('cells', 'pdf')">
                        <i class="fas fa-download"></i> <?php _e('Gerar PDF', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-outline" onclick="HaklaiReports.generateReport('cells', 'excel')">
                        <i class="fas fa-file-excel"></i> <?php _e('Excel', 'haklai-app'); ?>
                    </button>
                    <button class="haklai-btn haklai-btn-secondary" onclick="HaklaiReports.viewReport('cells')">
                        <i class="fas fa-eye"></i> <?php _e('Visualizar', 'haklai-app'); ?>
                    </button>
                </div>
            </div>
        </div>
        

    </div>
</div>

<script>
// Dados para JavaScript
window.haklaiReports = {
    nonce: '<?php echo wp_create_nonce('haklai_nonce'); ?>',
    ajaxUrl: '<?php echo admin_url('admin-ajax.php'); ?>',
    siteUrl: '<?php echo esc_url(home_url('/')); ?>'
};

// FASE 1: Proteção contra sobrescrita de valores dos cards por scripts globais
// Garante que os valores renderizados pelo PHP sejam mantidos
(function() {
    'use strict';
    
    // Valores originais dos cards (vindos do PHP) - FASE 2
    const originalValues = {
        total_members: <?php echo intval($total_members_consistent ?? $stats['total_members'] ?? 0); ?>,
        total_fa: <?php echo intval($stats['total_fa'] ?? 0); ?>,
        formations: {
            cme: <?php echo floatval($stats['formations']['cme']['percentage'] ?? 0); ?>,
            ctl: <?php echo floatval($stats['formations']['ctl']['percentage'] ?? 0); ?>,
            stp: <?php echo floatval($stats['formations']['stp']['percentage'] ?? 0); ?>
        },
        total_baptisms: <?php echo intval($stats['total_baptisms'] ?? 0); ?>,
        total_visitors_30days: <?php echo intval($stats['total_visitors_30days'] ?? 0); ?>
    };
    
    // Função para restaurar valores originais - FASE 2
    function restoreOriginalValues() {
        const $statsDashboard = jQuery('.reports-container .stats-dashboard');
        if ($statsDashboard.length === 0) {
            return; // Não está na página de relatórios
        }
        
        // Restaura valores dos cards
        const $cards = $statsDashboard.find('.stat-card');
        
        // Card 1: Total de Membros
        if ($cards.length > 0) {
            const $card1 = $cards.eq(0);
            const $number1 = $card1.find('.number');
            if ($number1.length > 0 && !$number1.data('protected')) {
                $number1.data('protected', true);
                $number1.text(originalValues.total_members);
            }
        }
        
        // Card 2: F.A (Frequentador Assíduo) - FASE 2
        if ($cards.length > 1) {
            const $card2 = $cards.eq(1);
            const $number2 = $card2.find('.number');
            if ($number2.length > 0 && !$number2.data('protected')) {
                $number2.data('protected', true);
                $number2.text(originalValues.total_fa);
            }
        }
        
        // Card 3: Formações (CME, CTL, STP) - FASE 2
        if ($cards.length > 2) {
            const $card3 = $cards.eq(2);
            if ($card3.hasClass('stat-card-formations')) {
                // Protege os percentuais de formações
                const $cmePercent = $card3.find('.formation-item').eq(0).find('.formation-percentage');
                const $ctlPercent = $card3.find('.formation-item').eq(1).find('.formation-percentage');
                const $stpPercent = $card3.find('.formation-item').eq(2).find('.formation-percentage');
                
                if ($cmePercent.length > 0 && !$cmePercent.data('protected')) {
                    $cmePercent.data('protected', true);
                    $cmePercent.text(originalValues.formations.cme + '%');
                }
                if ($ctlPercent.length > 0 && !$ctlPercent.data('protected')) {
                    $ctlPercent.data('protected', true);
                    $ctlPercent.text(originalValues.formations.ctl + '%');
                }
                if ($stpPercent.length > 0 && !$stpPercent.data('protected')) {
                    $stpPercent.data('protected', true);
                    $stpPercent.text(originalValues.formations.stp + '%');
                }
            }
        }
        
        // Card 4: Novos Batismos
        if ($cards.length > 3) {
            const $card4 = $cards.eq(3);
            const $number4 = $card4.find('.number');
            if ($number4.length > 0 && !$number4.data('protected')) {
                $number4.data('protected', true);
                $number4.text(originalValues.total_baptisms);
            }
        }
        
        // Card 5: Visitantes (30 dias) - FASE 2
        if ($cards.length > 4) {
            const $card5 = $cards.eq(4);
            const $number5 = $card5.find('.number');
            if ($number5.length > 0 && !$number5.data('protected')) {
                $number5.data('protected', true);
                $number5.text(originalValues.total_visitors_30days);
            }
        }
    }
    
    // Executa quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', restoreOriginalValues);
    } else {
        restoreOriginalValues();
    }
    
    // Proteção adicional: restaura valores após pequeno delay (caso scripts executem depois)
    setTimeout(restoreOriginalValues, 100);
    setTimeout(restoreOriginalValues, 500);
    setTimeout(restoreOriginalValues, 1000);
    
    // Observa mudanças nos elementos (MutationObserver como última proteção)
    if (typeof MutationObserver !== 'undefined') {
        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' || mutation.type === 'characterData') {
                    restoreOriginalValues();
                }
            });
        });
        
        // Observa mudanças nos cards de estatísticas
        jQuery(document).ready(function() {
            const $statsDashboard = jQuery('.reports-container .stats-dashboard');
            if ($statsDashboard.length > 0) {
                $statsDashboard.find('.stat-card .number').each(function() {
                    observer.observe(this, {
                        childList: true,
                        characterData: true,
                        subtree: true
                    });
                });
            }
        });
    }
})();
</script>

