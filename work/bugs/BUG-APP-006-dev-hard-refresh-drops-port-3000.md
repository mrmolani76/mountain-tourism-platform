---
id: BUG-APP-006
title: Dev hard refresh drops :3000 (falls through to Apache :80) + login 502
status: BUG_CREATED
type: APP
severity: high
created: 2026-11-06
---

# BUG-APP-006: Dev hard refresh drops :3000

## 1. Summary
Hard refresh on the Nuxt SPA removes `:3000` from the URL. The browser then hits Apache on port 80. Login `/api` calls return 502 because the Vite proxy target host was wrong / unreachable.

## 2. Environment
Local Nuxt dev (`frontend/`), staff host like `http://lab.localhost:3000`. Machine also has Apache listening on `*:80`.

## 3. Steps to Reproduce
1. Open `http://lab.localhost:3000/login` (Nuxt on 3000).
2. Hard refresh (Ctrl+Shift+R).
3. Address bar loses `:3000` (or API calls 502 while still on SPA).

## 4. Expected
URL stays on port 3000. `/api` proxies to Laravel on the configured backend port.

## 5. Actual
Port stripped and/or Vite proxy returns 502. Apache answers Host `lab.localhost` on :80 with 404.

## 6. Evidence
- `ss`: Apache listens `*:80`; nothing on `:3000`/`:8000` when SPA/API down.
- `curl Host: lab.localhost` → Apache 404.
- `nuxt.config.ts` `devServer.host: 'localhost'` (blocks subdomain bind).
- Vite proxy default `http://lab.local.mountain.local:8000` (host does not resolve on this machine).

## 7. Root Cause
1. `devServer.host: 'localhost'` + missing HMR `clientPort` causes bad host/port behaviour for `lab.localhost:3000`.
2. Proxy default points at an unresolvable host → gateway 502 for `/api/*`.

## 8. Workaround
Always use full `http://lab.localhost:3000/...` and set `BACKEND_URL=http://lab.localhost:8000` (Laravel must be running).

## 9. Suggested Fix
- `devServer.host: '0.0.0.0'`, `port: 3000`, `strictPort: true`.
- Vite `server.hmr.clientPort: 3000`.
- Proxy default → `http://lab.localhost:8000` (match `.env.example`).

## 10. Impact
Local staff login / SPA reload broken for Core+Hotel work.

## 11. Related
- Work item: 74
- Change report: (pending)
