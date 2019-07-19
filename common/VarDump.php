<?php


namespace common;

//сделал класс для вывода объектов в читабельном виде
class VarDump
{
    public static function varDum($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
        exit;
    }

    public static function printR($var)
    {
        echo '<pre>';
        print_r($var);
        echo '</pre>';
        exit;
    }

    public static function varDumSerial($var)
    {
        echo '<pre>';
        var_dump($var);
        echo '</pre>';
    }
}