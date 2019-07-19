<?php

namespace common\models;

use common\VarDump;
use Yii;

/**
 * This is the model class for table "currency_rates".
 *
 * @property int $id
 * @property string $date
 * @property int $currency_id
 * @property string $currency_rate
 */
class CurrencyRates extends \yii\db\ActiveRecord
{
    public static $currencyList = ['EUR' => 1, 'USD' => 2]; //список валют, которые отслеживает приложение

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'currency_rates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['currency_id'], 'integer'],
            [['currency_rate'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'currency_id' => 'Currency ID',
            'currency_rate' => 'Currency Rate',
        ];
    }

    /**
     * Метод проверяет, есть ли в базе данных запись для данной валюты с данной датой
     */
    public function checkIsDateInBase()
    {
        $result = self::find()->where(['currency_id' => $this->currency_id])
            ->andWhere(['date' => $this->date])->exists();
        return $result;
    }

    /**
     * Метод возвращает разницу между предыдущим курсом валюты и текущим
     */
    public function getRateChange()
    {
        //получим из базы предыдущий курс валюты
        $oldRate = self::find()->select('currency_rate')->where(['currency_id' => $this->currency_id])
            ->orderBy(['id' => SORT_DESC])->limit(1)->one();
        //найдем разницу между текущим и предыдущим курсом
        $rateChange = $oldRate->currency_rate - $this->currency_rate;
        return $rateChange;
    }
}
