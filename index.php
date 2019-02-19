
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

$name        = "Название здесь идет";
$email       = "storozhuk.nikita@gmail.com";
$to          = "$name <$email>";
$from        = "John Doe ";
$subject     = "тема ";
$mainMessage = "Привет,я сообщение с pdf файлом";
$fileatt     = "reciept.pdf"; // Расположение файла
$fileatttype = "application/pdf";
$fileattname = "newName.pdf"; //Имя, которое вы хотите использовать для отправки, или вы можете использовать то же имя
$headers     = "From: $from";

// Открываем и читаем файл в переменную.
$file = fopen($fileatt, 'rb');
$data = fread($file, filesize($fileatt));
fclose($file);

// Это прикрепляет файл
$semi_rand     = md5(time());
$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
$headers      .= "\nMIME-Version: 1.0\n" .
  "Content-Type: multipart/mixed;\n" .
  " boundary=\"{$mime_boundary}\"";
  $message = "Это multi-part сообщение в формате MIME․\n\n" .
  "-{$mime_boundary}\n" .
  "Content-Type: text/plain; charset=\"iso-8859-1\n" .
  "Content-Transfer-Encoding: 7bit\n\n" .
  $mainMessage  . "\n\n";

$data = chunk_split(base64_encode($data));
$message .= "--{$mime_boundary}\n" .
  "Content-Type: {$fileatttype};\n" .
  " name=\"{$fileattname}\"\n" .
  "Content-Disposition: attachment;\n" .
  " filename=\"{$fileattname}\"\n" .
  "Content-Transfer-Encoding: base64\n\n" .
$data . "\n\n" .
 "-{$mime_boundary}-\n";

// Отправить письмо
if(mail($to, $subject, $message, $headers))
{
    echo "Письмо отправлено.";
} else {
    echo "При отправке почты произошла ошибка.";
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


