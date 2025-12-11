<?php
/**
 * Aba 6: Configurações Gerais
 */

if (!defined('ABSPATH')) exit;
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Configurações Gerais', 'haklai-app'); ?></h2>
    
    <!-- Informações da Igreja -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-church"></i>
            </div>
            <div>
                <h3><?php _e('Informações da Igreja', 'haklai-app'); ?></h3>
                <p><?php _e('Configure dados básicos da sua igreja', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="form-group">
                <label><?php _e('Nome da Igreja', 'haklai-app'); ?></label>
                <input type="text" 
                       id="haklai-church-name" 
                       class="form-control-custom" 
                       value="<?php echo esc_attr($church_name); ?>" 
                       placeholder="<?php _e('Digite o nome da igreja...', 'haklai-app'); ?>">
            </div>
            
            <div class="form-group">
                <label><?php _e('Logo da Igreja', 'haklai-app'); ?></label>
                <div class="logo-upload">
                    <?php if ($church_logo): ?>
                        <div class="logo-preview">
                            <img src="<?php echo esc_url($church_logo); ?>" alt="Logo" id="haklai-logo-preview">
                        </div>
                    <?php endif; ?>
                    <input type="hidden" id="haklai-church-logo" value="<?php echo esc_attr($church_logo); ?>">
                    <button type="button" class="btn btn-secondary" id="haklai-upload-logo">
                        <i class="fas fa-image"></i>
                        <?php _e('Selecionar Logo', 'haklai-app'); ?>
                    </button>
                    <?php if ($church_logo): ?>
                        <button type="button" class="btn btn-link" id="haklai-remove-logo">
                            <i class="fas fa-times"></i>
                            <?php _e('Remover', 'haklai-app'); ?>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Modo de Tema -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-moon"></i>
            </div>
            <div>
                <h3><?php _e('Modo de Tema', 'haklai-app'); ?></h3>
                <p><?php _e('Escolha entre modo claro ou escuro para a interface', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="form-group">
                <label><?php _e('Tema da Interface', 'haklai-app'); ?></label>
                <select id="haklai-theme-mode" class="form-control-custom">
                    <option value="light" <?php selected($theme_mode, 'light'); ?>><?php _e('Modo Claro', 'haklai-app'); ?></option>
                    <option value="dark" <?php selected($theme_mode, 'dark'); ?>><?php _e('Modo Escuro', 'haklai-app'); ?></option>
                </select>
                <small class="form-text text-muted"><?php _e('O modo escuro aplica um fundo roxo escuro na página inicial e outras páginas do sistema.', 'haklai-app'); ?></small>
            </div>
        </div>
    </div>
    
    <!-- Personalização de Cores -->
    <div class="settings-card">
        <div class="card-header-custom">
            <div class="card-icon-large">
                <i class="fas fa-palette"></i>
            </div>
            <div>
                <h3><?php _e('Personalização de Cores', 'haklai-app'); ?></h3>
                <p><?php _e('Customize as cores da interface do sistema', 'haklai-app'); ?></p>
            </div>
        </div>
        
        <div class="card-body-custom">
            <div class="color-pickers-grid">
                <div class="color-picker-item">
                    <label><?php _e('Cor Primária', 'haklai-app'); ?></label>
                    <div class="color-input-group">
                        <input type="color" 
                               id="haklai-primary-color" 
                               class="color-picker" 
                               value="<?php echo esc_attr($primary_color); ?>">
                        <input type="text" 
                               class="color-hex" 
                               value="<?php echo esc_attr($primary_color); ?>" 
                               readonly>
                    </div>
                </div>
                
                <div class="color-picker-item">
                    <label><?php _e('Cor Secundária', 'haklai-app'); ?></label>
                    <div class="color-input-group">
                        <input type="color" 
                               id="haklai-secondary-color" 
                               class="color-picker" 
                               value="<?php echo esc_attr($secondary_color); ?>">
                        <input type="text" 
                               class="color-hex" 
                               value="<?php echo esc_attr($secondary_color); ?>" 
                               readonly>
                    </div>
                </div>
                
                <div class="color-picker-item">
                    <label><?php _e('Cor de Fundo', 'haklai-app'); ?></label>
                    <div class="color-input-group">
                        <input type="color" 
                               id="haklai-background-color" 
                               class="color-picker" 
                               value="<?php echo esc_attr($background_color); ?>">
                        <input type="text" 
                               class="color-hex" 
                               value="<?php echo esc_attr($background_color); ?>" 
                               readonly>
                    </div>
                </div>
            </div>
            
            <!-- Preview em Tempo Real -->
            <div class="color-preview-container">
                <h4><?php _e('👁️ Preview em Tempo Real', 'haklai-app'); ?></h4>
                
                <div class="preview-elements">
                    <div class="preview-buttons">
                        <button class="preview-btn-primary"><?php _e('Botão Primário', 'haklai-app'); ?></button>
                        <button class="preview-btn-secondary"><?php _e('Botão Secundário', 'haklai-app'); ?></button>
                    </div>
                    
                    <div class="preview-cards">
                        <div class="preview-card dashboard-card">
                            <div class="preview-card-header">
                                <?php _e('📊 Dashboard', 'haklai-app'); ?>
                            </div>
                            <div class="preview-card-body">
                                <div class="preview-stat">
                                    <span class="stat-num">45</span>
                                    <span class="stat-label"><?php _e('Presentes', 'haklai-app'); ?></span>
                                </div>
                                <div class="preview-stat">
                                    <span class="stat-num">5</span>
                                    <span class="stat-label"><?php _e('Ausentes', 'haklai-app'); ?></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="preview-card checkpoint-card">
                            <div class="preview-card-header">
                                <?php _e('✅ Checkpoint', 'haklai-app'); ?>
                            </div>
                            <div class="preview-card-body">
                                <div class="preview-member">
                                    <span class="member-name"><?php _e('João Silva', 'haklai-app'); ?></span>
                                    <span class="member-badge"><?php _e('Presente', 'haklai-app'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <button type="button" class="btn btn-link" id="haklai-restore-default-colors">
                    <i class="fas fa-undo"></i>
                    <?php _e('Restaurar Cores Padrão', 'haklai-app'); ?>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Botão Salvar -->
    <div class="tab-footer">
        <button type="button" class="btn btn-primary btn-save-tab" data-tab="general">
            <i class="fas fa-save"></i>
            <?php _e('Salvar Configurações Gerais', 'haklai-app'); ?>
        </button>
    </div>
</div>

