<?php

declare(strict_types=1);

namespace tests\unit\models;

use app\models\Product;
use PHPUnit\Framework\TestCase;

class ProductTest extends TestCase
{
    public function testTableNameReturnsProduct(): void
    {
        self::assertSame('product', Product::tableName());
    }

    public function testValidationRequiresName(): void
    {
        $model = new Product();
        $model->price = '9.99';

        self::assertFalse($model->validate(['name']));
        self::assertArrayHasKey('name', $model->errors);
    }

    public function testValidationRequiresPrice(): void
    {
        $model = new Product();
        $model->name = 'Test Product';

        self::assertFalse($model->validate(['price']));
        self::assertArrayHasKey('price', $model->errors);
    }

    public function testValidationPassesWithValidData(): void
    {
        $model = new Product();
        $model->name = 'Test Product';
        $model->price = '9.99';

        self::assertTrue($model->validate(['name', 'price']));
    }

    public function testPriceRejectsNegativeValue(): void
    {
        $model = new Product();
        $model->name = 'Bad Product';
        $model->price = '-1';

        self::assertFalse($model->validate(['price']));
        self::assertArrayHasKey('price', $model->errors);
    }
}
