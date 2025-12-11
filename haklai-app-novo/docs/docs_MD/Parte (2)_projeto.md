# Parte 2 — Modelo de Dados Presenças (Checkpoint: CP-F2)

Escopo:
- Criar tabela `wp_haklai_sessoes` (unificada) e tabelas de presenças (membros/visitantes).
- Índices e chaves para consultas rápidas.
- Endpoints REST: `GET /membros`, `GET/POST /presencas/sessao`.

Critérios de aceitação:
- Migrations idempotentes com `dbDelta` (tabelas) e rotinas opcionais.
- Consultas de resumo retornam em tempo adequado (índices válidos).
- Endpoints com respostas `{ success, data, errors }` e validações.

Checkpoint (Git):
- Tag: `CP-F2` (ou hash registrado abaixo)
- Hash: <preencher ao concluir>

Recuperação leve até este checkpoint:
```bash
# Voltar para o ponto estável CP-F2
git fetch --all --prune
git switch main
git pull --rebase
# Se existir tag
git checkout CP-F2
# Se não existir tag, use o hash registrado nesta Parte
# git checkout <HASH-CP-F2>
```

Notas técnicas:
- Se o hosting não permitir SP/Views, usar consultas agregadas em PHP.
- Garantir `wpdb->prepare` em todas as queries.

Assinatura do Projeto:
// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
