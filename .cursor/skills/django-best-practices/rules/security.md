# Security

- Never trust client-sent `tenant_id` for authorization; bind from the authenticated user.
- Object-level checks: user may only access outlets assigned to them when role requires it.
- No secrets in code; use env/settings.
- CSRF/session or token auth per chosen Django auth setup — do not disable CSRF casually for cookie auth.
- Validate file uploads if added later.
- Log auth failures without logging passwords.
