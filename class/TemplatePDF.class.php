<?php

class TemplatePDF extends FPDF {
    function Header() {
        //Логотип
        $this->Image('https://pp.userapi.com/c850520/v850520899/95be/JMAwardISGc.jpg',10,8,33);
        //шрифт Arial, жирный, размер 15
        $this->SetFont('Arial','B',15);
        //Перемещаемся вправо
        $this->Cell(80);
        //Название
        $this->Cell(30,10,'Title',1,0,'C');
        //Разрыв строки
        $this->Ln(20);

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