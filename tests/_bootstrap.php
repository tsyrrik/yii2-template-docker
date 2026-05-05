<?php

declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dotenv = Dotenv::createImmutable(dirname(__DIR__), ['.env', '.env.local']);
$dotenv->safeLoad();

defined('YII_DEBUG') or define('YII_DEBUG', true);
defined('YII_ENV') or define('YII_ENV', 'test');

require dirname(__DIR__) . '/vendor/yiisoft/yii2/Yii.php';

new yii\web\Application(require dirname(__DIR__) . '/config/web.php');
