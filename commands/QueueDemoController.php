<?php

declare(strict_types=1);

namespace app\commands;

use app\jobs\ProductCreatedJob;
use app\models\Product;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class QueueDemoController extends Controller
{
    public $defaultAction = 'product-created';

    public function actionProductCreated(int $productId): int
    {
        $product = Product::findOne($productId);
        if ($product === null) {
            $this->stderr("Product #{$productId} not found.\n");

            return ExitCode::UNSPECIFIED_ERROR;
        }

        Yii::$app->queue->push(new ProductCreatedJob([
            'productId' => (int) $product->id,
            'productName' => $product->name,
            'price' => (string) $product->price,
        ]));

        $this->stdout("Job dispatched for product #{$productId}.\n");

        return ExitCode::OK;
    }
}
