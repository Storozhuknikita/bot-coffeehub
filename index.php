<style type="text/css" href="style/style.css"></style>

<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
</script>

<?php

header("content-type:text/html; charset=utf-8");

require __DIR__.'/vendor/autoload.php';
include_once 'config/config.php';
use poster\src\PosterApi;

// Poster Class для авторизации
include_once 'class/Poster.class.php';

// PDF Class
require('class/fpdf181/fpdf.php');
// MAIL Class
require 'class/PHPMailer-master/src/Exception.php';
require 'class/PHPMailer-master/src/PHPMailer.php';
require 'class/PHPMailer-master/src/SMTP.php';

// Получаем данные от Poster
$code = $_REQUEST['code'];

// Отправляем запрос в Poster
$auth = Poster::auth($_REQUEST['code']);

// Настройка аккаунта и токена для запросов
PosterApi::init ([
    'account_name' => $auth->account_name,
    'access_token' => $auth->access_token,
]);


$params = array('dateFrom' => date('Ym01'), 'dateTo' => date('Ymt'));
$params2 = array('date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t')); // для чеков

// Берем данные из Poster
$data = (object)PosterApi::dash()->getSpotsSales($params); // Получение выручки
$files = (object)PosterApi::clients()->getClients(); // Получение клиентов
$finance = (object)PosterApi::finance()->getAccounts(); // Получение счетов
$orders = (object)PosterApi::transactions()->getTransactions($params2); // Получение чеков

$storage = (object)PosterApi::storage()->getStorageLeftovers(); // Получить складские остатки


$logo = (object)PosterApi::settings()->getLogo(); // Получаем лого

//debug($logo);

$balance_sum = 0; // Начальный баланс для счетов
$storage_sum = 0; // Начальный баланс для склада

// Просчет суммы по складам
foreach ($storage->response as $s) {
    $storage_sum = $s->storage_ingredient_sum + $storage_sum;
}

// Просчет суммы по всем счетам
foreach ($finance->response as $f) {
    $balance_sum = $f->balance + $balance_sum;
}

$i = 0; // Начальный счетчик клиентов

// Данные для подсчета новых клиентов
$year = date('Y');
$month_min = date('n')-1;
$month_max = date('n')-1;
$day_min = 1;
$day_max = 31;

// Считаем количество новых клиентов
foreach ($files->response as $file) {
    $date_active = date_parse($file->date_activale);
    if (
        ($date_active['year'] == $year AND
            $date_active['month'] >= $month_min AND $date_active['month'] <= $month_max) AND
        ($date_active['day'] >= $day_min AND $date_active['day'] <= $day_max)
    ) {
        $i++;
    }
}


// Подготовка финальных данных для генерации PDF
// Финансы
$finance = 'Balance: '.substr($balance_sum,0,-2); // Удаляем последние 2 цифры (копейки)
// Финансы
$storage = 'Balance Storage: '.substr($storage_sum,0,-2); // Удаляем последние 2 цифры (копейки)



// Статистика
$value = 'Revenue: '.$data->response->revenue.'';
$average = 'Middle Invoice: '.round($data->response->middle_invoice).''; // Округляем средний чек в большую сторону, избавляем от десятых

// Заголовок
$title = 'Company Performance Report For (' . $day_min . '-' . $month_min . '-' .$year. ') - (' . $day_max . '-' . $month_max . '-' . $year . ') ';


// Подготовка PDF
$pdf = new FPDF('P', 'pt', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->SetX(70);
$pdf->SetY(20);

$pdf->SetFont('Arial','B',18);
$pdf->Cell(100, 15, $title, 0, 2); // Заголовок листа
//$pdf->Image('https://joinposter.com'.$logo->response->value, 10, 10, 35, 35);

$pdf->SetX(70);
$pdf->SetY(100);
// Финансы
$pdf->SetFont('Arial','B',16);
$pdf->Cell(100, 15, 'Financial Balance', 0, 2); // Заголовок "Баланс"
$pdf->SetFont('');
$pdf->Cell(100, 15, $finance, 0, 2); // Баланс всех счетов
$pdf->Cell(100, 15, ' ', 0, 2); // Пустая строка



// Склад
$pdf->SetFont('Arial','B',16);
$pdf->Cell(100, 15, 'Storage', 0, 2); // Заголовок "Склад"
$pdf->SetFont('');
$pdf->Cell(100, 15, 'Balance Storage: ', 0, 2); // Баланс Склада
$pdf->Cell(100, 15, 'Withdrawals From The Warehouse: ', 0, 2); // Кол-во списаний
$pdf->Cell(100, 15, ' ', 0, 2); // Пустая строка


// Маркетинг
$pdf->SetFont('Arial','B',16);
$pdf->Cell(100, 15, 'Marketing', 0, 2); // Заголовок "Макретинг"
$pdf->SetFont('');

$pdf->Cell(300, 15, 'Clients: ', 0, 2); // Записываем количество клиентов
$pdf->Cell(300, 15, 'New Clients: '.$i, 0, 2); // Записываем количество новых клиентов
$pdf->Cell(100, 15, ' ', 0, 2); // Пустая строка


// Статистика
$pdf->SetFont('Arial','B',16);
$pdf->Cell(100, 15, 'Statistics', 0, 2); // Заголовок "Статистика"
$pdf->SetFont('');

$pdf->Cell(100, 15, 'All Objects', 0, 2); // Выручка
$pdf->Cell(100, 15, $value, 0, 2); // Выручка
$pdf->Cell(100, 15, $average, 0, 2); // Средний чек
$pdf->Cell(100, 15, 'Count order: '.$orders->response->count, 0, 2); // Количество чеков

$pdf->Cell(100, 15, 'Food Cost: ', 0, 2); // Food Cost
$pdf->Cell(100, 15, 'Cash: ', 0, 2); // Наличные
$pdf->Cell(100, 15, 'Cart: ', 0, 2); // Карта
$pdf->Cell(100, 15, 'Bonus: ', 0, 2); // Бонусами



$pdf->Ln(100);

$pdf->Output('reciept.pdf', 'F'); // Записываем в файл

// Генерация PDF и сохранение в файл
$doc = $pdf->Output('reciept.pdf', 'S');

echo 'Файл отчет готов - <a href="https://bot.coffee-hub.ru/reciept.pdf" target="_blank">Download</a> ';


/*
// Подготовка письма
$mail = new PHPMailer\PHPMailer\PHPMailer();
try {
    //Server settings
    /*$mail->SMTPDebug = 2;                                 // Enable verbose debug output
    $mail->isSMTP();                                      // Set mailer to use SMTP
    $mail->Host = 'smtp1.example.com;smtp2.example.com';  // Specify main and backup SMTP servers
    $mail->SMTPAuth = true;                               // Enable SMTP authentication
    $mail->Username = 'user@example.com';                 // SMTP username
    $mail->Password = 'secret';                           // SMTP password
    $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
    $mail->Port = 587;   */                                 // TCP port to connect to
/*
    $mail->CharSet = 'UTF-8';

    //Recipients
    $mail->setFrom('storozhuk.nikita@gmail.com', 'Отчеты о бизнесе'); // От кого
    $mail->addAddress($auth->ownerInfo->email, $auth->ownerInfo->name);     // Кому

    //$mail->addAddress('ellen@example.com');               // Name is optional
    //$mail->addReplyTo('storozhuk.nikita@gmail.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    // Файлы
    $mail->addAttachment('reciept.pdf');

    // Содержимое письма
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Отчет по бизнесу '.$auth->ownerInfo->company_name;
    $mail->Body    = 'У вас все заебок <b>in bold!</b>';
    $mail->AltBody = 'У вас все заебок без HTML';

    // Отправка письма
    //$mail->send();

    echo 'Сообщение было отправлено - <a href="https://bot.coffee-hub.ru/reciept.pdf">Download</a> ';
} catch (Exception $e) {
    echo 'Сообщение не было отправлено. Mailer Error: ', $mail->ErrorInfo;
}

*/
?>