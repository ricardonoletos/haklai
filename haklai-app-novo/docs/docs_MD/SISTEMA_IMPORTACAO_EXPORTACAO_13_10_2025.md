# Sistema de Importação e Exportação de Membros - Haklai App

**Data:** 13 de Outubro de 2025  
**Versão:** 2.0.0  
**Desenvolvido por:** Ricardo Sarmento - https://linx.pt

---

## 📋 Índice

1. [Visão Geral](#visão-geral)
2. [Arquivos Criados](#arquivos-criados)
3. [Funcionalidades](#funcionalidades)
4. [Como Usar](#como-usar)
5. [Estrutura do CSV/Excel](#estrutura-do-csvexcel)
6. [Filtros de Exportação](#filtros-de-exportação)
7. [Requisitos Técnicos](#requisitos-técnicos)
8. [Fluxo de Importação](#fluxo-de-importação)
9. [Fluxo de Exportação](#fluxo-de-exportação)
10. [Troubleshooting](#troubleshooting)

---

## 🎯 Visão Geral

O sistema de importação e exportação permite:
- **Importar em massa** até centenas de membros e líderes via arquivos CSV ou Excel
- **Exportar dados** dos membros com filtros avançados
- **Validação automática** dos dados antes da importação
- **Criação automática** de células quando necessário
- **Vinculação hierárquica** entre líderes e membros
- **Sincronização automática** com usuários WordPress para líderes

---

## 📁 Arquivos Criados

### 1. Classes PHP

#### `includes/admin/class-haklai-importer.php`
- **Função:** Classe principal de importação e exportação
- **Métodos principais:**
  - `process_import()` - Processa arquivo de importação
  - `export_members()` - Gera arquivo de exportação
  - `generate_template()` - Gera template CSV de exemplo
  - `load_csv()` / `load_excel()` - Carrega arquivos
  - `validate_data()` - Valida dados antes de importar
  - `create_member()` - Cria membro individual
  - `get_or_create_cell()` - Obtém ou cria célula
  - `prepare_export_data()` - Prepara dados para exportação
  - `write_csv_file()` / `write_excel_file()` - Escreve arquivos

#### `includes/admin/class-haklai-settings-page.php`
- **Função:** Página de configurações no WordPress Admin
- **Recursos:**
  - Registra página no menu admin
  - Handlers AJAX para importação/exportação
  - Download de template
  - Enfileiramento de scripts e estilos

### 2. Templates

#### `includes/templates/admin-settings-page.php`
- **Função:** Interface HTML da página de configurações
- **Seções:**
  - Importação de Membros com upload de arquivos
  - Exportação com filtros avançados
  - Informações do Sistema
  - Backup e Segurança

### 3. Assets

#### `assets/css/haklai-settings.css`
- **Função:** Estilos da página de configurações
- **Características:**
  - Design moderno e responsivo
  - Gradientes e animações
  - Compatível com tema dark/light
  - Mobile-first approach

#### `assets/js/haklai-settings.js`
- **Função:** JavaScript para interatividade
- **Recursos:**
  - Upload de arquivos via AJAX
  - Progress bar animado
  - Exportação com filtros
  - Tratamento de erros
  - Download automático

### 4. Atualização do Plugin Principal

#### `haklai-app-novo.php` (modificado)
- Adicionado carregamento das novas classes admin
- Condição `is_admin()` para otimização

---

## ✨ Funcionalidades

### Importação

#### ✅ Formatos Suportados
- CSV (UTF-8 com BOM)
- Excel (.xlsx, .xls)

#### ✅ Capacidades
- **Importação em massa**: Até 250+ registros
- **Validação pré-importação**: Verifica dados antes de inserir
- **Criação automática de células**: Se célula não existir, é criada
- **Vinculação hierárquica**: Liga membros aos seus líderes
- **Sincronização de usuários**: Cria conta WordPress para líderes automaticamente
- **Log detalhado**: Sucessos, erros e avisos
- **Rollback parcial**: Continua importação mesmo com erros individuais

#### ✅ Validações
- Nome obrigatório
- Email obrigatório para líderes
- Formato de email válido
- Tipo válido (lider/membro)
- Nível hierárquico válido (0-5)
- Status de batismo válido

### Exportação

#### ✅ Formatos de Saída
- CSV (UTF-8 com BOM)
- Excel (.xlsx)

#### ✅ Filtros Avançados
- **Por Célula**: Exporta membros de célula específica
- **Por Status**: Ativo, Inativo, Transferido
- **Por Nível Hierárquico**: Membro a Pastor Supervisor
- **Por Status de Batismo**: Visitante, Frequentador, Membro
- **Por Data**: Período de criação (De/Até)
- **Sem Filtros**: Exporta todos os membros

#### ✅ Características
- **Download direto**: Arquivo gerado e baixado automaticamente
- **Simétrico**: Arquivo exportado pode ser reimportado
- **Completo**: Inclui todos os metadados do membro
- **Formatação Excel**: Cabeçalhos em negrito, colunas auto-ajustadas

---

## 📝 Como Usar

### Acessar a Página

1. No WordPress Admin, vá para **Haklai → Configurações**
2. A página será aberta com todas as funcionalidades

### Importar Membros

#### Passo 1: Baixar Template
```
1. Clique em "Baixar Template CSV"
2. Abre um arquivo CSV de exemplo
3. Preencha com seus dados
```

#### Passo 2: Preparar Arquivo
```
- Mantenha a primeira linha (cabeçalho)
- Preencha uma linha por membro
- Use os valores exatos conforme documentado
- Salve como CSV ou Excel
```

#### Passo 3: Upload
```
1. Clique em "Selecione um arquivo CSV ou Excel"
2. Escolha seu arquivo preparado
3. Clique em "Iniciar Importação"
4. Aguarde o processamento
5. Verifique o resultado
```

### Exportar Membros

#### Passo 1: Aplicar Filtros (Opcional)
```
- Selecione a célula desejada
- Escolha o status
- Defina nível hierárquico
- Configure período de data
```

#### Passo 2: Escolher Formato
```
☑ CSV - Arquivo texto, compatível com qualquer sistema
☐ Excel (XLSX) - Arquivo com formatação, melhor para edição
```

#### Passo 3: Exportar
```
1. Clique em "Exportar Membros"
2. Aguarde a geração do arquivo
3. Download iniciará automaticamente
```

---

## 📊 Estrutura do CSV/Excel

### Colunas (ordem fixa)

| Coluna | Tipo | Obrigatório | Valores Aceitos | Descrição |
|--------|------|-------------|----------------|-----------|
| `tipo` | String | Sim | lider, membro | Define se é líder ou membro comum |
| `nome` | String | Sim | Qualquer texto | Nome completo do membro |
| `email` | Email | Condicional* | email@exemplo.com | Email válido (*obrigatório para líderes) |
| `telefone` | String | Não | +351912345678 | Formato internacional recomendado |
| `celula` | String | Não | Nome da Célula | Nome da célula (criada automaticamente se não existir) |
| `nivel_hierarquico` | Number | Não | 0-5 | 0=Membro, 1=Líder, 2=Discipulador, 3=Pastor Rede, 4=Pastor Senior, 5=Pastor Supervisor |
| `habilidade` | String | Não | musica, foto, filmagem, teatro, gastronomia, tecnologia, recepcao, som | Habilidade do membro |
| `status_batismo` | String | Não | visitor, frequent_visitor, member | Status de batismo |
| `data_batismo` | Date | Não | YYYY-MM-DD | Data do batismo (formato ISO) |
| `lider_email` | Email | Não | email@lider.com | Email do líder hierárquico |
| `encontro_deus` | String | Não | sim, nao | Participou do Encontro com Deus |
| `formacoes` | String | Não | ctl,cme,stp | Formações concluídas (separadas por vírgula) |
| `status` | String | Não | active, inactive, transferred | Status do membro |
| `foto_url` | URL | Não | https://exemplo.com/foto.jpg | URL da foto do membro |
| `data_criacao` | DateTime | Não | YYYY-MM-DD HH:MM:SS | Data de criação (auto na importação) |

### Exemplo de CSV

```csv
tipo,nome,email,telefone,celula,nivel_hierarquico,habilidade,status_batismo,data_batismo,lider_email,encontro_deus,formacoes,status,foto_url,data_criacao
lider,João Silva,joao.silva@email.com,+351912345678,Célula Alpha,1,musica,member,2024-01-15,,sim,ctl,cme,active,https://example.com/joao.jpg,
membro,Maria Santos,maria.santos@email.com,+351923456789,Célula Alpha,0,recepcao,frequent_visitor,,joao.silva@email.com,nao,,active,,
membro,Pedro Costa,pedro.costa@email.com,+351934567890,Célula Beta,0,,visitor,,,nao,,active,,
```

---

## 🔍 Filtros de Exportação

### 1. Filtro por Célula
- **Uso:** Exportar apenas membros de uma célula específica
- **Exemplo:** Célula Alpha → Exporta todos os membros da Célula Alpha

### 2. Filtro por Status
- **active:** Membros ativos
- **inactive:** Membros inativos
- **transferred:** Membros transferidos

### 3. Filtro por Nível Hierárquico
- **0:** Membros comuns
- **1:** Líderes de Célula
- **2:** Discipuladores
- **3:** Pastores de Rede
- **4:** Pastores Senior
- **5:** Pastores Supervisores

### 4. Filtro por Status de Batismo
- **visitor:** Visitantes
- **frequent_visitor:** Frequentadores assíduos
- **member:** Membros batizados

### 5. Filtro por Data
- **Data De:** Membros criados a partir desta data
- **Data Até:** Membros criados até esta data
- **Combinação:** Define um período específico

### Exemplo de Uso Combinado

```
Filtros:
- Célula: Célula Alpha
- Status: active
- Nível: 1 (Líderes)
- Data De: 2024-01-01

Resultado: Todos os líderes ativos da Célula Alpha criados a partir de 01/01/2024
```

---

## ⚙️ Requisitos Técnicos

### Servidor

- **PHP:** 8.1 ou superior
- **WordPress:** 6.0 ou superior
- **Memória PHP:** 256MB recomendado (512MB para arquivos grandes)
- **Upload Max Size:** 10MB mínimo
- **Post Max Size:** 20MB mínimo
- **Execution Time:** 300 segundos recomendado

### Dependências PHP

O plugin utiliza a biblioteca **PhpSpreadsheet** para processamento de Excel:

```json
{
  "require": {
    "phpoffice/phpspreadsheet": "^1.29"
  }
}
```

### Instalação das Dependências

Se necessário, executar no diretório do plugin:

```bash
composer install
```

### Permissões de Arquivo

O diretório `wp-content/uploads/haklai-app/` precisa ter permissão de escrita:

```bash
chmod 755 wp-content/uploads/haklai-app/
chmod 755 wp-content/uploads/haklai-app/exports/
```

---

## 🔄 Fluxo de Importação

### 1. Upload do Arquivo
```
Usuário → Seleciona Arquivo → JavaScript valida formato
```

### 2. Envio AJAX
```
JavaScript → FormData → wp-ajax.php → ajax_import_process()
```

### 3. Validação
```
Haklai_Importer::process_import()
  ↓
load_file() → CSV ou Excel
  ↓
validate_data() → Verifica campos obrigatórios
```

### 4. Pre-carga de Caches
```
preload_caches()
  ↓
Células em memória (evita queries repetidas)
Líderes em memória (vinculação rápida)
```

### 5. Importação Linha por Linha
```
Para cada linha:
  ↓
create_member()
  ↓
get_or_create_cell() → Cria célula se não existir
  ↓
get_leader_by_email() → Vincula ao líder
  ↓
wp_insert_post() → Cria membro
  ↓
Log: Sucesso ou Erro
```

### 6. Sincronização Automática
```
Se role_level >= 1:
  ↓
Haklai_User_Sync::sync_member_on_save()
  ↓
Cria usuário WordPress
  ↓
Envia email com credenciais
```

### 7. Retorno ao Cliente
```
Resultado:
{
  success: true,
  imported: 50,
  errors: 2,
  warnings: 1,
  log: {
    success: [...],
    errors: [...],
    warnings: [...]
  }
}
```

---

## 📤 Fluxo de Exportação

### 1. Seleção de Filtros
```
Usuário → Define filtros → Escolhe formato
```

### 2. Envio AJAX
```
JavaScript → Coleta filtros → wp-ajax.php → ajax_export_process()
```

### 3. Busca de Membros
```
Haklai_Importer::export_members()
  ↓
get_members_for_export() → Aplica filtros via WP_Query
  ↓
Meta queries para filtros complexos
```

### 4. Preparação dos Dados
```
prepare_export_data()
  ↓
Para cada membro:
  - Busca metadados
  - Resolve relacionamentos (célula, líder)
  - Formata datas
  - Prepara formações
  ↓
Array bidimensional [header, ...rows]
```

### 5. Geração do Arquivo
```
generate_export_file()
  ↓
Se CSV:
  write_csv_file() → UTF-8 BOM + fputcsv()
  ↓
Se Excel:
  write_excel_file() → PhpSpreadsheet
  - Formata header
  - Auto-size colunas
  - Salva XLSX
```

### 6. Armazenamento
```
Arquivo salvo em:
wp-content/uploads/haklai-app/exports/
  ↓
Nome: haklai-members-export-YYYY-MM-DD-HHmmss.{formato}
```

### 7. Retorno ao Cliente
```
Resultado:
{
  success: true,
  file_url: 'http://exemplo.com/wp-content/uploads/haklai-app/exports/...',
  total_records: 150,
  format: 'csv'
}
  ↓
JavaScript → Exibe link de download
```

---

## 🛠️ Troubleshooting

### Problema: Importação Falha com Erro de Timeout

**Causa:** Arquivo muito grande ou tempo de execução insuficiente

**Solução:**
```php
// No wp-config.php ou .htaccess
set_time_limit(300);
ini_set('max_execution_time', 300);
```

### Problema: Erro "Arquivo não suportado"

**Causa:** Formato de arquivo incorreto ou corrompido

**Solução:**
1. Verifique a extensão (.csv, .xlsx, .xls)
2. Abra no Excel e salve novamente
3. Para CSV, use UTF-8 com BOM

### Problema: Células Duplicadas

**Causa:** Nome da célula com diferenças mínimas (maiúsculas/espaços)

**Solução:**
- Use nomes exatos e consistentes
- O sistema faz match case-insensitive
- Evite espaços extras

### Problema: Líderes Não Vinculados

**Causa:** Email do líder não encontrado no sistema

**Solução:**
1. Importe primeiro os líderes
2. Depois importe os membros liderados
3. Verifique emails exatos (case-sensitive)

### Problema: Usuários WordPress Não Criados

**Causa:** Falta email ou nível hierárquico < 1

**Solução:**
- Certifique-se que `nivel_hierarquico >= 1`
- Email é obrigatório para líderes
- Verifique logs de sincronização

### Problema: Exportação Não Gera Arquivo

**Causa:** Permissões de diretório ou sem membros

**Solução:**
```bash
# Verificar permissões
ls -la wp-content/uploads/haklai-app/

# Criar diretório se necessário
mkdir -p wp-content/uploads/haklai-app/exports/
chmod 755 wp-content/uploads/haklai-app/exports/
```

### Problema: Excel Não Abre Corretamente

**Causa:** Formato de data ou caracteres especiais

**Solução:**
- Use formato de data ISO: YYYY-MM-DD
- Evite caracteres especiais em nomes
- Para CSV, abra como "Texto delimitado por vírgula"

### Problema: Erros de Validação em Massa

**Causa:** Template incorreto ou dados faltando

**Solução:**
1. Baixe template atualizado
2. Copie e cole dados (não modifique colunas)
3. Verifique campos obrigatórios
4. Use valores exatos para status/tipos

---

## 📊 Logs e Monitoramento

### Logs de Importação

Os logs de importação são armazenados temporariamente em transients:

```php
// Visualizar logs (código para debug)
$logs = get_transient('haklai_sync_logs');
print_r($logs);
```

### Logs de Exportação

Arquivos exportados ficam em:
```
wp-content/uploads/haklai-app/exports/
```

### Limpeza Automática

- **Transients:** Expiram em 30 dias
- **Arquivos de exportação:** Mantidos indefinidamente (limpar manualmente)

---

## 🎓 Boas Práticas

### Para Importação

1. **Teste com Arquivo Pequeno:** Importe 5-10 registros primeiro
2. **Backup Antes:** Exporte dados atuais antes de importar
3. **Importação Hierárquica:** Líderes primeiro, membros depois
4. **Validação Manual:** Revise arquivo antes do upload
5. **Monitore Logs:** Verifique avisos e erros

### Para Exportação

1. **Use Filtros:** Exportações menores são mais rápidas
2. **Formato CSV:** Para importação em outros sistemas
3. **Formato Excel:** Para edição manual com formatação
4. **Backup Regular:** Export semanal de todos os membros
5. **Nomenclatura Clara:** Organize arquivos por data

---

## 📞 Suporte e Documentação Adicional

Para mais informações, consulte:
- **Documentação Completa:** `/docs/DOCUMENTACAO_COMPLETA_HAKLAI_APP.md`
- **Estrutura do Banco:** `/docs/DATABASE_SCHEMA_DOCUMENTATION.md`
- **Sincronização:** `/docs/CHECKPOINT_SINCRONIZACAO_MEMBROS_USUARIOS.md`

---

**Última Atualização:** 13 de Outubro de 2025  
**Versão do Documento:** 1.0  
**Desenvolvido por:** Ricardo Sarmento - https://linx.pt

