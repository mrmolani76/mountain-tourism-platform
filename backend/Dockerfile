# Mountain Tourism API — dev image (Docker setup requested by user).
# Python 3.12 slim; deps mirror backend/requirements-dev.txt.

FROM python:3.12-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1

# build-essential, libpq-dev, pkg-config: psycopg2-binary needs libpq headers.
RUN apt-get update \
    && apt-get install -y --no-install-recommends build-essential libpq-dev pkg-config curl \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app/backend

COPY requirements.txt requirements-dev.txt ./
RUN python -m pip install --no-cache-dir --upgrade pip \
    && python -m pip install --no-cache-dir -r requirements-dev.txt

COPY . .

EXPOSE 8000

# Dev only: migrate then runserver; prod uses gunicorn (TODO-08). Host port bind in compose. Host port bind in compose.
CMD ["sh", "-c", "python manage.py migrate --no-input && python manage.py runserver 0.0.0.0:8000"]
