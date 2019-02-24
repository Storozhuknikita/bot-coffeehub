<style type="text/css" href="style.css"></style>

<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
</script>

<?php

require __DIR__.'/vendor/autoload.php';
include_once 'config.php';
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

$data = (object)PosterApi::dash()->getSpotsSales();
$files = (object)PosterApi::clients()->getClients();

$i = 0;

// данные для фильтров
//$year = $_POST['year'];
$year = date('Y');
$month_min = date('n')-1;
$month_max = date('n')-1;
$day_min = 1;
$day_max = 31;

foreach ($files['response'] as $file) {
    $date_active = date_parse($file['date_activale']);
    if (
        ($date_active['year'] == $year AND
            $date_active['month'] >= $month_min AND $date_active['month'] <= $month_max) AND
        ($date_active['day'] >= $day_min AND $date_active['day'] <= $day_max)
    ) {
        $i++;
    }
}
$clients = 'New clients (' . $day_min . '-' . $month_min . ') - (' . $day_max . '-' . $month_max . ') - ' . $i . '';

$pdf = new FPDF('P', 'pt', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$value = 'Revenue: '.$data->response->revenue.'';
$average = 'Middle Invoice: '.$data->response->middle_invoice.'';

$pdf->SetX(140);
$pdf->SetFont('Arial', 'I');
$pdf->Cell(200, 15, $value, 0, 2);
$pdf->Cell(200, 15, $average, 0, 2);
$pdf->Cell(200, 15, $clients);
$pdf->Ln(100);

$pdf->Output('reciept.pdf', 'F');

// Генерация PDF и сохранение в файл
$doc = $pdf->Output('reciept.pdf', 'S');

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


?>