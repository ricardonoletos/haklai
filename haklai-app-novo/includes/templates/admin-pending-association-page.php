<?php
/**
 * Template da página "Aguardando Associação"
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Aguardando Associação', 'haklai-app'); ?> - Haklai</title>
    <?php wp_head(); ?>
    <style>
        .haklai-pending-wrap {
            margin: 20px 20px 20px 0;
            max-width: 800px;
        }

        .haklai-pending-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 40px;
            text-align: center;
        }

        .haklai-pending-icon {
            font-size: 80px;
            margin-bottom: 20px;
        }

        .haklai-pending-title {
            font-size: 28px;
            color: #1d2327;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .haklai-pending-message {
            font-size: 16px;
            color: #50575e;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .haklai-admin-info {
            background: #f6f7f7;
            border-radius: 6px;
            padding: 25px;
            margin-top: 30px;
            text-align: left;
        }

        .haklai-admin-info h3 {
            font-size: 18px;
            color: #1d2327;
            margin: 0 0 15px 0;
            font-weight: 600;
        }

        .haklai-admin-info p {
            font-size: 14px;
            color: #50575e;
            margin: 8px 0;
        }

        .haklai-admin-info strong {
            color: #1d2327;
        }

        .haklai-admin-info a {
            color: #667eea;
            text-decoration: none;
        }

        .haklai-admin-info a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .haklai-pending-wrap {
                margin: 20px 10px;
            }

            .haklai-pending-card {
                padding: 30px 20px;
            }

            .haklai-pending-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <div class="haklai-pending-wrap">
        <div class="haklai-pending-card">
            <div class="haklai-pending-icon">⏳</div>
            <h1 class="haklai-pending-title"><?php _e('Aguardando Associação', 'haklai-app'); ?></h1>
            <p class="haklai-pending-message">
                <?php _e('Você precisa ser associado a um tenant por um administrador do sistema antes de poder acessar o Haklai.', 'haklai-app'); ?>
            </p>
            <p class="haklai-pending-message">
                <?php _e('Entre em contato com o administrador do sistema para solicitar sua associação a um tenant.', 'haklai-app'); ?>
            </p>

            <?php if (!empty($admin_email)): ?>
            <div class="haklai-admin-info">
                <h3><?php _e('Informações de Contato', 'haklai-app'); ?></h3>
                <p>
                    <strong><?php _e('Administrador:', 'haklai-app'); ?></strong> 
                    <?php echo esc_html($admin_name); ?>
                </p>
                <p>
                    <strong><?php _e('E-mail:', 'haklai-app'); ?></strong> 
                    <a href="mailto:<?php echo esc_attr($admin_email); ?>">
                        <?php echo esc_html($admin_email); ?>
                    </a>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>

