
<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
</script>

<?php

// https://php-academy.kiev.ua/blog/generating-pdfs-with-php

error_reporting(E_ALL);

/*
 * Задача файла
 * Сбор информации о выручке
 * Генерация PDF
 * Отправка на почту
 */

define('POSTER_CLIENT_ID', '223');
define('POSTER_CLIENT_SECRET', '1547ba15d5dc931f291bbf8fdb5e8fa4');

//header("Content-type:application/pdf");

// Poster Class
include_once 'class/Poster.class.php';

// PDF Class
require('fpdf181/fpdf.php');

// MAIL Class
require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Получаем данные от Poster
$code = $_REQUEST['code'];

// Отправляем запрос в Poster
$auth = Poster::auth($_REQUEST['code']);

$account_name = $auth->account_name;
$access_token = $auth->access_token;

// Получить всех сотрудников
$url = 'https://'.$account_name.'.joinposter.com/api/dash.getSpotsSales?token='.$access_token.'';
$data = json_decode(Poster::sendRequest($url));


echo'<pre>';
print_r($data);
echo'</pre>';

$pdf = new FPDF('P', 'pt', 'Letter');
$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$text = iconv('windows-1251', 'utf-8', 'Выручка: '.$data->response->revenue.'');

// Строки записываем
$pdf->Cell(100, 16, $text);


$pdf->Output('reciept.pdf', 'F');

//echo $pdf->Output('S');

// Генерация PDF и сохранение в файл
$doc = $pdf->Output('reciept.pdf', 'S');

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
    $mail->send();

    echo 'Сообщение было отправлено';
} catch (Exception $e) {
    echo 'Сообщение не было отправлено. Mailer Error: ', $mail->ErrorInfo;
}

?>