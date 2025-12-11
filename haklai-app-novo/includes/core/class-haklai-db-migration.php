<?php
/**
 * Database Migration: Multi-Tenancy
 * Adiciona tenant_id nas tabelas existentes e migra dados
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
 * Classe Haklai_DB_Migration
 * Gerencia migrações do banco de dados
 */
class Haklai_DB_Migration {
    
    /**
     * Versão da migração atual
     */
    const MIGRATION_VERSION = '2.1.0'; // Multi-tenancy
    
    /**
     * Executa migração para multi-tenancy
     * 
     * @return bool True se migração foi bem-sucedida
     */
    public static function migrate_to_multi_tenancy() {
        global $wpdb;
        
        // Verifica se já foi migrado
        $migration_key = 'haklai_migration_' . self::MIGRATION_VERSION;
        if (get_option($migration_key)) {
            error_log('Haklai Migration: Multi-tenancy já foi migrado');
            return true;
        }
        
        error_log('Haklai Migration: Iniciando migração para multi-tenancy...');
        
        try {
            // 1. Cria tenant padrão se não existir
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $default_tenant_id = $tenant_manager->create_default_tenant();
            
            if (!$default_tenant_id) {
                error_log('Haklai Migration: Erro ao criar tenant padrão');
                return false;
            }
            
            error_log("Haklai Migration: Tenant padrão criado com ID: {$default_tenant_id}");
            
            // 2. Adiciona tenant_id nas tabelas existentes
            $tables_to_migrate = array('members', 'cells', 'networks', 'meetings');
            
            foreach ($tables_to_migrate as $table_name) {
                if (!self::add_tenant_id_column($table_name)) {
                    error_log("Haklai Migration: Erro ao adicionar tenant_id na tabela {$table_name}");
                    return false;
                }
                
                // Associa dados existentes ao tenant padrão
                if (!self::associate_existing_data_to_tenant($table_name, $default_tenant_id)) {
                    error_log("Haklai Migration: Erro ao associar dados ao tenant na tabela {$table_name}");
                    return false;
                }
                
                error_log("Haklai Migration: Tabela {$table_name} migrada com sucesso");
            }
            
            // 3. Marca migração como concluída
            update_option($migration_key, current_time('mysql'));
            
            error_log('Haklai Migration: Migração para multi-tenancy concluída com sucesso!');
            
            return true;
            
        } catch (Exception $e) {
            error_log('Haklai Migration: Erro na migração - ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Adiciona coluna tenant_id em uma tabela
     * 
     * @param string $table_name Nome da tabela
     * @return bool
     */
    private static function add_tenant_id_column($table_name) {
        global $wpdb;
        $full_table_name = Haklai_DB::get_table_name($table_name);
        
        // Verifica se coluna já existe
        $column_exists = $wpdb->get_results($wpdb->prepare(
            "SHOW COLUMNS FROM {$full_table_name} LIKE 'tenant_id'"
        ));
        
        if (!empty($column_exists)) {
            error_log("Haklai Migration: Coluna tenant_id já existe na tabela {$table_name}");
            return true;
        }
        
        // Adiciona coluna tenant_id
        $result = $wpdb->query(
            "ALTER TABLE {$full_table_name} 
             ADD COLUMN tenant_id BIGINT UNSIGNED NULL AFTER id,
             ADD INDEX idx_tenant_id (tenant_id)"
        );
        
        if ($result === false) {
            error_log("Haklai Migration: Erro SQL ao adicionar tenant_id - " . $wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Associa dados existentes ao tenant padrão
     * 
     * @param string $table_name Nome da tabela
     * @param int $tenant_id ID do tenant padrão
     * @return bool
     */
    private static function associate_existing_data_to_tenant($table_name, $tenant_id) {
        global $wpdb;
        $full_table_name = Haklai_DB::get_table_name($table_name);
        
        // Atualiza todos os registros sem tenant_id para o tenant padrão
        $result = $wpdb->query($wpdb->prepare(
            "UPDATE {$full_table_name} 
             SET tenant_id = %d 
             WHERE tenant_id IS NULL",
            $tenant_id
        ));
        
        if ($result === false) {
            error_log("Haklai Migration: Erro SQL ao associar dados - " . $wpdb->last_error);
            return false;
        }
        
        return true;
    }
    
    /**
     * Verifica se migração é necessária
     * 
     * @return bool
     */
    public static function migration_needed() {
        $migration_key = 'haklai_migration_' . self::MIGRATION_VERSION;
        return !get_option($migration_key);
    }
}

