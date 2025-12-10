<?php
/**
 * Template de Impressão de Relatório
 * Página HTML simples para impressão de relatórios
 *
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

// Verifica se é uma requisição AJAX
if (!wp_doing_ajax()) {
    wp_die(__('Acesso negado', 'haklai-app'));
}

// Verifica nonce
if (!wp_verify_nonce($_POST['nonce'] ?? '', 'haklai_nonce')) {
    wp_die(__('Token de segurança inválido', 'haklai-app'));
}

$report_id = intval($_POST['report_id'] ?? 0);
if (!$report_id) {
    wp_die(__('ID do relatório inválido', 'haklai-app'));
}

// Busca o relatório
$report = get_post($report_id);
if (!$report || $report->post_type !== 'haklai_report') {
    wp_die(__('Relatório não encontrado', 'haklai-app'));
}

// Busca dados do relatório
$meeting_id = get_post_meta($report_id, '_haklai_meeting_id', true);
$meeting = $meeting_id ? get_post($meeting_id) : null;
$cell_id = $meeting ? get_post_meta($meeting_id, '_haklai_cell_id', true) : null;
$cell = $cell_id ? get_post($cell_id) : null;
$leader_id = $cell ? get_post_meta($cell_id, '_haklai_leader_id', true) : null;
$leader = $leader_id ? get_userdata($leader_id) : null;

// Busca estatísticas da reunião
$attendance = get_posts(array(
    'post_type' => 'haklai_attendance',
    'meta_query' => array(
        array(
            'key' => '_haklai_meeting_id',
            'value' => $meeting_id
        )
    ),
    'posts_per_page' => -1
));

$members = get_posts(array(
    'post_type' => 'haklai_member',
    'meta_query' => array(
        array(
            'key' => '_haklai_cell_id',
            'value' => $cell_id
        )
    ),
    'posts_per_page' => -1
));

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

$presence_rate = $total_members > 0 ? round(($present_members / $total_members) * 100, 1) : 0;

$stats = array(
    'total_members' => $total_members,
    'present_members' => $present_members,
    'presence_rate' => $presence_rate
);

// Define cabeçalho para HTML
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($report->post_title); ?> - Haklai Church</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            line-height: 1.6;
            color: #333;
            background: white;
            padding: 20px;
        }
        
        .print-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        
        .header h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
        }
        
        .header .subtitle {
            color: #666;
            font-size: 16px;
        }
        
        .report-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #667eea;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            color: #667eea;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }
        
        .info-value {
            font-size: 16px;
            color: #333;
        }
        
        .stats-section {
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .stat-card {
            background: #667eea;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .stat-number {
            font-size: 32px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .members-section {
            margin-bottom: 30px;
        }
        
        .section-title {
            color: #667eea;
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .member-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #10b981;
        }
        
        .member-item.absent {
            border-left-color: #ef4444;
            opacity: 0.7;
        }
        
        .member-name {
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .member-status {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .status-present {
            color: #10b981;
        }
        
        .status-absent {
            color: #ef4444;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e2e8f0;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        
        @media print {
            body {
                padding: 0;
            }
            
            .print-container {
                max-width: none;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        @media screen {
            .print-button {
                position: fixed;
                top: 20px;
                right: 20px;
                background: #667eea;
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 6px;
                cursor: pointer;
                font-weight: bold;
                z-index: 1000;
            }
            
            .print-button:hover {
                background: #5a67d8;
            }
        }
    </style>
</head>
<body>
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Imprimir Relatório
    </button>
    
    <div class="print-container">
        <!-- Cabeçalho -->
        <div class="header">
            <div class="logo">HAKLAI CHURCH</div>
            <h1><?php echo esc_html($report->post_title); ?></h1>
            <div class="subtitle">Relatório de Reunião de Célula</div>
        </div>
        
        <!-- Informações do Relatório -->
        <div class="report-info">
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Data da Reunião</div>
                    <div class="info-value">
                        <?php 
                        if ($meeting) {
                            $meeting_date = get_post_meta($meeting_id, '_haklai_meeting_date', true);
                            echo esc_html(date_i18n('d/m/Y H:i', strtotime($meeting_date ?: $meeting->post_date)));
                        } else {
                            echo esc_html(date_i18n('d/m/Y H:i', strtotime($report->post_date)));
                        }
                        ?>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Célula</div>
                    <div class="info-value"><?php echo esc_html($cell ? $cell->post_title : 'N/A'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Líder</div>
                    <div class="info-value"><?php echo esc_html($leader ? $leader->display_name : 'N/A'); ?></div>
                </div>
                
                <div class="info-item">
                    <div class="info-label">Endereço</div>
                    <div class="info-value"><?php echo esc_html($meeting ? get_post_meta($meeting_id, '_haklai_address', true) : 'N/A'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Estatísticas -->
        <div class="stats-section">
            <h2 class="section-title">Estatísticas da Reunião</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['total_members']); ?></div>
                    <div class="stat-label">Total de Membros</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['present_members']); ?></div>
                    <div class="stat-label">Presentes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo esc_html($stats['presence_rate']); ?>%</div>
                    <div class="stat-label">Taxa de Presença</div>
                </div>
            </div>
        </div>
        
        <!-- Lista de Membros -->
        <div class="members-section">
            <h2 class="section-title">Lista de Presença</h2>
            <div class="members-grid">
                <?php foreach ($members as $member): ?>
                    <?php 
                    $is_present = false;
                    foreach ($attendance as $att) {
                        $member_id = get_post_meta($att->ID, '_haklai_member_id', true);
                        if ($member_id == $member->ID) {
                            $is_present = get_post_meta($att->ID, '_haklai_is_present', true) === '1';
                            break;
                        }
                    }
                    ?>
                    <div class="member-item <?php echo $is_present ? '' : 'absent'; ?>">
                        <div class="member-name"><?php echo esc_html($member->post_title); ?></div>
                        <div class="member-status <?php echo $is_present ? 'status-present' : 'status-absent'; ?>">
                            <?php echo $is_present ? 'Presente' : 'Ausente'; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Rodapé -->
        <div class="footer">
            <p>Relatório gerado em <?php echo esc_html(date_i18n('d/m/Y H:i')); ?></p>
            <p>Haklai Church - Sistema de Gestão de Células</p>
        </div>
    </div>
    
    <script>
        // Auto-print se solicitado
        if (window.location.search.includes('autoprint=1')) {
            window.onload = function() {
                setTimeout(function() {
                    window.print();
                }, 1000);
            };
        }
    </script>
</body>
</html>
<?php
exit;
?>
