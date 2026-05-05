<?php

declare(strict_types=1);

$db     = require __DIR__ . '/db.php';
$params = require __DIR__ . '/params.php';

return [
    'id'                   => 'app-console',
    'basePath'             => dirname(__DIR__),
    'bootstrap'            => ['log', 'queue'],
    'controllerNamespace'  => 'app\commands',
    'aliases'              => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'components' => [
        'redis' => [
            'class'    => \yii\redis\Connection::class,
            'hostname' => $_ENV['REDIS_HOST'] ?? 'redis',
            'port'     => (int) ($_ENV['REDIS_PORT'] ?? 6379),
            'database' => (int) ($_ENV['REDIS_DATABASE'] ?? 0),
        ],
        'cache' => [
            'class' => \yii\redis\Cache::class,
        ],
        'queue' => [
            'class'     => \yii\queue\amqp_interop\Queue::class,
            'driver'    => \yii\queue\amqp_interop\Queue::ENQUEUE_AMQP_LIB,
            'dsn'       => sprintf(
                'amqp://%s:%s@%s:%s/%s',
                $_ENV['RABBITMQ_USER'] ?? 'app',
                $_ENV['RABBITMQ_PASSWORD'] ?? '',
                $_ENV['RABBITMQ_HOST'] ?? 'rabbitmq',
                $_ENV['RABBITMQ_PORT'] ?? '5672',
                $_ENV['RABBITMQ_VHOST'] ?? '%2F',
            ),
            'queueName' => $_ENV['RABBITMQ_QUEUE'] ?? 'default',
        ],
        'log' => [
            'targets' => [
                [
                    'class'  => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
    ],
    'params' => $params,
];
