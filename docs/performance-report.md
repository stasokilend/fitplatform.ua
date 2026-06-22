# FitPlatform performance report

## Findings
- The application performs SQL directly from controllers/views. This makes query reuse and systematic optimization harder.
- Several dashboard and trainer screens can execute multiple queries per request, especially around workout details, chat unread counts, notifications and trainer-client summaries.
- Some aggregate counts are calculated with subqueries. They are acceptable for current compatibility, but should be monitored on larger datasets.
- Dynamic pagination is used in some places. Values are cast to integers; future work should bind them explicitly as `PDO::PARAM_INT` where supported.

## Recommended indexes
Add after validating production query plans:
- `users(email, is_active)` for login.
- `user_profiles(user_id)` for profile joins.
- `workout_plans(user_id, status, created_at)` for dashboard workout lists.
- `plan_exercises(plan_id, order_num)` for workout detail rendering.
- `notifications(user_id, is_read, created_at)` for notification lists and unread counts.
- `chat_messages(chat_id, is_read, created_at)` and `chats(user1_id, user2_id)` for chat screens.
- `trainer_clients(trainer_id, status, client_id)` for trainer dashboards.
- `user_activity_data(user_id, activity_date)` for Google Fit history.

## Optimizations applied
- Introduced a single PDO connection factory so connection options, charset and error handling are not repeated.
- Added environment-based configuration to support Linux/VPS/Docker without code edits.
- Added Docker services for repeatable local performance testing with MySQL 8.

## Manual performance checks
- Exercise dashboard, trainer clients, chat and notification pages with realistic data volumes.
- Compare query plans before adding indexes in production.
- Add slow-query logging on MySQL for real traffic before further optimization.
