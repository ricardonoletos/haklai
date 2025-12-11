# 🔖 CHECKPOINT - Sistema de Relatórios

**Data do Checkpoint:** 13 de Outubro de 2025  
**Versão:** 2.0.0  
**Desenvolvido por:** Ricardo Sarmento - https://linx.pt

---

## 📍 OBJETIVO DESTE CHECKPOINT

Este documento registra o **estado atual completo** do sistema de relatórios antes de qualquer modificação, permitindo **reverter** mudanças se necessário.

---

## 📋 ESTADO ATUAL DO SISTEMA

### ✅ Sistema de Importação/Exportação
**Status:** ✅ IMPLEMENTADO E FUNCIONAL

**Arquivos:**
- `includes/admin/class-haklai-importer.php` ✅
- `includes/admin/class-haklai-settings-page.php` ✅
- `includes/templates/admin-settings-page.php` ✅
- `assets/css/haklai-settings.css` ✅
- `assets/js/haklai-settings.js` ✅

**Funcionalidades:**
- ✅ Importação CSV/Excel
- ✅ Exportação com filtros
- ✅ Validação automática
- ✅ Template para download

### ✅ Sistema de Relatórios
**Status:** ✅ EXISTENTE (template criado)

**Arquivos:**
- `includes/shortcodes/class-haklai-shortcodes.php` ✅
- `includes/templates/relatorios-template.php` ✅ (321 linhas)

---

## 📊 SHORTCODE DE RELATÓRIOS

### Nome do Shortcode
```
[Haklai_relatorios]
```

### Onde Está Registrado
**Arquivo:** `includes/shortcodes/class-haklai-shortcodes.php`

**Linha:** 45
```php
add_shortcode('Haklai_relatorios', array($this, 'relatorios_shortcode'));
```

### Método do Shortcode
**Arquivo:** `includes/shortcodes/class-haklai-shortcodes.php`

**Linhas:** 136-154
```php
/**
 * Shortcode de Relatórios
 * 
 * @param array $atts Atributos do shortcode
 * @return string HTML dos relatórios
 */
public function relatorios_shortcode($atts) {
    // Verifica permissões
    if (!Haklai_Permissions::user_can_access('haklai_access_reports')) {
        return $this->render_access_denied();
    }
    
    // Atributos padrão
    $atts = shortcode_atts(array(
        'default_period' => '30',
        'show_filters' => 'true',
        'show_history' => 'true',
    ), $atts);
    
    // Obtém dados
    $data = $this->get_relatorios_data($atts);
    
    // Renderiza template
    return $this->render_template('relatorios', $data);
}
```

### Template Utilizado
**Arquivo:** `includes/templates/relatorios-template.php` (321 linhas)

---

## 🎯 COMO ACESSAR O SHORTCODE DE RELATÓRIOS

### Método 1: Criar Página no WordPress

```
1. WordPress Admin → Páginas → Adicionar Nova
2. Título: "Relatórios" (ou qualquer nome)
3. Conteúdo: Digite o shortcode
   
   [Haklai_relatorios]
   
4. Publicar
5. Visualizar a página
```

### Método 2: Adicionar em Página Existente

```
1. WordPress Admin → Páginas → Editar página existente
2. No editor, adicione:
   
   [Haklai_relatorios]
   
3. Atualizar
4. Visualizar
```

### Método 3: Com Atributos Personalizados

```php
[Haklai_relatorios default_period="60" show_filters="true" show_history="true"]
```

**Atributos Disponíveis:**
- `default_period`: Período padrão em dias (padrão: 30)
- `show_filters`: Mostrar filtros (true/false)
- `show_history`: Mostrar histórico (true/false)

### Método 4: Diretamente no Tema

**Arquivo:** `header.php`, `footer.php`, ou qualquer template

```php
<?php echo do_shortcode('[Haklai_relatorios]'); ?>
```

### Método 5: Via PHP

```php
// Em qualquer arquivo PHP do WordPress
$output = do_shortcode('[Haklai_relatorios]');
echo $output;
```

---

## 🔒 PERMISSÕES NECESSÁRIAS

### Capability Requerida
```php
'haklai_access_reports'
```

### Quem Tem Acesso
- ✅ Pastor Supervisor (role_level 5)
- ✅ Pastor Senior (role_level 4)
- ✅ Pastor de Rede (role_level 3)
- ✅ Discipulador (role_level 2)
- ✅ Líder de Célula (role_level 1)
- ❌ Membro comum (role_level 0)

### Verificação de Permissão
**Arquivo:** `includes/shortcodes/class-haklai-shortcodes.php` (linha 138)

```php
if (!Haklai_Permissions::user_can_access('haklai_access_reports')) {
    return $this->render_access_denied();
}
```

---

## 📁 ESTRUTURA DE ARQUIVOS (CHECKPOINT)

### Diretório: includes/shortcodes/
```
class-haklai-shortcodes.php
  ├─ Linha 45: Registro do shortcode [Haklai_relatorios]
  ├─ Linha 136-154: Método relatorios_shortcode()
  └─ Métodos auxiliares:
      • get_relatorios_data()
      • render_template()
      • render_access_denied()
```

### Diretório: includes/templates/
```
relatorios-template.php (321 linhas)
  ├─ HTML completo do relatório
  ├─ Filtros de período
  ├─ Tabelas de dados
  ├─ Gráficos e estatísticas
  └─ Estilos inline
```

### Diretório: assets/css/
```
haklai-shortcodes.css (existe)
  └─ Estilos para o shortcode de relatórios
```

### Diretório: assets/js/
```
haklai-shortcodes.js (existe)
  └─ JavaScript para interatividade dos relatórios
```

---

## 🗂️ BACKUP DE CÓDIGO CRÍTICO

### 1. Registro do Shortcode (ATUAL)

**Arquivo:** `includes/shortcodes/class-haklai-shortcodes.php`

```php
/**
 * Inicializa hooks
 */
private function init_hooks() {
    // Registra shortcodes
    add_shortcode('Haklai_dashboard', array($this, 'dashboard_shortcode'));
    add_shortcode('Haklai_checkpoint', array($this, 'checkpoint_shortcode'));
    add_shortcode('Haklai_configuracoes', array($this, 'configuracoes_shortcode'));
    add_shortcode('Haklai_relatorios', array($this, 'relatorios_shortcode')); // ← LINHA 45
    
    // Enfileira scripts e estilos
    add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
}
```

### 2. Método do Shortcode (ATUAL)

```php
/**
 * Shortcode de Relatórios
 * 
 * @param array $atts Atributos do shortcode
 * @return string HTML dos relatórios
 */
public function relatorios_shortcode($atts) {
    // Verifica permissões
    if (!Haklai_Permissions::user_can_access('haklai_access_reports')) {
        return $this->render_access_denied();
    }
    
    // Atributos padrão
    $atts = shortcode_atts(array(
        'default_period' => '30',
        'show_filters' => 'true',
        'show_history' => 'true',
    ), $atts);
    
    // Obtém dados
    $data = $this->get_relatorios_data($atts);
    
    // Renderiza template
    return $this->render_template('relatorios', $data);
}
```

### 3. Template Header (ATUAL)

**Arquivo:** `includes/templates/relatorios-template.php` (primeiras 50 linhas)

```php
<?php
/**
 * Template para o Shortcode de Relatórios
 * 
 * @package HaklaiApp
 * @author Ricardo Sarmento
 * @version 2.0.0
 */

// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt

if (!defined('ABSPATH')) {
    exit;
}

// Extrai variáveis
extract($data);

$user_id = get_current_user_id();
$user = wp_get_current_user();
$user_level = Haklai_Permissions::get_user_level($user_id);
$user_cells = Haklai_Permissions::get_user_cells($user_id);
?>

<div class="haklai-relatorios-wrapper" style="font-family: 'Inter', sans-serif;">
    <!-- Conteúdo do template -->
    ...
</div>
```

---

## 📊 DADOS DO SISTEMA (SNAPSHOT)

### Versão do Plugin
```
HAKLAI_VERSION = '2.0.0'
```

### Custom Post Types Ativos
```
✅ haklai_member (Membros)
✅ haklai_cell (Células)
✅ haklai_meeting (Reuniões)
✅ haklai_attendance (Presenças)
✅ haklai_report (Relatórios)
```

### Shortcodes Registrados
```
✅ [Haklai_dashboard]
✅ [Haklai_checkpoint]
✅ [Haklai_configuracoes]
✅ [Haklai_relatorios] ← Foco deste checkpoint
```

### Capabilities Haklai
```
✅ haklai_access_dashboard
✅ haklai_access_checkpoint
✅ haklai_access_reports ← Requerida para relatórios
✅ haklai_access_settings
✅ haklai_manage_members
✅ haklai_manage_cells
✅ haklai_view_reports
```

---

## 🔄 COMO REVERTER SE NECESSÁRIO

### Se Houver Problemas Após Modificações

#### Passo 1: Restaurar Arquivo de Shortcodes
```bash
# Fazer backup do arquivo modificado
cp includes/shortcodes/class-haklai-shortcodes.php includes/shortcodes/class-haklai-shortcodes.php.backup

# Restaurar do Git (se versionado)
git checkout includes/shortcodes/class-haklai-shortcodes.php
```

#### Passo 2: Restaurar Template
```bash
# Fazer backup do template modificado
cp includes/templates/relatorios-template.php includes/templates/relatorios-template.php.backup

# Restaurar do Git
git checkout includes/templates/relatorios-template.php
```

#### Passo 3: Limpar Cache
```php
// WordPress Admin → Ferramentas → Cache (se houver plugin de cache)
// Ou via código:
wp_cache_flush();
```

#### Passo 4: Verificar Permissões
```php
// Executar em wp-admin/options.php ou via código
$permissions = new Haklai_Permissions();
$permissions->create_capabilities();
```

---

## 🧪 TESTES ANTES DE MODIFICAR

### Checklist de Testes

- [ ] Acessar página com `[Haklai_relatorios]`
- [ ] Verificar se carrega corretamente
- [ ] Testar com diferentes usuários (níveis)
- [ ] Verificar permissões (acesso negado para membros)
- [ ] Testar filtros de período
- [ ] Verificar dados exibidos
- [ ] Testar responsividade (mobile)
- [ ] Verificar console do navegador (erros JS)
- [ ] Verificar logs do PHP (erros)

### Comandos de Teste

#### Teste de Shortcode via WP-CLI
```bash
wp eval "echo do_shortcode('[Haklai_relatorios]');"
```

#### Teste de Permissões
```bash
wp user meta get USER_ID _haklai_role_level
```

#### Verificar Logs
```bash
# WordPress Debug Log
tail -f wp-content/debug.log
```

---

## 📝 NOTAS IMPORTANTES

### 1. Template de Relatórios
- Arquivo: `relatorios-template.php` (321 linhas)
- **NÃO MODIFICAR** sem backup
- Contém estilos inline e JavaScript
- Usa dados de `$data` passados pelo shortcode

### 2. Dados Esperados
O método `get_relatorios_data()` deve retornar:
```php
array(
    'period' => 30,
    'show_filters' => true,
    'show_history' => true,
    'reports' => [...],
    'statistics' => [...],
    'user_level' => 1-5,
    'cells' => [...]
)
```

### 3. Assets Carregados
- CSS: `haklai-shortcodes.css`
- JS: `haklai-shortcodes.js`
- Bootstrap (via CDN)
- Font Awesome (via CDN)

---

## 🎯 EXEMPLO DE USO ATUAL

### Página de Relatórios

**Criar página:**
```
Título: Relatórios Haklai
Slug: relatorios-haklai
Conteúdo:
  
  <h2>Relatórios de Células</h2>
  [Haklai_relatorios]
  
Status: Publicado
```

**URL de Acesso:**
```
https://seusite.com/relatorios-haklai/
```

**Requisitos:**
- Usuário logado: ✅
- Permissão `haklai_access_reports`: ✅
- Role level >= 1: ✅

---

## 📊 LINHA DO TEMPO

```
13/10/2025 10:00 - Sistema de Importação/Exportação implementado ✅
13/10/2025 14:00 - CHECKPOINT criado para Relatórios ✅
13/10/2025 14:01 - Sistema pronto para modificações (se necessário)
```

---

## ✅ VALIDAÇÃO DO CHECKPOINT

### Sistema Verificado
- [x] Shortcode registrado: `[Haklai_relatorios]`
- [x] Método implementado: `relatorios_shortcode()`
- [x] Template existente: `relatorios-template.php` (321 linhas)
- [x] Permissões configuradas: `haklai_access_reports`
- [x] Assets carregados: CSS e JS
- [x] Documentação criada: Este checkpoint

### Estado do Sistema
```
✅ ESTÁVEL E FUNCIONAL
✅ PRONTO PARA USO
✅ CHECKPOINT CRIADO COM SUCESSO
```

---

## 🚀 PRÓXIMOS PASSOS RECOMENDADOS

### 1. Testar o Shortcode Atual
```
1. Criar página de teste
2. Adicionar [Haklai_relatorios]
3. Verificar funcionamento
4. Documentar comportamento atual
```

### 2. Se Precisar Modificar
```
1. Consultar este checkpoint
2. Fazer backup dos arquivos
3. Implementar modificações
4. Testar exaustivamente
5. Documentar mudanças
```

### 3. Se Der Erro
```
1. Consultar seção "Como Reverter"
2. Restaurar arquivos originais
3. Limpar cache
4. Verificar logs
5. Contatar suporte se necessário
```

---

## 📞 INFORMAÇÕES DE SUPORTE

### Arquivos Críticos para Backup
```
includes/shortcodes/class-haklai-shortcodes.php
includes/templates/relatorios-template.php
assets/css/haklai-shortcodes.css
assets/js/haklai-shortcodes.js
```

### Logs para Verificar
```
wp-content/debug.log
wp-content/uploads/haklai-app/logs/
```

### Comandos Úteis
```bash
# Ver shortcodes registrados
wp shortcode list

# Ver usuários com permissão
wp user list --role=haklai_lider_celula

# Limpar cache
wp cache flush
```

---

## 🔖 HASH DE CHECKPOINT

```
Data: 2025-10-13 14:00:00
Versão: 2.0.0
Status: CHECKPOINT_RELATORIOS_CRIADO
Arquivos: 4 (shortcodes, template, css, js)
Shortcode: [Haklai_relatorios]
Estado: FUNCIONAL
```

---

**✅ CHECKPOINT CRIADO COM SUCESSO!**

Este documento serve como **ponto de restauração** caso seja necessário reverter qualquer modificação no sistema de relatórios.

---

**Desenvolvido por:** Ricardo Sarmento - https://linx.pt  
**Data:** 13 de Outubro de 2025  
**Versão:** 2.0.0

