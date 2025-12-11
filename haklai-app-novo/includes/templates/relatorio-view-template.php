<?php
/**
 * Template de Visualização de Relatório de Presença
 * Exibe os resultados do relatório de presença com filtros aplicados
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

// Verifica permissões
if (!haklai_user_can_access('haklai_access_reports')) {
    wp_die(__('Você não tem permissão para acessar este relatório.', 'haklai-app'));
}

// Obtém parâmetros da URL
$report_type = sanitize_text_field($_GET['report_type'] ?? '');
$start_date = sanitize_text_field($_GET['start_date'] ?? '');
$end_date = sanitize_text_field($_GET['end_date'] ?? '');
$cell_id = intval($_GET['cell_id'] ?? 0);

// Valida parâmetros
if (empty($report_type) || empty($start_date) || empty($end_date)) {
    wp_die(__('Parâmetros inválidos. Por favor, selecione um período válido.', 'haklai-app'));
}

// Valida formato de data
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $start_date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $end_date)) {
    wp_die(__('Formato de data inválido.', 'haklai-app'));
}

// Obtém dados do relatório
$report_data = array();
$cell_name = __('Todas as Células', 'haklai-app');

if ($report_type === 'attendance') {
    // Busca dados de presença
    $report_data = Haklai_DB::get_attendance_report($start_date, $end_date, $cell_id ?: null);
    
    // Obtém nome da célula se filtrada
    if ($cell_id) {
        $cell = Haklai_DB::get_cell($cell_id);
        if ($cell) {
            $cell_name = $cell->name;
        }
    }
} else {
    wp_die(__('Tipo de relatório não suportado.', 'haklai-app'));
}

// Calcula estatísticas gerais
$total_meetings = count($report_data);
$total_present = 0;
$total_absent = 0;
$total_members = 0;
$attendance_rates = array();

foreach ($report_data as $row) {
    $total_present += intval($row->present_count);
    $total_absent += intval($row->absent_count);
    $total_members += intval($row->total_members);
    if (!empty($row->attendance_rate)) {
        $attendance_rates[] = floatval($row->attendance_rate);
    }
}

$avg_attendance_rate = !empty($attendance_rates) ? round(array_sum($attendance_rates) / count($attendance_rates), 2) : 0;
$overall_attendance_rate = ($total_present + $total_absent) > 0 
    ? round(($total_present * 100) / ($total_present + $total_absent), 2) 
    : 0;

// Obtém informações do usuário
$current_user = wp_get_current_user();
$user_avatar = get_avatar_url($current_user->ID, array('size' => 40));
$user_level = Haklai_Permissions::get_user_level(get_current_user_id());

// Formata datas para exibição
$start_date_formatted = date_i18n('d/m/Y', strtotime($start_date));
$end_date_formatted = date_i18n('d/m/Y', strtotime($end_date));
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Relatório de Presença', 'haklai-app'); ?> - <?php bloginfo('name'); ?></title>
    <?php wp_head(); ?>
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 12px;
        }
        .report-view-container {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        .report-view-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 24px 28px;
            position: relative;
        }
        .header-content {
            position: relative;
            z-index: 1;
        }
        .header-content h1 {
            margin: 0 0 8px 0;
            font-size: 1.5rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .header-content .subtitle {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-bottom: 16px;
        }
        .report-filters {
            background: rgba(255,255,255,0.15);
            padding: 12px 16px;
            border-radius: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
        }
        .filter-item {
            flex: 1;
            min-width: 150px;
        }
        .filter-item label {
            display: block;
            font-size: 0.75rem;
            opacity: 0.85;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .filter-item .value {
            font-size: 0.95rem;
            font-weight: 600;
        }
        .report-view-content {
            padding: 24px 28px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 12px;
            margin-bottom: 24px;
        }
        .stat-card {
            background: white;
            padding: 16px 18px;
            border-radius: 10px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 14px;
        }
        .stat-card:hover {
            border-color: #d1d5db;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .stat-card .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        .stat-card .stat-icon.presence { background: #10b981; color: white; }
        .stat-card .stat-icon.absent { background: #ef4444; color: white; }
        .stat-card .stat-icon.meetings { background: #3b82f6; color: white; }
        .stat-card .stat-icon.rate { background: #8b5cf6; color: white; }
        .stat-card .stat-content {
            flex: 1;
            min-width: 0;
        }
        .stat-card .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #111827;
            margin-bottom: 2px;
            line-height: 1.2;
        }
        .stat-card .stat-label {
            font-size: 0.8rem;
            color: #6b7280;
            line-height: 1.3;
        }
        .report-table-wrapper {
            overflow-x: auto;
            margin-top: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        .report-table thead {
            background: #f9fafb;
        }
        .report-table th {
            padding: 10px 14px;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #f3f4f6;
            color: #4b5563;
            font-size: 0.875rem;
        }
        .report-table tbody tr:last-child td {
            border-bottom: none;
        }
        .report-table tbody tr:hover {
            background: #f9fafb;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .badge i {
            font-size: 0.7rem;
        }
        .badge-success {
            background: #d1fae5;
            color: #065f46;
        }
        .badge-danger {
            background: #fee2e2;
            color: #991b1b;
        }
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
        .report-actions {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 8px 16px;
            border-radius: 6px;
            border: none;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn i {
            font-size: 0.8rem;
        }
        .btn-primary {
            background: #667eea;
            color: white;
        }
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
        }
        .btn-secondary {
            background: #f3f4f6;
            color: #4b5563;
        }
        .btn-secondary:hover {
            background: #e5e7eb;
        }
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6b7280;
        }
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 12px;
            opacity: 0.4;
        }
        .empty-state h3 {
            color: #374151;
            margin-bottom: 8px;
            font-size: 1.1rem;
        }
        .empty-state p {
            font-size: 0.875rem;
        }
        @media print {
            body { background: white; padding: 0; }
            .report-actions { display: none; }
            .btn { display: none; }
        }
        @media (max-width: 768px) {
            body { padding: 8px; }
            .report-view-header { padding: 16px 20px; }
            .report-view-content { padding: 16px 20px; }
            .stats-grid { grid-template-columns: 1fr; gap: 10px; }
            .report-filters { flex-direction: column; gap: 12px; }
            .stat-card { padding: 14px 16px; }
        }
    </style>
</head>
<body>
    <div class="report-view-container">
        <!-- Header -->
        <div class="report-view-header">
            <div class="header-content">
                <h1>
                    <i class="fas fa-users"></i> <?php _e('Relatório de Presença', 'haklai-app'); ?>
                </h1>
                <div class="subtitle"><?php _e('Análise detalhada da presença em reuniões', 'haklai-app'); ?></div>
                
                <!-- Filtros Aplicados -->
                <div class="report-filters">
                    <div class="filter-item">
                        <label><?php _e('Período', 'haklai-app'); ?></label>
                        <div class="value"><?php echo esc_html($start_date_formatted); ?> - <?php echo esc_html($end_date_formatted); ?></div>
                    </div>
                    <div class="filter-item">
                        <label><?php _e('Célula', 'haklai-app'); ?></label>
                        <div class="value"><?php echo esc_html($cell_name); ?></div>
                    </div>
                    <div class="filter-item">
                        <label><?php _e('Total de Reuniões', 'haklai-app'); ?></label>
                        <div class="value"><?php echo number_format_i18n($total_meetings); ?></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="report-view-content">
            <!-- Estatísticas Resumidas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon presence">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format_i18n($total_present); ?></div>
                        <div class="stat-label"><?php _e('Total de Presenças', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon absent">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format_i18n($total_absent); ?></div>
                        <div class="stat-label"><?php _e('Total de Ausências', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon meetings">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format_i18n($total_meetings); ?></div>
                        <div class="stat-label"><?php _e('Reuniões Realizadas', 'haklai-app'); ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon rate">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo number_format_i18n($avg_attendance_rate, 1); ?>%</div>
                        <div class="stat-label"><?php _e('Taxa Média de Presença', 'haklai-app'); ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Tabela de Detalhes -->
            <?php if (!empty($report_data)): ?>
                <div class="report-table-wrapper">
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th><?php _e('Data', 'haklai-app'); ?></th>
                                <th><?php _e('Célula', 'haklai-app'); ?></th>
                                <th><?php _e('Presentes', 'haklai-app'); ?></th>
                                <th><?php _e('Ausentes', 'haklai-app'); ?></th>
                                <th><?php _e('Total Membros', 'haklai-app'); ?></th>
                                <th><?php _e('Taxa de Presença', 'haklai-app'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data as $row): 
                                $meeting_date = date_i18n('d/m/Y', strtotime($row->meeting_date));
                                $attendance_rate = floatval($row->attendance_rate ?? 0);
                                $rate_class = $attendance_rate >= 80 ? 'badge-success' : ($attendance_rate >= 50 ? 'badge-info' : 'badge-danger');
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($meeting_date); ?></strong></td>
                                    <td><?php echo esc_html($row->cell_name ?: __('Não informado', 'haklai-app')); ?></td>
                                    <td>
                                        <span class="badge badge-success">
                                            <i class="fas fa-check"></i> <?php echo number_format_i18n($row->present_count); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-danger">
                                            <i class="fas fa-times"></i> <?php echo number_format_i18n($row->absent_count); ?>
                                        </span>
                                    </td>
                                    <td><?php echo number_format_i18n($row->total_members); ?></td>
                                    <td>
                                        <span class="badge <?php echo esc_attr($rate_class); ?>">
                                            <?php echo number_format_i18n($attendance_rate, 1); ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3><?php _e('Nenhum dado encontrado', 'haklai-app'); ?></h3>
                    <p><?php _e('Não há registros de presença para o período e filtros selecionados.', 'haklai-app'); ?></p>
                </div>
            <?php endif; ?>
            
            <!-- Ações -->
            <div class="report-actions">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> <?php _e('Imprimir', 'haklai-app'); ?>
                </button>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> <?php _e('Voltar', 'haklai-app'); ?>
                </a>
            </div>
        </div>
    </div>
    
    <?php wp_footer(); ?>
</body>
</html>
<?php
exit;
