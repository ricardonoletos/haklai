# 📁 Estrutura de Documentação - Haklai Church Plugin

**Plugin:** Haklai Church Management System  
**Versão:** 2.0.0  
**Autor:** Ricardo Sarmento - https://linx.pt  
**Data de Organização:** 28/10/2025

---

## 📋 Índice

- [Estrutura](#estrutura)
- [Documentação Principal](#documentação-principal)
- [Backups](#backups)
- [Arquivos de Teste](#arquivos-de-teste)
- [Documentação Técnica](#documentação-técnica)

---

## 📂 Estrutura

```
docs/
├── README.md                              # Documentação geral do plugin
├── README_ORGANIZACAO.md                  # Este arquivo
├── backups/                               # Arquivos de backup
│   ├── backup__member_modal_refactor.php  # Backup de refatoração de modal
│   ├── backup_2025-01-27_before_member_unification/
│   └── backup_2025-10-17_17-51-19_before_image_optimization/
├── docs_MD/                               # Documentação Markdown por data/funcionalidade
├── test-*.html                            # Arquivos HTML de teste
└── *.md, *.txt                            # Documentação adicional
```

---

## 📚 Documentação Principal

### Documentação Geral

- **README.md** - Documentação completa do plugin
- **PROJETO.md** - Escopo e objetivos do projeto
- **PROJETO_HAKLAI_COMPLETO.md** - Visão completa do projeto Haklai
- **DOCUMENTACAO_COMPLETA_HAKLAI_APP.md** - Documentação técnica completa

### Documentação de Funcionalidades

- **CHECKPOINT_SINCRONIZACAO_MEMBROS_USUARIOS.md** - Sincronização de membros
- **VALIDACAO_TELEFONE_INTERNACIONAL.md** - Validação de telefones
- **DATABASE_SCHEMA_DOCUMENTATION.md** - Esquema do banco de dados
- **CHAT_CONTEXT_IMPLEMENTACAO_TEMPLATES.md** - Contexto de implementação
- **SHORTCODES_DOCUMENTATION.md** - Documentação de shortcodes
- **ESTRUTURA_BASICA_PLUGIN.md** - Estrutura básica do plugin

### Relatórios de Implementação

- **IMPLEMENTACAO_COMPLETA.txt** - Relatório completo de implementação
- **SESSAO_COMPLETA_13_10_2025.md** - Sessão completa de desenvolvimento
- **TESTE_MANUAL_SINCRONIZACAO.md** - Teste manual de sincronização

### Anotações e Testes

- **notas.txt** - Anotações gerais
- **TESTE_CONFIGURACOES_6_ABAS.txt** - Teste de configurações
- **TESTE_MENU_CONFIGURACOES.txt** - Teste de menu de configurações
- **TODOS_SHORTCODES_HAKLAI.txt** - Lista de todos os shortcodes
- **ACESSO_RAPIDO_RELATORIOS.txt** - Acesso rápido a relatórios
- **ACAO_EXECUTADA_SHORTCODE_CONFIG.txt** - Ações executadas
- **localhost.har** - Log do navegador (não incluído no git)

---

## 📂 Documentação Detalhada (docs_MD/)

Documentação organizada por data e funcionalidade:

### Funcionalidades Principais

- **SISTEMA_IMPORTACAO_EXPORTACAO_13_10_2025.md** - Sistema de importação/exportação
- **RESUMO_IMPLEMENTACAO_IMPORTACAO_EXPORTACAO.md** - Resumo da implementação
- **QUICK_START_IMPORTACAO.md** - Início rápido de importação
- **CHECKPOINT_F1_COMPLETO.md** - Checkpoint Fase 1 completa
- **CHECKPOINT_F2_MENU_ADMIN_COMPLETO.md** - Checkpoint Fase 2 - Menu Admin
- **CHECKPOINT_RELATORIOS_13_10_2025.md** - Sistema de relatórios
- **RELATORIOS_COMPLETO_10_10_2025.md** - Relatórios completo

### Configurações e Menus

- **CONFIGURACOES_COMPLETAS_6_ABAS_13_10_2025.md** - Configurações com 6 abas
- **CORRECAO_MENU_CONFIGURACOES_13_10_2025.md** - Correção do menu
- **NOTA_SHORTCODE_CONFIGURACOES_FASE_FINAL.md** - Nota sobre configurações

### Funcionalidades Específicas

- **LOGIN_CHECKPOINT_10_10_2025.md** - Login do checkpoint
- **PAGINA_BOAS_VINDAS_14_10_2025.md** - Página de boas-vindas
- **ACESSO_SHORTCODE_RELATORIOS.md** - Acesso via shortcode a relatórios
- **OTIMIZACAO_UPLOAD_IMAGENS_17_10_2025.md** - Otimização de upload

### Documentação de Projeto

- **Parte (1)_projeto.md** - Parte 1 do projeto
- **Parte (2)_projeto.md** - Parte 2 do projeto
- **Parte (3)_projeto.md** - Parte 3 do projeto
- **scrumm.MD** - Metodologia Scrum

---

## 💾 Backups

Arquivos de backup guardados para referência:

### Backup de Refatoração

- **backup__member_modal_refactor.php** - Backup antes da refatoração do modal de membros

### Backups por Data

- **backup_2025-01-27_before_member_unification/** - Backup antes da unificação de membros
  - checkpoint-template.php
  - haklai-app-novo.php
  - haklai-main.js

- **backup_2025-10-17_17-51-19_before_image_optimization/** - Backup antes da otimização de imagens
  - haklai-app-novo.php
  - haklai-main.css
  - haklai-main.js

---

## 🧪 Arquivos de Teste

Arquivos HTML para testes de interface:

- **test-checkpoint-final.html** - Teste final do checkpoint
- **test-configuracoes-checkpoint-style.html** - Teste de estilo das configurações
- **test-dashboard-checkpoint-style.html** - Teste de estilo do dashboard
- **test-relatorios-checkpoint-style.html** - Teste de estilo dos relatórios

---

## 🎯 Como Usar Esta Documentação

### Para Desenvolvedores

1. Comece por **README.md** para entender a estrutura geral
2. Leia **DOCUMENTACAO_COMPLETA_HAKLAI_APP.md** para visão técnica completa
3. Consulte **DATABASE_SCHEMA_DOCUMENTATION.md** para entender o banco
4. Revise **docs_MD/** para funcionalidades específicas por data

### Para Implementar Novas Features

1. Consulte **ESTRUTURA_BASICA_PLUGIN.md** para entender a base
2. Veja exemplos em **docs_MD/** de implementações anteriores
3. Revise **SHORTCODES_DOCUMENTATION.md** para criar novos shortcodes

### Para Troubleshooting

1. Consulte **SESSAO_COMPLETA_13_10_2025.md** para ver decisões importantes
2. Revise **TESTE_MANUAL_SINCRONIZACAO.md** para problemas de sincronização
3. Verifique **VALIDACAO_TELEFONE_INTERNACIONAL.md** para validações

---

## 📌 Convenções

### Nomenclatura de Arquivos

- **DATABASE_** - Esquema e documentação de banco de dados
- **TESTE_** - Arquivos de teste manual
- **CHECKPOINT_** - Checkpoints de desenvolvimento
- **IMPLEMENTACAO_** - Relatórios de implementação
- **SISTEMA_** - Documentação de sistemas específicos

### Padrão de Data

Arquivos com data seguem o padrão: `NOME_DD_MM_YYYY.md`

Exemplo: `CHECKPOINT_RELATORIOS_13_10_2025.md`

---

## ✅ Status da Organização

- [x] Documentação movida para `docs/`
- [x] Backups organizados em `docs/backups/`
- [x] Documentação técnica em `docs/docs_MD/`
- [x] Arquivos de teste identificados
- [x] README criado

---

## 🔗 Links Úteis

- **Plugin Principal:** `haklai-app-novo.php`
- **Documentação Completa:** `docs/DOCUMENTACAO_COMPLETA_HAKLAI_APP.md`
- **README Principal:** `docs/README.md`

---

**Última atualização:** 28/10/2025  
**Mantido por:** Ricardo Sarmento - https://linx.pt


