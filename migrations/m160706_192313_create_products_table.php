<?php

use yii\db\Migration;

/**
 * Handles the creation for table `products`.
 */
class m160706_192313_create_products_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('products', [
            'id' => $this->primaryKey(),
            'name' => $this->string(100),
            'description' => $this->text(),
            'price' => $this->decimal(10, 2),
            'quantity' => $this->integer(),
            'image' => $this->string(250),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('products');
    }
}
