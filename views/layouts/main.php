<?php

declare(strict_types=1);

/** @var string $content */
/** @var yii\web\View $this */

use yii\helpers\Html;

$appName = Yii::$app->name;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= Html::encode($this->title ?: $appName) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <?= Html::a(Html::encode($appName), ['/site/index'], ['class' => 'navbar-brand']) ?>
        <div class="navbar-nav">
            <?= Html::a('Home', ['/site/index'], ['class' => 'nav-link']) ?>
            <?= Html::a('Products', ['/product/index'], ['class' => 'nav-link']) ?>
        </div>
    </div>
</nav>
<main class="container">
    <?= $content ?>
</main>
</body>
</html>
