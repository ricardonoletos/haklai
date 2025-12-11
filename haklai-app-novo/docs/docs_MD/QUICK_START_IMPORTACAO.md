# 🚀 QUICK START - Importação e Exportação de Membros

**5 minutos para começar a usar!**

---

## 📍 ACESSO RÁPIDO

1. WordPress Admin
2. Menu lateral: **Haklai**
3. Submenu: **Configurações**

✅ Pronto! Você está na página de importação/exportação.

---

## 📥 IMPORTAR MEMBROS (Passo a Passo)

### 1️⃣ Baixar Template

```
Botão: "Baixar Template CSV"
↓
Abre arquivo: haklai-import-template.csv
```

### 2️⃣ Preencher com Seus Dados

**Abra o template no Excel ou Google Sheets**

```csv
tipo,nome,email,telefone,celula,nivel_hierarquico,...
lider,João Silva,joao@email.com,+351912345678,Célula Alpha,1,...
membro,Maria Santos,maria@email.com,+351923456789,Célula Alpha,0,...
membro,Pedro Costa,pedro@email.com,+351934567890,Célula Beta,0,...
```

**IMPORTANTE:**
- ✅ Mantenha a primeira linha (cabeçalho)
- ✅ Uma linha = um membro
- ✅ Líderes primeiro, depois membros

### 3️⃣ Fazer Upload

```
1. Clique na área de upload
2. Selecione seu arquivo .csv ou .xlsx
3. Botão: "Iniciar Importação"
4. ⏳ Aguarde (progress bar aparece)
5. ✅ Veja o resultado
```

### 4️⃣ Verificar Resultado

```
✅ 50 registros importados com sucesso
⚠️ 2 avisos (células criadas automaticamente)
❌ 0 erros
```

---

## 📤 EXPORTAR MEMBROS (Passo a Passo)

### 1️⃣ Escolher Filtros (Opcional)

```
• Célula: [Todas as células] ou escolha uma
• Status: [Todos] ou Ativo/Inativo
• Nível: [Todos] ou Líder/Membro
• Datas: Deixe vazio ou defina período
```

### 2️⃣ Escolher Formato

```
○ CSV       - Para importar em outros sistemas
● Excel     - Para editar no Excel/Google Sheets
```

### 3️⃣ Exportar

```
Botão: "Exportar Membros"
↓
⏳ Aguarde geração
↓
💾 Download automático
```

---

## 📋 CAMPOS DO CSV/EXCEL

### Campos Obrigatórios

| Campo | Exemplo | Descrição |
|-------|---------|-----------|
| **tipo** | lider ou membro | Define o tipo |
| **nome** | João Silva | Nome completo |
| **email** | joao@email.com | Email (obrigatório para líderes) |

### Campos Opcionais Importantes

| Campo | Exemplo | Valores |
|-------|---------|---------|
| **celula** | Célula Alpha | Nome da célula |
| **nivel_hierarquico** | 1 | 0=Membro, 1=Líder, 2-5=Pastores |
| **telefone** | +351912345678 | Formato internacional |
| **lider_email** | lider@email.com | Email do líder (para membros) |
| **habilidade** | musica | musica, foto, teatro, recepcao, etc |
| **status_batismo** | member | visitor, frequent_visitor, member |
| **encontro_deus** | sim | sim ou nao |
| **formacoes** | ctl,cme | ctl, cme, stp (separados por vírgula) |

---

## 💡 DICAS RÁPIDAS

### ✅ Para Importação

```
1. Teste com 5 membros primeiro
2. Exporte dados atuais antes (backup)
3. Importe líderes antes dos membros
4. Use o template fornecido
5. Verifique os logs após importação
```

### ✅ Para Exportação

```
1. Use filtros para exportações específicas
2. CSV para backup ou importar em outro sistema
3. Excel para editar e reimportar
4. Faça backup semanal (exportar tudo)
```

---

## 🎯 EXEMPLOS PRÁTICOS

### Exemplo 1: Importar 3 Líderes

```csv
tipo,nome,email,telefone,celula,nivel_hierarquico,habilidade,status_batismo,data_batismo,lider_email,encontro_deus,formacoes,status,foto_url,data_criacao
lider,João Silva,joao@email.com,+351912345678,Célula Alpha,1,musica,member,2024-01-15,,sim,ctl,cme,active,,
lider,Maria Costa,maria@email.com,+351923456789,Célula Beta,1,recepcao,member,2024-02-20,,sim,ctl,active,,
lider,Pedro Santos,pedro@email.com,+351934567890,Célula Gama,1,tecnologia,member,2024-03-10,,sim,ctl,cme,stp,active,,
```

**Salvar como:** `lideres.csv`  
**Upload:** Importar → Resultado: 3 líderes criados + 3 usuários WordPress + 3 emails enviados

### Exemplo 2: Importar Membros Liderados

```csv
tipo,nome,email,telefone,celula,nivel_hierarquico,habilidade,status_batismo,data_batismo,lider_email,encontro_deus,formacoes,status,foto_url,data_criacao
membro,Ana Silva,ana@email.com,+351945678901,Célula Alpha,0,foto,frequent_visitor,,,nao,,active,,
membro,Carlos Mendes,carlos@email.com,+351956789012,Célula Alpha,0,som,visitor,,,nao,,active,,
membro,Beatriz Lima,beatriz@email.com,+351967890123,Célula Beta,0,teatro,member,2024-05-10,,sim,ctl,active,,
```

**Salvar como:** `membros.csv`  
**Upload:** Importar → Resultado: 3 membros criados

### Exemplo 3: Exportar Líderes Ativos de 2024

```
Filtros:
✓ Nível Hierárquico: Líder de Célula
✓ Status: Ativo
✓ Data De: 2024-01-01

Formato: ● Excel

Resultado: Download de arquivo com todos os líderes
```

---

## ⚠️ PROBLEMAS COMUNS

### ❌ "Email obrigatório para líderes"

**Solução:** Preencha o email de todos os líderes

### ❌ "Erro ao processar arquivo"

**Solução:** 
1. Verifique se é CSV ou Excel (.xlsx)
2. Abra e salve novamente
3. Use o template fornecido

### ❌ Células duplicadas

**Solução:** Use nomes exatos (Alpha ≠ alpha ≠ ALPHA)

### ❌ Líderes não vinculados

**Solução:** Importe líderes antes dos membros liderados

---

## 📞 PRECISA DE AJUDA?

**Documentação Completa:**
- `/docs_MD/SISTEMA_IMPORTACAO_EXPORTACAO_13_10_2025.md`

**Resumo da Implementação:**
- `/docs_MD/RESUMO_IMPLEMENTACAO_IMPORTACAO_EXPORTACAO.md`

---

## ✅ CHECKLIST DE INÍCIO

- [ ] Acessei Haklai → Configurações
- [ ] Baixei o template CSV
- [ ] Preenchi com meus dados
- [ ] Fiz upload e importei
- [ ] Verifiquei o resultado
- [ ] Testei a exportação

---

**🎉 Pronto! Você está usando o sistema de importação/exportação!**

**Desenvolvido por:** Ricardo Sarmento - https://linx.pt

