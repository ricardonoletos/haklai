<?php
/**
 * Template da Página de Configurações do Haklai - 6 Abas
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

// Carrega dados necessários
$email_templates_manager = new Haklai_Email_Templates();
$capabilities_manager = new Haklai_Capabilities_Manager();
$import_history = Haklai_Import_History::get_history();

// Obtém células para filtros
$cells = get_posts([
    'post_type' => 'haklai_cell',
    'post_status' => 'publish',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC'
]);

// Obtém configurações atuais
$church_name = get_option('haklai_church_name', get_bloginfo('name'));
$church_logo = get_option('haklai_church_logo', '');
$primary_color = get_option('haklai_primary_color', '#667eea');
$secondary_color = get_option('haklai_secondary_color', '#764ba2');
$background_color = get_option('haklai_background_color', '#f7fafc');
$theme_mode = get_option('haklai_theme_mode', 'light');
$license_key = get_option('haklai_license_key', '');
$license_expires = get_option('haklai_license_expires', '');
$license_status = get_option('haklai_license_status', 'inactive');

$current_user = wp_get_current_user();
?>

<div class="wrap haklai-settings-wrap">
    <div class="config-container">
        
        <!-- Header -->
        <div class="config-header">
            <div class="header-content">
                <h1><?php _e('Configurações do Sistema Haklai', 'haklai-app'); ?></h1>
                <div class="subtitle"><?php _e('Gerencie todas as configurações do sistema', 'haklai-app'); ?></div>
            </div>
            
            <div class="user-info">
                <img src="<?php echo esc_url(get_avatar_url($current_user->ID)); ?>" alt="<?php echo esc_attr($current_user->display_name); ?>" class="user-avatar">
                <div>
                    <div style="font-weight: 600;"><?php echo esc_html($current_user->display_name); ?></div>
                    <div style="font-size: 0.8rem; opacity: 0.8;"><?php _e('Administrador Sistema', 'haklai-app'); ?></div>
                </div>
            </div>
        </div>
        
        <!-- Navegação de Abas -->
        <div class="tabs-navigation">
            <button class="tab-button active" data-tab="license">
                <i class="fas fa-key"></i>
                <span><?php _e('Licença', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="import">
                <i class="fas fa-file-import"></i>
                <span><?php _e('Importar', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="email">
                <i class="fas fa-envelope"></i>
                <span><?php _e('Email', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="permissions">
                <i class="fas fa-shield-alt"></i>
                <span><?php _e('Permissões', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="backup">
                <i class="fas fa-database"></i>
                <span><?php _e('Backup', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="sync">
                <i class="fas fa-sync-alt"></i>
                <span><?php _e('Sincronização', 'haklai-app'); ?></span>
            </button>
            <button class="tab-button" data-tab="general">
                <i class="fas fa-cog"></i>
                <span><?php _e('Gerais', 'haklai-app'); ?></span>
            </button>
        </div>
        
        <!-- Conteúdo das Abas -->
        <div class="tabs-content">
            
            <!-- ABA 1: LICENÇA E SISTEMA -->
            <div id="tab-license" class="tab-content active">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-license.php'; ?>
            </div>
            
            <!-- ABA 2: IMPORTAR/EXPORTAR -->
            <div id="tab-import" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-import.php'; ?>
            </div>
            
            <!-- ABA 3: EMAIL -->
            <div id="tab-email" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-email.php'; ?>
            </div>
            
            <!-- ABA 4: PERMISSÕES -->
            <div id="tab-permissions" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-permissions.php'; ?>
            </div>
            
            <!-- ABA 5: BACKUP -->
            <div id="tab-backup" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-backup.php'; ?>
            </div>
            
            <!-- ABA 6: SINCRONIZAÇÃO -->
            <div id="tab-sync" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-sync.php'; ?>
            </div>
            
            <!-- ABA 7: GERAIS -->
            <div id="tab-general" class="tab-content">
                <?php include HAKLAI_PLUGIN_PATH . 'includes/templates/tabs/tab-general.php'; ?>
            </div>
            
        </div>
    </div>
</div>

<!-- Modal de Log de Importação -->
<div id="haklai-import-log-modal" class="haklai-modal" style="display:none;">
    <div class="haklai-modal-overlay"></div>
    <div class="haklai-modal-content">
        <div class="haklai-modal-header">
            <h3><?php _e('Log de Importação', 'haklai-app'); ?></h3>
            <button class="haklai-modal-close">&times;</button>
        </div>
        <div class="haklai-modal-body" id="haklai-modal-log-content">
            <?php _e('Carregando...', 'haklai-app'); ?>
        </div>
    </div>
</div>
