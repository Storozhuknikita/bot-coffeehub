<?php
error_reporting(E_ALL);
/*
 * Этот файл отвечает за обработку Веб-Хуков от Joinposter
 *
 */

// Рума #kaluga
define('SLACK_WEBHOOK', 'https://hooks.slack.com/services/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');

// Секретный ключ вашего приложения берется из config.php
$client_secret = '1547ba15d5dc931f291bbf8fdb5e8fa4';

// Приводим к нужному формату входящие данные
$postJSON = file_get_contents('php://input');
$postData = json_decode($postJSON, true);

// Верификация. Код от Joinposter.
$verify_original = $postData['verify'];
unset($postData['verify']);
// Верификация
$verify = array(
    $postData['account'],
    $postData['object'],
    $postData['object_id'],
    $postData['action'],
);

// Если есть дополнительные параметры
if (isset($postData['data'])) {
    $verify[] = $postData['data'];
}
$verify[] = $postData['time'];
$verify[] = $client_secret;

// Создаём строку для верификации запроса клиентом
$verify = md5(implode(';', $verify));

// Проверяем валидность данных
if ($verify != $verify_original) {
    exit;
}
// Если не ответить на запрос, Poster продолжит слать Webhook
echo json_encode(array('status' => 'accept'));

/*
 * Дальше идет для отработки веб-хуков по сотрудникам. Сущность waiter.
 * Для приложения "Сообщения персоналу".
 */
/*
$text = 'Сотрудник отредактирован. Уведомление отправлено в чат Калуги.';

$message = array('payload' => json_encode(array('text' => $text)));
// Use curl to send your message
$c = curl_init(SLACK_WEBHOOK);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($c, CURLOPT_POST, true);
curl_setopt($c, CURLOPT_POSTFIELDS, $message);
curl_exec($c);
curl_close($c);

*/
// Чтобы отправлять запросы, нам нужен токен. Мы берем его по аккаунту.
//$poster_access_token = Login::get_token($postData['account']);

// Ловим вебхуки.
// 1) на удаление сотрудника (помечаем статусом 0)

if($postData['action'] == 'removed' AND $postData['object'] == 'waiter'){

    $poster_account_name = $postData['account'];
    $worker_poster_id = $postData['object_id'];

    // обновляем статус сотрудника на 1 - не актив
    //WorkerPoster::update_status($worker_poster_id, $poster_account_name);

    $text = 'Сотрудник с аккаунта: '.$postData['account'].' ';
    $text .= 'с ID: '.$postData['object_id'].' ';
    $text .= 'Помечен как удаленный';
    $text .= 'Time: '.$postData['time'].' ';

    // Отправляем уведомление
    mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);

    // Уведомление в SLACK
    /*$message = array('payload' => json_encode(array('text' => $text)));
    // Use curl to send your message
    $c = curl_init(SLACK_WEBHOOK);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $message);
    curl_exec($c);
    curl_close($c);*/


}elseif($postData['action'] == 'changed' AND $postData['object'] == 'waiter') {

    // 2) на редактирование сотрудника
    $poster_account_name = $postData['account'];
    $worker_poster_id = $postData['object_id'];

    $text = 'Сотрудник отредактирован. Сообщение отправлено в чат Калуги.';
    // Уведомление в SLACK
    /*$message = array('payload' => json_encode(array('text' => $text)));
    // Use curl to send your message
    $c = curl_init(SLACK_WEBHOOK);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $message);
    curl_exec($c);
    curl_close($c);*/



}elseif($postData['action'] == 'added' AND $postData['object'] == 'waiter'){
// 3) на добавление сотрудника
    $poster_account_name = $postData['account'];
    $worker_poster_id = $postData['object_id'];

    // Получить всех сотрудников
    $url = 'https://'.$poster_account_name.'.joinposter.com/api/access.getEmployees?token='.$poster_access_token.'';
    $data = json_decode(Poster::sendRequest($url));

    // Найти имя сотрудника
    for($i = 0; $i < count($data->response); $i++){
        if($worker_poster_id == $data->response[$i]->user_id){
            $worker_poster_name = $data->response[$i]->name;
        }
    }

    // Добавляем сотрудника
    //WorkerPoster::add_value($poster_account_name, $worker_poster_id, $worker_poster_name);

    // Отправляем уведомление
    $text = 'Сотрудник '.$worker_poster_name.' добавлен на аккаунт: '.$postData['account'].' ';
    //mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
    // Уведомление в SLACK
    /*$message = array('payload' => json_encode(array('text' => $text)));
    // Use curl to send your message
    $c = curl_init(SLACK_WEBHOOK);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $message);
    curl_exec($c);
    curl_close($c);*/


}else{
    // false
    $text = 'Аккаунт: '.$postData['account'].' ';
    $text .= 'Объект: '.$postData['object_id'].' ';
    $text .= 'Экшен: '.$postData['action'].' ';
    $text .= 'Object: '.$postData['object'].' ';
    $text .= 'Time: '.$postData['time'].' ';
    $text .= 'Data: '.$postData['data'].' ';

    mail('storozhuk.nikita@gmail.com', 'Webhook Waiter Error', $text);

    // Уведомление в SLACK
    /*$message = array('payload' => json_encode(array('text' => $text)));
    // Use curl to send your message
    $c = curl_init(SLACK_WEBHOOK);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($c, CURLOPT_POST, true);
    curl_setopt($c, CURLOPT_POSTFIELDS, $message);
    curl_exec($c);
    curl_close($c);*/

}