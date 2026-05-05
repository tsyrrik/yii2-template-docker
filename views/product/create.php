<?php

declare(strict_types=1);

/** @var app\models\Product $model */
/** @var yii\web\View $this */

$this->title = 'Create Product';
?>
<h1>Create Product</h1>
<?= $this->render('_form', ['model' => $model]) ?>
