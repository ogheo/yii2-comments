<?php

use yii\db\Schema;

/**
 * Class m170222_120908_comments_rating
 */
class m170222_120908_comments_rating extends \yii\db\Migration
{
    public $tableOptions;

    public function safeUp()
    {
        if ($this->db->driverName === 'mysql') {
            $this->tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%comments_rating}}', [

            'id' => Schema::TYPE_PK,
            'comment_id' => $this->integer(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
            'ip' => $this->string(46),
            'status' =>  $this->smallInteger(1)->notNull()->comment('
                1-like,
                2-dislike,
            '),

        ], $this->tableOptions);

        $this->createIndex('comments_rating_comment_id1_idx', '{{%comments_rating}}', 'comment_id');
        $this->createIndex('comments_rating_status2_idx', '{{%comments_rating}}', 'status');
        $this->addForeignKey(
            'comments_rating_fidkey', '{{%comments_rating}}', 'comment_id', '{{%comments}}', 'id', 'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {
        $this->dropTable('{{%comments_rating}}');
    }
}
