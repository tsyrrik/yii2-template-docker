<?php

declare(strict_types=1);

namespace app\jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class ProductCreatedJob extends BaseObject implements JobInterface
{
    public int $productId;
    public string $productName;
    public string $price;

    public function execute($queue): void
    {
        Yii::info(
            sprintf('Product created: #%d "%s" at %s', $this->productId, $this->productName, $this->price),
            'queue',
        );
    }
}
