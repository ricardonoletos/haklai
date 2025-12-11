<?php
/**
 * Aba 3: Configurações de Email
 */

if (!defined('ABSPATH')) exit;

$templates_list = $email_templates_manager->get_templates_list();
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Configurações de Email', 'haklai-app'); ?></h2>
    
    <?php foreach ($templates_list as $key => $template_info): 
        $config = $email_templates_manager->get_template_config($key);
    ?>
        <div class="settings-card email-template-card">
            <div class="card-header-custom">
                <div class="card-icon-large">
                    <i class="fas fa-envelope"></i>
                </div>
                <div>
                    <h3><?php echo esc_html($template_info['name']); ?></h3>
                    <p><?php echo esc_html($template_info['description']); ?></p>
                </div>
            </div>
            
            <div class="card-body-custom">
                <div class="form-group">
                    <label><?php _e('Assunto do Email', 'haklai-app'); ?></label>
                    <input type="text" 
                           class="form-control-custom email-subject" 
                           data-template="<?php echo esc_attr($key); ?>"
                           value="<?php echo esc_attr($config['subject']); ?>" 
                           placeholder="<?php _e('Digite o assunto...', 'haklai-app'); ?>">
                </div>
                
                <div class="form-group">
                    <label><?php _e('Conteúdo do Email (HTML)', 'haklai-app'); ?></label>
                    <textarea class="form-control-custom email-body" 
                              data-template="<?php echo esc_attr($key); ?>"
                              rows="10" 
                              placeholder="<?php _e('Digite o conteúdo em HTML...', 'haklai-app'); ?>"><?php echo esc_textarea($config['body']); ?></textarea>
                    
                    <div class="variables-info">
                        <strong><?php _e('Variáveis disponíveis:', 'haklai-app'); ?></strong>
                        <?php foreach ($template_info['variables'] as $var): ?>
                            <code><?php echo esc_html($var); ?></code>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-primary save-email-template" data-template="<?php echo esc_attr($key); ?>">
                        <i class="fas fa-save"></i>
                        <?php _e('Salvar Template', 'haklai-app'); ?>
                    </button>
                    <button type="button" class="btn btn-secondary test-email-template" data-template="<?php echo esc_attr($key); ?>">
                        <i class="fas fa-paper-plane"></i>
                        <?php _e('Enviar Teste', 'haklai-app'); ?>
                    </button>
                    <button type="button" class="btn btn-link restore-email-default" data-template="<?php echo esc_attr($key); ?>">
                        <i class="fas fa-undo"></i>
                        <?php _e('Restaurar Padrão', 'haklai-app'); ?>
                    </button>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

