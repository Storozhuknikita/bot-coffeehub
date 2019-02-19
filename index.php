
<script type="text/javascript">
    window.addEventListener('load', function () { top.postMessage({hideSpinner: true}, '*') }, false);
</script>

<?php

// https://php-academy.kiev.ua/blog/generating-pdfs-with-php


error_reporting(E_ALL);

define('POSTER_CLIENT_ID', '223');
define('POSTER_CLIENT_SECRET', '1547ba15d5dc931f291bbf8fdb5e8fa4');


//header("Content-type:application/pdf");


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

$account_name = $auth->account_name;
$access_token = $auth->access_token;

// Получить всех сотрудников
$url = 'https://'.$account_name.'.joinposter.com/api/access.getEmployees?token='.$access_token.'';
$data = json_decode(Poster::sendRequest($url));

require('fpdf181/fpdf.php');
$pdf = new FPDF('P', 'pt', 'Letter');

$pdf->AddPage();
$pdf->SetFont('Arial', '', 12);

$pdf->Cell(100, 16, "Hello, World!");


$pdf->Output('reciept.pdf', 'F');

//echo $pdf->Output('S');


$doc = $pdf->Output('reciept.pdf', 'S');

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

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

    //Recipients
    $mail->setFrom('storozhuk.nikita@gmail.com', 'Mailer');
    $mail->addAddress('storozhuk.nikita@gmail.com', 'Joe User');     // Add a recipient
    //$mail->addAddress('ellen@example.com');               // Name is optional
    $mail->addReplyTo('storozhuk.nikita@gmail.com', 'Information');
    //$mail->addCC('cc@example.com');
    //$mail->addBCC('bcc@example.com');

    //Attachments
    $mail->addAttachment('reciept.pdf');         // Add attachments
    //$mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name

    //Content
    $mail->isHTML(true);                                  // Set email format to HTML
    $mail->Subject = 'Here is the subject';
    $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
    $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

    $mail->send();
    echo 'Message has been sent';
} catch (Exception $e) {
    echo 'Message could not be sent. Mailer Error: ', $mail->ErrorInfo;
}


//$mail->AddStringAttachment($doc, 'doc.pdf', 'base64', 'application/pdf');
//$mail->Send();

/*
echo'<pre>';
print_r($data);
echo'</pre>';

echo'<hr>';

echo'<pre>';
print_r($auth);
echo'</pre>';

// Отправляем уведомление
//mail('storozhuk.nikita@gmail.com', 'Webhook Waiter', $text);
*/

?>


