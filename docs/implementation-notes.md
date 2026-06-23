# FitPlatform technical improvements

## Caching
`includes/Cache.php` provides a small cache facade. It prefers Redis when `REDIS_HOST` is set and the Redis PHP extension is available, falls back to Memcached when `MEMCACHED_HOST` is set, and finally uses a local file cache for development.

## Rate limiting
`includes/RateLimiter.php` is used by `api/workout.php` to limit noisy API traffic. The file backend works without extra services and can be replaced by Redis-backed storage later.

## Database indexes
Apply `sql/2026_06_23_performance_indexes.sql` after the base schema to improve frequent dashboard, workout, chat, notification, and program lookups.

## Realtime chat / WebSockets
The current chat remains HTTP polling. For production WebSockets, add a long-running worker such as Ratchet/Swoole behind the web server and broadcast new `chat_messages` rows to clients subscribed by `chat_id`.

## YouTube lessons
The `exercises.video_url` field already exists. Store YouTube URLs there and render them through a sanitized embed URL in workout/detail views.

## MVC migration
The codebase already has `controllers/`, `views/`, and config layers. A future Laravel/Symfony migration should start by moving controllers and DB access behind framework routes while keeping existing views as templates during the transition.
