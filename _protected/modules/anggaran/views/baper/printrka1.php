<?php
Use app\itbz\fpdf\src\fpdf\fpdf;


class PDF_Rotate extends \fpdf\FPDF
{
    var $angle=0;

    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5F %.5F %.5F %.5F %.2F %.2F cm 1 0 0 1 %.2F %.2F cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
}

class PDF extends PDF_Rotate
{

    function setModel($model){
        $this->model = $model;
    }

    function Header(){
        //masukkan watermark apabila status == 0
        IF($this->model->status == 1){
            //Put the watermark
            $this->SetFont('Arial','B',50);
            $this->SetTextColor(163,163,163);
            $this->RotatedText(60,190,'Draft',45);
        }        
    }
    function Footer()
    {
        //ambil link
        // $link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];        
        // $this->Image("http://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$link", 156, 320 ,5,0,'PNG');        
        // Go to 1.5 cm from bottom
        $this->SetY(-15);
        // Select Arial italic 8
        $this->SetFont('Arial','I',8);
        // Print centered page number
        $this->Cell(0,10,'Printed By BosSTAN '.$this->PageNo().'/{nb}',0,0,'R');
    }


    function RotatedText($x, $y, $txt, $angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);
        $this->Text($x,$y,$txt);
        $this->Rotate(0);
    }    
}

//menugaskan variabel $pdf pada function fpdf().
$pdf = new PDF('P','mm',array(216,330));
$pdf->setModel($model);
function bulan($bulan){
    Switch ($bulan){
        case 1 : $bulan="Januari";
            Break;
        case 2 : $bulan="Februari";
            Break;
        case 3 : $bulan="Maret";
            Break;
        case 4 : $bulan="April";
            Break;
        case 5 : $bulan="Mei";
            Break;
        case 6 : $bulan="Juni";
            Break;
        case 7 : $bulan="Juli";
            Break;
        case 8 : $bulan="Agustus";
            Break;
        case 9 : $bulan="September";
            Break;
        case 10 : $bulan="Oktober";
            Break;
        case 11 : $bulan="November";
            Break;
        case 12 : $bulan="Desember";
            Break;
        }
    return $bulan;
}


function kekata($x) {
    $x = abs($x);
    $angka = array("", "satu", "dua", "tiga", "empat", "lima",
    "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
    $temp = "";
    if ($x <12) {
        $temp = " ". $angka[$x];
    } else if ($x <20) {
        $temp = kekata($x - 10). " belas";
    } else if ($x <100) {
        $temp = kekata($x/10)." puluh". kekata($x % 10);
    } else if ($x <200) {
        $temp = " seratus" . kekata($x - 100);
    } else if ($x <1000) {
        $temp = kekata($x/100) . " ratus" . kekata($x % 100);
    } else if ($x <2000) {
        $temp = " seribu" . kekata($x - 1000);
    } else if ($x <1000000) {
        $temp = kekata($x/1000) . " ribu" . kekata($x % 1000);
    } else if ($x <1000000000) {
        $temp = kekata($x/1000000) . " juta" . kekata($x % 1000000);
    } else if ($x <1000000000000) {
        $temp = kekata($x/1000000000) . " milyar" . kekata(fmod($x,1000000000));
    } else if ($x <1000000000000000) {
        $temp = kekata($x/1000000000000) . " trilyun" . kekata(fmod($x,1000000000000));
    }     
        return $temp;
}
 
 
function terbilang($x, $style=4) {
    if($x<0) {
        $hasil = "minus ". trim(kekata($x));
    } else {
        $hasil = trim(kekata($x));
    }     
    switch ($style) {
        case 1:
            $hasil = strtoupper($hasil);
            break;
        case 2:
            $hasil = strtolower($hasil);
            break;
        case 3:
            $hasil = ucwords($hasil);
            break;
        default:
            $hasil = ucfirst($hasil);
            break;
    }     
    return $hasil;
}

//Menambahkan halaman, untuk menambahkan halaman tambahkan command ini. P artinya potrait dan L artinya Landscape
$pdf->AddPage();
$pdf->SetMargins(25,25,25) ;//(float left, float top [, float right])
$pdf->SetAutoPageBreak(true, 25); // set bottom margin (boolean auto [, float margin])
$link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
//cara menambahkan image dalam dokumen. Urutan data-> alamat file-posisi X- posisi Y-ukuran width - ukurang high -  menambahkan link bila perlu

$pdf->Image('images/logo.jpg',15,15,25,28,'');
$left = 15;

$pdf->SetXY(40,10);
$pdf->SetFont('Arial','B',15); 
$pdf->MultiCell(160,18,strtoupper(\app\models\TaTh::dokudoku('bulat', $ref['set_10'])), '', 'C', 0);

$pdf->SetXY(40,25);
$pdf->SetFont('Arial','B',17); 
$pdf->MultiCell(160,4,strtoupper(\app\models\TaTh::dokudoku('bulat', $ref['set_11'])), '', 'C', 0);

IF($pdf->GetY() <= 35){
    $y = 35;
}ELSE{
    $y = $pdf->GetY();
}
$pdf->SetXY(40,$y);
$pdf->SetFont('Arial','B',10); 
$pdf->MultiCell(160,4, \app\models\TaTh::dokudoku('bulat', $ref['set_5']), '', 'C', 0);

IF($pdf->GetY() <= 40){
    $y = 40;
}ELSE{
    $y = $pdf->GetY();
}
$pdf->SetXY(15,$y);
$pdf->SetFont('Arial','B',9); 
$pdf->MultiCell(185,4,'RINCIAN ANGGARAN PENDAPATAN ORGANISASI PENDAPATAN DAERAH', 1, 'C', 0);

$y = $pdf->GetY()+4;

//content
$w = [20,67,20,20,30,30]; // Tentukan width masing-masing kolom

$pdf->SetFont('Arial','B',9);
$pdf->SetXY($left,$pdf->getY());
$pdf->Cell($w['0'],4,'KODE','LT',0,'C');
$pdf->Cell($w['1'],4,'URAIAN','LTR',0,'C');
$pdf->Cell($w['2']+$w['3']+$w['4'],4,'RINCIAN PERHITUNGAN','LTR',0,'C');
$pdf->Cell($w['5'],4,'Jumlah','LTR',0,'C');
$pdf->ln();

$pdf->SetXY($left,$pdf->getY());
$pdf->Cell($w['0'],4,'REKENING','L',0,'C');
$pdf->Cell($w['1'],4,'','LR',0,'C');
$pdf->Cell($w['2'],4,'Volume','LTR',0,'C');
$pdf->Cell($w['3'],4,'Satuan','LTR',0,'C');
$pdf->Cell($w['4'],4,'Tarif','LTR',0,'C');
$pdf->Cell($w['5'],4,'(Rp)','LR',0,'C');
$pdf->ln();

$pdf->SetFont('Arial','B',10);
$pdf->SetXY($left,$pdf->getY());
$pdf->Cell($w['0'],4,'1','LTB',0,'C');
$pdf->Cell($w['1'],4,'2','LTRB',0,'C');
$pdf->Cell($w['2'],4,'3','LTRB',0,'C');
$pdf->Cell($w['3'],4,'4','LTRB',0,'C');
$pdf->Cell($w['4'],4,'5','LTRB',0,'C');
$pdf->Cell($w['5'],4,'6','LTRB',0,'C');
$pdf->ln();

$y1 = $pdf->GetY(); // Untuk baris berikutnya
$y2 = $pdf->GetY(); //untuk baris berikutnya
$y3 = $pdf->GetY(); //untuk baris berikutnya
$yst = $pdf->GetY(); //untuk Y pertama sebagai awal rectangle
$x = 15;
$kegiatan = NULL;
$i = 1;

$ysisa = $y1;
$kd_program = $kd_sub_program = $kd_kegiatan = $kd_rek_1 = $kd_rek_2 = $kd_rek_3 = $kd_rek_4 = $kd_rek_5 = NULL;

$totalusulan = 0;
$kegiatanlama = NULL;


foreach($data as $data){

	//hitung karakter dari masing-masing record terlebih dahulu
	$kegiatanbaru = $data['kd_program'].'.'.$data['kd_sub_program'].'.'.substr('0'.$data['kd_kegiatan'], -2);
	$uraiankegiatan = $data['uraian_program'].' / '.$data['uraian_kegiatan'];
	// $data['kd_program'].'.'.substr('0'.$data['kd_kegiatan'], -2).' '.$data['Ket_Program'].' / '.$data['Ket_Kegiatan'];
	$uraianrekening = $data['Nm_Rek_5'];
	// $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2).'.'.substr('0'.$data['Kd_Rek_5'], -2).' '.$data['Nm_Rek_5'];
	$charkegiatan = strlen($data['uraian_program'].' / '.$data['uraian_kegiatan']); //35 menampung 23 char
	$charrekening = strlen($data['Nm_Rek_5']); //35 widht menampung 23 char

	// IF($kegiatanlama <> $kegiatanbaru){
	//     $y = MAX($y1, $y2, $y3);

	//     IF($y1 + (4*(strlen($uraiankegiatan)/23)) > 160){ //cek pagebreak
	//         $ylst = 190 - $yst; //207 batas margin bawah dikurang dengan y pertama
	//         //setiap selesai page maka buat rectangle
    //         $pdf->Rect($x, $yst, $w['0'] ,$ylst);
    //         $pdf->Rect($x+$w['0'], $yst, $w['1'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1'], $yst, $w['2'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2'], $yst, $w['3'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3'], $yst, $w['4'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4'], $yst, $w['5'] ,$ylst);
    //         // $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4']+$w['5'], $yst ,$w['6'],$ylst);
	        
	//         //setelah buat rectangle baru kemudian addPage
	//         $pdf->AddPage();

    //         $pdf->SetFont('Arial','B',10);
    //         $pdf->SetXY($left,35);
    //         $pdf->Cell($w['0'],11,'NO','LT',0,'C');
    //         $pdf->SetFont('Arial','B',9);
    //         $pdf->Cell($w['1'],11,'Kode','LTR',0,'C');
    //         $pdf->SetFont('Arial','B',10);
    //         $pdf->Cell($w['2'],11,'Uraian ','LTR',0,'C');
    //         $pdf->SetFont('Arial','B',9);
    //         $pdf->Cell($w['3'],11,'Nilai','LTR',0,'C');
    //         $pdf->SetFont('Arial','B',10);
    //         $pdf->Cell($w['4'],11,'Sumber Dana','LTR',0,'C');
    //         $pdf->Cell($w['5'],11,'Keterangan','LTR',0,'C');
    //         $pdf->ln();

    //         $pdf->SetFont('Arial','B',10);
    //         $pdf->SetXY($left,41);
    //         $pdf->Cell($w['0'],6,'','L',0,'C');
    //         $pdf->Cell($w['1'],6,'','LR',0,'C');
    //         $pdf->Cell($w['2'],6,'','LR',0,'C');
    //         $pdf->Cell($w['3'],6,'','LR',0,'C');
    //         $pdf->Cell($w['4'],6,'','LR',0,'C');
    //         $pdf->Cell($w['5'],6,'','LR',0,'C');
    //         $pdf->ln();

    //         $pdf->SetFont('Arial','B',10);
    //         $pdf->SetXY($left,46);
    //         $pdf->Cell($w['0'],6,'1','LTB',0,'C');
    //         $pdf->Cell($w['1'],6,'2','LTRB',0,'C');
    //         $pdf->Cell($w['2'],6,'3','LTRB',0,'C');
    //         $pdf->Cell($w['3'],6,'4','LTRB',0,'C');
    //         $pdf->Cell($w['4'],6,'5','LTRB',0,'C');
    //         $pdf->Cell($w['5'],6,'6','LTRB',0,'C');
    //         $pdf->ln();


	//         $y1 = $pdf->GetY(); // Untuk baris berikutnya
	//         $y2 = $pdf->GetY(); //untuk baris berikutnya
	//         $y3 = $pdf->GetY(); //untuk baris berikutnya
	//         $yst = $pdf->GetY(); //untuk Y pertama sebagai awal rectangle
	//         $x = 15;
	//         $ysisa = $y1;

	//     }

	//     //new data
	//     $pdf->SetFont('Arial','B',8);    
	//     $pdf->SetXY($x, $y);
	//     $xcurrent= $x;
	//     $pdf->MultiCell($w['0'],4,'','T','C');
	//     $xcurrent = $xcurrent+$w['0'];
	//     $pdf->SetXY($xcurrent, $y);
    //     $pdf->MultiCell($w['1'],4,$data['kd_program'].'.'.$data['kd_sub_program'].'.'.substr('0'.$data['kd_kegiatan'], -2),'T','L');
	//     $xcurrent = $xcurrent+$w['1'];
	//     $pdf->SetXY($xcurrent, $y);
	//     $pdf->MultiCell($w['2'],4,$data['uraian_program'].' / '.$data['uraian_kegiatan'],'T','L');
    //     $y1 = $pdf->GetY();
	//     $xcurrent = $xcurrent+$w['2'];  
	//     $pdf->SetXY($xcurrent, $y);
	//     $pdf->MultiCell($w['3'],4,'','T','R');
	//     $xcurrent = $xcurrent+$w['3'];
	//     $pdf->SetXY($xcurrent, $y);
	//     $pdf->MultiCell($w['4'],4,'','T','L');
	//     $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
	//     $xcurrent = $xcurrent+$w['4'];
	//     $pdf->SetXY($xcurrent, $y);
	//     $pdf->MultiCell($w['5'],4,'','T','L');
	//     $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
	//     $xcurrent = $xcurrent+$w['5'];
	//     $pdf->SetXY($xcurrent, $y);
	    
	//     $ysisa = $y;

	//     $pdf->ln();
	// }

    // IF($y2 > 160 || $y1 + (4*(strlen($uraianrekening)/23)) > 160 || $y3 > 160  ){ //cek pagebreak
    //     $ylst = 190 - $yst; //207 batas margin bawah dikurang dengan y pertama
    //     //setiap selesai page maka buat rectangle
    //         $pdf->Rect($x, $yst, $w['0'] ,$ylst);
    //         $pdf->Rect($x+$w['0'], $yst, $w['1'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1'], $yst, $w['2'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2'], $yst, $w['3'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3'], $yst, $w['4'] ,$ylst);
    //         $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4'], $yst, $w['5'] ,$ylst);
    //         // $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4']+$w['5'], $yst ,$w['6'],$ylst);
        
    //     //setelah buat rectangle baru kemudian addPage
    //     $pdf->AddPage();

    //     $pdf->SetFont('Arial','B',10);
    //     $pdf->SetXY($left,35);
    //     $pdf->Cell($w['0'],11,'NO','LT',0,'C');
    //     $pdf->SetFont('Arial','B',9);
    //     $pdf->Cell($w['1'],11,'Kode','LTR',0,'C');
    //     $pdf->SetFont('Arial','B',10);
    //     $pdf->Cell($w['2'],11,'Uraian ','LTR',0,'C');
    //     $pdf->SetFont('Arial','B',9);
    //     $pdf->Cell($w['3'],11,'Nilai','LTR',0,'C');
    //     $pdf->SetFont('Arial','B',10);
    //     $pdf->Cell($w['4'],11,'Sumber Dana','LTR',0,'C');
    //     $pdf->Cell($w['5'],11,'Keterangan','LTR',0,'C');
    //     $pdf->ln();

    //     $pdf->SetFont('Arial','B',10);
    //     $pdf->SetXY($left,41);
    //     $pdf->Cell($w['0'],6,'','L',0,'C');
    //     $pdf->Cell($w['1'],6,'','LR',0,'C');
    //     $pdf->Cell($w['2'],6,'','LR',0,'C');
    //     $pdf->Cell($w['3'],6,'','LR',0,'C');
    //     $pdf->Cell($w['4'],6,'','LR',0,'C');
    //     $pdf->Cell($w['5'],6,'','LR',0,'C');
    //     $pdf->ln();

    //     $pdf->SetFont('Arial','B',10);
    //     $pdf->SetXY($left,46);
    //     $pdf->Cell($w['0'],6,'1','LTB',0,'C');
    //     $pdf->Cell($w['1'],6,'2','LTRB',0,'C');
    //     $pdf->Cell($w['2'],6,'3','LTRB',0,'C');
    //     $pdf->Cell($w['3'],6,'4','LTRB',0,'C');
    //     $pdf->Cell($w['4'],6,'5','LTRB',0,'C');
    //     $pdf->Cell($w['5'],6,'6','LTRB',0,'C');
    //     $pdf->ln();


    //     $y1 = $pdf->GetY(); // Untuk baris berikutnya
    //     $y2 = $pdf->GetY(); //untuk baris berikutnya
    //     $y3 = $pdf->GetY(); //untuk baris berikutnya
    //     $yst = $pdf->GetY(); //untuk Y pertama sebagai awal rectangle
    //     $x = 15;
    //     $ysisa = $y1;

    // }

    if($kd_rek_1 != $data['Kd_Rek_1']){
        $y = MAX($y1, $y2, $y3);
        
        //new data
        $pdf->SetFont('Arial','B',8);    
        $pdf->SetXY($x+2, $y);
        $xcurrent= $x;
        $pdf->MultiCell($w['0'],4,$data['Kd_Rek_1'],'','L');
        $xcurrent = $xcurrent+$w['0'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['1'],4,$data['Nm_Rek_1'],'','L');
        $xcurrent = $xcurrent+$w['1'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['2'],4,'','','L');
        $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
        $xcurrent = $xcurrent+$w['2'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['3'],4,'','','R');
        $xcurrent = $xcurrent+$w['3'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['4'],4,'','','L');
        $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['4'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['5'],4,'','','L');
        $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['5'];
        $pdf->SetXY($xcurrent, $y);
    }

    if($kd_rek_2 != $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2']){
        $y = MAX($y1, $y2, $y3);
        
        //new data
        $pdf->SetFont('Arial','B',8);    
        $pdf->SetXY($x+2, $y);
        $xcurrent= $x;
        $pdf->MultiCell($w['0'],4,$data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'],'','L');
        $xcurrent = $xcurrent+$w['0'];
        $pdf->SetXY($xcurrent+2, $y);
        $pdf->MultiCell($w['1'],4,$data['Nm_Rek_2'],'','L');
        $xcurrent = $xcurrent+$w['1'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['2'],4,'','','L');
        $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
        $xcurrent = $xcurrent+$w['2'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['3'],4,'','','R');
        $xcurrent = $xcurrent+$w['3'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['4'],4,'','','L');
        $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['4'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['5'],4,'','','L');
        $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['5'];
        $pdf->SetXY($xcurrent, $y);
    }

    if($kd_rek_3 != $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3']){
        $y = MAX($y1, $y2, $y3);
        
        //new data
        $pdf->SetFont('Arial','',8);    
        $pdf->SetXY($x+2, $y);
        $xcurrent= $x;
        $pdf->MultiCell($w['0'],4,$data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'],'','L');
        $xcurrent = $xcurrent+$w['0'];
        $pdf->SetXY($xcurrent+4, $y);
        $pdf->MultiCell($w['1'],4,$data['Nm_Rek_3'],'','L');
        $xcurrent = $xcurrent+$w['1'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['2'],4,'','','L');
        $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
        $xcurrent = $xcurrent+$w['2'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['3'],4,'','','R');
        $xcurrent = $xcurrent+$w['3'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['4'],4,'','','L');
        $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['4'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['5'],4,'','','L');
        $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['5'];
        $pdf->SetXY($xcurrent, $y);
    }

    if($kd_rek_4 != $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2)){
        $y = MAX($y1, $y2, $y3);
        
        //new data
        $pdf->SetFont('Arial','',8);    
        $pdf->SetXY($x+2, $y);
        $xcurrent= $x;
        $pdf->MultiCell($w['0'],4,$data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2),'','L');
        $xcurrent = $xcurrent+$w['0'];
        $pdf->SetXY($xcurrent+6, $y);
        $pdf->MultiCell($w['1'],4,$data['Nm_Rek_4'],'','L');
        $xcurrent = $xcurrent+$w['1'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['2'],4,'','','L');
        $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
        $xcurrent = $xcurrent+$w['2'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['3'],4,'','','R');
        $xcurrent = $xcurrent+$w['3'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['4'],4,'','','L');
        $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['4'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['5'],4,'','','L');
        $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['5'];
        $pdf->SetXY($xcurrent, $y);
    }

    if($kd_rek_5 != $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2).'.'.substr('0'.$data['Kd_Rek_5'], -2)){
        $y = MAX($y1, $y2, $y3);
        
        //new data
        $pdf->SetFont('Arial','',8);    
        $pdf->SetXY($x+2, $y);
        $xcurrent= $x;
        $pdf->MultiCell($w['0'],4,$data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2).'.'.substr('0'.$data['Kd_Rek_5'], -2),'','L');
        $xcurrent = $xcurrent+$w['0'];
        $pdf->SetXY($xcurrent+8, $y);
        $pdf->MultiCell($w['1'],4,$data['Nm_Rek_5'],'','L');
        $xcurrent = $xcurrent+$w['1'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['2'],4,'','','L');
        $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
        $xcurrent = $xcurrent+$w['2'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['3'],4,'','','R');
        $xcurrent = $xcurrent+$w['3'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['4'],4,'','','L');
        $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['4'];
        $pdf->SetXY($xcurrent, $y);
        $pdf->MultiCell($w['5'],4,'','','L');
        $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
        $xcurrent = $xcurrent+$w['5'];
        $pdf->SetXY($xcurrent, $y);
    }

    $y = MAX($y1, $y2, $y3);
    
    //new data
    $pdf->SetFont('Arial','',8);    
    $pdf->SetXY($x, $y);
    $xcurrent= $x;
    $pdf->MultiCell($w['0'],4,'','','C');
    $xcurrent = $xcurrent+$w['0'];
    $pdf->SetXY($xcurrent+10, $y);
    $pdf->MultiCell($w['1'],4,'- '.$data['nama_sekolah'],'','L');
    $xcurrent = $xcurrent+$w['1'];
    $pdf->SetXY($xcurrent, $y);
    $pdf->MultiCell($w['2'],4,number_format($data['jml_satuan'],0,',','.'),'','C');
    $y1 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan    
    $xcurrent = $xcurrent+$w['2'];
    $pdf->SetXY($xcurrent, $y);
    $pdf->MultiCell($w['3'],4,$data['satuan123'],'','C');
    $xcurrent = $xcurrent+$w['3'];
    $pdf->SetXY($xcurrent, $y);
    $pdf->MultiCell($w['4'],4,number_format($data['nilai_rp'],0,',','.'),'','R');
    $y2 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
    $xcurrent = $xcurrent+$w['4'];
    $pdf->SetXY($xcurrent, $y);
    $pdf->MultiCell($w['5'],4,number_format($data['total'],0,',','.'),'','R');
    $y3 = $pdf->GetY(); //berikan nilai untuk $y1 titik terbawah Uraian Kegiatan
    $xcurrent = $xcurrent+$w['5'];
    $pdf->SetXY($xcurrent, $y);

    
    $totalusulan = $totalusulan+$data['total'];
    $kegiatanlama = $data['kd_program'].'.'.$data['kd_sub_program'].'.'.substr('0'.$data['kd_kegiatan'], -2);
    $kd_rek_1 = $data['Kd_Rek_1'];
    $kd_rek_2 = $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'];
    $kd_rek_3 = $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'];
    $kd_rek_4 = $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2);
    $kd_rek_5 = $data['Kd_Rek_1'].'.'.$data['Kd_Rek_2'].'.'.$data['Kd_Rek_3'].'.'.substr('0'.$data['Kd_Rek_4'], -2).'.'.substr('0'.$data['Kd_Rek_5'], -2);

    
    $ysisa = $y;

    $i++; //Untuk urutan nomor
    $pdf->ln();

}

//membuat kotak di halaman terakhir
$y = MAX($y1, $y2, $y3);
$ylst = $y - $yst;  //$y batas marjin bawah dikurangi dengan y pertama
    $pdf->Rect($x, $yst, $w['0'] ,$ylst);
    $pdf->Rect($x+$w['0'], $yst, $w['1'] ,$ylst);
    $pdf->Rect($x+$w['0']+$w['1'], $yst, $w['2'] ,$ylst);
    $pdf->Rect($x+$w['0']+$w['1']+$w['2'], $yst, $w['3'] ,$ylst);
    $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3'], $yst, $w['4'] ,$ylst);
    $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4'], $yst, $w['5'] ,$ylst);
    // $pdf->Rect($x+$w['0']+$w['1']+$w['2']+$w['3']+$w['4']+$w['5'],$yst, $w['6'],$ylst);


//Menampilkan jumlah halaman terakhir
$pdf->SetFont('Arial','B',8);
$pdf->setxy($x,$y);
$pdf->Cell($w['0'],6,'','LB');
$pdf->Cell($w['1'],6,'','B',0,'C');
$pdf->Cell($w['2'],6,'TOTAL','B',0,'C');
$pdf->Cell($w['3'],6,'','B',0,'C');
$pdf->Cell($w['4'],6,number_format($totalusulan, 0, ',', '.'),'BLR',0,'R');
$pdf->Cell($w['5'],6,'','BR',0,'R');
// $pdf->Cell($w['5'],6,'','BR',0,'C');

$pdf->ln();

// Penandatangan 
// $y = $pdf->GetY()+8;
// $pdf->SetXY(130,$y);
// $pdf->SetFont('Arial','',11);
// $pdf->MultiCell(70,6, \app\models\TaTh::dokudoku('bulat', $ref['set_4']).', '.DATE('j', strtotime($model['tgl_sp3b'])).' '.bulan(DATE('m', strtotime($model['tgl_sp3b']))).' '.DATE('Y', strtotime($model['tgl_sp3b'])), '', 'C', 0);

// $pdf->SetXY(130,$pdf->GetY());
// $pdf->SetFont('Arial','',11);
// $pdf->MultiCell(70,6,$model['jbt_penandatangan'], '', 'C', 0);

// $pdf->SetXY(130,$pdf->GetY()+25);
// $pdf->SetFont('Arial','',11);
// $pdf->MultiCell(70,6,$model['penandatangan'], '', 'C', 0);

// $pdf->SetXY(130,$pdf->GetY());
// $pdf->SetFont('Arial','',11);
// $pdf->MultiCell(70,6,'NIP '.$model['nip_penandatangan'], '', 'C', 0);

//Untuk mengakhiri dokumen pdf, dan mengirip dokumen ke output
$pdf->Output();
exit;
?>