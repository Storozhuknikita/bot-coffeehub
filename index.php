<?php
error_reporting(E_ALL);

define('POSTER_CLIENT_ID', '223');
define('POSTER_CLIENT_SECRET', '1547ba15d5dc931f291bbf8fdb5e8fa4');

include_once 'class/Poster.class.php';
/*
 * Сбор информации о выручке
 * Генерация PDF
 * Отправка на почту
 */

// Получаем данные от Poster
$code = $_REQUEST['code'];

// Отправляем запрос в Poster
$auth = Poster::auth($_REQUEST['code']);

$account_name = $auth['account_name'];
$access_token = $auth['access_token'];

// Получить всех сотрудников
$url = 'https://'.$account_name.'.joinposter.com/api/access.getEmployees?token='.$access_token.'';
$data = json_decode(Poster::sendRequest($url));

print_r($data);

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
