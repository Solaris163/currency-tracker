<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "exchange_rates".
 *
 * @property int $id
 * @property string $date
 * @property string $eur
 * @property string $usd
 */
class ExchangeRates extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'exchange_rates';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date'], 'safe'],
            [['eur', 'usd'], 'number'],
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
            'eur' => 'Eur',
            'usd' => 'Usd',
        ];
    }
}
