<?php
error_reporting(E_ALL);
set_time_limit(180);

include '../src/Cezpdf.php';

$time_start = microtime(true);

$pdf = new Cezpdf('a4');

if(strpos(PHP_OS, 'WIN') !== false)
	Cpdf::$TempPath = 'D:/xampp/tmp';
//Cpdf::$DEBUGLEVEL = Cpdf_Common::DEBUG_OUTPUT;

$pdf->selectFont('Helvetica');
//$pdf->ezText("Test Hello World");

//$pdf->ezText("Next Textblock",0, array('justification'=> 'right'));
//$pdf->ezText("First <c:alink:www.web.de>Textblock2</c:alink>");
//$pdf->ezText("Second <c:alink:www.google.de>google</c:alink>");

$text = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc";

for ($i=0; $i < 100; $i++) { 
	$pdf->ezText($text, 10, array('justification'=>'full'));
}


//$pdf->ezImage('images/test_indexed.png', 5, 0, ''); // 2
//$pdf->ezImage('images/test_alpha2.png', 5, 0, '');

//$pdf->ezText("Next 333333333", 30, array('justification'=> 'right'));

//$pdf->ezImage('images/test_grayscaled.png', 5, 0, ''); // 4

//$pdf->ezText("dfasdfsdf asdf sdf\nadsfsdf asdfasdf\ndasfj <c:alink:www.google.de>skdf</c:alink> kljsdf\n haaafd sd asdf sdfa s df\n dafsdf asdf\nfda daksjfösdf lkajsdhf aslkdfhalskd fhljkafh sdlf jh kljasdfjhas dfkjah laskdjf hlskdjfh asldkfjh asdkfjhs aldkjfhlsaf hasd", 45);
//$pdf->ezImage('images/bg.jpg', 5, 0); // 6

//$pdf->ezText("dfasdfsdf asdf sdf\nTEST TEST TEST", 45);
//$pdf->ezImage('images/test_indexed.png', 5, 0, '');
//$pdf->ezImage('images/test_grayscaled.png', 5, 0, '');

$pdf->ezStream(array('compress'=>0));

$time_end = microtime(true);
$time = $time_end - $time_start;
error_log("$time new");

?>