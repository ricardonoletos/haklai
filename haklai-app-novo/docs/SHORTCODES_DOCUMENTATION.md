# 📋 **DOCUMENTAÇÃO DOS SHORTCODES - HAKLAI APP**

## 🎯 **Visão Geral**

O sistema Haklai App utiliza shortcodes para renderizar as diferentes páginas do sistema. Cada shortcode corresponde a um módulo específico e possui suas próprias funcionalidades e permissões.

---

## 📱 **Shortcodes Disponíveis**

### **1. 🏠 [Haklai_dashboard]**
**Função:** Exibe o dashboard principal do sistema

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

---

### **2. ⚙️ [Haklai_configuracoes]**
**Função:** Exibe as configurações do sistema

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

---

### **3. 📊 [Haklai_relatorios]**
**Função:** Exibe sistema de relatórios e estatísticas

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

---

### **4. 🎯 [Haklai_checkpoint]**
**Função:** Ambiente produtivo para líderes de célula

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

## 🔐 **Sistema de Permissões**

### **Capabilities Necessárias:**
```php
// Dashboard
haklai_access_dashboard

// Configurações
haklai_access_settings

// Relatórios
haklai_access_reports

// Checkpoint
haklai_access_checkpoint

// Gerenciamento
haklai_manage_members
haklai_manage_cells
```

### **Hierarquia de Acesso:**
```
Nível 5: Pastor Supervisor    - Acesso total
Nível 4: Pastor Senior       - Configurações limitadas
Nível 3: Pastor de Rede      - Rede específica
Nível 2: Discipulador        - Células supervisionadas
Nível 1: Líder de Célula     - Apenas sua célula
Nível 0: Membro              - Sem acesso aos shortcodes
```

---

## 🎨 **Design System**

### **Cores Principais:**
```css
--primary-color: #667eea
--secondary-color: #10b981
--accent-color: #f59e0b
--danger-color: #ef4444
--success-color: #10b981
--info-color: #06b6d4
```

### **Componentes:**
- **Headers:** Gradiente com animação flutuante
- **Cards:** Bordas arredondadas, sombras suaves, hover effects
- **Botões:** Gradientes, estados hover, loading
- **Formulários:** Inputs modernos, validação visual
- **Tabelas:** Responsivas, hover states
- **Modais:** Headers com gradiente, animações suaves

---

## 📱 **Responsividade**

### **Breakpoints:**
- **Desktop:** 1200px+
- **Tablet:** 768px - 1199px
- **Mobile:** < 768px

### **Adaptações Mobile:**
- Grids se tornam coluna única
- Cards menores com menos padding
- Botões em pilha vertical
- Modais em tela cheia
- Tabelas com scroll horizontal

---

## ⚡ **Funcionalidades JavaScript**

### **Interatividade:**
- **Dashboard:** Atualização de métricas, gráficos dinâmicos
- **Configurações:** Toggles de permissões, backup automático
- **Relatórios:** Filtros dinâmicos, preview de relatórios
- **Checkpoint:** Toggle de presença, busca de membros

### **AJAX:**
- Todas as operações são assíncronas
- Feedback visual em tempo real
- Validação de formulários
- Notificações de sucesso/erro

---

## 🔧 **Implementação Técnica**

### **Estrutura de Arquivos:**
```
includes/
├── shortcodes/
│   ├── class-haklai-shortcodes.php
│   └── templates/
│       ├── dashboard-template.php
│       ├── configuracoes-template.php
│       ├── relatorios-template.php
│       └── checkpoint-template.php
├── core/
│   ├── class-haklai-roles.php
│   ├── class-haklai-db.php
│   └── ...
assets/
├── css/
│   └── haklai-main.css
└── js/
    └── haklai-main.js
```

### **Assets Carregados:**
- **CSS:** Bootstrap 5, FontAwesome 6, Google Fonts (Inter)
- **JS:** jQuery, Bootstrap 5, Haklai Main JS
- **Localização:** URLs AJAX, nonces, dados do usuário

---

## 📋 **Exemplos de Uso**

### **Página Completa:**
```php
<!-- Página do Dashboard -->
[Haklai_dashboard]

<!-- Página de Configurações -->
[Haklai_configuracoes]

<!-- Página de Relatórios -->
[Haklai_relatorios]

<!-- Página do Checkpoint -->
[Haklai_checkpoint]
```

### **Combinando Shortcodes:**
```php
<!-- Página com múltiplos módulos -->
<div class="haklai-tabs">
    <ul class="nav nav-tabs">
        <li><a href="#dashboard" data-toggle="tab">Dashboard</a></li>
        <li><a href="#relatorios" data-toggle="tab">Relatórios</a></li>
    </ul>
    <div class="tab-content">
        <div id="dashboard" class="tab-pane active">
            [Haklai_dashboard]
        </div>
        <div id="relatorios" class="tab-pane">
            [Haklai_relatorios]
        </div>
    </div>
</div>
```

---

## 🚀 **Personalização**

### **CSS Customizado:**
```css
/* Personalizar cores */
:root {
    --primary-color: #sua-cor;
    --secondary-color: #sua-cor;
}

/* Personalizar layout */
.dashboard-container {
    max-width: 1600px; /* Largura maior */
}
```

### **JavaScript Customizado:**
```javascript
// Hook personalizado
jQuery(document).ready(function($) {
    // Sua lógica personalizada
    $('.haklai-custom-element').on('click', function() {
        // Ação personalizada
    });
});
```

---

## 🐛 **Troubleshooting**

### **Problemas Comuns:**

1. **Shortcode não aparece:**
   - Verificar se o usuário tem permissão
   - Verificar se os assets estão carregados
   - Verificar logs de erro do WordPress

2. **Estilos não aplicados:**
   - Verificar conflitos com tema
   - Verificar se o CSS está sendo carregado
   - Verificar z-index de elementos

3. **JavaScript não funciona:**
   - Verificar console do navegador
   - Verificar se jQuery está carregado
   - Verificar conflitos com outros scripts

4. **AJAX não funciona:**
   - Verificar nonce
   - Verificar permissões do usuário
   - Verificar logs do servidor

---

## 📞 **Suporte**

Para suporte técnico ou dúvidas sobre implementação:

- **Desenvolvedor:** Jefter Ruthes
- **Website:** https://ruthes.dev
- **Email:** contato@ruthes.dev

---

**Esta documentação está sempre atualizada com a versão mais recente do plugin Haklai App.**
