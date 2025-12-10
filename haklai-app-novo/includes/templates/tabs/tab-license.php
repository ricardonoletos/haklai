<?php
/**
 * Aba 1: Licença e Sistema
 */

if (!defined('ABSPATH')) exit;
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Licença e Sistema', 'haklai-app'); ?></h2>
    
    <!-- Informações da Licença -->
    <div class="settings-card license-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-key"></i>
            </div>
            <div>
                <h3><?php _e('Haklai App - Licença Premium', 'haklai-app'); ?></h3>
                <span class="license-badge <?php echo $license_status === 'active' ? 'active' : 'inactive'; ?>">
                    <?php echo $license_status === 'active' ? __('✅ Ativa', 'haklai-app') : __('❌ Inativa', 'haklai-app'); ?>
                </span>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="form-grid">
                <div class="form-group">
                    <label><?php _e('Chave da Licença', 'haklai-app'); ?></label>
                    <input type="text" 
                           id="haklai-license-key" 
                           class="form-control-custom" 
                           value="<?php echo esc_attr($license_key); ?>" 
                           placeholder="XXXX-XXXX-XXXX-XXXX">
                    <small class="form-text"><?php _e('Insira sua chave de licença Premium', 'haklai-app'); ?></small>
                </div>
                
                <div class="form-group">
                    <label><?php _e('Data de Expiração', 'haklai-app'); ?></label>
                    <input type="date" 
                           id="haklai-license-expires" 
                           class="form-control-custom" 
                           value="<?php echo esc_attr($license_expires); ?>">
                    <small class="form-text"><?php _e('Quando a licença expira', 'haklai-app'); ?></small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" disabled>
                    <i class="fas fa-sync"></i>
                    <?php _e('Validar Licença', 'haklai-app'); ?>
                </button>
                <button type="button" class="btn btn-secondary" disabled>
                    <i class="fas fa-redo"></i>
                    <?php _e('Renovar Licença', 'haklai-app'); ?>
                </button>
            </div>
            
            <p class="info-note">
                <i class="fas fa-info-circle"></i>
                <?php _e('Sistema de validação de licença será implementado na fase final do projeto.', 'haklai-app'); ?>
            </p>
        </div>
    </div>
    
    <!-- Status do Sistema -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-server"></i>
            </div>
            <div>
                <h3><?php _e('Status do Sistema', 'haklai-app'); ?></h3>
                <p><?php _e('Informações técnicas e de performance', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="system-grid">
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Versão Plugin', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(HAKLAI_VERSION); ?></div>
                </div>
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Versão PHP', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(phpversion()); ?></div>
                </div>
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Versão WordPress', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(get_bloginfo('version')); ?></div>
                </div>
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Total de Membros', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(wp_count_posts('haklai_member')->publish); ?></div>
                </div>
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Total de Células', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(wp_count_posts('haklai_cell')->publish); ?></div>
                </div>
                <div class="system-stat">
                    <div class="stat-label"><?php _e('Memória PHP', 'haklai-app'); ?></div>
                    <div class="stat-value"><?php echo esc_html(ini_get('memory_limit')); ?></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Botão Salvar -->
    <div class="tab-footer">
        <button type="button" class="btn btn-primary btn-save-tab" data-tab="license">
            <i class="fas fa-save"></i>
            <?php _e('Salvar Configurações', 'haklai-app'); ?>
        </button>
    </div>
</div>

