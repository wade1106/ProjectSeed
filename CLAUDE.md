# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

DPP (Digital Product Passport) is a web application with three main components:
- **dpp-api-service**: Laravel 12 API backend with JWT authentication
- **dpp-web**: Vue 3 frontend with Pinia state management
- **dpp-ai-engine**: AI engine component (minimal setup)

## Development Commands

### API Service (Laravel)
```bash
cd dpp-api-service

# Install dependencies
composer install

# Setup database & run migrations
php artisan migrate

# Start development server and queue worker
composer run dev

# Run tests
composer run test

# Build frontend assets
npm run build
```

### Web Frontend (Vue)
```bash
cd dpp-web

# Install dependencies
npm install

# Development server with hot reload
npm run serve

# Build for production
npm run build

# Lint code
npm run lint
```

## Architecture

### Authentication Flow
- JWT-based authentication with separate login endpoints for admins and users
- Token blacklisting for logout functionality
- Route guards in Vue frontend check authentication and role permissions
- Pinia store manages authentication state with localStorage persistence

### Key API Endpoints
- `POST /api/v1/admin/login` - Admin authentication
- `POST /api/v1/user/login` - User authentication
- `POST /api/v1/logout` - Logout (requires auth)
- `GET/POST/PUT/DELETE /api/v1/admin/management/*` - Admin CRUD operations

### Frontend Structure
- Vue Router with hash history mode
- Role-based routing (admin vs user routes)
- MainLayout.vue for user interface, AdminLayout.vue for admin interface
- Pinia auth store with user type and permissions management

### Database & Models
- MySQL database with models for:
  - Admin (administrator accounts)
  - User (end users)
  - Customer (business customers)
  - Product (product data)
  - Role/Permission (RBAC system)
  - TokenBlacklist (JWT token invalidation)

## Key Configuration Files

- `dpp-api-service/.env` - API environment variables (database, JWT secret)
- `dpp-api-service/config/app.php` - Laravel app configuration
- `dpp-web/vue.config.js` - Vue build configuration
- `dpp-web/src/router/index.js` - Route definitions and navigation guards
- `dpp-web/src/stores/auth.js` - Authentication state management