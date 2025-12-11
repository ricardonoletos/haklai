Projeto: Haklai Church — Plugin WordPress Modular (Core + módulos Presença e Financeiro)

Contexto:
- Plugin modular para gestão de igrejas. O objeto central é o "Membro" (CPT `membro`).
- Já existe um esqueleto inicial (haklai-church) com Loader, Module Manager e exemplos de módulos. Use-o como base e evolua para um MVP funcional.

Objetivo do CURSOR:
- Entregar um repositório WordPress pronto para instalar que contenha:
  1. Core robusto: Member CPT, Database installer/migrations, ModuleManager, RestApi, i18n.
  2. Módulo Presença com geração de QR, check-in (AJAX + REST), tabelas e relatórios.
  3. Módulo Financeiro com lançamentos (ofertas/despesas), relatórios agregados (mensal/trimestral), CSV export e REST endpoints.
  4. Testes unitários (phpunit), configuração mínima de CI (GitHub Actions), e documentação (README, DECISIONS.md).

Requisitos técnicos:
- PHP 8.0+, WP 5.8+/6.x, MySQL compatible.
- Namespaces: Haklai\Core e Haklai\Modules\<Slug>.
- PSR-4/autoload (composer) preferencial.
- Segurança: use nonces, current_user_can, wpdb->prepare, escapamentos.
- Internationalization: textdomain `haklai`, locale pt_PT.

DB:
- Criar `wp_haklai_presencas` e `wp_haklai_financas` via dbDelta. Use db_version para migrations.

REST:
- Base `/wp-json/haklai/v1/`
- Endpoints mínimos: membros (GET), presenca/checkin (POST), finance/summary (GET), finance/entry (POST).
- Padrão de resposta JSON com success/data/errors.

Entregáveis:
- Código completo no repositório (estrutura listada no prompt).
- README com instruções de instalação, execução de testes e exemplos de curl.
- Tests: cobertura mínima para funções core e endpoints.
- Checklist de aceitação preenchido no PR.

Restrições:
- Não inventar features além das pedidas sem pedir permissão.
- Não remover automaticamente dados em desativação de módulos.

Critérios de aceitação:
- Plugin instala corretamente, módulos ativam/desativam, checkin registra, lançamentos financeiros salvam e relatórios funcionam.
- Testes passam e CI configurado para rodá-los.

Comunicação:
- Criar PR com descrição, screenshots e passo-a-passo para validar manualmente.
- Commits pequenos e descritivos.

Notas finais:
- Documentar decisões técnicas em docs/DECISIONS.md.
- Se surgir dúvida técnica que exija decisão (ex.: CPT vs tabela para eventos), abrir issue e propor 2 opções com trade-offs.

Fim do prompt.
