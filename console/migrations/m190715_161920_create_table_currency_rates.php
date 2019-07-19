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
            'date' => $this->string(),
            'currency_id' => $this->integer(),
            'currency_rate' => $this->DECIMAL (6,4),
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
