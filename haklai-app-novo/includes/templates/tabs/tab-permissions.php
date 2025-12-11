<?php
/**
 * Aba 4: Permissões e Roles
 */

if (!defined('ABSPATH')) exit;

$roles = $capabilities_manager->get_all_roles();
$all_capabilities = $capabilities_manager->get_all_capabilities();
?>

<div class="tab-section">
    <h2 class="tab-section-title"><?php _e('Gerenciamento de Permissões', 'haklai-app'); ?></h2>
    
    <div class="permissions-grid">
        <?php foreach ($roles as $role_slug => $role_info): 
            $role_caps = $capabilities_manager->get_role_capabilities($role_slug);
        ?>
            <div class="settings-card permission-role-card">
                <div class="card-header-custom">
                    <div class="card-icon-large">
                        <i class="fas <?php echo esc_attr($role_info['icon']); ?>"></i>
                    </div>
                    <div>
                        <h3><?php echo esc_html($role_info['name']); ?></h3>
                        <span class="role-level">Nível <?php echo esc_html($role_info['level']); ?></span>
                    </div>
                </div>
                
                <div class="card-body-custom">
                    <?php foreach ($all_capabilities as $cap_key => $cap_label): ?>
                        <div class="capability-item">
                            <label class="checkbox-label">
                                <input type="checkbox" 
                                       class="capability-checkbox"
                                       data-role="<?php echo esc_attr($role_slug); ?>"
                                       data-capability="<?php echo esc_attr($cap_key); ?>"
                                       <?php checked(isset($role_caps[$cap_key]) && $role_caps[$cap_key]); ?>>
                                <span><?php echo esc_html($cap_label); ?></span>
                            </label>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-primary save-role-permissions" data-role="<?php echo esc_attr($role_slug); ?>">
                            <i class="fas fa-save"></i>
                            <?php _e('Salvar Permissões', 'haklai-app'); ?>
                        </button>
                        <button type="button" class="btn btn-link restore-role-default" data-role="<?php echo esc_attr($role_slug); ?>">
                            <i class="fas fa-undo"></i>
                            <?php _e('Restaurar Padrão', 'haklai-app'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

