<?php
/**
 * Aba 5: Backup e Segurança
 */

if (!defined('ABSPATH')) exit;
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Backup e Segurança', 'haklai-app'); ?></h2>
    
    <!-- Backup Automático -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large backup-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <div>
                <h3><?php _e('Backup Automático', 'haklai-app'); ?></h3>
                <p><?php _e('Sistema de backup programado para proteção de dados', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="backup-status">
                <p><strong><?php _e('Último backup:', 'haklai-app'); ?></strong> <?php echo esc_html(date_i18n('d/m/Y \à\s H:i')); ?></p>
            </div>
            
            <div class="form-actions">
                <button class="btn btn-warning" disabled>
                    <i class="fas fa-download"></i>
                    <?php _e('Fazer Backup Agora', 'haklai-app'); ?>
                </button>
                <button class="btn btn-secondary" disabled>
                    <i class="fas fa-upload"></i>
                    <?php _e('Restaurar Backup', 'haklai-app'); ?>
                </button>
            </div>
            
            <p class="info-note">
                <i class="fas fa-info-circle"></i>
                <?php _e('Funcionalidade de backup automático em desenvolvimento. Use a exportação de membros para backup manual.', 'haklai-app'); ?>
            </p>
        </div>
    </div>
    
    <!-- Logs de Atividade -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <div>
                <h3><?php _e('Logs de Atividade', 'haklai-app'); ?></h3>
                <p><?php _e('Registro de ações importantes do sistema', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <p class="info-note">
                <i class="fas fa-info-circle"></i>
                <?php _e('Sistema de logs em desenvolvimento.', 'haklai-app'); ?>
            </p>
        </div>
    </div>
</div>

