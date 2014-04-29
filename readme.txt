--- CURRENT RELEASE CANDIDATE version 0.12-rc16

 Use SVN to checkout from 
 svn://svn.code.sf.net/p/pdf-php/code/trunk
 
 Manual and change log can be found here:
 http://pdf-php.sf.net/pdf-php-code/readme.php 
 
 Example usage:
 
 <?php
	include 'Cezpdf.php';
	// initialize a ROS PDF class object using DIN-A4, with background color gray
	$pdf = new Cezpdf('a4','portrait','color',array(0.8,0.8,0.8));
	// set pdf Bleedbox
	$pdf->ezSetMargins(20,20,20,20);
	//use one of the pdf core fonts
	$mainFont = 'Times-Roman';
	// select the font
	$pdf->selectFont($mainFont);
	// define the font size
	$size=12;
	// modified to use the local file if it can
	$pdf->openHere('Fit');

	// Output some colored text by using text directives and justify it to the right of the document
	$pdf->ezText("PDF with some <c:color:1,0,0>blue</c:color> <c:color:0,1,0>red</c:color> and <c:color:0,0,1>green</c:color> colours", $size, array('justification'=>'right'));
	// output the pdf as stream, but uncompress
	$pdf->ezStream(array('compress'=>0));
 ?>
 

--- EXPERIMENTAL

 ROS PDF OBJECT ORIENTED CLASS - version 0.13.0
 This ROS-OO PDF class is completely  reprogrammed
 (except some image related methods) to fully support php5 object orientation
 
 Some Examples (01-11) can be found here:
 http://pdf-php.sourceforge.net/pdf-php-experimental/examples/