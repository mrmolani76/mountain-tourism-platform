# Local setup — Nexro-style `{slug}.mtmp.local`

Local multi-tenant URLs matching **Nexro Inventory** subdomain style, for Mountain Tourism.

**This guide is configuration + scripts.** Do not edit `/etc/hosts` or start Apache until the human asks (or runs the scripts with sudo themselves).

Related:

- Tenancy model: [`MULTI_TENANT_LOCAL.md`](MULTI_TENANT_LOCAL.md)
- Apache (Complere path): [`APACHE_LOCAL_SETUP.md`](APACHE_LOCAL_SETUP.md)
- Nexro reference: `nexro/docs/DOCKER_SETUP.md` (hosts + `{shop}.nexro.local`)

---

## 1. URL scheme (like Nexro)

Nexro:

| Host | Role |
|------|------|
| `nexro.local` / `saas.nexro.local` | Central |
| `shop1.nexro.local:3001` | Tenant SPA |

Mountain Tourism (this repo):

| Host | Role | Example |
|------|------|---------|
| `saas.mtmp.local` | Central SaaS | `http://saas.mtmp.local:3000` |
| `{slug}.mtmp.local` | Tenant complex | `http://demo.mtmp.local:3000/login` |
| `:3000` | Nuxt dev | |
| `:8000` | Laravel `artisan serve` (direct API) | |

Base domain env: `APP_BASE_DOMAIN=mtmp.local`  
Tenant host = `{slug}.` + `APP_BASE_DOMAIN`  
Central host = `saas.` + `APP_BASE_DOMAIN` (also in `TENANCY_CENTRAL_HOSTS`)

Legacy still understood by SPA parser: `lab.localhost` (optional). Prefer **mtmp.local** for new local work.

---

## 2. How subdomain resolve works

```text
Browser  Host: demo.mtmp.local:3000
    → Nuxt (dev) same-origin /api  OR  proxy to Laravel
    → Laravel ResolveTenant
         Host demo.mtmp.local
         → saas_domain.hostname row
         → Tenant.db_name (e.g. demo_local)
         → activate tenant connection
```

Same idea as Nexro `InitializeTenancyByDomain`: **full hostname** in `domains` / `saas_domain`, not path-based tenancy.

---

## 3. One-time machine config (human / sudo)

### 3.1 Env

Copy and set (examples):

**`backend-laravel/.env`**

```env
APP_URL=http://demo.mtmp.local:8000
FRONTEND_URL=http://demo.mtmp.local:3000
APP_BASE_DOMAIN=mtmp.local
TENANCY_CENTRAL_HOSTS=saas.mtmp.local
CENTRAL_DOMAIN=saas.mtmp.local
SANCTUM_STATEFUL_DOMAINS=mtmp.local,mtmp.local:3000,saas.mtmp.local,saas.mtmp.local:3000,demo.mtmp.local,demo.mtmp.local:3000,lab.mtmp.local,lab.mtmp.local:3000,127.0.0.1,127.0.0.1:3000
```

**`frontend/.env`**

```env
NUXT_PUBLIC_API_BASE_URL=
BACKEND_URL=http://demo.mtmp.local:8000
ENV=local
```

Empty `NUXT_PUBLIC_API_BASE_URL` → same-origin `/api` when SPA and API share host (Apache) or Nuxt proxies `/api`.

### 3.2 Hosts file (Nexro `hosts:sync`)

Managed block markers (same idea as Nexro Inventory block):

```text
# ── Mountain Tourism (auto-managed) ──
127.0.0.1	mtmp.local
127.0.0.1	saas.mtmp.local
127.0.0.1	demo.mtmp.local
127.0.0.1	lab.mtmp.local
# ── /Mountain Tourism ──
```

**Bash (no PHP DB required for seed list):**

```bash
# Dry run
bash scripts/mtmp-hosts-sync.sh --dry-run

# Apply (needs write to /etc/hosts)
sudo bash scripts/mtmp-hosts-sync.sh
```

**Artisan (syncs central + every `saas_domain.hostname` from DB — like Nexro):**

```bash
cd backend-laravel
php artisan hosts:sync --dry-run
sudo php artisan hosts:sync
```

Remove managed block:

```bash
sudo bash scripts/mtmp-hosts-sync.sh --remove-all
# or
sudo php artisan hosts:sync --remove-all
```

### 3.3 Provision a tenant

```bash
cd backend-laravel
php artisan tenants:provision-local demo --with-saas-admin --reset
# Default hostname: demo.mtmp.local  (from APP_BASE_DOMAIN)
```

Login: `http://demo.mtmp.local:3000/login` — `owner@demo.test` / `password123`

### 3.4 Run apps (dev)

```bash
# Terminal 1 — API
cd backend-laravel && php artisan serve --host=127.0.0.1 --port=8000

# Terminal 2 — SPA (bind all interfaces so *.mtmp.local works)
cd frontend && pnpm exec nuxt dev --host 0.0.0.0 --port 3000
```

Open: `http://demo.mtmp.local:3000/login`

Optional Apache: see `APACHE_LOCAL_SETUP.md` (proxy or DocumentRoot). Wildcard vhost for `*.mtmp.local` is nicest; not required for Nuxt `:3000`.

---

## 4. Scripts map (vs Nexro)

| Nexro | Mountain Tourism |
|-------|------------------|
| `php artisan hosts:sync` | Same: `backend-laravel` → `hosts:sync` |
| `SyncHostsOnDomainChange` listener | Not wired yet (optional later) |
| Docker `HOSTS_FILE_SOURCE` | Not used (no Docker default path) |
| Manual hosts seed | `scripts/mtmp-hosts-sync.sh` |

---

## 5. Do / Do not

**Do**

- Use `{slug}.mtmp.local:3000` for staff SPA
- Keep `SESSION_DOMAIN` empty (per-host cookies, Nexro lesson)
- Sync hosts after adding tenants

**Do not**

- Run `hosts:sync` on production without `--force` (command refuses by default)
- Share session cookie across `.mtmp.local` with a leading-dot `SESSION_DOMAIN`
- Commit real `/etc/hosts` or `.env` secrets

---

## 6. Quick checklist

1. [ ] `.env` uses `APP_BASE_DOMAIN=mtmp.local` and central `saas.mtmp.local`
2. [ ] `sudo bash scripts/mtmp-hosts-sync.sh` (or artisan `hosts:sync`)
3. [ ] DB + `tenants:provision-local demo`
4. [ ] Nuxt `:3000` + Laravel `:8000`
5. [ ] Browser `http://demo.mtmp.local:3000/login`
