<?php
error_reporting(E_ALL);

/*
 * Сбор информации о выручке
 * Генерация PDF
 * Отправка на почту
 */

// Секретный ключ вашего приложения берется из config.php
$client_secret = '1547ba15d5dc931f291bbf8fdb5e8fa4';

echo'<pre>';
print_r($_GET['code']);
echo'</pre>';


echo'Настройки уведомления';
echo'
<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
        </script>';

// Отправляем уведомление
//mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
