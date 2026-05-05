<?php

declare(strict_types=1);

namespace app\controllers;

use app\jobs\ProductCreatedJob;
use app\models\Product;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class ProductController extends Controller
{
    public function actionIndex(): string
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Product::find()->orderBy(['id' => SORT_DESC]),
            'pagination' => ['pageSize' => 20],
        ]);

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionView(int $id): string
    {
        return $this->render('view', ['model' => $this->findModel($id)]);
    }

    public function actionCreate(): Response|string
    {
        $model = new Product();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->queue->push(new ProductCreatedJob([
                'productId' => (int) $model->id,
                'productName' => $model->name,
                'price' => (string) $model->price,
            ]));

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('create', ['model' => $model]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', ['model' => $model]);
    }

    public function actionDelete(int $id): Response
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    private function findModel(int $id): Product
    {
        $model = Product::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException("Product #{$id} not found.");
        }

        return $model;
    }
}
