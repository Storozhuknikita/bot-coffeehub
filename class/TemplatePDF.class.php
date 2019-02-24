<?php

class TemplatePDF extends FPDF {

    function Header() {

        global $title;


    }


    function Footer() {
        //Позиция на 1,5 cm от нижнего края страницы
        $this->SetY(-15);
        //Шрифт Arial, курсив, размер 8
        $this->SetFont('Arial','I',8);
        //Номер страницы
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }

}