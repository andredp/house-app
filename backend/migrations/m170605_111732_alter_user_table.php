<?php

use yii\db\Migration;

class m170605_111732_alter_user_table extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%user}}', 'password_hash', $this->string()->notNull());
    }

    public function down()
    {
        $this->alterColumn('{{%user}}', 'password_hash', $this->string());
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
