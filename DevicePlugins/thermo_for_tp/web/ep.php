<?php

//http://ab-log.ru/smart-house/ethernet/avr

//точка входа
//определение девайса по IP

//реакция на порт
//if ( $_GET['pt'] == "0" )


//запрос состояния
//192.168.0.14/sec/pt=7&cmd=get

/*
 Нам необходимо только написать простейший PHP-скрипт, отвечающий на запрос устройства.

<?
if ( $_GET['pt'] == "0" )
echo "6:2";
?>

Если сработал вход "0", сообщить устройству в ответ "6:2". Но что это означает эта команда?

Команда устройству состоит из двух полей, разделенных двоеточием.
Первое поле (6) - номер порта от 0 до 12 (у нас 13 входов/выходов)
Второе поле (2) - действие. Возможные варианты (0 - выключить, 1 - включить, 2 - переключить с вкл на выкл или наоборот)


 Изменение состояния портов

Изменение текущего состояния выходов по сути ничем не отличается от считывания.
URL вида:

http://192.168.0.14/sec/?cmd=2:1

Где формат команды (cmd) стандартный. Если выход настроен как PWM (ШИМ), то допускается указывать значения от 0 до 255

http://192.168.0.14/sec/?cmd=3:150

*/

/*
договариваемся deviice.raw_id = IP адрес
http://pbr-serv-rv-cron.asmirnov.fenrir.immo/DevicePlugins/plugin_example/web/ep.php
*/

chdir(dirname(dirname(dirname(dirname(__FILE__)))));

require_once ("class.Facade.php");
$facade = Majordomo_Facade::getInstance("./config/current/global.php");

var_dump($_SERVER['REMOTE_ADDR']);die();
$remoteAddr = $_SERVER['REMOTE_ADDR'];
$device=SQLSelectOne("SELECT * FROM device WHERE raw_id='$remoteAddr'");
