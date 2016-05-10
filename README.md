# ROS PHP Pdf creation class
<sup>**Version 0.12-rc12** | Author: ole1986 | License: GNU Lesser General Public License (LGPLv3) </sup>

![ros.jpg](https://raw.githubusercontent.com/ole1986/pdf-php/master/ros.jpg "R&OS PHP Pdf creation class")

This is the offical GIT clone from the R&OS PHP Pdf class previously stored on https://sourceforge.net/projects/pdf-php/

The R&OS Pdf class is used to generate PDF Documents using PHP without installing any additional modules or extensions
It comes with a base class called "Cpdf.php" plus a helper class "Cezpdf.php" to generate tables, add backgrounds and provide paging

<p align="center"> <a href="https://github.com/ole1986/pdf-php/blob/master/readme.pdf">DOCUMENTATION</a> : <a href="https://github.com/ole1986/pdf-php/archive/master.zip">DOWNLOAD</a></p>

### Features
- Quick and easy to use
- Support for extension classes
- Unicode and ANSI formated text
- Custom TTF fonts and font subsetting (version >= 0.11.8)
- Auto page and line breaks
- Text alignments (left, right, center, justified)
- Linked XObjects
- Internal and external links
- Compression by using gzcompress
- Encryption 40bit, 128bit since PDF 1.4
- Image support for JPEG, PNG and GIF (partly)
- Template support

### Installation

Copy the `src` folder into your project directory and include the Cezpdf.php using php `include 'src\Cezpdf.php'`

### Example

```php
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
```
