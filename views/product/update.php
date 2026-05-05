<?php

declare(strict_types=1);

/** @var app\models\Product $model */
/** @var yii\web\View $this */

use yii\helpers\Html;

$this->title = 'Update Product: ' . $model->name;
?>
<h1><?= Html::encode($this->title) ?></h1>
<?= $this->render('_form', ['model' => $model]) ?>
