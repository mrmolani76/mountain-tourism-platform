# Mountain Tourism Nuxt SPA — development image
# ssr: false; outputs to .output/public
# Same-origin /api: nginx proxies /api → laravel:8000. Empty api base = same-origin.

FROM node:22-alpine AS builder

# Public runtime config baked into the static build (ssr: false).
ARG NUXT_PUBLIC_API_BASE_URL=""
ARG NUXT_PUBLIC_ENABLE_REALTIME="false"
ARG ENV=local
ARG BACKEND_URL=http://laravel:8000
ENV NUXT_PUBLIC_API_BASE_URL=$NUXT_PUBLIC_API_BASE_URL \
    NUXT_PUBLIC_ENABLE_REALTIME=$NUXT_PUBLIC_ENABLE_REALTIME \
    ENV=$ENV \
    BACKEND_URL=$BACKEND_URL

# Install pnpm (project uses .npmrc with shamefully-hoist)
RUN corepack enable && corepack prepare pnpm@latest --activate

WORKDIR /app

# Copy package files first for layer caching
COPY package.json pnpm-lock.yaml .npmrc ./

# Install deps (ignore scripts; we run postinstall manually later)
RUN pnpm install --no-frozen-lockfile --ignore-scripts

# Copy the rest of the application (read-only)
COPY . .

# Generate Iconify CSS (postinstall skipped via --ignore-scripts above)
RUN pnpm run build:icons

# Generate the static SPA (SSR=false) — emits index.html + 200.html in .output/public
RUN pnpm exec nuxt generate

###############################################################################
# Stage 2: Production runtime (nginx serving .output/public)
###############################################################################

FROM nginx:1.27-alpine AS production

# Remove default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom nginx config that proxies /api to the backend
COPY docker/nginx-spa-proxy.conf /etc/nginx/conf.d/default.conf

# nitro.output.publicDir is overridden to `.output/` in nuxt.config.ts,
# so the static site lands directly in /app/.output (not /app/.output/public).
COPY --from=builder /app/.output /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]