# Yii2 Basic Docker Template

Template for Yii2 Basic web applications running in Docker.

The project uses the native Yii2 Basic layout: application code lives at the repository root, and Docker configuration lives in `docker/`.

## Stack

- PHP-FPM 8.3
- Yii2 Basic
- Nginx 1.27
- MySQL 8.0
- Redis 7 for cache and sessions
- RabbitMQ 3 management for `yii2-queue`
- Codeception and PHPUnit
- PHPStan, PHP-CS-Fixer, Rector, PHPat, composer-dependency-analyser
- Optional production override
- Optional MySQL backup service with S3-compatible upload
- Optional Prometheus, Grafana, Loki monitoring stack

## Project Structure

```text
config/                         Yii2 web, console, db and params config
commands/                       Console controllers
controllers/                    Web controllers
jobs/                           Queue jobs
migrations/                     Yii2 migrations
models/                         ActiveRecord models
runtime/                        Runtime logs and cache
tests/                          Codeception, PHPUnit and architecture tests
views/                          Yii2 views and layout
web/                            Public document root
docker/                         Docker configs and service files
docker/mysql-backup/            MySQL backup image and scripts
docker/prometheus/              Prometheus config
docker/grafana/                 Grafana provisioning and dashboards
docker/loki/                    Loki config
tools/                          Isolated Composer tools
```

Main files:

- `yii` - console entrypoint
- `web/index.php` - web entrypoint
- `docker-compose.yml` - development stack
- `docker-compose.prod.yml` - production override
- `docker-compose.monitoring.yml` - monitoring override
- `Makefile` - command shortcuts
- `.env` - committed defaults
- `.env.local` - local secrets, ignored by git

## Quick Start

Install dependencies:

```bash
composer install
composer bin all install
```

Build and start the development stack:

```bash
make up
```

Open the app:

```text
http://127.0.0.1:8080/
```

Useful checks:

```bash
docker compose ps
curl http://127.0.0.1:8080/fpm-ping
docker compose exec php php yii help
```

Expected FPM ping response:

```text
pong
```

## Environment

Committed defaults live in `.env`. Put local secrets into `.env.local`.

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

Generate local secret values:

```bash
make gen-secrets
```

Then place generated values in `.env.local`.

Values with spaces must be quoted for `vlucas/phpdotenv`, for example:

```dotenv
MYSQL_BACKUP_CRON="17 3 * * *"
```

## Docker Services

Development services are defined in `docker-compose.yml`.

- `php` - PHP 8.3 FPM, built from `docker/php/Dockerfile`
- `nginx` - HTTP entrypoint, proxies PHP requests to `php:9000`
- `db` - MySQL 8.0 with config from `docker/mysql/my.cnf`
- `redis` - Yii2 cache and sessions
- `rabbitmq` - queue broker and management UI

Security defaults:

- `security_opt: no-new-privileges:true` is used for application services.
- `cap_drop: ALL` is used where the image entrypoint supports it.
- Published service ports are bound to `127.0.0.1` by default.

## Yii2 Commands

Run Yii commands inside the PHP container:

```bash
make yii CMD="help"
make yii CMD="cache/index"
```

Migrations:

```bash
make migrate
make migrate-up N=1
make migrate-down N=1
docker compose exec php php yii migrate/create create_example_table
docker compose exec php php yii migrate/history --interactive=0
```

Redis cache:

```bash
make cache-flush
make redis-cli
```

RabbitMQ queue:

```bash
make worker
make queue-run
docker compose exec php php yii queue-demo/product-created 1
```

For multiple queues, define multiple Yii queue components and run them as separate console commands.

## Demo App

The template includes a small Product example:

- `models/Product.php`
- `controllers/ProductController.php`
- `migrations/m000000_000000_create_product_table.php`
- `jobs/ProductCreatedJob.php`
- `commands/QueueDemoController.php`
- `views/product/*`

Routes:

- `/` - home page
- `/product` - Product list
- `/product/create` - create Product
- `/product/view?id=1` - view Product
- `/product/update?id=1` - update Product

## Makefile

Main targets:

```bash
make up
make down
make restart
make ps
make logs
make php
make yii CMD="..."
make composer-install
make migrate
make migrate-up N=1
make migrate-down N=1
make worker
make queue-run
make cache-flush
make redis-cli
make cs-fix
make cs-check
make phpstan
make rector
make rector-dry
make phpat
make dep-analyse
make test
make test-unit
make test-functional
make gen-secrets
make verify-prod-env
make up-prod
make build-prod
make up-monitoring
make down-monitoring
make backup-prod-now
make kics
```

## Code Quality And Tests

Tools are installed through `bamarni/composer-bin-plugin` into `tools/`.

Install all isolated tools:

```bash
composer bin all install
```

Run checks:

```bash
make cs-check
make phpstan
make phpat
make dep-analyse
make test-unit
```

Run formatters/refactoring:

```bash
make cs-fix
make rector-dry
make rector
```

Codeception:

```bash
docker compose exec php vendor/bin/codecept build
docker compose exec php vendor/bin/codecept run unit
```

## Production

Production services are defined through `docker-compose.prod.yml`.

The PHP image uses the `prod` Dockerfile target:

- no Xdebug
- no bind-mounted source code
- dependencies installed with `composer install --no-dev`
- optimized Composer autoload
- immutable runtime copied from the build stage

Before production usage:

```bash
make verify-prod-env
make build-prod
make up-prod
make migrate
```

Override all placeholder secrets in `.env.local` before running production services.

## MySQL Backups

The production override includes `mysql-backup`.

Backup files are created with `mysqldump`, compressed with `gzip`, and can be uploaded to S3-compatible storage.

Manual backup:

```bash
make backup-prod-now
```

Important variables:

```dotenv
MYSQL_BACKUP_CRON="17 3 * * *"
MYSQL_BACKUP_RETENTION_DAYS=7
MYSQL_BACKUP_S3_ENABLED=0
MYSQL_BACKUP_S3_BUCKET=
MYSQL_BACKUP_S3_PREFIX=mysql
MYSQL_BACKUP_S3_ENDPOINT=
MYSQL_BACKUP_S3_REGION=us-east-1
MYSQL_BACKUP_S3_ACCESS_KEY=
MYSQL_BACKUP_S3_SECRET_KEY=
```

## Monitoring

Start the optional monitoring stack:

```bash
make up-monitoring
```

Stop it:

```bash
make down-monitoring
```

Services:

- Prometheus
- Grafana
- Loki
- MySQL exporter
- Redis exporter
- RabbitMQ exporter
- PHP-FPM exporter

Grafana is exposed on `127.0.0.1:${APP_GRAFANA_PORT:-3000}`.

## Dockerfile

The PHP image uses four stages:

- `php-base` - system dependencies, Composer, PHP extensions, PHP-FPM config
- `dev` - Xdebug and host UID/GID mapping
- `build` - production dependency install, optimized autoload, `php yii help` smoke check
- `prod` - runtime image copied from `build`

Installed PHP extensions:

```text
pdo_mysql
intl
mbstring
opcache
gd
zip
bcmath
sockets
amqp
redis
xdebug in dev only
```

The RabbitMQ queue driver uses `enqueue/amqp-lib`; `ext-amqp` is also installed in the PHP image for projects that need native AMQP support.

## Verification

Current checks used for this repository:

```bash
composer validate --strict
docker compose config --quiet
docker compose -f docker-compose.yml -f docker-compose.prod.yml config --quiet
docker compose -f docker-compose.yml -f docker-compose.monitoring.yml config --quiet
docker compose exec -T php php yii migrate --interactive=0
docker compose exec -T php vendor/bin/codecept run unit
docker compose exec -T php php tools/phpstan/vendor/bin/phpstan analyse -c phpstan.neon.dist --no-progress
docker compose exec -T php php tools/php-cs-fixer/vendor/bin/php-cs-fixer fix --dry-run --diff --using-cache=no --sequential
docker compose exec -T php php tools/phpat/vendor/bin/phpstan analyse -c phpat.neon.dist --no-progress
docker compose exec -T php php tools/composer-dependency-analyser/vendor/bin/composer-dependency-analyser --composer-json composer.json --config composer-dependency-analyser.php
docker compose exec -T php php yii queue-demo/product-created 1
curl http://127.0.0.1:8080/
curl http://127.0.0.1:8080/product
docker compose -f docker-compose.yml -f docker-compose.prod.yml build php
```
