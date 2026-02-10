# Jitsi Admin - Professional Meeting Management Platform

[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![Laravel](https://img.shields.io/badge/Laravel-12-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.3+-purple.svg)](https://php.net)

A production-ready, enterprise-grade meeting management platform built on Laravel 12 and Jitsi Meet. Provides comprehensive scheduling, access control, invitations, and notifications for professional video conferencing.

## 🌟 Features

### Core Functionality
- **Smart Scheduling** - Single and recurring meetings with timezone support
- **Time-Based Access Control** - Configurable join windows and participant validation
- **JWT Authentication** - Secure, token-based access to Jitsi meetings
- **Multi-Tenant Architecture** - Organization-based separation with role management
- **Complete Audit Trail** - Full visibility into meeting lifecycle and participant actions

### Communication
- **Email Invitations** - Automated invitation emails with calendar attachments (.ics)
- **Meeting Reminders** - Configurable reminder notifications before meetings
- **Status Updates** - Automatic notifications for meeting updates and cancellations
- **Guest Access** - Secure invite links for external participants

### Administration
- **Dashboard** - Comprehensive admin interface powered by Tyro Dashboard
- **User Management** - Role-based access control (super-admin, org-admin, host, member)
- **Meeting Management** - Full CRUD operations with participant tracking
- **Audit Logs** - Detailed event logs for compliance and monitoring

## 📋 Requirements

- PHP 8.3 or higher
- Composer
- Node.js & NPM (for asset compilation)
- SQLite/PostgreSQL/MySQL database
- A Jitsi Meet instance (self-hosted or Jitsi.org)

## 🚀 Quick Start

### Installation

1. **Clone the repository**
```bash
git clone https://github.com/md-riaz/Jitsi-Laravel-Admin.git
cd Jitsi-Laravel-Admin
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Set up database**
```bash
touch database/database.sqlite
php artisan migrate --seed
```

Demo credentials: `admin@example.com` / `password`

5. **Configure Jitsi** (edit `.env`):
```env
JITSI_DOMAIN=meet.example.com
JITSI_JWT_SECRET=your-secret-key
```

6. **Build and run**
```bash
npm run build
php artisan serve
```

Visit: http://localhost:8000

## 📖 Key Concepts

### Meeting Workflow
1. Create meeting with participants
2. System sends email invitations with .ics files
3. Guests accept via secure link
4. Join during allowed time window
5. Backend issues JWT for Jitsi access
6. All actions logged for audit

### Email Notifications
- **Invitation** - Sent when invited (includes calendar file)
- **Reminder** - 10 minutes before start
- **Updated** - When meeting details change
- **Cancelled** - When meeting is cancelled

### Access Control
- Join windows prevent early/late access
- JWT tokens validate participants
- Role-based permissions (Tyro RBAC)
- Meeting visibility controls

## 🛠️ Production Deployment

### Docker-first deployment (GitHub URL compatible)

This repository now includes a portable `Dockerfile`, so most Docker-native platforms can deploy directly from the GitHub repo URL.

1. Point your platform to this GitHub repository.
2. Let it detect and build the root `Dockerfile`.
3. Set required environment variables (`APP_KEY`, DB settings, mail settings, Jitsi settings, etc.).
4. Set `RUN_MIGRATIONS=true` only when you want startup migrations executed by the container.
5. Expose container port `8090` (or provide platform `PORT`).

Local Docker smoke test:

```bash
docker compose up --build
```

App URL: `http://localhost:8090`

1. **Optimize**
```bash
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

2. **Environment**
```env
APP_ENV=production
APP_DEBUG=false
QUEUE_CONNECTION=database
CACHE_STORE=database
SESSION_DRIVER=database
```

3. **Queue Worker** (use Supervisor)
```bash
php artisan queue:work database --tries=3
```

4. **Permissions**
```bash
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

See [SETUP.md](SETUP.md) for detailed deployment guide.

## 📚 Documentation

- **[Setup Guide](SETUP.md)** - Detailed installation instructions
- **[Project Spec](PROJECT_SPEC.md)** - Complete feature specifications
- **[Architecture](ARCHITECTURE.md)** - System design and patterns
- **[Domain Rules](DOMAIN_RULES.md)** - Business logic and constraints

## 🔧 Development

```bash
# Run tests
php artisan test

# Watch assets
npm run dev -- --watch

# Check logs
tail -f storage/logs/laravel.log

# View failed queue jobs
php artisan queue:failed
```

## 🤝 Contributing

Contributions welcome! Please:
1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push and open Pull Request

## 📄 License

MIT License - see [LICENSE](LICENSE) file

## 🙏 Credits

Built with [Laravel](https://laravel.com), [Jitsi Meet](https://jitsi.org), and [Tyro](https://github.com/hasinhayder/tyro)

---

**Professional Meeting Management Made Simple**
