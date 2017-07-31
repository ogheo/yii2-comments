<?php

use yii\db\Schema;

/**
 * Class m161126_113528_comments
 */
class m161126_113528_comments extends \yii\db\Migration
{
    public $tableOptions;

    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%comments}}', [

            'id' => Schema::TYPE_PK,
            'url' => $this->string(255),
            'model' => $this->string(64),
            'model_key' => $this->string(64),
            'main_parent_id' => $this->integer(),
            'parent_id' => $this->integer(),
            'email' => $this->string(128),
            'username' => $this->string(128),
            'content' => $this->text(),
            'language' => $this->string(10),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'ip' => $this->string(46),
            'status' => $this->smallInteger(1)->notNull()->defaultValue(1)->comment('
                0-pending,
                1-published,
                2-spam
            '),

        ], $this->tableOptions);

        $this->createIndex('comments_url1_idx', '{{%comments}}', 'url');
        $this->createIndex('comments_model2_idx', '{{%comments}}', 'model');
        $this->createIndex('comments_model_key3_idx', '{{%comments}}', 'model_key');
    }

    public function down()
    {
        $this->dropTable('{{%comments}}');
    }
}
