<?php
/**
 * Template da página de boas-vindas do Haklai no WP-Admin
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
if (!current_user_can('haklai_access_dashboard')) {
    wp_die(__('Acesso negado', 'haklai-app'));
}

// NOTA: A verificação de tenant e redirecionamento foi movida para o callback
// (admin_menu_redirect_callback em haklai-app-novo.php) para evitar erro "headers already sent"
// Este template só é incluído se o usuário já tiver tenant associado

// Obtém estatísticas rápidas
$total_members = wp_count_posts('haklai_member');
$total_cells = wp_count_posts('haklai_cell');
$total_meetings = wp_count_posts('haklai_meeting');
$total_reports = wp_count_posts('haklai_report');
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        .haklai-welcome-wrap {
            margin: 20px 20px 20px 0;
        }

        .haklai-welcome-wrap * {
            box-sizing: border-box;
        }

        .haklai-wrap-inner {
            max-width: 1400px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .haklai-header-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 60px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .haklai-header-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 600px;
            height: 600px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .haklai-header-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -5%;
            width: 400px;
            height: 400px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
        }

        .haklai-header-content {
            position: relative;
            z-index: 1;
        }

        .haklai-header-banner h1 {
            font-size: 42px;
            font-weight: 700;
            margin: 0 0 15px 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .haklai-header-banner .haklai-subtitle {
            font-size: 18px;
            opacity: 0.95;
            font-weight: 400;
        }

        .haklai-version-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            margin-top: 15px;
            backdrop-filter: blur(10px);
        }

        .haklai-content-section {
            padding: 50px 40px;
        }

        .haklai-welcome-text {
            text-align: center;
            margin-bottom: 50px;
        }

        .haklai-welcome-text h2 {
            font-size: 32px;
            color: #1d2327;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .haklai-welcome-text p {
            font-size: 16px;
            color: #50575e;
            line-height: 1.6;
            max-width: 800px;
            margin: 0 auto;
        }

        .haklai-stats-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }

        .haklai-stat-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 30px;
            border-radius: 8px;
            text-align: center;
        }

        .haklai-stat-number {
            font-size: 36px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .haklai-stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .haklai-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .haklai-feature-card {
            background: #fff;
            border: 1px solid #dcdcde;
            border-radius: 8px;
            padding: 30px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .haklai-feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .haklai-feature-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transform: translateY(-4px);
        }

        .haklai-feature-card:hover::before {
            transform: scaleX(1);
        }

        .haklai-feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 28px;
        }

        .haklai-feature-card h3 {
            font-size: 20px;
            color: #1d2327;
            margin: 0 0 12px 0;
            font-weight: 600;
        }

        .haklai-feature-card p {
            font-size: 14px;
            color: #50575e;
            line-height: 1.6;
            margin: 0;
        }

        .haklai-quick-links {
            background: #f6f7f7;
            padding: 40px;
            margin-top: 50px;
            border-radius: 8px;
        }

        .haklai-quick-links h3 {
            font-size: 24px;
            color: #1d2327;
            margin: 0 0 25px 0;
            text-align: center;
            font-weight: 600;
        }

        .haklai-links-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .haklai-link-button {
            display: block;
            padding: 20px;
            background: #fff;
            border: 2px solid #dcdcde;
            border-radius: 6px;
            text-decoration: none;
            color: #1d2327;
            transition: all 0.3s ease;
            text-align: center;
        }

        .haklai-link-button:hover,
        .haklai-link-button:focus {
            border-color: #667eea;
            background: #667eea;
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102,126,234,0.3);
        }

        .haklai-link-button-icon {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }

        .haklai-link-button-title {
            font-size: 16px;
            font-weight: 600;
            display: block;
        }

        .haklai-footer-info {
            text-align: center;
            padding: 30px;
            border-top: 1px solid #dcdcde;
            margin-top: 40px;
            color: #50575e;
        }

        .haklai-footer-info p {
            margin: 5px 0;
            font-size: 14px;
        }

        .haklai-footer-info a {
            color: #667eea;
            text-decoration: none;
        }

        .haklai-footer-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .haklai-header-banner {
                padding: 40px 20px;
            }

            .haklai-header-banner h1 {
                font-size: 32px;
            }

            .haklai-content-section {
                padding: 30px 20px;
            }

            .haklai-features-grid {
                grid-template-columns: 1fr;
            }

            .haklai-stats-section {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 480px) {
            .haklai-stats-section {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="haklai-welcome-wrap">
        <div class="haklai-wrap-inner">
            <!-- Header Banner -->
            <div class="haklai-header-banner">
                <div class="haklai-header-content">
                    <h1>🏛️ Haklai Church Management</h1>
                    <p class="haklai-subtitle"><?php _e('Sistema Completo de Gestão para Igrejas', 'haklai-app'); ?></p>
                    <span class="haklai-version-badge"><?php echo sprintf(__('Versão %s', 'haklai-app'), HAKLAI_VERSION); ?></span>
                </div>
            </div>

            <!-- Welcome Section -->
            <div class="haklai-content-section">
                <div class="haklai-welcome-text">
                    <h2><?php _e('Bem-vindo ao Haklai!', 'haklai-app'); ?></h2>
                    <p>
                        <?php _e('O Haklai é um sistema moderno e completo desenvolvido especialmente para gerenciar todos os aspectos da sua igreja. Com uma interface intuitiva e recursos poderosos, você pode gerenciar membros, células, reuniões, relatórios e muito mais, tudo em um só lugar.', 'haklai-app'); ?>
                    </p>
                </div>

                <!-- Stats Section -->
                <div class="haklai-stats-section">
                    <div class="haklai-stat-box">
                        <div class="haklai-stat-number"><?php echo intval($total_members->publish ?? 0); ?></div>
                        <div class="haklai-stat-label"><?php _e('Membros Cadastrados', 'haklai-app'); ?></div>
                    </div>
                    <div class="haklai-stat-box">
                        <div class="haklai-stat-number"><?php echo intval($total_cells->publish ?? 0); ?></div>
                        <div class="haklai-stat-label"><?php _e('Células Ativas', 'haklai-app'); ?></div>
                    </div>
                    <div class="haklai-stat-box">
                        <div class="haklai-stat-number"><?php echo intval($total_meetings->publish ?? 0); ?></div>
                        <div class="haklai-stat-label"><?php _e('Reuniões Registradas', 'haklai-app'); ?></div>
                    </div>
                    <div class="haklai-stat-box">
                        <div class="haklai-stat-number"><?php echo intval($total_reports->publish ?? 0); ?></div>
                        <div class="haklai-stat-label"><?php _e('Relatórios Gerados', 'haklai-app'); ?></div>
                    </div>
                </div>

                <!-- Features Grid -->
                <div class="haklai-features-grid">
                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">👥</div>
                        <h3><?php _e('Gestão de Membros', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Cadastre e gerencie todos os membros da sua igreja com informações completas: dados pessoais, foto, habilidades, status de batismo, formações (CTL, CME, STP), participação no Encontro com Deus e muito mais.', 'haklai-app'); ?>
                        </p>
                    </div>

                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">🏘️</div>
                        <h3><?php _e('Controle de Células', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Organize e acompanhe todas as células da sua igreja. Defina líderes, discipuladores, endereços, redes e mantenha tudo organizado para um crescimento estruturado.', 'haklai-app'); ?>
                        </p>
                    </div>

                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">📅</div>
                        <h3><?php _e('Reuniões e Presença', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Registre reuniões de células com facilidade. Faça controle de presença em tempo real e acompanhe o histórico de participação de cada membro nas últimas reuniões.', 'haklai-app'); ?>
                        </p>
                    </div>

                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">📊</div>
                        <h3><?php _e('Relatórios Inteligentes', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Gere relatórios detalhados de reuniões, presença, crescimento e estatísticas. Exporte para impressão ou envie por e-mail. Tome decisões baseadas em dados reais.', 'haklai-app'); ?>
                        </p>
                    </div>

                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">🔐</div>
                        <h3><?php _e('Controle de Permissões', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Sistema hierárquico de permissões com 6 níveis: Membro, Líder de Célula, Discipulador, Pastor de Rede, Pastor Senior e Pastor Supervisor. Cada nível tem acesso específico.', 'haklai-app'); ?>
                        </p>
                    </div>

                    <div class="haklai-feature-card">
                        <div class="haklai-feature-icon">📥</div>
                        <h3><?php _e('Importação/Exportação', 'haklai-app'); ?></h3>
                        <p>
                            <?php _e('Importe dados em massa via planilhas Excel/CSV e exporte seus dados para backup. Histórico completo de todas as importações realizadas.', 'haklai-app'); ?>
                        </p>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="haklai-quick-links">
                    <h3>🚀 <?php _e('Acesso Rápido', 'haklai-app'); ?></h3>
                    <div class="haklai-links-grid">
                        <a href="<?php echo admin_url('edit.php?post_type=haklai_member'); ?>" class="haklai-link-button">
                            <span class="haklai-link-button-icon">👥</span>
                            <span class="haklai-link-button-title"><?php _e('Membros', 'haklai-app'); ?></span>
                        </a>
                        <a href="<?php echo admin_url('edit.php?post_type=haklai_cell'); ?>" class="haklai-link-button">
                            <span class="haklai-link-button-icon">🏘️</span>
                            <span class="haklai-link-button-title"><?php _e('Células', 'haklai-app'); ?></span>
                        </a>
                        <a href="<?php echo admin_url('edit.php?post_type=haklai_meeting'); ?>" class="haklai-link-button">
                            <span class="haklai-link-button-icon">📅</span>
                            <span class="haklai-link-button-title"><?php _e('Reuniões', 'haklai-app'); ?></span>
                        </a>
                        <a href="<?php echo admin_url('edit.php?post_type=haklai_report'); ?>" class="haklai-link-button">
                            <span class="haklai-link-button-icon">📊</span>
                            <span class="haklai-link-button-title"><?php _e('Relatórios', 'haklai-app'); ?></span>
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=haklai-settings'); ?>" class="haklai-link-button">
                            <span class="haklai-link-button-icon">⚙️</span>
                            <span class="haklai-link-button-title"><?php _e('Configurações', 'haklai-app'); ?></span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="haklai-footer-info">
                <p><strong><?php echo sprintf(__('Haklai Church Management System v%s', 'haklai-app'), HAKLAI_VERSION); ?></strong></p>
                <p><?php echo sprintf(__('Desenvolvido por %s', 'haklai-app'), '<a href="https://linx.pt" target="_blank">Ricardo Sarmento</a>'); ?></p>
                <p><?php echo sprintf(__('© %s - Todos os direitos reservados', 'haklai-app'), date('Y')); ?></p>
            </div>
        </div>
    </div>
</body>
</html>

