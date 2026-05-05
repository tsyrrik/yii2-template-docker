<?php

declare(strict_types=1);

/** @var string $message */
/** @var string $name */
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = $name;
?>
<h1><?= Html::encode($name) ?></h1>
<p><?= Html::encode($message) ?></p>
