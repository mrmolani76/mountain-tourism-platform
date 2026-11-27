---
name: auth-development
description: Use for Laravel authentication, session, password, role, or outlet-access work in this platform.
---

# Authentication Development

- Inspect the installed Laravel/Sanctum setup and current routes before assuming a package or flow.
- Use the platform's email/password staff authentication and tenant resolution in `CLAUDE.md`.
- Enforce authentication, role, and outlet access on the server. UI hiding is not authorization.
- Keep central SaaS identities separate from tenant staff identities and preserve tenant database boundaries.
- Do not introduce Fortify, Passport, OAuth, passkeys, 2FA, or other auth packages/features unless the user approves the scope and dependency.
- Add focused coverage for unauthenticated, forbidden, and cross-tenant access when tests are requested.
- Never print or commit credentials, session tokens, or real `.env` values.

Use `laravel-best-practices` for implementation and `security-code-review` for review-only requests.
