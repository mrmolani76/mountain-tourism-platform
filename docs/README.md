# Docs index

Agent-readable docs extracted from `document/Mountain_MVP_Scope_v1.pdf`.  
PDF remains the legal contract. If docs and PDF disagree, **PDF wins** until humans update both.

| Doc | Purpose |
|-----|---------|
| [TECHNICAL_RULES.md](TECHNICAL_RULES.md) | Non-negotiable tech rules (§7) |
| [GLOSSARY.md](GLOSSARY.md) | Domain vocabulary |
| [MODULES.md](MODULES.md) | Modules A–I index + done-when |
| [modules/](modules/) | Per-module deep files (link to phase embeds) |
| [../work/phases/](../work/phases/) | Phase plans with **complete** module descriptions |
| [MILESTONES.md](MILESTONES.md) | Payment milestones 1–5 |
| [HOSTING.md](HOSTING.md) | VPS / CI/CD constraints (§6) |
| [MULTI_TENANT_LOCAL.md](MULTI_TENANT_LOCAL.md) | Central vs tenant separation (EQMS + Nexro + MTMTP); docs only |
| [LOCAL_MTMP_SETUP.md](LOCAL_MTMP_SETUP.md) | Nexro-style `{slug}.mtmp.local` guide + hosts:sync scripts |
| [APACHE_LOCAL_SETUP.md](APACHE_LOCAL_SETUP.md) | Local Apache multi-tenant (Complere-style; no Docker) |
| [CI.md](CI.md) | GitHub Actions testing workflow |
| [ROLES.md](ROLES.md) | Staff roles |
| [SEED_COMPLEX.md](SEED_COMPLEX.md) | Demo buildings/altitudes |
| [DECISIONS.md](DECISIONS.md) | Stack choices (Django + Nuxt) |
| [ARCHITECTURE.md](ARCHITECTURE.md) | Living architecture note (Milestone 1 deliverable) |
| [DATABASE_SCHEMA.md](DATABASE_SCHEMA.md) | Full platform DB schema (Modules A to I, central + tenant) |
| [HANDOVER.md](HANDOVER.md) | Handover checklist (§11) |
| [OUT_OF_SCOPE.md](OUT_OF_SCOPE.md) | After-MVP / do not build yet |

Update flow: change PDF agreement with client → update these docs → update `CLAUDE.md` pointers if rules change.
