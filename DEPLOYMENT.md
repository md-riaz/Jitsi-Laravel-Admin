# Deployment Guide

This guide covers deploying Jitsi Admin to production environments.

## Pre-Deployment Checklist

### Required Infrastructure
- [ ] Web server (Nginx/Apache)
- [ ] PHP 8.3+ with required extensions
- [ ] Database (PostgreSQL/MySQL/SQLite)
- [ ] Mail server (SMTP/SES/Mailgun)
- [ ] Jitsi Meet instance
- [ ] SSL certificate
- [ ] Domain name configured

### Environment Preparation
- [ ] `.env` file configured for production
- [ ] APP_DEBUG set to false
- [ ] APP_KEY generated
- [ ] Database credentials configured
- [ ] Mail credentials configured
- [ ] Jitsi credentials configured
- [ ] Jitsi webhook secret configured if using room lifecycle integration
- [ ] Queue driver set to database
- [ ] Cache driver set to database
- [ ] Session driver set to database

## Step-by-Step Deployment

## Docker Deployment (GitHub URL)

Use this option on platforms that deploy directly from a repository URL.

1. Connect this GitHub repository in your Docker-capable host.
2. Build from the root `Dockerfile`.
3. Configure runtime env vars (`APP_KEY`, database credentials, mail, queue, Jitsi vars, etc.).
4. Set `RUN_MIGRATIONS=true` only when startup migrations are desired.
5. Publish port `8090` (or pass `PORT` from your platform).

Example local validation:

```bash
cp stack.env .env
docker compose up --build -d
```

The included `docker-compose.yml` starts three services:
- `app` on port `8090`
- `queue` for database-backed jobs
- `scheduler` for Laravel scheduled commands

SQLite is used by default and persisted in the `app_database` Docker volume, so no separate database container is required.

For first boot, keep `RUN_MIGRATIONS=true`. After the schema is created, switch it to `false` for normal restarts.

### 1. Server Preparation

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.3 and extensions
sudo add-apt-repository ppa:ondrej/php
sudo apt install -y php8.3 php8.3-fpm php8.3-cli php8.3-mysql \
    php8.3-pgsql php8.3-sqlite3 php8.3-curl \
    php8.3-mbstring php8.3-xml php8.3-zip php8.3-gd

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install -y nginx
sudo systemctl enable nginx
```

### 2. Application Deployment

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/md-riaz/Jitsi-Laravel-Admin.git
cd Jitsi-Laravel-Admin

# Install dependencies
sudo composer install --optimize-autoloader --no-dev
sudo npm install
sudo npm run build

# Set permissions
sudo chown -R www-data:www-data /var/www/Jitsi-Laravel-Admin
sudo chmod -R 775 storage bootstrap/cache

# Configure environment
sudo cp .env.example .env
sudo nano .env  # Edit configuration

# Generate app key
sudo php artisan key:generate

# Run migrations
sudo php artisan migrate --force

# Seed database (if needed)
sudo php artisan db:seed --force

# Cache configuration
sudo php artisan config:cache
sudo php artisan route:cache
sudo php artisan view:cache
```

### 3. Nginx Configuration

Create `/etc/nginx/sites-available/jitsi-admin`:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name your-domain.com www.your-domain.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name your-domain.com www.your-domain.com;
    root /var/www/Jitsi-Laravel-Admin/public;

    # SSL configuration
    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;

    # Index file
    index index.php;

    charset utf-8;

    # Main location
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Static files
    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    # Error pages
    error_page 404 /index.php;

    # PHP handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Logging
    access_log /var/log/nginx/jitsi-admin-access.log;
    error_log /var/log/nginx/jitsi-admin-error.log;
}
```

Enable the site:
```bash
sudo ln -s /etc/nginx/sites-available/jitsi-admin /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 4. SSL Certificate (Let's Encrypt)

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-nginx

# Obtain certificate
sudo certbot --nginx -d your-domain.com -d www.your-domain.com

# Auto-renewal is configured automatically
# Test renewal:
sudo certbot renew --dry-run
```

### 5. Queue Worker Configuration

Create `/etc/supervisor/conf.d/jitsi-admin-worker.conf`:

```ini
[program:jitsi-admin-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/Jitsi-Laravel-Admin/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/Jitsi-Laravel-Admin/storage/logs/worker.log
stopwaitsecs=3600
```

Enable supervisor:
```bash
sudo apt install -y supervisor
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start jitsi-admin-worker:*
```

### 6. Scheduled Tasks (Cron)

If you are using instant meeting lifecycle tracking, make sure both scheduler commands run every minute:

```bash
* * * * * cd /var/www/Jitsi-Laravel-Admin && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler must include:
- `meetings:update-statuses`
- `meetings:cleanup-empty-instant`

Also set these env vars in production when using the Prosody webhook bridge:

```env
JITSI_WEBHOOK_SECRET=change-this-secret
JITSI_EMPTY_ROOM_GRACE_SECONDS=60
```

If the app is served from a subpath like `/jitsiadmin`, the public webhook URL becomes:

```text
https://your-domain.com/jitsiadmin/api/v1/jitsi/events
```

See `docs/JITSI_ROOM_LIFECYCLE_INTEGRATION.md` for the full Prosody integration.


Add to crontab:
```bash
sudo crontab -e -u www-data
```

Add this line:
```cron
* * * * * cd /var/www/Jitsi-Laravel-Admin && php artisan schedule:run >> /dev/null 2>&1
```

### 7. Production Environment Variables

Example `.env` for production:

```env
APP_NAME="Jitsi Admin"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
APP_DEBUG=false
APP_URL=https://your-domain.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=jitsi_admin
DB_USERNAME=jitsi_admin_user
DB_PASSWORD=secure_password_here

BROADCAST_DRIVER=log
CACHE_STORE=database
FILESYSTEM_DISK=local
QUEUE_CONNECTION=database
SESSION_DRIVER=database
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com
MAIL_FROM_NAME="${APP_NAME}"

JITSI_DOMAIN=meet.your-domain.com
JITSI_JWT_ISSUER=jitsi-admin
JITSI_JWT_AUDIENCE=jitsi
JITSI_JWT_SECRET=your_very_secure_secret_key_here
JITSI_JWT_SUB=meet.your-domain.com
```

## Post-Deployment

### Health Checks

```bash
# Check application
curl https://your-domain.com

# Check queue workers
sudo supervisorctl status

# Check logs
tail -f /var/www/Jitsi-Laravel-Admin/storage/logs/laravel.log

# Check database connection
php artisan tinker
>>> DB::connection()->getPdo();

# Check queue jobs
php artisan queue:monitor
```

### Monitoring

#### Application Monitoring
- Monitor queue jobs with `php artisan queue:monitor`
- Configure error tracking (Sentry, Bugsnag)
- Set up uptime monitoring (UptimeRobot, Pingdom)

#### Server Monitoring
- CPU and memory usage
- Disk space
- Network traffic
- Database performance

#### Log Management
```bash
# Rotate logs
sudo nano /etc/logrotate.d/jitsi-admin
```

```
/var/www/Jitsi-Laravel-Admin/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 www-data www-data
    sharedscripts
}
```

### Backup Strategy

#### Database Backup
```bash
# PostgreSQL
pg_dump -U jitsi_admin_user jitsi_admin > backup-$(date +%Y%m%d).sql

# MySQL
mysqldump -u jitsi_admin_user -p jitsi_admin > backup-$(date +%Y%m%d).sql

# Automated daily backup (cron)
0 2 * * * /usr/local/bin/backup-jitsi-admin.sh
```

#### Application Backup
```bash
#!/bin/bash
# backup-jitsi-admin.sh

DATE=$(date +%Y%m%d)
BACKUP_DIR="/backups/jitsi-admin"
APP_DIR="/var/www/Jitsi-Laravel-Admin"

# Create backup directory
mkdir -p $BACKUP_DIR

# Backup database
pg_dump -U jitsi_admin_user jitsi_admin > $BACKUP_DIR/db-$DATE.sql

# Backup uploads/storage
tar -czf $BACKUP_DIR/storage-$DATE.tar.gz $APP_DIR/storage/app

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

### Security Hardening

#### File Permissions
```bash
# Restrict sensitive files
sudo chmod 600 /var/www/Jitsi-Laravel-Admin/.env
sudo chmod -R 755 /var/www/Jitsi-Laravel-Admin
sudo chmod -R 775 /var/www/Jitsi-Laravel-Admin/storage
sudo chmod -R 775 /var/www/Jitsi-Laravel-Admin/bootstrap/cache
```

#### Firewall Configuration
```bash
# Allow HTTP/HTTPS
sudo ufw allow 'Nginx Full'

# Allow SSH (if not already)
sudo ufw allow OpenSSH

# Enable firewall
sudo ufw enable
```

#### Database Security
- Use strong passwords
- Limit database user permissions
- Enable SSL for database connections
- Whitelist application server IP

## Troubleshooting

### Common Issues

**500 Server Error**
- Check `.env` file configuration
- Verify file permissions
- Check logs: `tail -f storage/logs/laravel.log`
- Clear cache: `php artisan cache:clear`

**Queue Jobs Not Processing**
- Check supervisor status: `sudo supervisorctl status`
- Restart workers: `sudo supervisorctl restart jitsi-admin-worker:*`
- Check logs: `tail -f storage/logs/worker.log`

**Email Not Sending**
- Verify SMTP credentials in `.env`
- Test email: `php artisan tinker` → `Mail::raw('Test', fn($m) => $m->to('test@example.com')->subject('Test'));`
- Check mail logs

**Database Connection Failed**
- Verify database credentials
- Check database server is running
- Test connection: `php artisan tinker` → `DB::connection()->getPdo();`

## Scaling

### Horizontal Scaling

1. **Load Balancer**: Use Nginx or HAProxy
2. **Multiple App Servers**: Deploy app to multiple servers
3. **Shared Storage**: Use S3 or shared filesystem for uploads
4. **Centralized Database**: Single database instance for all app servers (with replication for reads)
5. **Database Replication**: Master-slave setup for reads

### Vertical Scaling

- Increase server resources (CPU, RAM)
- Optimize PHP-FPM workers
- Tune database parameters (increase memory, connections)
- Enable OPcache
- Use database connection pooling

### Performance Optimization

```bash
# Enable OPcache
sudo nano /etc/php/8.3/fpm/php.ini

opcache.enable=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0
opcache.save_comments=1
opcache.fast_shutdown=1

# Restart PHP-FPM
sudo systemctl restart php8.3-fpm
```

## Updates and Maintenance

### Updating Application

```bash
cd /var/www/Jitsi-Laravel-Admin

# Backup database
./backup-script.sh

# Pull latest code
git pull origin main

# Update dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build

# Run migrations
php artisan migrate --force

# Clear and rebuild cache
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart services
sudo systemctl restart php8.3-fpm
sudo supervisorctl restart jitsi-admin-worker:*
```

### Zero-Downtime Deployment

Use tools like:
- **Envoyer**: Laravel deployment service
- **Deployer**: PHP deployment tool
- **Capistrano**: General deployment tool

## Support

- GitHub Issues: https://github.com/md-riaz/Jitsi-Laravel-Admin/issues
- Documentation: See README.md and code comments

---

**Last Updated**: February 2026
