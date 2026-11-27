---
name: tailwindcss-development
description: Use when the user asks for Tailwind styling or responsive frontend layout work in `frontend/`.
---

# Tailwind CSS Development

- Check `frontend/package.json`, Tailwind configuration, and nearby Vue components before choosing syntax.
- This project currently uses Tailwind CSS 3 with Vuetify. Do not apply Tailwind v4-only directives or assume Tailwind owns Vuetify component styling.
- Follow existing theme, Vuetify, and utility-class conventions. Avoid adding dependencies.
- Prefer accessible responsive layouts and reuse existing components.
- Keep business logic and money rules out of presentation code.
- For frontend implementation, also follow `nuxt-vuetify-best-practices` and `coding-agent`.
- Run frontend checks only when the user explicitly authorizes them.
