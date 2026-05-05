<?php

declare(strict_types=1);

/** @var app\models\Product $model */
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = $model->name;
?>
<h1><?= Html::encode($this->title) ?></h1>
<p>
    <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    <?= Html::a('Delete', ['delete', 'id' => $model->id], [
        'class' => 'btn btn-danger',
        'data-method' => 'post',
        'data-confirm' => 'Are you sure?',
    ]) ?>
    <?= Html::a('Back', ['index'], ['class' => 'btn btn-secondary']) ?>
</p>

<?= DetailView::widget([
    'model' => $model,
    'attributes' => ['id', 'name', 'price', 'created_at', 'updated_at'],
]) ?>
