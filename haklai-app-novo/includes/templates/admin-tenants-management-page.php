<?php
/**
 * Template da página de Gerenciamento de Tenants
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

// SEGURANÇA: Verifica se é Super Admin
if (!current_user_can('manage_options')) {
    wp_die(__('Acesso negado. Apenas administradores podem gerenciar tenants.', 'haklai-app'));
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php _e('Gerenciar Tenants', 'haklai-app'); ?> - Haklai</title>
    <?php wp_head(); ?>
</head>
<body>
    <div class="wrap haklai-tenants-wrap">
        <h1><?php _e('Gerenciar Tenants', 'haklai-app'); ?></h1>
        
        <div class="haklai-tenants-container">
            <!-- Seção: Criar Novo Tenant -->
            <div class="haklai-section haklai-create-tenant-section">
                <h2><?php _e('Criar Novo Tenant', 'haklai-app'); ?></h2>
                <form id="haklai-create-tenant-form" class="haklai-form">
                    <div class="haklai-form-row">
                        <div class="haklai-form-group">
                            <label for="create_church_name"><?php _e('Nome da Igreja', 'haklai-app'); ?> <span class="required">*</span></label>
                            <input type="text" id="create_church_name" name="church_name" required>
                        </div>
                        <div class="haklai-form-group">
                            <label for="create_location"><?php _e('Localização', 'haklai-app'); ?> <span class="required">*</span></label>
                            <input type="text" id="create_location" name="location" required>
                        </div>
                    </div>
                    <div class="haklai-form-row">
                        <div class="haklai-form-group">
                            <label for="create_contact"><?php _e('Contato', 'haklai-app'); ?> <span class="required">*</span></label>
                            <input type="text" id="create_contact" name="contact" required>
                        </div>
                        <div class="haklai-form-group">
                            <label for="create_supervisor_user_id"><?php _e('Supervisor (Opcional)', 'haklai-app'); ?></label>
                            <select id="create_supervisor_user_id" name="supervisor_user_id">
                                <option value="0"><?php _e('-- Selecionar Usuário --', 'haklai-app'); ?></option>
                                <?php foreach ($users_without_tenant as $user): ?>
                                    <option value="<?php echo esc_attr($user->ID); ?>">
                                        <?php echo esc_html($user->display_name ? $user->display_name : $user->user_login); ?> 
                                        (<?php echo esc_html($user->user_email); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="haklai-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Criar Tenant', 'haklai-app'); ?>
                        </button>
                    </div>
                </form>
                <div id="haklai-create-message" class="haklai-message" style="display: none;"></div>
            </div>

            <!-- Seção: Lista de Tenants -->
            <div class="haklai-section haklai-tenants-list-section">
                <h2><?php _e('Tenants Existentes', 'haklai-app'); ?></h2>
                <?php if (empty($tenants)): ?>
                    <p><?php _e('Nenhum tenant cadastrado ainda.', 'haklai-app'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'haklai-app'); ?></th>
                                <th><?php _e('Nome', 'haklai-app'); ?></th>
                                <th><?php _e('Código', 'haklai-app'); ?></th>
                                <th><?php _e('Localização', 'haklai-app'); ?></th>
                                <th><?php _e('Supervisor', 'haklai-app'); ?></th>
                                <th><?php _e('Status', 'haklai-app'); ?></th>
                                <th><?php _e('Ações', 'haklai-app'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tenants as $tenant): 
                                $supervisor = null;
                                if ($tenant->supervisor_pastor_id) {
                                    $supervisor = get_userdata($tenant->supervisor_pastor_id);
                                }
                            ?>
                                <tr>
                                    <td><?php echo esc_html($tenant->id); ?></td>
                                    <td><strong><?php echo esc_html($tenant->name); ?></strong></td>
                                    <td><code><?php echo esc_html($tenant->code); ?></code></td>
                                    <td><?php echo esc_html($tenant->location); ?></td>
                                    <td>
                                        <?php if ($supervisor): ?>
                                            <?php echo esc_html($supervisor->display_name ? $supervisor->display_name : $supervisor->user_login); ?>
                                            <br><small><?php echo esc_html($supervisor->user_email); ?></small>
                                        <?php else: ?>
                                            <em><?php _e('Não definido', 'haklai-app'); ?></em>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($tenant->is_active): ?>
                                            <span class="haklai-status-active"><?php _e('Ativo', 'haklai-app'); ?></span>
                                        <?php else: ?>
                                            <span class="haklai-status-inactive"><?php _e('Inativo', 'haklai-app'); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <button type="button" class="button button-small haklai-associate-btn" 
                                                data-tenant-id="<?php echo esc_attr($tenant->id); ?>"
                                                data-tenant-name="<?php echo esc_attr($tenant->name); ?>">
                                            <?php _e('Associar Usuário', 'haklai-app'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>

            <!-- Seção: Usuários Sem Tenant -->
            <div class="haklai-section haklai-users-section">
                <h2><?php _e('Usuários Sem Tenant', 'haklai-app'); ?></h2>
                <?php if (empty($users_without_tenant)): ?>
                    <p><?php _e('Todos os usuários já estão associados a um tenant.', 'haklai-app'); ?></p>
                <?php else: ?>
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('ID', 'haklai-app'); ?></th>
                                <th><?php _e('Nome', 'haklai-app'); ?></th>
                                <th><?php _e('E-mail', 'haklai-app'); ?></th>
                                <th><?php _e('Role', 'haklai-app'); ?></th>
                                <th><?php _e('Ações', 'haklai-app'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users_without_tenant as $user): ?>
                                <tr>
                                    <td><?php echo esc_html($user->ID); ?></td>
                                    <td><strong><?php echo esc_html($user->display_name ? $user->display_name : $user->user_login); ?></strong></td>
                                    <td><?php echo esc_html($user->user_email); ?></td>
                                    <td><?php echo esc_html(implode(', ', $user->roles)); ?></td>
                                    <td>
                                        <select class="haklai-associate-select" data-user-id="<?php echo esc_attr($user->ID); ?>">
                                            <option value="0"><?php _e('-- Selecionar Tenant --', 'haklai-app'); ?></option>
                                            <?php foreach ($tenants as $tenant): ?>
                                                <option value="<?php echo esc_attr($tenant->id); ?>">
                                                    <?php echo esc_html($tenant->name); ?> (<?php echo esc_html($tenant->code); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="button" class="button button-small haklai-associate-user-btn" 
                                                data-user-id="<?php echo esc_attr($user->ID); ?>"
                                                data-user-name="<?php echo esc_attr($user->display_name ? $user->display_name : $user->user_login); ?>"
                                                style="display: none;">
                                            <?php _e('Associar', 'haklai-app'); ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal: Associar Usuário -->
        <div id="haklai-associate-modal" class="haklai-modal" style="display: none;">
            <div class="haklai-modal-content">
                <span class="haklai-modal-close">&times;</span>
                <h2><?php _e('Associar Usuário ao Tenant', 'haklai-app'); ?></h2>
                <p id="haklai-modal-message"></p>
                <form id="haklai-associate-form">
                    <input type="hidden" id="associate_user_id" name="user_id">
                    <input type="hidden" id="associate_tenant_id" name="tenant_id">
                    <div class="haklai-form-actions">
                        <button type="submit" class="button button-primary">
                            <?php _e('Confirmar Associação', 'haklai-app'); ?>
                        </button>
                        <button type="button" class="button haklai-modal-cancel">
                            <?php _e('Cancelar', 'haklai-app'); ?>
                        </button>
                    </div>
                </form>
                <div id="haklai-associate-message" class="haklai-message" style="display: none;"></div>
            </div>
        </div>
    </div>
    <?php wp_footer(); ?>
</body>
</html>

