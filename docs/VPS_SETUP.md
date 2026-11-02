# VPS Setup Guide — Mountain Tourism Platform API

No Docker. Direct install on Ubuntu 24.04 LTS VPS.

## 1. Linux user

```bash
sudo adduser --disabled-password --gecos "" mtmtp
sudo usermod -aG sudo mtmtp
sudo mkdir -p /home/mtmtp/app /home/mtmtp/backups
sudo chown -R mtmtp:mtmtp /home/mtmtp
```

## 2. PostgreSQL

```bash
sudo apt update
sudo apt install -y postgresql postgresql-contrib
sudo -u postgres psql -c "CREATE USER mtmtp WITH PASSWORD 'CHANGE_ME';"
sudo -u postgres psql -c "CREATE DATABASE mountain OWNER mtmtp;"
sudo -u postgres psql -c "GRANT ALL PRIVILEGES ON DATABASE mountain TO mtmtp;"
```

## 3. Python + dependencies

```bash
sudo apt install -y python3.12 python3.12-venv python3-pip
sudo -u mtmtp python3.12 -m venv /home/mtmtp/venv
sudo -u mtmtp /home/mtmtp/venv/bin/pip install -r /home/mtmtp/app/backend/requirements.txt
```

## 4. Environment file

Create `/home/mtmtp/app/backend/.env` (never commit):

```bash
DJANGO_SETTINGS_MODULE=config.settings
DATABASE_URL=postgres://mtmtp:CHANGE_ME@localhost:5432/mountain
SECRET_KEY=generate-a-strong-key-here
DEBUG=0
ALLOWED_HOSTS=YOUR_IP.sslip.io
TENANCY_CENTRAL_HOSTS=saas.YOUR_IP.sslip.io
TENANCY_CENTRAL_PREFIX=saas.
```

## 5. Nginx

```bash
sudo apt install -y nginx
sudo tee /etc/nginx/sites-available/mtmtp > /dev/null <<'EOF'
server {
    listen 80;
    server_name YOUR_IP.sslip.io saas.YOUR_IP.sslip.io;

    location / {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF
sudo ln -s /etc/nginx/sites-available/mtmtp /etc/nginx/sites-enabled/
sudo nginx -t && sudo systemctl reload nginx
```

## 6. systemd service

```bash
sudo tee /etc/systemd/system/mtmtp.service > /dev/null <<'EOF'
[Unit]
Description=Mountain Tourism Platform API
After=network.target postgresql.service

[Service]
User=mtmtp
Group=mtmtp
WorkingDirectory=/home/mtmtp/app/backend
EnvironmentFile=/home/mtmtp/app/backend/.env
ExecStart=/home/mtmtp/venv/bin/gunicorn config.wsgi:application --bind 127.0.0.1:8000 --workers 3
Restart=on-failure
RestartSec=5

[Install]
WantedBy=multi-user.target
EOF
sudo systemctl daemon-reload
sudo systemctl enable mtmtp
sudo systemctl start mtmtp
```

## 7. Migrate

```bash
sudo -u mtmtp /home/mtmtp/venv/bin/python /home/mtmtp/app/backend/manage.py migrate
```

## 8. SSL (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d YOUR_IP.sslip.io -d saas.YOUR_IP.sslip.io
```

## 9. Backups

Nightly pg_dump via cron:

```bash
sudo -u mtmtp crontab -e
# Add: 0 2 * * * pg_dump -U mtmtp mountain | gzip > /home/mtmtp/backups/mtmtp_$(date +\%F).sql.gz
# Keep last 7: find /home/mtmtp/backups -name '*.sql.gz' -mtime +7 -delete
```

## Domain-later note

When a real domain is available: update Nginx `server_name` + `.env` `ALLOWED_HOSTS` + `TENANCY_CENTRAL_HOSTS`. No code change needed.

## No Docker

This setup uses no Docker. All services run directly on the host via systemd + Nginx. Docker is optional for local dev only.