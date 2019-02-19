
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
$url = 'https://'.$account_name.'.joinposter.com/api/access.getEmployees?token='.$access_token.'';
$data = json_decode(Poster::sendRequest($url));

//echo'<pre>';
//print_r($auth);
//echo'</pre>';

class PDF_reciept extends FPDF {
    function __construct ($orientation = 'P', $unit = 'pt', $format = 'Letter', $margin = 40) {
        $this->FPDF($orientation, $unit, $format);
        $this->SetTopMargin($margin);
        $this->SetLeftMargin($margin);
        $this->SetRightMargin($margin);
        $this->SetAutoPageBreak(true, $margin);
    }

    function Header() {
        $this->SetFont('Arial', 'B', 20);
        $this->SetFillColor(36, 96, 84);
        $this->SetTextColor(225);
        $this->Cell(0, 30, "Nettuts+ Online Store", 0, 1, 'C', true);
    }

    function Footer() {
        $this->SetFont('Arial', '', 12);
        $this->SetTextColor(0);
        $this->SetXY(0,-60);
        $this->Cell(0, 20, "Thank you for shopping at Nettuts+!", 'T', 0, 'C');
    }

    function PriceTable($products, $prices) {
        $this->SetFont('Arial', 'B', 12);
        $this->SetTextColor(0);
        $this->SetFillColor(36, 140, 129);
        $this->SetLineWidth(1);
        $this->Cell(427, 25, "Item Description", 'LTR', 0, 'C', true);
        $this->Cell(100, 25, "Price", 'LTR', 1, 'C', true);
        $this->SetFont('Arial', '');
        $this->SetFillColor(238);
        $this->SetLineWidth(0.2);
        $fill = false;
        for ($i = 0; $i < count($products); $i++) {
            $this->Cell(427, 20, $products[$i], 1, 0, 'L', $fill);
            $this->Cell(100, 20, '$' . $prices[$i], 1, 1, 'R', $fill);
            $fill = !$fill;
        }
        $this->SetX(367);
        $this->Cell(100, 20, "Total", 1);
        $this->Cell(100, 20, '$' . array_sum($prices), 1, 1, 'R');
    }


}





//$pdf = new FPDF('P', 'pt', 'Letter');
$pdf = new PDF_reciept();

$pdf->AddPage(); // Создание первой страницы
$pdf->SetFont('Arial', '', 12); // Шрифт
$pdf->SetY(100);

$pdf->Cell(100, 13, "Ordered By");
$pdf->SetFont('Arial', '');
$pdf->Cell(100, 13, $_POST['name']);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 13, 'Date');
$pdf->SetFont('Arial', '');
$pdf->Cell(100, 13, date('F j, Y'), 0, 1);

$pdf->SetX(140);
$pdf->SetFont('Arial', 'I');
$pdf->Cell(200, 15, $_POST['address'], 0, 2);
$pdf->Cell(200, 15, $_POST['city'] . ', ' . $_POST['province'], 0, 2);
$pdf->Cell(200, 15, $_POST['postal_code'] . ' ' . $_POST['country']);
$pdf->Ln(100);

$pdf->PriceTable($_POST['product'], $_POST['price']);

$pdf->Ln(50);

$message = "Thank you for ordering at the Nettuts+ online store. Our policy is to ship your materials within two business days of purchase. On all orders over $20.00, we offer free 2-3 day shipping. If you haven't received your items in 3 busines days, let us know and we'll reimburse you 5%.We hope you enjoy the items you have purchased. If you have any questions, you can email us at the following email address:";
$pdf->MultiCell(0, 15, $message);

$pdf->SetFont('Arial', 'U', 12);
$pdf->SetTextColor(1, 162, 232);
$pdf->Write(13, "store@nettuts.com", "mailto:example@example.com");

//$pdf->Output('reciept.pdf', 'F');
$doc = $pdf->Output('reciept.pdf', 'S');


// Строки записываем
//$pdf->Cell(100, 16, "Hello, World!");
//$pdf->Cell(100, 16, "Hello, World!");
//$pdf->Cell(100, 16, "Hello, World!");
//$pdf->Cell(100, 16, "Hello, World!");


//$pdf->Output('reciept.pdf', 'F');

//echo $pdf->Output('S');

// Генерация PDF и сохранение в файл
//$doc = $pdf->Output('reciept.pdf', 'S');

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