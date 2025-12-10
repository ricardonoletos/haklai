<?php
/**
 * Classe responsável pela ativação do plugin
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
 * Classe Haklai_Activator
 */
class Haklai_Activator {
    
    /**
     * Executa a ativação do plugin
     */
    public static function activate() {
        // Verifica requisitos mínimos
        self::check_requirements();
        
        // Cria tabelas do banco de dados (PRIMEIRO!)
        self::create_database_tables();
        
        // Configura roles e capabilities
        self::setup_roles_and_capabilities();
        
        // Cria dados iniciais
        self::create_initial_data();
        
        // Configura opções padrão
        self::set_default_options();
        
        // Agenda tarefas cron
        self::schedule_cron_jobs();
        
        // Log da ativação
        self::log_activation();
    }
    
    /**
     * Verifica requisitos mínimos do sistema
     */
    private static function check_requirements() {
        global $wp_version;
        
        $requirements = array(
            'php' => '8.1',
            'wordpress' => '6.0',
            'mysql' => '5.7',
        );
        
        $errors = array();
        
        // Verifica versão do PHP
        if (version_compare(PHP_VERSION, $requirements['php'], '<')) {
            $errors[] = sprintf(
                __('PHP %s ou superior é necessário. Versão atual: %s', 'haklai-app'),
                $requirements['php'],
                PHP_VERSION
            );
        }
        
        // Verifica versão do WordPress
        if (version_compare($wp_version, $requirements['wordpress'], '<')) {
            $errors[] = sprintf(
                __('WordPress %s ou superior é necessário. Versão atual: %s', 'haklai-app'),
                $requirements['wordpress'],
                $wp_version
            );
        }
        
        // Se há erros, exibe e para a ativação
        if (!empty($errors)) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                '<h1>' . __('Erro na Ativação', 'haklai-app') . '</h1>' .
                '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>',
                __('Erro na Ativação', 'haklai-app'),
                array('back_link' => true)
            );
        }
    }
    
    /**
     * Configura roles e capabilities
     */
    private static function setup_roles_and_capabilities() {
        // Remove capabilities antigas se existirem
        self::remove_old_capabilities();
        
        // Cria roles personalizados
        self::create_custom_roles();
    }
    
    /**
     * Remove capabilities antigas
     */
    private static function remove_old_capabilities() {
        $roles = array('administrator', 'editor', 'author', 'contributor', 'subscriber');
        
        foreach ($roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                // Remove capabilities antigas se existirem
                $role->remove_cap('haklai_old_capability');
            }
        }
    }
    
    /**
     * Cria roles personalizados com 5 níveis hierárquicos
     */
    private static function create_custom_roles() {
        // Pastor Supervisor (Nível 5) - Acesso total
        add_role('haklai_pastor_supervisor', __('Pastor Supervisor', 'haklai-app'), array(
            'read' => true,
            'haklai_access_dashboard' => true,
            'haklai_access_settings' => true,
            'haklai_access_reports' => true,
            'haklai_access_checkpoint' => true,
            'haklai_manage_members' => true,
            'haklai_manage_cells' => true,
            'haklai_manage_users' => true,
            'haklai_view_all_data' => true,
        ));
        
        // Pastor Senior (Nível 4) - Múltiplas redes
        add_role('haklai_pastor_senior', __('Pastor Senior', 'haklai-app'), array(
            'read' => true,
            'haklai_access_dashboard' => true,
            'haklai_access_reports' => true,
            'haklai_manage_members' => true,
            'haklai_manage_cells' => true,
            'haklai_view_all_data' => true,
        ));
        
        // Pastor de Rede (Nível 3) - Rede específica
        add_role('haklai_pastor_rede', __('Pastor de Rede', 'haklai-app'), array(
            'read' => true,
            'haklai_access_dashboard' => true,
            'haklai_access_reports' => true,
            'haklai_manage_members' => true,
            'haklai_manage_cells' => true,
        ));
        
        // Discipulador (Nível 2) - Múltiplas células
        add_role('haklai_discipulador', __('Discipulador', 'haklai-app'), array(
            'read' => true,
            'haklai_access_dashboard' => true,
            'haklai_access_reports' => true,
            'haklai_manage_members' => true,
            'haklai_manage_cells' => true,
        ));
        
        // Líder de Célula (Nível 1) - Apenas sua célula + checkpoint
        add_role('haklai_lider_celula', __('Líder de Célula', 'haklai-app'), array(
            'read' => true,
            'haklai_access_dashboard' => true,
            'haklai_access_checkpoint' => true,
            'haklai_manage_members' => true,
        ));
    }
    
    /**
     * Cria dados iniciais
     */
    private static function create_initial_data() {
        // Verifica se já existem dados
        $existing_cells = get_posts(array(
            'post_type' => 'haklai_cell',
            'post_status' => 'publish',
            'posts_per_page' => 1
        ));
        
        if (!empty($existing_cells)) {
            return; // Já existem dados
        }
        
        // Cria célula padrão
        $default_cell = array(
            'post_type' => 'haklai_cell',
            'post_title' => 'Célula Alpha',
            'post_content' => 'Célula padrão criada automaticamente pelo sistema.',
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_address' => 'Rua das Flores, 123 - Lisboa',
                '_haklai_status' => 'active',
                '_haklai_created_at' => current_time('mysql'),
            )
        );
        
        $cell_id = wp_insert_post($default_cell);
        
        if (!$cell_id) {
            return;
        }
        
        // Cria membros de exemplo
        $default_members = array(
            array(
                'post_title' => 'João Silva',
                'post_type' => 'haklai_member',
                'post_status' => 'publish',
                'meta_input' => array(
                    '_haklai_email' => 'joao.silva@exemplo.com',
                    '_haklai_phone' => '(11) 99999-9999',
                    '_haklai_role_level' => 1,
                    '_haklai_cell_id' => $cell_id,
                    '_haklai_skill' => 'musica',
                    '_haklai_baptism_status' => 'member',
                    '_haklai_status' => 'active',
                    '_haklai_is_visitor' => '0',
                    '_haklai_created_at' => current_time('mysql'),
                )
            ),
            array(
                'post_title' => 'Maria Santos',
                'post_type' => 'haklai_member',
                'post_status' => 'publish',
                'meta_input' => array(
                    '_haklai_email' => 'maria.santos@exemplo.com',
                    '_haklai_phone' => '(11) 88888-8888',
                    '_haklai_role_level' => 0,
                    '_haklai_cell_id' => $cell_id,
                    '_haklai_skill' => 'foto',
                    '_haklai_baptism_status' => 'member',
                    '_haklai_status' => 'active',
                    '_haklai_is_visitor' => '0',
                    '_haklai_created_at' => current_time('mysql'),
                )
            ),
            array(
                'post_title' => 'Pedro Costa',
                'post_type' => 'haklai_member',
                'post_status' => 'publish',
                'meta_input' => array(
                    '_haklai_email' => 'pedro.costa@exemplo.com',
                    '_haklai_phone' => '(11) 77777-7777',
                    '_haklai_role_level' => 0,
                    '_haklai_cell_id' => $cell_id,
                    '_haklai_skill' => 'tecnologia',
                    '_haklai_baptism_status' => 'frequent_visitor',
                    '_haklai_status' => 'active',
                    '_haklai_is_visitor' => '1',
                    '_haklai_created_at' => current_time('mysql'),
                )
            ),
        );
        
        foreach ($default_members as $member) {
            wp_insert_post($member);
        }
        
        // Cria reunião de exemplo
        $default_meeting = array(
            'post_type' => 'haklai_meeting',
            'post_title' => 'Reunião - ' . date('d/m/Y'),
            'post_content' => 'Reunião de exemplo criada automaticamente.',
            'post_status' => 'publish',
            'meta_input' => array(
                '_haklai_cell_id' => $cell_id,
                '_haklai_meeting_date' => date('Y-m-d'),
                '_haklai_host' => 'João Silva',
                '_haklai_address' => 'Rua das Flores, 123 - Lisboa',
                '_haklai_created_by' => get_current_user_id(),
                '_haklai_created_at' => current_time('mysql'),
            )
        );
        
        wp_insert_post($default_meeting);
    }
    
    /**
     * Define opções padrão do plugin
     */
    private static function set_default_options() {
        $default_options = array(
            'haklai_version' => HAKLAI_VERSION,
            'haklai_db_version' => HAKLAI_DB_VERSION,
            'haklai_settings' => array(
                'enable_notifications' => true,
                'enable_backup' => true,
                'backup_frequency' => 'daily',
                'max_backup_files' => 30,
                'enable_logging' => true,
                'log_retention_days' => 90,
                'attendance_history_limit' => 6, // Últimas 6 reuniões
            ),
            'haklai_permissions' => array(
                'dashboard_access_level' => 1,
                'settings_access_level' => 5,
                'reports_access_level' => 2,
                'checkpoint_access_level' => 1,
            ),
            'haklai_ui' => array(
                'theme' => 'light',
                'primary_color' => '#667eea',
                'secondary_color' => '#10b981',
                'language' => 'pt_BR',
            ),
            'haklai_colors' => array(
                'church_primary' => '#667eea',
                'church_secondary' => '#10b981',
                'church_accent' => '#f59e0b',
            ),
        );
        
        foreach ($default_options as $option_name => $option_value) {
            if (get_option($option_name) === false) {
                update_option($option_name, $option_value);
            }
        }
    }
    
    /**
     * Agenda tarefas cron
     */
    private static function schedule_cron_jobs() {
        // Backup diário
        if (!wp_next_scheduled('haklai_daily_backup')) {
            wp_schedule_event(time(), 'daily', 'haklai_daily_backup');
        }
        
        // Limpeza de logs
        if (!wp_next_scheduled('haklai_cleanup_logs')) {
            wp_schedule_event(time(), 'weekly', 'haklai_cleanup_logs');
        }
        
        // Limpeza de notificações antigas
        if (!wp_next_scheduled('haklai_cleanup_notifications')) {
            wp_schedule_event(time(), 'daily', 'haklai_cleanup_notifications');
        }
    }
    
    /**
     * Registra log da ativação
     */
    private static function log_activation() {
        $log_data = array(
            'action' => 'plugin_activation',
            'version' => HAKLAI_VERSION,
            'timestamp' => current_time('mysql'),
            'user_id' => get_current_user_id(),
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        );
        
        // Salva no log do WordPress
        error_log('Haklai App: Plugin activated - ' . json_encode($log_data));
        
        // Cria diretório de uploads se não existir
        $upload_dir = HAKLAI_UPLOAD_DIR;
        if (!file_exists($upload_dir)) {
            wp_mkdir_p($upload_dir);
        }
        
        // Salva log de ativação
        $log_file = $upload_dir . 'activation.log';
        file_put_contents($log_file, json_encode($log_data, JSON_PRETTY_PRINT) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Cria tabelas do banco de dados
     * FASE 4: Corrigido para sempre criar tabela tenants e executar migração
     */
    private static function create_database_tables() {
        global $wpdb;
        
        // FASE 4: Verifica especificamente se a tabela tenants existe
        $tenants_table = Haklai_DB::get_table_name('tenants');
        $tenants_exists = $wpdb->get_var("SHOW TABLES LIKE '{$tenants_table}'") === $tenants_table;
        
        // Verifica se outras tabelas existem
        $other_tables_exist = false;
        $tables_to_check = ['members', 'cells', 'networks', 'meetings'];
        foreach ($tables_to_check as $table) {
            $table_name = Haklai_DB::get_table_name($table);
            if ($wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name) {
                $other_tables_exist = true;
                break;
            }
        }
        
        // Se tenants não existe OU outras tabelas não existem, cria todas as tabelas
        if (!$tenants_exists || !$other_tables_exist) {
            error_log("Haklai DB: Criando/atualizando tabelas do banco de dados...");
            
            // Cria todas as tabelas (dbDelta atualiza se já existirem)
            $result = Haklai_DB::create_tables();
            
            if ($result) {
                error_log("Haklai DB: Todas as tabelas criadas/atualizadas com sucesso!");
                
                // Log detalhado das tabelas criadas
                $stats = Haklai_DB::get_db_stats();
                error_log("Haklai DB: Estatísticas das tabelas - " . json_encode($stats));
            } else {
                error_log("Haklai DB: ERRO ao criar tabelas!");
                wp_die(
                    '<h1>' . __('Erro na Ativação', 'haklai-app') . '</h1>' .
                    '<p>' . __('Não foi possível criar as tabelas do banco de dados. Verifique os logs para mais detalhes.', 'haklai-app') . '</p>' .
                    '<p>' . __('Erro registrado em: wp-content/debug.log', 'haklai-app') . '</p>',
                    __('Erro na Ativação', 'haklai-app'),
                    array('back_link' => true)
                );
            }
        } else {
            // Todas as tabelas existem, mas verifica se precisa atualizar estrutura
            $current_version = Haklai_DB::get_db_version();
            $new_version = Haklai_DB::DB_VERSION;
            
            if (version_compare($current_version, $new_version, '<')) {
                error_log("Haklai DB: Atualizando banco de dados de {$current_version} para {$new_version}");
                // Força criação/atualização das tabelas para garantir estrutura atualizada
                $result = Haklai_DB::create_tables();
                if (!$result) {
                    error_log("Haklai DB: ERRO ao atualizar tabelas!");
                }
            } else {
                error_log("Haklai DB: Tabelas já existem e estão atualizadas");
            }
        }
        
        // FASE 4: Para instalação NOVA, cria tenant padrão e associa usuário
        // (Migração só roda em instalações antigas que já têm dados)
        if (!$tenants_exists && !$other_tables_exist) {
            // É uma instalação completamente nova
            self::setup_default_tenant_for_new_installation();
        } else {
            // Instalação antiga - verifica se precisa migração
            // (Mas como estamos em desenvolvimento, não executamos migração)
            error_log("Haklai DB: Instalação antiga detectada. Migração desabilitada em desenvolvimento.");
        }
        
        // MIGRAÇÃO AUTOMÁTICA: Verifica se há dados nos CPTs e se as tabelas estão vazias
        self::auto_migrate_existing_data();
    }
    
    /**
     * FASE 4: Configura tenant padrão para instalação nova
     * Cria tenant padrão e associa usuário atual
     */
    private static function setup_default_tenant_for_new_installation() {
        if (!class_exists('Haklai_Tenant_Manager')) {
            error_log("Haklai DB: AVISO - Haklai_Tenant_Manager não encontrado!");
            return;
        }
        
        $tenant_manager = Haklai_Tenant_Manager::get_instance();
        
        // Cria tenant padrão
        $default_tenant_id = $tenant_manager->create_default_tenant();
        
        if (!$default_tenant_id) {
            error_log("Haklai DB: ERRO ao criar tenant padrão na instalação nova!");
            return;
        }
        
        error_log("Haklai DB: Tenant padrão criado com ID: {$default_tenant_id}");
        
        // Associa usuário atual ao tenant padrão
        $current_user_id = get_current_user_id();
        if (!$current_user_id) {
            // Se não houver usuário logado, busca primeiro admin
            $admins = get_users(array('role' => 'administrator', 'number' => 1));
            if (!empty($admins)) {
                $current_user_id = $admins[0]->ID;
            }
        }
        
        if ($current_user_id) {
            $tenant_repository = new TenantRepository();
            if ($tenant_repository->setCurrentTenant($default_tenant_id, $current_user_id)) {
                error_log("Haklai DB: Usuário (ID: {$current_user_id}) associado ao tenant padrão");
            }
        }
    }
    
    /**
     * Migra automaticamente dados existentes dos CPTs para as tabelas
     * Executa apenas se houver dados nos CPTs e as tabelas estiverem vazias
     */
    private static function auto_migrate_existing_data() {
        // Verifica se as tabelas estão vazias
        if (!Haklai_DB::tables_are_empty()) {
            error_log("Haklai DB: Tabelas já possuem dados. Migração automática ignorada.");
            return;
        }
        
        // Verifica se há dados nos CPTs
        $cpt_data = Haklai_DB::check_cpt_data();
        
        if (!$cpt_data['has_data']) {
            error_log("Haklai DB: Nenhum dado encontrado nos CPTs. Migração não necessária.");
            return;
        }
        
        // Há dados nos CPTs e tabelas estão vazias - executa migração
        error_log("Haklai DB: Iniciando migração automática de dados dos CPTs...");
        error_log("Haklai DB: Dados encontrados - Membros: {$cpt_data['members_count']}, Células: {$cpt_data['cells_count']}, Reuniões: {$cpt_data['meetings_count']}, Presenças: {$cpt_data['attendance_count']}");
        
        $migration_result = Haklai_DB::migrate_all_data_from_cpt();
        
        if ($migration_result['success']) {
            error_log("Haklai DB: Migração automática concluída com sucesso!");
            error_log("Haklai DB: Resultado - " . json_encode($migration_result));
            
            // Salva flag de migração concluída
            update_option('haklai_auto_migration_completed', true);
            update_option('haklai_auto_migration_date', current_time('mysql'));
        } else {
            error_log("Haklai DB: AVISO - Migração automática teve alguns erros: " . json_encode($migration_result));
            // Não interrompe a ativação, mas registra o erro
        }
    }
}

