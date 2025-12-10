# 🗄️ **DOCUMENTAÇÃO COMPLETA DO BANCO DE DADOS - HAKLAI APP**

## 📋 **VISÃO GERAL**

Este documento detalha a estrutura completa do banco de dados do Haklai App, incluindo todas as tabelas, relacionamentos, índices e especificações técnicas.

**Versão do Banco:** 2.0.0  
**Desenvolvedor:** Jefter Ruthes - https://ruthes.dev  
**Data:** Janeiro 2025  

---

## 🏗️ **ARQUITETURA DO BANCO DE DADOS**

### **Características Principais:**
- **12 Tabelas Principais** com relacionamentos bem definidos
- **Índices Otimizados** para consultas frequentes
- **Suporte a JSON** para dados flexíveis
- **Auditoria Completa** com logs de todas as ações
- **Escalabilidade** preparada para grandes volumes de dados

### **Padrões de Nomenclatura:**
- **Prefixo:** `wp_haklai_`
- **Campos de Data:** `created_at`, `updated_at`
- **IDs:** `id` (BIGINT UNSIGNED AUTO_INCREMENT)
- **Status:** ENUMs para valores controlados
- **Metadados:** `created_by`, `updated_by`

---

## 📊 **ESTRUTURA DAS TABELAS**

### **1. 🧑‍🤝‍🧑 wp_haklai_members**
**Função:** Armazenar dados completos dos membros da igreja

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
wp_user_id          BIGINT UNSIGNED NULL (relaciona com WordPress)
name                VARCHAR(255) NOT NULL
email               VARCHAR(255) NULL
phone               VARCHAR(20) NULL
birth_date          DATE NULL
gender              ENUM('M', 'F', 'O') NULL
address             TEXT NULL
city                VARCHAR(100) NULL
state               VARCHAR(50) NULL
zip_code            VARCHAR(10) NULL
```

#### **Campos Hierárquicos:**
```sql
role_level          TINYINT(1) NOT NULL DEFAULT 0 (0-5)
cell_id             BIGINT UNSIGNED NULL (FK para cells)
leader_id           BIGINT UNSIGNED NULL (FK para members)
network_id          BIGINT UNSIGNED NULL (FK para networks)
```

#### **Campos de Status:**
```sql
baptism_status      ENUM('visitor', 'frequent_visitor', 'baptized')
baptism_date        DATE NULL
is_visitor          TINYINT(1) NOT NULL DEFAULT 0
visitor_count       INT(11) NOT NULL DEFAULT 0
status              ENUM('active', 'inactive', 'transferred', 'suspended')
```

#### **Campos de Controle:**
```sql
last_attendance_date    DATE NULL
total_attendances       INT(11) NOT NULL DEFAULT 0
consecutive_absences    INT(11) NOT NULL DEFAULT 0
```

#### **Índices:**
- **Únicos:** `unique_wp_user`
- **Simples:** `idx_name`, `idx_email`, `idx_phone`, `idx_cell_id`, `idx_leader_id`, `idx_network_id`, `idx_role_level`, `idx_baptism_status`, `idx_status`
- **Compostos:** `idx_cell_status`, `idx_role_status`, `idx_baptism_status_active`, `idx_visitor_active`

---

### **2. 🏠 wp_haklai_cells**
**Função:** Informações das células (grupos de membros)

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Localização:**
```sql
address             TEXT NULL
neighborhood        VARCHAR(100) NULL
city                VARCHAR(100) NULL
state               VARCHAR(50) NULL
zip_code            VARCHAR(10) NULL
coordinates         POINT NULL (para mapas)
```

#### **Liderança:**
```sql
leader_id           BIGINT UNSIGNED NULL (FK para members)
discipler_id        BIGINT UNSIGNED NULL (FK para members)
network_id          BIGINT UNSIGNED NULL (FK para networks)
```

#### **Configuração de Reuniões:**
```sql
meeting_day         ENUM('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')
meeting_time        TIME NULL
meeting_frequency   ENUM('weekly', 'biweekly', 'monthly') DEFAULT 'weekly'
```

#### **Estatísticas:**
```sql
total_members       INT(11) NOT NULL DEFAULT 0
active_members      INT(11) NOT NULL DEFAULT 0
visitors_count      INT(11) NOT NULL DEFAULT 0
avg_attendance      DECIMAL(5,2) NOT NULL DEFAULT 0.00
last_meeting_date   DATE NULL
```

#### **Índices:**
- **Simples:** `idx_name`, `idx_leader_id`, `idx_discipler_id`, `idx_network_id`, `idx_status`, `idx_meeting_day`, `idx_city`, `idx_neighborhood`
- **Compostos:** `idx_network_status`, `idx_leader_status`, `idx_discipler_status`, `idx_meeting_schedule`

---

### **3. 🌐 wp_haklai_networks**
**Função:** Estrutura das redes (grupos de células)

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
name                VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Liderança:**
```sql
pastor_id           BIGINT UNSIGNED NULL (FK para members)
senior_pastor_id    BIGINT UNSIGNED NULL (FK para members)
```

#### **Estatísticas:**
```sql
total_cells         INT(11) NOT NULL DEFAULT 0
active_cells        INT(11) NOT NULL DEFAULT 0
total_members       INT(11) NOT NULL DEFAULT 0
active_members      INT(11) NOT NULL DEFAULT 0
avg_attendance      DECIMAL(5,2) NOT NULL DEFAULT 0.00
```

#### **Índices:**
- **Simples:** `idx_name`, `idx_pastor_id`, `idx_senior_pastor_id`, `idx_status`, `idx_created_at`

---

### **4. 📅 wp_haklai_meetings**
**Função:** Registro de todas as reuniões realizadas

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
cell_id             BIGINT UNSIGNED NOT NULL (FK para cells)
meeting_date        DATE NOT NULL
meeting_time        TIME NULL
```

#### **Local da Reunião:**
```sql
host_name           VARCHAR(255) NULL
host_phone          VARCHAR(20) NULL
address             TEXT NULL
is_online           TINYINT(1) NOT NULL DEFAULT 0
online_link         VARCHAR(500) NULL
```

#### **Estatísticas da Reunião:**
```sql
total_members       INT(11) NOT NULL DEFAULT 0
present_members     INT(11) NOT NULL DEFAULT 0
visitors_count      INT(11) NOT NULL DEFAULT 0
attendance_rate     DECIMAL(5,2) NOT NULL DEFAULT 0.00
```

#### **Conteúdo da Reunião:**
```sql
lesson_title        VARCHAR(255) NULL
lesson_scripture    VARCHAR(255) NULL
lesson_notes        TEXT NULL
prayer_requests     TEXT NULL
announcements       TEXT NULL
```

#### **Índices:**
- **Únicos:** `unique_cell_date` (evita reuniões duplicadas)
- **Simples:** `idx_cell_id`, `idx_meeting_date`, `idx_status`, `idx_created_by`
- **Compostos:** `idx_cell_date`, `idx_cell_status`, `idx_date_status`

---

### **5. ✅ wp_haklai_attendance**
**Função:** Controle detalhado de presença nas reuniões

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
meeting_id          BIGINT UNSIGNED NOT NULL (FK para meetings)
member_id           BIGINT UNSIGNED NOT NULL (FK para members)
```

#### **Status da Presença:**
```sql
is_present          TINYINT(1) NOT NULL DEFAULT 0
arrival_time        TIME NULL
departure_time      TIME NULL
```

#### **Tipo de Participação:**
```sql
participation_type  ENUM('member', 'visitor', 'leader') DEFAULT 'member'
is_new_visitor      TINYINT(1) NOT NULL DEFAULT 0
```

#### **Observações:**
```sql
notes               TEXT NULL
reason_absence      VARCHAR(255) NULL
```

#### **Índices:**
- **Únicos:** `unique_meeting_member` (evita registros duplicados)
- **Simples:** `idx_meeting_id`, `idx_member_id`, `idx_is_present`, `idx_participation_type`
- **Compostos:** `idx_meeting_present`, `idx_member_present`, `idx_meeting_type`

---

### **6. 📊 wp_haklai_reports**
**Função:** Gerenciamento de relatórios gerados pelo sistema

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
type                ENUM('attendance', 'baptisms', 'hierarchy', 'skills', 'growth', 'custom')
format              ENUM('pdf', 'excel', 'csv', 'json') DEFAULT 'pdf'
title               VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Filtros:**
```sql
filters             JSON NULL
date_from           DATE NULL
date_to             DATE NULL
scope               ENUM('all', 'network', 'cell', 'member') DEFAULT 'all'
scope_ids           JSON NULL
```

#### **Arquivo:**
```sql
file_path           VARCHAR(500) NULL
file_size           INT(11) NULL
file_hash           VARCHAR(64) NULL
```

#### **Status:**
```sql
status              ENUM('processing', 'completed', 'failed', 'expired') DEFAULT 'processing'
progress            INT(3) NOT NULL DEFAULT 0
error_message       TEXT NULL
```

#### **Índices:**
- **Simples:** `idx_type`, `idx_format`, `idx_status`, `idx_scope`, `idx_created_by`
- **Compostos:** `idx_type_status`, `idx_format_status`, `idx_scope_status`

---

### **7. 🚨 wp_haklai_alerts**
**Função:** Sistema de alertas e notificações

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
type                ENUM('absence', 'baptism_eligible', 'low_attendance', 'cell_inactive', 'system', 'custom')
priority            ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium'
title               VARCHAR(255) NOT NULL
message             TEXT NOT NULL
```

#### **Destinatários:**
```sql
target_type         ENUM('user', 'role', 'cell', 'network', 'all')
target_ids          JSON NULL
```

#### **Dados Relacionados:**
```sql
related_type        ENUM('member', 'cell', 'network', 'meeting', 'report') NULL
related_id          BIGINT UNSIGNED NULL
```

#### **Status:**
```sql
status              ENUM('pending', 'sent', 'read', 'dismissed', 'expired') DEFAULT 'pending'
is_actionable       TINYINT(1) NOT NULL DEFAULT 0
action_url          VARCHAR(500) NULL
```

#### **Índices:**
- **Simples:** `idx_type`, `idx_priority`, `idx_target_type`, `idx_status`, `idx_related_type`, `idx_related_id`
- **Compostos:** `idx_type_status`, `idx_priority_status`, `idx_target_status`

---

### **8. 🎓 wp_haklai_member_training**
**Função:** Formações e treinamentos dos membros

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
member_id           BIGINT UNSIGNED NOT NULL (FK para members)
training_type       ENUM('CTL', 'CME', 'STP', 'Liderança', 'Evangelismo', 'Discipulado', 'Outros')
training_name       VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Datas:**
```sql
start_date          DATE NULL
end_date            DATE NULL
completion_date     DATE NULL
```

#### **Status e Resultados:**
```sql
status              ENUM('enrolled', 'in_progress', 'completed', 'dropped', 'cancelled') DEFAULT 'enrolled'
grade               DECIMAL(5,2) NULL
certificate_url     VARCHAR(500) NULL
```

#### **Local e Instrutor:**
```sql
location            VARCHAR(255) NULL
instructor          VARCHAR(255) NULL
```

#### **Índices:**
- **Simples:** `idx_member_id`, `idx_training_type`, `idx_status`, `idx_completion_date`
- **Compostos:** `idx_member_type`, `idx_member_status`, `idx_type_status`

---

### **9. 🎨 wp_haklai_member_skills**
**Função:** Habilidades e talentos dos membros

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
member_id           BIGINT UNSIGNED NOT NULL (FK para members)
skill_category      ENUM('Fotografia', 'Música', 'Gastronomia', 'Tecnologia', 'Ensino', 'Evangelismo', 'Liderança', 'Administração', 'Outros')
skill_name          VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Nível de Habilidade:**
```sql
skill_level         ENUM('Iniciante', 'Intermediário', 'Avançado', 'Especialista') DEFAULT 'Iniciante'
years_experience    INT(3) NULL
```

#### **Disponibilidade:**
```sql
is_available        TINYINT(1) NOT NULL DEFAULT 1
availability_notes  TEXT NULL
```

#### **Certificações:**
```sql
certifications      TEXT NULL
portfolio_url       VARCHAR(500) NULL
```

#### **Índices:**
- **Simples:** `idx_member_id`, `idx_skill_category`, `idx_skill_name`, `idx_skill_level`, `idx_is_available`
- **Compostos:** `idx_member_category`, `idx_category_level`, `idx_member_available`

---

### **10. 🔐 wp_haklai_auth_logs**
**Função:** Logs de autenticação e segurança

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
user_id             BIGINT UNSIGNED NOT NULL (FK para wp_users)
action              ENUM('login', 'logout', 'failed_login', 'password_change', 'role_change', 'permission_change', 'session_expired', 'account_locked')
```

#### **Dados da Sessão:**
```sql
ip_address          VARCHAR(45) NOT NULL
user_agent          TEXT NULL
session_id          VARCHAR(64) NULL
```

#### **Dados Adicionais:**
```sql
data                JSON NULL
success             TINYINT(1) NOT NULL DEFAULT 1
error_message       TEXT NULL
```

#### **Índices:**
- **Simples:** `idx_user_id`, `idx_action`, `idx_ip_address`, `idx_success`, `idx_created_at`
- **Compostos:** `idx_user_action`, `idx_user_success`, `idx_action_date`

---

### **11. 💾 wp_haklai_backups**
**Função:** Gerenciamento de backups do sistema

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
type                ENUM('full', 'database', 'files', 'incremental')
name                VARCHAR(255) NOT NULL
description         TEXT NULL
```

#### **Arquivo:**
```sql
file_path           VARCHAR(500) NOT NULL
file_size           BIGINT UNSIGNED NOT NULL
file_hash           VARCHAR(64) NOT NULL
compression_type    ENUM('none', 'zip', 'gzip', 'tar') DEFAULT 'zip'
```

#### **Status:**
```sql
status              ENUM('processing', 'completed', 'failed', 'expired') DEFAULT 'processing'
progress            INT(3) NOT NULL DEFAULT 0
error_message       TEXT NULL
```

#### **Configurações:**
```sql
retention_days      INT(5) NOT NULL DEFAULT 30
is_automatic        TINYINT(1) NOT NULL DEFAULT 0
```

#### **Índices:**
- **Simples:** `idx_type`, `idx_status`, `idx_created_by`, `idx_created_at`, `idx_expires_at`, `idx_is_automatic`
- **Compostos:** `idx_type_status`, `idx_status_expires`

---

### **12. ⚙️ wp_haklai_settings**
**Função:** Configurações do sistema

#### **Campos Principais:**
```sql
id                  BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
setting_key         VARCHAR(255) NOT NULL
setting_value       LONGTEXT NULL
setting_type        ENUM('string', 'number', 'boolean', 'json', 'array') DEFAULT 'string'
description         TEXT NULL
```

#### **Categorização:**
```sql
category            VARCHAR(100) NOT NULL DEFAULT 'general'
is_public           TINYINT(1) NOT NULL DEFAULT 0
is_encrypted        TINYINT(1) NOT NULL DEFAULT 0
```

#### **Índices:**
- **Únicos:** `unique_setting_key`
- **Simples:** `idx_category`, `idx_is_public`, `idx_created_at`

---

## 🔗 **RELACIONAMENTOS ENTRE TABELAS**

### **Hierarquia Principal:**
```
networks (1) ←→ (N) cells (1) ←→ (N) members
    ↑                    ↑                    ↑
pastor_id          leader_id           member_id
                discipler_id
```

### **Relacionamentos Detalhados:**

#### **1. Membros ↔ Células:**
```sql
-- Um membro pertence a uma célula
members.cell_id → cells.id

-- Um membro pode ser líder de uma célula
members.id → cells.leader_id

-- Um membro pode ser discipulador de células
members.id → cells.discipler_id
```

#### **2. Células ↔ Redes:**
```sql
-- Uma célula pertence a uma rede
cells.network_id → networks.id
```

#### **3. Redes ↔ Liderança:**
```sql
-- Uma rede tem um pastor
networks.pastor_id → members.id

-- Uma rede pode ter um pastor sênior
networks.senior_pastor_id → members.id
```

#### **4. Reuniões ↔ Células:**
```sql
-- Uma reunião pertence a uma célula
meetings.cell_id → cells.id
```

#### **5. Presença ↔ Reuniões e Membros:**
```sql
-- Uma presença pertence a uma reunião
attendance.meeting_id → meetings.id

-- Uma presença pertence a um membro
attendance.member_id → members.id
```

#### **6. Formações ↔ Membros:**
```sql
-- Uma formação pertence a um membro
member_training.member_id → members.id
```

#### **7. Habilidades ↔ Membros:**
```sql
-- Uma habilidade pertence a um membro
member_skills.member_id → members.id
```

#### **8. Logs de Autenticação:**
```sql
-- Um log pertence a um usuário WordPress
auth_logs.user_id → wp_users.ID
```

---

## 📈 **ÍNDICES PARA PERFORMANCE**

### **Índices Compostos Estratégicos:**

#### **Para Consultas de Dashboard:**
```sql
-- Membros por célula e status
idx_cell_status (cell_id, status)

-- Membros por nível hierárquico e status
idx_role_status (role_level, status)

-- Presença por reunião e status
idx_meeting_present (meeting_id, is_present)
```

#### **Para Relatórios:**
```sql
-- Reuniões por célula e data
idx_cell_date (cell_id, meeting_date)

-- Presença por membro e status
idx_member_present (member_id, is_present)

-- Relatórios por tipo e status
idx_type_status (type, status)
```

#### **Para Sistema de Alertas:**
```sql
-- Alertas por tipo e status
idx_type_status (type, status)

-- Alertas por prioridade e status
idx_priority_status (priority, status)
```

### **Índices Adicionais para Performance:**
```sql
-- Tabela members
idx_members_baptism_date (baptism_date)
idx_members_visitor_count (visitor_count)
idx_members_total_attendances (total_attendances)

-- Tabela cells
idx_cells_avg_attendance (avg_attendance)
idx_cells_total_members (total_members)

-- Tabela meetings
idx_meetings_attendance_rate (attendance_rate)
idx_meetings_present_members (present_members)
```

---

## 🔒 **SEGURANÇA E INTEGRIDADE**

### **Chaves Estrangeiras (FK):**
- Todas as referências entre tabelas são mantidas via aplicação
- Índices garantem performance nas consultas JOIN
- Validação de integridade via código PHP

### **Campos Sensíveis:**
- **Passwords:** Não armazenados (usar WordPress)
- **Dados Pessoais:** Criptografia quando necessário
- **Logs de Auditoria:** Registro de todas as ações importantes

### **Backup e Recuperação:**
- **Tabela de Backups:** Controle de versões
- **Retenção Configurável:** Limpeza automática
- **Integridade:** Verificação de hash dos arquivos

---

## 📊 **CONSULTAS FREQUENTES**

### **1. Dashboard - Estatísticas Gerais:**
```sql
-- Total de membros ativos
SELECT COUNT(*) FROM wp_haklai_members WHERE status = 'active';

-- Taxa de presença média
SELECT AVG(attendance_rate) FROM wp_haklai_meetings 
WHERE meeting_date >= DATE_SUB(NOW(), INTERVAL 30 DAY);

-- Novos batismos do mês
SELECT COUNT(*) FROM wp_haklai_members 
WHERE baptism_date >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

### **2. Relatórios de Presença:**
```sql
-- Presença por célula no período
SELECT 
    c.name as cell_name,
    COUNT(DISTINCT m.id) as total_members,
    COUNT(DISTINCT a.member_id) as present_members,
    ROUND(COUNT(DISTINCT a.member_id) * 100.0 / COUNT(DISTINCT m.id), 2) as attendance_rate
FROM wp_haklai_cells c
LEFT JOIN wp_haklai_members m ON c.id = m.cell_id AND m.status = 'active'
LEFT JOIN wp_haklai_meetings mt ON c.id = mt.cell_id 
    AND mt.meeting_date BETWEEN ? AND ?
LEFT JOIN wp_haklai_attendance a ON mt.id = a.meeting_id AND a.is_present = 1
GROUP BY c.id, c.name;
```

### **3. Membros com Ausências Consecutivas:**
```sql
-- Membros com mais de 3 ausências consecutivas
SELECT 
    m.name,
    m.phone,
    c.name as cell_name,
    m.consecutive_absences,
    m.last_attendance_date
FROM wp_haklai_members m
JOIN wp_haklai_cells c ON m.cell_id = c.id
WHERE m.consecutive_absences >= 3 
    AND m.status = 'active'
ORDER BY m.consecutive_absences DESC;
```

### **4. Relatório de Habilidades:**
```sql
-- Mapeamento de habilidades por categoria
SELECT 
    skill_category,
    COUNT(*) as total_skills,
    COUNT(CASE WHEN is_available = 1 THEN 1 END) as available_skills,
    AVG(CASE 
        WHEN skill_level = 'Iniciante' THEN 1
        WHEN skill_level = 'Intermediário' THEN 2
        WHEN skill_level = 'Avançado' THEN 3
        WHEN skill_level = 'Especialista' THEN 4
    END) as avg_level
FROM wp_haklai_member_skills
GROUP BY skill_category
ORDER BY total_skills DESC;
```

---

## 🚀 **OTIMIZAÇÕES IMPLEMENTADAS**

### **1. Índices Estratégicos:**
- **Consultas Frequentes:** Índices compostos para JOINs comuns
- **Filtros de Data:** Índices em campos de data
- **Status e Categorias:** Índices em ENUMs

### **2. Estrutura de Dados:**
- **JSON Fields:** Para dados flexíveis (filtros, configurações)
- **ENUMs:** Para valores controlados e performance
- **DECIMAL:** Para valores monetários e percentuais precisos

### **3. Relacionamentos:**
- **Normalização:** Evita redundância de dados
- **Flexibilidade:** Suporte a estruturas hierárquicas
- **Escalabilidade:** Preparado para crescimento

### **4. Auditoria:**
- **Logs Completos:** Todas as ações registradas
- **Rastreabilidade:** Quem fez o quê e quando
- **Segurança:** Monitoramento de acessos

---

## 📋 **CHECKLIST DE IMPLEMENTAÇÃO**

### **✅ Tabelas Criadas:**
- [x] wp_haklai_members (membros)
- [x] wp_haklai_cells (células)
- [x] wp_haklai_networks (redes)
- [x] wp_haklai_meetings (reuniões)
- [x] wp_haklai_attendance (presença)
- [x] wp_haklai_reports (relatórios)
- [x] wp_haklai_alerts (alertas)
- [x] wp_haklai_member_training (formações)
- [x] wp_haklai_member_skills (habilidades)
- [x] wp_haklai_auth_logs (logs de autenticação)
- [x] wp_haklai_backups (backups)
- [x] wp_haklai_settings (configurações)

### **✅ Índices Implementados:**
- [x] Índices primários em todas as tabelas
- [x] Índices únicos para evitar duplicatas
- [x] Índices compostos para consultas frequentes
- [x] Índices adicionais para performance

### **✅ Funcionalidades:**
- [x] Sistema de versionamento do banco
- [x] Migração automática entre versões
- [x] Verificação de integridade das tabelas
- [x] Estatísticas do banco de dados
- [x] Limpeza segura das tabelas

---

## 🔧 **MANUTENÇÃO DO BANCO**

### **Comandos Úteis:**

#### **Verificar Status das Tabelas:**
```php
// Verificar se todas as tabelas existem
$tables_exist = Haklai_DB::tables_exist();

// Obter estatísticas
$stats = Haklai_DB::get_db_stats();

// Verificar versão
$version = Haklai_DB::get_db_version();
```

#### **Atualizar Banco:**
```php
// Atualizar se necessário
Haklai_DB::maybe_update_db();
```

#### **Limpar Banco (CUIDADO!):**
```php
// Remover todas as tabelas
Haklai_DB::drop_tables();
```

### **Monitoramento:**
- **Logs de Erro:** Verificar logs do WordPress
- **Performance:** Monitorar consultas lentas
- **Espaço:** Verificar crescimento das tabelas
- **Backups:** Verificar execução automática

---

## 📞 **SUPORTE TÉCNICO**

**Desenvolvedor:** Jefter Ruthes  
**Website:** https://ruthes.dev  
**Email:** contato@ruthes.dev  

Para suporte técnico relacionado ao banco de dados ou dúvidas sobre implementação, entre em contato através dos canais acima.

---

**Esta documentação está sempre atualizada com a versão mais recente do banco de dados do Haklai App.**

*Última atualização: Janeiro 2025*

