# Scrum do Projeto — Haklai Church (Fases e Checkpoints)

Esta documentação organiza as fases de implementação do plugin e define checkpoints de recuperação.

## Fase 1 — Core e Fundações (Checkpoint: CP-F1)
- Autoload (Composer), bootstrap do plugin, Loader, i18n.
- Installer e migrations iniciais (tabelas base de dados).
- Estrutura modular (ModuleManager) e contratos.
- README inicial e DECISIONS.md.

Recuperação rápida até CP-F1:
```bash
git fetch --all --prune
git switch main
git pull --rebase
git checkout CP-F1
```

## Fase 2 — Modelo de Dados Presenças (Checkpoint: CP-F2)
- Modelagem MySQL para sessões (reuniões/eventos) e presenças.
- Índices, FKs (quando aplicável), visões (views) e SPs opcionais.
- Endpoints REST mínimos para membros e sessões (GET/POST).

Recuperação rápida até CP-F2:
```bash
git fetch --all --prune
git switch main
git pull --rebase
git checkout CP-F2
```

## Fase 3 — Shortcode de Check-in (Checkpoint: CP-F3)
- Shortcode `[haklai_checkin]` com UI inspirada no test-checkpoint-final.html.
- Integração com REST e segurança (nonce, capabilities).
- Testes básicos e documentação de uso.

Recuperação rápida até CP-F3:
```bash
git fetch --all --prune
git switch main
git pull --rebase
git checkout CP-F3
```

## Fase 4 — Financeiro (Checkpoint: CP-F4)
- Lançamentos (ofertas, dízimos, campanhas, primícias) e resumo.
- Endpoints REST e relatórios agregados.
- Integração leve com sessões (oferta do dia/opcional).

Recuperação rápida até CP-F4:
```bash
git fetch --all --prune
git switch main
git pull --rebase
git checkout CP-F4
```

## Fase 5 — Admin/UX, Testes e CI (Checkpoint: CP-F5)
- Telas admin essenciais, ajustes UX e acessibilidade.
- PHPUnit, cobertura mínima, GitHub Actions.

Recuperação rápida até CP-F5:
```bash
git fetch --all --prune
git switch main
git pull --rebase
git checkout CP-F5
```

## Notas sobre Checkpoints
- Cada checkpoint será marcado com uma tag Git com o nome indicado (ex.: `CP-F3`).
- Para voltar ao desenvolvimento após revisar um checkpoint: `git switch -` (ou `git switch main`).
- Se preferir sem tags, cada Parte documenta o hash do commit do checkpoint.

## Assinatura do Projeto
// Projeto: Haklai Church — Plugin WordPress Modular - Desenvolvido por: Ricardo Sarmento - https://linx.pt


