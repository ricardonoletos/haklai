# Parte 3 — Shortcode de Check-in (Checkpoint: CP-F3)

Escopo:
- Shortcode `[haklai_checkin]` com UI inspirada em `docs/test-checkpoint-final.html`.
- Integração com endpoints REST de sessões e membros.
- Segurança (nonces, capabilities) e i18n.

Critérios de aceitação:
- Renderização do shortcode sem erros e responsivo.
- Fluxo: carregar membros, marcar presença, enviar relatório.
- Resposta padrão `{ success, data, errors }` nos endpoints.

Checkpoint (Git):
- Tag: `CP-F3` (ou hash registrado abaixo)
- Hash: <preencher ao concluir>

Recuperação leve até este checkpoint:
```bash
# Voltar para o ponto estável CP-F3
git fetch --all --prune
git switch main
git pull --rebase
# Se existir tag
git checkout CP-F3
# Se não existir tag, use o hash registrado nesta Parte
# git checkout <HASH-CP-F3>
```

Notas:
- Validar no shortcode `[Haklai_dashboard]` a consistência visual.
- Acessibilidade básica (teclado, ARIA).

Assinatura do Projeto:
// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt
