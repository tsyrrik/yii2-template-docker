<?php

declare(strict_types=1);

namespace app\controllers;

use yii\web\Controller;
use yii\web\ErrorAction;

class SiteController extends Controller
{
    public function actions(): array
    {
        return [
            'error' => [
                'class' => ErrorAction::class,
            ],
        ];
    }

    public function actionIndex(): string
    {
        return $this->render('index');
    }
}
