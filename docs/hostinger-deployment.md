# Hostinger Deployment

This application is Laravel 11. The safest Hostinger layout keeps the Laravel application outside `public_html` and points the domain document root at the application's `public` directory.

## 1. Create the Hostinger services

In hPanel, create:

- A MySQL database and database user.
- An email mailbox for application mail, if order and account notifications are required.
- An SSL certificate for the domain.

Use PHP 8.2 or newer and enable the Laravel extensions for cURL, Fileinfo, Mbstring, OpenSSL, PDO, PDO MySQL, Tokenizer, XML, DOM, and BCMath.

## 2. Upload the application

Upload the repository to a private directory such as:

```text
/home/ACCOUNT/onje-casa
```

Set the domain's document root to:

```text
/home/ACCOUNT/onje-casa/public
```

Do not set the document root to the Laravel project root. This keeps `.env`, `app`, `config`, and `storage` outside the web-facing directory.

If the Hostinger plan cannot change the document root, place the contents of this project's `public` directory in `public_html`, keep the rest of the application one directory above it, and update the two paths in `public/index.php` to point to that application directory.

## 3. Configure production

From the project directory:

```bash
cp .env.example .env
php artisan key:generate --force
```

Update `.env` with the real values:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hostinger_database_name
DB_USERNAME=hostinger_database_user
DB_PASSWORD=database_password

SESSION_DRIVER=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

Also configure SMTP, `ADMIN_ORDER_NOTIFICATION_EMAIL`, live Paystack keys, and `SANCTUM_STATEFUL_DOMAINS` with the real domain. Never commit the production `.env` file.

## 4. Install and initialize

Run these commands over SSH, or use Hostinger's PHP/Composer tools:

```bash
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Build frontend assets before uploading if the deployment machine has Node.js:

```bash
npm ci
npm run build
```

The application needs write access to `storage` and `bootstrap/cache`. On shared hosting, `775` is usually appropriate when the PHP process uses the same account:

```bash
chmod -R 775 storage bootstrap/cache
```

Product images and videos are written directly under `public/uploads` and POS media under `public/assets`, so those directories must also be writable for admin uploads.

## 5. Scheduled work

Add this Hostinger cron job, using the full PHP and application paths from hPanel:

```cron
* * * * * /usr/bin/php /home/ACCOUNT/onje-casa/artisan schedule:run >> /dev/null 2>&1
```

The scheduler handles monthly reports. If database queue jobs are enabled, add a suitable queue worker strategy in hPanel; otherwise queued notifications will remain in the `jobs` table.

## 6. Production checks

After deployment, verify:

- `https://your-domain.com/up` returns successfully.
- Login, registration, checkout, Paystack callback, and admin login OTP work.
- Product images, uploads, and storage-backed files load over HTTPS.
- `storage/logs/laravel.log` is writable and contains no connection errors.
- The browser console has no mixed-content or missing Vite asset errors.

Use Paystack live credentials only after the test checkout flow succeeds. The callback URL is:

```text
https://your-domain.com/checkout/paystack/callback
```
