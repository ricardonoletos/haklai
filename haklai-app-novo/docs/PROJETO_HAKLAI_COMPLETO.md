# 🏛️ **HAKLAI APP - SISTEMA DE GERENCIAMENTO DE MEMBROS**

## 📖 **VISÃO GERAL DO PROJETO**

**Projeto:** Haklai App - Sistema de Gerenciamento de Membros de Igreja  
**Desenvolvedor:** Ricardo Sarmento (https://linx.pt)  
**Versão:** 1.5.0  
**Tecnologia:** WordPress Plugin  
**Arquitetura:** Multi-tenant com hierarquias de permissões  

---

## 🎯 **OBJETIVOS DO SISTEMA**

### **PRINCIPAL:**
- Gerenciar membros de igreja em estrutura hierárquica
- Controlar presença em reuniões de célula
- Gerar relatórios e estatísticas
- Facilitar o discipulado e crescimento espiritual

### **TÉCNICOS:**
- Suportar 60-100 usuários simultâneos
- Interface responsiva e moderna
- Sistema de permissões robusto
- Dados seguros e escaláveis

---

## 🏗️ **ARQUITETURA DO SISTEMA**

### **HIERARQUIA DE USUÁRIOS:**
```
Nível 5: Pastor Supervisor    (haklai_supervisor)
Nível 4: Pastor Senior       (haklai_senior)
Nível 3: Pastor de Rede      (haklai_network)
Nível 2: Discipulador        (haklai_discipler)
Nível 1: Líder de Célula     (haklai_leader)
Nível 0: Membro              (haklai_member)
```

### **ESTRUTURA DE DADOS:**
- **Membros:** Dados pessoais, habilidades, status de batismo, formações
- **Células:** Grupos de membros com líderes
- **Presença:** Controle de frequência nas reuniões, e gestão de membros
- **Relatórios:** Estatísticas e análises 

---

## 🎨 **DESIGN SYSTEM APROVADO**

### **PALETA DE CORES:**
```css
--primary-color: #667eea
--secondary-color: #10b981
--accent-color: #f59e0b
--danger-color: #ef4444
--success-color: #10b981
--info-color: #06b6d4
```

### **TIPOGRAFIA:**
- **Fonte:** Inter (Google Fonts)
- **Tamanhos:** xs, sm, base, lg, xl, 2xl, 3xl, 4xl
- **Pesos:** 400, 500, 600, 700

### **COMPONENTES:**
- **Cards:** Bordas arredondadas, sombras suaves
- **Botões:** Gradientes, hover effects
- **Formulários:** Inputs modernos, validação visual
- **Tabelas:** Responsivas, hover states

---

## 📱 **PARTES DO PROJETO**

### **1. 🏠 DASHBOARD**
**Função:** Visão geral do sistema e métricas principais

**Funcionalidades:**
- Estatísticas gerais (membros, presença, batismos)
- Gráficos de presença por semana
- Notificações do sistema
- Lista de membros recentes
- Acesso rápido a funcionalidades

**Regras de Negócio:**
- **Líder de Célula:** Vê apenas dados da sua célula
- **Discipulador:** Vê dados das células que supervisiona
- **Pastor:** Vê dados de toda a rede/igreja
- **Admin:** Acesso total ao sistema

**Dados Exibidos:**
- Total de membros ativos
- Taxa de presença média
- Novos batismos do mês
- Células ativas
- Membros recentes (últimos 5)

---

### **2. ⚙️ CONFIGURAÇÕES**
**Função:** Gerenciar configurações do sistema e permissões

**Funcionalidades:**
- Informações da licença
- Dados do sistema (versão, performance)
- Configurações de reuniões (horários, dias)
- Permissões por nível hierárquico
- Configurações de backup

**Regras de Negócio:**
- **Apenas Pastores Senior/Supervisor:** Podem alterar configurações
- **Outros níveis:** Apenas visualização
- **Backup automático:** Configurável por frequência
- **Permissões:** Baseadas na hierarquia

**Configurações Disponíveis:**
- Horário padrão das reuniões
- Duração das reuniões
- Dias da semana para reuniões
- Frequência de backup
- Retenção de dados

---

### **3. 📊 RELATÓRIOS**
**Função:** Gerar relatórios detalhados e estatísticas

**Funcionalidades:**
- Relatório de presença
- Relatório de batismos
- Relatório de hierarquia
- Relatório de habilidades
- Filtros por período e célula
- Exportação em PDF/Excel

**Regras de Negócio:**
- **Período:** Última semana, mês, trimestre, ano, personalizado
- **Escopo:** Baseado no nível do usuário
- **Frequência:** Relatórios automáticos mensais
- **Acesso:** Hierárquico (célula → rede → igreja)

**Tipos de Relatórios:**
- **Presença:** Análise de frequência e padrões
- **Batismos:** Dados demográficos e crescimento
- **Hierarquia:** Estrutura organizacional
- **Habilidades:** Mapeamento de talentos

---

### **4. 🎯 CHECKPOINT**
**Função:** Ambiente produtivo para líderes de célula

**Funcionalidades:**
- Dashboard de presença da reunião
- Controles da reunião (data, horário)
- Lista de membros da célula
- Marcação de presença (presente/ausente toggle botton)
- Adição de novos membros (visitantes ou batizados)
- Envio de relatório da reunião

**Regras de Negócio:**
- **Exclusivo para Líderes de Célula:** Ambiente produtivo
- **Membros por padrão:** Aparecem como "ausentes"
- **Líder marca presença:** Clique para alternar status
- **Visitantes:** Adicionados à célula para validação
- **Relatório automático:** Enviado ao final da reunião

**Fluxo de Trabalho:**
1. Líder acessa o checkpoint
2. Vê lista de membros da célula
3. Marca presença de cada membro
4. Adiciona visitantes se necessário
5. Envia relatório da reunião

---

## 🔄 **REGRAS DE NEGÓCIO PRINCIPAIS**

### **STATUS DE MEMBROS:**
- **Visitante:** Novo na célula, sem batismo
- **Frequentador Assíduo:** 4+ presenças consecutivas
- **Membro Batizado:** Batizado, status permanente
- **Remoção de um membro batizado:** 16 ausências consecutivas

### **HIERARQUIA DE ACESSO:**
- **Membro:** não tem perfil nem acesso
- **Líder de Célula:** Sua célula + checkpoint
- **Discipulador:** 3-20 células supervisionadas
- **Pastor de Rede:** Todas as células da rede
- **Pastor Senior:** Múltiplas redes
- **Pastor Supervisor:** Sistema completo

### **PRESENÇA E FREQUÊNCIA:**
- **Reuniões semanais:** Dias configuráveis
- **Controle de presença:** Por reunião
- **Estatísticas:** Últimas 4 semanas
- **Alertas:** Ausências consecutivas, exclusão de membros, 

---

## 🛠️ **TECNOLOGIAS E ESTRUTURA**

### **BACKEND:**
- **WordPress:** CMS base
- **PHP 8.1+:** Linguagem principal
- **MySQL:** Banco de dados
- **Custom Post Types:** Estrutura de dados
- **AJAX:** Comunicação assíncrona

### **FRONTEND:**
- **HTML5:** Estrutura semântica
- **CSS3:** Design system moderno
- **JavaScript:** Interatividade
- **Bootstrap 5:** Grid e componentes
- **FontAwesome:** Ícones

### **ARQUITETURA:**
- **MVC:** Separação de responsabilidades
- **OOP:** Programação orientada a objetos
- **Hooks:** Integração com WordPress
- **Admin Pages e Shortcodes:** Renderização de conteúdo
- **AJAX:** Operações dinâmicas

---

## 📁 **ESTRUTURA DE ARQUIVOS**

```
haklai-app/
├── haklai-app.php                 # Arquivo principal
├── includes/
│   ├── core/                      # Classes principais
│   ├── models/                    # Modelos de dados
│   ├── services/                  # Serviços de negócio
│   └── class-haklai-data-seeder.php
├── public/
│   ├── assets/
│   │   ├── css/
│   │   │   └── haklai-main.css    # CSS consolidado
│   │   └── js/                    # Scripts JavaScript
│   ├── pages/                     # Páginas externas
│   └── shortcodes/                # Shortcodes do sistema
├── admin/                         # Área administrativa
└── languages/                     # Traduções
```

---

## 🎨 **PADRÕES DE DESIGN**

### **CONTAINERS:**
- **Max-width:** 1200px
- **Padding:** 1.5rem
- **Border-radius:** 16px
- **Box-shadow:** Sombra suave
- **Background:** Branco

### **HEADERS:**
- **Flexbox:** Espaço entre elementos
- **Avatar:** 40px, circular
- **Título:** Gradiente, 1.875rem
- **Subtítulo:** Cinza, 1.125rem

### **CARDS:**
- **Grid:** Responsivo, minmax(250px, 1fr)
- **Hover:** TranslateY(-2px), sombra
- **Ícones:** 2xl, centralizados
- **Números:** 3xl, cor primária

### **BOTÕES:**
- **Gradiente:** Cor primária
- **Hover:** TranslateY(-1px)
- **Ícones:** FontAwesome
- **Estados:** Primary, secondary, outline

---

## 🔐 **SISTEMA DE PERMISSÕES**

### **CAPABILITIES:**
- `haklai_access_dashboard`
- `haklai_access_reports`
- `haklai_access_settings`
- `haklai_access_checkpoint`
- `haklai_manage_members`
- `haklai_manage_cells`

### **HIERARQUIA:**
- **Nível 0:** Apenas visualização
- **Nível 1:** Gerenciar membros da célula
- **Nível 2:** Supervisar múltiplas células
- **Nível 3:** Gerenciar rede completa
- **Nível 4:** Acesso a múltiplas redes
- **Nível 5:** Acesso total ao sistema

---

## 📊 **BANCO DE DADOS**

### **TABELAS PRINCIPAIS:**
- `wp_haklai_members`: Dados dos membros
- `wp_haklai_cells`: Informações das células
- `wp_haklai_attendance`: Controle de presença
- `wp_haklai_reports`: Relatórios gerados
- `wp_haklai_alerts`: registo dos alertas do sistema.

### **CAMPOS IMPORTANTES:**
- `role_level`: Nível hierárquico (0-5)
- `baptism_status`: Status de batismo
- `is_visitor`: É visitante
- `visitor_count`: Contador de presenças
- `leader_id`: ID do líder associado
- `member_training`: Formações realizadas pelos membros CTL, CME, STP associada ao member_id
- `member_skills`: Habilidades do membro associada ao member_id (fotografia, musica, gastronomia, tecnologia etc..)
- `member_id`: ID do membro, que estará associado aos dados do registo e as demais cadeias hierarquicas do sistema..
---
