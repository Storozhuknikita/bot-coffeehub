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


// Отправляем уведомление
//mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
