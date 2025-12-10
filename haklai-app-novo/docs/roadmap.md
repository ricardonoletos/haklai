# 🗺️ **ROADMAP DE IMPLEMENTAÇÃO - HAKLAI APP**

**Data de Criação:** Janeiro 2025  
**Versão do Plugin:** 2.0.0  
**Desenvolvedor:** Ricardo Sarmento - https://linx.pt

---

## 📋 **RESUMO EXECUTIVO**

Este roadmap organiza todas as alterações solicitadas neste chat, priorizando a estabilidade do sistema e evitando quebras. A implementação será feita em **5 fases sequenciais**, com validação entre cada etapa.

**Princípio Fundamental:** Não quebrar funcionalidades existentes que já estão validadas e funcionando.

---

## 🎯 **FASE 1: CORREÇÃO DOS CARDS DE ESTATÍSTICAS** ⚠️ **CRÍTICO** ✅ **CONCLUÍDA**

### **Objetivo**
Corrigir o problema dos cards que aparecem com valores corretos por um segundo e depois zeram, usando o mesmo método de contagem da página `admin-welcome-page.php`.

### **Status Atual**
- ✅ **Implementado:** `relatorios-template.php` já usa `wp_count_posts()` para Total de Membros
- ✅ **Implementado:** JavaScript protegido para não sobrescrever valores na página de relatórios
- ✅ **Implementado:** Script de proteção robusto com MutationObserver no template

### **Alterações Realizadas**

#### **1.1 Arquivo: `includes/templates/relatorios-template.php`**
- ✅ Usar `wp_count_posts('haklai_member')->publish` para Total de Membros (já estava implementado)
- ✅ Adicionado script inline robusto para proteger valores contra sobrescrita do JavaScript
- ✅ Implementado MutationObserver para detectar e reverter mudanças não autorizadas
- ✅ Múltiplas camadas de proteção com timeouts para garantir valores corretos
- **Status:** ✅ **CONCLUÍDO**

#### **1.2 Arquivo: `assets/js/haklai-main.js`**
- ✅ Verificação adicionada no início do método `updateStats()` para não executar na página de relatórios
- ✅ Adicionada verificação: `if ($('.reports-container').length > 0) return;`
- ✅ Comentário explicativo adicionado sobre a FASE 1
- **Status:** ✅ **CONCLUÍDO**

### **Arquivos Afetados**
- `includes/templates/relatorios-template.php` (modificar)
- `assets/js/haklai-main.js` (modificar)

### **Riscos**
- 🟢 **Baixo** - Apenas correção de bug existente

### **Testes Necessários**
- ✅ Verificar se cards mantêm valores após carregamento completo
- ✅ Verificar se não há conflito com outras páginas (checkpoint, dashboard)

---

## 🎯 **FASE 2: ALTERAÇÃO DOS CARDS DE ESTATÍSTICAS** ✅ **CONCLUÍDA**

### **Objetivo**
Modificar os cards conforme solicitado:
1. **Reuniões (Mês)** → **Formações (CME, CTL, STP em porcentagem)**
2. **Células Ativas** → **Total de F.A (Frequentador Assíduo)**
3. **Adicionar:** **Visitantes (30 dias)**

### **Status Atual**
- ✅ **Implementado:** Método `get_dashboard_statistics()` modificado com novas estatísticas
- ✅ **Implementado:** Cards atualizados no template `relatorios-template.php`
- ✅ **Implementado:** Estilos CSS adicionados para card de formações
- ✅ **Implementado:** Script de proteção atualizado para novos cards

### **Alterações Realizadas**

#### **2.1 Arquivo: `includes/shortcodes/class-haklai-shortcodes.php`**
**Método:** `get_dashboard_statistics()` (linhas 429-560)

**Removido:**
- ✅ Contagem de reuniões (`total_meetings`) - removida
- ✅ Contagem de células (`total_cells`) - removida do retorno (mantida internamente se necessário)

**Adicionado:**
- ✅ Contagem de F.A (`_haklai_baptism_status = 'frequent_visitor'`) - `total_fa`
- ✅ Contagem de formações (CME, CTL, STP) com porcentagens - `formations` array
- ✅ Contagem de visitantes últimos 30 dias - `total_visitors_30days`

**Lógica Implementada:**
- Busca todos os membros ativos de uma vez (otimização)
- Processa cada membro para calcular F.A, formações e visitantes
- Calcula percentuais de formações: `(count / total_members) * 100`
- Filtra visitantes por `_haklai_is_visitor = '1'` e `post_date` dos últimos 30 dias

**Lógica de Formações:**
```php
// Buscar todos os membros
// Para cada membro, verificar meta '_haklai_formacoes' (array)
// Contar: cme_count, ctl_count, stp_count
// Calcular porcentagens: (count / total_members) * 100
```

**Lógica de F.A:**
```php
// Meta query: '_haklai_baptism_status' = 'frequent_visitor'
// Contar membros com esse status
```

**Lógica de Visitantes (30 dias):**
```php
// Meta query: '_haklai_baptism_status' = 'visitor' OU '_haklai_is_visitor' = '1'
// Date query: últimos 30 dias (post_date)
```

#### **2.2 Arquivo: `includes/templates/relatorios-template.php`**
**Modificar Cards (linhas 53-80):**

**Card 1:** Total de Membros (manter)
```php
<div class="stat-card">
    <div class="icon">👥</div>
    <div class="stat-content">
        <div class="number"><?php echo esc_html($stats['total_members']); ?></div>
        <div class="label"><?php _e('Total de Membros', 'haklai-app'); ?></div>
    </div>
</div>
```

**Card 2:** Total de F.A (substituir Células Ativas)
```php
<div class="stat-card">
    <div class="icon">🔄</div>
    <div class="stat-content">
        <div class="number"><?php echo esc_html($stats['total_fa']); ?></div>
        <div class="label"><?php _e('Frequentador Assíduo', 'haklai-app'); ?></div>
    </div>
</div>
```

**Card 3:** Formações com 3 porcentagens (substituir Reuniões)
```php
<div class="stat-card formations-card">
    <div class="icon">📚</div>
    <div class="stat-content">
        <div class="formations-stats">
            <div class="formation-item">
                <span class="formation-label">CME:</span>
                <span class="formation-percentage"><?php echo esc_html($stats['formations']['cme']['percentage']); ?>%</span>
            </div>
            <div class="formation-item">
                <span class="formation-label">CTL:</span>
                <span class="formation-percentage"><?php echo esc_html($stats['formations']['ctl']['percentage']); ?>%</span>
            </div>
            <div class="formation-item">
                <span class="formation-label">STP:</span>
                <span class="formation-percentage"><?php echo esc_html($stats['formations']['stp']['percentage']); ?>%</span>
            </div>
        </div>
        <div class="label"><?php _e('Formações', 'haklai-app'); ?></div>
    </div>
</div>
```

**Card 4:** Novos Batismos (manter)
```php
<div class="stat-card">
    <div class="icon">💧</div>
    <div class="stat-content">
        <div class="number"><?php echo esc_html($stats['total_baptisms']); ?></div>
        <div class="label"><?php _e('Novos Batismos', 'haklai-app'); ?></div>
    </div>
</div>
```

**Card 5:** Visitantes (30 dias) - NOVO
```php
<div class="stat-card">
    <div class="icon">👋</div>
    <div class="stat-content">
        <div class="number"><?php echo esc_html($stats['total_visitors_30days']); ?></div>
        <div class="label"><?php _e('Visitantes (30 dias)', 'haklai-app'); ?></div>
    </div>
</div>
```

#### **2.3 Arquivo: `assets/css/haklai-shortcodes.css`**
**Adicionar estilos para card de formações:**
```css
/* Card de Formações */
.stat-card.formations-card .formations-stats {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 10px;
}

.stat-card.formations-card .formation-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 4px 0;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.stat-card.formations-card .formation-item:last-child {
    border-bottom: none;
}

.stat-card.formations-card .formation-label {
    font-size: 0.85rem;
    color: #4a5568;
    font-weight: 600;
}

.stat-card.formations-card .formation-percentage {
    font-size: 1.1rem;
    font-weight: 700;
    color: #667eea;
}
```

### **Estrutura de Dados Retornada**

```php
return array(
    'total_members' => int,
    'total_fa' => int,                    // NOVO
    'formations' => array(                // NOVO
        'cme' => array(
            'count' => int,
            'percentage' => float
        ),
        'ctl' => array(
            'count' => int,
            'percentage' => float
        ),
        'stp' => array(
            'count' => int,
            'percentage' => float
        )
    ),
    'total_baptisms' => int,
    'total_visitors_30days' => int,        // NOVO
);
```

### **Arquivos Afetados**
- `includes/shortcodes/class-haklai-shortcodes.php` (modificar)
- `includes/templates/relatorios-template.php` (modificar)
- `assets/css/haklai-shortcodes.css` (modificar)

### **Dependências**
- ✅ Fase 1 concluída

### **Riscos**
- 🟡 **Médio** - Altera estrutura de dados retornada
- **Mitigação:** Manter compatibilidade com código existente

### **Testes Necessários**
- ✅ Verificar contagem de F.A está correta
- ✅ Verificar porcentagens de formações estão corretas
- ✅ Verificar contagem de visitantes (30 dias)
- ✅ Validar filtros por nível de usuário funcionam

---

## 🎯 **FASE 3: REPOSITORY PATTERN (ARQUITETURA)**

### **Objetivo**
Implementar Repository Pattern para abstrair acesso aos dados e facilitar expansão futura.

### **Alterações Necessárias**

#### **3.1 Criar Estrutura de Diretórios**
```
includes/
├── repositories/
│   ├── MemberRepository.php
│   ├── FormationRepository.php
│   ├── RoleRepository.php
│   ├── CellRepository.php
│   └── StatisticsRepository.php
└── entities/
    ├── Member.php
    ├── Formation.php
    ├── Role.php
    └── Tenant.php
```

#### **3.2 Criar Repositories**

**MemberRepository.php**
- `findById($id)` - Busca membro por ID
- `findBy(array $criteria)` - Busca membros por critérios
- `countByStatus($status)` - Conta membros por status
- `countByBaptismStatus($status)` - Conta por status de batismo
- `countVisitorsLast30Days()` - Conta visitantes últimos 30 dias
- `countActive($user_cells = null)` - Conta membros ativos

**FormationRepository.php**
- `countByType($type)` - Conta formações por tipo (cme, ctl, stp)
- `getPercentages($total_members)` - Calcula porcentagens
- `getFormationsByMember($member_id)` - Busca formações de um membro

**RoleRepository.php**
- `findByCode($code)` - Busca role por código
- `findByLevel($level)` - Busca roles por nível hierárquico
- `findAll()` - Lista todas as roles

**StatisticsRepository.php**
- `getGeneralStats($user_cells)` - Estatísticas gerais
- Usa outros repositories internamente
- Orquestra consultas relacionadas

#### **3.3 Refatorar Código Existente**
- Modificar `get_dashboard_statistics()` para usar Repositories
- Manter compatibilidade com código existente
- Migração gradual

### **Arquivos a Criar**
- `includes/repositories/MemberRepository.php` (novo)
- `includes/repositories/FormationRepository.php` (novo)
- `includes/repositories/RoleRepository.php` (novo)
- `includes/repositories/StatisticsRepository.php` (novo)
- `includes/entities/Member.php` (novo)
- `includes/entities/Formation.php` (novo)
- `includes/entities/Role.php` (novo)

### **Arquivos a Modificar**
- `includes/shortcodes/class-haklai-shortcodes.php` (refatorar)

### **Dependências**
- ✅ Fase 2 concluída

### **Riscos**
- 🟡 **Médio** - Refatoração de código existente
- **Mitigação:** Implementar gradualmente, manter código antigo funcionando

### **Testes Necessários**
- ✅ Validar que Repositories retornam dados corretos
- ✅ Validar compatibilidade com código existente
- ✅ Performance: Comparar tempo de execução

---

## 🎯 **FASE 4: MULTI-TENANCY (SAAS)**

### **Objetivo**
Implementar isolamento de dados por Tenant (país/localidade) para suportar múltiplos grupos independentes.

### **Alterações Necessárias**

#### **4.1 Criar Tabela `tenants`**
```sql
CREATE TABLE wp_hklapp_tenants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    location VARCHAR(255) NULL,
    supervisor_contact VARCHAR(255) NULL,
    supervisor_pastor_id BIGINT UNSIGNED NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    created_by BIGINT UNSIGNED NULL,
    INDEX idx_code (code),
    INDEX idx_supervisor (supervisor_pastor_id)
);
```

#### **4.2 Modificar Tabelas Existentes**
- Adicionar `tenant_id` em: `members`, `cells`, `networks`, `meetings`
- Criar índices em `tenant_id`
- Script de migração para associar dados existentes a tenant padrão

#### **4.3 Criar TenantRepository**
- `create($data)` - Cria novo tenant
- `findById($id)` - Busca tenant por ID
- `getCurrentTenant($user_id)` - Obtém tenant atual do usuário
- `setCurrentTenant($tenant_id)` - Define tenant atual

#### **4.4 Modificar Repositories Existentes**
- Adicionar filtro de `tenant_id` em todas as consultas
- `MemberRepository` deve filtrar por tenant
- `CellRepository` deve filtrar por tenant
- `StatisticsRepository` deve considerar tenant

#### **4.5 Criar TenantManager**
- `get_current_tenant_id()` - Obtém ID do tenant atual
- `set_current_tenant($tenant_id)` - Define tenant atual
- `user_is_tenant_owner($user_id)` - Verifica se é owner

### **Arquivos a Criar**
- `includes/core/class-haklai-tenant-manager.php` (novo)
- `includes/repositories/TenantRepository.php` (novo)
- `includes/entities/Tenant.php` (novo)
- `includes/core/class-haklai-db-migration.php` (novo - script de migração)

### **Arquivos a Modificar**
- `includes/core/class-haklai-db.php` (adicionar métodos de tenant)
- Todos os Repositories (adicionar filtro tenant_id)
- `includes/shortcodes/class-haklai-shortcodes.php` (adicionar tenant)

### **Dependências**
- ✅ Fase 3 concluída (Repository Pattern)

### **Riscos**
- 🔴 **Alto** - Altera estrutura do banco de dados
- **Mitigação:**
  - Criar script de migração completo
  - Associar dados existentes a tenant padrão
  - Testar em ambiente de desenvolvimento primeiro

### **Testes Necessários**
- ✅ Validar criação de tenant
- ✅ Validar isolamento de dados entre tenants
- ✅ Validar migração de dados existentes
- ✅ Performance: Impacto de filtros por tenant

---

## 🎯 **FASE 5: FLUXO DE ONBOARDING (SAAS)**

### **Objetivo**
Criar fluxo de registro inicial para Tenant Owner, preparando sistema para SaaS.

### **Alterações Necessárias**

#### **5.1 Criar Página de Onboarding**
- `includes/admin/class-haklai-onboarding-page.php`
- `includes/templates/admin-onboarding-page.php`

#### **5.2 Formulário de Registro**
- Nome do Supervisor (Tenant Owner)
- Nome da Igreja (nome do tenant)
- Localidade
- Contacto do responsável

#### **5.3 Processo de Registro**
- Criar Tenant no banco de dados
- Associar usuário como Tenant Owner
- Atribuir role de Pastor Supervisor
- Definir tenant atual na sessão
- Redirecionar para `admin-welcome-page.php`

#### **5.4 Modificar admin-welcome-page.php**
- Verificar se usuário tem tenant
- Se não tiver → redirecionar para onboarding
- Se tiver → exibir normalmente

#### **5.5 Modificar Menu Admin**
- Adicionar página de onboarding (se não tiver tenant)
- Ocultar se já tiver tenant

### **Arquivos a Criar**
- `includes/admin/class-haklai-onboarding-page.php` (novo)
- `includes/templates/admin-onboarding-page.php` (novo)
- `assets/css/haklai-onboarding.css` (novo)
- `assets/js/haklai-onboarding.js` (novo)

### **Arquivos a Modificar**
- `haklai-app-novo.php` (registrar classe de onboarding)
- `includes/templates/admin-welcome-page.php` (verificar tenant)
- `includes/shortcodes/class-haklai-shortcodes.php` (verificar tenant)

### **Dependências**
- ✅ Fase 4 concluída (Multi-Tenancy)

### **Riscos**
- 🟡 **Médio** - Novo fluxo de usuário
- **Mitigação:** Testar fluxo completo antes de produção

### **Testes Necessários**
- ✅ Validar criação de tenant via onboarding
- ✅ Validar redirecionamento correto
- ✅ Validar que usuário fica como Tenant Owner
- ✅ Validar que dados futuros são associados ao tenant

---

## 📊 **ORDEM DE EXECUÇÃO RECOMENDADA**

```
FASE 1 (Crítico) → FASE 2 → FASE 3 → FASE 4 → FASE 5
   ↓                ↓         ↓         ↓         ↓
Correção        Alteração  Arquitetura  SaaS    Onboarding
Cards           Cards      Repository   Tenant  Flow
```

### **Sequência Detalhada**

1. **FASE 1: Correção dos Cards** (1-2 dias)
   - Corrige bug crítico
   - Baixo risco
   - Base para próximas fases

2. **FASE 2: Alteração dos Cards** (2-3 dias)
   - Implementa novas funcionalidades
   - Médio risco
   - Depende da Fase 1

3. **FASE 3: Repository Pattern** (3-5 dias)
   - Refatoração arquitetural
   - Médio risco
   - Depende da Fase 2

4. **FASE 4: Multi-Tenancy** (5-7 dias)
   - Mudança estrutural significativa
   - Alto risco
   - Depende da Fase 3
   - Requer migração de dados

5. **FASE 5: Onboarding Flow** (2-3 dias)
   - Novo fluxo de usuário
   - Médio risco
   - Depende da Fase 4

---

## ✅ **CHECKLIST DE SEGURANÇA**

### **Antes de Cada Fase**
- [ ] Backup completo do banco de dados
- [ ] Backup dos arquivos do plugin
- [ ] Testar em ambiente de desenvolvimento
- [ ] Documentar alterações

### **Durante Cada Fase**
- [ ] Implementar gradualmente
- [ ] Manter código antigo funcionando
- [ ] Testar após cada alteração
- [ ] Validar que não quebra funcionalidades existentes

### **Após Cada Fase**
- [ ] Testes completos
- [ ] Validar performance
- [ ] Documentar mudanças
- [ ] Commit com mensagem descritiva

---

## 📁 **RESUMO DAS ALTERAÇÕES POR ARQUIVO**

### **Arquivos a Criar (Novos)**
```
includes/
├── repositories/
│   ├── MemberRepository.php
│   ├── FormationRepository.php
│   ├── RoleRepository.php
│   ├── CellRepository.php
│   ├── StatisticsRepository.php
│   └── TenantRepository.php
├── entities/
│   ├── Member.php
│   ├── Formation.php
│   ├── Role.php
│   └── Tenant.php
├── admin/
│   └── class-haklai-onboarding-page.php
├── core/
│   ├── class-haklai-tenant-manager.php
│   └── class-haklai-db-migration.php
└── templates/
    └── admin-onboarding-page.php

assets/
├── css/
│   └── haklai-onboarding.css
└── js/
    └── haklai-onboarding.js
```

### **Arquivos a Modificar (Existentes)**
```
includes/
├── shortcodes/
│   └── class-haklai-shortcodes.php (FASE 1, 2, 3, 4, 5)
├── templates/
│   ├── relatorios-template.php (FASE 1, 2)
│   └── admin-welcome-page.php (FASE 5)
└── core/
    └── class-haklai-db.php (FASE 4)

assets/
├── css/
│   └── haklai-shortcodes.css (FASE 2)
└── js/
    └── haklai-main.js (FASE 1)

haklai-app-novo.php (FASE 5)
```

---

## ⏱️ **ESTIMATIVA DE TEMPO TOTAL**

- **Fase 1:** 1-2 dias
- **Fase 2:** 2-3 dias
- **Fase 3:** 3-5 dias
- **Fase 4:** 5-7 dias
- **Fase 5:** 2-3 dias

**Total Estimado:** 13-20 dias úteis

---

## 🚀 **PRÓXIMOS PASSOS IMEDIATOS**

1. ✅ Validar Fase 1 (correção dos cards)
2. ⏳ Implementar Fase 2 (alteração dos cards)
3. ⏳ Planejar Fase 3 (Repository Pattern)
4. ⏳ Preparar migração para Fase 4 (Multi-Tenancy)

---

## 📝 **NOTAS IMPORTANTES**

- **Princípio Fundamental:** Não quebrar funcionalidades existentes que já estão validadas e funcionando
- **Backup:** Sempre fazer backup antes de cada fase
- **Testes:** Validar cada alteração antes de prosseguir
- **Documentação:** Documentar todas as mudanças realizadas

---

**Última Atualização:** Janeiro 2025  
**Status:** Em Planejamento

