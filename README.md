# ROS PHP Pdf creation class

[![Latest Stable Version](https://poser.pugx.org/rospdf/pdf-php/v/stable)](https://packagist.org/packages/rospdf/pdf-php) [![Total Downloads](https://poser.pugx.org/rospdf/pdf-php/downloads)](https://packagist.org/packages/rospdf/pdf-php) [![Latest Unstable Version](https://poser.pugx.org/rospdf/pdf-php/v/unstable)](https://packagist.org/packages/rospdf/pdf-php) [![License](https://poser.pugx.org/rospdf/pdf-php/license)](https://packagist.org/packages/rospdf/pdf-php) [![Build Status](https://travis-ci.org/rospdf/pdf-php.svg?branch=master)](https://travis-ci.org/rospdf/pdf-php) 

![ros.jpg](https://raw.githubusercontent.com/rospdf/pdf-php/master/ros.jpg "R&OS PHP Pdf creation class")

This is the offical GIT clone from the R&OS PHP Pdf class previously stored on [sourceforge.net/projects/pdf-php](https://sourceforge.net/projects/pdf-php/). Development will take place here now.

The R&OS Pdf class is used to generate PDF Documents using PHP without installing any additional modules or extensions
It comes with a base class called "Cpdf.php" plus a helper class "Cezpdf.php" to generate tables, add backgrounds and provide paging.

<p align="center"> <a href="https://github.com/rospdf/pdf-php/blob/master/readme.pdf">DOCUMENTATION</a> : <a href="https://github.com/rospdf/pdf-php/archive/master.zip">DOWNLOAD</a></p>

## Features
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

## Installation

### Installation via composer

To leverage an automatic autoloader, you can install through `composer`. Simply add a dependency on ´rospdf/pdf-php´ to your projects `composer.json`.

```
{
    "require": {
        "rospdf/pdf-php": "0.12.*"
    }
}
```

When managing your dependencies through `composer`, you can leverage the autoloader placed in the `vendor` directory.

```php5
require_once 'vendor/autoload.php';
```

For a system-wide installation via Composer, you can run:

    composer global require "rospdf/pdf-php=0.12.*"

### Clone via git

You can also use git to install it using:

    git clone https://github.com/rospdf/pdf-php.git
    git checkout <tag name>

### Manual installation

Copy the `src` folder into your project directory and include the Cezpdf.php using php `include 'src\Cezpdf.php'`

### Example

```php5
<?php
include 'src\Cezpdf.php'; // Or using the autoloader from vendor/autoload.php if installed through composer
// Initialize a ROS PDF class object using DIN-A4, with background color gray
$pdf = new Cezpdf('a4','portrait','color',array(0.8,0.8,0.8));
// Set pdf Bleedbox
$pdf->ezSetMargins(20,20,20,20);
// Use one of the pdf core fonts
$mainFont = 'Times-Roman';
// Select the font
$pdf->selectFont($mainFont);
// Define the font size
$size=12;
// Modified to use the local file if it can
$pdf->openHere('Fit');

// Output some colored text by using text directives and justify it to the right of the document
$pdf->ezText("PDF with some <c:color:1,0,0>blue</c:color> <c:color:0,1,0>red</c:color> and <c:color:0,0,1>green</c:color> colours", $size, array('justification'=>'right'));
// Output the pdf as stream, but uncompress
$pdf->ezStream(array('compress'=>0));
?>
```

## Contributors

[ole1986](http://github.com/ole1986) is lead developer. See the full list of [contributors](https://github.com/rospdf/pdf-php/graphs/contributors).
