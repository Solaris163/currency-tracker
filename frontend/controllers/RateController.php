<?php


namespace frontend\controllers;

use common\models\CurrencyRates;
use Yii;
use yii\web\Controller;

class RateController extends Controller
{
    const LIMIT_FALL_RATE = 2; //предельное понижение курса, при превышении которого посылается сообщение администратору
    const XML_FILE_URL = 'http://www.cbr.ru/scripts/XML_daily.asp'; //адрес файла с текущими курсами валют
    const ADMIN_EMAIL = 'admin@test.ru';
    const ATTEMPTS_DELAYS = '0 60 1800 3600 21600'; //массив задержек между попытками получения файла xml


    /**
     * {@inheritdoc}
     */
    public function actionRun()
    {
        $xml = $this->getXml(); //получим объект из файла xml

        //переберем все валюты и внесем в базу курсы тех, которые нужно отслеживать
        foreach ($xml->Valute as $currency)
        {
            //проверяем находится ли валюта в списке для отслеживания, который находится в модели CurrencyRates
            if (array_key_exists((string) $currency->CharCode, CurrencyRates::$currencyList))
            {
                $model = new CurrencyRates(); //создаем модель курса валют для внесения в базу
                $model->date = ((array) $xml)["@attributes"]["Date"]; //вставляем в модель дату обновления курса
                //Вставляем в модель id валюты, соответствующий названию валюты из списка валют $currencyList
                $model->currency_id = CurrencyRates::$currencyList["{$currency->CharCode}"];
                $model->currency_rate = $this->getFloat((string) $currency->Value); //вставляем в модель курс валюты

                $this->checkAndSaveRate($model);
            }
        }
    }

    /**
     * Метод получает массив с временными задержками из константы ATTEMPTS_DELAYS
     * Перебирает этот массив, пока не получит объект из файла xml, или пока не кончится массив
     * //Метод возвращает объект xml или false//Затем вызывает метод getAndSaveRates
     */
    public function getXml()
    {
        $xml = false;
        $arrDelays = explode(' ', self::ATTEMPTS_DELAYS); //получим массив задержек из константы ATTEMPTS_DELAYS
        foreach ($arrDelays as $delay) //делаем попытки получения файла в соответствии с массивом задержек
        {
            sleep((int)$delay);

            try {
                $xml = simplexml_load_file(self::XML_FILE_URL); //делаем попытку получения объекта xml
            } catch (\Exception $e) {
                Yii::warning("Xml file is not available");
            }

		    if ($xml != false) return $xml; //Если бъект xml получен, возвращаем его
	    }
        //после перебора массива, отправляем сообщение администратору, так как объект xml не получен
        $this->contact('Xml file is not available');
        return false;
    }

    /**
     * метод принимает строку с запятой и возвращает число с плавающей точкой.
     */
    public function getFloat($str)
    {
        return (float) str_ireplace(',', '.', $str);

    }

    /**
     * метод проверяет, нет ли в базе записи для данной валюты на эту же дату, если нет, то
     * прверяет, не упал ли курс валюты больше чем на величину константы LIMIT_FALL_RATE и сохраняет курс в базе
     * @var $model CurrencyRates
     */
    public function checkAndSaveRate($model)
    {
        //проверяем нет ли в базе уже записи для данной валюты на эту же дату
        if (!$model->checkIsDateInBase())
        {
            //если такой даты в базе нет, то проверяем не упал ли курс более чем на величину константы LIMIT_FALL_RATE
            if ($model->getRateChange() > self::LIMIT_FALL_RATE)
            {
                //если упал, то отправляем сообщение администратору
                $this->contact("Rate of currency with id = {$model->currency_id} fell more than LIMIT_FALL_RATE");
            }
            //сохраняем модель в базу данных
            $this->saveRate($model);
        }
    }

    /**
     * метод сохраняет модель в базе данных, а если сохранение не удалось, отправляет сообщение администратору
     * @var $model CurrencyRates
     */
    public function saveRate($model)
    {
        $isSave = false; //введем переменную $isSave для проверки, получилось ли сохранить данные в базу

        try {
            $isSave = $model->save(); //делаем попытку сохранения данных в базу
        } catch (\Exception $e) {
            Yii::warning("data in database is not saved");
        }

        if (!$isSave) //проверяем сохранились ли данные в базе данных
        {
            $this->contact('data in database is not saved');
        }
    }

    /**
     * {@inheritdoc}
     * @var $message string
     */
    public function contact($message)
    {
        \Yii::$app->mailer->compose()
            ->setTo(self::ADMIN_EMAIL)
            ->setSubject('currency rates')
            ->setTextBody($message)
            ->send();
    }
}