<?php
/**
 * Template de Email - Credenciais de Acesso
 * 
 * Variáveis disponíveis:
 * @var WP_User $user Usuário criado
 * @var string $password Senha gerada
 * @var int $member_id ID do membro
 * @var string $member_name Nome do membro
 * @var string $cell_name Nome da célula
 * @var string $role_name Nome do role/função
 */

if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html(get_bloginfo('name')); ?> - Credenciais de Acesso</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 700;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 16px;
            opacity: 0.9;
        }
        .content {
            padding: 40px 30px;
        }
        .greeting {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        .intro-text {
            font-size: 16px;
            color: #555;
            margin-bottom: 30px;
        }
        .credentials-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 25px;
            margin: 30px 0;
            border-radius: 4px;
        }
        .credentials-box h3 {
            margin-top: 0;
            color: #2c3e50;
            font-size: 18px;
            display: flex;
            align-items: center;
        }
        .credential-item {
            margin: 15px 0;
            padding: 12px;
            background: white;
            border-radius: 4px;
        }
        .credential-label {
            display: block;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            margin-bottom: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .credential-value {
            display: block;
            color: #667eea;
            font-size: 16px;
            font-weight: 600;
            word-break: break-all;
        }
        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .alert-box strong {
            color: #856404;
            display: block;
            margin-bottom: 5px;
        }
        .alert-box p {
            margin: 0;
            color: #856404;
            font-size: 14px;
        }
        .info-section {
            background: #e7f3ff;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
            border-left: 4px solid #2196F3;
        }
        .info-section h3 {
            margin-top: 0;
            color: #1976D2;
            font-size: 16px;
        }
        .info-section ul {
            margin: 10px 0;
            padding-left: 20px;
        }
        .info-section li {
            margin: 8px 0;
            color: #555;
        }
        .button-container {
            text-align: center;
            margin: 35px 0;
        }
        .button {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }
        .footer {
            background: #2c3e50;
            color: #ecf0f1;
            text-align: center;
            padding: 30px 20px;
            font-size: 13px;
        }
        .footer p {
            margin: 5px 0;
        }
        .footer a {
            color: #3498db;
            text-decoration: none;
        }
        .divider {
            height: 1px;
            background: #e0e0e0;
            margin: 25px 0;
        }
        @media only screen and (max-width: 600px) {
            .content {
                padding: 25px 15px;
            }
            .header h1 {
                font-size: 24px;
            }
            .credentials-box {
                padding: 20px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <h1><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <p>Sistema de Gerenciamento de Células</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <!-- Greeting -->
            <div class="greeting">
                Olá, <?php echo esc_html($member_name); ?>! 👋
            </div>
            
            <!-- Intro -->
            <p class="intro-text">
                Seja bem-vindo(a) ao <strong>Sistema Haklai</strong>! Sua conta foi criada com sucesso e agora você tem acesso completo à plataforma de gerenciamento.
            </p>
            
            <!-- Credentials Box -->
            <div class="credentials-box">
                <h3>🔐 Suas Credenciais de Acesso</h3>
                
                <div class="credential-item">
                    <span class="credential-label">🌐 URL de Login</span>
                    <span class="credential-value"><?php echo esc_url(wp_login_url()); ?></span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">👤 Nome de Usuário</span>
                    <span class="credential-value"><?php echo esc_html($user->user_login); ?></span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">📧 E-mail</span>
                    <span class="credential-value"><?php echo esc_html($user->user_email); ?></span>
                </div>
                
                <div class="credential-item">
                    <span class="credential-label">🔑 Senha Temporária</span>
                    <span class="credential-value"><?php echo esc_html($password); ?></span>
                </div>
            </div>
            
            <!-- Alert -->
            <div class="alert-box">
                <strong>⚠️ Ação Necessária</strong>
                <p>Por motivos de segurança, recomendamos que você altere sua senha no primeiro acesso. Você pode fazer isso em "Perfil" após o login.</p>
            </div>
            
            <!-- User Info -->
            <div class="info-section">
                <h3>📊 Informações da Sua Conta</h3>
                <ul>
                    <li><strong>Função no Sistema:</strong> <?php echo esc_html($role_name); ?></li>
                    <li><strong>Célula Associada:</strong> <?php echo esc_html($cell_name); ?></li>
                    <li><strong>Status:</strong> Ativo</li>
                </ul>
            </div>
            
            <!-- CTA Button -->
            <div class="button-container">
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="button">
                    ✨ Acessar Sistema Agora
                </a>
            </div>
            
            <div class="divider"></div>
            
            <!-- Help Text -->
            <p style="color: #777; font-size: 14px; text-align: center;">
                Caso tenha alguma dúvida ou precise de ajuda, entre em contato com o administrador do sistema.
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong><?php echo esc_html(get_bloginfo('name')); ?></strong></p>
            <p>Este é um email automático. Por favor, não responda diretamente a esta mensagem.</p>
            <p style="margin-top: 15px; opacity: 0.7;">
                © <?php echo date('Y'); ?> - Todos os direitos reservados
            </p>
        </div>
    </div>
</body>
</html>




