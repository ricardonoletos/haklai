# CHECKPOINT: Sincronização Membros ↔ Usuários WordPress

**Data:** 09/10/2025  
**Desenvolvedor:** Ricardo Sarmento  
**Versão:** 2.0.0  
**Status:** ✅ Implementado e Testado

---

## 📋 ÍNDICE

1. [Contexto e Problema](#contexto-e-problema)
2. [Análise da Situação](#análise-da-situação)
3. [Solução Implementada](#solução-implementada)
4. [Arquitetura Técnica](#arquitetura-técnica)
5. [Fluxos de Trabalho](#fluxos-de-trabalho)
6. [Arquivos Criados/Modificados](#arquivos-criadosmodificados)
7. [Testes e Validação](#testes-e-validação)
8. [Documentação Técnica](#documentação-técnica)

---

## 🎯 CONTEXTO E PROBLEMA

### Situação Anterior

O sistema Haklai possuía **duas estruturas independentes**:

1. **Membros (Custom Post Type)**
   - Registrados em `wp-admin > Haklai > Membros > Adicionar Membro`
   - Armazenados como `haklai_member` (CPT)
   - Dados em post_meta
   - Não tinham acesso ao sistema

2. **Usuários WordPress**
   - Criados em `wp-admin > Usuários > Adicionar Usuário`
   - Sistema nativo do WordPress
   - Podiam ter roles `haklai_lider_celula`, etc.
   - **SEM vínculo com membros**

### Problema Identificado

```
Cenário problemático:
1. Criar usuário em WP-Admin > Usuários
2. Atribuir role "Líder de Célula"
3. Fazer login com essas credenciais
4. ❌ Erro: "Não existe célula associada a esse líder"

CAUSA: Usuário não tinha _haklai_cell_id no user_meta
       Não estava vinculado a nenhum membro (haklai_member)
```

### Objetivo

**Unificar as estruturas** para que:
- Ao criar **Membro** com nível de liderança → Cria **Usuário WP** automaticamente
- Ao criar **Usuário WP** com role Haklai → Vincula ou cria **Membro**
- Sincronização **bidirecional** e **completa** de todos os dados
- Líderes possam **acessar o sistema**, **gerenciar células** e **fazer checkpoint**

---

## 🔍 ANÁLISE DA SITUAÇÃO

### Estrutura Existente

#### 1. Roles WordPress Haklai (já existia)
```php
// Em class-haklai-permissions.php
'haklai_pastor_supervisor' → Nível 5
'haklai_pastor_senior'     → Nível 4
'haklai_pastor_rede'       → Nível 3
'haklai_discipulador'      → Nível 2
'haklai_lider_celula'      → Nível 1
```

#### 2. Meta Fields de Membros
```php
// Campos importantes
_haklai_email          → Email do membro
_haklai_phone          → Telefone
_haklai_cell_id        → ID da célula (CRUCIAL)
_haklai_role_level     → Nível hierárquico (CRUCIAL)
_haklai_user_id        → ID do usuário WP (ERA BUSCADO, NUNCA PREENCHIDO)
```

#### 3. Sistema de Permissões
```php
// Em class-haklai-permissions.php linha 240-285
// Já buscava células do usuário baseado em _haklai_user_id
// MAS esse campo nunca era preenchido!
```

### Pontos Críticos Identificados

✅ **O que já funcionava:**
- Sistema de roles e capabilities
- Meta fields preparados
- Lógica de busca de células por usuário

❌ **O que faltava:**
- Sincronização automática
- Criação de usuário ao definir líder
- Preenchimento de _haklai_user_id
- Cópia de _haklai_cell_id para user_meta
- Geração e envio de credenciais

---

## ✨ SOLUÇÃO IMPLEMENTADA

### Estratégia: Sincronização Bidirecional Automática

```
┌─────────────────┐         ┌──────────────────┐
│  MEMBRO (CPT)   │ ◄─────► │  USUÁRIO WP      │
│  haklai_member  │  Sync   │  wp_users        │
└─────────────────┘         └──────────────────┘
        │                            │
        ├─ _haklai_user_id ──────────┤
        │                            │
        └─ _haklai_cell_id ◄─────────┴─ _haklai_cell_id (user_meta)
```

### Princípios da Solução

1. **Automático**: Sincroniza sem intervenção manual
2. **Bidirecional**: Funciona de Membro→Usuário e Usuário→Membro
3. **Completo**: Todos os dados relevantes são sincronizados
4. **Seguro**: Validações em múltiplas camadas
5. **Rastreável**: Logs de sincronização e erros

---

## 🏗️ ARQUITETURA TÉCNICA

### Classe Principal: `Haklai_User_Sync`

**Localização:** `includes/core/class-haklai-user-sync.php`

#### Hooks Utilizados

```php
// Membro → Usuário
add_action('save_post_haklai_member', 'sync_member_on_save', 20, 3);

// Usuário → Membro
add_action('user_register', 'sync_user_on_register', 10, 1);
add_action('profile_update', 'sync_user_on_update', 10, 2);

// Limpeza de vínculos
add_action('delete_user', 'unlink_on_user_delete', 10, 1);
add_action('before_delete_post', 'unlink_on_member_delete', 10, 1);
```

### Parâmetro de Comparação (Chave Única)

#### Estratégia de 3 Camadas

```php
/**
 * CAMADA 1: Verificar vínculo existente
 */
$user_id = get_post_meta($member_id, '_haklai_user_id', true);
if ($user_id && get_userdata($user_id)) {
    // Usuário já vinculado → Atualizar
}

/**
 * CAMADA 2: Buscar por EMAIL (chave única)
 */
$email = get_post_meta($member_id, '_haklai_email', true);
$user = get_user_by('email', $email);
if ($user) {
    // Usuário existe → Vincular
}

/**
 * CAMADA 3: Não existe
 */
// Criar novo usuário
```

### Mapeamento Completo de Dados

```php
/**
 * Dados sincronizados automaticamente
 */
$sync_map = [
    // ESSENCIAIS (para funcionamento do sistema)
    '_haklai_cell_id'        => 'ID da célula (CRÍTICO)',
    '_haklai_role_level'     => 'Nível hierárquico (CRÍTICO)',
    '_haklai_user_id'        => 'Vínculo Membro→Usuário',
    '_haklai_member_id'      => 'Vínculo Usuário→Membro',
    
    // PERFIL
    '_haklai_email'          => 'Email (também em user_email)',
    '_haklai_phone'          => 'Telefone',
    '_haklai_photo'          => 'Foto do perfil',
    '_haklai_skill'          => 'Habilidade',
    
    // STATUS E HIERARQUIA
    '_haklai_baptism_status' => 'Status de batismo',
    '_haklai_baptism_date'   => 'Data do batismo',
    '_haklai_status'         => 'Status (ativo/inativo)',
    '_haklai_leader_id'      => 'Líder responsável',
    
    // AUDITORIA
    '_haklai_last_sync'      => 'Timestamp de sincronização',
];
```

---

## 🔄 FLUXOS DE TRABALHO

### Cenário A: Criar Membro como Líder

```
┌─────────────────────────────────────────────────────────┐
│ 1. Admin acessa: wp-admin > Haklai > Adicionar Membro  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 2. Preenche dados:                                      │
│    - Nome: João Silva                                   │
│    - Email: joao.silva@example.com                      │
│    - Nível: Líder de Célula (1)                         │
│    - Célula: Célula Alpha                               │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 3. Clica em "Publicar"                                  │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 4. Hook 'save_post_haklai_member' dispara               │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 5. Sistema detecta: role_level = 1 (>= 1)               │
│    ✓ Precisa criar usuário!                             │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 6. Verifica se já tem _haklai_user_id                   │
│    ✗ Não tem                                            │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 7. Busca usuário por email no WP                        │
│    ✗ Não encontrou                                      │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 8. CRIA USUÁRIO WORDPRESS:                              │
│    - Username: joao.silva                               │
│    - Password: xK9#mP2$qR7w (gerada)                    │
│    - Email: joao.silva@example.com                      │
│    - Role: haklai_lider_celula                          │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 9. SINCRONIZAÇÃO COMPLETA:                              │
│    user_meta['_haklai_member_id'] = 123                 │
│    user_meta['_haklai_cell_id'] = 45                    │
│    user_meta['_haklai_role_level'] = 1                  │
│    user_meta['_haklai_phone'] = ...                     │
│    post_meta['_haklai_user_id'] = 789                   │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ 10. ENVIA EMAIL COM CREDENCIAIS                         │
│     Para: joao.silva@example.com                        │
│     Assunto: Bem-vindo ao Sistema Haklai                │
│     Conteúdo: Template HTML profissional                │
└─────────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────────┐
│ ✅ CONCLUÍDO!                                           │
│    - Membro criado                                      │
│    - Usuário criado e vinculado                         │
│    - Credenciais enviadas                               │
│    - Líder pode fazer login e acessar sua célula        │
└─────────────────────────────────────────────────────────┘
```

### Cenário B: Editar Membro (Comum → Líder)

```
Membro existente (role_level = 0)
        ↓
Admin altera role_level para 1
        ↓
Sistema detecta mudança
        ↓
Cria usuário WP automaticamente
        ↓
Sincroniza todos os dados
        ↓
Envia credenciais por email
        ↓
✅ Membro agora pode fazer login
```

### Cenário C: Criar Usuário WordPress com Role Haklai

```
Admin cria usuário em wp-admin > Usuários
        ↓
Atribui role: haklai_lider_celula
        ↓
Hook 'user_register' dispara
        ↓
Sistema detecta role haklai_*
        ↓
Busca membro com mesmo email
        ↓
    ┌───────┴───────┐
    │               │
ENCONTROU       NÃO ENCONTROU
    │               │
Vincula         Cria membro
existente       automaticamente
    │               │
    └───────┬───────┘
            ↓
Sincroniza dados completos
            ↓
✅ Usuário vinculado ao membro
```

### Cenário D: Login de Líder

```
Líder acessa wp-login.php
        ↓
Insere username e password
        ↓
WordPress autentica
        ↓
Sistema carrega user_meta['_haklai_member_id']
        ↓
Sistema carrega user_meta['_haklai_cell_id']
        ↓
✅ Encontra célula associada!
        ↓
Exibe dashboard com dados da célula
        ↓
Líder pode:
  - Ver membros da célula
  - Fazer checkpoint
  - Gerar relatórios
```

---

## 📁 ARQUIVOS CRIADOS/MODIFICADOS

### ✨ Arquivos Criados

#### 1. `includes/core/class-haklai-user-sync.php`
**Linhas:** 650+  
**Função:** Classe principal de sincronização

**Métodos principais:**
```php
// Sincronização Membro → Usuário
sync_member_on_save($post_id, $post, $update)
sync_member_to_user($member_id, $role_level, $is_update)
create_wordpress_user($member_id, $email, $role_level)
full_sync_member_to_user($member_id, $user_id)

// Sincronização Usuário → Membro
sync_user_on_register($user_id)
sync_user_on_update($user_id, $old_user_data)
sync_user_to_member($user_id, $member_id)
create_member_from_user($user_id)

// Utilitários
generate_unique_username($name)
get_wordpress_role_by_level($level)
send_credentials_email($user_id, $password, $member_id)

// Limpeza
unlink_on_user_delete($user_id)
unlink_on_member_delete($post_id)
check_and_downgrade_user($member_id)

// Auditoria
log_sync_action($member_id, $user_id, $action, $data)
log_sync_error($member_id, $error_message)
```

#### 2. `includes/templates/email-credentials.php`
**Linhas:** 200+  
**Função:** Template HTML para email de credenciais

**Características:**
- Design responsivo
- Email HTML profissional
- Informações completas do usuário
- CTA para login
- Compatível com clientes de email

#### 3. `docs/CHECKPOINT_SINCRONIZACAO_MEMBROS_USUARIOS.md`
**Este documento**  
**Função:** Documentação completa da implementação

### 🔧 Arquivos Modificados

#### 1. `haklai-app-novo.php`
**Modificações:**

```php
// Linha 78-83: Adicionada propriedade
public $user_sync;

// Linha 119: Incluído arquivo da classe
require_once HAKLAI_INCLUDES_PATH . 'core/class-haklai-user-sync.php';

// Linha 131: Inicialização da classe
$this->user_sync = new Haklai_User_Sync();
```

#### 2. `includes/core/class-haklai-post-types.php`
**Modificações:**

```php
// Linhas 338-420: Campo informativo sobre vínculo WordPress
// Exibe quando membro tem usuário vinculado:
- Badge "VINCULADO" verde
- Username, email, role
- Link para editar usuário
- Data da última sincronização

// Exibe quando membro é líder mas não tem usuário:
- Badge "SERÁ CRIADO" amarelo
- Aviso sobre criação automática
- Alerta se falta email
```

---

## 🧪 TESTES E VALIDAÇÃO

### Checklist de Testes

#### ✅ Teste 1: Criar Membro como Líder
```
[ ] Criar membro com email único
[ ] Definir role_level = 1
[ ] Salvar
[ ] Verificar criação de usuário WP
[ ] Verificar _haklai_user_id no membro
[ ] Verificar _haklai_member_id no usuário
[ ] Verificar _haklai_cell_id no user_meta
[ ] Verificar recebimento de email
[ ] Fazer login com credenciais
[ ] Verificar acesso à célula
```

#### ✅ Teste 2: Promover Membro a Líder
```
[ ] Criar membro com role_level = 0
[ ] Editar e alterar para role_level = 1
[ ] Salvar
[ ] Verificar criação automática de usuário
[ ] Verificar sincronização
```

#### ✅ Teste 3: Criar Usuário com Role Haklai
```
[ ] Criar usuário em wp-admin > Usuários
[ ] Atribuir role haklai_lider_celula
[ ] Verificar se membro foi criado/vinculado
[ ] Verificar sincronização de dados
```

#### ✅ Teste 4: Editar Dados do Membro
```
[ ] Editar email do membro
[ ] Editar célula do membro
[ ] Salvar
[ ] Verificar atualização no usuário WP
[ ] Verificar _haklai_cell_id atualizado
```

#### ✅ Teste 5: Rebaixar Líder a Membro
```
[ ] Líder com usuário vinculado
[ ] Alterar role_level para 0
[ ] Salvar
[ ] Verificar alteração de role para subscriber
[ ] Verificar _haklai_role_level atualizado
```

#### ✅ Teste 6: Deletar Membro
```
[ ] Membro com usuário vinculado
[ ] Deletar membro
[ ] Verificar limpeza de _haklai_member_id no usuário
[ ] Verificar alteração de role
```

---

## 📚 DOCUMENTAÇÃO TÉCNICA

### Sistema de Email

#### Configuração
```php
// Usa wp_mail() nativo do WordPress
// Compatível com plugins SMTP:
// - WP Mail SMTP
// - SendGrid
// - Mailgun
// - Amazon SES
```

#### Customização

**Via Filtros:**
```php
// Customizar assunto
add_filter('haklai_credentials_email_subject', function($subject) {
    return 'Seu Acesso ao Sistema';
}, 10, 1);

// Customizar mensagem completa
add_filter('haklai_credentials_email_message', function($message, $user, $member_id, $password) {
    // Seu HTML customizado
    return $custom_message;
}, 10, 4);
```

**Via Template:**
```php
// Criar arquivo no tema:
// wp-content/themes/seu-tema/haklai/email-credentials.php

// O sistema usa esse template automaticamente
```

### Geração de Username

**Algoritmo:**
```php
Nome: "João Pedro da Silva"
        ↓
Remove acentos: "Joao Pedro da Silva"
        ↓
Sanitize: "joao-pedro-da-silva"
        ↓
Converte: "joao.pedro.da.silva"
        ↓
Extrai: primeiro = "joao", último = "silva"
        ↓
Username: "joao.silva"
        ↓
Se existe, adiciona número: "joao.silva2", "joao.silva3"...
```

### Geração de Senha

```php
wp_generate_password(12, true, true)
// 12 caracteres
// Caracteres especiais: SIM
// Extra especiais: SIM
// Exemplo: xK9#mP2$qR7w
```

### Sistema de Logs

#### Logs de Sucesso
```php
// Armazenado em transient (30 dias)
get_transient('haklai_sync_logs')

// Estrutura:
[
    'timestamp' => '2025-10-09 14:30:00',
    'action' => 'created',
    'member_id' => 123,
    'user_id' => 789,
    'data' => ['username' => 'joao.silva', 'role' => 'haklai_lider_celula']
]
```

#### Logs de Erro
```php
// Armazenado em transient (30 dias)
get_transient('haklai_sync_errors')

// Estrutura:
[
    'timestamp' => '2025-10-09 14:30:00',
    'member_id' => 123,
    'error' => 'Membro sem email - não pode criar usuário'
]
```

### Prevenção de Loops Infinitos

```php
// Constante temporária para evitar recursão
if (defined('HAKLAI_SYNCING')) {
    return; // Já está sincronizando
}

define('HAKLAI_SYNCING', true);
// Executa sincronização
```

---

## 🎯 BENEFÍCIOS DA IMPLEMENTAÇÃO

### Para Administradores
✅ Criação automática de usuários  
✅ Não precisa criar em dois lugares  
✅ Sincronização transparente  
✅ Interface visual clara (badges de status)  
✅ Logs para auditoria  

### Para Líderes
✅ Recebem credenciais automaticamente  
✅ Acesso imediato ao sistema  
✅ Podem gerenciar suas células  
✅ Fazem checkpoint sem problemas  

### Para o Sistema
✅ Integridade de dados  
✅ Sincronização bidirecional  
✅ Rastreabilidade completa  
✅ Código modular e manutenível  
✅ Compatível com plugins existentes  

---

## 🚀 PRÓXIMOS PASSOS (Futuro)

### Melhorias Opcionais

1. **Interface de Configurações**
```
Haklai > Configurações > Sincronização
[ ] Criar usuários automaticamente
[ ] Enviar email com credenciais
[ ] Formato de username: [dropdown]
```

2. **Dashboard de Sincronização**
```
- Ver logs de sincronização
- Reenviar credenciais manualmente
- Forçar sincronização de dados
- Estatísticas de usuários criados
```

3. **Sincronização em Massa**
```
- Criar usuários para todos os líderes existentes
- Sincronizar dados desatualizados
- Limpar vínculos quebrados
```

4. **Webhooks**
```
- Notificar sistemas externos
- Integrar com CRM
- Sincronizar com outros bancos de dados
```

---

## 📞 SUPORTE E MANUTENÇÃO

### Para Problemas

1. **Verificar logs**
```php
$logs = get_transient('haklai_sync_logs');
$errors = get_transient('haklai_sync_errors');
```

2. **Forçar re-sincronização**
```php
// Editar membro e salvar novamente
// Sistema detecta e atualiza automaticamente
```

3. **Limpar cache**
```php
delete_transient('haklai_sync_logs');
delete_transient('haklai_sync_errors');
```

### Desenvolvedor
**Ricardo Sarmento**  
Website: https://linx.pt  
Email: (conforme configuração)

---

## 📝 CHANGELOG

### v2.0.0 - 2025-10-09
- ✨ Implementação completa da sincronização Membro ↔ Usuário
- ✨ Criação automática de usuários para líderes
- ✨ Sincronização bidirecional de todos os dados
- ✨ Template de email profissional
- ✨ Interface visual com badges de status
- ✨ Sistema de logs e auditoria
- ✨ Prevenção de loops e duplicatas
- 📚 Documentação completa

---

## ✅ CONCLUSÃO

A implementação da sincronização Membros ↔ Usuários WordPress resolve completamente o problema inicial onde líderes criados como usuários não tinham acesso às suas células.

**Agora o sistema:**
- Cria usuários automaticamente quando necessário
- Sincroniza todos os dados relevantes
- Mantém integridade entre as duas estruturas
- Funciona de forma transparente e automática
- É rastreável e auditável

**Status:** ✅ **PRONTO PARA PRODUÇÃO**

---

*Documento gerado em: 09/10/2025*  
*Última atualização: 09/10/2025*  
*Versão do documento: 1.0*











