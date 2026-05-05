# Yii2 Basic Docker Template

This repository is a template for Yii2 Basic web applications running in Docker.

The application uses the native Yii2 Basic layout: Yii2 code lives at the repository root, and Docker configuration lives in `docker/`.

Current stack:

- PHP-FPM 8.3 with Yii2 Basic
- Nginx 1.27 as HTTP entrypoint
- MySQL 8.0
- Redis 7 for cache and sessions
- RabbitMQ 3 management for `yii2-queue`
- Composer dependencies managed at the repository root
- Environment variables loaded from `.env` and optional `.env.local`

Planned by the design spec:

- Production override with immutable PHP runtime image
- MySQL backup service with S3-compatible upload
- Prometheus, Grafana, Loki monitoring stack
- PHPStan, PHP-CS-Fixer, Rector, PHPat, composer-dependency-analyser
- Codeception + PHPUnit tests
- Demo Product CRUD and queue job

---

## Project Structure

Implemented files:

- `config/` - Yii2 application configuration
  - `web.php` - web application config
  - `console.php` - console application config
  - `db.php` - MySQL connection config, reads from env
  - `params.php` - application params
- `web/index.php` - web entrypoint, loads `.env` and `.env.local`
- `yii` - console entrypoint, loads `.env` and `.env.local`
- `migrations/` - Yii2 migrations
- `runtime/` - logs and cache, ignored by git except placeholders
- `web/assets/` - published Yii2 assets, ignored by git except placeholder
- `docker/`
  - `php/` - PHP-FPM Dockerfile and config files
  - `nginx/` - Nginx config and vhost
  - `mysql/` - MySQL config and init SQL
  - `generate-secrets.sh` - helper for local secret generation
  - `verify-prod-env.sh` - production secret guard
- `docker-compose.yml` - development stack
- `.env` - committed defaults and placeholders
- `.env.local` - local secrets, ignored by git
- `composer.json` / `composer.lock` - Yii2 dependencies

Planned files from `docs/superpowers/specs/2026-05-05-yii2-template-docker-design.md`:

- `controllers/`, `models/`, `views/`
- `jobs/`, `commands/`
- `tests/`
- `Makefile`
- `docker-compose.prod.yml`
- `docker-compose.monitoring.yml`
- `docker/mysql-backup/`
- `docker/prometheus/`, `docker/grafana/`, `docker/loki/`
- code quality config files

---

## Docker Stack

Core services are defined in `docker-compose.yml`.

- `php` - PHP 8.3 FPM
  - Built from `docker/php/Dockerfile`
  - Dev target includes Xdebug
  - Uses `HOST_UID` / `HOST_GID` build args to map `www-data` to the host user
  - Working directory: `/var/www/app`
  - Mounts the repository root as `/var/www/app`
- `nginx` - Nginx 1.27 Alpine
  - Configured via `docker/nginx/nginx.conf` and `docker/nginx/conf.d/app.conf`
  - Proxies PHP requests to `php:9000`
  - Exposes `127.0.0.1:${APP_HTTP_PORT:-8080}`
  - `/fpm-ping` is used for health checks
- `db` - MySQL 8.0
  - Data volume: `db-data`
  - Config: `docker/mysql/my.cnf`
  - Init SQL: `docker/mysql/init.sql`
  - Exposes `127.0.0.1:${APP_DB_PORT:-3306}`
- `redis` - Redis 7 Alpine
  - Used by Yii2 cache and sessions
  - `maxmemory` is controlled by `APP_REDIS_MEMORY_LIMIT`
  - Exposes `127.0.0.1:${APP_REDIS_PORT:-6379}`
- `rabbitmq` - RabbitMQ 3 management Alpine
  - Used by `yiisoft/yii2-queue`
  - AMQP port: `127.0.0.1:${APP_RABBITMQ_PORT:-5672}`
  - Management UI: `127.0.0.1:${APP_RABBITMQ_MGMT_PORT:-15672}`

Security defaults:

- `security_opt: no-new-privileges:true` is used for application services.
- `cap_drop: ALL` is used for `php` and `nginx`; `nginx` gets only the capabilities it needs back.
- MySQL and RabbitMQ do not use `cap_drop: ALL`, because their entrypoints can break under that restriction.

---

## Quick Start

Install dependencies if `vendor/` is missing:

```bash
composer install
```

Build and start the development stack:

```bash
docker compose up -d --build
```

Check containers:

```bash
docker compose ps
```

Run Yii console inside PHP:

```bash
docker compose exec php php yii help
```

Check PHP-FPM through Nginx:

```bash
curl http://127.0.0.1:8080/fpm-ping
```

Expected response:

```text
pong
```

At the moment, `/` may return `500` until `controllers/SiteController.php` and the Yii2 views are added. The infrastructure itself is already able to serve PHP through Nginx.

---

## Environment

Committed defaults live in `.env`. Local secrets should go into `.env.local`.

Important variables:

```dotenv
APP_NAME=platform
APP_ENV=dev
APP_DEBUG=1
APP_SECRET=change-me-in-.env.local

DB_HOST=db
DB_PORT=3306
DB_NAME=app
DB_USER=app
DB_PASSWORD=change-me-in-.env.local

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_DATABASE=0

RABBITMQ_HOST=rabbitmq
RABBITMQ_PORT=5672
RABBITMQ_USER=app
RABBITMQ_PASSWORD=change-me-in-.env.local
RABBITMQ_VHOST=%2F
RABBITMQ_QUEUE=default

APP_HTTP_PORT=8080
APP_DB_PORT=3306
APP_REDIS_PORT=6379
APP_RABBITMQ_PORT=5672
APP_RABBITMQ_MGMT_PORT=15672
```

Values with spaces must be quoted for `vlucas/phpdotenv`, for example:

```dotenv
MYSQL_BACKUP_CRON="17 3 * * *"
```

Generate local secrets:

```bash
sh docker/generate-secrets.sh
```

Then place the generated values in `.env.local`.

---

## Yii2 Application

### Entrypoints

- Web: `web/index.php`
- Console: `yii`

Both entrypoints load Composer autoload first, then `.env` and `.env.local`, then Yii.

### Database

DB config is in `config/db.php`:

```php
return [
    'class' => 'yii\db\Connection',
    'dsn' => sprintf(
        'mysql:host=%s;port=%s;dbname=%s',
        $_ENV['DB_HOST'] ?? 'db',
        $_ENV['DB_PORT'] ?? '3306',
        $_ENV['DB_NAME'] ?? 'app',
    ),
    'username' => $_ENV['DB_USER'] ?? 'app',
    'password' => $_ENV['DB_PASSWORD'] ?? '',
    'charset' => 'utf8mb4',
];
```

Run migrations:

```bash
docker compose exec php php yii migrate --interactive=0
```

Show migration history:

```bash
docker compose exec php php yii migrate/history --interactive=0
```

Create a migration:

```bash
docker compose exec php php yii migrate/create create_product_table
```

Rollback one migration:

```bash
docker compose exec php php yii migrate/down 1 --interactive=0
```

### Redis

Redis is configured as a shared Yii2 component in `config/web.php` and `config/console.php`.

Cache:

```php
'cache' => [
    'class' => \yii\redis\Cache::class,
],
```

Session storage:

```php
'session' => [
    'class' => \yii\redis\Session::class,
],
```

Check registered caches:

```bash
docker compose exec php php yii cache/index
```

Flush Redis DB:

```bash
docker compose exec redis redis-cli FLUSHDB
```

### RabbitMQ Queue

Queue package:

```text
yiisoft/yii2-queue
enqueue/amqp-lib
```

Configured component:

```php
'queue' => [
    'class' => \yii\queue\amqp_interop\Queue::class,
    'driver' => \yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_LIB,
    'dsn' => sprintf(
        'amqp://%s:%s@%s:%s/%s',
        $_ENV['RABBITMQ_USER'] ?? 'app',
        $_ENV['RABBITMQ_PASSWORD'] ?? '',
        $_ENV['RABBITMQ_HOST'] ?? 'rabbitmq',
        $_ENV['RABBITMQ_PORT'] ?? '5672',
        $_ENV['RABBITMQ_VHOST'] ?? '%2F',
    ),
    'queueName' => $_ENV['RABBITMQ_QUEUE'] ?? 'default',
],
```

The queue is bootstrapped in `config/console.php`, so console commands are available:

```bash
docker compose exec php php yii help queue/listen
docker compose exec php php yii queue/listen --verbose=1
```

For a template with multiple queues, add multiple queue components and run them as separate console commands.

---

## Dockerfile

The PHP image uses a multi-stage Dockerfile:

- `php-base` - system dependencies, Composer, PHP extensions, PHP-FPM config
- `dev` - Xdebug and host UID/GID mapping
- `build` - production dependency install, optimized autoload, `php yii help` smoke check
- `prod` - runtime image copied from `build`

Installed PHP extensions in the current Dockerfile:

```text
pdo_mysql
intl
mbstring
opcache
gd
zip
bcmath
sockets
redis
xdebug in dev only
```

The design spec mentions `amqp`, but the current queue driver uses `enqueue/amqp-lib`, which can work through PHP libraries without `ext-amqp`.

---

## Useful Commands

Current direct commands:

```bash
docker compose up -d --build
docker compose down
docker compose ps
docker compose exec php bash
docker compose exec php php yii help
docker compose exec php php yii migrate --interactive=0
docker compose exec php php yii migrate/up 1 --interactive=0
docker compose exec php php yii migrate/down 1 --interactive=0
docker compose exec php php yii queue/listen --verbose=1
docker compose exec php php yii cache/index
docker compose exec redis redis-cli FLUSHDB
composer validate --strict
```

Planned Makefile targets:

```bash
make up
make up-prod
make up-monitoring
make php
make yii CMD="..."
make migrate
make migrate-up N=1
make migrate-down N=1
make worker
make queue-run
make cache-flush
make redis-cli
make message
make composer-install
make cs-fix
make phpstan
make rector
make gen-secrets
make kics
make backup-prod-now
```

---

## Tooling

Root dependencies currently include:

- `yiisoft/yii2`
- `yiisoft/yii2-redis`
- `yiisoft/yii2-queue`
- `enqueue/amqp-lib`
- `yiisoft/yii2-symfonymailer`
- `vlucas/phpdotenv`
- `bamarni/composer-bin-plugin`
- `codeception/codeception`
- `codeception/module-yii2`
- `codeception/module-asserts`

Planned isolated tools under `tools/`:

- PHPStan
- PHP-CS-Fixer
- Rector
- PHPat
- composer-dependency-analyser

---

## Production And Monitoring

The design spec includes:

- `docker-compose.prod.yml`
- `docker/mysql-backup/`
- `docker-compose.monitoring.yml`
- `docker/prometheus/`
- `docker/grafana/`
- `docker/loki/`

These files are not implemented yet in the current repository state.

Before production usage:

- Override all placeholder secrets in `.env.local`.
- Keep DB/RabbitMQ/Redis ports bound to `127.0.0.1` or remove host port publishing.
- Run `docker/verify-prod-env.sh prod`.
- Build the `prod` Dockerfile target.
- Run migrations explicitly.

---

## Current Verification

The following checks pass in the current repository state:

```bash
composer validate --strict
php yii help
docker compose config --quiet
docker compose build php
docker compose up -d
docker compose exec -T php php yii help
docker compose exec -T php php yii cache/index
docker compose exec -T php php yii help queue/listen
curl http://127.0.0.1:8080/fpm-ping
```

Known current gap:

- `GET /` returns `500` until `SiteController` and web views are added.
