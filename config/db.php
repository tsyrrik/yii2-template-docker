<?php

declare(strict_types=1);

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
