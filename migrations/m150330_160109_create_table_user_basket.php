<?php

use yii\db\Schema;
use yii\db\Migration;

class m150330_160109_create_table_user_basket extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%user_basket}}', [
            'id' => Schema::TYPE_PK,
            'group' => Schema::TYPE_STRING . '(50)',
            'id_user' => Schema::TYPE_INTEGER . ' NOT NULL',
            'id_product' => Schema::TYPE_STRING . ' NOT NULL',
            'hash_product' => Schema::TYPE_STRING . '(32) NOT NULL',
            'price' => Schema::TYPE_DECIMAL . '(10,2) NOT NULL DEFAULT 0',
            'count' => Schema::TYPE_INTEGER . ' NOT NULL DEFAULT 0',
            'params' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
        ]);

//        $this->createIndex('idx_meta_tag_url', '{{%meta_tag}}', 'url');
    }

    public function safeDown()
    {
        $this->dropTable('{{%user_basket}}');
    }
}
