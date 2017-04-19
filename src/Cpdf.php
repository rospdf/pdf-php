<?php
/**
 * Create pdf documents without additional modules
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see http://www.gnu.org/licenses/
 *
 * @category Documents
 * @package  Cpdf
 * @version  0.13.0 (>=php5)
 * @author   Ole Koeckemann <ole1986@users.sourceforge.net>
 *
 * @copyright 2013 The author(s)
 * @license  GNU General Public License v3
 * @link     http://pdf-php.sf.net
 */

namespace ROSPDF;
// include TTF and TTFsubset classes
require_once 'include/TTFsubset.php';

if(!defined('ROSPDF_SKIP_AUTOLOAD')) {
    spl_autoload_register(function ($class) {
        if(strpos($class,'ROSPDF\\Cpdf') === false) return;
        
        $parts = explode('\\', $class);
        error_log("Loading $class...");
        require_once end($parts) . '.php';
    });
}

if(!defined('ROSPDF_TEMPDIR'))
    define('ROSPDF_TEMPDIR', sys_get_temp_dir());
if(!defined('ROSPDF_TEMPNAM'))
    define('ROSPDF_TEMPNAM', get_current_user());
/**
 * Main PDF class to add object from different classes and mange the output
 *
 * Example usage:
 * <pre>
 * $pdf = new Cpdf(Cpdf::$Layout['A4']);
 * $textObject = $pdf->NewText();
 * $textObject->AddText("Hello World");
 * $textObject->AddText("Hello World",0, 'center');
 * $textObject->AddText("Hello World",0, 'right');
 *
 * $pdf->Stream();
 * </pre>
 */
class Cpdf extends CpdfEntry
{
    const DEBUG_TEXT = 1;
    const DEBUG_BBOX = 2;
    const DEBUG_TABLE = 4;
    const DEBUG_ROWS = 8;
    const DEBUG_MSG_WARN = 16;
    const DEBUG_MSG_ERR = 48; // DEBUG_MSG_WARN IS INCLUDED HERE
    const DEBUG_OUTPUT = 64;
    const DEBUG_ALL = 127;
    
    public $ObjectId = 2;
    public $PDFVersion = 1.3;

    public $EmbedFont = true;
    public $FontSubset = false;

    /**
     * The current page object
     * @var CpdfPage
     */
    public $CURPAGE;
    /**
     * additional options
     * @var CpdfOption
     */
    public $Options;
    /**
     * Meta info
     * @var CpdfMetadata
     */
    public $Metadata;

    /**
     * encryption object
     * @var CpdfEncryption
     */
    public $encryptionObject;
    /**
     * Contains all CpdfPage objects as an array
     * @var Array
     */
    private $pageObjects;
    /**
     * Contains all CpdfFont objects as an array
     * @var Array
     */
    private $fontObjects;
    /**
     * Contains all content and annotation (incl. repeating) references
     * @var Array
     */
    public $contentRefs;

    /**
     * array containing length of all available objects (filled at the very end)
     */
    private $xref;

    /**
     * internal counter for pdf object numbers
     */
    public $objectNum = 2;
    /**
     * internal counter for pages
     */
    public $PageNum = 0;
    /**
     * internal counter for images
     */
    public $ImageNum = 0;

    /**
     * contains all content objects
     */
    protected $contentObjects;

    /**
     * primitive hashtable for images
     */
    private $hashTable;
    /**
     * Debug output level
     *
     * Use the constants Cpdf::DEBUG_* to define the level
     * @default DEBUG_MSG_ERR show errors only
     */
    public static $DEBUGLEVEL = 48;

    /**
     * Force the use of CMYK instead of RGB colors
     */
    public static $ForceCMYK = false;

    /**
     * prefix used for font label
     * @var String
     */
    public static $FontLabel = 'F';

    /**
     * prefix used for pdf image label
     * @var String
     */
    public static $ImageLabel = 'Im';

    /**
     * Target encoding for non-unicode text output
     */
    public static $TargetEncoding = 'CP1252';

    /**
     * timeout when the font cache expires
     */
    public static $CacheTimeout = '30 minutes';

    /**
     * Default timezone
     */
    public static $Locale = 'UTC';

    /**
     * stores the absolute path of the font directory
     */
    public $FontPath;

    /**
     * allowed tags for custom callbacks used in Cpdf
     */
    public $AllowedTags = 'b|strong|i';

    /**
     * FileIdentifier
     * @var String
     */
    public $FileIdentifier = '';

    /**
     * Compression level (default: -1)
     *
     * If set to zero (0) compression is disabled
     */
    public $Compression = -1;

    /**
     * all possible core fonts (case-sensitive)
     */
    public static $CoreFonts = ['Courier', 'Courier-Bold', 'Courier-Oblique', 'Courier-BoldOblique',
                                'Helvetica', 'Helvetica-Bold', 'Helvetica-Oblique', 'Helvetica-BoldOblique',
                                'Times-Roman', 'Times-Bold', 'Times-Italic', 'Times-BoldItalic',
                                'Symbol', 'ZapfDingbats'];

    /**
     * Default font families
     */
    public $DefaultFontFamily = ['helvetica' => ['b'=>'helvetica-bold', 'i'=>'helvetica-oblique', 'bi'=>'helvetica-boldoblique','ib'=>'helvetica-boldoblique'],
                                 'courier' =>   ['b'=>'courier-bold', 'i'=>'courier-oblique', 'bi'=>'courier-boldoblique', 'ib'=>'courier-boldoblique'],
                                 'times-roman' => ['b'=>'times-bold', 'i'=>'times-Italic', 'bi'=>'times-bolditalic', 'ib'=>'times-bolditalic']];

    /**
     * Some Page layouts
     */
    public static $Layout = [   '4A0' => [0,0,4767.87,6740.79],  '2A0' => [0,0,3370.39,4767.87],
                                'A0' => [0,0,2383.94,3370.39], 'A1' => [0,0,1683.78,2383.94],
                                'A2' => [0,0,1190.55,1683.78], 'A3' => [0,0,841.89,1190.55],
                                'A4' => [0,0,595.28,841.89], 'A5' => [0,0,419.53,595.28],
                                'A6' => [0,0,297.64,419.53], 'A7' => [0,0,209.76,297.64],
                                'A8' => [0,0,147.40,209.76], 'A9' => [0,0,104.88,147.40],
                                'A10' => [0,0,73.70,104.88], 'B0' => [0,0,2834.65,4008.19],
                                'B1' => [0,0,2004.09,2834.65], 'B2' => [0,0,1417.32,2004.09],
                                'B3' => [0,0,1000.63,1417.32], 'B4' => [0,0,708.66,1000.63],
                                'B5' => [0,0,498.90,708.66], 'B6' => [0,0,354.33,498.90],
                                'B7' => [0,0,249.45,354.33], 'B8' => [0,0,175.75,249.45],
                                'B9' => [0,0,124.72,175.75], 'B10' => [0,0,87.87,124.72],
                                'C0' => [0,0,2599.37,3676.54], 'C1' => [0,0,1836.85,2599.37],
                                'C2' => [0,0,1298.27,1836.85], 'C3' => [0,0,918.43,1298.27],
                                'C4' => [0,0,649.13,918.43], 'C5' => [0,0,459.21,649.13],
                                'C6' => [0,0,323.15,459.21], 'C7' => [0,0,229.61,323.15],
                                'C8' => [0,0,161.57,229.61], 'C9' => [0,0,113.39,161.57],
                                'C10' => [0,0,79.37,113.39], 'RA0' => [0,0,2437.80,3458.27],
                                'RA1' => [0,0,1729.13,2437.80], 'RA2' => [0,0,1218.90,1729.13],
                                'RA3' => [0,0,864.57,1218.90], 'RA4' => [0,0,609.45,864.57],
                                'SRA0' => [0,0,2551.18,3628.35], 'SRA1' => [0,0,1814.17,2551.18],
                                'SRA2' => [0,0,1275.59,1814.17], 'SRA3' => [0,0,907.09,1275.59],
                                'SRA4' => [0,0,637.80,907.09], 'LETTER' => [0,0,612.00,792.00],
                                'LEGAL' => [0,0,612.00,1008.00], 'EXECUTIVE' => [0,0,521.86,756.00],
                                'FOLIO' => [0,0,612.00,936.00] ];

    /**
     * Initialize the pdf class
     * @param Array $mediabox Bounding box defining the Mediabox
     * @param Array $cropbox Bounding box defining the Cropbox
     * @param Array $bleedbox Bounding box defining the Bleedbox
     */
    public function __construct($mediabox, $cropbox = null, $bleedbox = null)
    {
        // set the default timezone to UTC
        date_default_timezone_set(self::$Locale);

        $this->Options = new CpdfOption($this);

        $this->pageObjects = array();
        $this->fontObjects = array();
        $this->repeatingRefs = array();
        $this->contentRefs = array(
                                'annot' => array(),
                                'content' => array(),
                                'nopage' => array(),
                                'nopageA' => array()
                            );

        $this->xref = array();
        $this->contentObjects = array();
        $this->hashTable = array();

        $this->AddEntry('Type', '/Pages');
        $this->AddResource('ProcSet', '[/PDF/TEXT/ImageB/ImageC/ImageI]');

        $this->Metadata = new CpdfMetadata($this);

        $this->FontPath =  dirname(__FILE__).'/fonts';

        $this->FileIdentifier = md5('ROSPDF'.microtime());

        // if constructor is being executed, create the first page
        $this->NewPage($mediabox, $cropbox, $bleedbox);
    }

    /**
     * create a new page
     * @param array $mediabox layout of the page
     * @param array $cropbox
     * @param array $bleedbox
     */
    public function NewPage($mediabox = null, $cropbox = null, $bleedbox = null)
    {
        if (!isset($mediabox) && is_object($this->CURPAGE)) {
            $mediabox = $this->CURPAGE->Mediabox;
        }

        $this->CURPAGE = new CpdfPage($this, $mediabox, $cropbox, $bleedbox);

        $this->insertPage();

        return $this->CURPAGE;
    }

    private $insertPos = 0;
    private $insertOffset = 0;

    public function InsertMode($pos = 0)
    {
        if ($pos > 0) {
            $this->insertPos = $pos;
        } else {
            $this->insertPos = 0;
        }
    }

    public function IsInsertMode()
    {
        return ($this->insertPos > 0)? true : false;
    }

    private function insertPage()
    {
        $this->PageNum++;
        if ($this->insertPos > 0 && isset($this->pageObjects[$this->insertPos])) {
            $this->CURPAGE->PageNum = $this->insertPos + 1;

            array_splice($this->pageObjects, $this->insertPos, 1, [$this->CURPAGE]);

            for($i = $this->insertPos; $i < count($this->pageObjects); $i++) {
                $this->pageObjects[$i]->PageNum = $i + 1;
            }
            $this->insertPos++;
        } else {
            $this->CURPAGE->PageNum = $this->PageNum;
            $this->pageObjects[] = $this->CURPAGE;
        }
    }

    /**
     * get the page object by passing the page number
     *
     * @return CpdfPage page object or null
     */
    public function GetPageByNo($pageNo)
    {
        $match = array_filter($this->pageObjects, function($p) use($pageNo) { return $p->PageNum === $pageNo; });
        return (!empty($match))?array_pop($match) : null;
        //return (isset($this->pageObjects[$pageNo]))?$this->pageObjects[$pageNo]:null;
    }

    /**
     * create a new font
     * return CpdfFont
     */
    public function NewFont($fontName, $isUnicode)
    {
        $f = strtolower($fontName);
        if (!isset($this->fontObjects[$f])) {
            $font = new CpdfFont($this, $fontName, $this->FontPath, $isUnicode);
            // objectID will be set in output
            $this->fontObjects[$f] = $font;
            $font->FontId = count($this->fontObjects);
            return $font;
        } else {
            return $this->fontObjects[$f];
        }
    }

    /**
     * Create new ANSI or Unicode text
     *
     * TODO: Make use of Encoding parameter and allow defining a "differences" array
     *
     * @param array $bbox Bounding box where the text should be places
     * @param bool $unicode defines if the text input is either ANSI or UNICODE text
     * @param string manuelly set the encoding - used only for ANSI text
     *
     * @return CpdfAppearance return newly created CpdfAppearance object
     */
    public function NewText($bbox = null, $color = array(0,0,0))
    {
        $t = new CpdfAppearance($this, $bbox, $color);

        array_push($this->contentObjects, $t);
        return $t;
    }

    /**
     * Add a new content object
     *
     * Espacially used for RAW input
     *
     * @return CpdfContent
     */
    public function NewContent()
    {
        $c = new CpdfContent($this);
        array_push($this->contentObjects, $c);
        return $c;
    }

    /**
     * Create a new table
     * @return CpdfTable
     */
    public function NewTable($bbox = array(), $columns = 2, $backgroundColor = null, $lineStyle = null, $drawLines = CpdfTable::DRAWLINE_TABLE)
    {
        $t = new CpdfTable($this, $bbox, $columns, $backgroundColor, $lineStyle, $drawLines);
        array_push($this->contentObjects, $t);
        return $t;
    }

    /**
     * Add a new image
     * @param string $source file path
     * @return CpdfImage
     */
    public function NewImage($source)
    {
        if (!isset($this->hashTable[$source])) {
            $i = new CpdfImage($this, $source);
            $i->ImageNum = ++$this->ImageNum;
            array_push($this->contentObjects, $i);
            $this->hashTable[$source] = &$i;
        } else {
            $i = &$this->hashTable[$source];
        }
        return $i;
    }

    /**
     * Add a new appearance
     *
     * TODO: Add polygons and circles into CpdfAppearance class
     * TODO: check bounding box if it is working properly
     *
     * @param array $BBox area where should start and end up
     * @return CpdfAppearance
     */
    public function NewAppearance($BBox = array())
    {
        $g = new CpdfAppearance($this, $BBox);
        //$this->contentObjects[++$this->objectNum] = $g;
        array_push($this->contentObjects, $g);
        return $g;
    }
    /**
     * Add a new Annotation
     *
     * Espacially used for external and internal links
     *
     * TODO: Implement audio and video comments
     * @param string $annoType annotation type - can be either text, freetext or link (later sound, and video will be added)
     * @param array $bbox bounding box where the annotation 'click' is located
     * @param CpdfBorderStyle $border defines the border style
     * @param CpdfColor defines the color
     * @return CpdfAnnotation
     */
    public function NewAnnotation($annoType, $bbox, $border, $color)
    {
        $annot = new CpdfAnnotation($this, $annoType, $bbox, $border, $color);
        //$annot->ObjectId = ++$this->pages->objectNum;

        //$this->contentObjects[++$this->objectNum] = $annot;
        array_push($this->contentObjects, $annot);
        return $annot;
    }

    /**
     * Setup the encryption
     *
     * Encryption up to 128bit is supported (PDF-1.4)
     *
     * @param int $mode set the encryption mode - '1' for 48bit '2' for 128bit
     * @param string user password (appears when PDF Viewer tries to open the PDF)
     * @param string owner password (when user need to change the document)
     * @param array set permission like  'print', 'modify', 'copy', 'add', 'fill', 'extract','assemble' ,'represent'
     */
    public function SetEncryption($mode, $user, $owner, $permission)
    {
        $this->encryptionObject = new CpdfEncryption($this, $mode, $user, $owner, $permission);
    }

    /**
     * INTERNAL PURPOSE - PAGING
     */
    public function addObject(&$contentObject, $before = false)
    {
        if ($before) {
            $c = count($this->contentObjects) - 1;
            $this->contentObjects = array_merge(array_slice($this->contentObjects, 0, $c), array($contentObject), array_slice($this->contentObjects, $c));
        } else {
            array_push($this->contentObjects, $contentObject);
        }
    }

    /**
     * INTERNAL PURPOSE - XREF
     */
    public function AddXRef($id, $length)
    {
        $this->xref[$id] = $length;
    }

    /**
     * Output the header info
     */
    private function outputHeader()
    {
        $res = '%PDF-'.sprintf("%.1F\n%s", $this->PDFVersion, "%\xe2\xe3\xcf\xd3");
        $this->AddXRef(0, strlen($res));
        return $res;
    }
    /**
     * Output the trailer info
     */
    private function outputTrailer()
    {
        $res = "\nxref\n0 ".($this->objectNum + 1);

        $res.="\n0000000000 65535 f \n";
        $pos = 0;
        ksort($this->xref);

        foreach ($this->xref as $k => $l) {
            $pos += $l;
            if ($this->objectNum > $k) {
                $res.=substr('0000000000', 0, 10-strlen($pos+1)).($pos+1)." 00000 n \n";
            }
        }

        $res.= "trailer\n<< /Size ".($this->objectNum + 1)." /Root ".$this->Options->ObjectId." 0 R";

        if (isset($this->Metadata)) {
            $res.= ' /Info '.$this->Metadata->ObjectId.' 0 R';
        }

        if (isset($this->encryptionObject)) {
            $res.= ' /Encrypt '.$this->encryptionObject->ObjectId.' 0 R';
        }
        $res.= ' /ID [<'.$this->FileIdentifier.'><'.$this->FileIdentifier.'>]';
        $res.= " >>";
        $res.="\nstartxref\n".($pos+1)."\n%%EOF\n";
        return $res;
    }

    /**
     * PDF Output of outlines (dummy)
     *
     * TODO: Implement Pdf outlines
     */
    private function outputOutline()
    {
        $res = "\n1 0 obj\n<< /Type /Outlines /Count 0 >>\nendobj";
        $this->AddXRef(1, strlen($res));
        return $res;
    }

    private function outputFonts(){
        $fonts = '';
        $fontrefs = '';
        foreach ($this->fontObjects as $value) {
            $value->ObjectId = ++$this->objectNum;
            $fontrefs .= ' /'.Cpdf::$FontLabel.$value->FontId.' '.$value->ObjectId.' 0 R';
            $fonts.= $value->OutputProgram();
        }

        $this->AddResource('Font', '<<'.$fontrefs.' >>');
        return $fonts;
    }

    private function outputPages(){
        // num of pages
        $pageCount=count($this->pageObjects);
        $pageRefs = '';
        $result = '';
        // -- START assign object ids to all pages
        if ($pageCount > 0) {
            foreach ($this->pageObjects as &$value) {
                $value->ObjectId = ++$this->objectNum;
                $pageRefs.= $value->ObjectId.' 0 R ';

                // content object per page
                $value->Objects = $this->outputPageObjects($value);
                $result.= $value->OutputAsObject();
                $result.= implode('', array_map(function($c){ return $c->OutputAsObject(); }, $value->Objects));
            }
        }

        if (!empty($pageRefs)) {
            $this->AddEntry('Count', $pageCount);
            $this->AddEntry('Kids', '['.$pageRefs.']');
        }

        return $result;
    }
    
    private function outputPageObjects(&$page){
        $filtered = array_filter($this->contentObjects, function($c) use($page){
            return $c->page === $page && (($c instanceof CpdfAppearance) && $c->Length() > 0);
        });

        uasort($filtered, function($a, $b){ return $a->ZIndex < $b->ZIndex ? -1 : 1; });

        foreach($filtered as &$o) {
            $o->ObjectId = ++$this->objectNum;
        }
        return $filtered;
    }

    /**
     * Return everything as a valid PDF string
     *
     * Built up the references for repeating content, when paging is set to either 'all' or 'repeat'
     */
    public function OutputAll()
    {
        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_OUTPUT)) {
            $this->Compression = 0;
        }
        // output the PDF header
        $res = $this->outputHeader();
        // static outlines
        $res.= $this->outputOutline();

        $pages = $this->outputPages();

        // -- START Object output
        // set the object Ids
        //$this->prepareObjects();

        // go thru all object (inclusive objects without any page as parent - like backgrounds)
        //$objects = $this->outputObjects();

        // -- END Object output
        /*$contentObjectLastIndex = count($this->contentObjects) - 1;
        // -- START Page content
        $pages = '';
        */
        $repeatContent = '';

        if (method_exists($this, 'OnPagesCallback')) {
            // should only occurs once
            $this->OnPagesCallback();
        }

        $fonts = $this->outputFonts();

        $tmp = "\n$this->ObjectId 0 obj\n";

        // -- START Resource Header
        // add xobject refs, mostly images into resources
        if (isset($this->contentRefs['pages'])) {
            $imagerefs = '<<';
            foreach ($this->contentRefs['pages'] as $key => $value) {
                $imagerefs.=' /'.Cpdf::$ImageLabel.$value[0]." $key 0 R";
            }
            $imagerefs.= ' >>';
            $this->AddResource('XObject', $imagerefs);
        }

        $tmp.= $this->outputEntries($this->entries);
        // -- END Page Header
        $tmp.= "\nendobj";
        $this->AddXRef($this->ObjectId, strlen($tmp));

        // put PAGES and ALL OBJECTS into result
        $res.= $tmp.$pages.$fonts.$repeatContent;

        if (isset($this->encryptionObject)) {
            $this->encryptionObject->ObjectId = ++$this->objectNum;
            $res.= $this->encryptionObject->OutputAsObject();
        }

        // -- START output catalog
        if (isset($this->Metadata)) {
            $this->Metadata->ObjectId = ++$this->objectNum;
            $res.= $this->Metadata->OutputAsObject();
            if ($this->PDFVersion >= 1.4) {
                // put metadata xml as reference into catalog
                $this->Metadata->ObjectId = ++$this->objectNum;
                $res.= $this->Metadata->OutputAsObject('XML');
                $this->Options->SetMetadata($this->Metadata->ObjectId);
            }
        }
        $this->Options->ObjectId = ++$this->objectNum;

        $res.= $this->Options->OutputAsObject();
        $res.= $this->outputTrailer();
        // -- END output catalog
        return $res;
    }

    /**
     * Stream output the PDF document
     */
    public function Stream($filename = 'output.pdf')
    {
        $tmp = $this->OutputAll();
        $c = "application/pdf";

        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Pragma: no-cache');

        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_OUTPUT)) {
            $c = "text/html";
            $tmp = '<pre>' . $tmp . '</pre>';
        } else {
            header("Content-Length: ".strlen(ltrim($tmp)));
            header("Content-Disposition:inline;filename='$filename'");
        }

        header("Content-Type: $c");

        echo $tmp;
    }
    
    /**
     * unicode version of php ord
     *
     * Used the get the decimal number for an utf-8 character higher then 0x7F (127)
     *
     * @param string $c one character to be converted
     *
     * @return int decimal value of the utf8 character or false on error
     */
    public static function uniord($c)
    {
        // important condition to allow char "0" (zero) being converted to decimal
        if (strlen($c) <= 0) {
            return false;
        }
        $ord0 = ord($c{0});
        if ($ord0>=0   && $ord0<=127) {
            return $ord0;
        }
        $ord1 = ord($c{1});
        if ($ord0>=192 && $ord0<=223) {
            return ($ord0-192)*64 + ($ord1-128);
        }
        $ord2 = ord($c{2});
        if ($ord0>=224 && $ord0<=239) {
            return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
        }
        $ord3 = ord($c{3});
        if ($ord0>=240 && $ord0<=247) {
            return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
        }
        return false;
    }

    /**
     * filter text and convert it into either UTF-16BE or any other non-unicode encoding
     *
     * @param CpdfFont $fontObject object of the current font - as reference
     * @param string $text text string
     * @param bool $convert_encoding boolean value to either convert or not convert the text paramenter
     *
     * @return string converted and parsed text string
     */
    public static function filterText(&$fontObject, $text, $convert_encoding = true)
    {
        if (isset($fontObject) && $convert_encoding) {
            // store all used characters if subset font is set to true
            if ($fontObject->IsUnicode) {
                $text = mb_convert_encoding($text, 'UTF-16BE', 'UTF-8');

                if ($fontObject->SubsetFont) {
                    for ($i = 0; $i < mb_strlen($text, 'UTF-16BE'); $i++) {
                        $fontObject->AddChar(mb_substr($text, $i, 1, 'UTF-16BE'));
                    }
                }
            } else {
                $text = mb_convert_encoding($text, Cpdf::$TargetEncoding, 'UTF-8');
                if ($fontObject->SubsetFont) {
                    for ($i = 0; $i < strlen($text); $i++) {
                        $fontObject->AddChar($text[$i]);
                    }
                }
            }
        }

        $text = strtr($text, array(')' => '\\)', '(' => '\\(', '\\' => '\\\\', chr(8) => '\\b', chr(9) => '\\t', chr(10) => '\\n', chr(12) => '\\f' ,chr(13) => '\\r', '&lt;'=>'<', '&gt;'=>'>', '&amp;'=>'&'));
        return $text;
    }

    /**
     * Clone an object (used for pages breaks)
     */
    public static function DoClone($object)
    {
        if (version_compare(phpversion(), '5.0') < 0) {
            return $object;
        } else {
            return @clone($object);
        }
    }

    /**
     * Helper to bitwise check enums
     *
     * @param int $value value to be checked for enum
     * @param int $enum bitwise enum
     */
    public static function IsDefined($value, $enum)
    {
        return (($value & $enum) == $enum)?true:false;
    }

    /**
     * Setup the Bounding Box
     *
     * Use either a full qualified rectangle stored as an array with 4 elements
     * or used the following array keys:
     * 'lx' - for lower X pos of the rectangle
     * 'ux' - upper X pos of the rectangle
     * 'ly' - lower Y pos of the rectangle
     * 'uy' - upper Y pos of the rectangle
     *
     * Additionally all keys can have the 'add*' prefix allowing to add or subtract the current Bounding Box
     *
     * Example:
     * <pre>
     * Cpdf::SetBBox( array('adduy'=> - 40, 'lx' => 50), $pdf->MediaBox);
     * </pre>
     */
    public static function SetBBox($bbox, &$current)
    {
        if (is_array($bbox)) {
            // set the lower X position either via key 'lx' or index 0
            if (isset($bbox['lx'])) {
                $current[0] = $bbox['lx'];
            } elseif (isset($bbox[0])) {
                $current[0] = $bbox[0];
            }
            // set the lower Y position either via key 'ly' or index 1
            if (isset($bbox['ly'])) {
                $current[1] = $bbox['ly'];
            } elseif (isset($bbox[0])) {
                $current[1] = $bbox[1];
            }
            // set the upper X position either via key 'ux' or index 2
            if (isset($bbox['ux'])) {
                $current[2] = $bbox['ux'];
            } elseif (isset($bbox[0])) {
                $current[2] = $bbox[2];
            }
            // set the upper Y position either via key 'uy' or index 3
            if (isset($bbox['uy'])) {
                $current[3] = $bbox['uy'];
            } elseif (isset($bbox[0])) {
                $current[3] = $bbox[3];
            }

            // use array keys "add*" to add or substract (negative) values
            if (isset($bbox['addlx'])) {
                $current[0] += $bbox['addlx'];
            }
            if (isset($bbox['addly'])) {
                $current[1] += $bbox['addly'];
            }
            if (isset($bbox['addux'])) {
                $current[2] += $bbox['addux'];
            }
            if (isset($bbox['adduy'])) {
                $current[3] += $bbox['adduy'];
            }

            return $current;
        }
    }
    
    /**
     * Output debug messages
     *
     * @param String $msg the message
     * @param Int $flag One of the DEBUG_* flags
     * @param Integer $debugflags WHAT DEBUG MESSAGES ARE BEING PRINTED
     */
    public static function DEBUG($msg, $flag, $debugflags)
    {
        if (self::IsDefined($debugflags, $flag)) {
            switch ($flag) {
                default:
                case Cpdf::DEBUG_MSG_ERR:
                    error_log("[ROSPDF-ERROR] ".$msg);
                    break;
                case Cpdf::DEBUG_MSG_WARN:
                    error_log("[ROSPDF-WARNING] ".$msg);
                    break;
                case Cpdf::DEBUG_OUTPUT:
                    error_log("[ROSPDF-OUTPUTINFO] ".$msg);
                    break;
            }
        }
    }
}


