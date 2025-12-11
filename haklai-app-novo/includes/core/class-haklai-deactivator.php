<?php
/**
 * Classe responsável pela desativação do plugin
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

/**
 * Classe Haklai_Deactivator
 */
class Haklai_Deactivator {
    
    /**
     * Executa a desativação do plugin
     */
    public static function deactivate() {
        // Remove tarefas cron agendadas
        self::unschedule_cron_jobs();
        
        // Limpa cache
        self::clear_cache();
        
        // Log da desativação
        self::log_deactivation();
    }
    
    /**
     * Remove tarefas cron agendadas
     */
    private static function unschedule_cron_jobs() {
        // Remove backup diário
        $timestamp = wp_next_scheduled('haklai_daily_backup');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'haklai_daily_backup');
        }
        
        // Remove limpeza de logs
        $timestamp = wp_next_scheduled('haklai_cleanup_logs');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'haklai_cleanup_logs');
        }
        
        // Remove limpeza de notificações
        $timestamp = wp_next_scheduled('haklai_cleanup_notifications');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'haklai_cleanup_notifications');
        }
    }
    
    /**
     * Limpa cache
     */
    private static function clear_cache() {
        // Limpa cache de objetos WordPress
        wp_cache_flush();
        
        // Limpa cache de transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_haklai_%'");
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_haklai_%'");
    }
    
    /**
     * Registra log da desativação
     */
    private static function log_deactivation() {
        $log_data = array(
            'action' => 'plugin_deactivation',
            'version' => HAKLAI_VERSION,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        );
        
        // Salva no log do WordPress
        error_log('Haklai App: Plugin deactivated - ' . json_encode($log_data));
        
        // Salva log de desativação
        $log_file = HAKLAI_UPLOAD_DIR . 'deactivation.log';
        if (file_exists(HAKLAI_UPLOAD_DIR)) {
            file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
        }
    }
}

