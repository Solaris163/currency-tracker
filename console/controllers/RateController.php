<?php


namespace console\controllers;


use yii\console\Controller;
use common\models\ExchangeRates;

class RateController extends Controller
{
    private $_limitFallRate = 2; //предельное понижение курса, после превышении которого посылается сообщение администратору
    private $_currencyList = ['EUR', 'USD']; //список валют, которые необходимо отслеживать в контроллере
    private $_attemptsIntervals =[0, 60, 1800, 3600, 21600];

    public function actionIndex()
    {
        $model = new ExchangeRates(); //создаем модель курса валют для внесения в базу
        $xml = $this->getXml(); //получим объект из файла xml

        foreach ($xml->Valute as $valute) //внесем в $model курсы валют для доллара и евро
        {
            $arr = (array) $valute; //преобразуем объект в массив
            if($arr["CharCode"] == "USD")
            {
                $model->usd = $this->getFloat($arr["Value"]);
            }elseif ($valute->CharCode == "EUR")
            {
                $model->eur = $this->getFloat($arr["Value"]);
            }
        }

        $model->date = ((array) $xml)["@attributes"]["Date"];

        $this->getDifference($model);
        $model->save();
    }

    //метод получает объект из файла xml и возвращате его


    public function getXml()
    {
        $try = 1; //номер попытки получения файла
        while ($try <= 5) //делаем 5 попыток
        {
            //получим объект из файла xml (пока для устранения сообщения об ошибке поставил символ @)
            @$xml = simplexml_load_file('http://www.cbr.ru/scripts/XML_daily.asp');
            if (is_object($xml)) //проверка создан ли объект из файла
            {
                break;
            }
            else
            {
                $try++; //если файл недоступен, увеличиваем номер попытки на единицу
                //запускаем switch, останавливающий выполнение скрипта на нужное время
                switch ($try) {
                    case 2:
                        sleep(60);
                        break;
                    case 3:
                        sleep(1800);
                        break;
                    case 4:
                        sleep(3600);
                        break;
                    case 5:
                        sleep(21600);
                        break;
                }
            }
        }
        return $xml;
    }

    //метод принимает строку с запятой и возвращает число с плавающей точкой.
    public function getFloat($str)
    {
        return (float) str_ireplace(',', '.', $str);
    }

    //метод находит разницу между старым и новым курсом валюты, и, если разница больше 2, вызывает метод contact()
    public function getDifference($new_rates)
    {
        //найдем из базы предыдущие курсы валют
        $old_rates = ExchangeRates::find()->select('*')->orderBy('id DESC')->limit(1)->asArray()->one();

        if ((float)$old_rates["usd"] - $new_rates->usd > 2 || (float)$old_rates["eur"] - $new_rates->eur > 2)
        {
            $this->contact('admin@test.ru');
        }

    }

    public function contact($email)
    {
        \Yii::$app->mailer->compose()
            ->setTo($email)
            ->setSubject('rate down')
            ->setTextBody("rate down" )
            ->send();
    }
}