<?php
/**
 * Template da página de onboarding do Haklai
 * Formulário de registro inicial para Tenant Owner
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

// Obtém dados do usuário atual
$current_user = wp_get_current_user();
$user_name = $current_user->display_name ?: $current_user->user_login;
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Configuração Inicial - Haklai', 'haklai-app'); ?></title>
</head>
<body class="haklai-onboarding-page">
    <div class="haklai-onboarding-wrap">
        <div class="haklai-onboarding-container">
            <!-- Header -->
            <div class="haklai-onboarding-header">
                <div class="haklai-onboarding-logo">
                    <span class="haklai-logo-icon">⛪</span>
                    <h1><?php _e('Haklai', 'haklai-app'); ?></h1>
                </div>
                <p class="haklai-onboarding-subtitle">
                    <?php _e('Bem-vindo! Vamos configurar seu grupo.', 'haklai-app'); ?>
                </p>
            </div>

            <!-- Formulário -->
            <div class="haklai-onboarding-content">
                <form id="haklai-onboarding-form" class="haklai-onboarding-form">
                    <div class="haklai-form-section">
                        <h2 class="haklai-form-title">
                            <?php _e('Informações do Grupo', 'haklai-app'); ?>
                        </h2>
                        <p class="haklai-form-description">
                            <?php _e('Preencha os dados abaixo para configurar seu grupo no sistema Haklai.', 'haklai-app'); ?>
                        </p>

                        <!-- Campo: Nome do Supervisor -->
                        <div class="haklai-form-group">
                            <label for="supervisor_name" class="haklai-form-label">
                                <?php _e('Nome do Supervisor', 'haklai-app'); ?>
                                <span class="haklai-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="supervisor_name" 
                                name="supervisor_name" 
                                class="haklai-form-input" 
                                value="<?php echo esc_attr($user_name); ?>"
                                placeholder="<?php esc_attr_e('Digite o nome do supervisor', 'haklai-app'); ?>"
                                required
                                autocomplete="name"
                            />
                            <p class="haklai-form-help">
                                <?php _e('Nome do Pastor Supervisor responsável pelo grupo.', 'haklai-app'); ?>
                            </p>
                        </div>

                        <!-- Campo: Nome da Igreja -->
                        <div class="haklai-form-group">
                            <label for="church_name" class="haklai-form-label">
                                <?php _e('Nome da Igreja', 'haklai-app'); ?>
                                <span class="haklai-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="church_name" 
                                name="church_name" 
                                class="haklai-form-input" 
                                placeholder="<?php esc_attr_e('Digite o nome da igreja', 'haklai-app'); ?>"
                                required
                                autocomplete="organization"
                            />
                            <p class="haklai-form-help">
                                <?php _e('Este nome será usado para identificar seu grupo no sistema.', 'haklai-app'); ?>
                            </p>
                        </div>

                        <!-- Campo: Localidade -->
                        <div class="haklai-form-group">
                            <label for="location" class="haklai-form-label">
                                <?php _e('Localidade', 'haklai-app'); ?>
                                <span class="haklai-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="location" 
                                name="location" 
                                class="haklai-form-input" 
                                placeholder="<?php esc_attr_e('Ex: Lisboa, Portugal', 'haklai-app'); ?>"
                                required
                                autocomplete="address-level2"
                            />
                            <p class="haklai-form-help">
                                <?php _e('Cidade e país onde o grupo está localizado.', 'haklai-app'); ?>
                            </p>
                        </div>

                        <!-- Campo: Contacto -->
                        <div class="haklai-form-group">
                            <label for="contact" class="haklai-form-label">
                                <?php _e('Contacto do Responsável', 'haklai-app'); ?>
                                <span class="haklai-required">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="contact" 
                                name="contact" 
                                class="haklai-form-input" 
                                placeholder="<?php esc_attr_e('Ex: +351 912 345 678', 'haklai-app'); ?>"
                                required
                                autocomplete="tel"
                            />
                            <p class="haklai-form-help">
                                <?php _e('Telefone ou email de contacto do responsável pelo grupo.', 'haklai-app'); ?>
                            </p>
                        </div>
                    </div>

                    <!-- Mensagens de Feedback -->
                    <div id="haklai-onboarding-messages" class="haklai-onboarding-messages" style="display: none;"></div>

                    <!-- Botões -->
                    <div class="haklai-form-actions">
                        <button 
                            type="submit" 
                            id="haklai-onboarding-submit" 
                            class="haklai-button haklai-button-primary"
                        >
                            <span class="haklai-button-text">
                                <?php _e('Avançar', 'haklai-app'); ?>
                            </span>
                            <span class="haklai-button-spinner" style="display: none;">
                                <span class="spinner is-active"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Footer -->
            <div class="haklai-onboarding-footer">
                <p class="haklai-footer-text">
                    <?php _e('Ao continuar, você concorda com os termos de uso do sistema Haklai.', 'haklai-app'); ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>

