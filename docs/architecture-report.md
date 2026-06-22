# FitPlatform architecture report

## Scope
Audit completed for the existing PHP application without changing business flows or UI layout. The project is a classic PHP app with page entry points, controller classes/functions, shared includes, SQL schema, and JSON endpoints.

## Entry points
- Public pages: `index.php`, `login.php`, `register.php`, `dashboard.php`, `profile-setup.php`, `google-fit-callback.php`, `logout.php`, `test_session.php`.
- Admin pages: `admin/index.php`, `admin/login.php`, `admin/logout.php`, `admin/users.php`, `admin/exercises.php`, `admin/restrictions.php`.
- API endpoints: `api/chat.php`, `api/gamification.php`, `api/google-fit.php`, `api/health-stats.php`, `api/heart-rate.php`, `api/notifications.php`, `api/trainer.php`, `api/workout.php`, `api/workout-generate.php`, `api/workout-track.php`.
- Dashboard views are loaded from `views/dashboard/**` by `dashboard.php` and related controllers.

## Controllers
Existing controllers are in `controllers/`:
- `AuthController.php` handles login, registration and logout POST actions.
- `ProfileController.php` contains profile update/read helper functions.
- `WorkoutController.php`, `WorkoutGenerator.php`, `HealthController.php`, `TrainerController.php`, `ChatController.php`, `NotificationController.php`, `GamificationController.php`, `GoogleFitController.php` contain application domain logic and database access.

## Models
The project does not currently have dedicated PHP model classes. Data models are represented by SQL tables and associative arrays returned from PDO. A PSR-4 compatible `app/Models/` directory has been created as a target for gradual migration.

## SQL queries
SQL is currently embedded in controllers, includes, admin pages, API files and dashboard views. The audited query types include user authentication/profile queries, workout plans/exercises, health metrics, notifications, chats, trainer-client relationships, Google Fit tokens/activity data, exercises/restrictions and admin CRUD operations. Most database operations already use PDO prepared statements; dynamic `LIMIT/OFFSET` clauses are cast to integers before concatenation and should be migrated to bound integer parameters during continued refactoring.

## Request/session/header usage
Audited superglobals and redirects:
- `$_GET` and `$_POST` are used in page controllers and API endpoints for actions, IDs, filters and form data.
- `$_SESSION` is used for authentication state, flash messages and role checks.
- `header()` is used for redirects and JSON content types.
- `$_REQUEST` was included in the audit pattern and should remain avoided for new code.

## Database connections
Legacy files include `config/database.php`, which now delegates to `App\Database\Database::getConnection()`. This keeps the existing global `$pdo` compatibility layer while providing a single future connection point.

## File uploads
No direct `move_uploaded_file()` usage was found during audit. `storage/uploads/` has been introduced as a non-public storage target for future uploads. Future upload handlers must validate MIME type, enforce size limits, generate random names and reject PHP/executable extensions.

## External integrations
- Google Fit/OAuth is implemented by `controllers/GoogleFitController.php`, `google-fit-callback.php` and `config/google-fit.php`.
- API integrations use cURL for Google OAuth/Fit calls.
- Mail configuration keys were added to `.env.example`; no direct `mail()` integration was identified in the current codebase.

## New compatibility architecture
- `config/Env.php` loads `.env` values safely and falls back to defaults.
- `app/Database/Database.php` provides the single PDO factory.
- `app/Core/ErrorHandler.php` centralizes error display/logging behavior.
- `includes/Csrf.php` provides CSRF token helpers.
- `app/Controllers`, `app/Services`, `app/Repositories`, `app/Models`, `app/Middleware` were created for gradual migration without breaking existing endpoints.

## Security observations
- Hard-coded database host and Google OAuth secrets were present and have been moved behind environment configuration.
- Login previously did not regenerate the PHP session ID; this is now done after successful password verification.
- Session cookie hardening has been added.
- Login, registration and profile setup POST forms now include CSRF protection. API clients need a coordinated token strategy before enforcing CSRF globally across all AJAX endpoints.
