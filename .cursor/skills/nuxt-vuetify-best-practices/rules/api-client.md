# API Client

- Centralize base URL and auth headers/cookies in one client module.
- Handle 401/403 with a clear login or "no access" path.
- Never send a forged `tenant_id` to escalate access.
- Optimistic UI is fine for UX; reconcile with server folio state after charge/payment/void.
- Do not store secrets in frontend env that are meant to be private server keys.
