# Base Framework - Nuxt/Vue Frontend

A reusable Nuxt 3 + Vue 3 base framework frontend with core functionality for building web applications.

## Features

### Core Functionality

- **User Management**: Complete user CRUD interface
- **Authentication**: Login, password reset, and session management
- **General Settings**: Application settings management interface
- **Audit Trail**: View and compare audit logs
- **Dashboard**: Home dashboard page
- **File Upload/View**: File upload and viewing capabilities
- **Responsive Design**: Modern, responsive UI components

### Technical Stack

- **Framework**: Nuxt 3
- **UI Framework**: Vue 3 with Composition API
- **Styling**: SCSS/CSS
- **State Management**: Pinia stores
- **HTTP Client**: Custom API client composables
- **TypeScript**: Full TypeScript support

## Project Structure

```
base-spa/
├── @core/              # Core components and utilities
├── @layouts/           # Layout components
├── assets/             # Static assets (images, styles)
├── components/         # Reusable Vue components
├── composables/        # Composable functions
│   └── api-clients/    # API client composables
├── layouts/            # Nuxt layouts
├── middleware/         # Route middleware
├── pages/              # Application pages
│   ├── index.vue      # Dashboard
│   ├── login.vue      # Login page
│   ├── users/         # User management pages
│   ├── general-settings.vue
│   └── audit-trail.vue
├── plugins/            # Nuxt plugins
├── stores/             # Pinia stores
├── types/              # TypeScript type definitions
└── utils/              # Utility functions
```

## Installation

1. **Install dependencies**
   ```bash
   npm install
   ```

2. **Environment setup**
   ```bash
   cp .env.example .env
   ```
   Update `.env` with your API endpoint and other configuration

3. **Start development server**
   ```bash
   npm run dev
   ```

4. **Build for production**
   ```bash
   npm run build
   ```

## Core Pages

- **Dashboard** (`/`) - Home page
- **Login** (`/login`) - Authentication page
- **Users** (`/users`) - User management (list, create, edit, view)
- **General Settings** (`/general-settings`) - Application settings
- **Audit Trail** (`/audit-trail`) - Audit log viewer
- **User Profile** (`/user-profile`) - Current user profile
- **User Profile Settings** (`/user-profile-setting`) - Profile settings

## API Clients

Core API client composables:

- `useApiClient` - Base API client
- `useSharedApiClient` - Shared API utilities
- `useUserApiClient` - User management API
- `useGeneralSettingApiClient` - General settings API
- `useAuditTrailApiClient` - Audit trail API

## Stores

- `authStore` - Authentication state management
- `socketStore` - WebSocket connection management

## Extending the Framework

### Adding New Pages

1. Create page in `pages/` directory
2. Add route to menu configuration (backend `config/menu.php`)
3. Create API client composable if needed
4. Add TypeScript types if needed

### Adding New API Clients

1. Create composable in `composables/api-clients/`
2. Use `useApiClient` as base
3. Implement CRUD methods as needed

## Configuration

Key configuration files:

- `nuxt.config.ts` - Nuxt configuration
- `tailwind.config.js` - Tailwind CSS configuration
- `themeConfig.ts` - Theme configuration

## License

MIT License
