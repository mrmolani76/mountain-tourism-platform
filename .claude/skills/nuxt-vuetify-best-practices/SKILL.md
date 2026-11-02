---
name: nuxt-vuetify-best-practices
description: >-
  Nuxt, Vue, and Vuetify best practices for the Mountain Tourism frontend. Use
  when writing or reviewing frontend/** files, Vue SFCs, composables, Pinia
  stores, Vuetify layouts, or Nuxt pages/components.
---

# Nuxt + Vuetify Best Practices

Source of truth: `CLAUDE.md`. Details in `rules/`.

## Defaults

- Nuxt app under `frontend/`
- Vuetify for staff UI; prefer dense, tablet-friendly layouts for POS/cashier/rental
- Call backend API for money and authz; UI can hide actions but server decides
- Keep role-based navigation clear (front desk, cashier, rental, lift, etc.)
- English copy in repo
- Reuse existing components/composables before adding new abstractions

## Load rule files as needed

- `rules/architecture.md`
- `rules/ui.md`
- `rules/api-client.md`
