<?php
/**
 * Classe responsável pelo gerenciamento do banco de dados
 * Cria e gerencia tabelas personalizadas com prefixo hklapp_
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
 * Classe Haklai_DB
 * Gerencia todas as operações com tabelas personalizadas
 */
class Haklai_DB {
    
    /**
     * Prefixo das tabelas
     */
    const TABLE_PREFIX = 'hklapp_';
    
    /**
     * Versão do banco de dados
     */
    const DB_VERSION = '2.0.0';
    
    /**
     * Nomes das tabelas
     */
    public static function get_table_names() {
        global $wpdb;
        $prefix = $wpdb->prefix . self::TABLE_PREFIX;
        
        return array(
            'tenants' => $prefix . 'tenants', // FASE 4: Multi-tenancy
            'members' => $prefix . 'members',
            'cells' => $prefix . 'cells',
            'networks' => $prefix . 'networks',
            'meetings' => $prefix . 'meetings',
            'attendance' => $prefix . 'attendance',
            'reports' => $prefix . 'reports',
            'alerts' => $prefix . 'alerts',
            'member_training' => $prefix . 'member_training',
            'member_skills' => $prefix . 'member_skills',
            'auth_logs' => $prefix . 'auth_logs',
            'backups' => $prefix . 'backups',
            'settings' => $prefix . 'settings',
        );
    }
    
    /**
     * Obtém o nome completo de uma tabela
     * 
     * @param string $table_name Nome da tabela sem prefixo
     * @return string Nome completo da tabela
     */
    public static function get_table_name($table_name) {
        global $wpdb;
        $tables = self::get_table_names();
        return isset($tables[$table_name]) ? $tables[$table_name] : $wpdb->prefix . self::TABLE_PREFIX . $table_name;
    }
    
    /**
     * FASE 4: Obtém tenant_id atual (helper centralizado)
     * Garante que sempre retorna um tenant_id válido
     * 
     * @return int|null ID do tenant atual ou null se não conseguir obter
     */
    private static function get_current_tenant_id() {
        // Tenta obter do TenantManager
        if (class_exists('Haklai_Tenant_Manager')) {
            $tenant_manager = Haklai_Tenant_Manager::get_instance();
            $tenant_id = $tenant_manager->get_current_tenant_id();
            
            if ($tenant_id) {
                return intval($tenant_id);
            }
        }
        
        // Fallback: busca tenant padrão
        if (class_exists('TenantRepository')) {
            $tenant_repository = new TenantRepository();
            $default_tenant = $tenant_repository->findByCode('DEFAULT');
            
            if ($default_tenant) {
                return intval($default_tenant->id);
            }
        }
        
        return null;
    }
    
    /**
     * Cria todas as tabelas do banco de dados
     * 
     * @return bool True se todas as tabelas foram criadas com sucesso
     */
    public static function create_tables() {
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $results = array();
        
        // FASE 4: Cria tabela de tenants primeiro (dependência para outras tabelas)
        $results['tenants'] = self::create_tenants_table($charset_collate);
        
        // Cria tabela de membros
        $results['members'] = self::create_members_table($charset_collate);
        
        // Cria tabela de células
        $results['cells'] = self::create_cells_table($charset_collate);
        
        // Cria tabela de redes
        $results['networks'] = self::create_networks_table($charset_collate);
        
        // Cria tabela de reuniões
        $results['meetings'] = self::create_meetings_table($charset_collate);
        
        // Cria tabela de presenças
        $results['attendance'] = self::create_attendance_table($charset_collate);
        
        // Cria tabela de relatórios
        $results['reports'] = self::create_reports_table($charset_collate);
        
        // Cria tabela de alertas
        $results['alerts'] = self::create_alerts_table($charset_collate);
        
        // Cria tabela de formações de membros
        $results['member_training'] = self::create_member_training_table($charset_collate);
        
        // Cria tabela de habilidades de membros
        $results['member_skills'] = self::create_member_skills_table($charset_collate);
        
        // Cria tabela de logs de autenticação
        $results['auth_logs'] = self::create_auth_logs_table($charset_collate);
        
        // Cria tabela de backups
        $results['backups'] = self::create_backups_table($charset_collate);
        
        // Cria tabela de configurações
        $results['settings'] = self::create_settings_table($charset_collate);
        
        // Atualiza versão do banco
        update_option('haklai_db_version', self::DB_VERSION);
        
        // Verifica se todas as tabelas foram criadas
        $all_success = true;
        foreach ($results as $table => $success) {
            if (!$success) {
                error_log("Haklai DB: Erro ao criar tabela {$table}");
                $all_success = false;
            }
        }
        
        return $all_success;
    }
    
    /**
     * Verifica se todas as tabelas existem
     * 
     * @return bool True se todas as tabelas existem
     */
    public static function tables_exist() {
        global $wpdb;
        $tables = self::get_table_names();
        
        foreach ($tables as $key => $table_name) {
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
            if (!$table_exists) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Remove todas as tabelas do banco de dados
     * ATENÇÃO: Isso apaga TODOS os dados!
     * 
     * @return bool True se todas as tabelas foram removidas
     */
    public static function drop_tables() {
        global $wpdb;
        $tables = self::get_table_names();
        
        foreach ($tables as $key => $table_name) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
        
        // Remove versão do banco
        delete_option('haklai_db_version');
        
        return true;
    }
    
    // ============================================================
    // MÉTODOS PRIVADOS - CRIAÇÃO DE TABELAS
    // ============================================================
    
    /**
     * FASE 4: Cria tabela de tenants
     */
    private static function create_tenants_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('tenants');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            code VARCHAR(10) NOT NULL,
            location VARCHAR(255) NULL,
            supervisor_contact VARCHAR(255) NULL,
            supervisor_pastor_id BIGINT UNSIGNED NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            created_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_code (code),
            KEY idx_supervisor (supervisor_pastor_id),
            KEY idx_is_active (is_active)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de membros
     */
    private static function create_members_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tenant_id BIGINT UNSIGNED NULL,
            wp_user_id BIGINT UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NULL,
            phone VARCHAR(20) NULL,
            birth_date DATE NULL,
            gender ENUM('M', 'F', 'O') NULL,
            address TEXT NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(50) NULL,
            zip_code VARCHAR(10) NULL,
            role_level TINYINT(1) NOT NULL DEFAULT 0,
            cell_id BIGINT UNSIGNED NULL,
            leader_id BIGINT UNSIGNED NULL,
            network_id BIGINT UNSIGNED NULL,
            baptism_status ENUM('visitor', 'frequent_visitor', 'baptized') NULL,
            baptism_date DATE NULL,
            is_visitor TINYINT(1) NOT NULL DEFAULT 0,
            visitor_count INT(11) NOT NULL DEFAULT 0,
            status ENUM('active', 'inactive', 'transferred', 'suspended') NOT NULL DEFAULT 'active',
            last_attendance_date DATE NULL,
            total_attendances INT(11) NOT NULL DEFAULT 0,
            consecutive_absences INT(11) NOT NULL DEFAULT 0,
            photo_url VARCHAR(500) NULL,
            skill VARCHAR(100) NULL,
            encontro_deus ENUM('sim', 'nao') NULL DEFAULT 'nao',
            formacoes JSON NULL,
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            created_by BIGINT UNSIGNED NULL,
            updated_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_wp_user (wp_user_id),
            KEY idx_name (name),
            KEY idx_email (email),
            KEY idx_phone (phone),
            KEY idx_cell_id (cell_id),
            KEY idx_tenant_id (tenant_id),
            KEY idx_leader_id (leader_id),
            KEY idx_network_id (network_id),
            KEY idx_role_level (role_level),
            KEY idx_baptism_status (baptism_status),
            KEY idx_status (status),
            KEY idx_cell_status (cell_id, status),
            KEY idx_role_status (role_level, status),
            KEY idx_baptism_status_active (baptism_status, status),
            KEY idx_visitor_active (is_visitor, status),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        // Verifica se a tabela foi criada
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de células
     */
    private static function create_cells_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('cells');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tenant_id BIGINT UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            address TEXT NULL,
            neighborhood VARCHAR(100) NULL,
            city VARCHAR(100) NULL,
            state VARCHAR(50) NULL,
            zip_code VARCHAR(10) NULL,
            coordinates POINT NULL,
            leader_id BIGINT UNSIGNED NULL,
            discipler_id BIGINT UNSIGNED NULL,
            network_id BIGINT UNSIGNED NULL,
            meeting_day ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NULL,
            meeting_time TIME NULL,
            meeting_frequency ENUM('weekly', 'biweekly', 'monthly') DEFAULT 'weekly',
            total_members INT(11) NOT NULL DEFAULT 0,
            active_members INT(11) NOT NULL DEFAULT 0,
            visitors_count INT(11) NOT NULL DEFAULT 0,
            avg_attendance DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            last_meeting_date DATE NULL,
            status ENUM('active', 'inactive', 'closed') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            created_by BIGINT UNSIGNED NULL,
            updated_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY idx_tenant_id (tenant_id),
            KEY idx_name (name),
            KEY idx_leader_id (leader_id),
            KEY idx_discipler_id (discipler_id),
            KEY idx_network_id (network_id),
            KEY idx_status (status),
            KEY idx_meeting_day (meeting_day),
            KEY idx_city (city),
            KEY idx_neighborhood (neighborhood),
            KEY idx_network_status (network_id, status),
            KEY idx_leader_status (leader_id, status),
            KEY idx_discipler_status (discipler_id, status),
            KEY idx_meeting_schedule (meeting_day, meeting_time)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de redes
     */
    private static function create_networks_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('networks');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tenant_id BIGINT UNSIGNED NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT NULL,
            pastor_id BIGINT UNSIGNED NULL,
            senior_pastor_id BIGINT UNSIGNED NULL,
            total_cells INT(11) NOT NULL DEFAULT 0,
            active_cells INT(11) NOT NULL DEFAULT 0,
            total_members INT(11) NOT NULL DEFAULT 0,
            active_members INT(11) NOT NULL DEFAULT 0,
            avg_attendance DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            created_by BIGINT UNSIGNED NULL,
            updated_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY idx_tenant_id (tenant_id),
            KEY idx_name (name),
            KEY idx_pastor_id (pastor_id),
            KEY idx_senior_pastor_id (senior_pastor_id),
            KEY idx_status (status),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de reuniões
     */
    private static function create_meetings_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('meetings');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            tenant_id BIGINT UNSIGNED NULL,
            cell_id BIGINT UNSIGNED NOT NULL,
            meeting_date DATE NOT NULL,
            meeting_time TIME NULL,
            host_name VARCHAR(255) NULL,
            host_phone VARCHAR(20) NULL,
            address TEXT NULL,
            is_online TINYINT(1) NOT NULL DEFAULT 0,
            online_link VARCHAR(500) NULL,
            total_members INT(11) NOT NULL DEFAULT 0,
            present_members INT(11) NOT NULL DEFAULT 0,
            visitors_count INT(11) NOT NULL DEFAULT 0,
            attendance_rate DECIMAL(5,2) NOT NULL DEFAULT 0.00,
            lesson_title VARCHAR(255) NULL,
            lesson_scripture VARCHAR(255) NULL,
            lesson_notes TEXT NULL,
            prayer_requests TEXT NULL,
            announcements TEXT NULL,
            status ENUM('scheduled', 'completed', 'cancelled') NOT NULL DEFAULT 'scheduled',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            created_by BIGINT UNSIGNED NULL,
            updated_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_cell_date (cell_id, meeting_date),
            KEY idx_tenant_id (tenant_id),
            KEY idx_cell_id (cell_id),
            KEY idx_meeting_date (meeting_date),
            KEY idx_status (status),
            KEY idx_created_by (created_by),
            KEY idx_cell_date (cell_id, meeting_date),
            KEY idx_date_range (meeting_date)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de presenças
     */
    private static function create_attendance_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('attendance');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            meeting_id BIGINT UNSIGNED NOT NULL,
            member_id BIGINT UNSIGNED NOT NULL,
            is_present TINYINT(1) NOT NULL DEFAULT 0,
            arrival_time TIME NULL,
            notes TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_meeting_member (meeting_id, member_id),
            KEY idx_meeting_id (meeting_id),
            KEY idx_member_id (member_id),
            KEY idx_is_present (is_present),
            KEY idx_meeting_member (meeting_id, member_id),
            KEY idx_created_at (created_at)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de relatórios
     */
    private static function create_reports_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('reports');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(50) NOT NULL,
            format ENUM('html', 'pdf', 'csv', 'excel') NOT NULL DEFAULT 'html',
            filters JSON NULL,
            data JSON NULL,
            file_path VARCHAR(500) NULL,
            file_size INT(11) NULL,
            status ENUM('processing', 'completed', 'failed') NOT NULL DEFAULT 'processing',
            created_at DATETIME NOT NULL,
            completed_at DATETIME NULL,
            created_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY idx_type (type),
            KEY idx_status (status),
            KEY idx_created_by (created_by),
            KEY idx_created_at (created_at),
            KEY idx_type_status (type, status)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de alertas
     */
    private static function create_alerts_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('alerts');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT NOT NULL,
            user_id BIGINT UNSIGNED NULL,
            cell_id BIGINT UNSIGNED NULL,
            member_id BIGINT UNSIGNED NULL,
            is_read TINYINT(1) NOT NULL DEFAULT 0,
            read_at DATETIME NULL,
            priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
            action_url VARCHAR(500) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_type (type),
            KEY idx_user_id (user_id),
            KEY idx_cell_id (cell_id),
            KEY idx_member_id (member_id),
            KEY idx_is_read (is_read),
            KEY idx_priority (priority),
            KEY idx_created_at (created_at),
            KEY idx_user_unread (user_id, is_read)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de formações de membros
     */
    private static function create_member_training_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('member_training');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            training_type VARCHAR(100) NOT NULL,
            training_name VARCHAR(255) NOT NULL,
            completion_date DATE NULL,
            certificate_url VARCHAR(500) NULL,
            notes TEXT NULL,
            status ENUM('in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'in_progress',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_member_id (member_id),
            KEY idx_training_type (training_type),
            KEY idx_status (status),
            KEY idx_completion_date (completion_date),
            KEY idx_member_status (member_id, status)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de habilidades de membros
     */
    private static function create_member_skills_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('member_skills');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            member_id BIGINT UNSIGNED NOT NULL,
            skill_name VARCHAR(100) NOT NULL,
            skill_level ENUM('beginner', 'intermediate', 'advanced', 'expert') NOT NULL DEFAULT 'beginner',
            description TEXT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_member_skill (member_id, skill_name),
            KEY idx_member_id (member_id),
            KEY idx_skill_name (skill_name),
            KEY idx_skill_level (skill_level)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de logs de autenticação
     */
    private static function create_auth_logs_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('auth_logs');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NULL,
            action VARCHAR(50) NOT NULL,
            ip_address VARCHAR(45) NULL,
            user_agent TEXT NULL,
            success TINYINT(1) NOT NULL DEFAULT 1,
            failure_reason VARCHAR(255) NULL,
            created_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY idx_user_id (user_id),
            KEY idx_action (action),
            KEY idx_success (success),
            KEY idx_created_at (created_at),
            KEY idx_user_action (user_id, action)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de backups
     */
    private static function create_backups_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('backups');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            backup_type VARCHAR(50) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size BIGINT UNSIGNED NULL,
            tables_included JSON NULL,
            status ENUM('in_progress', 'completed', 'failed') NOT NULL DEFAULT 'in_progress',
            created_at DATETIME NOT NULL,
            completed_at DATETIME NULL,
            created_by BIGINT UNSIGNED NULL,
            PRIMARY KEY (id),
            KEY idx_backup_type (backup_type),
            KEY idx_status (status),
            KEY idx_created_at (created_at),
            KEY idx_status_type (status, backup_type)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Cria tabela de configurações
     */
    private static function create_settings_table($charset_collate) {
        global $wpdb;
        $table_name = self::get_table_name('settings');
        
        $sql = "CREATE TABLE {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            setting_key VARCHAR(100) NOT NULL,
            setting_value LONGTEXT NULL,
            setting_type VARCHAR(50) NOT NULL DEFAULT 'string',
            description TEXT NULL,
            is_autoload TINYINT(1) NOT NULL DEFAULT 1,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY unique_setting_key (setting_key),
            KEY idx_setting_key (setting_key),
            KEY idx_is_autoload (is_autoload)
        ) {$charset_collate};";
        
        dbDelta($sql);
        
        return $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name;
    }
    
    /**
     * Obtém estatísticas do banco de dados
     * 
     * @return array Estatísticas das tabelas
     */
    public static function get_db_stats() {
        global $wpdb;
        $tables = self::get_table_names();
        $stats = array();
        
        foreach ($tables as $key => $table_name) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
            $stats[$key] = array(
                'table_name' => $table_name,
                'row_count' => intval($count),
                'exists' => $wpdb->get_var("SHOW TABLES LIKE '{$table_name}'") === $table_name
            );
        }
        
        return $stats;
    }
    
    /**
     * Obtém versão atual do banco de dados
     * 
     * @return string Versão do banco
     */
    public static function get_db_version() {
        return get_option('haklai_db_version', '0.0.0');
    }
    
    // ============================================================
    // MÉTODOS CRUD - MEMBROS
    // ============================================================
    
    /**
     * Obtém membro por ID
     * 
     * @param int $member_id ID do membro
     * @return object|false Objeto do membro ou false se não encontrado
     */
    public static function get_member($member_id) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            $member = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d AND tenant_id = %d",
                $member_id,
                $current_tenant_id
            ));
        } else {
            $member = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $member_id
            ));
        }
        
        return $member;
    }
    
    /**
     * Obtém lista de membros com filtros otimizados
     * 
     * @param array $args Argumentos de filtro
     * @return array Lista de membros
     */
    public static function get_members($args = array()) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        $defaults = array(
            'cell_id' => null,
            'network_id' => null,
            'status' => 'active',
            'role_level' => null,
            'baptism_status' => null,
            'is_visitor' => null,
            'search' => null,
            'limit' => 50,
            'offset' => 0,
            'orderby' => 'name',
            'order' => 'ASC'
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $prepare_values = array();
        
        if (!empty($args['cell_id'])) {
            $where[] = 'cell_id = %d';
            $prepare_values[] = $args['cell_id'];
        }
        
        if (!empty($args['network_id'])) {
            $where[] = 'network_id = %d';
            $prepare_values[] = $args['network_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        if ($args['role_level'] !== null) {
            $where[] = 'role_level = %d';
            $prepare_values[] = $args['role_level'];
        }
        
        if (!empty($args['baptism_status'])) {
            $where[] = 'baptism_status = %s';
            $prepare_values[] = $args['baptism_status'];
        }
        
        if ($args['is_visitor'] !== null) {
            $where[] = 'is_visitor = %d';
            $prepare_values[] = $args['is_visitor'] ? 1 : 0;
        }
        
        if (!empty($args['search'])) {
            $where[] = '(name LIKE %s OR email LIKE %s OR phone LIKE %s)';
            $search_term = '%' . $wpdb->esc_like($args['search']) . '%';
            $prepare_values[] = $search_term;
            $prepare_values[] = $search_term;
            $prepare_values[] = $search_term;
        }
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id && !isset($args['skip_tenant_filter'])) {
            $where[] = 'tenant_id = %d';
            $prepare_values[] = $current_tenant_id;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Validação de orderby para prevenir SQL injection
        $allowed_orderby = array('id', 'name', 'email', 'created_at', 'role_level', 'cell_id');
        $orderby = in_array($args['orderby'], $allowed_orderby) ? $args['orderby'] : 'name';
        $order = strtoupper($args['order']) === 'DESC' ? 'DESC' : 'ASC';
        
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
        $prepare_values[] = $args['limit'];
        $prepare_values[] = $args['offset'];
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Salva membro (cria ou atualiza)
     * 
     * @param array $data Dados do membro
     * @param int|null $member_id ID do membro (null para criar novo)
     * @return int|false ID do membro ou false em caso de erro
     */
    public static function save_member($data, $member_id = null) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        $defaults = array(
            'name' => '',
            'email' => null,
            'phone' => null,
            'cell_id' => null,
            'status' => 'active',
            'role_level' => 0,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        );
        
        $data = wp_parse_args($data, $defaults);
        
        // FASE 4: Garante tenant_id (obtém do array ou do contexto atual)
        if (!isset($data['tenant_id']) || empty($data['tenant_id'])) {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        // Sanitização
        $data['name'] = sanitize_text_field($data['name']);
        $data['email'] = !empty($data['email']) ? sanitize_email($data['email']) : null;
        $data['phone'] = !empty($data['phone']) ? sanitize_text_field($data['phone']) : null;
        $data['status'] = sanitize_text_field($data['status']);
        $data['updated_at'] = current_time('mysql');
        
        if ($member_id) {
            // Atualiza membro existente
            $result = $wpdb->update(
                $table_name,
                $data,
                array('id' => $member_id),
                null,
                array('%d')
            );
            
            return $result !== false ? $member_id : false;
        } else {
            // Cria novo membro
            $result = $wpdb->insert($table_name, $data);
            
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Remove membro
     * 
     * @param int $member_id ID do membro
     * @return bool True se removido com sucesso
     */
    public static function delete_member($member_id) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        return $wpdb->delete($table_name, array('id' => $member_id), array('%d')) !== false;
    }
    
    /**
     * Conta membros com filtros
     * 
     * @param array $args Argumentos de filtro
     * @return int Total de membros
     */
    public static function count_members($args = array()) {
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        $where = array('1=1');
        $prepare_values = array();
        
        if (!empty($args['cell_id'])) {
            $where[] = 'cell_id = %d';
            $prepare_values[] = $args['cell_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        $where_clause = implode(' AND ', $where);
        
        $query = "SELECT COUNT(*) FROM {$table_name} WHERE {$where_clause}";
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        return (int) $wpdb->get_var($query);
    }
    
    // ============================================================
    // MÉTODOS CRUD - CÉLULAS
    // ============================================================
    
    /**
     * Obtém célula por ID
     */
    public static function get_cell($cell_id) {
        global $wpdb;
        $table_name = self::get_table_name('cells');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d AND tenant_id = %d",
                $cell_id,
                $current_tenant_id
            ));
        } else {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $cell_id
            ));
        }
    }
    
    /**
     * Obtém lista de células
     */
    public static function get_cells($args = array()) {
        global $wpdb;
        $table_name = self::get_table_name('cells');
        
        $defaults = array(
            'network_id' => null,
            'leader_id' => null,
            'discipler_id' => null,
            'status' => 'active',
            'limit' => 50,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $prepare_values = array();
        
        if (!empty($args['network_id'])) {
            $where[] = 'network_id = %d';
            $prepare_values[] = $args['network_id'];
        }
        
        if (!empty($args['leader_id'])) {
            $where[] = 'leader_id = %d';
            $prepare_values[] = $args['leader_id'];
        }
        
        if (!empty($args['discipler_id'])) {
            $where[] = 'discipler_id = %d';
            $prepare_values[] = $args['discipler_id'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id && !isset($args['skip_tenant_filter'])) {
            $where[] = 'tenant_id = %d';
            $prepare_values[] = $current_tenant_id;
        }
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY name ASC LIMIT %d OFFSET %d";
        $prepare_values[] = $args['limit'];
        $prepare_values[] = $args['offset'];
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Converte ID do CPT para ID da tabela
     * 
     * Versão 2.0 - Usa Haklai_Sync_Manager para mapeamento rápido e confiável
     * 
     * @param int $cpt_id ID do CPT (post ID)
     * @param string $cpt_type Tipo do CPT (haklai_cell, haklai_member, haklai_network)
     * @return int|false ID da tabela ou false
     */
    public static function get_table_id_from_cpt($cpt_id, $cpt_type) {
        // Delega para Haklai_Sync_Manager (método otimizado com mapeamento via meta key)
        if (class_exists('Haklai_Sync_Manager')) {
            return Haklai_Sync_Manager::get_table_id_from_cpt($cpt_id, $cpt_type);
        }
        
        // Fallback para método antigo (compatibilidade)
        global $wpdb;
        
        switch ($cpt_type) {
            case 'haklai_cell':
                $table_name = self::get_table_name('cells');
                $cell_name = get_the_title($cpt_id);
                $table_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                    $cell_name
                ));
                return $table_id ? intval($table_id) : false;
                
            case 'haklai_member':
                $table_name = self::get_table_name('members');
                $member_name = get_the_title($cpt_id);
                $table_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                    $member_name
                ));
                return $table_id ? intval($table_id) : false;
                
            case 'haklai_network':
                $table_name = self::get_table_name('networks');
                $network_name = get_the_title($cpt_id);
                $table_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                    $network_name
                ));
                return $table_id ? intval($table_id) : false;
        }
        
        return false;
    }
    
    /**
     * Busca células associadas a um membro baseado no nível hierárquico
     * 
     * @param int $member_cpt_id ID do membro no CPT
     * @param int $role_level Nível hierárquico (1=Líder, 2=Discipulador, 3=Pastor Rede, 4=Pastor Senior, 5=Pastor Supervisor)
     * @return array Array de IDs de células da tabela
     */
    public static function get_cells_by_hierarchy_level($member_cpt_id, $role_level) {
        global $wpdb;
        $cells_table = self::get_table_name('cells');
        $members_table = self::get_table_name('members');
        $networks_table = self::get_table_name('networks');
        
        // Converte ID do CPT para ID da tabela
        $member_table_id = self::get_table_id_from_cpt($member_cpt_id, 'haklai_member');
        if (!$member_table_id) {
            return array();
        }
        
        $cell_ids = array();
        
        switch ($role_level) {
            case 1: // Líder de Célula
                // Busca células onde esse membro é líder
                $cells = $wpdb->get_results($wpdb->prepare(
                    "SELECT id FROM {$cells_table} WHERE leader_id = %d AND status = 'active'",
                    $member_table_id
                ));
                foreach ($cells as $cell) {
                    $cell_ids[] = intval($cell->id);
                }
                break;
                
            case 2: // Discipulador
                // Busca células onde esse membro é discipulador
                $cells = $wpdb->get_results($wpdb->prepare(
                    "SELECT id FROM {$cells_table} WHERE discipler_id = %d AND status = 'active'",
                    $member_table_id
                ));
                foreach ($cells as $cell) {
                    $cell_ids[] = intval($cell->id);
                }
                break;
                
            case 3: // Pastor de Rede
                // Busca redes onde esse membro é pastor
                $networks = $wpdb->get_results($wpdb->prepare(
                    "SELECT id FROM {$networks_table} WHERE pastor_id = %d AND status = 'active'",
                    $member_table_id
                ));
                foreach ($networks as $network) {
                    // Busca células dessa rede
                    $cells = $wpdb->get_results($wpdb->prepare(
                        "SELECT id FROM {$cells_table} WHERE network_id = %d AND status = 'active'",
                        $network->id
                    ));
                    foreach ($cells as $cell) {
                        $cell_ids[] = intval($cell->id);
                    }
                }
                break;
                
            case 4: // Pastor Senior
                // Busca redes onde esse membro é pastor senior
                $networks = $wpdb->get_results($wpdb->prepare(
                    "SELECT id FROM {$networks_table} WHERE senior_pastor_id = %d AND status = 'active'",
                    $member_table_id
                ));
                foreach ($networks as $network) {
                    // Busca células dessa rede
                    $cells = $wpdb->get_results($wpdb->prepare(
                        "SELECT id FROM {$cells_table} WHERE network_id = %d AND status = 'active'",
                        $network->id
                    ));
                    foreach ($cells as $cell) {
                        $cell_ids[] = intval($cell->id);
                    }
                }
                break;
                
            case 5: // Pastor Supervisor (todos)
                // Retorna todas as células ativas
                $cells = $wpdb->get_results(
                    "SELECT id FROM {$cells_table} WHERE status = 'active'"
                );
                foreach ($cells as $cell) {
                    $cell_ids[] = intval($cell->id);
                }
                break;
        }
        
        return array_unique($cell_ids);
    }
    
    /**
     * Salva célula
     */
    public static function save_cell($data, $cell_id = null) {
        global $wpdb;
        $table_name = self::get_table_name('cells');
        
        $data['updated_at'] = current_time('mysql');
        
        // FASE 4: Garante tenant_id
        if (!isset($data['tenant_id']) || empty($data['tenant_id'])) {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        if ($cell_id) {
            return $wpdb->update($table_name, $data, array('id' => $cell_id), null, array('%d')) !== false ? $cell_id : false;
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    // ============================================================
    // MÉTODOS CRUD - REUNIÕES
    // ============================================================
    
    /**
     * Obtém reunião por ID
     */
    public static function get_meeting($meeting_id) {
        global $wpdb;
        $table_name = self::get_table_name('meetings');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d AND tenant_id = %d",
                $meeting_id,
                $current_tenant_id
            ));
        } else {
            return $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $meeting_id
            ));
        }
    }
    
    /**
     * Obtém reuniões com filtros
     */
    public static function get_meetings($args = array()) {
        global $wpdb;
        $table_name = self::get_table_name('meetings');
        
        $defaults = array(
            'cell_id' => null,
            'date_from' => null,
            'date_to' => null,
            'status' => null,
            'limit' => 50,
            'offset' => 0,
        );
        
        $args = wp_parse_args($args, $defaults);
        
        $where = array('1=1');
        $prepare_values = array();
        
        if (!empty($args['cell_id'])) {
            $where[] = 'cell_id = %d';
            $prepare_values[] = $args['cell_id'];
        }
        
        if (!empty($args['date_from'])) {
            $where[] = 'meeting_date >= %s';
            $prepare_values[] = $args['date_from'];
        }
        
        if (!empty($args['date_to'])) {
            $where[] = 'meeting_date <= %s';
            $prepare_values[] = $args['date_to'];
        }
        
        if (!empty($args['status'])) {
            $where[] = 'status = %s';
            $prepare_values[] = $args['status'];
        }
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id && !isset($args['skip_tenant_filter'])) {
            $where[] = 'tenant_id = %d';
            $prepare_values[] = $current_tenant_id;
        }
        
        $where_clause = implode(' AND ', $where);
        $query = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY meeting_date DESC LIMIT %d OFFSET %d";
        $prepare_values[] = $args['limit'];
        $prepare_values[] = $args['offset'];
        
        if (!empty($prepare_values)) {
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    /**
     * Salva reunião
     */
    public static function save_meeting($data, $meeting_id = null) {
        global $wpdb;
        $table_name = self::get_table_name('meetings');
        
        $data['updated_at'] = current_time('mysql');
        
        // FASE 4: Garante tenant_id
        if (!isset($data['tenant_id']) || empty($data['tenant_id'])) {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        if ($meeting_id) {
            return $wpdb->update($table_name, $data, array('id' => $meeting_id), null, array('%d')) !== false ? $meeting_id : false;
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    // ============================================================
    // MÉTODOS CRUD - PRESENÇAS
    // ============================================================
    
    /**
     * Registra presença de membro em reunião
     */
    public static function save_attendance($meeting_id, $member_id, $is_present = true) {
        global $wpdb;
        $table_name = self::get_table_name('attendance');
        
        // Verifica se já existe registro
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE meeting_id = %d AND member_id = %d",
            $meeting_id,
            $member_id
        ));
        
        $data = array(
            'meeting_id' => $meeting_id,
            'member_id' => $member_id,
            'is_present' => $is_present ? 1 : 0,
            'updated_at' => current_time('mysql'),
        );
        
        if ($existing) {
            // Atualiza existente
            return $wpdb->update($table_name, $data, array('id' => $existing), null, array('%d')) !== false;
        } else {
            // Cria novo
            $data['created_at'] = current_time('mysql');
            return $wpdb->insert($table_name, $data) !== false;
        }
    }
    
    /**
     * Obtém presenças de uma reunião
     */
    public static function get_meeting_attendance($meeting_id) {
        global $wpdb;
        $attendance_table = self::get_table_name('attendance');
        $members_table = self::get_table_name('members');
        $meetings_table = self::get_table_name('meetings');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT a.*, m.name, m.email, m.phone 
                 FROM {$attendance_table} a 
                 LEFT JOIN {$members_table} m ON a.member_id = m.id 
                 LEFT JOIN {$meetings_table} mt ON a.meeting_id = mt.id
                 WHERE a.meeting_id = %d AND mt.tenant_id = %d AND m.tenant_id = %d
                 ORDER BY m.name ASC",
                $meeting_id,
                $current_tenant_id,
                $current_tenant_id
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT a.*, m.name, m.email, m.phone 
                 FROM {$attendance_table} a 
                 LEFT JOIN {$members_table} m ON a.member_id = m.id 
                 WHERE a.meeting_id = %d 
                 ORDER BY m.name ASC",
                $meeting_id
            ));
        }
    }
    
    /**
     * Obtém histórico de presenças de um membro
     */
    public static function get_member_attendance_history($member_id, $limit = 10) {
        global $wpdb;
        $attendance_table = self::get_table_name('attendance');
        $meetings_table = self::get_table_name('meetings');
        $members_table = self::get_table_name('members');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT a.*, m.meeting_date, m.cell_id 
                 FROM {$attendance_table} a 
                 LEFT JOIN {$meetings_table} m ON a.meeting_id = m.id 
                 LEFT JOIN {$members_table} mem ON a.member_id = mem.id
                 WHERE a.member_id = %d AND m.tenant_id = %d AND mem.tenant_id = %d
                 ORDER BY m.meeting_date DESC 
                 LIMIT %d",
                $member_id,
                $current_tenant_id,
                $current_tenant_id,
                $limit
            ));
        } else {
            return $wpdb->get_results($wpdb->prepare(
                "SELECT a.*, m.meeting_date, m.cell_id 
                 FROM {$attendance_table} a 
                 LEFT JOIN {$meetings_table} m ON a.meeting_id = m.id 
                 WHERE a.member_id = %d 
                 ORDER BY m.meeting_date DESC 
                 LIMIT %d",
                $member_id,
                $limit
            ));
        }
    }
    
    // ============================================================
    // QUERIES OTIMIZADAS PARA RELATÓRIOS
    // ============================================================
    
    /**
     * Gera relatório completo de reunião com uma única query otimizada
     * 
     * @param int $meeting_id ID da reunião
     * @return array Dados completos da reunião
     */
    public static function get_meeting_report_data($meeting_id) {
        global $wpdb;
        $meetings_table = self::get_table_name('meetings');
        $attendance_table = self::get_table_name('attendance');
        $members_table = self::get_table_name('members');
        $cells_table = self::get_table_name('cells');
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        
        // Query única otimizada com JOINs
        if ($current_tenant_id) {
            $query = $wpdb->prepare(
                "SELECT 
                    m.id as meeting_id,
                    m.meeting_date,
                    m.meeting_time,
                    m.host_name,
                    m.address,
                    m.total_members,
                    m.present_members,
                    m.visitors_count,
                    m.attendance_rate,
                    c.id as cell_id,
                    c.name as cell_name,
                    c.leader_id,
                    COUNT(DISTINCT CASE WHEN a.is_present = 1 THEN a.member_id END) as total_present,
                    COUNT(DISTINCT CASE WHEN a.is_present = 0 THEN a.member_id END) as total_absent,
                    COUNT(DISTINCT mem.id) as total_members_in_cell
                FROM {$meetings_table} m
                LEFT JOIN {$cells_table} c ON m.cell_id = c.id
                LEFT JOIN {$attendance_table} a ON m.id = a.meeting_id
                LEFT JOIN {$members_table} mem ON mem.cell_id = c.id AND mem.status = 'active'
                WHERE m.id = %d AND m.tenant_id = %d AND c.tenant_id = %d
                GROUP BY m.id",
                $meeting_id,
                $current_tenant_id,
                $current_tenant_id
            );
        } else {
            $query = $wpdb->prepare(
                "SELECT 
                    m.id as meeting_id,
                    m.meeting_date,
                    m.meeting_time,
                    m.host_name,
                    m.address,
                    m.total_members,
                    m.present_members,
                    m.visitors_count,
                    m.attendance_rate,
                    c.id as cell_id,
                    c.name as cell_name,
                    c.leader_id,
                    COUNT(DISTINCT CASE WHEN a.is_present = 1 THEN a.member_id END) as total_present,
                    COUNT(DISTINCT CASE WHEN a.is_present = 0 THEN a.member_id END) as total_absent,
                    COUNT(DISTINCT mem.id) as total_members_in_cell
                FROM {$meetings_table} m
                LEFT JOIN {$cells_table} c ON m.cell_id = c.id
                LEFT JOIN {$attendance_table} a ON m.id = a.meeting_id
                LEFT JOIN {$members_table} mem ON mem.cell_id = c.id AND mem.status = 'active'
                WHERE m.id = %d
                GROUP BY m.id",
                $meeting_id
            );
        }
        
        $report_data = $wpdb->get_row($query);
        
        if (!$report_data) {
            return null;
        }
        
        // Busca lista de membros com presença
        $report_data->members = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                mem.id,
                mem.name,
                mem.email,
                mem.phone,
                mem.role_level,
                COALESCE(a.is_present, 0) as is_present,
                a.arrival_time
            FROM {$members_table} mem
            LEFT JOIN {$attendance_table} a ON mem.id = a.member_id AND a.meeting_id = %d
            WHERE mem.cell_id = %d AND mem.status = 'active'
            ORDER BY mem.name ASC",
            $meeting_id,
            $report_data->cell_id
        ));
        
        return $report_data;
    }
    
    /**
     * Estatísticas gerais do sistema
     * 
     * @param array $filters Filtros opcionais
     * @return array Estatísticas
     */
    public static function get_statistics($filters = array()) {
        global $wpdb;
        $members_table = self::get_table_name('members');
        $cells_table = self::get_table_name('cells');
        $meetings_table = self::get_table_name('meetings');
        $attendance_table = self::get_table_name('attendance');
        
        // Query única para todas as estatísticas
        $cell_filter = '';
        if (!empty($filters['cell_id'])) {
            $cell_filter = $wpdb->prepare(' AND cell_id = %d', $filters['cell_id']);
        }
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        $tenant_filter = '';
        if ($current_tenant_id) {
            $tenant_filter = $wpdb->prepare(' AND tenant_id = %d', $current_tenant_id);
        }
        
        $query = "SELECT 
            (SELECT COUNT(*) FROM {$members_table} WHERE status = 'active'{$cell_filter}{$tenant_filter}) as total_active_members,
            (SELECT COUNT(*) FROM {$members_table} WHERE status = 'active' AND is_visitor = 0{$cell_filter}{$tenant_filter}) as total_members,
            (SELECT COUNT(*) FROM {$members_table} WHERE status = 'active' AND is_visitor = 1{$cell_filter}{$tenant_filter}) as total_visitors,
            (SELECT COUNT(*) FROM {$cells_table} WHERE status = 'active'{$tenant_filter}) as total_active_cells,
            (SELECT COUNT(*) FROM {$meetings_table} WHERE status = 'completed' AND meeting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY){$tenant_filter}) as meetings_last_month,
            (SELECT AVG(attendance_rate) FROM {$meetings_table} WHERE status = 'completed' AND meeting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY){$tenant_filter}) as avg_attendance_rate";
        
        return $wpdb->get_row($query);
    }
    
    /**
     * Relatório de presença por período
     * 
     * @param string $date_from Data inicial (Y-m-d)
     * @param string $date_to Data final (Y-m-d)
     * @param int|null $cell_id ID da célula (opcional)
     * @return array Dados de presença
     */
    public static function get_attendance_report($date_from, $date_to, $cell_id = null, $cell_ids = null) {
        global $wpdb;
        $meetings_table = self::get_table_name('meetings');
        $attendance_table = self::get_table_name('attendance');
        $members_table = self::get_table_name('members');
        $cells_table = self::get_table_name('cells');
        
        $where = array("m.meeting_date BETWEEN %s AND %s");
        $prepare_values = array($date_from, $date_to);
        
        // Suporta múltiplos cell_ids (array) ou cell_id único
        $has_cell_filter = false;
        if (!empty($cell_ids) && is_array($cell_ids)) {
            $cell_ids = array_map('intval', $cell_ids);
            $cell_ids = array_filter($cell_ids);
            if (!empty($cell_ids)) {
                // Para múltiplos valores, usa IN com escapa manual (já sanitizado com intval)
                $cell_ids_string = implode(',', $cell_ids);
                $where[] = "m.cell_id IN ({$cell_ids_string})";
                $has_cell_filter = true;
            }
        } elseif ($cell_id) {
            $where[] = "m.cell_id = %d";
            $prepare_values[] = intval($cell_id);
        }
        
        $where[] = "m.status = 'completed'";
        
        // FASE 4: Adiciona filtro de tenant (isolamento de dados)
        $current_tenant_id = self::get_current_tenant_id();
        if ($current_tenant_id) {
            $where[] = "m.tenant_id = %d";
            $prepare_values[] = $current_tenant_id;
        }
        
        $where_clause = implode(' AND ', $where);
        
        // Prepara a query base
        $query = "SELECT 
            m.id as meeting_id,
            m.meeting_date,
            c.name as cell_name,
            c.id as cell_id,
            COUNT(DISTINCT CASE WHEN a.is_present = 1 THEN a.member_id END) as present_count,
            COUNT(DISTINCT CASE WHEN a.is_present = 0 THEN a.member_id END) as absent_count,
            COUNT(DISTINCT mem.id) as total_members,
            ROUND(COUNT(DISTINCT CASE WHEN a.is_present = 1 THEN a.member_id END) * 100.0 / NULLIF(COUNT(DISTINCT mem.id), 0), 2) as attendance_rate
        FROM {$meetings_table} m
        LEFT JOIN {$cells_table} c ON m.cell_id = c.id
        LEFT JOIN {$attendance_table} a ON m.id = a.meeting_id
        LEFT JOIN {$members_table} mem ON mem.cell_id = c.id AND mem.status = 'active'
        WHERE {$where_clause}
        GROUP BY m.id
        ORDER BY m.meeting_date DESC";
        
        // Prepara a query: se usou IN, já está seguro (intval), senão usa prepare normalmente
        if ($has_cell_filter) {
            // cell_ids já estão sanitizados com intval, prepara apenas as datas
            $query = $wpdb->prepare($query, $date_from, $date_to);
        } else {
            // Prepara todos os valores
            $query = $wpdb->prepare($query, $prepare_values);
        }
        
        return $wpdb->get_results($query);
    }
    
    // ============================================================
    // SISTEMA DE SINCRONIZAÇÃO CPT ↔ TABELAS
    // ============================================================
    
    /**
     * Sincroniza membro do CPT para tabela
     * 
     * Versão 2.0 - Com conversão automática de IDs e mapeamento persistente
     * 
     * @param int $post_id ID do post (CPT)
     * @return int|false ID do membro na tabela ou false
     */
    public static function sync_member_from_cpt($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'haklai_member') {
            return false;
        }
        
        // 1. Busca mapeamento existente (se houver)
        $existing_table_id = get_post_meta($post_id, '_haklai_table_id', true);
        
        // 2. Extrai dados do CPT
        $cell_cpt_id = get_post_meta($post_id, '_haklai_cell_id', true);
        $leader_cpt_id = get_post_meta($post_id, '_haklai_leader_id', true);
        $network_cpt_id = get_post_meta($post_id, '_haklai_network_id', true);
        
        // 3. Converte IDs de relacionamento (CPT → Tabela MySQL)
        $cell_table_id = null;
        if ($cell_cpt_id) {
            // Tenta converter, criando mapeamento se necessário
            $cell_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $cell_cpt_id,
                'haklai_cell',
                true // create_if_missing = true (sincroniza célula se não existir)
            );
        }
        
        $leader_table_id = null;
        if ($leader_cpt_id) {
            $leader_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $leader_cpt_id,
                'haklai_member',
                true
            );
        }
        
        $network_table_id = null;
        if ($network_cpt_id) {
            $network_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $network_cpt_id,
                'haklai_network',
                true
            );
        }
        
        // 4. Prepara dados com IDs convertidos
        $data = array(
            'name' => $post->post_title,
            'email' => get_post_meta($post_id, '_haklai_email', true),
            'phone' => get_post_meta($post_id, '_haklai_phone', true),
            'cell_id' => $cell_table_id, // ID de tabela MySQL, não CPT!
            'leader_id' => $leader_table_id, // ID de tabela MySQL
            'network_id' => $network_table_id, // ID de tabela MySQL
            'role_level' => intval(get_post_meta($post_id, '_haklai_role_level', true)),
            'baptism_status' => get_post_meta($post_id, '_haklai_baptism_status', true) ?: 'visitor',
            'baptism_date' => get_post_meta($post_id, '_haklai_baptism_date', true) ?: null,
            'is_visitor' => get_post_meta($post_id, '_haklai_is_visitor', true) === '1' ? 1 : 0,
            'status' => $post->post_status === 'publish' ? 'active' : 'inactive',
            'photo_url' => get_post_meta($post_id, '_haklai_photo', true),
            'skill' => get_post_meta($post_id, '_haklai_skill', true),
            'encontro_deus' => get_post_meta($post_id, '_haklai_encontro_deus', true) ?: 'nao',
            'formacoes' => json_encode(get_post_meta($post_id, '_haklai_formacoes', true) ?: array()),
        );
        
        // FASE 4: Obtém tenant_id do CPT ou do contexto atual
        $cpt_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
        if ($cpt_tenant_id && intval($cpt_tenant_id) > 0) {
            $data['tenant_id'] = intval($cpt_tenant_id);
        } else {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        // 5. Busca ID existente (por mapeamento ou por nome como fallback)
        global $wpdb;
        $table_name = self::get_table_name('members');
        
        // Primeiro tenta usar o mapeamento existente
        if ($existing_table_id) {
            // Valida que ainda existe
            $valid = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE id = %d LIMIT 1",
                $existing_table_id
            ));
            
            if ($valid) {
                $existing_id = $existing_table_id;
            } else {
                // Mapeamento inválido, busca por nome
                $existing_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE wp_user_id IS NULL AND name = %s LIMIT 1",
                    $data['name']
                ));
            }
        } else {
            // Sem mapeamento, busca por nome
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE wp_user_id IS NULL AND name = %s LIMIT 1",
                $data['name']
            ));
        }
        
        // 6. Salva na tabela MySQL
        $table_id = self::save_member($data, $existing_id);
        
        // 7. Atualiza mapeamento (crítico para próximas buscas rápidas)
        if ($table_id) {
            Haklai_Sync_Manager::set_table_id_mapping($post_id, $table_id);
        }
        
        return $table_id;
    }
    
    /**
     * Sincroniza todos os membros dos CPTs para tabelas
     * 
     * @return array Resultado da sincronização
     */
    public static function sync_all_members_from_cpt() {
        $members = get_posts(array(
            'post_type' => 'haklai_member',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ));
        
        $synced = 0;
        $errors = 0;
        
        foreach ($members as $member) {
            $result = self::sync_member_from_cpt($member->ID);
            if ($result) {
                $synced++;
            } else {
                $errors++;
            }
        }
        
        return array(
            'synced' => $synced,
            'errors' => $errors,
            'total' => count($members),
        );
    }
    
    /**
     * Sincroniza célula do CPT para tabela
     * 
     * Versão 2.0 - Com conversão automática de IDs e mapeamento persistente
     * 
     * @param int $post_id ID do post (CPT)
     * @return int|false ID da célula na tabela ou false
     */
    public static function sync_cell_from_cpt($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'haklai_cell') {
            return false;
        }
        
        // 1. Busca mapeamento existente (se houver)
        $existing_table_id = get_post_meta($post_id, '_haklai_table_id', true);
        
        // 2. Extrai dados do CPT
        $leader_cpt_id = get_post_meta($post_id, '_haklai_leader_id', true);
        $discipler_cpt_id = get_post_meta($post_id, '_haklai_discipulador_id', true);
        $network_cpt_id = get_post_meta($post_id, '_haklai_network_id', true);
        
        // 3. Converte IDs de relacionamento (CPT → Tabela MySQL)
        $leader_table_id = null;
        if ($leader_cpt_id) {
            $leader_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $leader_cpt_id,
                'haklai_member',
                true // create_if_missing = true
            );
        }
        
        $discipler_table_id = null;
        if ($discipler_cpt_id) {
            $discipler_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $discipler_cpt_id,
                'haklai_member',
                true
            );
        }
        
        $network_table_id = null;
        if ($network_cpt_id) {
            $network_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $network_cpt_id,
                'haklai_network',
                true
            );
        }
        
        // 4. Prepara dados com IDs convertidos
        $data = array(
            'name' => $post->post_title,
            'description' => $post->post_content,
            'address' => get_post_meta($post_id, '_haklai_address', true),
            'neighborhood' => get_post_meta($post_id, '_haklai_neighborhood', true),
            'city' => get_post_meta($post_id, '_haklai_city', true),
            'state' => get_post_meta($post_id, '_haklai_state', true),
            'zip_code' => get_post_meta($post_id, '_haklai_zip_code', true),
            'leader_id' => $leader_table_id, // ID de tabela MySQL, não CPT!
            'discipler_id' => $discipler_table_id, // ID de tabela MySQL
            'network_id' => $network_table_id, // ID de tabela MySQL
            'status' => $post->post_status === 'publish' ? 'active' : 'inactive',
        );
        
        // FASE 4: Obtém tenant_id do CPT ou do contexto atual
        $cpt_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
        if ($cpt_tenant_id && intval($cpt_tenant_id) > 0) {
            $data['tenant_id'] = intval($cpt_tenant_id);
        } else {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        // 5. Busca ID existente (por mapeamento ou por nome como fallback)
        global $wpdb;
        $table_name = self::get_table_name('cells');
        
        // Primeiro tenta usar o mapeamento existente
        if ($existing_table_id) {
            // Valida que ainda existe
            $valid = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE id = %d LIMIT 1",
                $existing_table_id
            ));
            
            if ($valid) {
                $existing_id = $existing_table_id;
            } else {
                // Mapeamento inválido, busca por nome
                $existing_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                    $data['name']
                ));
            }
        } else {
            // Sem mapeamento, busca por nome
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                $data['name']
            ));
        }
        
        // 6. Salva na tabela MySQL
        $table_id = self::save_cell($data, $existing_id);
        
        // 7. Atualiza mapeamento (crítico para próximas buscas rápidas)
        if ($table_id) {
            Haklai_Sync_Manager::set_table_id_mapping($post_id, $table_id);
        }
        
        return $table_id;
    }
    
    /**
     * Sincroniza reunião do CPT para tabela
     * 
     * Versão 2.0 - Com conversão automática de IDs e mapeamento persistente
     * 
     * @param int $post_id ID do post (CPT)
     * @return int|false ID da reunião na tabela ou false
     */
    public static function sync_meeting_from_cpt($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'haklai_meeting') {
            return false;
        }
        
        // 1. Busca mapeamento existente (se houver)
        $existing_table_id = get_post_meta($post_id, '_haklai_table_id', true);
        
        // 2. Extrai e converte cell_id
        $cell_cpt_id = get_post_meta($post_id, '_haklai_cell_id', true);
        $cell_table_id = null;
        
        if ($cell_cpt_id) {
            $cell_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $cell_cpt_id,
                'haklai_cell',
                true // create_if_missing = true
            );
        }
        
        if (!$cell_table_id) {
            // Reunião sem célula não pode ser sincronizada
            return false;
        }
        
        $meeting_date = get_post_meta($post_id, '_haklai_meeting_date', true);
        if (!$meeting_date) {
            $meeting_date = date('Y-m-d', strtotime($post->post_date));
        }
        
        // 3. Prepara dados com cell_id convertido
        $data = array(
            'cell_id' => $cell_table_id, // ID de tabela MySQL, não CPT!
            'meeting_date' => $meeting_date,
            'meeting_time' => get_post_meta($post_id, '_haklai_meeting_time', true) ?: null,
            'host_name' => get_post_meta($post_id, '_haklai_host', true),
            'address' => get_post_meta($post_id, '_haklai_address', true),
            'status' => $post->post_status === 'publish' ? 'completed' : 'scheduled',
            'created_by' => get_post_meta($post_id, '_haklai_created_by', true) ?: get_current_user_id(),
        );
        
        // FASE 4: Obtém tenant_id do CPT ou do contexto atual
        $cpt_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
        if ($cpt_tenant_id && intval($cpt_tenant_id) > 0) {
            $data['tenant_id'] = intval($cpt_tenant_id);
        } else {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        // 4. Busca se já existe na tabela
        if (!empty($data['cell_id']) && !empty($data['meeting_date'])) {
            global $wpdb;
            $table_name = self::get_table_name('meetings');
            
            // Primeiro tenta usar mapeamento
            if ($existing_table_id) {
                $valid = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE id = %d LIMIT 1",
                    $existing_table_id
                ));
                
                if ($valid) {
                    $existing_id = $existing_table_id;
                } else {
                    // Busca por cell_id + data
                    $existing_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT id FROM {$table_name} WHERE cell_id = %d AND meeting_date = %s LIMIT 1",
                        $data['cell_id'],
                        $data['meeting_date']
                    ));
                }
            } else {
                // Busca por cell_id + data
                $existing_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE cell_id = %d AND meeting_date = %s LIMIT 1",
                    $data['cell_id'],
                    $data['meeting_date']
                ));
            }
            
            // 5. Salva na tabela
            $table_id = self::save_meeting($data, $existing_id);
            
            // 6. Atualiza mapeamento
            if ($table_id) {
                Haklai_Sync_Manager::set_table_id_mapping($post_id, $table_id);
            }
            
            return $table_id;
        }
        
        return false;
    }
    
    /**
     * Sincroniza presença do CPT para tabela
     * 
     * Versão 2.0 - Com conversão automática de IDs
     * 
     * @param int $post_id ID do post (CPT)
     * @return bool True se sincronizado com sucesso
     */
    public static function sync_attendance_from_cpt($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'haklai_attendance') {
            return false;
        }
        
        $cpt_meeting_id = get_post_meta($post_id, '_haklai_meeting_id', true);
        $cpt_member_id = get_post_meta($post_id, '_haklai_member_id', true);
        $is_present = get_post_meta($post_id, '_haklai_is_present', true) === '1';
        $arrival_time = get_post_meta($post_id, '_haklai_arrival_time', true);
        
        if (!$cpt_meeting_id || !$cpt_member_id) {
            return false;
        }
        
        // 1. Converte IDs de CPT para IDs de tabela MySQL
        $table_meeting_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
            $cpt_meeting_id,
            'haklai_meeting',
            true // create_if_missing = true (sincroniza reunião se não existir)
        );
        
        $table_member_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
            $cpt_member_id,
            'haklai_member',
            true // create_if_missing = true
        );
        
        if (!$table_meeting_id || !$table_member_id) {
            return false;
        }
        
        // 2. Salva/atualiza presença na tabela MySQL
        return self::save_attendance_sync($table_meeting_id, $table_member_id, $is_present, $arrival_time);
    }
    
    /**
     * Salva/atualiza presença na tabela MySQL (versão interna para sync)
     * 
     * @param int $meeting_id ID da reunião na tabela MySQL
     * @param int $member_id ID do membro na tabela MySQL
     * @param bool $is_present Se está presente
     * @param string|null $arrival_time Hora de chegada (opcional)
     * @return bool True se salvo com sucesso
     */
    private static function save_attendance_sync($meeting_id, $member_id, $is_present = true, $arrival_time = null) {
        global $wpdb;
        $table_name = self::get_table_name('attendance');
        
        // Verifica se já existe
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table_name} WHERE meeting_id = %d AND member_id = %d",
            $meeting_id,
            $member_id
        ));
        
        $data = array(
            'meeting_id' => $meeting_id,
            'member_id' => $member_id,
            'is_present' => $is_present ? 1 : 0,
            'arrival_time' => $arrival_time,
            'updated_at' => current_time('mysql'),
        );
        
        if ($existing) {
            // Atualiza existente
            return $wpdb->update($table_name, $data, array('id' => $existing), null, array('%d')) !== false;
        } else {
            // Cria novo
            $data['created_at'] = current_time('mysql');
            return $wpdb->insert($table_name, $data) !== false;
        }
    }
    
    /**
     * Sincroniza rede do CPT para tabela
     * 
     * Versão 2.0 - Com conversão automática de IDs e mapeamento persistente
     * 
     * @param int $post_id ID do post (CPT)
     * @return int|false ID da rede na tabela ou false
     */
    public static function sync_network_from_cpt($post_id) {
        $post = get_post($post_id);
        
        if (!$post || $post->post_type !== 'haklai_network') {
            return false;
        }
        
        // 1. Busca mapeamento existente (se houver)
        $existing_table_id = get_post_meta($post_id, '_haklai_table_id', true);
        
        // 2. Extrai e converte pastor_id
        $pastor_cpt_id = get_post_meta($post_id, '_haklai_pastor_id', true);
        $pastor_table_id = null;
        
        if ($pastor_cpt_id) {
            $pastor_table_id = Haklai_Sync_Manager::convert_cpt_id_to_table_id(
                $pastor_cpt_id,
                'haklai_member',
                true // create_if_missing = true
            );
        }
        
        // 3. Prepara dados
        $data = array(
            'name' => $post->post_title,
            'description' => $post->post_content,
            'pastor_id' => $pastor_table_id, // ID de tabela MySQL, não CPT!
            'status' => $post->post_status === 'publish' ? 'active' : 'inactive',
        );
        
        // FASE 4: Obtém tenant_id do CPT ou do contexto atual
        $cpt_tenant_id = get_post_meta($post_id, '_haklai_tenant_id', true);
        if ($cpt_tenant_id && intval($cpt_tenant_id) > 0) {
            $data['tenant_id'] = intval($cpt_tenant_id);
        } else {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        // 4. Busca ID existente
        global $wpdb;
        $table_name = self::get_table_name('networks');
        
        // Primeiro tenta usar mapeamento
        if ($existing_table_id) {
            $valid = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE id = %d LIMIT 1",
                $existing_table_id
            ));
            
            if ($valid) {
                $existing_id = $existing_table_id;
            } else {
                // Busca por nome
                $existing_id = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                    $data['name']
                ));
            }
        } else {
            // Busca por nome
            $existing_id = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$table_name} WHERE name = %s LIMIT 1",
                $data['name']
            ));
        }
        
        // 5. Salva na tabela
        $table_id = self::save_network($data, $existing_id);
        
        // 6. Atualiza mapeamento
        if ($table_id) {
            Haklai_Sync_Manager::set_table_id_mapping($post_id, $table_id);
        }
        
        return $table_id;
    }
    
    /**
     * Salva rede na tabela MySQL
     * 
     * @param array $data Dados da rede
     * @param int|null $network_id ID existente (se atualização)
     * @return int|false ID da rede ou false
     */
    private static function save_network($data, $network_id = null) {
        global $wpdb;
        $table_name = self::get_table_name('networks');
        
        $data['updated_at'] = current_time('mysql');
        
        // FASE 4: Garante tenant_id
        if (!isset($data['tenant_id']) || empty($data['tenant_id'])) {
            $data['tenant_id'] = self::get_current_tenant_id();
        }
        
        if ($network_id) {
            return $wpdb->update($table_name, $data, array('id' => $network_id), null, array('%d')) !== false ? $network_id : false;
        } else {
            $data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $data);
            return $result ? $wpdb->insert_id : false;
        }
    }
    
    /**
     * Migra TODOS os dados dos CPTs para as tabelas otimizadas
     * Executa migração completa do sistema
     * 
     * @return array Resultado detalhado da migração
     */
    public static function migrate_all_data_from_cpt() {
        $results = array(
            'members' => array('synced' => 0, 'errors' => 0, 'total' => 0),
            'cells' => array('synced' => 0, 'errors' => 0, 'total' => 0),
            'meetings' => array('synced' => 0, 'errors' => 0, 'total' => 0),
            'attendance' => array('synced' => 0, 'errors' => 0, 'total' => 0),
            'success' => true,
        );
        
        // Verifica se as tabelas existem
        if (!self::tables_exist()) {
            return array(
                'success' => false,
                'message' => 'Tabelas não existem. Execute a ativação do plugin primeiro.',
                'results' => $results
            );
        }
        
        // 1. Migra CÉLULAS primeiro (membros dependem de células)
        $cells = get_posts(array(
            'post_type' => 'haklai_cell',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ));
        
        $results['cells']['total'] = count($cells);
        $cell_id_mapping = array(); // Mapeia ID do CPT para ID da tabela
        
        foreach ($cells as $cell) {
            $table_id = self::sync_cell_from_cpt($cell->ID);
            if ($table_id) {
                $results['cells']['synced']++;
                $cell_id_mapping[$cell->ID] = $table_id;
            } else {
                $results['cells']['errors']++;
            }
        }
        
        // 2. Migra MEMBROS (após células, pois dependem de cell_id)
        $members = get_posts(array(
            'post_type' => 'haklai_member',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ));
        
        $results['members']['total'] = count($members);
        $member_id_mapping = array(); // Mapeia ID do CPT para ID da tabela
        
        foreach ($members as $member) {
            $table_id = self::sync_member_from_cpt($member->ID);
            if ($table_id) {
                $results['members']['synced']++;
                $member_id_mapping[$member->ID] = $table_id;
                
                // Atualiza cell_id se necessário (converte ID do CPT para ID da tabela)
                $cpt_cell_id = get_post_meta($member->ID, '_haklai_cell_id', true);
                if ($cpt_cell_id && isset($cell_id_mapping[$cpt_cell_id])) {
                    global $wpdb;
                    $table_name = self::get_table_name('members');
                    $wpdb->update(
                        $table_name,
                        array('cell_id' => $cell_id_mapping[$cpt_cell_id]),
                        array('id' => $table_id),
                        null,
                        array('%d')
                    );
                }
            } else {
                $results['members']['errors']++;
            }
        }
        
        // 3. Migra REUNIÕES (após células)
        $meetings = get_posts(array(
            'post_type' => 'haklai_meeting',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ));
        
        $results['meetings']['total'] = count($meetings);
        $meeting_id_mapping = array(); // Mapeia ID do CPT para ID da tabela
        
        foreach ($meetings as $meeting) {
            $table_id = self::sync_meeting_from_cpt($meeting->ID);
            if ($table_id) {
                $results['meetings']['synced']++;
                $meeting_id_mapping[$meeting->ID] = $table_id;
                
                // Atualiza cell_id se necessário
                $cpt_cell_id = get_post_meta($meeting->ID, '_haklai_cell_id', true);
                if ($cpt_cell_id && isset($cell_id_mapping[$cpt_cell_id])) {
                    global $wpdb;
                    $table_name = self::get_table_name('meetings');
                    $wpdb->update(
                        $table_name,
                        array('cell_id' => $cell_id_mapping[$cpt_cell_id]),
                        array('id' => $table_id),
                        null,
                        array('%d')
                    );
                }
            } else {
                $results['meetings']['errors']++;
            }
        }
        
        // 4. Migra PRESENÇAS (após reuniões e membros)
        $attendances = get_posts(array(
            'post_type' => 'haklai_attendance',
            'post_status' => 'any',
            'posts_per_page' => -1,
        ));
        
        $results['attendance']['total'] = count($attendances);
        
        foreach ($attendances as $attendance) {
            $cpt_meeting_id = get_post_meta($attendance->ID, '_haklai_meeting_id', true);
            $cpt_member_id = get_post_meta($attendance->ID, '_haklai_member_id', true);
            $is_present = get_post_meta($attendance->ID, '_haklai_is_present', true) === '1';
            
            // Converte IDs do CPT para IDs da tabela
            $table_meeting_id = isset($meeting_id_mapping[$cpt_meeting_id]) ? $meeting_id_mapping[$cpt_meeting_id] : $cpt_meeting_id;
            $table_member_id = isset($member_id_mapping[$cpt_member_id]) ? $member_id_mapping[$cpt_member_id] : $cpt_member_id;
            
            if ($table_meeting_id && $table_member_id) {
                $result = self::save_attendance($table_meeting_id, $table_member_id, $is_present);
                if ($result) {
                    $results['attendance']['synced']++;
                } else {
                    $results['attendance']['errors']++;
                }
            } else {
                $results['attendance']['errors']++;
            }
        }
        
        // Verifica se houve erros significativos
        $total_errors = $results['members']['errors'] + $results['cells']['errors'] + 
                       $results['meetings']['errors'] + $results['attendance']['errors'];
        
        $results['success'] = $total_errors < ($results['members']['total'] + $results['cells']['total'] + 
                                              $results['meetings']['total'] + $results['attendance']['total']) * 0.1; // Menos de 10% de erro
        
        return $results;
    }
    
    /**
     * Verifica se há dados nos CPTs que precisam ser migrados
     * 
     * @return array Informações sobre dados existentes
     */
    public static function check_cpt_data() {
        $data = array(
            'has_data' => false,
            'members_count' => 0,
            'cells_count' => 0,
            'meetings_count' => 0,
            'attendance_count' => 0,
        );
        
        $members = get_posts(array(
            'post_type' => 'haklai_member',
            'post_status' => 'any',
            'posts_per_page' => 1,
        ));
        
        $cells = get_posts(array(
            'post_type' => 'haklai_cell',
            'post_status' => 'any',
            'posts_per_page' => 1,
        ));
        
        $meetings = get_posts(array(
            'post_type' => 'haklai_meeting',
            'post_status' => 'any',
            'posts_per_page' => 1,
        ));
        
        $data['members_count'] = wp_count_posts('haklai_member')->publish + wp_count_posts('haklai_member')->draft;
        $data['cells_count'] = wp_count_posts('haklai_cell')->publish + wp_count_posts('haklai_cell')->draft;
        $data['meetings_count'] = wp_count_posts('haklai_meeting')->publish + wp_count_posts('haklai_meeting')->draft;
        
        $attendances = get_posts(array(
            'post_type' => 'haklai_attendance',
            'post_status' => 'any',
            'posts_per_page' => 1,
        ));
        $data['attendance_count'] = wp_count_posts('haklai_attendance')->publish + wp_count_posts('haklai_attendance')->draft;
        
        $data['has_data'] = $data['members_count'] > 0 || $data['cells_count'] > 0 || 
                           $data['meetings_count'] > 0 || $data['attendance_count'] > 0;
        
        return $data;
    }
    
    /**
     * Verifica se as tabelas estão vazias
     * 
     * @return bool True se todas as tabelas estão vazias
     */
    public static function tables_are_empty() {
        global $wpdb;
        $tables = self::get_table_names();
        
        foreach ($tables as $key => $table_name) {
            if (in_array($key, array('members', 'cells', 'meetings', 'attendance'))) {
                $count = $wpdb->get_var("SELECT COUNT(*) FROM {$table_name}");
                if ($count > 0) {
                    return false;
                }
            }
        }
        
        return true;
    }
}
