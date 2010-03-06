<?
function code39($text,$barcodethinwidth=2,$barcodeheight=40,$xpos=0,$ypos=0)
{
    $barcodethickwidth=$barcodethinwidth*3;
    $codingmap  =  Array( "0"=> "000110100", "1"=> "100100001", 
        "2"=> "001100001", "3"=> "101100000", "4"=> "000110001", 
        "5"=> "100110000", "6"=> "001110000", "7"=> "000100101", 
        "8"=> "100100100", "9"=> "001100100", "A"=> "100001001", 
        "B"=> "001001001", "C"=> "101001000", "D"=> "000011001", 
        "E"=> "100011000", "F"=> "001011000", "G"=> "000001101", 
        "H"=> "100001100", "I"=> "001001100", "J"=> "000011100", 
        "K"=> "100000011", "L"=> "001000011", "M"=> "101000010", 
        "N"=> "000010011", "O"=> "100010010", "P"=> "001010010",     
        "Q"=> "000000111", "R"=> "100000110", "S"=> "001000110", 
        "T"=> "000010110", "U"=> "110000001", "V"=> "011000001", 
        "W"=> "111000000", "X"=> "010010001", "Y"=> "110010000", 
        "Z"=> "011010000", " "=> "011000100", "$"=> "010101000", 
        "%"=> "000101010", "*"=> "010010100", "+"=> "010001010", 
        "-"=> "010000101", "."=> "110000100", "/"=> "010100010"); 
	$text  =  strtoupper($text);
	$text  =  "*$text*";  //  add  start/stop  chars. 
	$textlen  =  strlen($text); 
	$barcodewidth  =  ($textlen)*(7*$barcodethinwidth +  3*$barcodethickwidth)-$barcodethinwidth;
	for  ($idx=0;$idx<$textlen;$idx++)  { 
        $char  =  substr($text,$idx,1); 
        //  make  unknown  chars  a  '-'; 
        if  (!isset($codingmap[$char]))  $char  =  "-"; 
        for  ($baridx=0;$baridx<=8;$baridx++)  { 
            $elementwidth  =  (substr($codingmap[$char],$baridx,1))  ? 
                                                    $barcodethickwidth  
:  $barcodethinwidth; 
            if  (($baridx+1)%2)  $rectangle[] = array('x'=>$xpos,'y'=>$ypos,'b'=>$elementwidth,'h'=>$barcodeheight);
            $xpos+=$elementwidth; 
        } 
        $xpos+=$barcodethinwidth; 
    } 
    return $rectangle;
}

?>