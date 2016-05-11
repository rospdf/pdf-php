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

// include TTF and TTFsubset classes
include_once 'include/TTFsubset.php';

/**
 * Common class containing static methods and properties incl. debug level, font path, temporary path, etc...
 *
 * To display all DEBUG information, simple set
 * <pre>
 * Cpdf_Common::$DEBUGLEVEL = Cpdf::DEBUG_ALL
 * </pre>
 *
 * To only show Bounding boxes (including table) used within the pdf output use:
 * <pre>
 * Cpdf_Common::$DEBUGLEVEL = Cpdf::DEBUG_BBOX | Cpdf::DEBUG_TABLE
 * </pre>
 */
class Cpdf_Common
{
    const DEBUG_TEXT = 1;
    const DEBUG_BBOX = 2;
    const DEBUG_TABLE = 4;
    const DEBUG_ROWS = 8;
    const DEBUG_MSG_WARN = 16;
    const DEBUG_MSG_ERR = 48; // DEBUG_MSG_WARN IS INCLUDED HERE
    const DEBUG_OUTPUT = 64;
    const DEBUG_ALL = 127;

    /**
     * Debug output level
     *
     * Use the constants Cpdf_Common::DEBUG_* to define the level
     * @default DEBUG_MSG_ERR show errors only
     */
    public static $DEBUGLEVEL = 48;

    /**
     * Force the use of CMYK instead of RGB colors
     */
    public static $ForceCMYK = false;

    /**
     * temporary path for font cache and images
     * @var String
     */
    public static $TempPath = '/tmp';

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
                case Cpdf_Common::DEBUG_MSG_ERR:
                    error_log("[ROSPDF-ERROR] ".$msg);
                    break;
                case Cpdf_Common::DEBUG_MSG_WARN:
                    error_log("[ROSPDF-WARNING] ".$msg);
                    break;
                case Cpdf_Common::DEBUG_OUTPUT:
                    error_log("[ROSPDF-OUTPUTINFO] ".$msg);
                    break;
            }
        }
    }
    /**
     * stores the absolute path of the font directory
     */
    public $FontPath;

    /**
     * allowed tags for custom callbacks used in Cpdf_Writing
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
    public static $CoreFonts = array('Courier', 'Courier-Bold', 'Courier-Oblique', 'Courier-BoldOblique',
        'Helvetica', 'Helvetica-Bold', 'Helvetica-Oblique', 'Helvetica-BoldOblique',
        'Times-Roman', 'Times-Bold', 'Times-Italic', 'Times-BoldItalic',
        'Symbol', 'ZapfDingbats');

    /**
     * Default font families
     */
    public $DefaultFontFamily = array(
            'helvetica' => array(
                    'b'=>'helvetica-bold',
                    'i'=>'helvetica-oblique',
                    'bi'=>'helvetica-boldoblique',
                    'ib'=>'helvetica-boldoblique',
                ),
            'courier' => array(
                    'b'=>'courier-bold',
                    'i'=>'courier-oblique',
                    'bi'=>'courier-boldoblique',
                    'ib'=>'courier-boldoblique',
                ),
            'times-roman' => array(
                    'b'=>'times-bold',
                    'i'=>'times-Italic',
                    'bi'=>'times-bolditalic',
                    'ib'=>'times-bolditalic',
                )
    );

    /**
     * Some Page layouts
     */
    public static $Layout = array(
        '4A0' => array(0,0,4767.87,6740.79),  '2A0' => array(0,0,3370.39,4767.87),
        'A0' => array(0,0,2383.94,3370.39), 'A1' => array(0,0,1683.78,2383.94),
        'A2' => array(0,0,1190.55,1683.78), 'A3' => array(0,0,841.89,1190.55),
        'A4' => array(0,0,595.28,841.89), 'A5' => array(0,0,419.53,595.28),
        'A6' => array(0,0,297.64,419.53), 'A7' => array(0,0,209.76,297.64),
        'A8' => array(0,0,147.40,209.76), 'A9' => array(0,0,104.88,147.40),
        'A10' => array(0,0,73.70,104.88), 'B0' => array(0,0,2834.65,4008.19),
        'B1' => array(0,0,2004.09,2834.65), 'B2' => array(0,0,1417.32,2004.09),
        'B3' => array(0,0,1000.63,1417.32), 'B4' => array(0,0,708.66,1000.63),
        'B5' => array(0,0,498.90,708.66), 'B6' => array(0,0,354.33,498.90),
        'B7' => array(0,0,249.45,354.33), 'B8' => array(0,0,175.75,249.45),
        'B9' => array(0,0,124.72,175.75), 'B10' => array(0,0,87.87,124.72),
        'C0' => array(0,0,2599.37,3676.54), 'C1' => array(0,0,1836.85,2599.37),
        'C2' => array(0,0,1298.27,1836.85), 'C3' => array(0,0,918.43,1298.27),
        'C4' => array(0,0,649.13,918.43), 'C5' => array(0,0,459.21,649.13),
        'C6' => array(0,0,323.15,459.21), 'C7' => array(0,0,229.61,323.15),
        'C8' => array(0,0,161.57,229.61), 'C9' => array(0,0,113.39,161.57),
        'C10' => array(0,0,79.37,113.39), 'RA0' => array(0,0,2437.80,3458.27),
        'RA1' => array(0,0,1729.13,2437.80), 'RA2' => array(0,0,1218.90,1729.13),
        'RA3' => array(0,0,864.57,1218.90), 'RA4' => array(0,0,609.45,864.57),
        'SRA0' => array(0,0,2551.18,3628.35), 'SRA1' => array(0,0,1814.17,2551.18),
        'SRA2' => array(0,0,1275.59,1814.17), 'SRA3' => array(0,0,907.09,1275.59),
        'SRA4' => array(0,0,637.80,907.09), 'LETTER' => array(0,0,612.00,792.00),
        'LEGAL' => array(0,0,612.00,1008.00), 'EXECUTIVE' => array(0,0,521.86,756.00),
        'FOLIO' => array(0,0,612.00,936.00)
        );

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
     * @param Cpdf_Font $fontObject object of the current font - as reference
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
                $text = mb_convert_encoding($text, Cpdf_Common::$TargetEncoding, 'UTF-8');
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
     * Sort order for content references to verify which object has the highest ZIndex
     * and should be on focus
     */
    public function compareRefs($a, $b)
    {
        if (isset($a[1]) && !isset($b[1])) {
            return 1;
        } elseif (!isset($a[1]) && isset($b[1])) {
            return -1;
        }

        if (isset($a[1]) && isset($b[1])) {
            return ($a[1] < $b[1]) ? -1 : 1;
        }
        return 0;
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
}

/**
 * Encryption support for PDF up to version 1.4
 *
 * TODO: Extend the encryption for PDF 1.4 to use a user defined key length up to 128bit
 */
class Cpdf_Encryption
{
    public $ObjectId;

    /**
     * internal encryption mode (1 - 40bit, 2 = upto 128bit)
     */
    private $encryptionMode;
    /**
     * default padding string for PDF encryption
     */
    private $encryptionPad;
    /**
     * internal encryption key
     */
    private $encryptionKey;
    /**
     * internal permission set
     */
    private $permissionSet;
    /**
     * current Cpdf class object
     */
    private $pages;
    /**
     * user password
     */
    private $userPass;
    /**
     * owner password
     */
    private $ownerPass;

    /**
     * Constructor to enable PDF encryption
     *
     * More details, see Cpdf->SetEncryption(args)
     */
    public function __construct(&$pages, $mode, $user = '', $owner = '', $permission = null)
    {
        $this->pages = &$pages;
        $this->userPass = $user;
        $this->ownerPass = $owner;
        $this->encryptionPad = chr(0x28).chr(0xBF).chr(0x4E).chr(0x5E).chr(0x4E).chr(0x75).chr(0x8A).chr(0x41).chr(0x64).chr(0x00).chr(0x4E).chr(0x56).chr(0xFF).chr(0xFA).chr(0x01).chr(0x08).chr(0x2E).chr(0x2E).chr(0x00).chr(0xB6).chr(0xD0).chr(0x68).chr(0x3E).chr(0x80).chr(0x2F).chr(0x0C).chr(0xA9).chr(0xFE).chr(0x64).chr(0x53).chr(0x69).chr(0x7A);

        if ($mode > 1) {
            // increase the pdf version to support 128bit encryption
            if ($pages->PDFVersion < 1.4) {
                $pages->PDFVersion = 1.4;
            }
            $p=bindec('01111111111111111111000011000000'); // revision 3 is using bit 3 - 6 AND 9 - 12
        } else {
            $mode = 1; // make sure at least the 40bit encryption is set
            $p=bindec('01111111111111111111111111000000'); // while revision 2 is using bit 3 - 6 only
        }

        $options = ['print'=>4,'modify'=>8,'copy'=>16,'add'=>32,'fill'=>256,'extract'=>512,'assemble'=>1024,'represent'=>2048];

        if (is_array($permission)) {
            foreach ($permission as $k => $v) {
                if ($v && isset($options[$k])) {
                    $p+=$options[$k];
                } elseif (isset($options[$v])) {
                    $p+=$options[$v];
                }
            }
        }

        $this->permissionSet = $p;
        // set the encryption mode to either RC4 40bit or RC4 128bit
        $this->encryptionMode = $mode;

        if (strlen($this->ownerPass)==0) {
            $this->ownerPass=$this->userPass;
        }

        $this->init();
    }

    /**
     * internal method to initialize the encryption
     */
    private function init()
    {
        // Pad or truncate the owner password
        $this->ownerPass = substr($this->ownerPass.$this->encryptionPad, 0, 32);
        $this->userPass = substr($this->userPass.$this->encryptionPad, 0, 32);

        // convert permission set into binary string
        $permissions = sprintf("%c%c%c%c", ($this->permissionSet & 255), (($this->permissionSet >> 8) & 255), (($this->permissionSet >> 16) & 255), (($this->permissionSet >> 24) & 255));

        $this->ownerPass = $this->encryptOwner();
        $this->userPass = $this->encryptUser($permissions);
    }

    /**
     * encryption algorithm 3.4
     */
    private function encryptUser($permissions)
    {
        $keylength = 5;
        if ($this->encryptionMode > 1) {
            $keylength = 16;
        }
        // make hash with user, encrypted owner, permission set and fileIdentifier
        $hash = $this->md5_16($this->userPass.$this->ownerPass.$permissions.$this->hexToStr($this->pages->FileIdentifier));

        // loop thru the hash process when it is revision 3 of encryption routine (usually RC4 128bit)
        if ($this->encryptionMode > 1) {
            for ($i = 0; $i < 50; ++$i) {
                $hash = $this->md5_16(substr($hash, 0, $keylength)); // use only length of encryption key from the previous hash
            }
        }

        $this->encryptionKey = substr($hash, 0, $keylength); // PDF 1.4 - Create the encryption key (IMPORTANT: need to check Length)

        if ($this->encryptionMode > 1) { // if it is the RC4 128bit encryption
            // make a md5 hash from padding string (hardcoded by Adobe) and the fileIdenfier
            $userHash = $this->md5_16($this->encryptionPad.$this->hexToStr($this->pages->FileIdentifier));

            // encrypt the hash from the previous method by using the encryptionKey
            $this->ARC4_init($this->encryptionKey);
            $uvalue=$this->ARC4($userHash);

            $len = strlen($this->encryptionKey);
            for ($i = 1; $i<=19; ++$i) {
                $ek = '';
                for ($j=0; $j< $len; $j++) {
                    $ek .= chr(ord($this->encryptionKey[$j]) ^ $i);
                }
                $this->ARC4_init($ek);
                $uvalue = $this->ARC4($uvalue);
            }
            $uvalue .= substr($this->encryptionPad, 0, 16);
        } else { // if it is the RC4 40bit encryption
            $this->ARC4_init($this->encryptionKey);
            $uvalue=$this->ARC4($this->encryptionPad);
        }
        return $uvalue;
    }

    /**
     * encryption algorithm 3.3
     */
    private function encryptOwner()
    {
        $keylength = 5;
        if ($this->encryptionMode > 1) {
            $keylength = 16;
        }

        $ownerHash = $this->md5_16($this->ownerPass); // PDF 1.4 - repeat this 50 times in revision 3
        if ($this->encryptionMode > 1) { // if it is the RC4 128bit encryption
            for ($i = 0; $i < 50; $i++) {
                $ownerHash = $this->md5_16($ownerHash);
            }
        }

        $ownerKey = substr($ownerHash, 0, $keylength); // PDF 1.4 - Create the encryption key (IMPORTANT: need to check Length)

        $this->ARC4_init($ownerKey); // 5 bytes of the encryption key (hashed 50 times)
        $ovalue=$this->ARC4($this->userPass); // PDF 1.4 - Encrypt the padded user password using RC4

        if ($this->encryptionMode > 1) {
            $len = strlen($ownerKey);
            for ($i = 1; $i<=19; ++$i) {
                $ek = '';
                for ($j=0; $j < $len; $j++) {
                    $ek .= chr(ord($ownerKey[$j]) ^ $i);
                }
                $this->ARC4_init($ek);
                $ovalue = $this->ARC4($ovalue);
            }
        }
        return $ovalue;
    }

    /**
     * initialize the ARC4 encryption
     * @access private
     */
    private function ARC4_init($key = '')
    {
        $this->arc4 = '';
        // setup the control array
        if (strlen($key)==0) {
            return;
        }
        $k = '';
        while (strlen($k)<256) {
            $k.=$key;
        }
        $k=substr($k, 0, 256);
        for ($i=0; $i<256; $i++) {
            $this->arc4 .= chr($i);
        }
        $j=0;
        for ($i=0; $i<256; $i++) {
            $t = $this->arc4[$i];
            $j = ($j + ord($t) + ord($k[$i]))%256;
            $this->arc4[$i]=$this->arc4[$j];
            $this->arc4[$j]=$t;
        }
    }

    /**
     * initialize the encryption for processing a particular object
     * @access private
     */
    public function encryptInit($id)
    {
        $tmp = $this->encryptionKey;
        $hex = dechex($id);
        if (strlen($hex)<6) {
            $hex = substr('000000', 0, 6-strlen($hex)).$hex;
        }
        $tmp.= chr(hexdec(substr($hex, 4, 2))).chr(hexdec(substr($hex, 2, 2))).chr(hexdec(substr($hex, 0, 2))).chr(0).chr(0);
        $key = $this->md5_16($tmp);
        if ($this->encryptionMode > 1) {
            $this->ARC4_init(substr($key, 0, 16)); // use max 16 bytes for RC4 128bit encryption key
        } else {
            $this->ARC4_init(substr($key, 0, 10)); // use (n + 5 bytes) for RC4 40bit encryption key
        }
    }

    /**
     * calculate the 16 byte version of the 128 bit md5 digest of the string
     * @access private
     */
    private function md5_16($string)
    {
        $tmp = md5($string);
        $out = pack("H*", $tmp);
        return $out;
    }

    /**
     * internal method to convert string to hexstring (used for owner and user dictionary)
     * @param $string - any string value
     * @access protected
     */
    protected function strToHex($string)
    {
        $hex = '';
        for ($i=0; $i < strlen($string); $i++) {
            $hex .= sprintf("%02x", ord($string[$i]));
        }
        return $hex;
    }

    protected function hexToStr($hex)
    {
        $str = '';
        for ($i=0; $i<strlen($hex); $i+=2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }
        return $str;
    }

    public function ARC4($text)
    {
        $len=strlen($text);
        $a=0;
        $b=0;
        $c = $this->arc4;
        $out='';
        for ($i=0; $i<$len; $i++) {
            $a = ($a+1)%256;
            $t= $c[$a];
            $b = ($b+ord($t))%256;
            $c[$a]=$c[$b];
            $c[$b]=$t;
            $k = ord($c[(ord($c[$a])+ord($c[$b]))%256]);
            $out.=chr(ord($text[$i]) ^ $k);
        }
        return $out;
    }

    public function OutputAsObject()
    {
        $res = "\n".$this->ObjectId." 0 obj\n<<";
        $res.=' /Filter /Standard';
        if ($this->encryptionMode > 1) { // RC4 128bit encryption
            $res.= ' /V 2 /R 3 /Length 128';
        } else {
            $res.= ' /V 1 /R 2';
        }
        $res.= ' /O <'.$this->strToHex($this->ownerPass).'>';
        $res.= ' /U <'.$this->strToHex($this->userPass).'>';
        $res.= ' /P '.$this->permissionSet;
        $res.= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));
        return $res;
    }
}

/**
 * Font program class object
 * - TTF  in ANSI or UNICODE
 * - AFM fonts
 *
 * TODO: support for opentype fonts
 * TODO: AFM/PFB font embedding needs to be implemented
 */
class Cpdf_Font
{
    public $ObjectId;

    public $FontId;

    private $binaryId;
    private $descendantId;
    private $unicodeId;
    private $descriptorId;
    private $cidmapId;
    private $fontpath;

    /**
     * Main Cpdf class
     * @var Cpdf
     */
    private $pages;

    private $subsets;
    private $prefix;

    private $cidWidths;
    private $firstChar;
    private $lastChar;

    private $props;

    /**
     * the font file without extension
     * @var string
     */
    public $fontFile;
    /**
     * To verify of this is a coreFont program
     * @var bool
     */
    public $isCoreFont;
    /**
     * To verify if the is a unicode font program
     * @var bool
     */
    public $IsUnicode;

    /**
     * Used to determine if font program is embeded
     * @var bool
     */
    public $EmbedFont;
    /**
     * Used to determine if its a font subset
     * @var bool
     */
    public $SubsetFont;

    public function __construct(&$pages, $fontfile, $path, $isUnicode = false)
    {
        $this->pages = &$pages;
        $this->differences = array();
        $this->subsets = array();
        $this->IsUnicode = $isUnicode;

        $this->SubsetFont = $this->pages->FontSubset;
        $this->EmbedFont = $this->pages->EmbedFont;
        $this->props = array();

        $this->prefix = $this->randomSubset();

        $fontfile = strtolower($fontfile);

        if ($p=strrpos($fontfile, '.')) {
            $ext = substr($fontfile, $p);
            // file name gets a proper extension below
            $fontFile = substr($fontfile, 0, $p);
        }
        // check if fontfile is one of the coreFonts
        $found = preg_grep("/^".$fontfile."$/i", Cpdf_Common::$CoreFonts);
        if (count($found) > 0) {
            // use font name fron CoreFont array as they are case sensitive
            $this->fontFile = end($found);
            $this->isCoreFont = true;
            $ext = 'afm';
        } elseif (empty($ext)) { // otherwise use ttf by default
            $this->fontFile = $fontfile;
            $this->isCoreFont = false;
            $ext = 'ttf';
        }

        if (file_exists($path.'/'.$fontfile.'.'.$ext)) {
            $this->fontpath = $path.'/'.$fontfile.'.'.$ext;
            $this->loadFont();
        } else {
            Cpdf_Common::DEBUG("Font program '$path/$fontfile.$ext' not found", Cpdf_Common::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            die;
        }
    }

    /**
     * generate a random string as font subset prefix
     */
    private function randomSubset()
    {
        $length = 6;
        // can also have more then A-F, but should be enough
        $characters = 'ABCDEF';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString.'+';
    }

    /**
     * add chars to an array which is used for font subsetting
     */
    public function AddChar($char)
    {
        $this->subsets[$char] = true;
    }

    /**
     * initial method to read and load (via OutputProgram) the font program
     */
    private function loadFont()
    {
        $cachedFile = 'cache.'.$this->fontFile.'.php';

        // use the temp folder to read/write cached font data
        if (file_exists(Cpdf_Common::$TempPath.'/'.$cachedFile) && filemtime(Cpdf_Common::$TempPath.'/'.$cachedFile) > strtotime('-'.Cpdf_Common::$CacheTimeout)) {
            if (empty($this->props)) {
                $this->props = require(Cpdf_Common::$TempPath.'/'.$cachedFile);
            }

            if (isset($this->props['_version_']) && $this->props['_version_'] == 4) {
                // USE THE CACHED FILE and exit here
                $this->IsUnicode = $this->props['isUnicode'];
                return;
            }
        }
        // read ttf font properties via TTF class
        if ($this->isCoreFont == false && class_exists('TTF')) {
            // The selected font is a TTF font (any other is not yet supported)
            $this->readTTF($this->fontpath);
        } elseif ($this->isCoreFont == true) {
            // The selected font is a core font. So use the afm file to read the properties
            $this->readAFM($this->fontpath);
        } else {
            // ERROR: No alternative found to read ttf fonts
        }

        $this->props['_version_'] = 4;
        $fp = fopen(Cpdf_Common::$TempPath.'/'.$cachedFile, 'w'); // use the temp folder to write cached font data
        fwrite($fp, '<?php /* R&OS php pdf class font cache file */ return '.var_export($this->props, true).'; ?>');
        fclose($fp);
    }

    /**
     * Include only such glyphs into the PDF document which are really in use
     */
    private function subsetProgram()
    {
        if (class_exists('TTFsubset')) {
            $t = new TTFsubset();
            // combine all used characters as string
            $s = implode('', array_keys($this->subsets));

            // submit the string to TTFsubset class to return the subset (as binary)
            // $data is the new (subset) of the font font
            $data = $t->doSubset($this->fontpath, $s, null);
            // load the widths into $this->cidWidths
            $this->loadWidths($t->TTFchars);

            return $data;
        }
    }

    /**
     * Fully embed the ttf font into PDF
     */
    private function fullProgram()
    {
        $data = @file_get_contents($this->fontpath);

        // load the widths into $this->cidWidths
        $this->loadWidths();

        return $data;
    }

    /**
     * load the charachter widhts into $this->cidWidths[<int>] = width
     */
    private function loadWidths(&$TTFSubsetChars = null)
    {
        // START - adding cid widths
        $this->firstChar = 0;
        $this->lastChar = 0;

        $this->cidWidths = array();

        $widths = array();
        $cid_widths = array();

        if (!isset($TTFSubsetChars)) {
            // if it is not a TTF subset object then use the cached characters generated via loadFont
            foreach ($this->props['C'] as $num => $d) {
                if (intval($num) > 0 || $num == '0') {
                    $this->cidWidths[$num] = $d;
                    $this->lastChar = $num;
                }
            }
        } else {
            // but if TTFSubset object is set only load the widths which are being used
            foreach ($TTFSubsetChars as $TTFchar) {
                if (isset($TTFchar->charCode)) {
                    $this->cidWidths[$TTFchar->charCode] = (isset($this->props['C'][$TTFchar->charCode]))?$this->props['C'][$TTFchar->charCode]:700;
                }
            }
        }
    }

    /**
     * read the AFM (also core fonts are stored as .AFM) to calculate character width, height, descender and the FontBBox
     *
     * @param string $fontpath - path of then *.afm font file
     */
    private function readAFM($fontpath)
    {
        // AFM is always ANSI - no chance for unicode
        $this->IsUnicode = false;
        $this->props['isUnicode'] = $this->IsUnicode;

        $file = file($fontpath);
        foreach ($file as $row) {
            $row=trim($row);
            $pos=strpos($row, ' ');
            if ($pos) {
                // then there must be some keyword
                $key = substr($row, 0, $pos);
                switch ($key) {
                    case 'FontName':
                    case 'FullName':
                    case 'FamilyName':
                    case 'Weight':
                    case 'ItalicAngle':
                    case 'IsFixedPitch':
                    case 'CharacterSet':
                    case 'UnderlinePosition':
                    case 'UnderlineThickness':
                    case 'Version':
                    case 'EncodingScheme':
                    case 'CapHeight':
                    case 'XHeight':
                    case 'Ascender':
                    case 'Descender':
                    case 'StdHW':
                    case 'StdVW':
                    case 'StartCharMetrics':
                        $this->props[$key]=trim(substr($row, $pos));
                        break;
                    case 'FontBBox':
                        $this->props[$key]=explode(' ', trim(substr($row, $pos)));
                        break;
                    case 'C':
                        // C 39 ; WX 222 ; N quoteright ; B 53 463 157 718 ;
                        // use preg_match instead to improve performace
                        // IMPORTANT: if "L i fi ; L l fl ;" is required preg_match must be amended
                        $r = preg_match('/C (-?\d+) ; WX (-?\d+) ; N (\w+) ; B (-?\d+) (-?\d+) (-?\d+) (-?\d+) ;/', $row, $m);
                        if ($r == 1) {
                            //$dtmp = array('C'=> $m[1],'WX'=> $m[2], 'N' => $m[3], 'B' => array($m[4], $m[5], $m[6], $m[7]));
                            $c = (int)$m[1];
                            $n = $m[3];
                            $width = floatval($m[2]);

                            if ($c >= 0) {
                                if ($c != hexdec($n)) {
                                    $this->props['codeToName'][$c] = $n;
                                }
                                $this->props['C'][$c] = $width;
                                $this->props['C'][$n] = $width;
                            } else {
                                $this->props['C'][$n] = $width;
                            }

                            if (!isset($this->props['MissingWidth']) && $c == -1 && $n === '.notdef') {
                                  $this->props['MissingWidth'] = $width;
                            }
                        }
                        break;
                }
            }
        }
    }

    /**
     * read the TTF font (can also contain unicode glyphs) to calculate widths, height and FontBBox
     * The TTF.php class from Thanos Efraimidis (4real.gr) is used to read the TTF binary natively
     *
     * @param string $fontpath - path of the *.ttf font file
     */
    private function readTTF($fontpath)
    {
        // set unicode to all TTF fonts by default
        $this->IsUnicode = true;

        $ttf = new TTF(file_get_contents($fontpath));

        $head = $ttf->unmarshalHead();
        $uname = $ttf->unmarshalName();
        $hhea = $ttf->unmarshalHhea();
        $post = $ttf->unmarshalPost(true);
        $maxp = $ttf->unmarshalMAXP();
        $cmap = $ttf->unmarshalCmap();

        $this->props = array(
            'isUnicode' => $this->IsUnicode,
            'ItalicAngle' => $post['italicAngle'],
            'UnderlineThickness' => $post['underlineThickness'],
            'UnderlinePosition' => $post['underlinePosition'],
            'IsFixedPitch' => ($post['isFixedPitch'] == 0)? false : true,
            'Ascender' => $hhea['ascender'],
            'Descender' => $hhea['descender'],
            'LineGap' => $hhea['lineGap'],
            'FontName' => $uname['nameRecords'][2]['value'],
            'FamilyName' => $uname['nameRecords'][1]['value']
        );

        // calculate the bounding box properly by using 'units per em' property
        $this->props['FontBBox'] = array(
                                    intval($head['xMin'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['yMin'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['xMax'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['yMax'] / ($head['unitsPerEm'] / 1000))
                                );
        $this->props['UnitsPerEm'] = $head['unitsPerEm'];

        $encodingTable = array();

        $hmetrics = $ttf->unmarshalHmtx($hhea['numberOfHMetrics'], $maxp['numGlyphs']);

        // get format 6 or format 4 as primary cmap table map glyph with character
        foreach ($cmap['tables'] as $v) {
            if (isset($v['format']) && $v['format'] == "4") {
                $encodingTable = $v;
                break;
            }
        }

        if ($encodingTable['format'] == '4') {
            $glyphsIndices = range(1, $maxp['numGlyphs']);
            $charToGlyph = array();

            $segCount = $encodingTable['segCount'];
            $endCountArray = $encodingTable['endCountArray'];
            $startCountArray = $encodingTable['startCountArray'];
            $idDeltaArray = $encodingTable['idDeltaArray'];
            $idRangeOffsetArray = $encodingTable['idRangeOffsetArray'];
            $glyphIdArray = $encodingTable['glyphIdArray'];

            for ($seg = 0; $seg < $segCount; $seg++) {
                $endCount = $endCountArray[$seg];
                $startCount = $startCountArray[$seg];
                $idDelta = $idDeltaArray[$seg];
                $idRangeOffset = $idRangeOffsetArray[$seg];
                for ($charCode = $startCount; $charCode <= $endCount; $charCode++) {
                    if ($idRangeOffset != 0) {
                        $j = $charCode - $startCount + $seg + $idRangeOffset / 2 - $segCount;
                        $gid0 = $glyphIdArray[$j];
                    } else {
                        $gid0 = $idDelta + $charCode;
                    }
                    $gid0 %= 65536;
                    if (in_array($gid0, $glyphsIndices)) {
                        $charToGlyph[sprintf("%d", $charCode)] = $gid0;
                    }
                }
            }

            $cidtogid = str_pad('', 256*256*2, "\x00");

            $this->props['C'] = array();
            foreach ($charToGlyph as $char => $glyphIndex) {
                $m = TTF::getHMetrics($hmetrics, $hhea['numberOfHMetrics'], $glyphIndex);

                // calculate the correct char width by dividing it with 'units per em'
                $this->props['C'][$char] = intval($m[0] / ($head['unitsPerEm'] / 1000));

                // TODO: check if this mapping also works for non-unicode TTF fonts
                if ($char >= 0 && $char < 0xFFFF && $glyphIndex) {
                    $cidtogid[$char*2] = chr($glyphIndex >> 8);
                    $cidtogid[$char*2 + 1] = chr($glyphIndex & 0xFF);
                }
            }
        } else {
            Cpdf_Common::DEBUG('Font file does not contain format 4 cmap', Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }

        $this->props['CIDtoGID'] = base64_encode($cidtogid);
    }

    public function GetFontName()
    {
        if (!isset($this->props['FontName'])) {
            Cpdf_Common::DEBUG('No font name found for {$this->fontFile}', Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
            return;
        }
        return $this->props['FontName'];
    }
    public function GetFontFamily()
    {
        if (!isset($this->props['FamilyName'])) {
            Cpdf_Common::DEBUG('No font family found for {$this->fontFile}', Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
            return;
        }
        return $this->props['FamilyName'];
    }

    /**
     * calculate the font height by using the FontBBox
     *
     * @param float $fontSize - fontsize in points
     */
    public function getFontHeight($fontSize)
    {
        $h = $this->props['FontBBox'][3] - $this->props['FontBBox'][1];

        $unitsPerEm = 1000;
        if (isset($this->props['UnitsPerEm'])) {
            $unitsPerEm = $this->props['UnitsPerEm'];
        }

        return $fontSize*$h / $unitsPerEm;
    }

    /**
     * read the font descender from font properties
     *
     * @param float $fontSize - fontsize in points
     */
    public function getFontDescender($fontSize)
    {
        $h = $this->props['Descender'];

        $unitsPerEm = 1000;
        if (isset($this->props['UnitsPerEm'])) {
            $unitsPerEm = $this->props['UnitsPerEm'];
        }

        return $fontSize*$h / $unitsPerEm;
    }

    /**
     * get the characters width
     * @param int $cid character id. Example: 32 for space char
     */
    public function GetCharWidth($cid)
    {
        if (isset($this->props['C']) && isset($this->props['C'][$cid])) {
            return $this->props['C'][$cid];
        }
        return false;
    }

    /**
     * get the text length of a string and cut it if necessary it does not fit to $maxWidth
     *
     * TODO: check if the length is calculated correctly when angle and word alignment is used
     *
     * Example of the returned array:
     * array(532, 0, 124, 1) - the text length is greater $maxWidth on position 124; its a 'normal' line break where a space was found
     * array(554, 0, 128, 0) - the text length is greate $maxWidth on position 128; force line break (no space found)
     *
     * @param float $size font size
     * @param string $text text string to be calculated
     * @param float $maxWidth max width of the text string (if zero - no calculation required)
     * @param float $angle angle of the text string
     * @param float $wa word align
     *
     * @return array An array with four elements containing the width, the height offset, line break position and the offset
     */
    public function getTextLength($size, $text, $maxWidth = 0, $angle = 0, $wa = 0)
    {
        if ($maxWidth == 0) {
            return;
        }

        // possible white spaces allowing line breaks in HEX
        $spaces = array();
        // U+0020 ansi sapce          U+1680 ogham space mark
        // U+2000 en quad             U+2001 em quad            U+2002 en space
        // U+2003 em space            U+2004 three-per-em space U+2005 four-per-em space
        // U+2006 six-per-em space    U+2007 figure space       U+2008 punctuation space
        // U+2009 thin space
        $spaces = array_merge($spaces, array( 0x20, 0x1680 ), range(0x2000, 0x2009));

        $a = deg2rad((float)$angle);
        // get length of its unicode string
        $len=mb_strlen($text, 'UTF-8');

        $tw = $maxWidth/$size*1000;
        $break=0;
        $offset = 0;
        $w=0;

        for ($i=0; $i< $len; $i++) {
            $c = mb_substr($text, $i, 1, 'UTF-8');

            $cOrd = Cpdf_Common::uniord($c);
            if ($cOrd == 0) {
                continue;
            }

            if (isset($this->differences[$cOrd])) {
                // then this character is being replaced by another
                $cOrd2 = $this->differences[$cOrd];
            } else {
                $cOrd2 = $cOrd;
            }

            if (isset($this->props['C'][$cOrd2])) {
                $w+=$this->props['C'][$cOrd2];
            }

            if ($cOrd2 == 45) {
                $break=$i + 1;
                $offset = 0;
                // TODO: set the default width if not char width is found
                $breakWidth = $w*$size/1000;
            } elseif (in_array($cOrd2, $spaces)) {
                $break=$i;
                $correction = (isset($this->props['C'][$cOrd2])?$this->props['C'][$cOrd2]:0);
                $offset = 1;
                // word spacing
                $w += ($wa > 0)?$wa:0;
                $breakWidth = ($w - $correction)*$size/1000;
            }

            if ($maxWidth > 0 && (cos($a)*$w) > $tw && $break > 0) {
                return array(cos($a)*$breakWidth, -sin($a)*$breakWidth, $break, $offset);
            }
        }

        $tmpw=$w*$size/1000;
        return array(cos($a)*$tmpw, -sin($a)*$tmpw, -1, 0);
    }

    /**
     * return the the font descriptor output (indirect object reference)
     */
    private function outputDescriptor()
    {
        $this->descriptorId = ++$this->pages->objectNum;

        $res = "\n$this->descriptorId 0 obj\n";
        $res.= "<< /Type /FontDescriptor /Flags 32 /StemV 70";

        if ($this->SubsetFont && $this->EmbedFont && $this->IsUnicode) {
            $res.= '/FontName /'.$this->prefix.$this->fontFile;
        } else {
            $res.= '/FontName /'.$this->fontFile;
        }

        $res.= " /Ascent ".$this->props['Ascender'].' /Descent '.$this->props['Descender'];

        $bbox = &$this->props['FontBBox'];
        $res.= " /FontBBox [".$bbox[0].' '.$bbox[1].' '.$bbox[2].' '.$bbox[3].']';

        $res.= ' /ItalicAngle '.$this->props['ItalicAngle'];
        $res.= ' /MaxWidth '.$bbox[2];
        $res.= ' /MissingWidth 600';

        if ($this->EmbedFont) {
            $res.= ' /FontFile2 '.$this->binaryId.' 0 R';
        }

        $res.= " >>\nendobj";

        $this->pages->AddXRef($this->descriptorId, strlen($res));
        return $res;
    }

    /**
     * return the font descendant output (indirect object reference)
     */
    private function outputDescendant()
    {
        $this->descendantId = ++$this->pages->objectNum;

        $res = "\n$this->descendantId 0 obj\n";
        $res.="<< /Type /Font /Subtype /CIDFontType2";
        if ($this->SubsetFont) {
            $res.= ' /BaseFont /'.$this->prefix.$this->fontFile;
        } else {
            $res.= ' /BaseFont /'.$this->fontFile;
        }

        $res.=" /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >>";

        $res.=" /FontDescriptor $this->descriptorId 0 R";
        $res.=" /CIDToGIDMap $this->cidmapId 0 R";

        $res.=" /W [";
            reset($this->cidWidths);
            $opened = false;
        while (list($k,$v) = each($this->cidWidths)) {
            list($nextk, $nextv) = each($this->cidWidths);
            //echo "\n$k ($v) == $nextk ($nextv)";
            if (($k + 1) == $nextk) {
                if (!$opened) {
                    $res.= " $k [$v";
                    $opened = true;
                } elseif ($opened) {
                    $res.= ' '.$v;
                }
                prev($this->cidWidths);
            } else {
                if ($opened) {
                    $res.=" $v]";
                } else {
                    $res.= " $k [$v]";
                }

                $opened = false;
                prev($this->cidWidths);
            }
        }

        if (isset($nextk) && isset($nextv)) {
            if ($opened) {
                $res.= "]";
            }
            $res.= " $nextk [$nextv]";
        }
        /*
			  foreach ($this->cidWidths as $k => $v) {
				    $res.= "$k [$v] ";
			  }
        */
        $res.= ' ]';
        $res.= " >>";
        $res.="\nendobj";

        $this->pages->AddXRef($this->descendantId, strlen($res));

        return $res;
    }

    /**
     * return the ToUnicode output (indirect object reference)
     */
    private function outputUnicode()
    {
        $this->unicodeId = ++$this->pages->objectNum;

        $res = "\n$this->unicodeId 0 obj\n";

        $stream = "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n/CIDSystemInfo <</Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n1 beginbfrange\n<0000> <FFFF> <0000>\nendbfrange\nendcmap\nCMapName currentdict /CMap defineresource pop\nend\nend\n";

        $res.= '<< /Length '.strlen($stream)." >>\n";
        $res.= "stream\n".$stream."\nendstream";
        $res.= "\nendobj";

        $this->pages->AddXRef($this->unicodeId, strlen($res));
        return $res;
    }

    /**
     * return the CID mapping output (as an indirect object reference)
     */
    private function outputCIDMap()
    {
        $this->cidmapId = ++$this->pages->objectNum;

        $res = "\n$this->cidmapId 0 obj";
        $res.= "\n<<";

        $stream = base64_decode($this->props['CIDtoGID']);
        // compress the CIDMap if compression is enabled
        if ($this->pages->Compression <> 0) {
            $stream = gzcompress($stream, $this->pages->Compression);
            $res.= ' /Filter /FlateDecode';
        }

        $res.= ' /Length '.strlen($stream).' >>';

        $res.= "\nstream\n".$stream."\nendstream";
        $res.= "\nendobj";

        $this->pages->AddXRef($this->cidmapId, strlen($res));

        return $res;
    }

    /**
     * return the binary output, either as font subset or the complete font file
     */
    private function outputBinary()
    {
        $this->binaryId = ++$this->pages->objectNum;
        // allow font subbsetting only for unicode
        if ($this->SubsetFont && $this->IsUnicode) {
            $data = $this->subsetProgram();
        } else {
            $data = $this->fullProgram();
        }

        $l = strlen($data);
        $res = "\n$this->binaryId 0 obj\n<<";

        // compress the binary font program if compression is enabled
        if ($this->pages->Compression <> 0) {
            $data = gzcompress($data, $this->pages->Compression);
            $res.= ' /Filter /FlateDecode';
        }

        // make sure the compressed length is declared
        $l1 = strlen($data);

        $res.= "/Length1 $l /Length $l1 >>\nstream\n".$data."\nendstream\nendobj";

        $this->pages->AddXRef($this->binaryId, strlen($res));
        return $res;
    }

    /**
     * Output the font program
     */
    public function OutputProgram()
    {
        $res = "\n".$this->ObjectId." 0 obj";
        $res.= "\n<< /Type /Font /Subtype";

        $data = '';
        $unicode = '';
        $cidMap = '';
        $descr = '';
        $descendant = '';

        if ($this->isCoreFont) {
             // core fonts (plus additionals?!)
            $res.= ' /Type1 /BaseFont /'.$this->fontFile;
            //$res.= " /Encoding /".$this->props['EncodingScheme'];
            $res.= " /Encoding /WinAnsiEncoding";
        } else {
            $data = $this->outputBinary();

            $unicode = $this->outputUnicode();
            $cidMap = $this->outputCIDMap();

            $descr = $this->outputDescriptor();
            $descendant = $this->outputDescendant();

            // for Unicode fonts some additional info is required
            $res.= ' /Type0 /BaseFont';
            if ($this->SubsetFont) {
                $fontname = $this->prefix.$this->fontFile;
            } else {
                $fontname = $this->fontFile;
            }

             $res.=" /$fontname";
             $res.=" /Name /".Cpdf_Common::$FontLabel.$this->FontId;
             $res.= " /Encoding /Identity-H";
             $res.= " /DescendantFonts [$this->descendantId 0 R]";

             $res.= " /ToUnicode $this->unicodeId 0 R";
        }

        $res.= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res.$data.$unicode.$cidMap.$descr.$descendant;
    }
}

/**
 * Main PDF class to add object from different classes and mange the output
 *
 * Example usage:
 * <pre>
 * $pdf = new Cpdf(Cpdf_Common::$Layout['A4']);
 * $textObject = $pdf->NewText();
 * $textObject->AddText("Hello World");
 * $textObject->AddText("Hello World",0, 'center');
 * $textObject->AddText("Hello World",0, 'right');
 *
 * $pdf->Stream();
 * </pre>
 */
class Cpdf extends Cpdf_Common
{
    public $ObjectId = 2;
    public $PDFVersion = 1.3;

    public $EmbedFont = true;
    public $FontSubset = false;

    /**
     * The current page object
     * @var Cpdf_Page
     */
    public $CURPAGE;
    /**
     * additional options
     * @var CpdF_Option
     */
    public $Options;
    /**
     * Meta info
     * @var Cpdf_Metadata
     */
    public $Metadata;

    /**
     * encryption object
     * @var Cpdf_Encryption
     */
    public $encryptionObject;
    /**
     * Contains all Cpdf_Page objects as an array
     * @var Array
     */
    private $pageObjects;
    /**
     * Contains all Cpdf_Font objects as an array
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
     * pdf resources for all pages
     */
    protected $resources;

    /**
     * Initialize the pdf class
     * @param Array $mediabox Bounding box defining the Mediabox
     * @param Array $cropbox Bounding box defining the Cropbox
     * @param Array $bleedbox Bounding box defining the Bleedbox
     */
    public function __construct($mediabox, $cropbox = null, $bleedbox = null)
    {

        $this->Options = new Cpdf_Option($this);

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
        $this->resources = array('ProcSet'=>'[/PDF/TEXT/ImageB/ImageC/ImageI]');

        $this->Metadata = new Cpdf_Metadata($this);

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

        $this->CURPAGE = new Cpdf_Page($this, $mediabox, $cropbox, $bleedbox);

        $this->insertPage();

        return $this->CURPAGE;
    }

    private $insertPos = 0;
    private $pageOffset = array();

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
            if (!isset($this->pageOffset[$this->insertPos])) {
                $this->pageOffset[$this->insertPos] = 0;
            }

            $inserted = $this->insertPos  + $this->pageOffset[$this->insertPos];
            $i = $this->PageNum;
            while ($i > $inserted) {
                $i--;
                $tmp = &$this->pageObjects[$i];
                $tmp->PageNum = $i + 1;
                $this->pageObjects[$i+1] = $tmp;
            }

            // set the correct page position for its new page
            $this->CURPAGE->PageNum = $this->insertPos;
            // add the newly created page to page objects array
            $this->pageObjects[$inserted] = $this->CURPAGE;

            $this->pageOffset[$this->insertPos] += 1;
        } else {
            $this->CURPAGE->PageNum = $this->PageNum;
            $this->pageObjects[$this->PageNum] = $this->CURPAGE;
        }
    }

    public function PageOffset($fromPage = 0)
    {
        if (empty($fromPage)) {
            $fromPage = $this->PageNum;
        }

        $offset = 0;

        while ($fromPage-- > 0) {
            if (isset($this->pageOffset[$fromPage])) {
                $offset += $this->pageOffset[$fromPage];
            }
        }

        return $offset;
    }

    /**
     * get the page object by passing the page number
     *
     * @return Cpdf_Page page object or null
     */
    public function GetPageByNo($pageNo)
    {
        return (isset($this->pageObjects[$pageNo]))?$this->pageObjects[$pageNo]:null;
    }

    /**
     * create a new font
     * return Cpdf_Font
     */
    public function NewFont($fontName, $isUnicode)
    {
        $f = strtolower($fontName);
        if (!isset($this->fontObjects[$f])) {
            $font = new Cpdf_Font($this, $fontName, $this->FontPath, $isUnicode);
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
     * @return Cpdf_Appearance return newly created Cpdf_Appearance object
     */
    public function NewText($bbox = null, $color = array(0,0,0))
    {
        $t = new Cpdf_Appearance($this, $bbox, $color);

        array_push($this->contentObjects, $t);
        return $t;
    }

    /**
     * Add a new content object
     *
     * Espacially used for RAW input
     *
     * @return Cpdf_Content
     */
    public function NewContent()
    {
        $c = new Cpdf_Content($this);
        array_push($this->contentObjects, $c);
        return $c;
    }

    /**
     * Create a new table
     * @return Cpdf_Table
     */
    public function NewTable($bbox = array(), $columns = 2, $backgroundColor = null, $lineStyle = null, $drawLines = Cpdf_Table::DRAWLINE_TABLE)
    {
        $t = new Cpdf_Table($this, $bbox, $columns, $backgroundColor, $lineStyle, $drawLines);
        array_push($this->contentObjects, $t);
        return $t;
    }

    /**
     * Add a new image
     * @param string $source file path
     * @return Cpdf_Image
     */
    public function NewImage($source)
    {
        if (!isset($this->hashTable[$source])) {
            $i = new Cpdf_Image($this, $source);
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
     * TODO: Add polygons and circles into Cpdf_Appearance class
     * TODO: check bounding box if it is working properly
     *
     * @param array $BBox area where should start and end up
     * @param resources name the resources being used in Cpdf_Appearances
     * @return Cpdf_Appearance
     */
    public function NewAppearance($BBox = array(), $ressources = '')
    {
        $g = new Cpdf_Appearance($this, $BBox, $ressources);
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
     * @param Cpdf_BorderStyle $border defines the border style
     * @param Cpdf_Color defines the color
     * @return Cpdf_Annotation
     */
    public function NewAnnotation($annoType, $bbox, $border, $color)
    {
        $annot = new Cpdf_Annotation($this, $annoType, $bbox, $border, $color);
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
        $this->encryptionObject = new Cpdf_Encryption($this, $mode, $user, $owner, $permission);
    }

    protected function AddResource($key, $value)
    {
        $this->resources[$key] = $value;
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
    /**
     * PDF Output of all objects inherited by Cpdf_Content
     *
     * goes thru all content objects and return its result as string.
     * Add the content references into contentRefs to display it on the appropriate page
     */
    private function outputObjects()
    {
        $res = '';
        if (is_array($this->contentObjects) && count($this->contentObjects) > 0) {
            Cpdf::DEBUG("List of all Objects: ", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);
            foreach ($this->contentObjects as $k => &$value) {
                $l = $value->Length();
                Cpdf::DEBUG("$k => ".get_class($value) . " | Name: ".$value->Name." | Length: ".$l, Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

                // IGNORE OBJECTS WITH NO CONTENT (Length test) - Cpdf_Image and Cpdf_Annotation are skipped
                if (($l == 0 && !$value->HasEntries()) && get_class($value) != 'Cpdf_Image' && get_class($value) != 'Cpdf_Annotation') {
                    continue;
                }
                // content with Paging eq to 'none' or NULL it will be ignored
                if (!isset($value->Paging)) {
                    continue;
                }
                if ($value->Paging == Cpdf_Content::PMODE_NONE) {
                    continue;
                }

                // set the unique PDF objects Id for every content stored in contentObjects
                $value->ObjectId = ++$this->objectNum;

                if (method_exists($this, 'OnCallbackObject')) {
                    call_user_func(array($this, 'OnCallbackObject'), $value);
                }

                // does the content contain a page?
                if (isset($value->page)) {
                    $class_name = get_class($value);

                    if ($value->Paging == Cpdf_Content::PMODE_REPEAT) {
                        $this->objectNum--;
                        $this->contentRefs['nopage'][$value->ObjectId] = array(Cpdf_Content::PMODE_REPEAT, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId, $k);
                        continue;
                    } elseif ($value->Paging == Cpdf_Content::PMODE_ALL) {
                        if ($class_name == 'Cpdf_Annotation') {
                            $this->contentRefs['nopageA'][$value->ObjectId] = array($value->Paging, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
                        } else {
                            $this->contentRefs['nopage'][$value->ObjectId] = array($value->Paging, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
                        }
                    } elseif ($class_name == 'Cpdf_Image') {
                        $this->contentRefs['pages'][$value->ObjectId] = array($value->ImageNum);
                    } elseif ($class_name == 'Cpdf_Annotation') {
                        $this->contentRefs['annot'][$value->page->ObjectId][$value->ObjectId] = array($value->Paging, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
                    } else {
                        switch ($value->Paging) {
                            default:
                            case Cpdf_Content::PMODE_ADD:
                                $this->contentRefs['content'][$value->page->ObjectId][$value->ObjectId] = array($value->Paging, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
                                break;
                            case Cpdf_Content::PMODE_ALL_FROM_HERE:
                                for ($i=$value->page->PageNum; $i <= $this->PageNum; $i++) {
                                    $page = &$this->GetPageByNo($i);
                                    $this->contentRefs['content'][$page->ObjectId][$value->ObjectId] = array(Cpdf_Content::PMODE_ADD, (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
                                }
                                break;
                        }
                    }
                    if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_OUTPUT)) {
                        $res.= "\n% contentObject: $k - Class $class_name";
                    }
                    $res.= $value->OutputAsObject();
                } else {
                    // objects with NO PAGE as parent
                    $res.= $value->OutputAsObject();
                    $this->contentRefs['nopage'][$value->ObjectId] = array('nopage', -1);
                }

                if (isset($value->Name)) {
                    $bbox = $value->GetBBox();
                    $this->Options->AddName($value->Name, $value->page->ObjectId, $bbox[3]);
                }
            }
        }
        return $res;
    }

    /**
     * Return everything as a valid PDF string
     *
     * Built up the references for repeating content, when paging is set to either 'all' or 'repeat'
     */
    public function OutputAll()
    {
        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_OUTPUT)) {
            $this->Compression = 0;
        }

        $res = $this->outputHeader();

        // num of pages
        $pageCount=count($this->pageObjects);
        $pageRefs = '';
        // -- START assign object ids to all pages
        if ($pageCount > 0) {
            foreach ($this->pageObjects as $value) {
                $value->ObjectId = ++$this->objectNum;
                $pageRefs.= $value->ObjectId.' 0 R ';
            }
        }
        // -- END

        // static outlines
        $res.= $this->outputOutline();

        // -- START Font output
        $fonts = '';
        $fontrefs = '';
        foreach ($this->fontObjects as $value) {
            $value->ObjectId = ++$this->objectNum;
            $fontrefs .= ' /'.Cpdf_Common::$FontLabel.$value->FontId.' '.$value->ObjectId.' 0 R';
            $fonts.= $value->OutputProgram();
        }
        // -- END Font output

        // -- START go thru all object (inclusive objects without any page as parent - like backgrounds)
        $objects = $this->outputObjects();
        // -- END
        $contentObjectLastIndex = count($this->contentObjects) - 1;
        // -- START Page content
        $pages = '';
        $repeatContent = '';
        if ($pageCount > 0) {
            foreach ($this->pageObjects as &$value) {
                if (!empty($value->Name)) {
                    $this->Options->AddName($value->Name, $value->ObjectId);
                }
                // callback function for each page object
                if (method_exists($this, 'OnCallbackPage')) {
                    call_user_func(array($this, 'OnCallbackPage'), $value);
                }
                // output the page header here
                foreach ($this->contentRefs['nopage'] as $objectId => $mode) {
                    if ($mode[0] == Cpdf_Content::PMODE_REPEAT) {
                        $o = $this->contentObjects[$mode[2]];
                        $o->ClearEntries();

                        $o->ObjectId = ++$this->objectNum;
                        $o->page = $value;
                        $repeatContent.= $o->OutputAsObject();
                        for ($i = $contentObjectLastIndex + 1; $i < count($this->contentObjects); $i++) {
                            $co = &$this->contentObjects[$i];
                            $class_name = get_class($co);

                            $co->ObjectId = ++$this->objectNum;
                            $repeatContent.= $co->OutputAsObject();
                            $contentObjectLastIndex++;

                            if ($class_name == 'Cpdf_Annotation') {
                                $this->contentRefs['annot'][$value->ObjectId] [$co->ObjectId] = array(Cpdf_Content::PMODE_ADD, $o->ObjectId);
                            } else {
                                $this->contentRefs['content'][$value->ObjectId] [$co->ObjectId] = array(Cpdf_Content::PMODE_ADD, $o->ObjectId);
                            }
                        }
                        $this->contentRefs['content'][$value->ObjectId][$o->ObjectId] = array(Cpdf_Content::PMODE_ADD, $o->ObjectId);
                    }
                }
                $pages.= $value->OutputAsObject();
            }
        }
        // -- END

        $tmp = "\n$this->ObjectId 0 obj\n";
        $tmp.= "<< /Type /Pages";

        // -- START Resource Header
        // according to pdf ref we can put all procsets by default

        // add font refs into resource
        if (!empty($fontrefs)) {
            $this->AddResource('Font', '<<'.$fontrefs.' >>');
        }
        // add xobject refs, mostly images into resources
        if (isset($this->contentRefs['pages'])) {
            $imagerefs = '<<';
            foreach ($this->contentRefs['pages'] as $key => $value) {
                $imagerefs.=' /'.Cpdf_Common::$ImageLabel.$value[0]." $key 0 R";
            }
            $imagerefs.= ' >>';
            $this->AddResource('XObject', $imagerefs);
        }

        $tmp.= ' /Resources <<';
        foreach ($this->resources as $k => $v) {
            $tmp.= " /$k $v";
        }
        $tmp.= ' >>';
        // -- END Resource Header

        // -- START Page Header
        if (!empty($pageRefs)) {
            $tmp.= ' /Count '.$pageCount.' /Kids ['.$pageRefs.']';
        }
        // -- END Page Header
        $tmp.= " >>\nendobj";
        $this->AddXRef($this->ObjectId, strlen($tmp));

        // put PAGES and ALL OBJECTS into result
        $res.= $tmp.$pages.$fonts.$objects.$repeatContent;

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
        // -- END output catalog
        return $res.$this->outputTrailer();
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

        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_OUTPUT)) {
            $c = "text/html";
            $tmp = '<pre>' . $tmp . '</pre>';
        } else {
            header("Content-Length: ".strlen(ltrim($tmp)));
            header("Content-Disposition:inline;filename='$filename'");
        }

        header("Content-Type: $c");

        echo $tmp;
    }
}

/**
 * Extension class allowing the use of callbacks and directives.
 * The following methods are being used for callbacks
 * - DoCall
 * - DoTrigger
 * - Callback
 *
 * By default this class provides paging, internal and external links, backgrounds and colored text.
 * Text directives, like strong and italic are dependent on the Font family set defined in Cpdf_Common::$DefaultFontFamily
 */
class Cpdf_Extension extends Cpdf
{
    private $callbackPageMode;

    private $callbackStack;
    private $callbackFunc;

    /**
     * Used to register default callback functions by using RegisterCallbackFunc
     */
    public function __construct($mediabox, $cropbox = null, $bleedbox = null)
    {
        parent::__construct($mediabox, $cropbox, $bleedbox);

        $this->callbackFunc = array();
        $this->callbackStack = array();

        $this->RegisterCallbackFunc('i', '');
        $this->RegisterCallbackFunc('b', '');
        $this->RegisterCallbackFunc('strong', '');

        $this->RegisterCallbackFunc('pager', 'pager');

        $this->RegisterCallbackFunc('pager', 'pager');
        $this->RegisterCallbackFunc('alink', 'alink:?.*?', 'appearance');
        $this->RegisterCallbackFunc('ilink', 'ilink:?.*?', 'appearance');
        $this->RegisterCallbackFunc('background', 'background', 'appearance');
        $this->RegisterCallbackFunc('color', 'color:?.*?');

    }

    /**
     * register a callback function to use it in any text directive
     * @param String $funcName name of the function to be called
     */
    public function RegisterCallbackFunc($funcName, $regEx)
    {
        $this->callbackFunc[$funcName] = array();

        if (!empty($regEx)) {
            $this->AllowedTags.= '|'.$regEx;
        }

        $params = func_get_args();
        array_shift($params);
        array_shift($params);

        $this->callbackFunc[$funcName] = array();

        foreach ($params as $value) {
            switch (strtolower($value)) {
                case 'appearance':
                    $app = $this->NewAppearance();
                    $app->IsCallback = true;
                    $this->callbackFunc[$funcName][strtolower($value)] = &$app;
                    break;
            }
        }
    }

    /**
     * initial the call to define the start point of the Bounding box plus additional parameters
     * @param Cpdf_Writing $sender The sender class object
     * @param String $funcName function name to be called
     * @param Array $BBox First part of the Bounding Box containing lower X and lower Y coordinates
     * @param mixed $param optional parameters
     */
    public function DoCall(&$sender, $funcName, $BBox, $param)
    {
        if (!isset($this->callbackFunc[$funcName])) {
            Cpdf_Common::DEBUG("Callback function '$funcName' not registered", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            return;
        }

        $args = func_get_args();
        array_shift($args); // remove sender
        array_shift($args); // remove funcName
        array_shift($args); // remove BBox

        switch ($funcName) {
            case 'b':
            case 'strong':
                return $this->strong($sender, true);
                break;
            case 'i':
                return $this->italic($sender, true);
                break;
            default:
                array_push($this->callbackStack, array(     'funcName' => $funcName,
                                                'appearance' => &$this->callbackFunc[$funcName]['appearance'],
                                                'bbox' => $BBox,
                                                'param' => $args));
                break;
        }
    }

    /**
     * trigger the callback function
     * @param Cpdf_Writing $sender The sender class object
     * @param String $funcName function name to be called
     * @param Array $BBox Rest of the Bounding Box containing UPPER X and UPPER Y coordinates
     * @param mixed additional parameters (optional)
     */
    public function DoTrigger(&$sender, $funcName, $BBox, $param = null)
    {
        switch ($funcName) {
            case 'b':
            case 'strong':
                return $this->strong($sender, false, $param);
                break;
            case 'i':
                return $this->italic($sender, false, $param);
                break;
        }

        $i = count($this->callbackStack);

        $func = null;
        foreach (array_reverse($this->callbackStack, true) as $k => $tmp) {
            if ($tmp['funcName'] == $funcName) {
                $func = &$this->callbackStack[$k];
                break;
            }
        }

        if (!isset($func)) {
            Cpdf_Common::DEBUG("Callback function '$funcName' not registered in stack", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
            return;
        }

        $args = func_get_args();
        array_shift($args); // remove sender
        array_shift($args); // remove funcName
        array_shift($args); // remove BBox

        Cpdf_Common::SetBBox($BBox, $func['bbox']);

        $app = &$func['appearance'];
        if (isset($app)) {
            $app->CallbackNo = 0;

            if ($this->CURPAGE !== $app->page) {
                // if it is a new page, register a new appearance object
                $app = $this->NewAppearance();
                $app->IsCallback = true;
            }
        }

        if (is_array($args)) {
            $args = array_merge($func['param'], $args);
        } else {
            $args = $func['param'];
        }

        $res = $this->$funcName($sender, $func, $func['bbox'], $args);
        $func['done'] = true;
        return $res;
    }

    /**
     * TODO: Implement the paging for callbacks
     */
    public function SetCallbackPageMode($pm)
    {
        $this->callbackPageMode = $pm;
    }

    /**
     * Correct the BBox for all calls located in callbackStack by using Cpdf_*->Callback function call
     * @param Int $offsetX correction of the X coordinate
     * @param Int $offsetY correction of the Y coordinate
     * @param Bool $resize request a fully resize the Cpdf_* object
     */
    public function Callback($offsetX = 0, $offsetY = 0)
    {
        if (count($this->callbackStack) <= 0) {
            Cpdf_Common::DEBUG("Callback Stack is empty", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);
            return;
        }

        foreach ($this->callbackStack as $key => &$func) {
            Cpdf_Common::DEBUG("---CALLBACK '".$func['funcName']."' offsetX = $offsetX, offsetY = $offsetY STARTED---", Cpdf_Common::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

            if (!isset($func)) {
                Cpdf_Common::DEBUG("No Callback found", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                continue;
            }

            $func['bbox'][0] += $offsetX;
            $func['bbox'][2] += $offsetX;
            $func['bbox'][1] += $offsetY;
            $func['bbox'][3] += $offsetY;

            foreach ($func as $k => &$cb) {
                if ($k == 'bbox' || $k == 'param' || $k == 'funcName' || $k == "done") {
                    continue;
                }
                if (is_object($cb)) {
                    $cb->Callback($func['bbox']);
                }
            }
            unset($this->callbackStack[$key]);
        }

        Cpdf_Common::DEBUG("---CALLBACK '".$func['funcName']."' ENDED---", Cpdf_Common::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);
    }

    /**
     * Used to start font style italic
     *
     * @param {Cpdf_Writing} $sender
     * @param {bool} $begin
     * @param {string} $str
     */
    public function italic(&$sender, $begin, $str = "")
    {
        $curStyle = $sender->GetFontStyle();
        $pos = strpos($curStyle, 'i');
        if ($pos === false && $begin == true) {
            $curStyle.= 'i';
        } elseif ($pos !== false && $begin == false) {
            $curStyle = str_replace('i', '', $curStyle);
        }

        $sender->SetFont('', 0, $curStyle);

        $res = '';
        if (!empty($str)) {
            $res.= $sender->Tj($str);
        }
        $res .= $sender->TF();

        return $res;
    }

    /**
     * Used to start font style strong
     *
     * @param Cpdf_Writing $sender
     * @param Bool $begin
     * @param String $str
     */
    public function strong(&$sender, $begin, $str = "")
    {
        $curStyle = $sender->GetFontStyle();
        $pos = strpos($curStyle, 'b');
        if ($pos === false && $begin == true) {
            $curStyle.= 'b';
        } elseif ($pos !== false && $begin == false) {
            $curStyle = str_replace('b', '', $curStyle);
        }

        $res = '';
        if (!empty($str)) {
            $res.= $sender->Tj($str);
        }

        $sender->SetFont($sender->FontFamily, 0, $curStyle);

        $res .= $sender->TF();

        return $res;
    }

    /**
     * Callback function to put a pager on every page
     * TODO: Complete the pager function
     */
    public function pager(&$sender, &$cb, $bbox, $param)
    {
        return $sender->Tj($sender->page->PageNum.' of '.$sender->pages->PageNum);
    }

    /**
     * Give $sender object a background at BBox position by using Cpdf_Appearance->AddRectangle
     * @param Cpdf_Writing|Cpdf_Table $sender sender class object
     */
    public function background(&$sender, &$cb, $bbox, $params)
    {
        $app = &$cb['appearance'];
        $app->ZIndex = -10;
        $color = $params[0]['backgroundColor'];

        $app->AddColor($color[0], $color[1], $color[2]);
        $app->AddRectangle($bbox[0], $bbox[3], $bbox[2] - $bbox[0], $bbox[3] - $bbox[1], true);
    }

    /**
     * Colorize the text output by using Cpdf_Writing->ColoredTj([...]);
     */
    public function color(&$sender, &$cb, $bbox, $params)
    {
        $initBBox = $sender->GetBBox();

        //$params[1] = "OVERWRITE EXAMPLE";

        $width = $sender->GetTextWidth($params[1]);

        return $sender->ColoredTj($params[1], explode(',', $params[0]))." ".$sender->TD($width + $bbox[0] - $initBBox[0]);
    }

    /**
     * callback function for external links
     *
     * @param {Cpdf_Writing} $sender class object from callback function
     * @param {Array} $cb
     * @param {Array} $bbox Bounding box
     * @param {Array} $params additional callback parameters
     * @return bool true to remove the previous text content, false to ignore
     */
    public function alink(&$sender, &$cb, $bbox, $params)
    {
        $app = &$cb['appearance'];

        $app->AddColor(0, 0, 1, true);
        $app->AddColor(0, 0, 1, false);
        $lineStyle = new Cpdf_LineStyle(0.5, 'butt', '');
        $app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);

        $annot = $sender->pages->NewAnnotation('link', $bbox, null, new Cpdf_Color(array(0,0,1)));
        $annot->SetUrl($params[0]);
        $c = count($cb);

        $cb["link_$c"] = $annot;

        $initBBox = $sender->GetBBox();

        $width = $sender->GetTextWidth($params[1]);

        return $sender->ColoredTj($params[1], array(0,0,1))." ".$sender->TD($width + $bbox[0] - $initBBox[0]);
    }

    /**
     * callback function for internal links
     *
     * @param {Cpdf_Writing} $sender class object from callback function
     * @param {Array} $cb
     * @param {Array} $bbox Bounding box
     * @param {Array} $params additional callback parameters
     * @return bool true to remove the previous text content, false to ignore
     */
    public function ilink(&$sender, &$cb, $bbox, $params)
    {
        $app = &$cb['appearance'];

        $lineStyle = new Cpdf_LineStyle(0.5, 'butt', '', array(3,1));
        $app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);

        $annot = $sender->pages->NewAnnotation('link', $bbox, null, new Cpdf_Color(array(0,0,1)));
        $annot->SetDestination($params[0]);

        $c = count($cb);
        $cb['link' + $c] = $annot;
        return false;
    }
}


/**
 * PDF document info (Metadata)
 */
class Cpdf_Metadata
{
    public $ObjectId;

    private $pages;
    private $info;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;

        $this->info = array(
            'Title' => 'PDF Document Title',
            'Author' => 'ROS pdf class',
            'Producer' => 'ROS for PHP',
            'Description' => '',
            'Subject' => '',
            'Creator'=>'ROS pdf class',
            'CreationDate'=> time(),
            'ModDate' => time(),
            'Trapped' => 'False'
        );
    }

    public function SetInfo($key = 'Title', $value = 'PDF document title')
    {
        $this->info[$key] = $value;
    }

    private function outputInfo()
    {
        $res = "\n<<";
        if (count($this->info) > 0) {
            $encObj = &$this->pages->encryptionObject;

            if (isset($encObj)) {
                $encObj->encryptInit($this->ObjectId);
            }

            foreach ($this->info as $key => $value) {
                switch ($key) {
                    case 'Trapped':
                        $res.= " /$key /$value";
                        break;
                    case 'ModDate':
                    case 'CreationDate':
                        $value = $this->getDate($value);
                    default:
                        if (isset($encObj)) {
                            $dummyAsRef = null;
                            $res.= " /$key (".$this->pages->filterText($dummyAsRef, $encObj->ARC4($value)).")";
                        } else {
                            $res.= " /$key ($value)";
                        }
                        break;
                }
            }
        }
        $res.= " >>";
        return $res;
    }

    /**
     * TODO: build up the XML metadata object which is avail since PDF version 1.4
     */
    private function outputXML()
    {
        $res= "\n<< /Type /Metadata /Subtype /XML";
        // dummy output for XMP
        $tmp= "\n".'<?xpacket begin="" id="W5M0MpCehiHzreSzNTczkc9d"?>
		<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">
			<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
				<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">
					<dc:format>application/pdf</dc:format>
					<dc:title>
						<rdf:Alt>
							<rdf:li xml:lang="x-default">'.$this->info['Title'].'</rdf:li>
						</rdf:Alt>
					</dc:title>
					<dc:creator>
						<rdf:Seq>
							<rdf:li>'.$this->info['Creator'].'</rdf:li>
						</rdf:Seq>
					</dc:creator>
					<dc:description>
						<rdf:Alt>
							<rdf:li xml:lang="x-default">'.$this->info['Description'].'</rdf:li>
						</rdf:Alt>
					</dc:description>
					<dc:subject>
						<rdf:Bag>
							<rdf:li>'.$this->info['Subject'].'</rdf:li>
						</rdf:Bag>
					</dc:subject>
				</rdf:Description>
				<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">
					<xmp:CreateDate>'.$this->getDate($this->info['CreationDate'], 'XML').'</xmp:CreateDate>
					<xmp:CreatorTool>'.$this->info['Creator'].'</xmp:CreatorTool>
					<xmp:ModifyDate>'.$this->getDate($this->info['ModDate'], 'XML').'</xmp:ModifyDate>
				</rdf:Description>
				<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">
					<pdf:Producer>'.$this->info['Producer'].'</pdf:Producer>
				</rdf:Description>
			</rdf:RDF>
		</x:xmpmeta>
		<?xpacket end="w"?>';

        $res.= ' /Length '.strlen($tmp).' >>';
        $res.= "\nstream".$tmp."\n\nendstream";
        return $res;
    }

    private function getDate($t, $type = 'PLAIN')
    {
        switch (strtoupper($type)) {
            default:
            case 'PLAIN':
                return 'D:'.date('YmdHis', $t)."+00'00'";
                break;
            case 'XML':
                return date('Y-m-d', $t).'T'.date('H:i:s').'Z';
                break;
        }

    }

    public function OutputAsObject($type = 'PLAIN')
    {
        $res= "\n$this->ObjectId 0 obj";

        switch (strtoupper($type)) {
            case 'PLAIN':
                $res.= $this->outputInfo();
                break;
            case 'XML':
                $res.= $this->outputXML();
                break;
        }

        $res.= "\nendobj";
        $this->pages->AddXRef($this->ObjectId, strlen($res));
        return $res;
    }
}

class Cpdf_Option
{
    public $ObjectId;

    private $pages;

    private $preferences;
    private $pageLayout;

    private $oPage;
    private $oAction;

    private $names;

    private $metadataId;
    private $destinationId;
    private $intentsId;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;
        $this->preferences = array();
        $this->names = array();
    }

    public function OpenAction(&$page, $action = 'Fit')
    {
        $this->oPage = &$page;
        $this->oAction = $action;
    }

    public function AddName($name, $pageId, $y = null)
    {
        $this->names[$name] = array('pageId'=> $pageId, 'y' => $y);
    }

    public function SetPageLayout($name = 'SinglePage')
    {
        $this->pageLayout = $name;
    }

    public function SetPreferences($key, $value)
    {
        $this->preferences[$key] = $value;
    }

    public function SetMetadata($id)
    {
        $this->metadataId = $id;
    }

    /**
     * TODO: implement outlines
     */
    public function SetOutlines()
    {

    }

    private function outputDestinations()
    {
        $this->destinationId = ++$this->pages->objectNum;
        $res = "\n$this->destinationId 0 obj";
        $res.="\n<< ";
        foreach ($this->names as $k => $v) {
            $res.="\n  ";
            if (isset($v['y'])) {
                $res.= "/$k [".$v['pageId'].' 0 R /FitH '.$v['y'].']';
            } else {
                $res.= "/$k [".$v['pageId'].' 0 R /Fit]';
            }
        }
        $res.=" \n>>";
        $res.="\nendobj";

        $this->pages->AddXRef($this->destinationId, strlen($res));
        return $res;
    }

    private function outputIntents()
    {
        $this->intentsId = ++$this->pages->objectNum;
        $res = "\n$this->intentsId 0 obj";
        $res.="\n<< /Type /OutputIntent /S /GTS_PDFX /OutputConditionIdentifier (CGATS TR 001) /RegistryName (www.color.org) >>";

        $res.="\nendobj";
        $this->pages->AddXRef($this->intentsId, strlen($res));
        return $res;
    }

    public function OutputAsObject()
    {
        $res = "\n$this->ObjectId 0 obj";
        $res.= "\n<< /Type /Catalog";
        if (count($this->preferences) > 0) {
            $res.=" /ViewerPreferences <<";
            foreach ($this->preferences as $key => $value) {
                $res.=" /$key $value";
            }
            $res.=" >>";
        }

        $res.= " /Pages 2 0 R";

        if (isset($this->pageLayout)) {
            $res.= " /PageLayout /".$this->pageLayout;
        }

        if (isset($this->oAction)) {
            $res.= ' /OpenAction ['.$this->oPage->ObjectId.' 0 R /'.$this->oAction.']';
        }

        if (isset($this->metadataId)) {
            $res.= ' /Metadata '.$this->metadataId.' 0 R';
        }

        $intents = '';
        //$intents = $this->outputIntents();
        //$res.= ' /OutputIntents ['.$this->intentsId.' 0 R]';

        $dests='';
        if (count($this->names) > 0) {
            $dests = $this->outputDestinations();
            $res.= ' /Dests '.$this->destinationId.' 0 R';
        }

        $res.= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res.$intents.$dests;
    }
}

/**
 * Page class object
 */
class Cpdf_Page
{
    public $ObjectId;

    /**
     * Only for displaying current and max page(s)
     */
    public $PageNum;

    public $pages;

    public $Mediabox;
    public $Bleedbox;
    public $Cropbox;

    public $Background;

    public $Name;

    private $entries;

    public function __construct(&$pages, $mediabox, $cropbox = null, $bleedbox = null)
    {

        if (!isset($cropbox) || (is_array($cropbox) && count($cropbox) != 4)) {
            $cropbox = $mediabox;
        }

        if (!isset($bleedbox) || (is_array($bleedbox) && count($bleedbox) != 4)) {
            $bleedbox = $cropbox;
            Cpdf_Common::SetBBox(array('addlx'=> 30, 'addly' => 30, 'addux' => -30, 'adduy' => -30), $bleedbox);
        }

        $this->Mediabox = $mediabox;
        $this->Cropbox = $cropbox;
        $this->Bleedbox = $bleedbox;

        $this->pages = &$pages;
        $this->entries = array();
    }

    public function MovePage($pagePos = 1)
    {
        $this->pages->MovePage($this, $pagePos);
    }

    /**
     * set background color or image
     *
     * @param array $color color array in form of R, G, B
     * @param string $source image path
     */
    public function SetBackground($color, $source = '', $x = 'left', $y = 'top', $width = null, $height = null)
    {
        // use the mediabox to draw a fully filled rectangle
        $mb = &$this->Mediabox;

        $app = &$this->pages->NewAppearance($mb);
        $app->page = null;
        $app->SetPageMode(Cpdf_Content::PMODE_NOPAGE);
        $app->ZIndex = -1;

        if (is_array($color)) {
            $app->AddColor($color[0], $color[1], $color[2]);
            $app->AddRectangle(0, 0, $mb[2], $mb[3], true);
        }

        if (is_string($source) && !empty($source)) {
            $app->AddImage($x, $y, $source, $width, $height);
        }

        $this->Background = &$app;
    }

    public function Rotate()
    {
        $tmp = $this->Mediabox[2];
        $this->Mediabox[2] = $this->Mediabox[3];
        $this->Mediabox[3] = $tmp;

        $tmp = $this->Cropbox[2];
        $this->Cropbox[2] = $this->Cropbox[3];
        $this->Cropbox[3] = $tmp;

        $tmp = $this->Bleedbox[2];
        $this->Bleedbox[2] = $this->Bleedbox[3];
        $this->Bleedbox[3] = $tmp;
    }

    public function AddEntry($key, $value)
    {
        $this->entries[$key] = $value;
    }

    public function OutputAsObject()
    {
        // the Object Id of the page will be set in Cpdf_Pages->OutputAll()
        $res = "\n".$this->ObjectId . " 0 obj\n";
        $res.="<< /Type /Page /Parent ".$this->pages->ObjectId." 0 R";

        $annotRefsPerPage = &$this->pages->contentRefs['annot'][$this->ObjectId];
        $noPageAnnotRefs = &$this->pages->contentRefs['nopageA'];

        if (!is_array($annotRefsPerPage)) {
            $annotRefsPerPage = array();
        }
        if (is_array($noPageAnnotRefs)) {
            $mergedAnnot = $annotRefsPerPage + $noPageAnnotRefs;
        }

        $contentRefsPerPage = &$this->pages->contentRefs['content'][$this->ObjectId];
        $noPageRefs = &$this->pages->contentRefs['nopage'];

        // merge page contents with NO PAGE content (but only those with Paging != 'none' will be displayed)
        if (!is_array($contentRefsPerPage)) {
            $contentRefsPerPage = array();
        }

        if (is_array($noPageRefs)) {
            $merged = $contentRefsPerPage + $noPageRefs;
        } else {
            $merged = $contentRefsPerPage;
        }

        if (count($mergedAnnot) > 0) {
            // is a focus sort required for annotations?
            //uasort($annotRefsPerPage, array($this->pages, 'compareRefs'));
            $res.=' /Annots [';
            foreach ($mergedAnnot as $objId => $mode) {
                $res.= $objId.' 0 R ';
            }
            $res.= ']';
        }

        if (is_array($this->Mediabox)) {
            $res.= ' /MediaBox '.sprintf("[%.3F %.3F %.3F %.3F]", $this->Mediabox[0], $this->Mediabox[1], $this->Mediabox[2], $this->Mediabox[3]);
        }
        if (is_array($this->Cropbox)) {
            $res.= ' /CropBox '.sprintf("[%.3F %.3F %.3F %.3F]", $this->Cropbox[0], $this->Cropbox[1], $this->Cropbox[2], $this->Cropbox[3]);
        }
        if (is_array($this->Bleedbox)) {
            $res.= ' /BleedBox '.sprintf("[%.3F %.3F %.3F %.3F]", $this->Bleedbox[0], $this->Bleedbox[1], $this->Bleedbox[2], $this->Bleedbox[3]);
        }

        if (count($merged) > 0) {
            //sort the content to set object to foreground dependent on the ZIndex property
            uasort($merged, array($this->pages, 'compareRefs'));

            $res.=' /Contents [';
            // if a Backround is set than put it first into the content entry
            if (isset($this->Background) && isset($this->Background->ObjectId)) {
                $res.= $this->Background->ObjectId.' 0 R ';
            }
            foreach ($merged as $objId => $mode) {
                if ($mode[0] != Cpdf_Content::PMODE_NOPAGE && $mode[0] != Cpdf_Content::PMODE_REPEAT) {
                    $res.= $objId.' 0 R ';
                }
            }
            $res.="]";
        }

        foreach ($this->entries as $k => $v) {
            $res.= " /$k $v";
        }

        $res.=" >>\nendobj";
        $this->pages->AddXRef($this->ObjectId, strlen($res));
        return $res;
    }
}

class Cpdf_Content extends Cpdf_Common
{
    public $Paging;

    /**
     * page mode 'none' cause the content to NOT output the object at all
     */
    const PMODE_NONE = -1;
    /**
     * page mode 'NOPAGE' used for general objects, like background appearances (or images)
     */
    const PMODE_NOPAGE = 0;
    /**
     * add the content to the current page
     */
    const PMODE_ADD = 1;
    /**
     * add the content to all pages
     */
    const PMODE_ALL = 2;
    /**
     * add the content to all pages after current
     */
    const PMODE_ALL_FROM_HERE = 4;
    /**
     * repeat the content at runtime (only works for AddText) - useful to display page number on every page
     */
    const PMODE_REPEAT = 8;

    /**
     * used for destination names
     */
    public $Name;

    protected $pagingCallback;

    const PB_BLEEDBOX = 1;
    const PB_BBOX = 2;
    const PB_CELL = 4;

    public $BreakPage;
    public $BreakColumn;

    public $ObjectId;
    public $ZIndex;

    /**
     * Main Cpdf class object
     * @var Cpdf
     */
    public $pages;
    /**
     * current page object
     * @var Cpdf_Page
     */
    public $page;

    protected $contents;
    protected $entries;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;
        $this->page = $pages->CURPAGE;

        //$this->transferGlobalSettings();

        $this->contents = '';
        $this->entries = array();

        $this->BreakPage = self::PB_BLEEDBOX;
        $this->BreakColumn = false;

        $this->SetPageMode(self::PMODE_ADD, self::PMODE_ADD);
    }

    public function AddRaw($str)
    {
        $this->contents .= $str;
    }

    public function AddEntry($k, $value)
    {
        $this->entries[$k] = $value;
    }

    public function ClearEntries()
    {
        $this->entries = array();
    }

    /**
     * Set page option for content and callbacks to define when the object should be displayed
     *
     * @param string $content paging mode for content objects (default: PMODE_ADD)
     * @param string $callbacks paging mode for the nested callbacks (default: PMODE_ADD)
     */
    public function SetPageMode($pm_content, $pm_callbacks = 1)
    {
        $this->Paging = $pm_content;
        $this->pagingCallback = $pm_callbacks;
    }

    public function Length()
    {
        return strlen($this->contents);
    }

    public function HasEntries()
    {
        return (count($this->entries) > 0) ? true : false;
    }

    public function Output()
    {
        return $this->contents;
    }

    public function OutputAsObject()
    {
        $res = '<<';

        $l = 0;
        $tmp = $this->Output();
        if (!empty($tmp)) {
            // make sure compression is included and declare it properly
            if (function_exists('gzcompress') && $this->pages->Compression && $this->Compression) {
                if (isset($this->entries['Filter'])) {
                    $this->AddEntry('Filter', '[/FlateDecode '.$this->entries['Filter'].']');
                } else {
                    $this->AddEntry('Filter', '/FlateDecode');
                }
                $tmp = gzcompress($tmp, $this->Compression);
            }

            if (isset($this->pages->encryptionObject)) {
                $encObj = &$this->pages->encryptionObject;
                $encObj->encryptInit($this->ObjectId);
                $tmp = $encObj->ARC4($tmp);
            }
            $l = strlen($tmp);
            $this->AddEntry('Length', $l);
            //$res.= ' /Length '.$l;
        }

        if (is_array($this->entries)) {
            foreach ($this->entries as $k => $v) {
                $res.= " /$k $v";
            }
        }

        $res.= ' >>';

        if ($l > 0) {
            $res.= " stream\n".$tmp."\nendstream";
        }
        $res = "\n".$this->ObjectId." 0 obj\n".$res."\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}

/**
 * graphic class used for drawings like rectangles and lines in order to allow callbacks
 * Callback function may overwrite the X, Y, Width and Height property to adjust size or position
 */
class Cpdf_Graphics
{
    public $Type;

    public $X;
    public $Y;

    public $Width;
    public $Height;

    public $Params;

    public function __construct($type = 'line', $x, $y)
    {
        $this->Type = $type;
        $this->Params = array();
        $this->X = $x;
        $this->Y = $y;
    }

    public function Output()
    {
        $res = 'q ';
        if (isset($this->Params['style']) && is_object($this->Params['style'])) {
            $ls = &$this->Params['style'];
            $res.= $ls->Output();
        }

        switch ($this->Type) {
            case 'rectangle':
                $res.= sprintf('%.3F %.3F %.3F %.3F re', $this->X, $this->Y, $this->Width, $this->Height);

                if (isset($this->Params['filled']) && $this->Params['filled']) {
                    if (isset($this->Params['style']) && (is_object($this->Params['style']) || (is_bool($this->Params['style']) && $this->Params['style']))) {
                        $res.=' b';
                    } else {
                        $res.=' f';
                    }
                } else {
                    $res.=' S';
                }
                break;
            case 'line':
                $res.= sprintf('%.3F %.3F m %.3F %.3F l S', $this->X, $this->Y, $this->X + $this->Width, $this->Y + $this->Height);
                break;
        }
        return $res.' Q';
    }
}

class Cpdf_Appearance extends Cpdf_Content
{
    /**
     * the current Cpdf_Font object as reference
     * Use SetFont('fontname'[, ...]) to change it
     * @var Cpdf_Font
     */
    protected $CURFONT;

    /**
     * stores the font family
     * @var String
     */
    public $FontFamily;

    public $LineGap = 0;
    /**
     * ColumnGap is used when BreakColumn is TRUE
     */
    public $ColumnGap = 10;

    /**
     * the current font height received by $this->CURFONT->getFontHeight($size)
     * Use public property LineGap to change the distance
     */
    protected $fontHeight;

    /**
     * the current font descender received by $this->CURFONT->getFontDecender($size)
     * Use public property LineGap to change the distance
     */
    protected $fontDescender;

    /**
     * the current font size.
     * Use SetFont('fontname'[, ...]) to change it
     */
    private $fontSize;

    /**
     * the current font style
     * Use SetFont('fontname'[, ...]) to change it
     */
    private $fontStyle;

    /**
     * used to store the rotation while adding text elements
     */
    private $angle;

    /**
     * font color
     */
    private $fontColor;

    /**
     * Bounding Box to define the position of the text
     *
     * Use Cpdf_Common::SetBBox([changeBBox], $this->BBox) to change the BBox
     * And Cpdf_Writing->GetBBox([which]) to get Bounding box properties
     * See Adobe PDF Refence 1.4, Chapter 3.8.3 Rectangle for more information
     */
    protected $BBox;
    protected $initialBBox;
    protected $resizeBBox = false;

    /**
     * relative position of the rectangle
     */
    public $y;

    private $isFirst = true;

    /**
     * Properly an Cpdf_Callback object is used
     */
    public $IsCallback;
    public $CallbackNo;
    protected $callbackObjects;

    public $Ressources;

    public $JustifyCallback;

    public function __construct(&$pages, $BBox = array(), $color = null, $ressources = '')
    {
        parent::__construct($pages, $BBox);

        $this->Ressources = $ressources;
        $this->JustifyCallback = true;

        if (!empty($color)) {
            $this->fontColor = new Cpdf_Color($color, false);
        }

        $this->setColor();
        // make sure this is not a callback object
        $this->IsCallback = false;
        $this->CallbackNo = 0;
        $this->callbackObjects = array();

        $this->BBox = $pages->CURPAGE->Bleedbox;
        Cpdf_Common::SetBBox($BBox, $this->BBox);

        if (isset($this->BBox)) {
            $this->x = $this->BBox[0];
            $this->y = $this->BBox[3];

            $this->initialBBox = $this->BBox;
        }

        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_BBOX)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q";
        }
    }

    private function setColor()
    {
        if (is_object($this->fontColor)) {
            $this->contents.= "\n".$this->fontColor->Output(false, true);
        }
    }

    /**
     * Receives the current bounding box
     * Can be equal the bounding box of the page or any other user defined coordinates
     */
    public function GetBBox($which = null)
    {
        if (empty($which)) {
            return $this->BBox;
        }

        $which = strtolower($which);
        switch ($which) {
            case 'height':
                return $this->BBox[3] - $this->BBox[1];
            case 'width':
                return $this->BBox[2] - $this->BBox[0];
            case 'y':
                return $this->BBox[1];
            case 'x':
                return $this->BBox[0];
        }
    }

    public function GetY()
    {
        return $this->y;
    }

    public function UpdateBBox($bbox, $resetCursor = false)
    {
        Cpdf_Common::SetBBox($bbox, $this->BBox);

        if ($resetCursor) {
            $this->y = $this->BBox[3];
            $this->x = $this->BBox[0];
        }
    }

    public function GetTextWidth($text, $size = 0)
    {
        if ($size <= 0) {
            $size = $this->fontSize;
        }

        $tm = $this->CURFONT->getTextLength($size, $text, -1, 0, 0);
        return $tm[0];
    }

    public function GetFontStyle()
    {
        return $this->fontStyle;
    }

    public function GetFontHeight()
    {
        return $this->fontHeight;
    }

    public function GetFontDescender()
    {
        return $this->fontDescender;
    }

    private $delayedContent = array();

    /**
     * Set the font and font size for the current text session
     * By default font size is set to 10 units
     *
     * TODO: Make use of default font families for TTF fonts (including UNICODE)
     */
    public function SetFont($fontName, $fontSize = 10, $style = '', $isUnicode = false)
    {
        if (empty($fontName)) {
            $fontName = $this->FontFamily;
        }

        $fontName = strtolower($fontName);
        $this->FontFamily = $fontName;

        $this->fontStyle = '';

        if (!empty($style)) {
            if (isset($this->pages->DefaultFontFamily[$fontName])) {
                if (isset($this->pages->DefaultFontFamily[$fontName][$style])) {
                    $fontName = $this->pages->DefaultFontFamily[$fontName][$style];
                    $this->fontStyle = $style;
                }
            }
        }

        if (empty($fontName)) {
            Cpdf_Common::DEBUG("Could not find either base font or style for '$fontName'", Cpdf_Common::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            return;
        }

        $this->CURFONT = $this->pages->NewFont($fontName, $isUnicode);
        //$this->baseFontName = $f;

        if ($fontSize > 0 && $this->fontSize != $fontSize) {
            $this->fontSize = $fontSize;
            $tmpHeight = $this->fontHeight;
            $tmpDescender = $this->fontDescender;
            // call getFontHeight to calculate the correct leading
            $this->fontHeight = $this->CURFONT->getFontHeight($fontSize);
            $this->fontDescender = $this->CURFONT->getFontDescender($fontSize);

            $this->y += ($tmpHeight + $tmpDescender);
            $this->y -= $this->fontHeight + $this->fontDescender;
        }

        // if Y coord has not been changed yet - correct the margin with font height
        if ($this->y == $this->BBox[3] && !$this->IsCallback) {
            $this->y -= $this->fontHeight + $this->fontDescender;
        }

    }

    /**
     * Add a text by using either "default" formattings (like <b> or <i>) or any ALLOWED callback function
     * To allow callback please...
     * TODO: Either registering callbacks or continue with extending $this->allowedTags property
     */
    public function AddText($text, $width = 0, $justify = 'left', $wordSpaceAdjust = 0)
    {
        if ($this->Paging == Cpdf_Content::PMODE_REPEAT) {
            array_push($this->delayedContent, array($text, $width, $justify, $wordSpaceAdjust));
            return;
        }
        // convert to text
        $text = "$text";
        if (mb_detect_encoding($text) != 'UTF-8') {
            $text = utf8_encode($text);
        }

        if (!isset($this->CURFONT)) {
            $this->SetFont('Helvetica');
        }

        // use the BBox to calculate the possible width depended on the page size
        // ignore the width for callbacks TODO: include the bbox of the caller
        if ($width == 0) {
            $width = $this->BBox[2] - $this->BBox[0];
        }

        // split all manual line breaks
        $lines = preg_split("/\n/", $text);
        foreach ($lines as $v) {
            $this->isFirst = true;

            do {
                if (trim($v) == '') {
                    $this->y -= $this->fontHeight - $this->fontDescender;
                    break;
                }

                if ($this->y < $this->BBox[1] && !$this->IsCallback) {
                    //$width = $this->BBox[2] - $this->BBox[0];
                    if ($this->BreakColumn) {
                        $this->BBox[2] = $this->x + $width + $this->ColumnGap;
                    }

                    // break into columns
                    if ($this->resizeBBox) {
                        $this->BBox[1] = $this->y;
                        if ($this->BBox[1] <= $this->initialBBox[1] && Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_CELL)) {
                            $this->BBox[1] += $this->fontHeight + $this->fontDescender;
                            break 2;
                        }
                    } elseif ($this->BreakColumn && ($this->BBox[2] + $width) <= $this->page->Bleedbox[2]) {
                        $obj = Cpdf_Common::DoClone($this);
                        $this->pages->addObject($obj, true);
                        $this->contents = '';

                        $this->BBox[0] = $this->BBox[2];
                        $this->BBox[2] += $width;

                        $this->x = $this->BBox[0];
                        $this->y = $this->BBox[3];
                        $this->y -= $this->fontHeight + $this->fontDescender;
                    } elseif ($this->BreakPage > 0 && !$this->IsCallback) {
                        $obj = Cpdf_Common::DoClone($this);
                        $this->pages->addObject($obj, true);

                        // reset the current object to initial values
                        $this->contents = '';
                        // reset the font color for the next page
                        $this->setColor();

                        $this->BBox = $this->initialBBox;

                        $p = $this->pages->GetPageByNo($this->page->PageNum + 1);
                        if (!isset($p) || $this->pages->IsInsertMode()) {
                            $p = $this->pages->NewPage($this->page->Mediabox, $this->page->Cropbox, $this->page->Bleedbox);
                            // put background as reference to the new page
                            $p->Background = $this->page->Background;
                        }

                        $this->page = $p;

                        if (Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_BLEEDBOX)) {
                            $this->initialBBox[1] = $this->page->Bleedbox[1];
                            $this->initialBBox[3] = $this->page->Bleedbox[3];
                        }

                        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
                        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_BBOX)) {
                            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q";
                        }

                        $this->x = $this->initialBBox[0];
                        $this->y = $this->initialBBox[3];
                        $this->y -= $this->fontHeight + $this->fontDescender;
                    } else {
                        // no page break at all
                        // use the below line to truncate everything what applies to the next page
                        //break 2;
                    }
                }

                // BT [...] ET content goes here including font selection and formatting (callbacks)
                $v = $this->addTextDirectives1($v, $width, $justify, $wordSpaceAdjust, true);

                // determine the next Y pos by using font size attributes
                $this->y -= $this->fontHeight - $this->fontDescender + $this->LineGap;
            } while ($v);
        }

        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_TEXT)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q";
        }
    }
    /**
     * Use the affine transformation to rotate the text
     */
    public function SetRotation($angle, $x, $y)
    {
        if ($angle != 0) {
            $a = deg2rad((float)$angle);
            $tmp = sprintf('%.3F', cos($a)).' '.sprintf('%.3F', (-1.0*sin($a))).' '.sprintf('%.3F', sin($a)).' '.sprintf('%.3F', cos($a)).' ';
            $tmp .= sprintf('%.3F', $x).' '.sprintf('%.3F', $y).' Tm';
            $this->contents.= "\n".$tmp;
            $this->angle = $angle;
        }
    }
    /**
     * Reset the rotation (or any transformation) by calling the ResetTransform() method
     */
    public function ResetRotation()
    {
        $this->ResetTransform();
        $this->angle = 0;
    }
    /**
     * This will reset the matrix to its default values
     */
    public function ResetTransform()
    {
        $this->contents.="\n1 0 0 1 0 0 Tm";
    }

    private function justifyImage($width, $height, $xpos = 'left', $ypos = 'top')
    {
        $x = $this->BBox[0];
        $y = $this->BBox[1];
        // if xpos is a string dynamically calculate the horizontal alignment
        if (is_string($xpos)) {
            $maxWidth = $this->BBox[2] - $this->BBox[0];

            switch ($xpos) {
                case 'right':
                    $x += $maxWidth-$width;
                    break;
                case 'center':
                    $x += ($maxWidth - $width)/2;
                    break;
                default:
                    break;
            }
        } else {
            $x = $xpos;
        }

        if (is_string($ypos)) {
            switch ($ypos) {
                case 'top':
                    $y = $this->BBox[3] - $height;
                    break;
                case 'middle':
                    $middle = ($this->BBox[3] - $this->BBox[1]) / 2;
                    $y += $middle - ($height/2);
                    break;
            }
        } else {
            $y = $ypos;
        }

        $this->y = $y;

        // make use of the bounding box
        return sprintf('%.3F 0 0 %.3F %.3F %.3F cm', $width, $height, $x, $y);
    }

    /**
     * Add image into the page by either using coordinates or justification strings, like 'center', 'top', 'bottom', ...
     *
     * @param mixed $x either a float for exact positioning or a string to justify automatically
     * @param mixed $y either a float for exact positioning or a string to justify automatically
     * @param string $source source file or url of an JPEG or PNG image
     * @param float $width optional width to resize the image
     * @param float $height optional height to resize the image
     */
    public function AddImage($x, $y, $source, $width = null, $height = null)
    {
        //print_r($this->BBox);
        $img = $this->pages->NewImage($source);

        $w = $img->Width;
        $h = $img->Height;

        if ((isset($width) && isset($height)) || isset($width) || isset($height)) {
            // if its a string then use percentage calc
            if (is_string($width) && preg_match('/([0-9]{1,3})%/', $width, $regs)) {
                $p = $regs[1];
                $maxWidth = $this->BBox[2] - $this->BBox[0];
                $w = $maxWidth / 100 * $p;
            } else {
                $w = $width;
            }

            if (is_string($height) && preg_match('/([0-9]{1,3})%/', $height, $regs)) {
                $p = $regs[1];
                $maxHeight = $this->BBox[3] - $this->BBox[1];
                $h = $maxHeight / 100 * $p;
            } else {
                $h = $width;
            }

            if (isset($width) && !isset($height)) {
                $h = $img->Height / $img->Width * $w;
            } elseif (isset($height) && !isset($width)) {
                $w = $img->Width / $img->Height * $h;
            }
        }

        if (!is_string($y)) {
            $y -= $h;
        }

        $this->contents.= "\nq ".$this->justifyImage($w, $h, $x, $y);
        $this->contents.= ' /'.Cpdf_Common::$ImageLabel.$img->ImageNum.' Do';
        $this->contents.= " Q";
    }

    /**
     * Add a rectangle (usable as callback)
     *
     * @param float $x x coordinate relative to bounding box
     * @param float $y y coordinate relative to bounding box
     * @param float $width width of the rectangle - Callbacks will overwrite this value
     * @param float $height height of the rectangle - Callbacks will overwrite this value
     */
    public function AddRectangle($x, $y, $width = 0, $height = 0, $filled = false, $lineStyle = null)
    {
        $o = new Cpdf_Graphics('rectangle', $this->BBox[0] + $x, $this->BBox[1] + $y);

        // if no width is set, take 100% of the current bounding box (or wait for callback)
        if ($width == 0) {
            $width = $this->BBox[2] - $this->BBox[0];
        }
        // if no height is set, take 100% of the current bounding box (or wait for callback)
        if ($height == 0) {
            $height = $this->BBox[3] - $this->BBox[1];
        }

        $o->Width = $width;
        $o->Height = $height;
        $o->Params = array('filled'=>$filled, 'style'=> $lineStyle);

        if (!$this->IsCallback) {
            $this->contents.= "\n".$o->Output();
        } else {
            $this->CallbackNo += 1;
            array_push($this->callbackObjects, $o);
        }
    }

    /**
     * set a default line style for all drawing within the Appearance object
     */
    public function SetDefaultLineStyle($width, $cap, $join = null, $dash = null)
    {
        $o = new Cpdf_LineStyle($width, $cap, $join, $dash);

        $this->contents.= "\n".$o->Output();
    }

    /**
     * Draw a line (usable as callback)
     *
     * Example: $app->AddLine(10, 800, 300, -300, new Cpdf_LineStyle(2, 'round','', array(5,3)));
     *
     * @param float $x initial x coordinate
     * @param float $y initial y coordinate
     * @param float $width width of the line
     * @param float $height height is used to set the end y coordinate
     * @param Cpdf_LineStyle $lineStyle defines the style of the line by using the Cpdf_LineStyle object
     *
     */
    public function AddLine($x, $y, $width = 0, $height = 0, $lineStyle = null)
    {
        $o = new Cpdf_Graphics('line', $this->BBox[0] + $x, $this->BBox[3] + $y);
        $o->Params['style'] = $lineStyle;
        // if no width is set, take 100% of the current bounding box (or wait for callback)
        /*
        if($width == 0){
			      $width = $this->BBox[2] - $this->BBox[0];
		    }
        */

        $o->Width = $width;
        $o->Height = $height;

        if (!$this->IsCallback) {
            $this->contents.= "\n".$o->Output();
        } else {
            $this->CallbackNo += 1;
            array_push($this->callbackObjects, $o);
        }
    }

    public function AddCurve($x, $y, $x1, $y1, $x2, $y2, $x3, $y3)
    {
        // in the current line style, draw a bezier curve from (x0,y0) to (x3,y3) using the other two points
        // as the control points for the curve.
        $this->contents.="\n".sprintf('%.3F', $x).' '.sprintf('%.3F', $y).' m '.sprintf('%.3F', $x1).' '.sprintf('%.3F', $y1);
        $this->contents.= ' '.sprintf('%.3F', $x2).' '.sprintf('%.3F', $y2).' '.sprintf('%.3F', $x3).' '.sprintf('%.3F', $y3).' c S';
    }

    public function AddCircleAsLine($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360)
    {

        $astart = deg2rad((float)$minRad);
        $afinish = deg2rad((float)$maxRad);

        $totalAngle =$afinish-$astart;

        $dt = $totalAngle/$nSeg;

        for ($i=0; $i < $nSeg; $i++) {
            $a0 = $x+$size*cos($astart);
            $b0 = $y+$size*sin($astart);

            $this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S', $x, $y, $a0, $b0);

            $astart += $dt;
        }
    }

    public function AddLinesInCircle($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360)
    {

        $astart = deg2rad((float)$minRad);
        $afinish = deg2rad((float)$maxRad);

        $totalAngle =$afinish-$astart;

        $dt = $totalAngle/$nSeg;

        for ($i=0; $i <= $nSeg; $i++) {
            $a0 = $x+$size*cos($astart);
            $b0 = $y+$size*sin($astart);

            $this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S', $x, $y, $a0, $b0);

            if ($astart > $afinish) {
                break;
            }

            $astart += $dt;
        }
    }

    public function AddPolyInCircle($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360)
    {

        $astart = deg2rad((float)$minRad);
        $afinish = deg2rad((float)$maxRad);

        $totalAngle =$afinish-$astart;

        $dt = $totalAngle/$nSeg;

        $a0 = $x+$size*cos($astart);
        $b0 = $y+$size*sin($astart);

        for ($i=0; $i < $nSeg; $i++) {
            $astart += $dt;

            $a1 = $x+$size*cos($astart);
            $b1 = $y+$size*sin($astart);

            $this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S', $a0, $b0, $a1, $b1);

            $a0 = $a1;
            $b0 = $b1;

            if ($astart > $afinish) {
                break;
            }
        }
    }
    /**
     * add an oval by using PDF curve graphcs
     */
    public function AddOval($x, $y, $size = 50, $aspect = 1, $rotate = 0)
    {
        if ($rotate != 0) {
            $a = -1*deg2rad((float)$rotate);
            $tmp = "\nq ";
            $tmp .= sprintf('%.3F', cos($a)).' '.sprintf('%.3F', (-1.0*sin($a))).' '.sprintf('%.3F', sin($a)).' '.sprintf('%.3F', cos($a)).' ';
            $tmp .= sprintf('%.3F', $x).' '.sprintf('%.3F', $y).' cm';
            $this->contents.= $tmp;
            $x=0;
            $y=0;
        }

        if ($aspect > 1) {
            $aspect = 1;
        }

        $s= $size * -1.333 * $aspect;

        $this->contents.=" ".sprintf('%.3F %.3F', $x - $size, $y).' m ';
        $this->contents.= sprintf(' %.3F %.3F', $x - $size, $y + $s);
        $this->contents.= sprintf(' %.3F %.3F', $x + $size, $y + $s);
        $this->contents.= sprintf(' %.3F %.3F', $x + $size, $y);
        $this->contents.=' c S';

        $this->contents.=" ".sprintf('%.3F %.3F', $x - $size, $y).' m ';
        $this->contents.= sprintf(' %.3F %.3F', $x - $size, $y - $s);
        $this->contents.= sprintf(' %.3F %.3F', $x + $size, $y - $s);
        $this->contents.= sprintf(' %.3F %.3F', $x + $size, $y);
        $this->contents.=' c S';

        if ($rotate != 0) {
            $this->contents.= " Q";
        }
    }

    /**
     * Draw a polygon with nearly unlimit point(X,Y) coordinates
     *
     * Example: $app->AddPolygon(300, 700, array(350, 750, 400, 600, 300, 600, 250, 500, 100, 550, 50, 800), true, true);
     *
     * @param float $x initial X coordinate
     * @param float $y initial Y coordinate
     * @param array $coord coordintes written as points in one single array - array(x1, y1, x2, y2, ...)
     * @param bool $filled defines if polygon should be filled or not
     * @param bool $closed defines if polygon should be closed at the end - it uses the PDF 's' property
     * @param Cpdf_LineStyle/bool $lineStyle can be either an object of Cpdf_LineStyle or boolean to set the default line style
     */
    public function AddPolygon($x, $y, $coord = array(), $filled = false, $closed = false, $lineStyle = null)
    {
        $c = count($coord);
        if ($c % 2) {
            array_pop($coord);
            $c--;
        }

        $ls = '';
        if (isset($lineStyle) && is_object($lineStyle)) {
            $ls = $lineStyle->Output();
        }

        $this->contents.= "\nq ".$ls.sprintf("%.3F %.3F m ", $x, $y);

        for ($i = 0; $i< $c; $i = $i+2) {
            $this->contents.= sprintf('%.3F %.3F l ', $coord[$i], $coord[$i+1]);
        }

        /*
        if ($closed){
			      $this->contents.= sprintf('%.3F %.3F l ',$x, $y);
		    }
        */

        if ($filled) {
            if (isset($lineStyle) && (is_object($lineStyle) || (is_bool($lineStyle) && $lineStyle))) {
                if ($closed) {
                    $this->contents.='b';
                } else {
                    $this->contents.='B';
                }
            } else {
                $this->contents.='f';
            }
        } elseif ($closed) {
            $this->contents.='s';
        } else {
            $this->contents.='S';
        }

        $this->contents.= ' Q';

        // lines are only shown when polygon has no filling - So repeat it for the lines only
        //if($fillRequired){
            //$this->AddPolygon($x, $y, $coord, true, $closed);
        //}
    }

    /**
     * Use RGB color as default
     */
    public function AddColor($r, $g, $b, $strokeColor = false)
    {
        $this->AddColorRGB($r, $g, $b, $strokeColor);
    }

    public function AddColorRGB($r, $g, $b, $strokeColor = false)
    {
        $o = new Cpdf_Color(array($r, $g, $b), $strokeColor);
        if (!$this->IsCallback) {
            $this->contents.="\n".$o->Output(false, true);
        } else {
            $this->CallbackNo += 1;
            array_push($this->callbackObjects, $o);
        }
    }

    public function Output()
    {
        if (count($this->delayedContent) > 0) {
            $this->SetPageMode(Cpdf_Content::PMODE_ADD, $this->pagingCallback);

            $this->contents = '';
            $this->callbackObject = null;
            $this->y = $this->BBox[3];
            $this->y -= $this->fontHeight + $this->fontDescender;

            foreach ($this->delayedContent as $v) {
                $this->AddText($v[0], $v[1], $v[2], $v[3]);
            }
        }

        $res = parent::Output();
        if (!empty($res)) {
            $res = "\nq ".$res ."\nQ";
        }
        return $res;
    }

    public function OutputAsObject()
    {
        if (!empty($this->Ressources)) {
            $this->AddEntry('Resources', $this->Ressources);
        }
        return parent::OutputAsObject();
    }

    private function justifyLine1(&$TEXTBLOCK, $textWidth, $lineWidth, $direction, &$adjust)
    {
        switch ($direction) {
            case 'right':
                $this->BBox[0] += $lineWidth-$textWidth;
                break;
            case 'center':
                $this->BBox[0] +=($lineWidth-$textWidth)/2;
                break;
            case 'full':
                if (preg_match_all("/\((.*?)\) Tj/", $TEXTBLOCK, $regs, PREG_OFFSET_CAPTURE)) {
                    // use Tw operator for justify full on non-unicode text
                    $text = '';
                    foreach ($regs[1] as $v) {
                        $text .= $v[0];
                    }

                    $nspaces = substr_count($text, ' ');
                    if ($nspaces > 0) {
                        $adjust = ($lineWidth - $textWidth)/$nspaces;

                        if ($this->CURFONT->IsUnicode) {
                            $spaceLength = $this->CURFONT->GetCharWidth(32);
                            if ($spaceLength === false) {
                                Cpdf_Common::DEBUG("Character width 'space' not found while justifying", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                            }

                            $rest = $lineWidth * 1000 - $textWidth * 1000;
                            $spaceW = ($spaceLength + (($rest/$this->fontSize) / $nspaces));

                            $start = 0;
                            $length = 0;
                            foreach ($regs[1] as $k => $v) {
                                $start = $regs[0][$k][1];
                                $length = strlen($regs[0][$k][0]);

                                if (!empty($v[0])) {
                                    $l = strlen($v[0]);
                                    $r = '[('.str_replace("\x00\x20", ') '.round(-$spaceW).' (', $v[0]).')] TJ';

                                    $TEXTBLOCK = substr_replace($TEXTBLOCK, $r, $start, $length);
                                }
                            }
                            $adjust = 0;
                        }
                    } else {
                        $adjust=0;
                    }
                }
                break;
        }
    }

    public function Tj($text)
    {
        $text = Cpdf_Common::filterText($this->CURFONT, $text);

        return sprintf(" (%s) Tj", $text);
    }

    public function ColoredTj($text, $color = array())
    {
        $c = new Cpdf_Color($color, false);
        return sprintf("q %s %s Q", $c->Output(false, true), $this->Tj($text));
    }

    private $currentTD = 0;

    public function TD($xoffset = 0, $yoffset = 0)
    {
        $tmpX = $xoffset - $this->currentTD;
        $this->currentTD = $xoffset;

        return sprintf("%.3F %.3F TD", $tmpX, $yoffset);
    }

    public function TF()
    {
        return sprintf(" /%s %.1F Tf", Cpdf_Common::$FontLabel.$this->CURFONT->FontId, $this->fontSize);
    }

    protected function checkDirective($text)
    {
        $tagStart = mb_strpos($text, '<', 0, 'UTF-8');
        if ($tagStart === false) {
            return;
        }

        $tagEnd = mb_strpos($text, '>', $tagStart, 'UTF-8');
        $fullTag = mb_substr($text, $tagStart, $tagEnd - $tagStart + 1, 'UTF-8');

        $regex = "/<\/?([cC]:|)(".$this->pages->AllowedTags.")\>/";

        if (!preg_match($regex, $fullTag, $regs)) {
            return;
        }

        $p = explode(':', $regs[2]);
        if (count($p) > 1) {
            $func = $p[0];
            $parameter = $p[1];
        } else {
            $func = $regs[2];
            $parameter = '';
        }

        $isEndTag = 0;
        if (substr($fullTag, 0, 2) == "</") {
            $isEndTag = 1;
        } elseif ($regs[1] == "C:") {
            $isEndTag = 2;
        }
        return array('func' => $func, 'param' => $parameter, 'start'=> $tagStart, 'end' => $tagEnd, 'close' => $isEndTag);
    }

    private function addTextDirectives1(&$text, $width = 0, $justification = 'left', &$wordSpaceAdjust = 0, $first = false)
    {
        $cb = array();
        $len = mb_strlen($text, 'UTF-8');
        $orgLength = $len;

        $i = 0;

        $tmpFontId = $this->CURFONT->FontId;

        $tmpX = $this->BBox[0];

        $lineWidth = 0;
        $TEXTBLOCK = "";
        $part = '';
        $found = null;
        $stack = array();

        while ($i < $len) {
            $textPart = mb_substr($text, $i, $len - $i, 'UTF-8');

            if (!$this->IsCallback) {
                $found = $this->checkDirective($textPart);
            }
            // found a directive
            if (is_array($found)) {
                $strBefore = mb_substr($textPart, 0, $found['start'], 'UTF-8');
                //echo "-- '$strBefore'\n";
                $tmA = $this->CURFONT->getTextLength($this->fontSize, $strBefore, ($width - $lineWidth), $this->angle, $wordSpaceAdjust);
                if ($tmA[2] >= 0) {
                    $lineWidth += $tmA[0];

                    $part = mb_substr($textPart, 0, $tmA[2], 'UTF-8');
                    $tj= $this->Tj($part);

                    if (isset($stack[$found['func']])) {
                        $replace = $this->pages->DoTrigger($this, $found['func'], array('ux'=> $this->BBox[0] + $lineWidth ), $part);
                        if (is_string($replace)) {
                            $tj = " ".$replace;
                        }
                    }

                    $TEXTBLOCK .= $tj;
                    $len = $i + $tmA[2] + $tmA[3];
                    break;
                }

                $lineWidth += $tmA[0];

                if (!$found['close']) {
                    $add = $this->pages->DoCall(
                        $this,
                        $found['func'],
                        array(
                            $this->BBox[0] + $lineWidth, // lower X
                            $this->y + $this->fontDescender, // lower Y
                            0, // upper X
                            $this->y + $this->fontHeight + $this->fontDescender),
                        $found['param']
                    );
                    $tj = $this->Tj($strBefore);

                    $stack[$found['func']] = $found['param'];

                    if (is_string($add) && !empty($add)) {
                        $tj .= $add;
                    }
                } else {
                    if ($found['close'] == 2) {
                        $this->pages->DoCall(
                            $this,
                            $found['func'],
                            array(
                                $this->BBox[0] + $lineWidth, // lower X
                                $this->y + $this->fontDescender, // lower Y
                                0, // upper X
                                $this->y + $this->fontHeight + $this->fontDescender),
                            $found['param']
                        );
                    }
                    $replace = $this->pages->DoTrigger($this, $found['func'], array('ux'=> $this->BBox[0] + $lineWidth ), $strBefore);
                    unset($stack[$found['func']]);
                    if (is_string($replace)) {
                        $tj = " ".$replace;
                    } else {
                        $tj = $this->Tj($strBefore);
                    }
                }

                $TEXTBLOCK .= $tj;
                if ($found['close'] && $tmA[2] >= 0) {
                    $tj= $this->Tj(mb_substr($strBefore, 0, $tmA[2], 'UTF-8'));

                    $TEXTBLOCK .= $tj;
                    $len = $i + $tmA[2] + $tmA[3];
                    break;
                }

                $i += $found['end'] + 1;
            } else {
                $tm = $this->CURFONT->getTextLength($this->fontSize, $textPart, ($width - $lineWidth), $this->angle, $wordSpaceAdjust);
                //print_r($tm);
                if ($tm[2] >= 0) {
                    $lineWidth += $tm[0];
                    $part = mb_substr($textPart, 0, $tm[2], 'UTF-8');
                    $tj= $this->Tj($part);

                    $TEXTBLOCK .= $tj;

                    $len = $i + $tm[2] + $tm[3];
                    break;
                }

                $lineWidth += $tm[0];

                $tj = $this->Tj($textPart);

                $TEXTBLOCK .= $tj;
                $i = $len;
            }
        }

        $reopenCallbacks = '';
        if (count($stack) > 0 && $len < $orgLength) {
            foreach ($stack as $k => $v) {
                if (!empty($v)) {
                    $reopenCallbacks .= "<$k:$v>";
                } else {
                    $reopenCallbacks .= "<$k>";
                }
            }
        }

        $ws = '';
        if ($i < $len && $justification == 'full') {
            $this->justifyLine1($TEXTBLOCK, $lineWidth, $width, $justification, $wordSpaceAdjust);
            if (!$this->CURFONT->IsUnicode) {
                if ($wordSpaceAdjust > 0) {
                    $ws = sprintf("%.3F Tw", $wordSpaceAdjust);
                }
            }
        } elseif ($justification != 'full') {
            $this->justifyLine1($TEXTBLOCK, $lineWidth, $width, $justification, $wordSpaceAdjust);
        }

        if (!$this->IsCallback) {
            $this->pages->Callback($this->BBox[0] - $tmpX);
        }

        if (!$this->CURFONT->IsUnicode && $i == $len && $justification == 'full') {
            $ws = sprintf("%.3F Tw", 0);
        }

        // font and font size
        $TEXTBLOCK = sprintf(" /%s %.1F Tf %s", Cpdf_Common::$FontLabel.$tmpFontId, $this->fontSize, $ws) . $TEXTBLOCK;

        // begin text start tag
        $TEXTBLOCK = sprintf("\nBT %.3F %.3F Td", $this->BBox[0], $this->y) . $TEXTBLOCK;

        // recover some properties which have been used
        $this->BBox[0] = $tmpX;
        $this->currentTD = 0;
        // reset the font

        // end text tag
        $TEXTBLOCK.= " ET";

        $this->contents .= $TEXTBLOCK;

        return $reopenCallbacks.mb_substr($text, $len, $orgLength, 'UTF-8');
    }

    public function Callback($bbox)
    {
        Cpdf_Common::DEBUG("-- ".count($this->callbackObjects)." CallbackObjects | STEPS : ".$this->CallbackNo, Cpdf_Common::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

        if ($this->IsCallback && count($this->callbackObjects) > 0) {
            for ($i = 0; $i < $this->CallbackNo; $i++) {
                $cbObject = array_shift($this->callbackObjects);
                if (is_object($cbObject)) {
                    $class_name = get_class($cbObject);

                    switch ($class_name) {
                        case 'Cpdf_Graphics':
                            if ($this->JustifyCallback) {
                                $cbObject->X = $bbox[0];
                                $cbObject->Y = $bbox[1];
                            }

                            $this->contents.= "\n".$cbObject->Output();
                            break;
                        case 'Cpdf_Color':
                            $this->contents.= "\n".$cbObject->Output(false, true);
                            break;
                    }
                }
            }

            return (!count($this->callbackObjects))?true:false;
        }
        return false;
    }
}

class Cpdf_Table extends Cpdf_Appearance
{

    const DRAWLINE_ALL = 31;
    const DRAWLINE_DEFAULT = 29;
    const DRAWLINE_TABLE = 24;
    const DRAWLINE_TABLE_H = 16;
    const DRAWLINE_TABLE_V = 8;
    const DRAWLINE_HEADERROW = 4;
    const DRAWLINE_ROW = 2;
    const DRAWLINE_COLUMN = 1;

    public $Fit = true;
    public $DrawLine;

    private $columnWidths;

    private $numCells;

    private $cellIndex = 0;
    private $rowIndex = 0;
    private $pageBreak;

    private $maxCellY;
    private $pageBreakCells;

    private $columnStyle;

    private $lineStyle;
    private $lineWeight;
    private $backgroundColor;

    private $app;

    public function __construct(&$pages, $bbox = array(), $nColumns = 2, $bgColor = array(), $lineStyle = null, $drawLines = Cpdf_Table::DRAWLINE_TABLE)
    {
        parent::__construct($pages, $bbox, '');

        $this->backgroundColor = $bgColor;

        $this->BreakPage = Cpdf_Content::PB_CELL | Cpdf_Content::PB_BBOX;
        $this->resizeBBox = true;

        $this->pageBreakCells = array();
        $this->columnStyle = array();
        $this->DrawLine = $drawLines;

        $this->numCells = $nColumns;
        $this->SetColumnWidths();

        $this->lineWeight = 0;
        if (is_object($lineStyle)) {
            $this->lineStyle = $lineStyle;
            $this->lineWeight = $lineStyle->GetWeight();
        }

        // reset font color
        $this->AddColor(0, 0, 0);
        // set default font

        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_TABLE)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q";
        }

        $this->BBox[1] = $this->BBox[3];

        $this->app = $pages->NewAppearance($this->initialBBox);
        $this->app->ZIndex = -5;
    }

    /**
     * set the width for each column
     */
    public function SetColumnWidths()
    {
        $this->columnWidths = array();

        $widths = func_get_args();

        $maxWidth = ($this->BBox[2] - $this->BBox[0]);

        if (count($widths) > 0) {
            $usedWidth = 0;
            $j = 0;
            for ($i=0; $i < $this->numCells; $i++) {
                if (isset($widths[$i])) {
                    $this->columnWidths[$i] = $widths[$i];
                    $usedWidth += $widths[$i];
                    $j++;
                }
            }

            $restColumns = $this->numCells - $j;
            if ($restColumns > 0) {
                $restWidth = $maxWidth - $usedWidth;
                $restPerCell = $restWidth / $restColumns;

                for ($i=0; $i < $this->numCells; $i++) {
                    if (!isset($this->columnWidths[$i])) {
                        $this->columnWidths[$i] = $restPerCell;
                    }
                }
            }
        } else {
            // calculate the cell max width (incl. border weight)
            $cellWidth = $maxWidth / $this->numCells;

            if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)) {
                $cellWidth -= $this->lineWeight / 2;
            }

            foreach (range(0, ($this->numCells - 1)) as $v) {
                $this->columnWidths[$v] = $cellWidth;
            }
        }
    }

    private function getHalfLineWeight($drawMode = 0)
    {
        if ($this->getLineWeight($drawMode) > 0) {
            return ($this->lineWeight / 2);
        }
        return 0;
    }

    private function getLineWeight($drawMode = 0)
    {
        if (!isset($drawMode)) {
            $drawMode = $this->DrawLine;
        }
        if (Cpdf_Common::IsDefined($this->DrawLine, $drawMode)) {
            return $this->lineWeight;
        }
        return 0;
    }

    public function AddCell($text, $justify = 'left', $backgroundColor = array(), $padding = array())
    {
        $paddingBBox = $this->BBox;

        if (isset($padding['top'])) {
            Cpdf_Common::SetBBox(array('adduy' => -$padding['top']), $paddingBBox);
        }

        if (isset($padding['bottom'])) {
            Cpdf_Common::SetBBox(array('addly' => -$padding['bottom']), $paddingBBox);
        }

        if (!isset($this->CURFONT)) {
            $this->SetFont("Helvetica");
        }

        if (!isset($this->maxCellY)) {
            $this->y += $this->fontHeight + $this->fontDescender - $this->fontDescender;
        }

        $this->y = $paddingBBox[3];
        $this->y -= $this->fontHeight + $this->fontDescender;

        // to recover the column style on page break, store it globally
        $this->columnStyle[$this->cellIndex] = array('justify' => $justify,'backgroundColor'=>$backgroundColor, 'padding'=>$padding);

        // force page break before writting any text content as it does not fit to the current font size
        if ($this->y < $this->initialBBox[1] && Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_CELL)) {
            $this->pageBreak = true;
            $this->pageBreakCells[$this->cellIndex] = $text;
            $this->cellIndex++;
            if ($this->cellIndex >= $this->numCells) {
                $this->endRow(true);
            }
            return;
        }

        //$this->x = $this->BBox[0];
        $this->BBox[2] = $this->BBox[0] + $this->columnWidths[$this->cellIndex];

        $lw = $this->getLineWeight();
        // amend the margin to display table border completely
        if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)) {
            if ($this->cellIndex == 0) {
                Cpdf_Common::SetBBox(array('addlx'=> $lw), $this->BBox);
            } elseif ($this->cellIndex + 1 >= $this->numCells) {
                Cpdf_Common::SetBBox(array('addux'=> -$lw), $this->BBox);
            }
        }

        // some text offset if column line is shown
        if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_COLUMN) && $this->cellIndex + 1 < $this->numCells) {
            Cpdf_Common::SetBBox(array('addux'=> -$lw), $this->BBox);
        }

        $p = $this->AddText($text, 0, $justify);

        // recover BBox UX when column line has been printed
        if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_COLUMN) && $this->cellIndex + 1 < $this->numCells) {
            Cpdf_Common::SetBBox(array('addux'=> $lw), $this->BBox);
        }

        if (isset($p)) {
            $t = substr($text, $p);
            if (!empty($t)) {
                $this->pageBreak = true;

                $this->pageBreakCells[$this->cellIndex] = $t;
            }
        }

        if (!isset($this->maxCellY) || $paddingBBox[1] < $this->maxCellY) {
            $this->maxCellY = $paddingBBox[1];
        }

        $this->cellIndex++;
        if ($this->cellIndex >= $this->numCells) {
            $this->endRow();
        } else {
            //$this->y = $this->BBox[3] - $this->fontDescender;
            $this->BBox[0] = $this->BBox[2]; //$this->columnWidths[$this->cellIndex - 1];
        }

    }

    private function endRow($endOfTable = false)
    {
        // a bit more space between rows when line is shown
        if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_ROW)) {
            $this->maxCellY -= $this->getLineWeight();
        }

        $maxCellY = $this->maxCellY + $this->fontDescender;

        // reset cell counter
        $this->cellIndex = 0;

        $this->parsedRowIndex = $this->rowIndex;

        // increase the row number
        if (!$endOfTable) {
            $this->rowIndex++;
        }

        // reset x position
        $this->BBox[0] = $this->initialBBox[0];

        if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_ROWS)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], ($maxCellY) - $this->BBox[3])." S Q % DEBUG OUTPUT";
        }

        // draw the row border
        if (!$endOfTable && ( (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_ROW) && $this->rowIndex > 2) || Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_HEADERROW) && $this->rowIndex == 2)) {
            $tmp = $this->app->BBox;

            //$offset = $this->BBox[1] - $this->BBox[3];
            $width = $this->BBox[2] - $this->BBox[0];

            $this->app->BBox = $this->BBox;

            $this->app->AddLine(0, 0, $width, 0, null);
            $this->app->BBox = $tmp;
        }

        // draw cell background color
        if (!$endOfTable) {
            $cellBBox = $this->BBox;
            $cellxstart = $cellBBox[0] + $this->getHalfLineWeight(Cpdf_Table::DRAWLINE_TABLE_V);

            for ($i=0; $i < $this->numCells; $i++) {
                $cellxend = $cellxstart + $this->columnWidths[$i];

                $columnStyle = &$this->columnStyle[$i];

                if (is_array($columnStyle['backgroundColor']) && count($columnStyle['backgroundColor']) >= 3) {
                    if ($i + 1 == $this->numCells) {
                        $cellxend -= $this->getLineWeight(Cpdf_Table::DRAWLINE_TABLE_V);
                    }

                    $this->pages->DoCall($this, 'background', $this->BBox, $this->columnStyle[$i]);
                    Cpdf_Common::SetBBox(array( 'ly'=> $maxCellY,
                                                'lx' => $cellxstart,
                                                'ux' => $cellxend
                                            ), $cellBBox);
                    $this->pages->DoTrigger($this, 'background', $cellBBox);
                }
                $cellxstart = $cellxend;
            }
        }

        // draw the column border
        if (!$endOfTable && Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_COLUMN)) {
            $tmp = $this->app->BBox;
            $this->app->BBox = $this->BBox;
            $height = $this->getFontDescender() - $this->getFontHeight();

            $nx = 0;
            for ($i=0; $i < ($this->numCells - 1); $i++) {
                $nx += $this->columnWidths[$i];
                $this->app->AddLine($nx, 0, 0, $height, null);
            }
            $this->app->BBox = $tmp;
        }

        $this->pages->Callback(0, 0, true);

        // set the Y position for the next row
        $this->BBox[3] = $maxCellY;
        // XXX: is this correct? or plus fontDescender?
        $this->y = $maxCellY;

        // if its a page break - set in AddCell method
        if ($this->pageBreak) {
            $bbox = $this->setBackground();

            $obj = Cpdf_Common::DoClone($this);
            $this->pages->addObject($obj, true);

            $this->contents = '';

            $this->BBox = $this->initialBBox;

            $p = $this->pages->NewPage($this->page->Mediabox);
            $this->page = $p;

            if (Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_BLEEDBOX)) {
                $this->initialBBox[1] = $this->page->Bleedbox[1];
                $this->initialBBox[3] = $this->page->Bleedbox[3];
                $this->BBox[3] = $this->initialBBox[3];
                $this->BBox[1] = $this->initialBBox[1];
            }

            if (Cpdf_Common::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf_Common::DEBUG_TABLE)) {
                $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q % DEBUG OUTPUT";
            }

            $this->BBox[3] = $this->initialBBox[3];

            // force to rsize the BBox
            $this->BBox[1] = $this->BBox[3];

            $this->BBox[0] = $this->initialBBox[0];
            $this->y = $this->BBox[3] - $this->fontDescender;

            $this->maxCellY = $this->BBox[3];

            $this->app = $this->pages->NewAppearance($this->initialBBox);
            $this->app->ZIndex = -5;

            $this->pageBreak = false;

            if (count($this->pageBreakCells) > 0) {
                $pcells = $this->pageBreakCells;
                $this->pageBreakCells= array();

                for ($i = 0; $i < $this->numCells; $i++) {
                    $columnStyle = &$this->columnStyle[$i];
                    if (isset($pcells[$i])) {
                        $this->AddCell($pcells[$i], $columnStyle['justify'], $columnStyle['backgroundColor'], $columnStyle['padding']);
                    } else {
                        $this->AddCell("", $columnStyle['justify'], $columnStyle['backgroundColor'], $columnStyle['padding']);
                    }
                }
            }
        }
    }

    private function setBackground()
    {
        $bbox = $this->initialBBox;
        if (!isset($this->app)) {
            return $bbox;
        }

        $this->app->SetPageMode($this->Paging);

        $filled = false;
        if (is_array($this->backgroundColor) && count($this->backgroundColor) == 3) {
            $filled = true;
            $this->app->AddColor($this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
        }
        // width and height can be set to zero as it will use the BBox to calculate max widht and max height
        if ($this->Fit) {
            $newY = $this->maxCellY - $this->initialBBox[1] - ($this->lineWeight / 2) + $this->fontDescender;
            $height = $this->initialBBox[3] - $this->maxCellY + $this->lineWeight - $this->fontDescender;

            if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)) {
                $this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, $this->lineStyle);
            } elseif ($filled) {
                $this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, null);
            }
            $bbox[1] = $this->BBox[1] + $this->fontDescender;
        } else {
            if (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)) {
                $this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
            } elseif ($filled) {
                $this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
            }
        }

        $tmp = $this->app->BBox;

        return $bbox;
    }

    /**
     * End the table and return bounding box to define next Appearance or text object
     */
    public function EndTable()
    {
        $this->pageBreakCells = array();
        $this->endRow(true);

        $bbox = $this->setBackground();
        $this->x = $bbox[0];
        $this->y = $bbox[1];
    }
}

/**
 * Class object to provide Annotations, like Links, text and freetext
 *
 * TODO: Audio and video annotations
 */
class Cpdf_Annotation extends Cpdf_Content
{
    public $Type = '/Annot';

    private $annotation;

    private $rectangle;
    private $border;
    private $color;

    private $title;

    private $target;

    private $url;

    private $flags;

    /**
     * three possible appearances
     * Normal (required)
     * Rollover (optional)
     * Down (optional)
     *
     * This will overwrite the default appearance, if set!
     */
    public $Appearances;

    public function __construct(&$page, $annoType, $rectangle, $border = null, $color = null, $flags = array())
    {
        parent::__construct($page);

        $this->annotation = strtolower($annoType);
        $this->rectangle = $rectangle;
        $this->color = $color;
        $this->border = $border;

        $f = 0;
        // set bitflags for annotation properties
        if (is_array($flags)) {
            foreach ($flags as $v) {
                switch (strtolower($v)) {
                    case 'invisible':
                        $f += 1;
                        break;
                    case 'hidden':
                        $f += 2;
                        break;
                    case 'print':
                        $f += 4;
                        break;
                    case 'nozoom':
                        $f += 8;
                        break;
                    case 'norotate':
                        $f += 16;
                        break;
                    case 'noview':
                        $f += 32;
                        break;
                    case 'readonly':
                        $f += 64;
                        break;
                }
            }
        }
        $this->flags = $f;
    }

    public function GetBBox()
    {
        return $this->rectangle;
    }

    public function SetText($text, $title = '')
    {
        $this->contents = $text;
        $this->title = $title;
    }

    public function SetUrl($url)
    {
        $this->url = $url;
    }

    public function Callback($bbox)
    {
        $this->rectangle = $bbox;
    }

    /**
     * set the annotation to an internal destination either as name or page number
     * Name requires to hace Cpdf_Content->Name set to a unqiue string value
     *
     * @param mixed destination name or page number
     */
    public function SetDestination($targetName)
    {
        $this->target = $targetName;
    }
    /**
     * Used to set a different design for the current annotation
     * Build custom appearances between openAppearance() and closeAppearance()
     * openAppearance() returns the required object Id used in the parameters below
     *
     * TODO: The appearance is overlapping on FreeText annot and does not stay - tested on mac previewer
     *
     * @param int $normal the normal appearance
     * @param int $rollover Object Id of the roolover appearance
     * @param int $down Object Id of the down behavior appearance
     */
    public function SetAppearance(&$normal, &$rollover = null, &$down = null)
    {
        $this->Appearances = array('N'=>$normal, 'R'=>$rollover, 'D'=>$down);
    }

    public function Output($noKey = false)
    {
        $res='';
        if (is_array($this->rectangle) && count($this->rectangle) == 4) {
            $res.=' /Type '.$this->Type;
            switch ($this->annotation) {
                case 'link':
                    // plus default highlight mode 'Invert' - see PDF 1.3 reference on page 501
                    $res.=' /Subtype /Link /H /I';
                    break;
                default:
                case 'text':
                    $res.=' /Subtype /Text';
                    break;
                case 'freetext':
                    $res.=' /Subtype /FreeText';
                    // put default appearance for freetext, even when its empty
                    //$res.=' /DA ('.$this->defaultApperance.')';
                    break;
            }

            $res.= ' /Rect [ ';
            foreach ($this->rectangle as $v) {
                $res.= sprintf("%.4F ", $v);
            }
            $res.=']';

            // its an external url or an internal destination link
            if (!empty($this->url)) {
                $res.= ' /A << /S /URI /URI ('.$this->url.') >>';
            } elseif (($pageNum=intval($this->target)) > 0) {
                $page = $this->pages->GetPageByNo($pageNum);
                if (is_object($page)) {
                    $res.=' /Dest ['.$page->ObjectId.' 0 R /Fit]';
                }
            } elseif (!empty($this->target)) {
                $res.=' /Dest /'.$this->target;
            }

            if (!empty($this->title)) {
                $res.=' /T ('.$this->title.')';
            }

            if (!empty($this->contents)) {
                $res.=' /Contents ('.$this->contents.')';
            }

            // set the color via object class Cpdf_Color
            if (is_object($this->color)) {
                $c = $this->color;
                $res.=' /C '.$c->Output();
            }

            // PDF-1.1 hide the old border
            $res.=' /Border [0 0 0]';
            // set the border style via object class Cpdf_BorderStyle
            if (is_object($this->border)) {
                $b = $this->border;
                $res.= ' /BS <<'.$b->Output().' >>';
            }

            // put the AP (appearance streams) as reference into the annot
            if (isset($this->Appearances)) {
                $res.= ' /AP <<';
                foreach ($this->Appearances as $k => $v) {
                    if (isset($v)) {
                        $res.= " /$k ".$v->ObjectId." 0 R";
                    }
                }
                $res.=' >>';
            }

            if ($this->flags > 0) {
                $res.= ' /F '.$this->flags;
            }
        } else {
            Cpdf_Common::DEBUG("Invalid ractangle - array must contain 4 elements", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }
        return $res;
    }

    public function OutputAsObject()
    {
        $res = "\n$this->ObjectId 0 obj\n<< ".$this->Output(true)." >>\nendobj";
        $this->page->pages->AddXRef($this->ObjectId, strlen($res));
        return $res;
    }
}

/**
 * Class object to support JPEG and PNG images
 * <p>
 * Example usage:
 * </p>
 * <pre>
 * $pdf = new Cpdf(Cpdf_Common::$Layout['A4']);
 * $app = $pdf->NewAppearance();
 * $app->AddImage('left', 'middle', 'images/test_indexed.png');
 * $pdf->Stream();
 * </pre>
 */
class Cpdf_Image extends Cpdf_Content
{
    public $ImageNum;

    private $source;

    private $channels;
    private $bits;

    private $colorspace;

    private $numColors;

    private $data;
    /**
     * Used for PNG only
     */
    private $palette;
    /**
     * Used for PNG only
     */
    private $transparency;

    protected $orgWidth;
    protected $orgHeight;
    public $ImageType;

    public $Width;
    public $Height;

    /**
     * Constructor
     *
     * @param Cpdf_Pages $pages object of the main pdf_Pages object
     * @param string $filepath can be either a file or an url path of an image
     */
    public function __construct(&$pages, $filepath)
    {
        parent::__construct($pages);

        if (stristr($filepath, '://')) { //copy to temp file
            // PHP5: file_get_contents
            $cont = file_get_contents($filepath);

            $filepath = tempnam($pages->TempPath, "Cpdf_Image");
            $fp2 = @fopen($filepath, "w");
            fwrite($fp2, $cont);
            fclose($fp2);
        }

        if (file_exists($filepath)) {
            $this->source = $filepath;
            $imginfo = getimagesize($filepath);

            $this->orgWidth = $imginfo[0];
            $this->orgHeight = $imginfo[1];
            $this->ImageType = $imginfo[2];

            if (isset($imginfo['channels'])) {
                $this->channels = $imginfo['channels'];
            }

            $this->bits = $imginfo['bits'];

            $this->Width = $this->orgWidth;
            $this->Height = $this->orgHeight;
            $this->parseImage();
        } else {
            Cpdf_Common::DEBUG("Image file could not be found '$filepath'", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }
    }

    /**
     * Resize the image (missing)
     *
     * TODO: Implement resize feature for images by using gdlib or IM?
     */
    public function Resize($width = null, $height = null)
    {
        /*if(isset($width) && !isset($height)){
			$this->Height = $this->orgHeight / $this->orgWidth * $width;
			$this->Width = $width;

		} else if(isset($height) && !isset($width)){
			$this->Width = $this->orgWidth / $this->orgHeight * $height;
			$this->Height = $height;
		} else {
			// or break the ratio and define individual size
			$this->Width = $width;
			$this->Height = $height;
		}*/

        // TODO: recalculate the image using gd library
    }

    /**
     * Parse the image content
     */
    private function parseImage()
    {
        switch ($this->ImageType) {
            case IMAGETYPE_JPEG:
                $this->data = file_get_contents($this->source);

                if ($this->channels == 1) {
                    $this->colorspace = '/DeviceGray';
                } else {
                    $this->colorspace = '/DeviceRGB';
                }

                $entries['Filter'] = '/DCTDecode';

                break;
            case IMAGETYPE_PNG:
                $data = file_get_contents($this->source);

                $iChunk = $this->readPngChunks($data);

                if (!$iChunk['haveHeader']) {
                    Cpdf_Common::DEBUG("Info header missing for PNG image", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                    return;
                }

                if (!isset($iChunk['info'])) {
                    Cpdf_Common::DEBUG("Additional Info missing for PNG image", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                    return;
                }

                if (isset($iChunk['info']['interlaceMethod']) && $iChunk['info']['interlaceMethod']) {
                    Cpdf_Common::DEBUG("No support for interlaces png images for PDF", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                    return;
                }

                if ($iChunk['info']['bitDepth'] > 8) {
                    Cpdf_Common::DEBUG("Only bit depth of 8 or lower is supported for PNG", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                    return;
                }

                if ($iChunk['info']['colorType'] == 1 || $iChunk['info']['colorType'] == 5 || $iChunk['info']['colorType']== 7) {
                    Cpdf_Common::DEBUG("Unsupported  color type for PNG", Cpdf_Common::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
                    return;
                }

                switch ($iChunk['info']['colorType']) {
                    case 3:
                        $this->colorspace = 'DeviceRGB';
                        $this->numColors = 1;
                        break;
                    case 6:
                    case 2:
                        $this->colorspace = 'DeviceRGB';
                        $this->numColors = 3;
                        break;
                    case 4:
                    case 0:
                        $this->colorspace = 'DeviceGray';
                        $this->numColors = 1;
                        break;
                }
                //print_r($iChunk);
                $this->data = $iChunk['idata'];
                $this->palette = $iChunk['pdata'];
                $this->transparency = $iChunk['transparency'];

                break;
            case IMAGETYPE_GIF:
                break;
            default:
                Cpdf_Common::DEBUG("Unsupported image type", Cpdf_Common::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                break;
        }
    }

    /**
     * Extract $num of bytes from $pos
     *
     * @access private
     */
    private function getBytes(&$data, $pos, $num)
    {
        // return the integer represented by $num bytes from $pos within $data
        $ret=0;
        for ($i=0; $i<$num; $i++) {
            $ret=$ret*256;
            $ret+=ord($data[$pos+$i]);
        }
        return $ret;
    }

    /**
     * Read the PNG chunk
     *
     * @param $data - binary part of the png image
     * @access private
     */
    private function readPngChunks(&$data)
    {
        $default = array('info'=> array(), 'transparency'=> null, 'idata'=> null, 'pdata'=> null, 'haveHeader'=> false);
        // set pointer
        $p = 8;
        $len = strlen($data);
        // cycle through the file, identifying chunks
        while ($p<$len) {
            $chunkLen = $this->getBytes($data, $p, 4);
            $chunkType = substr($data, $p+4, 4);

            switch ($chunkType) {
                case 'IHDR':
                //this is where all the file information comes from
                    $default['info']['width']=$this->getBytes($data, $p+8, 4);
                    $default['info']['height']=$this->getBytes($data, $p+12, 4);
                    $default['info']['bitDepth']=ord($data[$p+16]);
                    $default['info']['colorType']=ord($data[$p+17]);
                    $default['info']['compressionMethod']=ord($data[$p+18]);
                    $default['info']['filterMethod']=ord($data[$p+19]);
                    $default['info']['interlaceMethod']=ord($data[$p+20]);

                    $default['haveHeader'] = true;

                    if ($default['info']['compressionMethod']!=0) {
                        Cpdf_Common::DEBUG("unsupported compression method for PNG image", Cpdf_Common::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                    }
                    if ($default['info']['filterMethod']!=0) {
                        Cpdf_Common::DEBUG("unsupported filter method for PNG image", Cpdf_Common::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                    }

                    $default['transparency'] = array('type'=> null, 'data' => null);

                    if ($default['info']['colorType'] == 3) { // indexed color, rbg
                        // corresponding to entries in the plte chunk
                        // Alpha for palette index 0: 1 byte
                        // Alpha for palette index 1: 1 byte
                        // ...etc...

                        // there will be one entry for each palette entry. up until the last non-opaque entry.
                        // set up an array, stretching over all palette entries which will be o (opaque) or 1 (transparent)
                        $default['transparency']['type']='indexed';
                        //$numPalette = strlen($default['pdata'])/3;
                        $trans=0;
                        for ($i=$chunkLen; $i>=0; $i--) {
                            if (ord($data[$p+8+$i])==0) {
                                $trans=$i;
                            }
                        }
                        $default['transparency']['data'] = $trans;
                    } elseif ($default['info']['colorType'] == 0) { // grayscale
                        // corresponding to entries in the plte chunk
                        // Gray: 2 bytes, range 0 .. (2^bitdepth)-1

                        // $transparency['grayscale']=$this->getBytes($data,$p+8,2); // g = grayscale
                        $default['transparency']['type']='indexed';
                        $default['transparency']['data'] = ord($data[$p+8+1]);
                    } elseif ($default['info']['colorType'] == 2) { // truecolor
                        // corresponding to entries in the plte chunk
                        // Red: 2 bytes, range 0 .. (2^bitdepth)-1
                        // Green: 2 bytes, range 0 .. (2^bitdepth)-1
                        // Blue: 2 bytes, range 0 .. (2^bitdepth)-1
                        $default['transparency']['r']=$this->getBytes($data, $p+8, 2); // r from truecolor
                        $default['transparency']['g']=$this->getBytes($data, $p+10, 2); // g from truecolor
                        $default['transparency']['b']=$this->getBytes($data, $p+12, 2); // b from truecolor
                    } elseif ($default['info']['colorType'] == 6 || $default['info']['colorType'] == 4) {
                        // set transparency type to "alpha" and proceed with it in $this->o_image later
                        $default['transparency']['type'] = 'alpha';

                        $img = imagecreatefromstring($data);

                        $imgalpha = imagecreate($default['info']['width'], $default['info']['height']);
                        // generate gray scale palette (0 -> 255)
                        for ($c = 0; $c < 256; ++$c) {
                            ImageColorAllocate($imgalpha, $c, $c, $c);
                        }
                        // extract alpha channel
                        for ($xpx = 0; $xpx < $default['info']['width']; ++$xpx) {
                            for ($ypx = 0; $ypx < $default['info']['height']; ++$ypx) {
                                $colorBits = imagecolorat($img, $xpx, $ypx);
                                $color = imagecolorsforindex($img, $colorBits);
                                $color['alpha'] = (((127 - $color['alpha']) / 127) * 255);
                                imagesetpixel($imgalpha, $xpx, $ypx, $color['alpha']);
                            }
                        }
                        $tmpfile_alpha=tempnam(Cpdf_Common::$TempPath, 'Cpdf_Image');

                        imagepng($imgalpha, $tmpfile_alpha);
                        imagedestroy($imgalpha);

                        $alphaData = file_get_contents($tmpfile_alpha);
                        // nested method call to receive info on alpha image
                        $alphaImg = $this->readPngChunks($alphaData);
                        // use 'pdate' to fill alpha image as "palette". But it s the alpha channel
                        $default['pdata'] = $alphaImg['idata'];

                        // generate true color image with no alpha channel
                        $tmpfile_tt=tempnam(Cpdf_Common::$TempPath, 'Cpdf_Image');

                        $imgplain = imagecreatetruecolor($default['info']['width'], $default['info']['height']);
                        imagecopy($imgplain, $img, 0, 0, 0, 0, $default['info']['width'], $default['info']['height']);
                        imagepng($imgplain, $tmpfile_tt);
                        imagedestroy($imgplain);

                        $ttData = file_get_contents($tmpfile_tt);
                        $ttImg = $this->readPngChunks($ttData);

                        $default['idata'] = $ttImg['idata'];

                        // remove temp files
                        unlink($tmpfile_alpha);
                        unlink($tmpfile_tt);
                        // return to addPngImage prematurely. IDAT has already been read and PLTE is not required
                        return $default;
                    }
                    break;
                case 'PLTE':
                    $default['pdata'] = substr($data, $p+8, $chunkLen);
                    break;
                case 'IDAT':
                    $default['idata'] .= substr($data, $p+8, $chunkLen);
                    break;
                case 'tRNS': // this HEADER info is optional. More info: rfc2083 (http://tools.ietf.org/html/rfc2083)
                    // this chunk can only occur once and it must occur after the PLTE chunk and before IDAT chunk
                    // KS End new code
                    break;
                default:
                    break;
            }
            $p += $chunkLen+12;
        }


        return $default;
    }

    /**
     * PDF Output of the Image
     */
    public function OutputAsObject()
    {

        $res = "\n$this->ObjectId 0 obj";
        $res.="\n<< /Subtype /Image";

        $this->AddEntry('Width', $this->orgWidth);
        $this->AddEntry('Height', $this->orgHeight);

        $paletteObj = null;

        switch ($this->ImageType) {
            case IMAGETYPE_JPEG:
                if ($this->channels == 1) {
                    $this->AddEntry('ColorSpace', '/DeviceGray');
                } else {
                    $this->AddEntry('ColorSpace', '/DeviceRGB');
                }
                $this->AddEntry('Filter', '/DCTDecode');
                $this->AddEntry('BitsPerComponent', $this->bits);
                break;
            case IMAGETYPE_PNG:
                if (strlen($this->palette)) {
                    $paletteObj = new Cpdf_Content($this->pages);
                    $paletteObj->ObjectId = ++$this->pages->objectNum;

                    $paletteObj->AddRaw($this->palette);
                    // do not compress the palette as it already is compressed
                    // when palette is used as alpha channel fir indexed PNG, ignore the compression
                    $paletteObj->Compression = 0;

                    $paletteObj->AddEntry('Subtype', '/Image');
                    $paletteObj->AddEntry('Width', $this->orgWidth);
                    $paletteObj->AddEntry('Height', $this->orgHeight);

                    $paletteObj->AddEntry('ColorSpace', '/DeviceGray');
                    $paletteObj->AddEntry('BitsPerComponent', $this->bits);
                    $paletteObj->AddEntry('DecodeParms', '<< /Predictor 15 /Colors 1 /BitsPerComponent '.$this->bits.' /Columns '.$this->orgWidth.' >>');

                    if (isset($this->transparency)) {
                        switch ($this->transparency['type']) {
                            case 'indexed':
                                // disable transparancy on indexed PNGs for time being
                                //$tmp=' ['.$this->transparency['data'].' '.$this->transparency['data'].'] ';
                                //$this->AddEntry('Mask', $tmp);
                                $this->AddEntry('ColorSpace', '[/Indexed /DeviceRGB '.(strlen($this->palette)/3-1).' '.$paletteObj->ObjectId.' 0 R]');
                                break;
                            case 'alpha':
                                $paletteObj->AddEntry('Filter', '/FlateDecode');
                                $this->AddEntry('SMask', $paletteObj->ObjectId.' 0 R');
                                $this->AddEntry('ColorSpace', '/'.$this->colorspace);
                                break;
                        }
                    }
                } else {
                    $this->AddEntry('ColorSpace', '/'.$this->colorspace);
                }

                $this->AddEntry('BitsPerComponent', $this->bits);
                $this->AddEntry('Filter', '/FlateDecode');
                $this->AddEntry('DecodeParms', '<< /Predictor 15 /Colors '.$this->numColors.' /Columns '.$this->orgWidth.' /BitsPerComponent '.$this->bits.'>>');
                break;
        }

        $tmp = $this->data;
        // gzcompress
        if (function_exists('gzcompress') && $this->Compression && $this->ImageType != IMAGETYPE_PNG) {
            if (isset($this->entries['Filter'])) {
                $this->AddEntry('Filter', '[/FlateDecode '.$this->entries['Filter'].']');
            } else {
                $this->AddEntry('Filter', '/FlateDecode');
            }
            $tmp = gzcompress($tmp, $this->Compression);
        }
        // encryption
        if (isset($this->page->pages->encryptionObject)) {
            $encObj = &$this->page->pages->encryptionObject;
            $encObj->encryptInit($this->ObjectId);
            $tmp = $encObj->ARC4($tmp);
        }

        foreach ($this->entries as $k => $v) {
            $res.= " /$k $v";
        }
        $res.=' /Length '.strlen($tmp).' >>';
        $res.= "\nstream\n".$tmp."\nendstream";
        $res.= "\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        if (is_object($paletteObj)) {
            $res.= $paletteObj->OutputAsObject();
        }
        return $res;
    }
}

/**
 * Color class object for RGB and CYMK
 */
class Cpdf_Color
{
    public $colorArray;
    public $stroke;

    public function __construct($color = array(), $stroke = true)
    {
        $this->colorArray = $color;
        $this->stroke = $stroke;

        if (Cpdf::$ForceCMYK) {
            $this->rgb2cmyk();
        }
    }

    private function rgb2cmyk()
    {
        if (is_array($this->colorArray) && count($this->colorArray) == 3) {
            $tmp = $this->colorArray;
            // cyan (c)
            $c = 1.0 - $tmp[0];
            $m = 1.0 - $tmp[1];
            $y = 1.0 - $tmp[2];
            $k = min($c, $m, $y);

            $UCR = $k;
            $BG = $k;

            $this->colorArray[0] = min(1.0, max(0.0, $c - $UCR));
            // magenty (m)
            $this->colorArray[1] = min(1.0, max(0.0, $m - $UCR));
            // yellow (y)
            $this->colorArray[2] = min(1.0, max(0.0, $y - $UCR));
            // black (k)
            $this->colorArray[3] = min(1.0, max(0.0, $UCR));
        }
    }

    /**
     * PDF output of the color
     */
    public function Output($asArray = true, $withColorspace = false)
    {
        $res='';

        if (is_array($this->colorArray)) {
            foreach ($this->colorArray as $v) {
                $res.= sprintf("%.3F ", $v);
            }

            if ($withColorspace) {
                if (count($this->colorArray) >= 4) { // DeviceCMYK
                    $res.= ($this->stroke)?'K':'k';
                } elseif (count($this->colorArray) >= 3) { // DeviceRGB
                    $res.= ($this->stroke)?'RG':'rg';
                } else {
                    $res.= ($this->stroke)?'G':'g';
                }
            }
        } else {
            $res = '0';
        }
        $res = ($asArray)?'['.$res.']':$res;
        return $res;
    }
}

/**
 * PDF border style object used in annotations
 */
class Cpdf_BorderStyle
{
    /**
     * static type name used in PDF object
     */
    public $Type = '/Border';
    /**
     * Width of the border in points
     * @default number 1
     */
    public $Width;
    /**
     * Style of the border
     * @default string 'S'
     */
    public $Style;
    /**
     * dash arrays
     */
    public $dashArray = array();

    /**
     * Borderstyle used in Annotations. Can be shown differently dependent on the PDF Viewer
     *
     * @param float $weight define the weight of the border
     * @param string $style define a style type - 'solid', 'dash', 'underline' or 'bevel'
     * @param array $dashArray used to define the gaps of a dashed line
     */
    public function __construct($weight = 0, $style = 'solid', $dashArray = array())
    {
        $this->Weight = $weight;
        $this->Style = $style;
        $this->dashArray = $dashArray;
    }

    /**
     * PDF output of the border style
     */
    public function Output()
    {
        $res='';
        if ($this->Weight > 0 && $this->Style != 'none') {
            $res = " /Type $this->Type /W ".sprintf("%.3F", $this->Weight);
            switch (strtolower($this->Style)) {
                case 'underline':
                case 'underlined':
                    $res .= ' /S /U';
                    break;
                case 'dash':
                    $res .= ' /S /D /D [';
                    if (is_array($this->dashArray) && count($this->dashArray) > 0) {
                        foreach ($this->dashArray as $v) {
                            $res.= sprintf("%d", $v);
                        }
                    } else {
                        $res.='3';
                    }
                    $res.=']';
                    break;
                case 'bevel':
                    $res .= ' /S /B';
                    break;
            }
        }
        return $res;
    }
}

/**
 * Class object allowing the use of lines in any Appearance object
 */
class Cpdf_LineStyle
{
    /**
     * stores the line weight
     * @var float
     */
    private $weight;
    /**
     * stores the cap style
     * @var String
     */
    private $capStyle;
    /**
     * stores the join style
     * @var String
     */
    private $joinStyle;
    /**
     * stores the dash style
     * @var String
     */
    private $dashStyle;

    /**
     * Contructor call
     * @param float $weight line weight
     * @param String $cap cap style, see $this->SetCap()
     * @param String $join join style, see $this->SetJoin()
     * @param Array $dash dash format, see $this->SetDashes();
     */
    public function __construct($weight = 0, $cap = '', $join = '', $dash = array())
    {
        $this->weight = $weight;
        $this->SetCap($cap);
        $this->SetJoin($join);

        if (is_array($dash)) {
            if (count($dash) == 3) {
                $this->SetDashes($dash[0], $dash[1], $dash[2]);
            } elseif (count($dash) == 2) {
                $this->SetDashes($dash[0], $dash[1]);
            } elseif (count($dash) == 1) {
                $this->SetDashes($dash[0], $dash[0]);
            }
        }
    }

    /**
     * get the line weight out of the class object
     */
    public function GetWeight()
    {
        return $this->weight;
    }

    /**
     * Set the cap style of a line
     * @param String $name possible styles are butt, round and square
     */
    public function SetCap($name = 'butt')
    {
        switch ($name) {
            default:
            case 'butt':
                $this->capStyle = 0;
                break;
            case 'round':
                $this->capStyle = 1;
                break;
            case 'square':
                $this->capStyle = 2;
                break;
        }
    }

    /**
     * set the join style of a line
     * @param String $name possible styles are butt, round and bevel
     */
    public function SetJoin($name = 'miter')
    {
        switch ($name) {
            default:
            case 'miter':
                $this->joinStyle = 0;
                break;
            case 'round':
                $this->joinStyle = 1;
                break;
            case 'bevel':
                $this->joinStyle = 2;
                break;
        }
    }

    /**
     * Used to define the line spaces
     * @param int $on
     * @param int $off
     * @param int $phase
     */
    public function SetDashes($on, $off, $phase = 0)
    {
        $this->dashStyle = array($on, $off, $phase);
    }

    /**
     * PDF output of the line style
     */
    public function Output()
    {
        $res = '';

        $res.= sprintf("%.3F w", $this->weight);

        if (isset($this->capStyle)) {
            $res.= ' '.$this->capStyle.' J';
        }
        if (isset($this->joinStyle)) {
            $res.= ' '.$this->joinStyle.' j';
        }
        if (is_array($this->dashStyle) && count($this->dashStyle) == 3) {
            if ($this->dashStyle[0] > 0) {
                $res.= ' ['.$this->dashStyle[0];
                if ($this->dashStyle[1] != $this->dashStyle[0]) {
                    $res.= ' '.$this->dashStyle[1];
                }
                $res.='] '.$this->dashStyle[2].' d';
            }
        } else {
            $res.= ' [] 0 d';
        }
        return $res.' ';
    }
}
