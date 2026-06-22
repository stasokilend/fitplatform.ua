# FitPlatform

FitPlatform is a PHP fitness platform with user profiles, generated workouts, trainer workflows, health metrics, notifications, chat and Google Fit integration.

## Requirements
- PHP 8.0+
- MySQL/MariaDB
- PDO MySQL extension
- Composer (recommended)
- Docker and Docker Compose (optional)

## Configuration
1. Copy `.env.example` to `.env`.
2. Set `APP_URL` for your installation path, for example `http://localhost`, `https://fitplatform.ua` or `https://domain.com/subfolder`.
3. Set database credentials.
4. Set Google OAuth credentials if Google Fit is used.

Secrets must not be committed. `.env` is ignored by Git.

## Database setup
Import `sql/install.sql` into the configured database.

## OpenServer
1. Place the project in the OpenServer domains directory.
2. Copy `.env.example` to `.env`.
3. Set `APP_URL` to the local domain.
4. Import `sql/install.sql`.
5. Open the configured local domain in the browser.

## Linux VPS/shared hosting
1. Upload the project to the web root.
2. Point the virtual host document root to the project directory.
3. Create `.env` from `.env.example` and configure DB credentials.
4. Import `sql/install.sql`.
5. Ensure `storage/logs` and `storage/uploads` are writable by the web server user.
6. Run `composer install` if Composer is available.

## Docker
```bash
docker compose up -d
```
The app is exposed on `http://localhost:8080` by default. MySQL is initialized from `sql/install.sql`.

## Tests
```bash
composer install
vendor/bin/phpunit
```

## Project structure
- `api/` JSON endpoints.
- `controllers/` legacy controllers and domain classes.
- `includes/` shared authentication/session/helper utilities.
- `views/` UI templates.
- `config/` environment and integration configuration.
- `app/` new PSR-4 architecture target for gradual migration.
- `storage/logs/` application logs.
- `storage/uploads/` non-public upload storage target.
- `docs/` architecture and performance reports.
