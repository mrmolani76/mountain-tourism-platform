# Apache local setup (Complere-style)

Local multi-tenant hosting **without Docker**, matching eQMS Complere / Quality Center:
Apache vhosts + `/etc/hosts` + Laravel `public/` + Nuxt SPA.

**This guide is docs only.** Do not apply machine config until the human says
`Apache setup karo`. Do not touch Docker files for this path.

Related:

- SPA hosts: `frontend/docs/LOCAL_SETUP.md`
- Laravel artisan serve: `docs/LARAVEL_LOCAL_SETUP.md`
- Optional Docker: `docs/DOCKER_SETUP.md` (separate; leave alone)
- MVP hosting rules: `docs/HOSTING.md` (VPS prefers Nginx; local Apache is OK)

---

## Hosts

Same host scheme already used in `.env.example` and tenancy tests.

| Host | Role | DB | Purpose |
|------|------|----|---------|
| `saas.localhost` | Central (landlord) | central SaaS DB | Super admin / Saas APIs |
| `lab.localhost` | Tenant | `lab` tenant DB | Day-to-day staff SPA + Core APIs |

- Tenant resolve: `Host` → `Domain` → tenant connection (`TenantResolver`).
- `saas.localhost` is **not** a tenant. Staff login uses a tenant host (`lab.localhost`).
- Optional later: `lab.local.mountain.local` / `saas.local.mountain.local` (SPA parser supports dotted form).

---

## Layout (Complere idea)

| Path | Serves |
|------|--------|
| DocumentRoot (SPA) | Nuxt static build under `frontend/.output/public` (or `frontend/dist` if that is the build output) |
| `Alias /api` or `/backend` | Laravel `backend-laravel/public` |
| Optional | Proxy Nuxt **dev** (`127.0.0.1:3000`) instead of a static build |

Recommended local names for Apache site files (easy to remove later):

- `mtmtp-saas.localhost.conf`
- `mtmtp-lab.localhost.conf`

Prefix `mtmtp-` = Mountain Tourism on this machine only.

---

## One-time machine setup (only when human asks)

Needs root **on the host** (not inside a read-only agent/container). Do **not**
run these steps just because this file exists.

### Quick install (preferred)

From the repo root on the host machine:

```bash
sudo bash scripts/apache-local-install.sh
```

This copies `docs/apache/mtmtp-*.conf`, enables sites, updates `/etc/hosts`, and
reloads Apache. Then start apps:

```bash
cd backend-laravel && php artisan serve --host=127.0.0.1 --port=8000
cd frontend && npm run dev -- --host 127.0.0.1 --port 3000
```

Remove later (when human asks):

```bash
sudo bash scripts/apache-local-remove.sh
```

### Manual steps (same as the script)

### 1. `/etc/hosts`

```bash
echo "127.0.0.1 saas.localhost lab.localhost" | sudo tee -a /etc/hosts
```

### 2. Apache modules

```bash
sudo a2enmod ssl rewrite headers proxy proxy_http alias
sudo apache2ctl configtest && sudo systemctl reload apache2
```

### 3. Example vhost — lab tenant (HTTP, port 80)

Copy to `/etc/apache2/sites-available/mtmtp-lab.localhost.conf`.
Replace `REPO_ROOT` with the real clone path
(e.g. `/home/anish/Documents/Anish/me/pratices/mountain-tourism-platform`).

```apache
<VirtualHost *:80>
    ServerName lab.localhost
    # Option A — static SPA build (after `cd frontend && npm run generate` or `npm run build`)
    DocumentRoot REPO_ROOT/frontend/.output/public

    <Directory REPO_ROOT/frontend/.output/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # Laravel API (Complere uses Alias /backend → public)
    Alias /api REPO_ROOT/backend-laravel/public
    <Directory REPO_ROOT/backend-laravel/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    # If Nuxt expects /api/* and Laravel routes are already under /api,
    # use a rewrite so /api/foo hits public/index.php without double /api.
    # Prefer matching whatever FRONTEND_URL / Vite proxy already use.

    ErrorLog ${APACHE_LOG_DIR}/mtmtp-lab-error.log
    CustomLog ${APACHE_LOG_DIR}/mtmtp-lab-access.log combined
</VirtualHost>
```

**Dev alternative:** DocumentRoot can proxy to Nuxt:

```apache
ProxyPreserveHost On
ProxyPass /api !
ProxyPass / http://127.0.0.1:3000/
ProxyPassReverse / http://127.0.0.1:3000/
```

Keep Laravel on `Alias /api` (or proxy `/api` to `php artisan serve` on `:8000` if PHP is not mod_php/php-fpm on this vhost).

### 4. Example vhost — saas central

`/etc/apache2/sites-available/mtmtp-saas.localhost.conf`:

```apache
<VirtualHost *:80>
    ServerName saas.localhost
    DocumentRoot REPO_ROOT/frontend/.output/public

    <Directory REPO_ROOT/frontend/.output/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    Alias /api REPO_ROOT/backend-laravel/public
    <Directory REPO_ROOT/backend-laravel/public>
        Options FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/mtmtp-saas-error.log
    CustomLog ${APACHE_LOG_DIR}/mtmtp-saas-access.log combined
</VirtualHost>
```

Enable:

```bash
sudo a2ensite mtmtp-lab.localhost mtmtp-saas.localhost
sudo apache2ctl configtest && sudo systemctl reload apache2
```

Optional HTTPS: snakeoil certs like Complere (`SSLEngine on` + `ssl-cert-snakeoil`).

### 5. App env (gitignored `.env` only)

`backend-laravel/.env` (see `.env.example`):

```env
APP_URL=http://lab.localhost
FRONTEND_URL=http://lab.localhost
SANCTUM_STATEFUL_DOMAINS=lab.localhost,saas.localhost,localhost,127.0.0.1
TENANCY_CENTRAL_HOSTS=saas.localhost
CENTRAL_DOMAIN=saas.localhost
```

`frontend/.env`: point API/same-origin to `http://lab.localhost` (or keep Vite proxy if using Nuxt dev).

Postgres + migrate + seed: follow `docs/LARAVEL_LOCAL_SETUP.md` and existing provision commands.
Do not commit secrets.

### 6. Verify

```bash
curl -s -o /dev/null -w "%{http_code}\n" http://lab.localhost/api/health
curl -s -o /dev/null -w "%{http_code}\n" http://saas.localhost/api/health
```

Expect health OK on both hosts when Laravel is wired. Staff login only on `lab.localhost`.

---

## Remove from this machine (only when human asks)

When the human says **machine se Apache setup hata do** / **remove setup**, undo **machine config only**.
Do **not** delete this MD or Docker docs unless asked separately.

```bash
# 1. Disable sites
sudo a2dissite mtmtp-lab.localhost mtmtp-saas.localhost || true

# 2. Remove vhost files
sudo rm -f /etc/apache2/sites-available/mtmtp-lab.localhost.conf
sudo rm -f /etc/apache2/sites-enabled/mtmtp-lab.localhost.conf
sudo rm -f /etc/apache2/sites-available/mtmtp-saas.localhost.conf
sudo rm -f /etc/apache2/sites-enabled/mtmtp-saas.localhost.conf

sudo apache2ctl configtest && sudo systemctl reload apache2

# 3. Remove hosts lines (edit carefully; only mtmtp hosts)
sudo sed -i '/saas.localhost/d;/lab.localhost/d' /etc/hosts
```

Optional (only if human explicitly asks): drop local Postgres DBs created for this project.
Never wipe unrelated Complere / other project vhosts.

---

## Agent rules

| Ask | Do |
|-----|----|
| `Apache setup MD` / this file | Docs only |
| `Apache setup karo` | Apply vhosts + hosts on this machine (with approval for sudo) |
| `machine se setup hata do` | Remove `mtmtp-*` vhosts + hosts lines |
| Docker | Do not touch unless human asks |

---

## Notes

- Complere uses `Alias /backend` + `DocumentRoot public_nuxt`. Mountain Tourism can use `/api` to match Nuxt’s existing `/api` proxy habit.
- If `Alias /api` + Laravel’s own `/api` routes conflict, prefer proxying to `php artisan serve` or a php-fpm pool with a clean `RewriteRule` to `index.php`.
- PDF MVP says no Docker on the VPS path; this Apache local guide aligns with that spirit for the laptop.
