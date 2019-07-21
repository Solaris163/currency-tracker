<?php

use yii\db\Migration;

/**
 * Class m190715_161920_create_table_currency_rates
 */
class m190715_161920_create_table_currency_rates extends Migration
{
    /**
     * {@inheritdoc}
     * Создадим таблицу, где
     * date - дата объявления курса валюты
     * currency_id - id валюты
     * rate - курс валюты
     */
    public function safeUp()
    {
        $this->createTable('currency_rates', [
            'id' => $this->primaryKey(),
            'date' => $this->string()->notNull(),
            'currency_id' => $this->integer()->notNull(),
            'currency_rate' => $this->DECIMAL (6,4)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('currency_rates');
    }
}
