<?php

declare(strict_types=1);

/** @var yii\data\ActiveDataProvider $dataProvider */
/** @var yii\web\View $this */

use app\models\Product;
use yii\helpers\Html;
use yii\widgets\ListView;

$this->title = 'Products';
?>
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="mb-0">Products</h1>
    <?= Html::a('Create', ['create'], ['class' => 'btn btn-primary']) ?>
</div>

<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => static fn(Product $model): string => Html::tag(
        'div',
        Html::a(Html::encode($model->name), ['view', 'id' => $model->id])
        . ' - ' . Html::encode(number_format((float) $model->price, 2))
        . ' ' . Html::a('Edit', ['update', 'id' => $model->id])
        . ' ' . Html::a('Delete', ['delete', 'id' => $model->id], [
            'data-method' => 'post',
            'data-confirm' => 'Are you sure?',
        ]),
        ['class' => 'mb-2'],
    ),
    'emptyText' => 'No products yet.',
]) ?>
