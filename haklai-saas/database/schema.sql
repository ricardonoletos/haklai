-- =================================================================
-- HAKLAI SAAS - CHURCH MANAGEMENT SYSTEM
-- Database Schema - MySQL 5.7+
-- Multi-tenant Architecture with Complete Hierarchy
-- Projeto: Haklai SaaS - Desenvolvido por: Jefter Ruthes
-- =================================================================

-- Configurações iniciais
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- =================================================================
-- TABELA: tenants (Organizações/Igrejas)
-- Descrição: Sistema multi-tenant para múltiplas igrejas
-- =================================================================
CREATE TABLE IF NOT EXISTS `tenants` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL COMMENT 'Nome da organização/igreja',
  `slug` VARCHAR(100) NOT NULL UNIQUE COMMENT 'URL amigável',
  `email` VARCHAR(255) NOT NULL COMMENT 'Email principal',
  `phone` VARCHAR(20) NULL COMMENT 'Telefone de contato',
  `logo_url` VARCHAR(500) NULL COMMENT 'URL do logo',
  `address` TEXT NULL COMMENT 'Endereço completo',
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(50) NULL,
  `zip_code` VARCHAR(10) NULL,
  `country` VARCHAR(50) DEFAULT 'Brasil',
  `subscription_status` ENUM('trial', 'active', 'suspended', 'cancelled') DEFAULT 'trial',
  `subscription_plan` VARCHAR(50) DEFAULT 'free' COMMENT 'free, basic, premium, enterprise',
  `max_members` INT DEFAULT 100 COMMENT 'Limite de membros por plano',
  `trial_ends_at` DATETIME NULL COMMENT 'Fim do período de trial',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_slug` (`slug`),
  KEY `idx_subscription` (`subscription_status`),
  KEY `idx_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Organizações/Igrejas (Multi-tenant)';

-- =================================================================
-- TABELA: users (Usuários do sistema)
-- Descrição: Autenticação e acesso ao sistema
-- =================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL COMMENT 'Organização vinculada',
  `member_id` BIGINT UNSIGNED NULL COMMENT 'Referência ao membro (se aplicável)',
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL COMMENT 'Bcrypt hash',
  `full_name` VARCHAR(255) NOT NULL,
  `role_level` TINYINT NOT NULL DEFAULT 0 COMMENT '0=Membro, 1=Líder, 2=Discipulador, 3=Pastor Rede, 4=Pastor Senior, 5=Supervisor, 99=Super Admin',
  `is_active` TINYINT(1) DEFAULT 1,
  `email_verified` TINYINT(1) DEFAULT 0,
  `email_verification_token` VARCHAR(64) NULL,
  `email_verified_at` DATETIME NULL,
  `password_reset_token` VARCHAR(64) NULL,
  `password_reset_expires` DATETIME NULL,
  `two_factor_secret` VARCHAR(255) NULL COMMENT 'Secret para 2FA',
  `two_factor_enabled` TINYINT(1) DEFAULT 0,
  `two_factor_code` VARCHAR(6) NULL COMMENT 'Código 2FA via email',
  `two_factor_expires` DATETIME NULL,
  `last_login_at` DATETIME NULL,
  `last_login_ip` VARCHAR(45) NULL,
  `login_attempts` INT DEFAULT 0,
  `locked_until` DATETIME NULL COMMENT 'Account lock após múltiplas tentativas',
  `remember_token` VARCHAR(100) NULL COMMENT 'Token para "lembrar-me"',
  `session_token` VARCHAR(255) NULL COMMENT 'Token de sessão atual',
  `session_expires_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_email` (`email`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_role` (`role_level`),
  KEY `idx_active` (`is_active`),
  KEY `idx_member` (`member_id`),
  CONSTRAINT `fk_users_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários do sistema';

-- =================================================================
-- TABELA: networks (Redes de células)
-- Descrição: Agrupamento de células por região/pastor
-- =================================================================
CREATE TABLE IF NOT EXISTS `networks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor de Rede (role_level 3)',
  `senior_pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor Senior (role_level 4)',
  `supervisor_pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor Supervisor (role_level 5)',
  `total_cells` INT DEFAULT 0,
  `active_cells` INT DEFAULT 0,
  `total_members` INT DEFAULT 0,
  `active_members` INT DEFAULT 0,
  `avg_attendance` DECIMAL(5,2) DEFAULT 0.00,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_pastor` (`pastor_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_networks_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Redes de células';

-- =================================================================
-- TABELA: cells (Células/Grupos pequenos)
-- Descrição: Grupos pequenos de comunhão
-- =================================================================
CREATE TABLE IF NOT EXISTS `cells` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `network_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `leader_id` BIGINT UNSIGNED NULL COMMENT 'Líder da célula (role_level 1)',
  `discipler_id` BIGINT UNSIGNED NULL COMMENT 'Discipulador (role_level 2)',
  `address` TEXT NULL COMMENT 'Endereço das reuniões',
  `neighborhood` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(50) NULL,
  `zip_code` VARCHAR(10) NULL,
  `latitude` DECIMAL(10, 8) NULL COMMENT 'Para Google Maps',
  `longitude` DECIMAL(11, 8) NULL COMMENT 'Para Google Maps',
  `meeting_day` ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday') NULL,
  `meeting_time` TIME NULL,
  `meeting_frequency` ENUM('weekly', 'biweekly', 'monthly') DEFAULT 'weekly',
  `total_members` INT DEFAULT 0,
  `active_members` INT DEFAULT 0,
  `visitors_count` INT DEFAULT 0,
  `avg_attendance` DECIMAL(5,2) DEFAULT 0.00,
  `last_meeting_date` DATE NULL,
  `status` ENUM('active', 'inactive', 'closed') DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_network` (`network_id`),
  KEY `idx_leader` (`leader_id`),
  KEY `idx_discipler` (`discipler_id`),
  KEY `idx_city` (`city`),
  KEY `idx_status` (`status`),
  KEY `idx_coords` (`latitude`, `longitude`),
  CONSTRAINT `fk_cells_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cells_network` FOREIGN KEY (`network_id`) REFERENCES `networks` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Células/Grupos pequenos';

-- =================================================================
-- TABELA: members (Membros da igreja)
-- Descrição: Cadastro completo de membros
-- =================================================================
CREATE TABLE IF NOT EXISTS `members` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL COMMENT 'Vínculo com usuário (se tiver acesso)',
  `cell_id` BIGINT UNSIGNED NULL,
  `network_id` BIGINT UNSIGNED NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(20) NULL,
  `birth_date` DATE NULL,
  `gender` ENUM('M', 'F', 'O') NULL,
  `photo_url` VARCHAR(500) NULL,
  `address` TEXT NULL,
  `city` VARCHAR(100) NULL,
  `state` VARCHAR(50) NULL,
  `zip_code` VARCHAR(10) NULL,
  `role_level` TINYINT DEFAULT 0 COMMENT 'Nível na hierarquia',
  `leader_id` BIGINT UNSIGNED NULL COMMENT 'Líder direto',
  `baptism_status` ENUM('visitor', 'frequent_visitor', 'baptized', 'member') DEFAULT 'visitor',
  `baptism_date` DATE NULL,
  `is_visitor` TINYINT(1) DEFAULT 0,
  `is_frequent_visitor` TINYINT(1) DEFAULT 0 COMMENT 'Frequentador Assíduo',
  `visitor_since` DATE NULL COMMENT 'Data da primeira visita',
  `visitor_count` INT DEFAULT 0 COMMENT 'Número de presenças como visitante',
  `encontro_deus` ENUM('sim', 'nao') DEFAULT 'nao',
  `encontro_deus_date` DATE NULL,
  `skill` VARCHAR(100) NULL COMMENT 'Habilidade/Dom ministerial',
  `status` ENUM('active', 'inactive', 'transferred', 'suspended', 'deceased') DEFAULT 'active',
  `last_attendance_date` DATE NULL,
  `total_attendances` INT DEFAULT 0,
  `consecutive_absences` INT DEFAULT 0,
  `notes` TEXT NULL COMMENT 'Observações gerais',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_cell` (`cell_id`),
  KEY `idx_network` (`network_id`),
  KEY `idx_name` (`name`),
  KEY `idx_email` (`email`),
  KEY `idx_baptism` (`baptism_status`),
  KEY `idx_visitor` (`is_visitor`),
  KEY `idx_fa` (`is_frequent_visitor`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_members_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_members_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_members_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Membros da igreja';

-- =================================================================
-- TABELA: formations (Formações/Cursos)
-- Descrição: Cursos de formação disponíveis
-- =================================================================
CREATE TABLE IF NOT EXISTS `formations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `code` VARCHAR(10) NOT NULL COMMENT 'CTL, CME, STP',
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT NULL,
  `duration_weeks` INT NULL COMMENT 'Duração em semanas',
  `is_active` TINYINT(1) DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_code` (`tenant_id`, `code`),
  CONSTRAINT `fk_formations_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formações/Cursos disponíveis';

-- =================================================================
-- TABELA: member_formations (Formações dos membros)
-- Descrição: Relacionamento membros x formações
-- =================================================================
CREATE TABLE IF NOT EXISTS `member_formations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `member_id` BIGINT UNSIGNED NOT NULL,
  `formation_id` BIGINT UNSIGNED NOT NULL,
  `completion_date` DATE NULL,
  `certificate_url` VARCHAR(500) NULL,
  `status` ENUM('in_progress', 'completed', 'cancelled') DEFAULT 'completed',
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_member_formation` (`member_id`, `formation_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_formation` (`formation_id`),
  CONSTRAINT `fk_mf_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_mf_formation` FOREIGN KEY (`formation_id`) REFERENCES `formations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Formações dos membros';

-- =================================================================
-- TABELA: meetings (Reuniões de célula)
-- Descrição: Registro de reuniões com hierarquia completa
-- =================================================================
CREATE TABLE IF NOT EXISTS `meetings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `cell_id` BIGINT UNSIGNED NOT NULL,
  `network_id` BIGINT UNSIGNED NULL COMMENT 'Rede da célula',
  `leader_id` BIGINT UNSIGNED NULL COMMENT 'Líder da célula',
  `discipler_id` BIGINT UNSIGNED NULL COMMENT 'Discipulador',
  `pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor de Rede',
  `senior_pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor Senior',
  `supervisor_pastor_id` BIGINT UNSIGNED NULL COMMENT 'Pastor Supervisor',
  `meeting_date` DATE NOT NULL,
  `meeting_time` TIME NULL,
  `host_name` VARCHAR(255) NULL COMMENT 'Anfitrião da reunião',
  `host_phone` VARCHAR(20) NULL,
  `address` TEXT NULL COMMENT 'Local específico da reunião',
  `neighborhood` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `is_online` TINYINT(1) DEFAULT 0,
  `online_link` VARCHAR(500) NULL,
  `lesson_title` VARCHAR(255) NULL,
  `lesson_scripture` VARCHAR(255) NULL,
  `lesson_notes` TEXT NULL,
  `prayer_requests` TEXT NULL,
  `announcements` TEXT NULL,
  `total_members` INT DEFAULT 0 COMMENT 'Total de membros da célula',
  `present_members` INT DEFAULT 0,
  `absent_members` INT DEFAULT 0,
  `visitors_count` INT DEFAULT 0,
  `attendance_rate` DECIMAL(5,2) DEFAULT 0.00,
  `status` ENUM('scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'scheduled',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_by` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_cell_date` (`cell_id`, `meeting_date`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_cell` (`cell_id`),
  KEY `idx_network` (`network_id`),
  KEY `idx_date` (`meeting_date`),
  KEY `idx_status` (`status`),
  KEY `idx_leader` (`leader_id`),
  CONSTRAINT `fk_meetings_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_meetings_cell` FOREIGN KEY (`cell_id`) REFERENCES `cells` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reuniões de célula';

-- =================================================================
-- TABELA: attendance (Presenças)
-- Descrição: Registro de presença/ausência em reuniões
-- =================================================================
CREATE TABLE IF NOT EXISTS `attendance` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `meeting_id` BIGINT UNSIGNED NOT NULL,
  `member_id` BIGINT UNSIGNED NOT NULL,
  `is_present` TINYINT(1) DEFAULT 0,
  `is_visitor` TINYINT(1) DEFAULT 0 COMMENT 'Se é visita ou F.A.',
  `arrival_time` TIME NULL,
  `notes` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_meeting_member` (`meeting_id`, `member_id`),
  KEY `idx_meeting` (`meeting_id`),
  KEY `idx_member` (`member_id`),
  KEY `idx_present` (`is_present`),
  CONSTRAINT `fk_attendance_meeting` FOREIGN KEY (`meeting_id`) REFERENCES `meetings` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_attendance_member` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Registro de presenças';

-- =================================================================
-- TABELA: reports (Relatórios gerados)
-- Descrição: Cache de relatórios complexos
-- =================================================================
CREATE TABLE IF NOT EXISTS `reports` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `type` VARCHAR(50) NOT NULL COMMENT 'attendance, formations, baptisms, growth, etc',
  `title` VARCHAR(255) NOT NULL,
  `filters` JSON NULL COMMENT 'Filtros aplicados',
  `data` JSON NULL COMMENT 'Dados do relatório',
  `format` ENUM('html', 'pdf', 'excel') DEFAULT 'html',
  `file_path` VARCHAR(500) NULL,
  `file_size` INT NULL,
  `period_start` DATE NULL,
  `period_end` DATE NULL,
  `status` ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `completed_at` DATETIME NULL,
  `created_by` BIGINT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  KEY `idx_period` (`period_start`, `period_end`),
  CONSTRAINT `fk_reports_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Relatórios gerados';

-- =================================================================
-- TABELA: notifications (Notificações/Alertas)
-- Descrição: Sistema de notificações para usuários
-- =================================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL COMMENT 'Usuário destinatário (NULL = todos)',
  `type` VARCHAR(50) NOT NULL COMMENT 'absence_alert, formation_reminder, etc',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `action_url` VARCHAR(500) NULL,
  `priority` ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
  `is_read` TINYINT(1) DEFAULT 0,
  `read_at` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_type` (`type`),
  KEY `idx_unread` (`user_id`, `is_read`),
  CONSTRAINT `fk_notifications_tenant` FOREIGN KEY (`tenant_id`) REFERENCES `tenants` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Notificações e alertas';

-- =================================================================
-- TABELA: audit_logs (Logs de auditoria)
-- Descrição: Registro de ações importantes no sistema
-- =================================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `action` VARCHAR(100) NOT NULL COMMENT 'login, logout, create_member, etc',
  `entity_type` VARCHAR(50) NULL COMMENT 'member, cell, meeting, etc',
  `entity_id` BIGINT UNSIGNED NULL,
  `description` TEXT NULL,
  `old_values` JSON NULL,
  `new_values` JSON NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_action` (`action`),
  KEY `idx_entity` (`entity_type`, `entity_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Logs de auditoria';

-- =================================================================
-- TABELA: settings (Configurações do sistema)
-- Descrição: Configurações globais e por tenant
-- =================================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `tenant_id` BIGINT UNSIGNED NULL COMMENT 'NULL = configuração global',
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT NULL,
  `type` VARCHAR(20) DEFAULT 'string' COMMENT 'string, int, bool, json',
  `description` TEXT NULL,
  `is_public` TINYINT(1) DEFAULT 0 COMMENT 'Visível no frontend',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_tenant_key` (`tenant_id`, `key`),
  KEY `idx_tenant` (`tenant_id`),
  KEY `idx_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Configurações do sistema';

-- =================================================================
-- INSERÇÃO DE DADOS PADRÃO
-- =================================================================

-- Formações padrão (serão copiadas para cada tenant na criação)
INSERT INTO `formations` (`tenant_id`, `code`, `name`, `description`, `duration_weeks`) VALUES
(1, 'CME', 'Curso de Maturidade Espiritual', 'Formação básica em maturidade espiritual cristã', 12),
(1, 'CTL', 'Curso de Treinamento de Líderes', 'Formação para liderança de células', 16),
(1, 'STP', 'Seminário Teológico Pastoral', 'Formação avançada em teologia pastoral', 24);

-- Configurações globais
INSERT INTO `settings` (`tenant_id`, `key`, `value`, `type`, `description`) VALUES
(NULL, 'app_name', 'Haklai SaaS', 'string', 'Nome da aplicação'),
(NULL, 'app_version', '1.0.0', 'string', 'Versão do sistema'),
(NULL, 'maintenance_mode', '0', 'bool', 'Modo manutenção'),
(NULL, 'session_lifetime', '7', 'int', 'Duração da sessão em dias'),
(NULL, 'max_login_attempts', '5', 'int', 'Máximo de tentativas de login'),
(NULL, 'lock_duration', '30', 'int', 'Duração do bloqueio em minutos'),
(NULL, '2fa_required_for_admins', '1', 'bool', '2FA obrigatório para admins');

COMMIT;

-- =================================================================
-- FIM DO SCHEMA
-- =================================================================
