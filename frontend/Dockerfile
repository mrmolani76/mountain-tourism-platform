# Mountain Tourism Nuxt SPA — development image
# ssr: false; outputs to .output/public
# Vite proxy /api → backend:8000 (Django tenant API)

FROM node:22-alpine AS builder

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

# Copy built output from builder stage
COPY --from=builder /app/.output/public /usr/share/nginx/html

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]