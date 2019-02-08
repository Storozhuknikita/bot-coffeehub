<?php
error_reporting(E_ALL);

/*
 * Сбор информации о выручке
 * Генерация PDf
 * Отправка на почту
 */

// Секретный ключ вашего приложения берется из config.php
$client_secret = '1547ba15d5dc931f291bbf8fdb5e8fa4';


print_r($_SERVER['code']);

// Отправляем уведомление
//mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
