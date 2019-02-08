<?php
error_reporting(E_ALL);

include_once 'class/Poster.class.php';
/*
 * Сбор информации о выручке
 * Генерация PDF
 * Отправка на почту
 */

// Секретный ключ вашего приложения берется из config.php
$client_secret = '1547ba15d5dc931f291bbf8fdb5e8fa4';

// Получаем данные от Poster
$code = $_REQUEST['code'];

// Отправляем запрос в Poster
$auth = Poster::auth($_REQUEST['code']);


echo'<pre>';
print_r($auth);
echo'</pre>';


echo'Настройки уведомления';
?>

<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
        </script>

// Отправляем уведомление
//mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
