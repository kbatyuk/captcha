# CAPTCHA Installation & Configuration Guide

## Installation

Install via Composer:

```bash
composer require kbatyuk/captcha
```

The CAPTCHA files will be installed to `/var/html/www/captcha/`

### Required System Fonts

The CAPTCHA library requires these fonts to be installed on your system:

- **DejaVuSans-Bold.ttf** (primary)
- **LiberationSans-Bold.ttf** (fallback)

**Ubuntu/Debian:**
```bash
sudo apt-get install fonts-dejavu fonts-liberation
```

**CentOS/RHEL:**
```bash
sudo yum install dejavu-fonts liberation-fonts
```

**macOS:**
```bash
brew install font-dejavu font-liberation
```

---

## Web Server Configuration

### Nginx Configuration

Create or update your Nginx configuration file:

```nginx
server {
    listen 80;
    server_name your-domain.com;

    root /var/html/www/captcha;
    index captcha.php;

    # Pass PHP files to PHP-FPM
    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;  # or 127.0.0.1:9000
        fastcgi_index captcha.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Essential for session handling
        fastcgi_param HTTP_X_FORWARDED_FOR $proxy_add_x_forwarded_for;
        fastcgi_param HTTP_X_FORWARDED_PROTO $scheme;
    }

    # Block access to hidden files
    location ~ /\. {
        deny all;
    }

    # Allow static assets
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    # Enable gzip compression
    gzip on;
    gzip_types text/plain text/css application/json application/javascript;
}
```

**Test and reload Nginx:**
```bash
sudo nginx -t
sudo systemctl reload nginx
```

**Access the CAPTCHA:**
```
http://your-domain.com/captcha.php
http://your-domain.com/captcha-image.php
```

---

### Apache Configuration

Create or update your Apache virtual host configuration:

```apache
<VirtualHost *:80>
    ServerName your-domain.com
    ServerAdmin admin@your-domain.com

    DocumentRoot /var/html/www/captcha

    # Enable PHP execution
    <FilesMatch "\.php$">
        SetHandler "proxy:unix:/run/php/php-fpm.sock|fcgi://localhost"
        # OR for mod_php:
        # SetHandler application/x-httpd-php
    </FilesMatch>

    # Index files
    DirectoryIndex captcha.php index.php index.html

    # Allow directory access
    <Directory /var/html/www/captcha>
        Options -Indexes +FollowSymLinks
        AllowOverride All
        Require all granted

        # Enable mod_rewrite if needed
        <IfModule mod_rewrite.c>
            RewriteEngine On
            # Add any custom rewrite rules here
        </IfModule>
    </Directory>

    # Deny access to hidden files
    <FilesMatch "^\.|^\.ht">
        Require all denied
    </FilesMatch>

    # Cache static assets
    <FilesMatch "\.(jpg|jpeg|png|gif|ico|css|js)$">
        Header set Cache-Control "max-age=2592000, public"
    </FilesMatch>

    # Enable gzip compression
    <IfModule mod_deflate.c>
        AddOutputFilterByType DEFLATE text/html text/plain text/css text/javascript application/json
    </IfModule>

    ErrorLog ${APACHE_LOG_DIR}/captcha_error.log
    CustomLog ${APACHE_LOG_DIR}/captcha_access.log combined
</VirtualHost>
```

**Enable the site and modules:**
```bash
sudo a2ensite your-domain.com
sudo a2enmod rewrite
sudo a2enmod deflate
sudo apache2ctl configtest
sudo systemctl reload apache2
```

**Access the CAPTCHA:**
```
http://your-domain.com/captcha.php
http://your-domain.com/captcha-image.php
```

---

## SSL/HTTPS Configuration

### Nginx with Let's Encrypt

```nginx
server {
    listen 443 ssl http2;
    server_name your-domain.com;

    ssl_certificate /etc/letsencrypt/live/your-domain.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/your-domain.com/privkey.pem;

    # SSL configuration
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers HIGH:!aNULL:!MD5;
    ssl_prefer_server_ciphers on;

    root /var/html/www/captcha;
    index captcha.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php-fpm.sock;
        fastcgi_index captcha.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}

# Redirect HTTP to HTTPS
server {
    listen 80;
    server_name your-domain.com;
    return 301 https://$server_name$request_uri;
}
```

### Apache with Let's Encrypt

```apache
<VirtualHost *:443>
    ServerName your-domain.com
    DocumentRoot /var/html/www/captcha

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/your-domain.com/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/your-domain.com/privkey.pem

    # SSL configuration
    SSLProtocol TLSv1.2 TLSv1.3
    SSLCipherSuite HIGH:!aNULL:!MD5

    # Rest of your configuration...
</VirtualHost>

# Redirect HTTP to HTTPS
<VirtualHost *:80>
    ServerName your-domain.com
    Redirect / https://your-domain.com/
</VirtualHost>
```

---

## File Permissions

File permissions are set automatically during `composer install`, but you can set them manually if needed:

```bash
# Set directory permissions
sudo chmod 755 /var/html/www/captcha

# Set PHP file permissions
sudo chmod 644 /var/html/www/captcha/*.php

# Ensure web server user owns the files (usually www-data or nginx)
sudo chown -R www-data:www-data /var/html/www/captcha  # For Apache
sudo chown -R nginx:nginx /var/html/www/captcha       # For Nginx
```

---

## PHP Configuration

Ensure these PHP settings in your `php.ini` or PHP-FPM pool config:

```ini
; Session handling
session.save_path = "/var/lib/php/sessions"
session.cookie_httponly = 1
session.cookie_secure = 1        ; If using HTTPS
session.cookie_samesite = "Lax"

; GD Library for image generation (required)
extension=gd

; Error handling
display_errors = Off             ; Don't expose errors in production
log_errors = On
error_log = /var/log/php_errors.log
```

**Verify GD is installed:**
```bash
php -m | grep GD
```

If not installed:
```bash
# Ubuntu/Debian
sudo apt-get install php-gd

# CentOS/RHEL
sudo yum install php-gd
```

Then restart PHP-FPM:
```bash
sudo systemctl restart php-fpm
```

---

## Session Storage Setup

Create a dedicated session directory:

```bash
sudo mkdir -p /var/lib/php/sessions
sudo chmod 1733 /var/lib/php/sessions
sudo chown root:root /var/lib/php/sessions
```

Update `php.ini`:
```ini
session.save_path = "/var/lib/php/sessions"
```

---

## Troubleshooting

### CAPTCHA images not displaying

1. **Check GD Library**: `php -m | grep GD`
2. **Check fonts**: Are `DejaVuSans-Bold.ttf` and `LiberationSans-Bold.ttf` installed?
3. **Check PHP errors**: `tail -f /var/log/php_errors.log`
4. **Check permissions**: Ensure web server can read PHP files

### Session validation failing

1. **Check session.save_path**: Ensure directory is writable by web server
2. **Check PHP error log** for session errors
3. **Verify session.cookie_path**: May need to be set in `php.ini`

### 403 Forbidden or 404 Not Found

1. **Check file permissions**: `ls -la /var/html/www/captcha/`
2. **Verify web server config**: Check Nginx/Apache error logs
3. **Check firewall**: Ensure port 80/443 is open
4. **Verify DocumentRoot**: Confirm it points to `/var/html/www/captcha`

---

## Security Best Practices

1. **Use HTTPS** - Always encrypt traffic in production
2. **Set secure cookies** - Enable `httponly` and `secure` flags
3. **Validate input** - Always sanitize user input
4. **Log suspicious activity** - Monitor CAPTCHA validation attempts
5. **Rate limit** - Implement rate limiting to prevent brute force attacks
6. **Hide errors** - Set `display_errors = Off` in production
7. **Keep PHP updated** - Regularly update PHP and extensions

---

## Support

For issues or questions, visit: https://github.com/kbatyuk/captcha/issues
