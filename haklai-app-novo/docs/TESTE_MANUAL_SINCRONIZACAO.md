# 🧪 TESTE MANUAL - Sincronização Membros ↔ Usuários WordPress

**Data:** 09/10/2025  
**Versão:** 2.0.0  
**Status:** Pronto para teste

---

## 📋 PRÉ-REQUISITOS

Antes de começar os testes:

- [ ] Plugin Haklai ativo
- [ ] Pelo menos uma célula criada
- [ ] Email configurado no WordPress (ou plugin SMTP instalado)
- [ ] Acesso ao wp-admin como administrador

---

## 🎯 TESTE 1: Criar Membro como Líder (Novo)

### Objetivo
Verificar se ao criar um membro com nível de liderança, o sistema cria usuário WordPress automaticamente.

### Passos

1. **Acessar criação de membro**
   ```
   wp-admin > Haklai > Membros > Adicionar Membro
   ```

2. **Preencher dados**
   - Nome: `Carlos Teste Silva`
   - Email: `carlos.teste@example.com` (use email real para receber credenciais)
   - Telefone: `(11) 99999-1234`
   - Célula: Selecione qualquer célula existente
   - **Nível Hierárquico:** `Líder de Célula`
   - Status de Batismo: `Membro Batizado`
   - Status: `Ativo`

3. **Antes de salvar, observe:**
   - Deve aparecer um box **amarelo** com:
     ```
     ⚠️ Usuário WordPress
     SERÁ CRIADO
     ```
   - Mensagem: "Este membro tem nível de liderança. Ao salvar, um usuário WordPress será criado..."

4. **Clicar em "Publicar"**

5. **Após salvar, recarregar a página**

### Validações Esperadas

- [ ] Box agora é **azul** com `✓ VINCULADO`
- [ ] Mostra username criado (ex: `carlos.silva`)
- [ ] Mostra email do usuário
- [ ] Mostra role: `haklai_lider_celula`
- [ ] Mostra "Última sincronização" com data/hora
- [ ] Botão "Editar Usuário" presente

### Verificar no Banco de Dados

```sql
-- Verificar meta do membro
SELECT * FROM wp_postmeta 
WHERE post_id = [ID_DO_MEMBRO] 
AND meta_key = '_haklai_user_id';

-- Deve retornar o ID do usuário criado
```

### Verificar Usuário WordPress

1. Ir em `wp-admin > Usuários`
2. Procurar por `carlos.silva` (ou username gerado)
3. Clicar em "Editar"
4. **Verificar:**
   - [ ] Role: `Líder de Célula`
   - [ ] Email correto
   - [ ] Nome correto

### Verificar Email Enviado

1. Checar caixa de email de `carlos.teste@example.com`
2. **Deve ter recebido:**
   - [ ] Email com assunto "Bem-vindo ao Sistema Haklai"
   - [ ] Design HTML profissional
   - [ ] Username visível
   - [ ] Senha temporária visível
   - [ ] Link para login
   - [ ] Informações da função e célula

### Testar Login

1. Abrir navegador anônimo/privado
2. Acessar `wp-login.php`
3. Usar credenciais do email
4. **Validar:**
   - [ ] Login bem-sucedido
   - [ ] Redireciona para wp-admin
   - [ ] Pode acessar área Haklai

---

## 🎯 TESTE 2: Promover Membro Comum a Líder

### Objetivo
Verificar se ao editar um membro comum e torná-lo líder, o usuário é criado automaticamente.

### Passos

1. **Criar membro comum primeiro**
   ```
   wp-admin > Haklai > Membros > Adicionar Membro
   ```
   - Nome: `Maria Teste Santos`
   - Email: `maria.teste@example.com`
   - **Nível Hierárquico:** `Membro` (0)
   - Célula: Qualquer
   - Publicar

2. **Verificar que NÃO tem box de usuário**
   - Não deve mostrar informação sobre usuário WordPress
   - Apenas campos normais de membro

3. **Editar o membro**
   - Alterar **Nível Hierárquico** para: `Líder de Célula`
   - Deve aparecer box amarelo "SERÁ CRIADO"

4. **Atualizar**

5. **Recarregar página**

### Validações Esperadas

- [ ] Box azul `✓ VINCULADO` aparece
- [ ] Usuário WordPress foi criado
- [ ] Email com credenciais enviado
- [ ] Pode fazer login

---

## 🎯 TESTE 3: Criar Usuário WordPress com Role Haklai

### Objetivo
Verificar sincronização reversa: criar usuário WP deve vincular ou criar membro.

### Cenário A: Usuário com email de membro existente

1. **Ter um membro SEM usuário vinculado**
   - Criar membro com email `teste.vinculo@example.com`
   - Nível: `Membro` (0)
   - Salvar

2. **Criar usuário WordPress**
   ```
   wp-admin > Usuários > Adicionar Usuário
   ```
   - Username: `teste.manual`
   - Email: `teste.vinculo@example.com` (MESMO email do membro)
   - Nome: `Teste Vínculo`
   - Senha: Qualquer
   - **Role:** `Líder de Célula`
   - Adicionar Novo Usuário

3. **Voltar ao membro**
   - Editar o membro `teste.vinculo@example.com`
   
### Validações Esperadas

- [ ] Box `✓ VINCULADO` aparece
- [ ] Mostra username `teste.manual`
- [ ] `_haklai_user_id` preenchido
- [ ] Nível do membro atualizado para 1

### Cenário B: Usuário com email novo

1. **Criar usuário WordPress**
   ```
   wp-admin > Usuários > Adicionar Usuário
   ```
   - Username: `novo.usuario`
   - Email: `novo.usuario@example.com` (NÃO existe membro com esse email)
   - Nome: `Novo Usuário`
   - **Role:** `Discipulador`
   - Adicionar

2. **Verificar Haklai > Membros**

### Validações Esperadas

- [ ] Novo membro criado automaticamente
- [ ] Nome: `Novo Usuário`
- [ ] Email: `novo.usuario@example.com`
- [ ] Nível: 2 (Discipulador)
- [ ] Vínculo com usuário criado

---

## 🎯 TESTE 4: Sincronização de Dados

### Objetivo
Garantir que mudanças em membros sincronizam com usuários.

### Teste 4A: Alterar Email do Membro

1. Editar membro com usuário vinculado
2. Mudar email de `teste@example.com` para `novo.email@example.com`
3. Salvar

**Validações:**
- [ ] Email do usuário WP também atualizado
- [ ] Campo "Email" no perfil do usuário alterado

### Teste 4B: Alterar Célula do Membro

1. Editar membro vinculado
2. Mudar célula para outra
3. Salvar

**Validações:**
```sql
-- Verificar user_meta
SELECT * FROM wp_usermeta 
WHERE user_id = [ID_DO_USUARIO] 
AND meta_key = '_haklai_cell_id';
-- Deve mostrar novo ID da célula
```

### Teste 4C: Mudar Nível Hierárquico

1. Editar membro (Líder de Célula - nível 1)
2. Mudar para `Discipulador` (nível 2)
3. Salvar

**Validações:**
- [ ] Role do usuário WP alterado para `haklai_discipulador`
- [ ] `_haklai_role_level` no user_meta = 2

---

## 🎯 TESTE 5: Rebaixar Líder a Membro

### Objetivo
Verificar comportamento ao remover liderança.

### Passos

1. Editar membro que é líder (nível >= 1)
2. Alterar **Nível Hierárquico** para `Membro` (0)
3. Salvar

### Validações Esperadas

- [ ] Role do usuário WP alterado para `subscriber`
- [ ] `_haklai_role_level` no user_meta = 0
- [ ] Vínculo mantido (opcional: pode ser removido)
- [ ] Usuário perde acesso às funcionalidades de liderança

---

## 🎯 TESTE 6: Logs e Auditoria

### Objetivo
Verificar sistema de logs.

### Via PHP (Console ou Plugin)

```php
// Ver logs de sucesso
$logs = get_transient('haklai_sync_logs');
print_r($logs);

// Ver logs de erro
$errors = get_transient('haklai_sync_errors');
print_r($errors);
```

### Validações Esperadas

- [ ] Logs contêm ações realizadas
- [ ] Timestamps corretos
- [ ] IDs de membro e usuário registrados
- [ ] Tipo de ação clara ('created', 'linked_existing', etc.)

---

## 🎯 TESTE 7: Prevenir Duplicatas

### Objetivo
Garantir que não cria usuários duplicados.

### Passos

1. Criar membro com email `duplicata@example.com`, nível 1
2. Sistema cria usuário
3. Editar membro e salvar novamente
4. Editar membro e salvar mais uma vez

### Validações Esperadas

- [ ] Apenas 1 usuário criado
- [ ] Edições subsequentes apenas atualizam dados
- [ ] Não envia múltiplos emails
- [ ] Logs mostram 'update' ao invés de 'create'

---

## 🎯 TESTE 8: Email sem Configuração SMTP

### Objetivo
Testar comportamento sem plugin SMTP.

### Passos

1. Desativar plugin SMTP (se houver)
2. Criar membro como líder
3. Verificar email

### Comportamento Esperado

- [ ] Sistema usa `wp_mail()` nativo
- [ ] Email pode não chegar (servidor pode não ter SMTP configurado)
- [ ] Usuário é criado mesmo se email falhar
- [ ] Log registra tentativa de envio

### Recomendação

Instalar plugin SMTP para produção:
- WP Mail SMTP
- SendGrid
- Mailgun

---

## 🎯 TESTE 9: Caracteres Especiais no Nome

### Objetivo
Verificar geração de username com nomes complexos.

### Casos de Teste

| Nome                          | Username Esperado        |
|-------------------------------|--------------------------|
| João Pedro da Silva           | joao.silva               |
| María José García             | maria.garcia             |
| André Luís dos Santos Júnior  | andre.junior             |
| 李明 (chinês)                  | sanitizado               |

### Passos

1. Criar membro com cada nome acima
2. Verificar username gerado

**Validações:**
- [ ] Remove acentos
- [ ] Converte para minúsculas
- [ ] Usa ponto como separador
- [ ] Adiciona número se duplicado

---

## 🎯 TESTE 10: Performance com Múltiplos Membros

### Objetivo
Verificar que sincronização não causa lentidão.

### Passos

1. Criar 10 membros rapidamente
2. Alternar níveis entre 0 e 1
3. Salvar várias vezes

### Validações

- [ ] Salvamento rápido (< 3 segundos)
- [ ] Não trava o admin
- [ ] Previne loops infinitos (constante HAKLAI_SYNCING)

---

## ✅ CHECKLIST FINAL DE VALIDAÇÃO

### Funcionalidades Core

- [ ] Criar membro como líder cria usuário WP
- [ ] Promover membro a líder cria usuário WP
- [ ] Criar usuário WP vincula a membro existente
- [ ] Criar usuário WP cria membro se não existir
- [ ] Sincronização bidirecional funciona
- [ ] Todos os dados relevantes sincronizados (_haklai_cell_id, etc.)

### Email e Credenciais

- [ ] Email HTML enviado corretamente
- [ ] Username gerado é único
- [ ] Senha forte gerada
- [ ] Login funciona com credenciais recebidas

### Interface

- [ ] Box de status aparece corretamente
- [ ] Badge "VINCULADO" verde quando tem usuário
- [ ] Badge "SERÁ CRIADO" amarelo quando vai criar
- [ ] Informações do usuário exibidas corretamente
- [ ] Link "Editar Usuário" funciona

### Integridade de Dados

- [ ] Sem usuários duplicados
- [ ] Vínculos corretos (member_id ↔ user_id)
- [ ] Cell_id copiado para user_meta
- [ ] Role_level sincronizado
- [ ] Timestamps de sincronização salvos

### Edge Cases

- [ ] Membro sem email não cria usuário (mostra erro apropriado)
- [ ] Username duplicado adiciona número
- [ ] Caracteres especiais tratados
- [ ] Rebaixamento de líder funciona
- [ ] Deleção limpa vínculos

---

## 🐛 PROBLEMAS COMUNS E SOLUÇÕES

### Problema: Email não chega

**Solução:**
1. Verificar configuração SMTP
2. Instalar plugin WP Mail SMTP
3. Testar envio de email do WordPress

### Problema: Usuário não criado

**Verificar:**
1. Email do membro preenchido?
2. Erro nos logs? `get_transient('haklai_sync_errors')`
3. Role level >= 1?

### Problema: Célula não aparece após login

**Verificar:**
```sql
SELECT * FROM wp_usermeta 
WHERE user_id = [ID] 
AND meta_key = '_haklai_cell_id';
```

**Solução:** Re-salvar o membro para forçar sincronização

### Problema: Username duplicado

**Normal!** Sistema adiciona número automaticamente:
- `joao.silva`
- `joao.silva2`
- `joao.silva3`

---

## 📊 RELATÓRIO DE TESTE

Preencher após realizar todos os testes:

**Data do Teste:** _______________  
**Testado por:** _______________  
**Ambiente:** [ ] Local [ ] Staging [ ] Produção

### Resultados

- Total de testes: 10
- Testes passados: _____ / 10
- Testes falhados: _____ / 10
- Bugs encontrados: _____

### Bugs/Issues

1. ________________________________________________
2. ________________________________________________
3. ________________________________________________

### Aprovação

- [ ] ✅ **APROVADO** - Pronto para produção
- [ ] ⚠️ **APROVADO COM RESSALVAS** - Pequenos ajustes necessários
- [ ] ❌ **REPROVADO** - Correções críticas necessárias

---

**Assinatura:** ___________________  
**Data:** _______________

---

*Documento de teste - Versão 1.0*  
*Gerado em: 09/10/2025*











