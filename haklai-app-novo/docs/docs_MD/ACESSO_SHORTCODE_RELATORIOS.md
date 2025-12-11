# 🚀 COMO ACESSAR O SHORTCODE DE RELATÓRIOS

**Guia Rápido de Acesso**

---

## 📋 INFORMAÇÕES BÁSICAS

### Nome do Shortcode
```
[Haklai_relatorios]
```

### Permissão Necessária
- ✅ Usuário deve estar **logado**
- ✅ Deve ter permissão `haklai_access_reports`
- ✅ Nível hierárquico >= 1 (Líder ou superior)

---

## 🎯 MÉTODO 1: CRIAR PÁGINA NOVA (RECOMENDADO)

### Passo a Passo

#### 1️⃣ Acessar o WordPress Admin
```
Dashboard → Páginas → Adicionar Nova
```

#### 2️⃣ Preencher os Campos

**Título da Página:**
```
Relatórios
```

**Conteúdo (no editor):**
```
[Haklai_relatorios]
```

**Ou com título:**
```html
<h2>Relatórios de Células</h2>
[Haklai_relatorios]
```

#### 3️⃣ Configurar a Página

**Slug (opcional):**
```
relatorios
```

**Template:**
```
Página Padrão (ou qualquer template do seu tema)
```

**Visibilidade:**
```
Pública (mas apenas usuários com permissão verão o conteúdo)
```

#### 4️⃣ Publicar
```
Botão: "Publicar"
```

#### 5️⃣ Acessar
```
URL: https://seusite.com/relatorios/
```

---

## 🎯 MÉTODO 2: ADICIONAR EM PÁGINA EXISTENTE

### Se você já tem uma página de membros ou dashboard

#### 1️⃣ Editar Página
```
Dashboard → Páginas → Todas as Páginas
Clique em "Editar" na página desejada
```

#### 2️⃣ Adicionar o Shortcode

**No editor de blocos (Gutenberg):**
```
1. Adicionar novo bloco
2. Pesquisar "Shortcode"
3. Colar: [Haklai_relatorios]
```

**No editor clássico:**
```
Simplesmente digite: [Haklai_relatorios]
```

#### 3️⃣ Atualizar
```
Botão: "Atualizar"
```

---

## 🎯 MÉTODO 3: COM ATRIBUTOS PERSONALIZADOS

### Shortcode Completo com Opções

```php
[Haklai_relatorios default_period="60" show_filters="true" show_history="true"]
```

### Atributos Disponíveis

| Atributo | Valores | Padrão | Descrição |
|----------|---------|--------|-----------|
| `default_period` | Número (dias) | 30 | Período padrão dos relatórios |
| `show_filters` | true/false | true | Mostrar filtros de período |
| `show_history` | true/false | true | Mostrar histórico de relatórios |

### Exemplos de Uso

#### Exemplo 1: Relatórios dos últimos 7 dias
```php
[Haklai_relatorios default_period="7"]
```

#### Exemplo 2: Sem filtros, só relatórios
```php
[Haklai_relatorios show_filters="false"]
```

#### Exemplo 3: Últimos 90 dias sem histórico
```php
[Haklai_relatorios default_period="90" show_history="false"]
```

---

## 🎯 MÉTODO 4: ADICIONAR AO MENU DO WORDPRESS

### Criar Link Direto no Menu

#### 1️⃣ Criar a Página
```
Primeiro criar a página com o shortcode (Método 1)
```

#### 2️⃣ Adicionar ao Menu
```
Aparência → Menus
Adicionar Itens → Páginas
Selecionar: "Relatórios"
Adicionar ao Menu
Salvar Menu
```

Agora o link aparecerá no menu do site!

---

## 🎯 MÉTODO 5: WIDGET NA SIDEBAR

### Adicionar em Widget

#### 1️⃣ Acessar Widgets
```
Aparência → Widgets
```

#### 2️⃣ Adicionar Widget de Shortcode
```
Procurar widget: "Shortcode" ou "HTML Personalizado"
Arrastar para a área desejada (sidebar, footer, etc)
```

#### 3️⃣ Inserir o Shortcode
```
[Haklai_relatorios]
```

#### 4️⃣ Salvar
```
Botão: "Salvar"
```

---

## 🎯 MÉTODO 6: DIRETAMENTE NO TEMA (AVANÇADO)

### Para Desenvolvedores

#### No arquivo do tema (header.php, page.php, etc)

```php
<?php
// Verificar se usuário tem permissão
if (Haklai_Permissions::user_can_access('haklai_access_reports')) {
    echo do_shortcode('[Haklai_relatorios]');
}
?>
```

#### Em template específico

**Criar:** `page-relatorios.php` no tema

```php
<?php
/**
 * Template Name: Página de Relatórios Haklai
 */

get_header(); ?>

<main class="site-main">
    <div class="container">
        <h1>Relatórios de Células</h1>
        
        <?php echo do_shortcode('[Haklai_relatorios]'); ?>
        
    </div>
</main>

<?php get_footer(); ?>
```

Depois criar página e selecionar este template.

---

## 🔒 CONTROLE DE ACESSO

### Quem Pode Ver os Relatórios?

#### ✅ Têm Acesso:
- Pastor Supervisor (nível 5)
- Pastor Senior (nível 4)
- Pastor de Rede (nível 3)
- Discipulador (nível 2)
- Líder de Célula (nível 1)

#### ❌ NÃO Têm Acesso:
- Membro comum (nível 0)
- Visitantes não logados

### O Que Acontece Se Não Tiver Permissão?

Aparecerá mensagem:
```
"Você não tem permissão para acessar este conteúdo."
```

---

## 🎨 PERSONALIZAÇÃO VISUAL

### Adicionar Classe CSS Personalizada

```php
[Haklai_relatorios class="minha-classe-customizada"]
```

### Estilos CSS Customizados

**No tema (style.css ou Customizador):**

```css
/* Container principal */
.haklai-relatorios-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Cards de relatório */
.report-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

/* Tabelas */
.haklai-relatorios-wrapper table {
    width: 100%;
    border-collapse: collapse;
}
```

---

## 🧪 TESTE RÁPIDO

### Verificar se Está Funcionando

#### 1️⃣ Criar Página de Teste
```
Título: Teste Relatórios
Conteúdo: [Haklai_relatorios]
Status: Rascunho (para testar antes de publicar)
```

#### 2️⃣ Pré-visualizar
```
Botão: "Pré-visualizar"
```

#### 3️⃣ Verificar
- [ ] Página carrega sem erros
- [ ] Relatórios aparecem
- [ ] Filtros funcionam
- [ ] Design está correto
- [ ] Responsivo no mobile

#### 4️⃣ Se Tudo OK
```
Mudar status para: "Publicado"
```

---

## 🚨 SOLUÇÃO DE PROBLEMAS

### Problema 1: Shortcode Aparece Como Texto

**Causa:** Shortcode não está registrado ou plugin desativado

**Solução:**
```
1. Verificar se plugin Haklai está ativo
2. Reativar o plugin se necessário
3. Limpar cache (se houver plugin de cache)
```

### Problema 2: Página em Branco

**Causa:** Erro PHP ou problema de permissão

**Solução:**
```
1. Ativar debug do WordPress
2. Verificar arquivo: wp-content/debug.log
3. Corrigir erro encontrado
```

### Problema 3: "Sem Permissão"

**Causa:** Usuário não tem o nível necessário

**Solução:**
```
1. Verificar nível do usuário
2. Atribuir permissão correta
3. Ou criar usuário com nível >= 1
```

### Problema 4: Sem Dados nos Relatórios

**Causa:** Não há reuniões ou presenças registradas

**Solução:**
```
1. Criar células primeiro
2. Registrar reuniões
3. Marcar presenças
4. Aguardar dados aparecerem
```

---

## 📊 EXEMPLO COMPLETO

### Página Profissional de Relatórios

```html
<!-- Título da Página: Relatórios -->

<div style="max-width: 1200px; margin: 0 auto; padding: 20px;">
    
    <h1 style="text-align: center; color: #667eea; margin-bottom: 30px;">
        📊 Relatórios de Células
    </h1>
    
    <p style="text-align: center; color: #6b7280; margin-bottom: 40px;">
        Acompanhe o desempenho e crescimento das células da igreja
    </p>
    
    [Haklai_relatorios default_period="30" show_filters="true" show_history="true"]
    
    <hr style="margin: 40px 0; border: none; border-top: 1px solid #e5e7eb;">
    
    <p style="text-align: center; color: #9ca3af; font-size: 14px;">
        © 2025 Haklai Church Management System
    </p>
    
</div>
```

**Resultado:** Página linda e profissional com relatórios integrados!

---

## ✅ CHECKLIST DE IMPLEMENTAÇÃO

Marque conforme for fazendo:

- [ ] Criei a página "Relatórios"
- [ ] Adicionei o shortcode `[Haklai_relatorios]`
- [ ] Publiquei a página
- [ ] Testei com usuário líder
- [ ] Testei com usuário membro (deve negar acesso)
- [ ] Adicionei ao menu (opcional)
- [ ] Personalizei visual (opcional)
- [ ] Documentei URL para compartilhar com líderes

---

## 🎯 URL PADRÃO RECOMENDADA

```
https://seusite.com/relatorios/

ou

https://seusite.com/relatorios-haklai/

ou

https://seusite.com/celulas/relatorios/
```

Escolha uma URL clara e fácil de lembrar!

---

## 📞 SUPORTE

### Documentação Relacionada

- **Checkpoint:** `/docs_MD/CHECKPOINT_RELATORIOS_13_10_2025.md`
- **Sistema Completo:** `/docs/DOCUMENTACAO_COMPLETA_HAKLAI_APP.md`

### Se Precisar de Ajuda

1. Consultar checkpoint de segurança
2. Verificar logs do WordPress
3. Testar com diferentes usuários
4. Verificar permissões dos roles

---

**✅ PRONTO! AGORA VOCÊ SABE COMO ACESSAR O SHORTCODE DE RELATÓRIOS!**

Escolha o método que preferir e comece a usar os relatórios imediatamente.

---

**Desenvolvido por:** Ricardo Sarmento - https://linx.pt  
**Data:** 13 de Outubro de 2025

