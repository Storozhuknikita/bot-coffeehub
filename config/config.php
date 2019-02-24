<?php

define('POSTER_CLIENT_ID', '162');
define('POSTER_CLIENT_SECRET', 'b1d1d560b773df86da147d36ae2b2294');


error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');


function debug($var) {
    echo'<pre>';
    print_r($var);
    echo'</pre>';
}


// Poster Class для авторизации
include_once '../class/Poster.class.php';

// PDF Class
require('../class/fpdf181/fpdf.php');

// Template PDF
include_once '../class/TemplatePDF.class.php';


// MAIL Class
require '../class/PHPMailer-master/src/Exception.php';
require '../class/PHPMailer-master/src/PHPMailer.php';
require '../class/PHPMailer-master/src/SMTP.php';

?>