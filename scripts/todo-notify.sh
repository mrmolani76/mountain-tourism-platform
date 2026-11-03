#!/bin/bash
# Mountain Tourism — send "todo complete" notification email.
# Sends to NOTIFY_TO (default anhali736@gmail.com) via Gmail SMTP.
# Credentials: scripts/.notify.env (gitignored, never committed).
#
# Usage:
#   bash scripts/todo-notify.sh            # notify about the most recently completed item
#   bash scripts/todo-notify.sh 28         # notify about a specific item id
set -euo pipefail

REPO="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="$REPO/scripts/.notify.env"

if [ ! -f "$ENV_FILE" ]; then
  echo "[WARN] $ENV_FILE missing — skipping email notification." >&2
  exit 0
fi
# shellcheck disable=SC1090
set -a; source "$ENV_FILE"; set +a

ITEM_ID="${1:-}"

python3 - "$REPO" "$ITEM_ID" <<'EOF'
import re, smtplib, ssl, sys
from pathlib import Path
from email.message import EmailMessage
import os

repo, item_id = Path(sys.argv[1]), (sys.argv[2] or None)
completed = repo / 'work' / 'completed'
files = sorted(completed.glob('*.md'), key=lambda p: p.stat().st_mtime)
if not files:
    print('[WARN] no completed items found; skipping email.')
    sys.exit(0)

if item_id:
    match = [f for f in files if f.name.startswith(f'{int(item_id):02d}-') or f.name.startswith(f'{item_id}-')]
    if not match:
        print(f'[WARN] no completed item found for id {item_id}; skipping email.')
        sys.exit(0)
    target = match[-1]
else:
    target = files[-1]

text = target.read_text(errors='replace')
fm = dict(re.findall(r'^([A-Za-z_]+):\s*(.*)$', text, re.M))
title = fm.get('title', target.stem).strip()
item_no = fm.get('id', target.stem.split('-')[0]).strip()
date = fm.get('updated') or fm.get('created') or ''

m = re.search(r'## Completion Log\s*(.*?)(?:\n## |\Z)', text, re.S)
log = (m.group(1).strip() if m else '').strip()
log = log[:1500] + ('…' if len(log) > 1500 else '')

body = f"""Mountain Tourism — Todo complete

Item : {item_no} — {title}
Date : {date}
File : work/completed/{target.name}

Completion log:
{log or '(see repo)'}

— Automated notification (mountain-tourism-platform)
"""

msg = EmailMessage()
msg['Subject'] = f'[Mountain Tourism] Todo {item_no} complete — {title}'
msg['From'] = f'{os.environ.get("SMTP_FROM_NAME", "Mountain Tourism Notifier")} <{os.environ["SMTP_FROM"]}>'
msg['To'] = os.environ.get('NOTIFY_TO', 'anhali736@gmail.com')
msg.set_content(body)

host = os.environ['SMTP_HOST']
port = int(os.environ.get('SMTP_PORT', '587'))
user = os.environ['SMTP_USER']
password = os.environ['SMTP_PASSWORD']

try:
    with smtplib.SMTP(host, port, timeout=20) as server:
        server.ehlo()
        server.starttls(context=ssl.create_default_context())
        server.login(user, password)
        server.send_message(msg)
    print(f'[  OK ] Email sent to {msg["To"]} — item {item_no}')
except Exception as exc:
    # Notification must never fail the workflow.
    print(f'[WARN] Email failed: {exc}', file=sys.stderr)
    sys.exit(0)
EOF
