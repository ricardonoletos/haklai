# Parte 1 — Core e Fundações (Checkpoint: CP-F1)

Escopo:
- Composer/autoload PSR-4.
- Bootstrap do plugin, Loader, i18n (textdomain `haklai`).
- Installer + migrations base (tabelas iniciais).
- Estrutura modular (ModuleManager) e contratos.

Critérios de aceitação:
- Plugin ativa sem erros; autoload funcional.
- `db_version` gravado em `wp_options` após ativação.
- Estrutura de diretórios definida e carregando módulos.

Checkpoint (Git):
- Tag: `CP-F1` (ou hash registrado abaixo)
- Hash: <preencher ao concluir>

Recuperação leve até este checkpoint:
```bash
# Voltar para o ponto estável CP-F1
git fetch --all --prune
git switch main
git pull --rebase
# Se existir tag
git checkout CP-F1
# Se não existir tag, use o hash registrado nesta Parte
# git checkout <HASH-CP-F1>
```

Observações:
- Manter o código mínimo viável e testável para reduzir riscos.
- Não remover dados em desativação.

Assinatura do Projeto:
// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
