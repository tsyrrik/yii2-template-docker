<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad();

defined('YII_DEBUG') or define('YII_DEBUG', (bool) ($_ENV['APP_DEBUG'] ?? false));
defined('YII_ENV')   or define('YII_ENV',   $_ENV['APP_ENV'] ?? 'prod');

require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

$config = require dirname(__DIR__) . '/config/web.php';

(new yii\web\Application($config))->run();
