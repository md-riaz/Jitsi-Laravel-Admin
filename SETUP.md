# Jitsi Meeting Orchestration Platform - Setup Guide

This is a Laravel 12-based meeting scheduling and orchestration platform built on top of Jitsi Meet, providing Matrix-like meeting experience with access control, JWT authentication, and beautiful UI.

## 🚀 Quick Start

### Prerequisites
- PHP 8.3+
- Composer
- Node.js & NPM
- SQLite (or PostgreSQL/MySQL)

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

3. **Set up environment**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Configure database**

The project is pre-configured for SQLite. Create the database file:
```bash
touch database/database.sqlite
```

Update `.env` with correct path:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

5. **Configure Jitsi settings**

Add to `.env`:
```env
JITSI_DOMAIN=meet.yourdomain.com
JITSI_JWT_ISSUER=your-app
JITSI_JWT_AUDIENCE=jitsi
JITSI_JWT_SECRET=your-secret-key-here
JITSI_JWT_SUB=meet.yourdomain.com

TYRO_DASHBOARD_PREFIX=dashboard
TYRO_DASHBOARD_APP_NAME="Jitsi Meeting Orchestration"
TYRO_DASHBOARD_LOGO=/images/logo.svg
```

6. **Run migrations and seeders**
```bash
php artisan migrate --force
php artisan db:seed
```

This will create:
- Roles and privileges (super-admin, org-admin, host, member)
- Demo user (admin@example.com / password)
- Sample meetings

7. **Build assets**
```bash
npm run build
```

8. **Start the development server**
```bash
php artisan serve
```

Visit: http://localhost:8000

## 📋 Features

### ✅ Implemented

#### Core Meeting System
- **Meeting Model** - UUID-based, immutable room names, join window calculations
- **Time-based Access Control** - Server-side enforcement of join windows
- **JWT Token Generation** - Secure, short-lived tokens for Jitsi authentication
- **Audit Trail** - All meeting events logged to database

#### Public Meeting Pages
- **Beautiful UI** - Gradient design matching Matrix.io aesthetic
- **Live Status Badges** - Real-time meeting status (Live, Upcoming, Ended)
- **Countdown Timer** - Real-time countdown for upcoming meetings
- **Jitsi Integration** - IFrame API ready for embedding video calls
- **Responsive Design** - Works on desktop and mobile

#### Dashboard
- **Tyro Dashboard Integration** - Professional admin interface
- **My Meetings Page** - View upcoming and past meetings
- **RBAC** - Role-based access control (super-admin, org-admin, host, member)
- **Protected Routes** - All dashboard pages require authentication

#### API Endpoints
- `GET /meet/{meeting}` - Public meeting page
- `POST /api/meetings/{meeting}/join` - Join meeting (with validation)

### 🔄 In Progress

- Meeting CRUD operations
- Invite system with signed tokens
- Guest join flow
- Email notifications
- Calendar (.ics) generation
- Reminder jobs

## 🎯 Usage

### Create a Test Meeting

Run the demo seeder to create sample meetings:
```bash
php artisan db:seed --class=DemoDataSeeder
```

This creates:
- Test user: admin@example.com / password
- 3 sample meetings (live, upcoming, future)

### Access Meetings

Visit any meeting using:
```
http://localhost:8000/meet/{meeting_id}
```

Example meetings created by seeder:
- Live meeting: Check the seeder output for IDs
- Upcoming meeting: Shows countdown timer
- Future meeting: Shows when join window opens

### Dashboard Access

1. Login at: http://localhost:8000/login
2. Email: admin@example.com
3. Password: password
4. Navigate to: http://localhost:8000/dashboard

## 🏗️ Architecture

### Tech Stack
- **Backend:** Laravel 12, PHP 8.3
- **RBAC:** Tyro + Tyro Login + Tyro Dashboard
- **Auth:** Laravel Sanctum, session-based
- **JWT:** firebase/php-jwt
- **Database:** SQLite (dev), PostgreSQL/MySQL (production)
- **Frontend:** Blade templates, vanilla JS
- **Video:** Jitsi Meet (external)

### Key Principles
1. **Jitsi is External** - Never query Jitsi for meeting state
2. **Backend is Authority** - All access decisions on server
3. **Immutable Room Names** - Generated once, never changed
4. **Time Window Enforcement** - Join only during allowed period
5. **Audit Everything** - All events logged

### Directory Structure
```
app/
├── Http/Controllers/
│   ├── Api/            # API endpoints
│   ├── Web/            # Web pages
│   └── Dashboard/      # Dashboard controllers
├── Models/             # Eloquent models
└── Services/           # Business logic
    ├── JitsiJwtService.php
    └── MeetingInviteService.php

database/
├── migrations/         # Database schema
└── seeders/           # Data seeders

resources/views/
├── dashboard/         # Dashboard pages (Tyro)
└── meeting/          # Public meeting pages
```

## 🧪 Testing

Run the test suite:
```bash
php artisan test
```

Current tests:
- Meeting room name generation
- Room name immutability
- Join window validation

## 🔐 Security

### Implemented
- ✅ Server-side join window validation
- ✅ Immutable room names
- ✅ Short-lived JWT tokens (2 hours)
- ✅ Role-based access control
- ✅ CSRF protection
- ✅ Audit logging

### Todo
- Rate limiting on join endpoints
- Invite token signing
- Email verification for guests

## 📖 Domain Rules

### Meeting Rules
- Every meeting has exactly one room_name
- room_name is generated server-side
- room_name is immutable after creation

### Time Rules
Join allowed only if:
```
now >= start_at - join_early_minutes
AND
now <= end_at + join_late_minutes
```

### Security Rules
- JWTs are short-lived (2 hours)
- JWTs are per-meeting
- JWTs must match room_name
- All access decisions are server-side

## 🤝 Contributing

1. Follow existing code style
2. Add tests for new features
3. Update documentation
4. Follow domain rules in DOMAIN_RULES.md
5. Respect architecture in ARCHITECTURE.md

## 📄 License

MIT License - see LICENSE file for details

## 🆘 Support

For issues or questions:
1. Check existing issues on GitHub
2. Review documentation in docs/
3. Create a new issue with details

## 🎓 Credits

Built with:
- [Laravel](https://laravel.com)
- [Tyro RBAC](https://github.com/hasinhayder/tyro)
- [Jitsi Meet](https://jitsi.org)
- [Tailwind CSS](https://tailwindcss.com)
