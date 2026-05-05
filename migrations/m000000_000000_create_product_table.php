<?php

declare(strict_types=1);

use yii\db\Migration;

class m000000_000000_create_product_table extends Migration
{
    public function safeUp(): void
    {
        $this->createTable('product', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255)->notNull(),
            'price' => $this->decimal(10, 2)->notNull(),
            'created_at' => $this->timestamp()->null()->defaultValue(null),
            'updated_at' => $this->timestamp()->null()->defaultValue(null),
        ]);
    }

    public function safeDown(): void
    {
        $this->dropTable('product');
    }
}
