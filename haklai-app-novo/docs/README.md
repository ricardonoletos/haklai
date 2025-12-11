# 🏛️ **HAKLAI APP - SISTEMA DE GERENCIAMENTO DE MEMBROS**

## 📖 **Descrição**

O **Haklai App** é um sistema completo de gerenciamento de membros de igreja desenvolvido como plugin WordPress. Oferece controle de presença, relatórios detalhados, hierarquias organizacionais e um ambiente produtivo para líderes de célula.

---

## ✨ **Características Principais**

### 🎯 **Funcionalidades Core**
- **Dashboard Inteligente:** Métricas em tempo real baseadas no nível hierárquico
- **Sistema de Presença:** Controle detalhado de frequência em reuniões
- **Relatórios Avançados:** 4 tipos de relatórios com exportação PDF/Excel
- **Checkpoint Produtivo:** Ambiente otimizado para líderes de célula
- **Hierarquia Flexível:** 6 níveis de acesso (Membro → Pastor Supervisor)

### 🎨 **Design Moderno**
- **Interface Responsiva:** Funciona perfeitamente em desktop, tablet e mobile
- **Design System Unificado:** Cores, tipografia e componentes consistentes
- **Animações Suaves:** Transições e hover effects profissionais
- **Tema Dark/Light:** Compatível com temas WordPress

### 🔐 **Segurança Robusta**
- **Sistema de Permissões:** Controle granular baseado em capabilities
- **Validação Rigorosa:** Sanitização e validação de todos os inputs
- **Nonces de Segurança:** Proteção contra CSRF em todas as operações
- **Logs de Auditoria:** Rastreamento completo de ações do sistema

---

## 🚀 **Instalação**

### **Requisitos**
- **WordPress:** 6.0 ou superior
- **PHP:** 8.1 ou superior
- **MySQL:** 5.7 ou superior
- **Memória:** Mínimo 256MB (recomendado 512MB)

### **Passos de Instalação**

1. **Upload do Plugin:**
   ```bash
   # Via FTP/SFTP
   wp-content/plugins/haklai-app-novo/
   
   # Via WordPress Admin
   Plugins → Adicionar Novo → Upload
   ```

2. **Ativação:**
   ```php
   // Via WordPress Admin
   Plugins → Haklai App → Ativar
   
   // Via WP-CLI
   wp plugin activate haklai-app-novo
   ```

3. **Configuração Inicial:**
   - Acesse o painel administrativo
   - Configure as permissões dos usuários
   - Execute o seeder de dados iniciais
   - Configure as células e hierarquias

---

## 📱 **Como Usar**

### **1. Shortcodes Disponíveis**

#### **Dashboard Principal**
```php
[Haklai_dashboard]
```
- Exibe métricas baseadas no nível do usuário
- Gráficos de presença e estatísticas
- Lista de membros recentes
- Notificações do sistema

#### **Configurações do Sistema**
```php
[Haklai_configuracoes]
```
- Gerenciamento de permissões
- Informações da licença
- Configurações de backup
- Dados do sistema

#### **Relatórios e Estatísticas**
```php
[Haklai_relatorios]
```
- 4 tipos de relatórios disponíveis
- Filtros por período e escopo
- Exportação PDF/Excel
- Histórico de relatórios

#### **Checkpoint de Célula**
```php
[Haklai_checkpoint]
```
- Controle de presença em tempo real
- Adição de visitantes e membros
- Relatórios de reunião
- Busca de anfitriões

### **2. Hierarquia de Usuários**

```
Nível 5: Pastor Supervisor    - Acesso total ao sistema
Nível 4: Pastor Senior       - Múltiplas redes
Nível 3: Pastor de Rede      - Rede específica
Nível 2: Discipulador        - 3-20 células supervisionadas
Nível 1: Líder de Célula     - Apenas sua célula + checkpoint
Nível 0: Membro              - Sem acesso aos shortcodes
```

### **3. Fluxo de Trabalho Típico**

#### **Para Líderes de Célula:**
1. Acesse `[Haklai_checkpoint]`
2. Configure dados da reunião
3. Marque presença dos membros
4. Adicione visitantes se necessário
5. Envie relatório da reunião

#### **Para Pastores/Discipuladores:**
1. Acesse `[Haklai_dashboard]` para visão geral
2. Use `[Haklai_relatorios]` para análises detalhadas
3. Configure permissões via `[Haklai_configuracoes]`

---

## 🎨 **Personalização**

### **Cores e Tema**
```css
/* Personalizar cores principais */
:root {
    --primary-color: #667eea;
    --secondary-color: #10b981;
    --accent-color: #f59e0b;
    --danger-color: #ef4444;
}
```

### **Layout Responsivo**
```css
/* Ajustar largura máxima */
.dashboard-container,
.config-container,
.reports-container {
    max-width: 1600px; /* Padrão: 1400px */
}
```

### **JavaScript Customizado**
```javascript
// Hook para inicialização personalizada
jQuery(document).ready(function($) {
    // Sua lógica personalizada
    HaklaiApp.init();
});
```

---

## 🔧 **Desenvolvimento**

### **Estrutura do Projeto**
```
haklai-app-novo/
├── haklai-app-novo.php              # Arquivo principal
├── includes/
│   ├── shortcodes/                  # Sistema de shortcodes
│   │   ├── class-haklai-shortcodes.php
│   │   └── templates/               # Templates PHP
│   ├── core/                        # Classes principais
│   └── ...
├── assets/
│   ├── css/
│   │   └── haklai-main.css         # CSS consolidado
│   └── js/
│       └── haklai-main.js          # JavaScript principal
└── README.md
```

### **Hooks Disponíveis**
```php
// Hooks de inicialização
do_action('haklai_app_init');
do_action('haklai_app_activated');

// Hooks de dados
apply_filters('haklai_dashboard_data', $data);
apply_filters('haklai_member_data', $member);

// Hooks de permissões
apply_filters('haklai_user_permissions', $permissions, $user_id);
```

### **API de Desenvolvimento**
```php
// Obter dados do dashboard
$dashboard_data = Haklai_Shortcodes::get_dashboard_data($user_level, $user_id);

// Verificar permissões
$has_access = current_user_can('haklai_access_dashboard');

// Obter membros de uma célula
$members = Haklai_DB::get_cell_members($cell_id);
```

---

## 📊 **Banco de Dados**

### **Tabelas Principais**
```sql
wp_haklai_members          # Dados dos membros
wp_haklai_cells            # Informações das células
wp_haklai_attendance       # Controle de presença
wp_haklai_meetings         # Dados das reuniões
wp_haklai_reports          # Relatórios gerados
wp_haklai_alerts           # Sistema de alertas
```

### **Campos Importantes**
```sql
-- Membros
role_level         # Nível hierárquico (0-5)
baptism_status     # Status de batismo
is_visitor         # É visitante
visitor_count      # Contador de presenças
leader_id          # ID do líder associado

-- Células
name               # Nome da célula
address            # Endereço
discipler_id       # ID do discipulador
network_id         # ID da rede
status             # Status ativo/inativo
```

---

## 🔍 **Troubleshooting**

### **Problemas Comuns**

#### **1. Shortcode não aparece**
```php
// Verificar permissões
if (!current_user_can('haklai_access_dashboard')) {
    echo 'Usuário sem permissão';
}

// Verificar se o plugin está ativo
if (!class_exists('Haklai_App')) {
    echo 'Plugin não está ativo';
}
```

#### **2. Estilos não aplicados**
```css
/* Verificar conflitos com tema */
.haklai-container {
    z-index: 9999 !important;
}

/* Forçar carregamento de CSS */
wp_enqueue_style('haklai-main-css', plugin_dir_url(__FILE__) . 'assets/css/haklai-main.css');
```

#### **3. JavaScript não funciona**
```javascript
// Verificar se jQuery está carregado
if (typeof jQuery === 'undefined') {
    console.error('jQuery não está carregado');
}

// Verificar se HaklaiApp está disponível
if (typeof HaklaiApp === 'undefined') {
    console.error('HaklaiApp não está carregado');
}
```

#### **4. AJAX não funciona**
```php
// Verificar nonce
if (!wp_verify_nonce($_POST['nonce'], 'haklai_nonce')) {
    wp_die('Erro de segurança');
}

// Verificar permissões
if (!current_user_can('haklai_access_dashboard')) {
    wp_send_json_error('Sem permissão');
}
```

---

## 📈 **Performance**

### **Otimizações Implementadas**
- **CSS Minificado:** Arquivo consolidado e otimizado
- **JavaScript Modular:** Carregamento sob demanda
- **Queries Otimizadas:** Índices e consultas eficientes
- **Cache de Dados:** Armazenamento temporário de consultas
- **Lazy Loading:** Carregamento progressivo de componentes

### **Métricas de Performance**
- **Tempo de Carregamento:** < 3 segundos
- **Core Web Vitals:** Otimizado para Google PageSpeed
- **Mobile Performance:** Score 90+ em dispositivos móveis
- **Acessibilidade:** WCAG 2.1 AA compliant

---

## 🔒 **Segurança**

### **Medidas Implementadas**
- **Sanitização:** Todos os inputs são sanitizados
- **Validação:** Validação rigorosa de dados
- **Nonces:** Proteção contra CSRF
- **Capabilities:** Sistema de permissões granular
- **SQL Injection:** Prepared statements obrigatórios
- **XSS Protection:** Escape de outputs

### **Logs de Auditoria**
```php
// Exemplo de log de ação
Haklai_Logger::log_action(
    'member_added',
    get_current_user_id(),
    array('member_id' => $member_id)
);
```

---

## 🤝 **Contribuição**

### **Como Contribuir**
1. Fork o repositório
2. Crie uma branch para sua feature
3. Implemente suas mudanças
4. Teste thoroughly
5. Submeta um Pull Request

### **Padrões de Código**
- **PSR-4:** Autoloading de classes
- **WordPress Coding Standards:** Seguir padrões WP
- **Documentação:** Comentários em português
- **Testes:** Cobertura mínima de 80%

---

## 📞 **Suporte**

### **Canais de Suporte**
- **Email:** contato@ruthes.dev
- **Website:** https://ruthes.dev
- **Documentação:** https://docs.ruthes.dev/haklai

### **FAQ**
- **Q:** Como alterar as cores do sistema?
- **A:** Edite as variáveis CSS em `assets/css/haklai-main.css`

- **Q:** Como adicionar novos tipos de relatório?
- **A:** Estenda a classe `Haklai_Shortcodes` e adicione novos métodos

- **Q:** Como personalizar as permissões?
- **A:** Use o hook `haklai_user_permissions` para modificar permissões

---

## 📄 **Licença**

Este plugin é licenciado sob GPL v2 ou posterior.

```
Copyright (C) 2025 Jefter Ruthes

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

## 🎯 **Roadmap**

### **Versão 2.1.0**
- [ ] Sistema de notificações push
- [ ] Relatórios automáticos por email
- [ ] Integração com WhatsApp API
- [ ] App mobile (React Native)

### **Versão 2.2.0**
- [ ] Sistema de eventos
- [ ] Integração com calendário
- [ ] Backup automático na nuvem
- [ ] Analytics avançados

### **Versão 3.0.0**
- [ ] Multi-tenant completo
- [ ] API REST pública
- [ ] Integração com sistemas externos
- [ ] Machine Learning para insights

---

**Desenvolvido com ❤️ por [Jefter Ruthes](https://ruthes.dev)**

*Sistema de Gerenciamento de Membros de Igreja - Haklai App v2.0.0*
