<?php

declare(strict_types=1);

/** @var app\models\Product $model */
/** @var yii\web\View $this */

use yii\helpers\Html;
use yii\widgets\ActiveForm;

$form = ActiveForm::begin();
echo $form->field($model, 'name')->textInput(['maxlength' => true]);
echo $form->field($model, 'price')->textInput(['type' => 'number', 'step' => '0.01', 'min' => '0']);
echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => 'btn btn-success']);
ActiveForm::end();
