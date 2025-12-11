<?php
/**
 * Gerenciador de Histórico de Importações
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

class Haklai_Import_History {
    
    /**
     * Adiciona registro ao histórico
     */
    public static function add_entry($import_data) {
        $history = self::get_history();
        
        $entry = [
            'id' => uniqid('import_'),
            'date' => current_time('mysql'),
            'timestamp' => current_time('timestamp'),
            'filename' => sanitize_text_field($import_data['filename'] ?? 'unknown'),
            'records' => intval($import_data['records'] ?? 0),
            'errors' => intval($import_data['errors'] ?? 0),
            'warnings' => intval($import_data['warnings'] ?? 0),
            'log' => $import_data['log'] ?? [],
            'user_id' => get_current_user_id()
        ];
        
        // Adiciona no início do array
        array_unshift($history, $entry);
        
        // Mantém apenas últimas 10
        $history = array_slice($history, 0, 10);
        
        update_option('haklai_import_history', $history);
        
        return $entry['id'];
    }
    
    /**
     * Obtém histórico completo
     */
    public static function get_history() {
        return get_option('haklai_import_history', []);
    }
    
    /**
     * Obtém entrada específica
     */
    public static function get_entry($entry_id) {
        $history = self::get_history();
        
        foreach ($history as $entry) {
            if ($entry['id'] === $entry_id) {
                return $entry;
            }
        }
        
        return null;
    }
    
    /**
     * Remove entrada
     */
    public static function delete_entry($entry_id) {
        $history = self::get_history();
        
        $history = array_filter($history, function($entry) use ($entry_id) {
            return $entry['id'] !== $entry_id;
        });
        
        update_option('haklai_import_history', array_values($history));
        
        return true;
    }
    
    /**
     * Limpa todo o histórico
     */
    public static function clear_history() {
        delete_option('haklai_import_history');
        return true;
    }
}

