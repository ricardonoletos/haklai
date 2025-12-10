# 🏛️ **DOCUMENTAÇÃO COMPLETA - HAKLAI APP**
## Sistema de Gerenciamento de Membros de Igreja

---

## 📖 **ÍNDICE**

1. [Visão Geral](#-visão-geral)
2. [Instalação e Configuração](#-instalação-e-configuração)
3. [Arquitetura do Sistema](#-arquitetura-do-sistema)
4. [Shortcodes Disponíveis](#-shortcodes-disponíveis)
5. [Sistema de Permissões](#-sistema-de-permissões)
6. [Design System](#-design-system)
7. [API e Desenvolvimento](#-api-e-desenvolvimento)
8. [Banco de Dados](#-banco-de-dados)
9. [Segurança](#-segurança)
10. [Performance](#-performance)
11. [Troubleshooting](#-troubleshooting)
12. [Roadmap](#-roadmap)

---

## 🎯 **VISÃO GERAL**

### **Sobre o Haklai App**
O Haklai App é um sistema completo de gerenciamento de membros de igreja desenvolvido como plugin WordPress. Oferece controle de presença, relatórios detalhados, hierarquias organizacionais e um ambiente produtivo para líderes de célula.

### **Características Principais**
- **Dashboard Inteligente:** Métricas em tempo real baseadas no nível hierárquico
- **Sistema de Presença:** Controle detalhado de frequência em reuniões
- **Relatórios Avançados:** 4 tipos de relatórios com exportação PDF/Excel
- **Checkpoint Produtivo:** Ambiente otimizado para líderes de célula
- **Hierarquia Flexível:** 6 níveis de acesso (Membro → Pastor Supervisor)

### **Tecnologias Utilizadas**
- **WordPress:** Plataforma base
- **PHP 8.1+:** Linguagem de programação
- **MySQL 5.7+:** Banco de dados
- **Bootstrap 5:** Framework CSS
- **jQuery:** Biblioteca JavaScript
- **FontAwesome 6:** Ícones

---

## 🚀 **INSTALAÇÃO E CONFIGURAÇÃO**

### **Requisitos do Sistema**
```
WordPress: 6.0 ou superior
PHP: 8.1 ou superior
MySQL: 5.7 ou superior
Memória: Mínimo 256MB (recomendado 512MB)
```

### **Passos de Instalação**

#### **1. Upload do Plugin**
```bash
# Via FTP/SFTP
wp-content/plugins/haklai-app-novo/

# Via WordPress Admin
Plugins → Adicionar Novo → Upload
```

#### **2. Ativação**
```php
// Via WordPress Admin
Plugins → Haklai App → Ativar

// Via WP-CLI
wp plugin activate haklai-app-novo
```

#### **3. Configuração Inicial**
1. Acesse o painel administrativo
2. Configure as permissões dos usuários
3. Execute o seeder de dados iniciais
4. Configure as células e hierarquias

### **Verificação de Instalação**
```php
// Verificar se o plugin está ativo
if (class_exists('Haklai_App')) {
    echo 'Plugin Haklai App está ativo';
}

// Verificar shortcodes disponíveis
global $shortcode_tags;
if (isset($shortcode_tags['Haklai_dashboard'])) {
    echo 'Shortcodes carregados com sucesso';
}
```

---

## 🏗️ **ARQUITETURA DO SISTEMA**

### **Estrutura de Diretórios**
```
haklai-app-novo/
├── haklai-app-novo.php              # Plugin principal
├── includes/
│   ├── shortcodes/                  # Sistema de shortcodes
│   │   ├── class-haklai-shortcodes.php
│   │   └── templates/               # Templates PHP
│   │       ├── dashboard-template.php
│   │       ├── configuracoes-template.php
│   │       ├── relatorios-template.php
│   │       └── checkpoint-template.php
│   └── core/                        # Classes principais
│       ├── class-haklai-plugin.php
│       ├── class-haklai-activator.php
│       ├── class-haklai-deactivator.php
│       ├── class-haklai-db.php
│       ├── class-haklai-roles.php
│       ├── class-haklai-cpt.php
│       ├── class-haklai-data-seeder.php
│       ├── class-haklai-dashboard.php
│       └── class-haklai-checkpoint.php
├── assets/
│   ├── css/
│   │   └── haklai-main.css         # CSS consolidado
│   └── js/
│       └── haklai-main.js          # JavaScript principal
├── README.md                        # Documentação principal
├── SHORTCODES_DOCUMENTATION.md      # Guia técnico dos shortcodes
└── uninstall.php                   # Script de desinstalação
```

### **Padrões Arquiteturais**
- **Singleton Pattern:** Classe principal do plugin
- **MVC Pattern:** Separação de lógica, apresentação e dados
- **Factory Pattern:** Criação de templates dinâmicos
- **Observer Pattern:** Sistema de notificações e eventos

### **Fluxo de Dados**
```
Usuário → Shortcode → Template → AJAX → Database → Response → Frontend
```

---

## 📱 **SHORTCODES DISPONÍVEIS**

### **1. [Haklai_dashboard]**
**Descrição:** Exibe o dashboard principal do sistema

**Uso:**
```php
[Haklai_dashboard]
```

**Permissões:** `haklai_access_dashboard`

**Funcionalidades:**
- Métricas principais (membros, presença, batismos, células)
- Gráficos de presença por semana
- Notificações do sistema
- Lista de membros recentes
- Modal para adicionar novos membros

**Dados Exibidos (por nível hierárquico):**
- **Líder de Célula:** Dados apenas da sua célula
- **Discipulador:** Dados das células supervisionadas
- **Pastor:** Dados da rede/igreja
- **Admin:** Dados completos do sistema

**Exemplo de Implementação:**
```php
// Em uma página WordPress
<div class="haklai-page">
    <h1>Dashboard da Igreja</h1>
    [Haklai_dashboard]
</div>
```

### **2. [Haklai_configuracoes]**
**Descrição:** Exibe as configurações do sistema

**Uso:**
```php
[Haklai_configuracoes]
```

**Permissões:** `haklai_access_settings` (apenas Pastores Senior/Supervisor)

**Funcionalidades:**
- Informações da licença
- Dados do sistema (PHP, WordPress, memória)
- Gerenciamento de permissões por nível
- Configurações de backup
- Sistema de backup automático

**Seções:**
- **Licença:** Status, tipo, expiração, usuários máximos
- **Sistema:** Versões, performance, última atualização
- **Permissões:** Grid de checkboxes por papel hierárquico
- **Backup:** Criação, restauração e configuração

### **3. [Haklai_relatorios]**
**Descrição:** Exibe sistema de relatórios e estatísticas

**Uso:**
```php
[Haklai_relatorios]
```

**Permissões:** `haklai_access_reports`

**Funcionalidades:**
- Estatísticas gerais do sistema
- Filtros por período e escopo
- 4 tipos de relatórios disponíveis
- Exportação em PDF e Excel
- Histórico de relatórios gerados

**Tipos de Relatórios:**
1. **Presença:** Análise de frequência e padrões
2. **Batismos:** Dados demográficos e crescimento
3. **Hierarquia:** Estrutura organizacional
4. **Habilidades:** Mapeamento de talentos

**Filtros Disponíveis:**
- Período (semana, mês, trimestre, ano, personalizado)
- Data início e fim
- Escopo (célula/rede específica)

### **4. [Haklai_checkpoint]**
**Descrição:** Ambiente produtivo para líderes de célula

**Uso:**
```php
[Haklai_checkpoint]
```

**Permissões:** `haklai_access_checkpoint` (apenas Líderes de Célula)

**Funcionalidades:**
- Dashboard de presença em tempo real
- Controles da reunião (data, anfitrião, endereço)
- Lista de membros com toggle de presença
- Adição de visitantes e novos membros
- Envio de relatório da reunião

**Fluxo de Trabalho:**
1. Líder acessa o checkpoint
2. Configura dados da reunião
3. Marca presença de cada membro
4. Adiciona visitantes se necessário
5. Envia relatório da reunião

**Recursos Especiais:**
- Busca de anfitrião com autocomplete
- Estatísticas atualizadas em tempo real
- Modais para adicionar visitantes e membros
- Validação de formulários

---

## 🔐 **SISTEMA DE PERMISSÕES**

### **Hierarquia de Usuários**
```
Nível 5: Pastor Supervisor    - Acesso total ao sistema
Nível 4: Pastor Senior       - Múltiplas redes
Nível 3: Pastor de Rede      - Rede específica
Nível 2: Discipulador        - 3-20 células supervisionadas
Nível 1: Líder de Célula     - Apenas sua célula + checkpoint
Nível 0: Membro              - Sem acesso aos shortcodes
```

### **Capabilities WordPress**
```php
// Capabilities principais
haklai_access_dashboard      // Acesso ao dashboard
haklai_access_settings       // Acesso às configurações
haklai_access_reports        // Acesso aos relatórios
haklai_access_checkpoint     // Acesso ao checkpoint
haklai_manage_members        // Gerenciar membros
haklai_manage_cells          // Gerenciar células
haklai_manage_users          // Gerenciar usuários
haklai_view_all_data         // Ver todos os dados
```

### **Implementação de Permissões**
```php
// Verificação de permissões
if (!current_user_can('haklai_access_dashboard')) {
    return '<div class="alert alert-danger">Você não tem permissão para acessar o dashboard.</div>';
}

// Obter nível do usuário
$user_level = get_user_meta($user_id, 'haklai_role_level', true);

// Verificar acesso baseado no nível
switch ($user_level) {
    case 5: // Pastor Supervisor - Acesso total
        break;
    case 4: // Pastor Senior - Configurações limitadas
        break;
    case 3: // Pastor de Rede - Rede específica
        break;
    case 2: // Discipulador - Células supervisionadas
        break;
    case 1: // Líder de Célula - Apenas sua célula
        break;
    default: // Sem acesso
        break;
}
```

### **Configuração de Permissões**
```php
// Adicionar capabilities a um papel
$role = get_role('administrator');
$role->add_cap('haklai_access_dashboard');
$role->add_cap('haklai_access_settings');
$role->add_cap('haklai_access_reports');

// Remover capabilities
$role->remove_cap('haklai_access_checkpoint');
```

---

## 🎨 **DESIGN SYSTEM**

### **Paleta de Cores**
```css
:root {
    /* Cores Principais */
    --primary-color: #667eea;      /* Azul principal */
    --secondary-color: #10b981;    /* Verde sucesso */
    --accent-color: #f59e0b;       /* Amarelo destaque */
    --danger-color: #ef4444;       /* Vermelho erro */
    --success-color: #10b981;      /* Verde sucesso */
    --info-color: #06b6d4;         /* Azul informação */
    --warning-color: #f59e0b;      /* Amarelo aviso */
    
    /* Cores de Texto */
    --text-primary: #2d3748;       /* Texto principal */
    --text-secondary: #4a5568;     /* Texto secundário */
    --text-muted: #718096;         /* Texto discreto */
    
    /* Cores de Fundo */
    --bg-primary: #ffffff;         /* Fundo principal */
    --bg-secondary: #f7fafc;       /* Fundo secundário */
    --bg-tertiary: #edf2f7;        /* Fundo terciário */
    
    /* Bordas */
    --border-color: #e2e8f0;       /* Cor das bordas */
    --border-radius: 16px;         /* Raio padrão */
    --border-radius-sm: 12px;      /* Raio pequeno */
    --border-radius-lg: 20px;      /* Raio grande */
    
    /* Sombras */
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.1);
    --shadow-md: 0 4px 20px rgba(0,0,0,0.08);
    --shadow-lg: 0 8px 30px rgba(0,0,0,0.12);
    --shadow-xl: 0 20px 60px rgba(0,0,0,0.2);
    
    /* Tipografia */
    --font-family: 'Inter', 'Segoe UI', -apple-system, BlinkMacSystemFont, sans-serif;
    --font-size-xs: 0.75rem;
    --font-size-sm: 0.875rem;
    --font-size-base: 1rem;
    --font-size-lg: 1.125rem;
    --font-size-xl: 1.25rem;
    --font-size-2xl: 1.5rem;
    --font-size-3xl: 1.875rem;
    --font-size-4xl: 2.25rem;
}
```

### **Componentes Principais**

#### **Headers**
```css
.dashboard-header,
.config-header,
.reports-header,
.checkpoint-header {
    background: linear-gradient(135deg, var(--primary-color) 0%, #764ba2 100%);
    color: white;
    padding: 40px;
    position: relative;
    overflow: hidden;
}

.header-content h1 {
    font-size: var(--font-size-4xl);
    font-weight: 800;
    margin: 0;
    text-shadow: 0 4px 8px rgba(0,0,0,0.3);
    letter-spacing: -1px;
}
```

#### **Cards**
```css
.metric-card,
.stat-card {
    background: var(--bg-primary);
    border-radius: var(--border-radius);
    padding: 30px;
    text-align: center;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--border-color);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.metric-card:hover,
.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}
```

#### **Botões**
```css
.btn {
    border: none;
    padding: 12px 24px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 25px;
    transition: all 0.3s ease;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background: linear-gradient(135deg, var(--primary-color) 0%, #764ba2 100%);
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
}
```

### **Responsividade**
```css
/* Desktop */
@media (min-width: 1200px) {
    .metrics-grid,
    .stats-grid {
        grid-template-columns: repeat(4, 1fr);
    }
}

/* Tablet */
@media (max-width: 1199px) and (min-width: 768px) {
    .metrics-grid,
    .stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

/* Mobile */
@media (max-width: 767px) {
    .metrics-grid,
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .haklai-container {
        padding: 10px;
    }
}
```

---

## 🔧 **API E DESENVOLVIMENTO**

### **Hooks WordPress**
```php
// Hooks de inicialização
do_action('haklai_app_init');
do_action('haklai_app_activated');
do_action('haklai_app_deactivated');

// Hooks de dados
apply_filters('haklai_dashboard_data', $data, $user_level, $user_id);
apply_filters('haklai_member_data', $member, $context);
apply_filters('haklai_attendance_data', $attendance, $meeting_id);

// Hooks de permissões
apply_filters('haklai_user_permissions', $permissions, $user_id);
apply_filters('haklai_can_access_feature', $can_access, $feature, $user_id);
```

### **Classes Principais**

#### **Haklai_Shortcodes**
```php
class Haklai_Shortcodes {
    public function __construct();
    public function register_shortcodes();
    public function enqueue_assets();
    public function dashboard_shortcode($atts);
    public function configuracoes_shortcode($atts);
    public function relatorios_shortcode($atts);
    public function checkpoint_shortcode($atts);
}
```

#### **Haklai_DB**
```php
class Haklai_DB {
    public static function create_tables();
    public static function get_members($filters = array());
    public static function get_cells($filters = array());
    public static function get_attendance($meeting_id);
    public static function save_attendance($data);
    public static function get_reports($filters = array());
}
```

#### **Haklai_Roles**
```php
class Haklai_Roles {
    public function __construct();
    public function create_roles();
    public function add_capabilities();
    public function remove_roles();
    public function get_user_level($user_id);
    public function get_user_permissions($user_id);
}
```

### **Funções Utilitárias**
```php
// Obter dados do dashboard
function haklai_get_dashboard_data($user_level, $user_id);

// Verificar permissões
function haklai_user_can_access($feature, $user_id = null);

// Obter membros de uma célula
function haklai_get_cell_members($cell_id);

// Calcular taxa de presença
function haklai_calculate_presence_rate($cell_id, $period = 'month');

// Gerar relatório
function haklai_generate_report($type, $filters);
```

### **JavaScript API**
```javascript
// Objeto principal
HaklaiApp.init();
HaklaiApp.showNotification(message, type);
HaklaiApp.executeAction(action, params);

// Funções específicas
DashboardFunctions.loadDashboardData();
CheckpointFunctions.toggleAttendance(card);
ReportsFunctions.generateReport(type, format);

// Utilitários
HaklaiApp.utils.formatDate(date);
HaklaiApp.utils.formatCurrency(value);
HaklaiApp.utils.debounce(func, wait);
```

---

## 🗄️ **BANCO DE DADOS**

### **Estrutura das Tabelas**

#### **wp_haklai_members**
```sql
CREATE TABLE wp_haklai_members (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) DEFAULT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) DEFAULT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    role_level TINYINT(1) DEFAULT 0,
    cell_id INT(11) DEFAULT NULL,
    leader_id INT(11) DEFAULT NULL,
    skill VARCHAR(100) DEFAULT NULL,
    baptism_status ENUM('visitor', 'frequent_visitor', 'baptized') DEFAULT 'visitor',
    baptism_date DATE DEFAULT NULL,
    is_visitor TINYINT(1) DEFAULT 0,
    visitor_count INT(11) DEFAULT 0,
    status ENUM('active', 'inactive', 'transferred') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_user_id (user_id),
    KEY idx_cell_id (cell_id),
    KEY idx_leader_id (leader_id),
    KEY idx_role_level (role_level),
    KEY idx_status (status)
);
```

#### **wp_haklai_cells**
```sql
CREATE TABLE wp_haklai_cells (
    id INT(11) NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT DEFAULT NULL,
    discipler_id INT(11) DEFAULT NULL,
    network_id INT(11) DEFAULT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_discipler_id (discipler_id),
    KEY idx_network_id (network_id),
    KEY idx_status (status)
);
```

#### **wp_haklai_attendance**
```sql
CREATE TABLE wp_haklai_attendance (
    id INT(11) NOT NULL AUTO_INCREMENT,
    meeting_id INT(11) DEFAULT NULL,
    member_id INT(11) NOT NULL,
    is_present TINYINT(1) DEFAULT 0,
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_meeting_id (meeting_id),
    KEY idx_member_id (member_id),
    KEY idx_is_present (is_present)
);
```

#### **wp_haklai_meetings**
```sql
CREATE TABLE wp_haklai_meetings (
    id INT(11) NOT NULL AUTO_INCREMENT,
    cell_id INT(11) NOT NULL,
    meeting_date DATE NOT NULL,
    host VARCHAR(255) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cell_id (cell_id),
    KEY idx_meeting_date (meeting_date),
    KEY idx_created_by (created_by)
);
```

#### **wp_haklai_reports**
```sql
CREATE TABLE wp_haklai_reports (
    id INT(11) NOT NULL AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL,
    format ENUM('pdf', 'excel', 'csv') DEFAULT 'pdf',
    filters TEXT DEFAULT NULL,
    file_path VARCHAR(500) DEFAULT NULL,
    status ENUM('processing', 'completed', 'failed') DEFAULT 'processing',
    created_by INT(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_type (type),
    KEY idx_status (status),
    KEY idx_created_by (created_by)
);
```

### **Relacionamentos**
```sql
-- Membros → Células
ALTER TABLE wp_haklai_members 
ADD FOREIGN KEY (cell_id) REFERENCES wp_haklai_cells(id) ON DELETE SET NULL;

-- Membros → Líderes
ALTER TABLE wp_haklai_members 
ADD FOREIGN KEY (leader_id) REFERENCES wp_haklai_members(id) ON DELETE SET NULL;

-- Células → Discipuladores
ALTER TABLE wp_haklai_cells 
ADD FOREIGN KEY (discipler_id) REFERENCES wp_haklai_members(id) ON DELETE SET NULL;

-- Presença → Reuniões
ALTER TABLE wp_haklai_attendance 
ADD FOREIGN KEY (meeting_id) REFERENCES wp_haklai_meetings(id) ON DELETE CASCADE;

-- Presença → Membros
ALTER TABLE wp_haklai_attendance 
ADD FOREIGN KEY (member_id) REFERENCES wp_haklai_members(id) ON DELETE CASCADE;
```

### **Índices para Performance**
```sql
-- Índices compostos para consultas frequentes
CREATE INDEX idx_member_cell_status ON wp_haklai_members(cell_id, status);
CREATE INDEX idx_attendance_meeting_member ON wp_haklai_attendance(meeting_id, member_id);
CREATE INDEX idx_meeting_cell_date ON wp_haklai_meetings(cell_id, meeting_date);

-- Índices para relatórios
CREATE INDEX idx_member_role_status ON wp_haklai_members(role_level, status);
CREATE INDEX idx_meeting_date_range ON wp_haklai_meetings(meeting_date);
```

---

## 🔒 **SEGURANÇA**

### **Medidas Implementadas**

#### **Validação e Sanitização**
```php
// Sanitização de inputs
$name = sanitize_text_field($_POST['name']);
$email = sanitize_email($_POST['email']);
$phone = sanitize_text_field($_POST['phone']);
$notes = sanitize_textarea_field($_POST['notes']);

// Validação de dados
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    wp_send_json_error('Email inválido');
}

if (!preg_match('/^\(\d{2}\) \d{4,5}-\d{4}$/', $phone)) {
    wp_send_json_error('Telefone inválido');
}
```

#### **Prepared Statements**
```php
// Consulta segura
$stmt = $wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}haklai_members 
     WHERE cell_id = %d AND status = %s 
     ORDER BY name ASC",
    $cell_id,
    'active'
);
$members = $wpdb->get_results($stmt);

// Inserção segura
$result = $wpdb->insert(
    $wpdb->prefix . 'haklai_members',
    array(
        'name' => $name,
        'email' => $email,
        'cell_id' => $cell_id,
        'status' => 'active'
    ),
    array('%s', '%s', '%d', '%s')
);
```

#### **Nonces de Segurança**
```php
// Verificação de nonce
if (!wp_verify_nonce($_POST['nonce'], 'haklai_nonce')) {
    wp_die('Erro de segurança');
}

// Geração de nonce
wp_localize_script('haklai-main-js', 'haklai_ajax', array(
    'ajax_url' => admin_url('admin-ajax.php'),
    'nonce' => wp_create_nonce('haklai_nonce')
));
```

#### **Escape de Output**
```php
// Escape de dados para exibição
echo esc_html($member->name);
echo esc_url($avatar_url);
echo esc_attr($member_id);

// Escape em templates
<img src="<?php echo esc_url($avatar_url); ?>" 
     alt="<?php echo esc_attr($member->name); ?>">
```

### **Controle de Acesso**
```php
// Verificação de capabilities
if (!current_user_can('haklai_access_dashboard')) {
    wp_send_json_error('Sem permissão');
}

// Verificação de nível hierárquico
$user_level = get_user_meta(get_current_user_id(), 'haklai_role_level', true);
if ($user_level < 2) {
    wp_send_json_error('Nível de acesso insuficiente');
}

// Verificação de propriedade
$member = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}haklai_members WHERE id = %d",
    $member_id
));

if ($member && !haklai_user_can_access_member($member, get_current_user_id())) {
    wp_send_json_error('Acesso negado a este membro');
}
```

### **Logs de Auditoria**
```php
// Log de ações importantes
function haklai_log_action($action, $user_id, $data = array()) {
    global $wpdb;
    
    $wpdb->insert(
        $wpdb->prefix . 'haklai_audit_log',
        array(
            'action' => $action,
            'user_id' => $user_id,
            'data' => json_encode($data),
            'ip_address' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'created_at' => current_time('mysql')
        )
    );
}

// Uso
haklai_log_action('member_added', get_current_user_id(), array(
    'member_id' => $member_id,
    'member_name' => $name
));
```

---

## ⚡ **PERFORMANCE**

### **Otimizações Implementadas**

#### **CSS e JavaScript**
```php
// Enqueue otimizado
function enqueue_assets() {
    // CSS consolidado
    wp_enqueue_style(
        'haklai-main-css',
        plugin_dir_url(__FILE__) . 'assets/css/haklai-main.css',
        array(),
        HAKLAI_APP_VERSION
    );
    
    // JavaScript com dependências mínimas
    wp_enqueue_script(
        'haklai-main-js',
        plugin_dir_url(__FILE__) . 'assets/js/haklai-main.js',
        array('jquery'),
        HAKLAI_APP_VERSION,
        true
    );
    
    // CDN para bibliotecas externas
    wp_enqueue_style(
        'bootstrap-5',
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        array(),
        '5.3.0'
    );
}
```

#### **Consultas Otimizadas**
```php
// Cache de consultas frequentes
function get_dashboard_data($user_level, $user_id) {
    $cache_key = "haklai_dashboard_{$user_level}_{$user_id}";
    $cached_data = wp_cache_get($cache_key, 'haklai');
    
    if ($cached_data === false) {
        $data = calculate_dashboard_data($user_level, $user_id);
        wp_cache_set($cache_key, $data, 'haklai', 300); // 5 minutos
        return $data;
    }
    
    return $cached_data;
}

// Consultas com LIMIT para grandes datasets
function get_recent_members($limit = 5) {
    global $wpdb;
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}haklai_members 
         WHERE status = 'active' 
         ORDER BY created_at DESC 
         LIMIT %d",
        $limit
    ));
}
```

#### **Lazy Loading**
```javascript
// Carregamento sob demanda
function loadDashboardData() {
    if (document.querySelector('.dashboard-container')) {
        $.ajax({
            url: haklai_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'haklai_load_dashboard_data',
                nonce: haklai_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    updateDashboard(response.data);
                }
            }
        });
    }
}

// Intersection Observer para elementos visíveis
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            loadComponent(entry.target);
        }
    });
});
```

### **Métricas de Performance**
```php
// Medição de tempo de execução
function measure_performance($callback, $label) {
    $start_time = microtime(true);
    $result = $callback();
    $end_time = microtime(true);
    
    $execution_time = ($end_time - $start_time) * 1000; // em ms
    
    if (WP_DEBUG) {
        error_log("Haklai App - {$label}: {$execution_time}ms");
    }
    
    return $result;
}

// Uso
$dashboard_data = measure_performance(
    function() use ($user_level, $user_id) {
        return get_dashboard_data($user_level, $user_id);
    },
    'Dashboard Data Loading'
);
```

---

## 🔍 **TROUBLESHOOTING**

### **Problemas Comuns e Soluções**

#### **1. Shortcode não aparece**
```php
// Verificar se o plugin está ativo
if (!class_exists('Haklai_App')) {
    echo 'Plugin Haklai App não está ativo';
    return;
}

// Verificar permissões
if (!current_user_can('haklai_access_dashboard')) {
    echo 'Usuário sem permissão';
    return;
}

// Verificar se os assets estão carregados
if (!wp_style_is('haklai-main-css', 'enqueued')) {
    echo 'CSS não está carregado';
    return;
}
```

#### **2. Estilos não aplicados**
```css
/* Verificar conflitos com tema */
.haklai-container {
    z-index: 9999 !important;
}

/* Forçar carregamento de CSS */
.haklai-container * {
    box-sizing: border-box !important;
}

/* Verificar se as variáveis CSS estão definidas */
:root {
    --primary-color: #667eea !important;
}
```

#### **3. JavaScript não funciona**
```javascript
// Verificar se jQuery está carregado
if (typeof jQuery === 'undefined') {
    console.error('jQuery não está carregado');
    return;
}

// Verificar se HaklaiApp está disponível
if (typeof HaklaiApp === 'undefined') {
    console.error('HaklaiApp não está carregado');
    return;
}

// Verificar se os dados AJAX estão disponíveis
if (typeof haklai_ajax === 'undefined') {
    console.error('Dados AJAX não estão disponíveis');
    return;
}
```

#### **4. AJAX não funciona**
```php
// Verificar nonce
if (!wp_verify_nonce($_POST['nonce'], 'haklai_nonce')) {
    wp_send_json_error('Nonce inválido');
}

// Verificar permissões
if (!current_user_can('haklai_access_dashboard')) {
    wp_send_json_error('Sem permissão');
}

// Verificar dados obrigatórios
if (empty($_POST['action'])) {
    wp_send_json_error('Ação não especificada');
}
```

#### **5. Problemas de Performance**
```php
// Verificar consultas lentas
function debug_slow_queries() {
    if (WP_DEBUG) {
        add_action('wp_footer', function() {
            global $wpdb;
            echo "<!-- Haklai App Queries: " . count($wpdb->queries) . " -->";
        });
    }
}

// Otimizar consultas
function optimize_queries() {
    // Usar índices
    $members = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}haklai_members 
        WHERE cell_id = %d AND status = 'active'
        ORDER BY name ASC
    ");
    
    // Limitar resultados
    $recent_members = $wpdb->get_results("
        SELECT * FROM {$wpdb->prefix}haklai_members 
        ORDER BY created_at DESC 
        LIMIT 10
    ");
}
```

### **Logs de Debug**
```php
// Habilitar logs de debug
function enable_haklai_debug() {
    if (WP_DEBUG) {
        add_action('wp_ajax_haklai_ajax_handler', function() {
            error_log('Haklai AJAX Handler chamado');
        }, 5);
        
        add_action('haklai_app_init', function() {
            error_log('Haklai App inicializado');
        });
    }
}

// Log personalizado
function haklai_log($message, $level = 'info') {
    if (WP_DEBUG) {
        error_log("Haklai App [{$level}]: {$message}");
    }
}
```

---

## 🗺️ **ROADMAP**

### **Versão 2.1.0 (Próxima)**
- [ ] **Sistema de Notificações Push**
  - Notificações em tempo real
  - Configuração por usuário
  - Integração com PWA

- [ ] **Relatórios Automáticos**
  - Envio por email
  - Agendamento de relatórios
  - Templates personalizáveis

- [ ] **Integração WhatsApp**
  - API oficial do WhatsApp Business
  - Mensagens automáticas
  - Notificações de reunião

- [ ] **App Mobile**
  - React Native
  - Sincronização offline
  - Push notifications

### **Versão 2.2.0 (Futuro)**
- [ ] **Sistema de Eventos**
  - Calendário integrado
  - Gestão de eventos
  - Notificações automáticas

- [ ] **Backup na Nuvem**
  - AWS S3 / Google Cloud
  - Backup automático
  - Restauração rápida

- [ ] **Analytics Avançados**
  - Métricas detalhadas
  - Gráficos interativos
  - Insights com IA

### **Versão 3.0.0 (Longo Prazo)**
- [ ] **Multi-tenant Completo**
  - Múltiplas igrejas
  - Isolamento de dados
  - Customização por igreja

- [ ] **API REST Pública**
  - Integração com sistemas externos
  - Webhooks
  - SDK para desenvolvedores

- [ ] **Machine Learning**
  - Predição de presença
  - Recomendações de líderes
  - Análise de crescimento

---

## 📞 **SUPORTE E CONTATO**

### **Canais de Suporte**
- **Email:** contato@ruthes.dev
- **Website:** https://ruthes.dev
- **Documentação:** https://docs.ruthes.dev/haklai
- **GitHub:** https://github.com/jefterruthes/haklai-app

### **FAQ Frequente**

**Q: Como alterar as cores do sistema?**
A: Edite as variáveis CSS em `assets/css/haklai-main.css`

**Q: Como adicionar novos tipos de relatório?**
A: Estenda a classe `Haklai_Shortcodes` e adicione novos métodos

**Q: Como personalizar as permissões?**
A: Use o hook `haklai_user_permissions` para modificar permissões

**Q: Como integrar com outros plugins?**
A: Use os hooks disponíveis para integrar com outros sistemas

**Q: Como fazer backup dos dados?**
A: Use o sistema de backup integrado ou exporte via phpMyAdmin

---

## 📄 **LICENÇA**

Este plugin é licenciado sob GPL v2 ou posterior.

```
Copyright (C) 2025 Jefter Ruthes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
```

---

## 🏆 **CRÉDITOS**

**Desenvolvido por:** Jefter Ruthes  
**Website:** https://ruthes.dev  
**Email:** contato@ruthes.dev  

**Agradecimentos:**
- Comunidade WordPress
- Contribuidores do Bootstrap
- Equipe do FontAwesome
- Desenvolvedores da Google Fonts

---

**Esta documentação está sempre atualizada com a versão mais recente do plugin Haklai App.**

*Última atualização: Janeiro 2025*
