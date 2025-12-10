<?php
/**
 * Arquivo de desinstalação do Haklai App
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

// Se não foi chamado pelo WordPress, saia
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Verifica se o usuário tem permissão para desinstalar plugins
if (!current_user_can('activate_plugins')) {
    return;
}

// Verifica se a desinstalação foi solicitada
if (!defined('WP_UNINSTALL_PLUGIN') || WP_UNINSTALL_PLUGIN !== plugin_basename(__FILE__)) {
    return;
}

/**
 * Remove todos os dados do plugin
 */
class Haklai_Uninstaller {
    
    /**
     * Executa a desinstalação
     */
    public static function uninstall() {
        // Remove dados do banco
        self::remove_database_data();
        
        // Remove opções
        self::remove_options();
        
        // Remove arquivos de upload
        self::remove_upload_files();
        
        // Remove roles e capabilities
        self::remove_roles_and_capabilities();
        
        // Remove cron jobs
        self::remove_cron_jobs();
        
        // Remove transients
        self::remove_transients();
        
        // Log da desinstalação
        self::log_uninstall();
    }
    
    /**
     * Remove dados do banco (Custom Post Types)
     */
    private static function remove_database_data() {
        global $wpdb;
        
        // Remove posts dos Custom Post Types
        $post_types = array(
            'haklai_member',
            'haklai_cell',
            'haklai_meeting',
            'haklai_attendance',
            'haklai_report'
        );
        
        foreach ($post_types as $post_type) {
            // Remove posts
            $posts = get_posts(array(
                'post_type' => $post_type,
                'post_status' => 'any',
                'posts_per_page' => -1,
                'fields' => 'ids'
            ));
            
            foreach ($posts as $post_id) {
                wp_delete_post($post_id, true);
            }
            
            // Remove meta dados órfãos
            $wpdb->query($wpdb->prepare(
                "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})"
            ));
        }
    }
    
    /**
     * Remove opções do plugin
     */
    private static function remove_options() {
        $options = array(
            'haklai_version',
            'haklai_db_version',
            'haklai_settings',
            'haklai_permissions',
            'haklai_ui',
            'haklai_colors',
            'haklai_capabilities_created',
            'haklai_license_key',
            'haklai_license_status',
            'haklai_license_expires',
        );
        
        foreach ($options as $option) {
            delete_option($option);
        }
        
        // Remove opções de rede (multisite)
        if (is_multisite()) {
            foreach ($options as $option) {
                delete_site_option($option);
            }
        }
    }
    
    /**
     * Remove arquivos de upload
     */
    private static function remove_upload_files() {
        $upload_dir = wp_upload_dir()['basedir'] . '/haklai-app/';
        
        if (file_exists($upload_dir)) {
            self::delete_directory($upload_dir);
        }
    }
    
    /**
     * Remove roles e capabilities personalizadas
     */
    private static function remove_roles_and_capabilities() {
        // Remove roles personalizados
        $custom_roles = array(
            'haklai_pastor_supervisor',
            'haklai_pastor_senior',
            'haklai_pastor_rede',
            'haklai_discipulador',
            'haklai_lider_celula',
        );
        
        foreach ($custom_roles as $role) {
            remove_role($role);
        }
        
        // Remove capabilities dos roles padrão
        $capabilities = array(
            'haklai_access_dashboard',
            'haklai_access_settings',
            'haklai_access_reports',
            'haklai_access_checkpoint',
            'haklai_manage_members',
            'haklai_manage_cells',
            'haklai_manage_users',
            'haklai_view_all_data',
        );
        
        $roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                foreach ($capabilities as $cap) {
                    $role->remove_cap($cap);
                }
            }
        }
    }
    
    /**
     * Remove cron jobs
     */
    private static function remove_cron_jobs() {
        $cron_jobs = array(
            'haklai_daily_backup',
            'haklai_cleanup_logs',
            'haklai_cleanup_notifications',
        );
        
        foreach ($cron_jobs as $cron_job) {
            $timestamp = wp_next_scheduled($cron_job);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $cron_job);
            }
            
            // Remove todas as ocorrências
            wp_clear_scheduled_hook($cron_job);
        }
    }
    
    /**
     * Remove transients
     */
    private static function remove_transients() {
        global $wpdb;
        
        // Remove transients do plugin
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_haklai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_haklai_%'");
        
        // Remove transients de rede (multisite)
        if (is_multisite()) {
            $wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_haklai_%'");
            $wpdb->query("DELETE FROM {$wpdb->sitemeta} WHERE meta_key LIKE '_site_transient_timeout_haklai_%'");
        }
    }
    
    /**
     * Remove diretório recursivamente
     */
    private static function delete_directory($dir) {
        if (!is_dir($dir)) {
            return false;
        }
        
        $files = array_diff(scandir($dir), array('.', '..'));
        
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            
            if (is_dir($path)) {
                self::delete_directory($path);
            } else {
                unlink($path);
            }
        }
        
        return rmdir($dir);
    }
    
    /**
     * Registra log da desinstalação
     */
    private static function log_uninstall() {
        $log_data = array(
            'action' => 'plugin_uninstall',
            'version' => defined('HAKLAI_VERSION') ? HAKLAI_VERSION : 'unknown',
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        );
        
        // Salva no log do WordPress
        error_log('Haklai App: Plugin uninstalled - ' . json_encode($log_data));
        
        // Salva em arquivo de log (se o diretório ainda existir)
        $log_file = wp_upload_dir()['basedir'] . '/haklai-app/uninstall.log';
        $log_dir = dirname($log_file);
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }
        
        if (file_exists($log_dir)) {
            file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}

// Executa a desinstalação
Haklai_Uninstaller::uninstall();

