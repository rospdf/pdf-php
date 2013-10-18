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
 * @package	 Cpdf
 * @version  0.13.0 (>=php5)
 * @author   Ole Koeckemann <ole1986@users.sourceforge.net>
 *
 * @copyright 2013 The author(s)
 * @license  GNU General Public License v3
 * @link     http://pdf-php.sf.net
 */
 
// include TTF and TTFsubset classes
set_include_path(dirname(__FILE__).'/include/'. PATH_SEPARATOR . get_include_path());

include_once('TTFsubset.php');

class Cpdf_Common {
	const DEBUG_TEXT = 1;
	const DEBUG_BBOX = 2;
	const DEBUG_TABLE = 4;
	const DEBUG_ROWS = 8;
	const DEBUG_MSG_WARN = 16;
	const DEBUG_MSG_ERR = 48; // DEBUG_MSG_WARN is included
	const DEBUG_OUTPUT = 64;
	const DEBUG_ALL = 127;
	
	public static function DEBUG($msg, $flag, $debugflags){
		if(self::IsDefined($debugflags, $flag)){
			switch ($flag) {
				default:
				case Cpdf_Common::DEBUG_MSG_ERR:
					error_log("[ROSPDF-ERROR] ".$msg);
					break;
				case Cpdf_Common::DEBUG_MSG_WARN:
					error_log("[ROSPDF-WARNING] ".$msg);
					break;
			}
			
		}
	}
	
	/**
	 * stores the default font path, relativly
	 */
	public $FontPath;
	
	/**
	 * temporary path - need to change when using XAMPP
	 */
	public $TempPath = '/tmp';
	
	/**
	 * allowed tags for custom callbacks used in AddText method
	 */
	public $AllowedTags = 'b|strong|i|alink:?.*?|ilink:?.*?';
	
	/**
	 * internal font label prefix 
	 */
	public $FontLabel = 'F';
	
	/**
	 * internal pdf image label prefix
	 */
	public $ImageLabel = 'Im';
	
	/**
	 * Debug level - Use the constants starting with Cpdf_Common::DEBUG_*
	 */
	public $DEBUG = 0;
	
	public $FileIdentifier = '';
	
	/**
	 * Target encoding for non-unicode text output
	 */
	public $TargetEncoding = 'CP1252';
	
	/**
	 * timeout when the font cache expires
	 */
	public $CacheTimeout = '15 minutes';
	
	/**
	 * Compression level (default: -1)
	 * If set to zero (0) compression is disabled 
	 */
	public $Compression = -1;
	
	/**
	 * array of all core fonts
	 */
	public $CoreFonts = array('courier', 'courier-bold', 'courier-oblique', 'courier-boldoblique',
    	'helvetica', 'helvetica-bold', 'helvetica-oblique', 'helvetica-boldoblique',
    	'times-roman', 'times-bold', 'times-italic', 'times-bolditalic',
    	'symbol', 'zapfdingbats');
	
	/**
	 * default font families
	 */
	public $DefaultFontFamily = array(
            'helvetica' => array(
                    'b'=>'Helvetica-Bold',
                    'i'=>'Helvetica-Oblique',
                    'bi'=>'Helvetica-BoldOblique',
                    'ib'=>'Helvetica-BoldOblique',
                ),
            'courier' => array(
                    'b'=>'Courier-Bold',
                    'i'=>'Courier-Oblique',
                    'bi'=>'Courier-BoldOblique',
                    'ib'=>'Courier-BoldOblique',
                ),
            'times-roman' => array(
                    'b'=>'Times-Bold',
                    'i'=>'Times-Italic',
                    'bi'=>'Times-BoldItalic',
                    'ib'=>'Times-BoldItalic',
                )
    );
	
	/**
	 * Some default page layouts
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
     * unicode version of php ord to get the decimal for an utf-8 character
	 * 
	 * @param string $c one character to be converted
	 * 
	 * @return int decimal value of the utf8 character or false on error
     */
    public function uniord($c)
    {
        // important condition to allow char "0" (zero) being converted to decimal
        if(strlen($c) <= 0) return false;
        $ord0 = ord($c{0}); if ($ord0>=0   && $ord0<=127) return $ord0;
        $ord1 = ord($c{1}); if ($ord0>=192 && $ord0<=223) return ($ord0-192)*64 + ($ord1-128);
        $ord2 = ord($c{2}); if ($ord0>=224 && $ord0<=239) return ($ord0-224)*4096 + ($ord1-128)*64 + ($ord2-128);
        $ord3 = ord($c{3}); if ($ord0>=240 && $ord0<=247) return ($ord0-240)*262144 + ($ord1-128)*4096 + ($ord2-128)*64 + ($ord3-128);
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
	public function filterText(&$fontObject, $text, $convert_encoding = true){
		if(isset($fontObject) && $convert_encoding){
			// store all used characters if subset font is set to true
			if($fontObject->IsUnicode){
				$text = mb_convert_encoding($text, 'UTF-16BE','UTF-8');
				
				if($fontObject->SubsetFont){
					for($i = 0; $i < mb_strlen($text,'UTF-16BE'); $i++)
						$fontObject->AddChar( mb_substr($text,$i, 1, 'UTF-16BE') );
				}
			}else {
				// 
				$text = mb_convert_encoding($text, $this->TargetEncoding,'UTF-8');
            	if($fontObject->SubsetFont){
            		for($i = 0; $i < strlen($text); $i++)
						$fontObject->AddChar( $text[$i] );
				}
			}
		}
		
        $text = strtr($text,  array(')' => '\\)', '(' => '\\(', '\\' => '\\\\', chr(8) => '\\b', chr(9) => '\\t', chr(10) => '\\n', chr(12) => '\\f' ,chr(13) => '\\r', '&lt;'=>'<', '&gt;'=>'>', '&amp;'=>'&') );
        return $text;
    }

	/**
	 * Sort order for content references to verify which object has the highest ZIndex
	 * and should be on focus
	 */
	public function compareRefs($a, $b){
		if(isset($a[1]) && !isset($b[1])){
			return 1;
		} else if (!isset($a[1]) && isset($b[1])) {
			return -1;
		} 
		
		if(isset($a[1]) && isset($b[1])){
			return ($a[1] < $b[1]) ? -1 : 1;
		}
		return 0;
	}

	/**
	 * Clone an object - specially used for page breaks or column breaks
	 */
	public function DoClone($object){
		if (version_compare(phpversion(), '5.0') < 0) {
   			return $object;
  		} else {
   		return @clone($object);
  		}
	}
	
	/**
	 * Is an Enum flag defined
	 * 
	 * @param int $value value to be checked for enum
	 * @param int $enum bitwise enum
	 */
	public static function IsDefined($value, $enum){
		return (($value & $enum) == $enum)?true:false;
	}
}

/**
 * Encrypt the PDF document
 * Support for encryption up to PDF version 1.4
 * 
 * TODO: Extend the encryption for PDF 1.4 to use a user defined key length up to 128bit 
 */
class Cpdf_Encryption {
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
	public function __construct(&$pages,$mode, $user = '', $owner = '', $permission = array()){
		$this->pages = &$pages;
		$this->userPass = $user;
		$this->ownerPass = $owner;
		$this->encryptionPad = chr(0x28).chr(0xBF).chr(0x4E).chr(0x5E).chr(0x4E).chr(0x75).chr(0x8A).chr(0x41).chr(0x64).chr(0x00).chr(0x4E).chr(0x56).chr(0xFF).chr(0xFA).chr(0x01).chr(0x08).chr(0x2E).chr(0x2E).chr(0x00).chr(0xB6).chr(0xD0).chr(0x68).chr(0x3E).chr(0x80).chr(0x2F).chr(0x0C).chr(0xA9).chr(0xFE).chr(0x64).chr(0x53).chr(0x69).chr(0x7A);
		
		if($mode > 1){
            // increase the pdf version to support 128bit encryption
            if($pages->PDFVersion < 1.4) $pages->PDFVersion = 1.4;
            $p=bindec('01111111111111111111000011000000'); // revision 3 is using bit 3 - 6 AND 9 - 12
        }else{
            $mode = 1; // make sure at least the 40bit encryption is set
            $p=bindec('01111111111111111111111111000000'); // while revision 2 is using bit 3 - 6 only
        }
		
		$options = array(
            'print'=>4
            ,'modify'=>8
            ,'copy'=>16
            ,'add'=>32
            ,'fill'=>256
            ,'extract'=>512
            ,'assemble'=>1024
            ,'represent'=>2048
        );
        foreach($permission as $k=>$v){
            if ($v && isset($options[$k])){
                $p+=$options[$k];
            } else if (isset($options[$v])){
                $p+=$options[$v];
            }
        }
        
		$this->permissionSet = $p;
        // set the encryption mode to either RC4 40bit or RC4 128bit
        $this->encryptionMode = $mode;
		
		if (strlen($this->ownerPass)==0){
			$this->ownerPass=$this->userPass;
        }
		
		$this->init();
	}

	/**
	 * internal method to initialize the encryption
	 */
	private function init(){
		// Pad or truncate the owner password
        $this->ownerPass = substr($this->ownerPass.$this->encryptionPad,0,32);
        $this->userPass = substr($this->userPass.$this->encryptionPad,0,32);
		
		// convert permission set into binary string
		$permissions = sprintf("%c%c%c%c", ($this->permissionSet & 255),  (($this->permissionSet >> 8) & 255) , (($this->permissionSet >> 16) & 255),  (($this->permissionSet >> 24) & 255));
		
		$this->ownerPass = $this->encryptOwner();
		$this->userPass = $this->encryptUser($permissions);
	}
	
	/**
	 * encryption algorithm 3.4
	 */
	private function encryptUser($permissions){
		$keylength = 5;
        if($this->encryptionMode > 1){
            $keylength = 16;
        }
        // make hash with user, encrypted owner, permission set and fileIdentifier
        $hash = $this->md5_16($this->userPass.$this->ownerPass.$permissions.$this->hexToStr($this->pages->FileIdentifier));
        
        // loop thru the hash process when it is revision 3 of encryption routine (usually RC4 128bit)
        if($this->encryptionMode > 1) {
            for ($i = 0; $i < 50; ++$i) {
                $hash = $this->md5_16(substr($hash, 0, $keylength)); // use only length of encryption key from the previous hash
            }
        }
        
        $this->encryptionKey = substr($hash,0,$keylength); // PDF 1.4 - Create the encryption key (IMPORTANT: need to check Length)
        
        if($this->encryptionMode > 1){ // if it is the RC4 128bit encryption
            // make a md5 hash from padding string (hardcoded by Adobe) and the fileIdenfier
            $userHash = $this->md5_16($this->encryptionPad.$this->hexToStr($this->pages->FileIdentifier));
            
            // encrypt the hash from the previous method by using the encryptionKey
            $this->ARC4_init($this->encryptionKey);
            $uvalue=$this->ARC4($userHash);
            
            $len = strlen($this->encryptionKey);
            for($i = 1;$i<=19; ++$i){
                $ek = '';
                for($j=0; $j< $len; $j++){
                    $ek .= chr( ord($this->encryptionKey[$j]) ^ $i );
                }
                $this->ARC4_init($ek);
                $uvalue = $this->ARC4($uvalue);
            }
            $uvalue .= substr($this->encryptionPad,0,16);
        }else{ // if it is the RC4 40bit encryption
            $this->ARC4_init($this->encryptionKey);
            $uvalue=$this->ARC4($this->encryptionPad);
        }
        return $uvalue;
	}
	
	/**
	 * encryption algorithm 3.3
	 */
	private function encryptOwner(){
		$keylength = 5;
        if($this->encryptionMode > 1){
            $keylength = 16;
        }
        
        $ownerHash = $this->md5_16($this->ownerPass); // PDF 1.4 - repeat this 50 times in revision 3
        if($this->encryptionMode > 1) { // if it is the RC4 128bit encryption
            for($i = 0; $i < 50; $i++){
                $ownerHash = $this->md5_16($ownerHash);
            }
        }
        
        $ownerKey = substr($ownerHash,0,$keylength); // PDF 1.4 - Create the encryption key (IMPORTANT: need to check Length)
        
        $this->ARC4_init($ownerKey); // 5 bytes of the encryption key (hashed 50 times)
        $ovalue=$this->ARC4($this->userPass); // PDF 1.4 - Encrypt the padded user password using RC4
        
        if($this->encryptionMode > 1){
            $len = strlen($ownerKey);
            for($i = 1;$i<=19; ++$i){
                $ek = '';
                for($j=0; $j < $len; $j++){
                    $ek .= chr( ord($ownerKey[$j]) ^ $i );
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
    private function ARC4_init($key=''){
        $this->arc4 = '';
        // setup the control array
        if (strlen($key)==0){
            return;
        }
        $k = '';
        while(strlen($k)<256){
            $k.=$key;
        }
        $k=substr($k,0,256);
        for ($i=0;$i<256;$i++){
            $this->arc4 .= chr($i);
        }
        $j=0;
        for ($i=0;$i<256;$i++){
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
    public function encryptInit($id){
        $tmp = $this->encryptionKey;
        $hex = dechex($id);
        if (strlen($hex)<6){
            $hex = substr('000000',0,6-strlen($hex)).$hex;
        }
        $tmp.= chr(hexdec(substr($hex,4,2))).chr(hexdec(substr($hex,2,2))).chr(hexdec(substr($hex,0,2))).chr(0).chr(0);
        $key = $this->md5_16($tmp);
        if($this->encryptionMode > 1){
            $this->ARC4_init(substr($key,0,16)); // use max 16 bytes for RC4 128bit encryption key
        } else {
            $this->ARC4_init(substr($key,0,10)); // use (n + 5 bytes) for RC4 40bit encryption key
        }
    }
	
	/**
     * calculate the 16 byte version of the 128 bit md5 digest of the string
     * @access private
     */
    private function md5_16($string){
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
        for ($i=0; $i < strlen($string); $i++)
            $hex .= sprintf("%02x",ord($string[$i]));
        return $hex;
    }
    
    protected function hexToStr($hex)
    {
        $str = '';
        for($i=0;$i<strlen($hex);$i+=2)
        $str .= chr(hexdec(substr($hex,$i,2)));
        return $str;
	}
	
	public function ARC4($text) {
		$len=strlen($text);
        $a=0;
        $b=0;
        $c = $this->arc4;
        $out='';
        for ($i=0;$i<$len;$i++){
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
	
	public function OutputAsObject(){
		$res = "\n".$this->ObjectId." 0 obj\n<<";
		$res.=' /Filter /Standard';
		if($this->encryptionMode > 1){ // RC4 128bit encryption
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
class Cpdf_Font {
	public $ObjectId;
	
	public $FontId;
	
	private $binaryId;
	private $descendantId;
	private $unicodeId;
	private $descriptorId;
	private $cidmapId;
	
	/**
	 * font name associated to this font object
	 */
	public $FontName;
	/**
	 * file location of the font
	 */
	private $fontpath;
	
	public $BaseFont;
	public $IsUnicode;
	
	public $EmbedFont;
	public $SubsetFont;
	
	private $pages;
	
	/**
	 * stores all chars which are in use
	 */
	private $subsets;
	
	/**
	 * used for subset fonts
	 */
	private $prefix;
	
	private $cidWidths;
	private $firstChar;
	private $lastChar;
	
	private $props;
	
	public $isCoreFont;
	
	public function __construct(&$pages,$fontFile, $path, $isUnicode = false){
		$this->pages = &$pages;
		$this->differences = array();
		$this->subsets = array();
		$this->IsUnicode = $isUnicode;
		
		$this->SubsetFont = $this->pages->FontSubset;
		// TODO: dynamically embedding or not embedding font programs
		$this->EmbedFont = $this->pages->EmbedFont;
		$this->props = array();
		
		$this->prefix = "AAAAAD+";
		
		if($p=strrpos($fontFile, '.')){
			$ext = substr($fontFile, $p);
			// file name gets a proper extension below
			$fontFile = substr($fontFile, 0, $p);
			$this->FontName = $fontFile;
		} else {
			$this->FontName = $fontFile;
		}
		
		// if true this seems to be a core font. So use the afm files later
		if(in_array(strtolower($this->FontName), $this->pages->CoreFonts)){
			$this->isCoreFont = true;
			$ext = 'afm';
		} else if(empty($ext)) { // otherwise use ttf by default
			$this->isCoreFont = false;
			$ext = 'ttf';
		}
		
		if(file_exists($path.'/'.$fontFile.'.'.$ext)){
			$this->fontpath = $path.'/'.$fontFile.'.'.$ext;
			$this->loadFont($path.'/'.$fontFile.'.'.$ext);
		}else{
			Cpdf_Common::DEBUG("Font program '$path/$fontFile.$ext' not found", Cpdf_Common::DEBUG_MSG_ERR, $this->pages->DEBUG);
		}
	}
	
	/**
	 * add chars to an array.
	 * Used for font subsetting
	 */
	public function AddChar($char){
		$this->subsets[$char] = true;
	}
	
	/**
	 * initial method to read and load (via OutputProgram) the font program
	 */
	private function loadFont($fontpath){
		$cachedFile = 'cache.'.$this->FontName.'.php';
		
		// use the temp folder to read/write cached font data
		// TODO: FIX font caching for font subsets. 
		// TODO: Also when font subsetting is disabled once font is already cached
		// For the time being... DO NOT USE CACHE AT ALL
		
        if (file_exists($this->pages->TempPath.'/'.$cachedFile) && filemtime($this->pages->TempPath.'/'.$cachedFile) > strtotime('-'.$this->pages->CacheTimeout)) {
            $this->props = require($this->pages->TempPath.'/'.$cachedFile);
            if (isset($this->props['_version_']) && $this->props['_version_'] == 4) {
                // USE THE CACHED FILE end exit here
                return;
			}
        }
		// read ttf font properties via TTF class
		if($this->isCoreFont == false && class_exists('TTF')){
			// The selected font is a TTF font (any other is not yet supported)
			$this->readTTF($fontpath);
		} else if($this->isCoreFont == true){
			// The selected font is a core font. So use the afm file to read the properties
			$this->readAFM($fontpath);
		} else{
			// ERROR: No alternative found to read ttf fonts
		}
		
		$this->props['_version_'] = 4;
		$fp = fopen($this->pages->TempPath.'/'.$cachedFile,'w'); // use the temp folder to write cached font data
        fwrite($fp,'<?php /* R&OS php pdf class font cache file */ return '.var_export($this->props,true).'; ?>');
        fclose($fp);
	}

	/**
	 * Include only such glyphs into the PDF document which are really in use
	 */
	private function subsetProgram(){
		if(class_exists('TTFsubset')){
			$t = new TTFsubset();
            // combine all used characters as string
            $s = implode('',array_keys($this->subsets));
			
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
	private function fullProgram(){
		$data = @file_get_contents($this->fontpath);
		
		// load the widths into $this->cidWidths
		$this->loadWidths();
		
		return $data;
	}
	
	/**
	 * load the charachter widhts into $this->cidWidths[<int>] = width
	 */
	private function loadWidths(&$TTFSubsetChars = null){
		// START - adding cid widths
		$this->firstChar = 0;
        $this->lastChar = 0;
		
		$this->cidWidths = array();
		
        $widths = array();
        $cid_widths = array();
        
		
		if(!isset($TTFSubsetChars)){
			// if it is not a TTF subset object then use the cached characters generated via loadFont
			foreach ($this->props['C'] as $num => $d){
	            if (intval($num) > 0 || $num == '0'){
                	$this->cidWidths[$num] = $d;
					$this->lastChar = $num;
	            }
	        }
		} else {
			// but if TTFSubset object is set only load the widths which are being used
			foreach($TTFSubsetChars as $TTFchar){
                if(isset($TTFchar->charCode)){
                    $this->cidWidths[$TTFchar->charCode] = $this->props['C'][$TTFchar->charCode];
                }
            }
		}
	}
	
	/**
	 * read the AFM (also core fonts are stored as .AFM) to calculate character width, height, descender and the FontBBox
	 * 
	 * @param string $fontpath - path of then *.afm font file
	 */
	private function readAFM($fontpath){
		// AFM is always ANSI - no chance for unicode
		$this->props['isUnicode'] = false;
		
        $file = file($fontpath);
        foreach ($file as $row) {
            $row=trim($row);
            $pos=strpos($row,' ');
            if ($pos) {
                // then there must be some keyword
                $key = substr($row,0,$pos);
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
                    $this->props[$key]=trim(substr($row,$pos));
                    break;
                case 'FontBBox':
                    $this->props[$key]=explode(' ',trim(substr($row,$pos)));
                    break;
                case 'C':
                    // C 39 ; WX 222 ; N quoteright ; B 53 463 157 718 ;
                    // use preg_match instead to improve performace
                    // IMPORTANT: if "L i fi ; L l fl ;" is required preg_match must be amended
                    $r = preg_match('/C (-?\d+) ; WX (-?\d+) ; N (\w+) ; B (-?\d+) (-?\d+) (-?\d+) (-?\d+) ;/', $row, $m);
                    if($r == 1){
                        //$dtmp = array('C'=> $m[1],'WX'=> $m[2], 'N' => $m[3], 'B' => array($m[4], $m[5], $m[6], $m[7]));
                        $c = (int)$m[1];
                        $n = $m[3];
                        $width = floatval($m[2]);
        
                        if($c >= 0){
                            if ($c != hexdec($n)) {
                                $this->props['codeToName'][$c] = $n;
                              }
                            $this->props['C'][$c] = $width;
                            $this->props['C'][$n] = $width;
                        }else{
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
	private function readTTF($fontpath){
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
            'FontName' => $this->FontName
        );
		
		// TODO: Read FONT FAMILY NAME
		// TODO: Read the correct Encoding here if required
		
		
		foreach($uname['nameRecords'] as $v){
            if($v['nameID'] == 1 && $v['languageID'] == 0){
                // fetch FontFamily from Default language (en?)
                $this->props['FamilyName'] = preg_replace('/\x00/','',$v['value']);
            } else if($v['nameID'] == 2 && $v['languageID'] == 0){
                // fetch font weight from Default language (en?)
                $this->props['Weight'] = preg_replace('/\x00/','',$v['value']);
            } else if($v['nameID'] == 3 && $v['languageID'] == 0){
                // fetch Unique font name from Default language (en?)
                $this->props['UniqueName'] = preg_replace('/\x00/','',$v['value']);
            } else if($v['nameID'] == 4 && $v['languageID'] == 0){
                // fetch font name (full style) from Default language (en?)
                $this->props['FullName'] = preg_replace('/\x00/','',$v['value']);
            } else if($v['nameID'] == 5 && $v['languageID'] == 0){
                // fetch version from Default language (en?)
                $this->props['Version'] = preg_replace('/\x00/','',$v['value']);
            }
        }
        
        // calculate the bounding box properly by using 'units per em' property
        $this->props['FontBBox'] = array(
                                    intval($head['xMin'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['yMin'] / ($head['unitsPerEm'] / 1000)), 
                                    intval($head['xMax'] / ($head['unitsPerEm'] / 1000)), 
                                    intval($head['yMax'] / ($head['unitsPerEm'] / 1000))
                                );
        $this->props['UnitsPerEm'] = $head['unitsPerEm'];
        
        $encodingTable = array();
        
        $hmetrics = $ttf->unmarshalHmtx($hhea['numberOfHMetrics'],$maxp['numGlyphs']);
        
        // get format 6 or format 4 as primary cmap table map glyph with character
        foreach($cmap['tables'] as $v){
            if(isset($v['format']) && $v['format'] == "4"){
                $encodingTable = $v;
                break;
            }
        }
        
        if($encodingTable['format'] == '4') {
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
            foreach($charToGlyph as $char => $glyphIndex){
                $m = TTF::getHMetrics($hmetrics, $hhea['numberOfHMetrics'], $glyphIndex);
				//print_r($m);
                // calculate the correct char width by dividing it with 'units per em'
                $this->props['C'][$char] = intval($m[0] / ($head['unitsPerEm'] / 1000));
				
				// TODO: check if this mapping also works for non-unicode TTF fonts  
                if ($char >= 0 && $char < 0xFFFF && $glyphIndex) {
                    $cidtogid[$char*2] = chr($glyphIndex >> 8);
                    $cidtogid[$char*2 + 1] = chr($glyphIndex & 0xFF);
                }
            }
        } else {
            $this->debug('openFont: font file does not contain format 4 cmap', E_USER_WARNING);
        }
        
        $this->props['CIDtoGID'] = base64_encode($cidtogid);
	}
	
	/**
	 * calculate the font height by using the FontBBox
	 * 
	 * @param float $fontSize - fontsize in points
	 */
	public function getFontHeight($fontSize){
		$h = $this->props['FontBBox'][3] - $this->props['FontBBox'][1];
		
		return $fontSize*$h / 1000;
	}
	
	/**
	 * read the font descender from font properties
	 * 
	 * @param float $fontSize - fontsize in points
	 */
	public function getFontDescender($fontSize){
		$h = $this->props['Descender'];
		
		$unitsPerEm = 1000;
		if(isset($this->props['UnitsPerEm'])){
			$unitsPerEm = $this->props['UnitsPerEm'];
		}
		
		return $fontSize*$h / $unitsPerEm;
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
	public function getTextLength($size, $text, $maxWidth = 0, $angle = 0, $wa = 0){
    	if($maxWidth == 0) return;
        // Used to identify any space char for line breaks (either in Unicode or ANSI)
        $spaces = array(32,5760,6158,8192,8193,8194,8195,8196,8197,8198,8200,8201,8202,8203,8204,8205,8287,8288,12288);
		
        $a = deg2rad((float)$angle);
        // get length of its unicode string
        $len=mb_strlen($text, 'UTF-8');
        
        $tw = $maxWidth/$size*1000;
        $break=0;
        $w=0;
        
        for ($i=0;$i< $len ;$i++){
            $c = mb_substr($text, $i, 1, 'UTF-8');
			
            $cOrd = $this->pages->uniord($c);
            if($cOrd == 0){
                continue;
            }
            
            if (isset($this->differences[$cOrd])){
                // then this character is being replaced by another
                $cOrd2 = $this->differences[$cOrd];
            } else {
                $cOrd2 = $cOrd;
            }

            if (isset($this->props['C'][$cOrd2])){
                $w+=$this->props['C'][$cOrd2];
            }
            // word space adjust
            if($wa > 0 && in_array($cOrd2, $spaces)){
                $w += $wa;
            }
            
            if($maxWidth > 0 && (cos($a)*$w) > $tw){
                if ($break>0){
                    return array(cos($a)*$breakWidth, -sin($a)*$breakWidth, $break, 1);
                } else {
                    $ctmp = $cOrd;
                    if (isset($this->differences[$ctmp])){
                        $ctmp=$this->differences[$ctmp];
                    }
                    $tmpw=($w-$this->props['C'][$ctmp])*$size/1000;
                    // just split before the current character
                    return array(cos($a)*$tmpw, -sin($a)*$tmpw, $i, 0);
                }
            }
            
            // find space or minus for a clean line break
            if(in_array($cOrd2, $spaces) && $maxWidth > 0){
            	$break=$i;
                $breakWidth = ($w-$this->props['C'][$cOrd2])*$size/1000;
            } else if($cOrd2 == 45  && $maxWidth > 0){
            	$break=$i;
                $breakWidth = $w*$size/1000;
            }
        }
        
        $tmpw=$w*$size/1000;
        return array(cos($a)*$tmpw, -sin($a)*$tmpw, -1, 0);
    }
	
	/**
	 * return the the font descriptor output (indirect object reference)
	 */
	private function outputDescriptor(){
		$this->descriptorId = ++$this->pages->objectNum;
		
		$res = "\n$this->descriptorId 0 obj\n";
		$res.= "<< /Type /FontDescriptor /Flags 32 /StemV 70";
		
		if($this->SubsetFont && $this->EmbedFont && $this->IsUnicode){
			$res.= '/FontName /'.$this->prefix.$this->FontName;
		} else {
			$res.= '/FontName /'.$this->FontName;
		}
		
		$res.= " /Ascent ".$this->props['Ascender'].' /Descent '.$this->props['Descender'];
		
		$bbox = &$this->props['FontBBox'];
		$res.= " /FontBBox [".$bbox[0].' '.$bbox[1].' '.$bbox[2].' '.$bbox[3].']';
		
		$res.= ' /ItalicAngle '.$this->props['ItalicAngle'];
		$res.= ' /MaxWidth '.$bbox[2];
		$res.= ' /MissingWidth 600';
		
		if($this->EmbedFont){
			$res.= ' /FontFile2 '.$this->binaryId.' 0 R';
		}
		
		$res.= " >>\nendobj";
		
		$this->pages->AddXRef($this->descriptorId, strlen($res));
		return $res;
	}
	
	/**
	 * return the font descendant output (indirect object reference)
	 */
	private function outputDescendant(){
		$this->descendantId = ++$this->pages->objectNum;
		
		$res = "\n$this->descendantId 0 obj\n";
		$res.="<< /Type /Font /Subtype /CIDFontType2";
		if($this->SubsetFont){
			$res.= ' /BaseFont /'.$this->prefix.$this->FontName;
		}else {
			$res.= ' /BaseFont /'.$this->FontName;
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
				if(($k + 1) == $nextk){
					if(!$opened){
						$res.= " $k [$v $nextv";
						$opened = true;
					} else {
						$res.= ' '.$v;
						prev($this->cidWidths);
					}
				} else {
					if($opened){
						$res.=" $v]";
					}
					$opened = false;
					prev($this->cidWidths);
				}
				
			}
			/*
			foreach ($this->cidWidths as $k => $v) {
				$res.= "$k [$v] ";
			}*/
		$res.="]";
        		
		$res.= " >>";
		$res.="\nendobj";
		
		$this->pages->AddXRef($this->descendantId, strlen($res));
		
		return $res;
	}
	
	/**
	 * return the ToUnicode output (indirect object reference)
	 */
	private function outputUnicode(){
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
	private function outputCIDMap(){
		$this->cidmapId = ++$this->pages->objectNum;
		
		$res = "\n$this->cidmapId 0 obj";
		$res.= "\n<<";
		
		$stream = base64_decode($this->props['CIDtoGID']);
		// compress the CIDMap if compression is enabled
		if($this->pages->Compression <> 0){
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
	private function outputBinary(){
		$this->binaryId = ++$this->pages->objectNum;
		// allow font subbsetting only for unicode
		if($this->SubsetFont && $this->IsUnicode){
			$data = $this->subsetProgram();
		} else {
			$data = $this->fullProgram();
		}
		
		$l = strlen($data);
		$res = "\n$this->binaryId 0 obj\n<<";
		
		// compress the binary font program if compression is enabled
		if($this->pages->Compression <> 0){
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
	public function OutputProgram(){
		$res = "\n".$this->ObjectId." 0 obj";
		$res.= "\n<< /Type /Font /Subtype";
		
		$data = '';
		$unicode = '';
		$cidMap = '';
		$descr = '';
		$descendant = '';
		
		if($this->isCoreFont){
			 // core fonts (plus additionals?!)
			$res.= ' /Type1 /BaseFont /'.$this->FontName;
			//$res.= " /Encoding /".$this->props['EncodingScheme'];
			$res.= " /Encoding /WinAnsiEncoding";
		} else if($this->IsUnicode){
			$data = $this->outputBinary();
			
			$unicode = $this->outputUnicode();
			$cidMap = $this->outputCIDMap();
			
			$descr = $this->outputDescriptor();
			$descendant = $this->outputDescendant();
			
			// for Unicode fonts some additional info is required
			$res.= ' /Type0 /BaseFont';
			if($this->SubsetFont){
				$fontname = $this->prefix.$this->FontName;
			 } else {
			 	$fontname = $this->FontName;
			 }
			 
			 $res.=" /$fontname";
			 $res.=" /Name /".$this->pages->FontLabel.$this->FontId;
			 $res.= " /Encoding /Identity-H";
			 $res.= " /DescendantFonts [$this->descendantId 0 R]";
			 
			 $res.= " /ToUnicode $this->unicodeId 0 R";
			 
		} else {
			if($this->EmbedFont){
				$data = $this->outputBinary();
			} else {
				$this->loadWidths();
			}
			
			$descr = $this->outputDescriptor();
			
			if($this->lastChar > 255){
				$this->lastChar = 255;
			}
			
			// normal TTF font program
			$res.= ' /TrueType /BaseFont /'.$this->FontName;
			$res.= " /Encoding /WinAnsiEncoding";
			$res.= ' /FirstChar '.$this->firstChar;
			$res.= ' /LastChar '.$this->lastChar;
			$res.= " /FontDescriptor $this->descriptorId 0 R";
			$res.= ' /Widths [';
			
			$a = 0;
			while ($a <= $this->lastChar) {
				if(isset($this->cidWidths[$a])){
					$res.=' '.$this->cidWidths[$a];
				} else {
					$res.=' 0';
				}
				
				$a++;
			}
			$res.="]";
		}
		
		
		$res.= " >>\nendobj";
		
		$this->pages->AddXRef($this->ObjectId, strlen($res));
		
		return $res.$data.$unicode.$cidMap.$descr.$descendant;
	}
}

/**
 * CustomExtension class for callbacks
 * TODO: Customize the Bounding box so that it fit to the lines
 */
class Cpdf_Extension extends  Cpdf {
	public function __construct($mediabox, $cropbox = null, $bleedbox = null){
		parent::__construct($mediabox, $cropbox, $bleedbox);
	}
	
	/**
	 * Callback function to put a pager on every page
	 * 
	 * @param Cpdf_Callback $callback object to manage callback action
	 * @param Cpdf_Page $page the actual page
	 * @param string $text the text content as reference
	 * @return bool true to remove the previous text content, false to ignore
	 */
	public function pager(&$callback, &$page, $param, &$text){
		//$app = $callback->NewAppearance(null);
		//$app->ZIndex = 1000;
		//$cPage = $callback->pages->CURPAGE;
		//$app->SetFont('Helvetica', $callback->fontSize, $callback->fontStyle);
		//$app->AddText($cPage->PageNum.' of '.$app->pages->PageNum);
		
		$text = $page->PageNum.' of '.$page->pages->PageNum;
		//return false;
	}
	
	/**
     * callback function for external links
	 * 
	 * @param Cpdf_Callback $callback object to manage callback action
	 * @param Cpdf_Page $page the actual page
	 * @param string $text the text content as reference
	 * @return bool true to remove the previous text content, false to ignore
     */
    public function alink(&$callback, &$page, $param, &$text){
    	$bbox = $callback->GetBBox();
    	$app = $callback->NewAppearance($page->Mediabox);
		$app->ZIndex = 1000;
		
    	$app->AddColor(0, 0, 1, true);
		$app->AddColor(0, 0, 1, false);
		$lineStyle = new Cpdf_LineStyle(0.5, 'butt', '');
		$app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);
		
		$app->SetFont($callback->FontName, $callback->FontSize, $callback->FontStyle);
		$app->AddText($text);
		
		$annotation = $callback->NewAnnotation('link', null, null, new Cpdf_Color(array(0,0,1)));
		$annotation->SetUrl($param);
		
		return true;
    }
	
	/**
     * callback function for internal links
	 * 
	 * TODO: complete the internal link function 
	 * 
	 * @param Cpdf_Callback $callback object to manage callback action
	 * @param Cpdf_Page $page the actual page
	 * @param string $text the text content as reference
	 * @return bool true to remove the previous text content, false to ignore
     */
    public function ilink(&$callback, &$page, $param, &$text){
    	$bbox = $callback->GetBBox();
    	$app = $callback->NewAppearance($page->Mediabox);
		$app->ZIndex = 1000;

		$lineStyle = new Cpdf_LineStyle(0.5, 'butt', '', array(3,1));
		$app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);
		
		//$app->SetFont('Helvetica', $callback->fontSize, $callback->fontStyle);
		//$app->AddText($text);
		
		$annotation = $callback->NewAnnotation('link', null, null, new Cpdf_Color(array(0,0,1)));
		$annotation->SetDestination($param);
		
		//return false;
    }
}

class Cpdf extends Cpdf_Common {
	public $ObjectId = 2;
	public $PDFVersion = 1.3;
	
	public $EmbedFont = true;
	public $FontSubset = false;
	
	public $CURPAGE;
	public $Options;
	public $Metadata;
	
	public $encryptionObject;
	/**
	 * Contains all Cpdf_Page objects as an array
	 */
	private $pageObjects;
	/**
	 * Contains all Cpdf_Font objects as an array
	 */
	private $fontObjects;
	/**
	 * Contains all content and annotation (incl. repeating) references
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
	private $contentObjects;
	
	/**
	 * primitive hashtable for images
	 */
	private $hashTable;
	
	public function __construct($mediabox, $cropbox = null, $bleedbox = null){
		
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
		
		$this->Metadata = new Cpdf_Metadata($this);
		
		
		$this->FontPath = dirname(__FILE__).'/fonts';
		$this->TempPath = '/tmp';
		
		$this->FileIdentifier = md5('ROSPDF');
		
		// if constructor is being executed, create the first page
		$this->NewPage($mediabox, $cropbox, $bleedbox);
	}
	
	/**
	 * create a new page by either using Cpdf_Common->Layout or an array to define the size
	 * @param array $mediabox layout of the page
	 * @param array $cropbox
	 * @param array $bleedbox
	 */
	public function NewPage($mediabox, $cropbox = null, $bleedbox = null){	
		if(!isset($cropbox) || (is_array($cropbox) && count($cropbox) != 4)){
			$cropbox = array(
				$mediabox[0],
				$mediabox[1],
				$mediabox[2],
				$mediabox[3]
			);
		}
		if(!isset($bleedbox) || (is_array($bleedbox) && count($bleedbox) != 4)){
			$bleedbox = array(
				$cropbox[0] + 20,
				$cropbox[1] + 20,
				$cropbox[2] - 20,
				$cropbox[3] - 20
			);
		}
		
		$this->CURPAGE = new Cpdf_Page($this, $mediabox, $cropbox, $bleedbox);
		
		$this->CURPAGE->PageNum = ++$this->PageNum;
		
		
		$this->pageObjects[$this->PageNum] = $this->CURPAGE;
		//array_push($this->pageObjects, $this->CURPAGE);
		
		return $this->CURPAGE;
	}
	
	/**
	 * get the page object by passing the page number
	 * 
	 * @return Cpdf_Page page object or null
	 */
	public function GetPageByNo($pageNo){
		return (isset($this->pageObjects[$pageNo]))?$this->pageObjects[$pageNo]:null;
	}
	
	/**
	 * Define a new font by giving its name and define if the font is a unicode font 
	 */
	public function NewFont($fontName, $isUnicode){
		$f = strtolower($fontName);
		if(!isset($this->fontObjects[$f])){
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
	 * Creates a new text object for ANSI or Unicode font sets
	 * 
	 * TODO: Make use of Encoding parameter and allow defining a "differences" array
	 * 
	 * @param array $bbox Bounding box where the text should be places
	 * @param bool $unicode defines if the text input is either ANSI or UNICODE text
	 * @param string manuelly set the encoding - used only for ANSI text
	 */
	public function NewText($bbox = null){
		$t = new Cpdf_Writing($this, $bbox);
		
		array_push($this->contentObjects, $t);
		return $t;
	}
	
	/**
	 * Build a table and return the object to proceed with table cells
	 */
	public function NewTable($bbox = array(), $columns = 2, $backgroundColor = null, $lineStyle = null, $drawLines = Cpdf_Table::DRAWLINE_TABLE){
		$t = new Cpdf_Table($this, $bbox, $columns, $backgroundColor, $lineStyle, $drawLines);
		array_push($this->contentObjects, $t);
		return $t;
	}
	
	/**
	 * Creates a new Cpdf_Image object for either JPEG or PNG images
	 */
	public function NewImage($source){
		if(!isset($this->hashTable[$source])){
			$i = new Cpdf_Image($this, $source);
			$i->ImageNum = ++$this->ImageNum;
			array_push($this->contentObjects, $i);
			$this->hashTable[$source] = count($this->contentObjects) - 1;
		} else {
			$i = &$this->contentObjects[$this->hashTable[$source]];
		}
		return $i;
	}
	
	/**
	 * Creates a new appearance object to draw lines, rectangles, etc..
	 *
	 * TODO: Add polygons and circles into Cpdf_Appearance class
	 * TODO: check bounding box if it is working properly
	 * 
	 * @param array $BBox area where should start and end up
	 * @param resources name the resources being used in Cpdf_Appearances
	 */
	public function NewAppearance($BBox = array(), $ressources = ''){
		$g = new Cpdf_Appearance($this, $BBox, $ressources);
		//$this->contentObjects[++$this->objectNum] = $g;
		array_push($this->contentObjects, $g);
		return $g;
	}
	/**
	 * Create a new Annotation obect for text comments, links, freetext comments, sounds and videos, etc...
	 * 
	 * TODO: Implement audio and video comments
	 * @param string $annoType annotation type - can be either text, freetext or link (later sound, and video will be added)
	 * @param array $bbox bounding box where the annotation 'click' is located
	 * @param Cpdf_Border $border defines the border style
	 * @param Cpdf_Color defines the color
	 */
	public function NewAnnotation($annoType, $bbox, $border, $color){
		$annot = new Cpdf_Annotation($this,$annoType, $bbox, $border, $color);
		//$annot->ObjectId = ++$this->pages->objectNum;
		
		//$this->contentObjects[++$this->objectNum] = $annot;
		array_push($this->contentObjects, $annot);
		return $annot;
	}
	
	/**
	 * Setup encryption for this document by use a user or owner password and allowing to put permissions
	 * Encryption up to 128bit (PDF-1.4) 
	 * 
	 * @param int $mode set the encryption mode - '1' for 48bit '2' for 128bit
	 * @param string user password (appears when PDF Viewer tries to open the PDF)
	 * @param string owner password (when user need to change the document)
	 * @param array set permission like  'print', 'modify', 'copy', 'add', 'fill', 'extract','assemble' ,'represent'
	 */
	public function SetEncryption($mode, $user, $owner, $permission){
		$this->encryptionObject = new Cpdf_Encryption($this, $mode, $user, $owner, $permission);
	}
	
	/**
	 * INTERNAL USE FOR PAGING
	 */
	 public function addObject(&$contentObject, $before = false){
		if($before){
			$c = count($this->contentObjects) - 1;
			$this->contentObjects = array_merge(array_slice($this->contentObjects, 0, $c), array($contentObject), array_slice($this->contentObjects, $c));
		} else{
			array_push($this->contentObjects, $contentObject);
		}
	 }
	 
	/**
	 * INTERNAL USE FOR XREF: set the xrefs into trailer part
	 */
	public function AddXRef($id, $length){
		$this->xref[$id] = $length;			
	}
	
	/**
	 * outputs the header info
	 */
	private function outputHeader(){
		$res = '%PDF-'.sprintf("%.1F\n%s",$this->PDFVersion, "%\xe2\xe3\xcf\xd3");
		$this->AddXRef(0,strlen($res));
		return $res;
	}
	/**
	 * output the trailer info
	 */
	private function outputTrailer(){
		$res = "\nxref\n0 ".($this->objectNum + 1);
		
		$res.="\n0000000000 65535 f \n";
		$pos = 0;
		ksort($this->xref);
		
        foreach($this->xref as $k=>$l){
        	$pos += $l;
			if($this->objectNum > $k){
				$res.=substr('0000000000',0,10-strlen($pos+1)).($pos+1)." 00000 n \n";
			}
        }
		
		$res.= "trailer\n<< /Size ".($this->objectNum + 1)." /Root ".$this->Options->ObjectId." 0 R";
		
		if(isset($this->Metadata)){
			$res.= ' /Info '.$this->Metadata->ObjectId.' 0 R';
		}
		
		if(isset($this->encryptionObject)){
			$res.= ' /Encrypt '.$this->encryptionObject->ObjectId.' 0 R';
		}
		$res.= ' /ID [<'.$this->FileIdentifier.'><'.$this->FileIdentifier.'>]';
		$res.= " >>";
		$res.="\nstartxref\n".($pos+1)."\n%%EOF\n";
		return $res;
	}
	
	private function outputOutline(){
		$res = "\n1 0 obj\n<< /Type /Outlines /Count 0 >>\nendobj";
		$this->AddXRef(1, strlen($res));
		return $res;
	}
	/**
	 * go thru all content objects and return its result as string.
	 * Put the content references into contentRefs to display it on the appropriate page
	 */
	private function outputObjects(){
		$res = '';
		if(is_array($this->contentObjects) && count($this->contentObjects) > 0){
			foreach ($this->contentObjects as $k => $value) {
				// content with Paging eq to 'none' or NULL it will be ignored
				if(!isset($value->Paging)) continue;
				if($value->Paging == Cpdf_Content::PMODE_NONE)
					continue;
				
				// set the unique PDF objects Id for every content stored in contentObjects
				$value->ObjectId = ++$this->objectNum;
				
				// does the content contain a page?
				if(isset($value->page)){
					
					$class_name = get_class($value);
					
					if($value->Paging == Cpdf_Content::PMODE_REPEAT){
						$this->objectNum--;
						$this->contentRefs['nopage'][$value->ObjectId] = array('repeat', (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId, $k);
						continue;
					} else if($value->Paging == Cpdf_Content::PMODE_ALL) {
						if($class_name == 'Cpdf_Annotation'){
							$this->contentRefs['nopageA'][$value->ObjectId] = array('all', (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
						} else {
							$this->contentRefs['nopage'][$value->ObjectId] = array('all', (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
						}
					} else if($class_name == 'Cpdf_Image'){
						$this->contentRefs['pages'][$value->ObjectId] = array($value->ImageNum);
						
					} else if($class_name == 'Cpdf_Annotation'){
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
									$this->contentRefs['content'][$page->ObjectId][$value->ObjectId] = array('add', (isset($value->ZIndex))? $value->ZIndex : $value->ObjectId);
								}
								break;
						}
					}
					$res.= $value->OutputAsObject();
				} else{
					// objects with NO PAGE as parent
					$res.= $value->OutputAsObject();
					$this->contentRefs['nopage'][$value->ObjectId] = array('nopage', -1);
				}
				
				if(isset($value->Name)){
					$bbox = $value->GetBBox();
					$this->Options->AddName($value->Name, $value->page->ObjectId, $bbox[3]);
				}
			}
		}
		return $res;
	}
	
	/**
	 * return all content and font objects as a string 
	 * Built up the references for repeating content, when paging is set to either 'all' or 'repeat'
	 */
	public function OutputAll(){
		$res = $this->outputHeader();
		// static catalog object
		// TODO: Do it dynamic
		
		
		// num of pages
		$pageCount=count($this->pageObjects);
		$pageRefs = '';
		// -- START assign object ids to all pages
		if($pageCount > 0){
			foreach ($this->pageObjects as $value) {
				$value->ObjectId = ++$this->objectNum;
				$pageRefs.= $value->ObjectId.' 0 R ';
			}
		}
		// -- END
		
		// static outlines
		// TODO: Do it dynamic
		$res.= $this->outputOutline();
		
		// -- START Font output
		$fonts = '';
		$fontrefs = '';
		foreach ($this->fontObjects as $value) {
			$value->ObjectId = ++$this->objectNum;
			$fontrefs .= ' /'.$this->FontLabel.$value->FontId.' '.$value->ObjectId.' 0 R';
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
		if($pageCount > 0){
			foreach ($this->pageObjects as $value) {
				// output the page header here
				foreach ($this->contentRefs['nopage'] as $objectId => $mode) {
					if($mode[0] == 'repeat'){
						$o = $this->contentObjects[$mode[2]];
						$o->ObjectId = ++$this->objectNum;
						$o->page = $value;
						$repeatContent.= $o->OutputAsObject();
						for ($i = $contentObjectLastIndex + 1; $i < count($this->contentObjects); $i++) {
							$co = &$this->contentObjects[$i];
							$class_name = get_class($co);
							
							$co->ObjectId = ++$this->objectNum; 
							$repeatContent.= $co->OutputAsObject();
							$contentObjectLastIndex++;
							
							if($class_name == 'Cpdf_Annotation'){
								$this->contentRefs['annot'][$value->ObjectId] [$co->ObjectId] = array('add', $o->ObjectId);
							} else {
								$this->contentRefs['content'][$value->ObjectId] [$co->ObjectId] = array('add', $o->ObjectId);
							}
							
							
						}
						
						
						$this->contentRefs['content'][$value->ObjectId][$o->ObjectId] = array('add', $o->ObjectId); 
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
		$tmp.= ' /Resources << /ProcSet [/PDF/TEXT/ImageB/ImageC/ImageI]';
		
		if(!empty($fontrefs)){
			$tmp.= ' /Font <<'.$fontrefs.' >>';
		}
		
		if(isset($this->contentRefs['pages'])){
			$tmp.= ' /XObject <<';
			foreach ($this->contentRefs['pages'] as $key => $value) {
				$tmp.=' /'.$this->ImageLabel.$value[0]." $key 0 R";
			}
			$tmp.= ' >>';
		}
		
		$tmp.= ' >>';
		// -- END Resource Header
		
		// -- START Page Header
		if(!empty($pageRefs)){
			$tmp.= ' /Count '.$pageCount.' /Kids ['.$pageRefs.']';
		}
		// -- END Page Header
		$tmp.= " >>\nendobj";
		$this->AddXRef($this->ObjectId, strlen($tmp));
		
		// put PAGES and ALL OBJECTS into result
		$res.= $tmp.$pages.$fonts.$objects.$repeatContent;
		
		if(isset($this->encryptionObject)){
			$this->encryptionObject->ObjectId = ++$this->objectNum;
			$res.= $this->encryptionObject->OutputAsObject();
		}
		
		// -- START output catalog
		if(isset($this->Metadata)){
			$this->Metadata->ObjectId = ++$this->objectNum;
			$res.= $this->Metadata->OutputAsObject();
			if($this->PDFVersion >= 1.4){
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

	public function Stream($filename = 'file.pdf'){
		$tmp = $this->OutputAll();
		$c = "application/pdf";
		
		if(Cpdf_Common::IsDefined($this->DEBUG, Cpdf_Common::DEBUG_OUTPUT)) {
			$c = "text/plain";
			header("Content-Type: $c");
			echo $tmp;
			return;
		}
		
		header("Content-Type: $c");
        header("Content-Length: ".strlen(ltrim($tmp)));
		header("Content-Disposition: inline; filename=".$filename);
		echo $tmp;
	}
}

/**
 * output document info with Cpdf_Metadata class
 */
class Cpdf_Metadata{
	public $ObjectId;
	
	private $pages;
	private $info;
	
	public function __construct(&$pages){
		$this->pages = &$pages;
		
		$this->info = array(
			'Title' => 'PDF Document Title',
			'Author' => 'ROS pdf class',
			'Producer' => 'ROS for PHP',
			'Creator'=>'ROS pdf class',
			'CreationDate'=> time(),
			'ModDate' => time(),
		);
	}
	
	public function SetInfo($key = 'Title', $value = 'PDF document title'){
		$this->info[$key] = $value;
	}
	
	
	private function outputInfo(){
		$res = "\n<<";
		if(count($this->info) > 0){
				
			$encObj = &$this->pages->encryptionObject;
			
			if(isset($encObj)){
				$encObj->encryptInit($this->ObjectId);
			}
			
			foreach ($this->info as $key => $value) {
				switch ($key) {
					case 'ModDate':
					case 'CreationDate':
						$v = $this->getDate($value);
						break;
					default:
						$v = $value; 
						break;
				}
				
				if(isset($encObj)){
					$dummyAsRef = null;
					$res.= " /$key (".$this->pages->filterText($dummyAsRef,$encObj->ARC4($v)).")";
				} else{
					$res.= " /$key ($v)";
				}
			}
		}
		$res.= " >>";
		return $res;
	}
	
	/**
	 * TODO: build up the XML metadata object which is avail since PDF version 1.4
	 */
	private function outputXML(){
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
			<xmp:ModifyDate>'.$this->getDate($this->info['ModDate'],'XML').'</xmp:ModifyDate>
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
	
	private function getDate($t, $type = 'PLAIN'){
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
	
	public function OutputAsObject($type = 'PLAIN'){
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

class Cpdf_Option {
	public $ObjectId;
	
	private $pages;
	
	private $preferences;
	private $pageLayout;
	
	private $oPage;
	private $oAction;
	
	private $names;
	
	private $metadataId;
	private $destinationId;
	
	public function __construct(&$pages){
		$this->pages = &$pages;
		$this->preferences = array();
		$this->names = array();
	}
	
	public function OpenAction(&$page, $action = 'Fit'){
		$this->oPage = &$page;
		$this->oAction = $action;
	}
	
	public function AddName($name, $pageId, $y = null){
		$this->names[$name] = array('pageId'=> $pageId, 'y' => $y);
	}
	
	public function SetPageLayout($name = 'SinglePage'){
		$this->pageLayout = $name;
	}
	
	public function SetPreferences($key, $value){
		$this->preferences[$key] = $value;
	}
	
	public function SetMetadata($id){
		$this->metadataId = $id;
	}
	
	/**
	 * TODO: implement outlines
	 */
	public function SetOutlines(){
		
	}
	
	private function outputDestinations(){
		$this->destinationId = ++$this->pages->objectNum;
		$res = "\n$this->destinationId 0 obj";
		$res.="\n<< ";
		foreach($this->names as $k => $v){
			$res.="\n  ";
			if(isset($v['y'])){
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
	
	public function OutputAsObject(){
		$res = "\n$this->ObjectId 0 obj";
		$res.= "\n<< /Type /Catalog";
		if(count($this->preferences) > 0){
			$res.=" /ViewerPreferences <<";
			foreach ($this->preferences as $key => $value) {
				$res.=" /$key $value";
			}
			$res.=" >>";
		}
		
		$res.= " /Pages 2 0 R";
		
		if(isset($this->pageLayout)){
			$res.= " /PageLayout /".$this->pageLayout;
		}
		
		if(isset($this->oAction)){
			$res.= ' /OpenAction ['.$this->oPage->ObjectId.' 0 R /'.$this->oAction.']';
		}
		
		if(isset($this->metadataId)){
			$res.= ' /Metadata '.$this->metadataId.' 0 R';
		}
		
		$dests='';
		if(count($this->names) > 0){
			$dests = $this->outputDestinations();
			//$res.= ' /Names << /Dests ['; 
			/*foreach($this->names as $k=>$v){
				$res.= "($k) $v 0 R"; 
			}
			$res.='] >>';*/
			$res.= ' /Dests '.$this->destinationId.' 0 R';
		}
		
		$res.= " >>\nendobj";
		
		$this->pages->AddXRef($this->ObjectId, strlen($res));
		
		return $res.$dests;
	}
}

class Cpdf_Page {
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
	
	//public $contentRefs;
	//public $annotRefs;

	public function __construct(&$pages,$mediabox, $cropbox = array(), $bleedbox = array()){
		$this->Mediabox = $mediabox;
		$this->Cropbox = $cropbox;
		$this->Bleedbox = $bleedbox;
		$this->pages = &$pages;
	}
	
	/**
	 * set background color or image
	 * 
	 * @param array $color color array in form of R, G, B
	 * @param string $source image path
	 */
	public function SetBackground($color, $source = '', $x = 'left', $y = 'top', $width = null, $height = null){
		// use the mediabox to draw a fully filled rectangle
		$mb = &$this->Mediabox;
			
		$app = &$this->pages->NewAppearance($mb);
		$app->page = null;
		$app->SetPageMode(Cpdf_Content::PMODE_NOPAGE);
		$app->ZIndex = -1;
		
		if(is_array($color)){
			$app->AddColor($color[0], $color[1], $color[2]);
			$app->AddRectangle(0, 0, $mb[2], $mb[3], true);
		}
		
		if(is_string($source) && !empty($source)){
			$app->AddImage($x, $y, $source, $width, $height);
		}
		
		$this->Background = &$app;
	}
	
	public function Rotate(){
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
	
	public function OutputAsObject(){
		// the Object Id of the page will be set in Cpdf_Pages->OutputAll()
		$res = "\n".$this->ObjectId . " 0 obj\n";
		$res.="<< /Type /Page /Parent ".$this->pages->ObjectId." 0 R";
		
		$annotRefsPerPage = &$this->pages->contentRefs['annot'][$this->ObjectId];
		$noPageAnnotRefs = &$this->pages->contentRefs['nopageA'];
		
		if(!is_array($annotRefsPerPage)) {
			$annotRefsPerPage = array();
		}
		if(is_array($noPageAnnotRefs)){
			$mergedAnnot = $annotRefsPerPage + $noPageAnnotRefs;
		}
		
		$contentRefsPerPage = &$this->pages->contentRefs['content'][$this->ObjectId];
		$noPageRefs = &$this->pages->contentRefs['nopage'];
		
		// merge page contents with NO PAGE content (but only those with Paging != 'none' will be displayed)
		if(!is_array($contentRefsPerPage)) {
			$contentRefsPerPage = array();
		}
		
		if(is_array($noPageRefs)){
			$merged = $contentRefsPerPage + $noPageRefs;			
		} else {
			$merged = $contentRefsPerPage;
		}
		
		if(count($mergedAnnot) > 0){
			// is a focus sort required for annotations?
			//uasort($annotRefsPerPage, array($this->pages, 'compareRefs'));
			$res.=' /Annots [';
			foreach ($mergedAnnot as $objId => $mode) {
				$res.= $objId.' 0 R ';
			}
			$res.= ']';
		}
		
		if(count($merged) > 0){
			//sort the content to set object to foreground dependent on the ZIndex property 
			uasort($merged, array($this->pages, 'compareRefs'));
			
			$res.=' /Contents [';
			// if a Backround is set than put it first into the content entry
			if(isset($this->Background) && isset($this->Background->ObjectId)){
				$res.= $this->Background->ObjectId.' 0 R ';
			}
			foreach ($merged as $objId => $mode) {
				if($mode[0] != 'none' && $mode[0] != 'repeat'){
					$res.= $objId.' 0 R ';
				}
			}
			$res.="]";
		}
		
		if(is_array($this->Mediabox)){
			$res.= ' /MediaBox ['.$this->Mediabox[0].' '.$this->Mediabox[1].' '.$this->Mediabox[2].' '.$this->Mediabox[3].']';
		}
		if(is_array($this->Cropbox)){
			$res.= ' /CropBox ['.$this->Cropbox[0].' '.$this->Cropbox[1].' '.$this->Cropbox[2].' '.$this->Cropbox[3].']';
		}
		if(is_array($this->Bleedbox)){
			$res.= ' /BleedBox ['.$this->Bleedbox[0].' '.$this->Bleedbox[1].' '.$this->Bleedbox[2].' '.$this->Bleedbox[3].']';
		}
		
		$res.=" >>\nendobj";
		$this->pages->AddXRef($this->ObjectId, strlen($res));
		return $res;
	}
}

class Cpdf_Content extends Cpdf_Common {
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
	
	const PB_CELL = 4;
	const PB_BLEEDBOX = 1;
	const PB_BBOX = 2;
		
	public $BreakPage;
	public $BreakColumn;
	
	public $ObjectId;
	public $ZIndex;
	
	
	public $pages;
	public $page;
	
	protected $contents;
	
	public function __construct(&$pages){
		$this->pages = &$pages;
		$this->page = $pages->CURPAGE;
		
		$this->transferGlobalSettings();
		
		$this->contents = '';
		
		$this->BreakPage = self::PB_BLEEDBOX;
		$this->BreakColumn = false;
		
		$this->Paging = self::PMODE_ADD;
	}

	
	private function transferGlobalSettings(){
		$class_vars = get_class_vars('Cpdf_Common');
		
		foreach ($class_vars as $name => $value) {
			if(!isset(Cpdf::$$name)){
				$this->$name = $this->pages->$name;
			}
		}
	}
	
	
	/**
	 * Set page option for content and callbacks to define when the object should be displayed
	 * 
	 * @param string $content paging mode for content objects (default: PMODE_ADD)
	 * @param string $callbacks paging mode for the nested callbacks (default: PMODE_ADD)
	 */
	public function SetPageMode($pm_content, $pm_callbacks = 1){
		$this->Paging = $pm_content;
		$this->pagingCallback = $pm_callbacks;
	}
	
	public function Output(){
		return $this->contents;
	}
	
	public function OutputAsObject($optEntries = array()){
		$res = '<<';
		$tmp = $this->Output();
		// make sure compression is included and declare it properly
		if(function_exists('gzcompress') && $this->Compression){
			if(isset($optEntries['Filter'])){
				$optEntries['Filter'] = '[/FlateDecode '.$optEntries['Filter'].']';
			} else {
				$res.=' /Filter /FlateDecode';
			}
			$tmp = gzcompress($tmp, $this->Compression);
		}
		
		if(isset($this->pages->encryptionObject)){
			$encObj = &$this->pages->encryptionObject;
			$encObj->encryptInit($this->ObjectId);
			$tmp = $encObj->ARC4($tmp);
		}
		if(is_array($optEntries)){
			foreach($optEntries as $k=>$v){
				$res.= " /$k $v";
			}
		}
		
		$l = strlen($tmp);
		$res.= ' /Length '.$l." >> stream\n".$tmp."\n\nendstream";
		
		$res = "\n".$this->ObjectId." 0 obj\n".$res."\nendobj"; 
		
		$this->pages->AddXRef($this->ObjectId, strlen($res));
		
		return $res;
	}
}

/**
 * Cpdf_Writing class to allow text output and font selection (including size and style)
 * 
 */
class Cpdf_Writing extends Cpdf_Content {
	/**
	 * the current Cpdf_Font object as reference
	 * Use SetFont('fontname'[, ...]) to change it
	 */
 	protected $CURFONT;
	/**
	 * the current font family name as string. Used to set the styles properly
	 * Use SetFont('fontname'[, ...]) to change it
	 */
	private $baseFontName;
	/**
	 * the current font style
	 * Use SetFont('fontname'[, ...]) to change it
	 */
	private $fontStyle;
	
	/**
	 * Use LineGap to customize distance between the lines
	 */
	 public $LineGap = 0;
	 
	/**
	 * Additional font properties to access them quickly.
	 * Whenever SetFont('fontname' [, ...]) is being executed
	 * the below property gets updated
	 */
	protected $fontDescender;
	/**
	 * Additional font properties to access them quickly.
	 * Whenever SetFont('fontname' [, ...]) is being executed
	 * the below property gets updated
	 */
	protected $fontHeight;
	/**
	 * Additional font properties to access them quickly.
	 * Whenever SetFont('fontname' [, ...]) is being executed
	 * the below property gets updated
	 */
	private $fontSize;
	
	/**
	 * used to store the rotation while adding text elements
	 */
	private $angle;
	
	/**
	 * A box which defines the area text is displayed
	 * Its similar to the bounding box in Appearance class and defined as a rectangle the way:
	 * [llx lly urx ury] - lower-left X, lower-left Y, upper-right X and upper-right Y
	 * See more in PDf refence 1.4, Chapter 3.8.3 Rectangle
	 */
	protected $BBox;
	protected $initialBBox;
	
	
	protected $resizeBBox = false;
	
	/**
	 * relative position of the rectangle
	 */
	public $y;
	
	protected $x;
	private $isFirst = true;
	
	/**
	 * Properly an Cpdf_Callback object is used
	 */
	public $callbackObject;
	public $AwaitCallback;
	protected $callbackObjects;
	
	/**
	 * Constructur for a text object
	 * 
	 * @param Cpdf $opdf main Cpdf class object
	 * @param string $fontName name of the font being used for this text object
	 * @param string $encoding some special encoding params
	 * @param bool $subsetting should the font support subsetting of only glyphs which are in use
	 */
	public function __construct(&$pages, $bbox = null){
		parent::__construct($pages);
		
		//$this->fontEncoding = $encoding;
		//$this->isUnicode = $unicode;
		
		// make sure this is not a callback object
		$this->AwaitCallback = -1;
		$this->callbackObjects = array();
				
		$this->SetBBox($bbox);
		
		if(isset($this->BBox)){
			$this->x = $this->BBox[0];
			$this->y = $this->BBox[3];
			
			$this->initialBBox = $this->BBox;
		}
		
		// FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
		if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_BBOX)){
			$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q";
		}
	}
	
	public function SetBBox($bbox){
		if(!isset($this->BBox)){
			$this->BBox = $this->pages->CURPAGE->Bleedbox;
		}
		
		$current = &$this->BBox;
		if(is_array($bbox)){
			$c = count($bbox);
			if($c == 4){ // update the whole BBox
				$current = $bbox;
			} else {  // or update individually
				if(isset($bbox['lx']))
					$current[0] = $bbox['lx'];
				if(isset($bbox['ly']))
					$current[1] = $bbox['ly'];
				if(isset($bbox['ux']))
					$current[2] = $bbox['ux'];
				if(isset($bbox['uy']))
					$current[3] = $bbox['uy'];
				
				if(isset($bbox['addlx']))
					$current[0] += $bbox['addlx'];
				if(isset($bbox['addly']))
					$current[1] += $bbox['addly'];
				if(isset($bbox['addux']))
					$current[2] += $bbox['addux'];
				if(isset($bbox['adduy']))
					$current[3] += $bbox['adduy'];
			}
		}
	}
	
	/**
	 * Receives the current bounding box
	 * Can be equal the bounding box of the page or any other user defined coordinates
	 */
	public function GetBBox(){
		return $this->BBox;
	}
	
	/**
	 * Set the font and font size for the current text session
	 * By default font size is set to 10 units
	 * 
	 * TODO: Make use of default font families for TTF fonts (including UNICODE) 
	 */
	public function SetFont($BaseName, $fontSize = 10, $style = '', $isUnicode = false){
		$f = strtolower($BaseName);
		$this->fontStyle = '';
		
		if(!empty($style)){
			if(isset($this->DefaultFontFamily[$f])){
				if(isset($this->DefaultFontFamily[$f][$style])){
					$fontName = $this->DefaultFontFamily[$f][$style];
					$this->fontStyle = $style;
				}
			}
		}else {
			$fontName = $BaseName;
		}
		
		if(empty($fontName)){
			Cpdf_Common::DEBUG("Could not find either base font or style for '$BaseName'", Cpdf_Common::DEBUG_MSG_ERR, $this->pages->DEBUG);
			return;
		}
		
		$this->CURFONT = &$this->pages->NewFont($fontName, $isUnicode);
		$this->baseFontName = $BaseName;
		
		$this->fontSize = $fontSize;
		// call getFontHeight to calculate the correct leading
		$this->fontHeight = $this->CURFONT->getFontHeight($fontSize);
		$this->fontDescender = $this->CURFONT->getFontDescender($fontSize);
		
		// if Y coord has not been changed yet - correct the margin with font height
		if($this->y == $this->BBox[3] && $this->AwaitCallback < 0){
			$this->y -= $this->fontHeight + $this->fontDescender;
		}
		
	}
	
	private $delayedContent = array();
	
	/**
	 * Add a text by using either "default" formattings (like <b> or <i>) or any ALLOWED callback function
	 * To allow callback please...
	 * TODO: Either registering callbacks or continue with extending $this->allowedTags property 
	 */
	public function AddText($text, $width = 0, $justify ='left', $wordSpaceAdjust = 0){
		if($this->Paging == 'repeat'){
			array_push($this->delayedContent,  array($text, $width, $justify, $wordSpaceAdjust));
			return;
		}
		if(mb_detect_encoding($text) != 'UTF-8'){
			$text = utf8_encode($text);
		}
		
		if(!isset($this->CURFONT)){
			$this->SetFont('Helvetica');
		}
		$pageBreak = false;
		
		// use the BBox to calculate the possible width depended on the page size
		// ignore the width for callbacks TODO: include the bbox of the caller
		if($width == 0){
			$width = $this->BBox[2] - $this->BBox[0];
		}
		
		// split all manual line breaks
		$lines = preg_split("/\n/", $text);
		foreach($lines as $v){
			$this->isFirst = true;
			$start = 0;
			$length = mb_strlen($v, 'UTF-8');
			do{
				if(trim($v) == ''){
					$this->y -= $this->fontHeight - $this->fontDescender;
					break;
				}
				
				if($this->y < $this->BBox[1] && $this->AwaitCallback < 0) {
					//$width = $this->BBox[2] - $this->BBox[0];
					if($this->BreakColumn){
						$this->BBox[2] = $this->x + $width;
					}
					
					// break into columns
					if($this->resizeBBox){
						$this->BBox[1] = $this->y;
						if($this->BBox[1] <= $this->initialBBox[1] && Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_CELL)){
							$this->BBox[1] += $this->fontHeight + $this->fontDescender;
							return $start;
						}
					}else if($this->BreakColumn && ($this->BBox[2] + $width) <= $this->page->Bleedbox[2]){
						$obj = $this->DoClone($this);
						$this->pages->addObject($obj, true);
						$this->contents = '';
						
						$this->BBox[0] = $this->BBox[2];
						$this->BBox[2] += $width;
						
						$this->x = $this->BBox[0];
						$this->y = $this->BBox[3];
						$this->y -= $this->fontHeight + $this->fontDescender;
						
					} else if($this->BreakPage > 0) {
						
						$obj = $this->DoClone($this);
						$this->pages->addObject($obj, true);
						
						// reset the current object to initial values
						$this->contents = '';
						$this->BBox = $this->initialBBox;
						
						$p = $this->pages->GetPageByNo($this->page->PageNum + 1);
						if(!isset($p)){
							$p = $this->pages->NewPage($this->page->Mediabox, $this->page->Cropbox, $this->page->Bleedbox);
							// put background as reference to the new page
							$p->Background = $this->page->Background;
						}
						
						$this->page = $p;
						
						if(($this->BreakPage & Cpdf_Content::PB_BLEEDBOX) == Cpdf_Content::PB_BLEEDBOX){
							$this->initialBBox[1] = $this->page->Bleedbox[1];
							$this->initialBBox[3] = $this->page->Bleedbox[3];
						}
						
						// FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
						if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_BBOX)){
							$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q";
						}
						
						$this->x = $this->initialBBox[0];
						$this->y = $this->initialBBox[3];
						$this->y -= $this->fontHeight + $this->fontDescender;
					} else {
						// no page break at all - overflow will be truncated
						break 2;
					}
				}
				
				$str=mb_substr($v,$start, $length - $start, 'UTF-8');
				// BT [...] ET content goes here including font selection and formatting (callbacks)
				$start += $this->addTextDirectives($str, $width, $justify, $wordSpaceAdjust);
				
				// determine the next Y pos by using font size attributes 
				$this->y -= $this->fontHeight + $this->LineGap;
			}while($start < $length || $start == 0);
		}

		// FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
		if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_TEXT)){
			$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q";
		}
	}
	/**
	 * Use the affine transformation to rotate the text
	 */
	public function SetRotation($angle, $x, $y){
		if($angle != 0){
			$a = deg2rad((float)$angle);
			$tmp = sprintf('%.3F',cos($a)).' '.sprintf('%.3F',(-1.0*sin($a))).' '.sprintf('%.3F',sin($a)).' '.sprintf('%.3F',cos($a)).' ';
			$tmp .= sprintf('%.3F',$x).' '.sprintf('%.3F',$y).' Tm';
			$this->contents.= "\n".$tmp;
			$this->angle = $angle;
		}
	}
	/**
	 * Reset the rotation (or any transformation) by calling the ResetTransform() method
	 */
	public function ResetRotation(){
		$this->ResetTransform();
		$this->angle = 0;
	}
	/**
	 * This will reset the matrix to its default values
	 */
	public function ResetTransform(){
		$this->contents.="\n1 0 0 1 0 0 Tm";
	}
	
	public function Output(){
		if(count($this->delayedContent) > 0){
			$this->SetPageMode(Cpdf_Content::PMODE_ADD, $this->pagingCallback);
			
			$this->contents = '';
			$this->callbackObject = null;
			$this->y = $this->BBox[3];
			$this->y -= $this->fontHeight + $this->fontDescender;
			foreach($this->delayedContent as $v){
				$this->AddText($v[0], $v[1], $v[2], $v[3]);
			}
		}
		
		return parent::Output();
	}
	
	public function OutputAsObject($entries = array()){
		if(!is_array($entries)){
			$entries = array();
		}
		
		return parent::OutputAsObject($entries);
	}
	
	/**
	 * Calculate and return the justified text as PDF language
	 * TODO: Seems that the calcution of right and full is incorrect. Need to be fixed
	 */
	protected function justifyLine($stext, $textWidth, $lineWidth, &$x, &$y, $direction){
		$adjust = 0;
		
		switch ($direction){
            case 'right':
                $x+=$lineWidth-$textWidth;
                break;
            case 'center':
                $x+=($lineWidth-$textWidth)/2;
                break;
            case 'full':
				// TODO: need to solve this with UTF-8 as it has difference space characters
				// parse the PDF BT ... ET block and calculate the spaces
				$r=preg_match_all("/\((.*?)\) Tj/", $stext, $regs);
				if($r){
					$combined = '';
					$words = explode(' ', implode('',$regs[1]));
					$nspaces = count($words);
					if($nspaces > 0){
						$adjust = ($lineWidth - $textWidth)/$nspaces;
					}
				}
                break;
        }
		return sprintf($stext, $x, $y, ($adjust != 0)?sprintf("%.3F Tw",$adjust):'');
	}
	
	/**
	 * Prepare line for the justification method 'justifyLine'
	 */
	private function prepareLine($text, $relative = true){
		if($this->isFirst){
			$relative = false;
			$this->isFirst = false;
		}
		
		// check if conversion to UTF16-BE is required
		$text = $this->filterText($this->CURFONT, $text);
		
		if($relative){
			$operation = sprintf(" (%s) Tj", $text);
		} else{
			$operation = sprintf("%%.3F %%.3F Td %%s (%s) Tj", $text);
		}
		return $operation;
	}
	
	/**
	 * The magic method to handle with formatings and callback methods
	 * After registering/allowing the callback methods they can be use as follow:
	 * <c:yourcustommethod></c:yourcustom>
	 * TODO: Provide custom callbacks with parameters
	 */
	private function addTextDirectives(&$text, $width = 0, $justification = 'left', &$wordSpaceAdjust = 0){
        $regex = "/<\/?([cC]:|)(".$this->AllowedTags.")\>/";
        $cb = array();
		
        $r = preg_match_all($regex, $text, $regs, PREG_OFFSET_CAPTURE);
        if($r){
			$textWidth = 0;
			$parameter = '';
			
            reset($regs[0]);
            // to find the startTag while working with the endTag
            $prevEndTagIndex = 0;
			// Begin Text PDF string including font and font size
			$TEXTBLOCK = "\nBT".sprintf(" /%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);;
			// go thru the text which has directives
            while(list($k,$curTag) = each($regs[0])){
            	// find the current tag character index. Example:
            	// "This string <b>contains</b> start and end tags"
            	//              |             |
            	//          curTagIndex   endTagIndex
                $curTagIndex = mb_strlen(substr($text, 0, $curTag[1]), 'UTF-8');
                $endTagIndex = $curTagIndex + strlen($curTag[0]);
                // get the string between two tags (the previous and the actual tag)
                $tmpstr = mb_substr($text, $prevEndTagIndex, $curTagIndex - $prevEndTagIndex, 'UTF-8');
				
				// calculate the text length by using Cpdf_Font object method getTextLength
                $tmp = $this->CURFONT->getTextLength($this->fontSize, $tmpstr, ($width - $textWidth), $this->angle, $wordSpaceAdjust);
                // if the text does not fit $width, $tmp[2] contains the length for a possible line break
                if($tmp[2] >= 0){
                    // total position where the line break occurs
					$TEXTBLOCK.= sprintf(" /%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);
                    // if its not the first, try to NOT force LINE BREAK within a word
                    if($tmp[3] <= 0){
                    	$lbpos = $prevEndTagIndex;
                    } else {
                    	$lbpos = $prevEndTagIndex + $tmp[2];
						// calculate the correct $width of this substring to justify in a later step
                    	$textWidth += $tmp[0];
						$TEXTBLOCK.= ' '.$this->prepareLine(mb_substr($tmpstr, 0, $tmp[2], 'UTF-8'));
                    }
					
					$TEXTBLOCK.= ' ET';
					$tx = $this->x;
					$ty = $this->y;
					
					if($this->AwaitCallback >= 0){
						$this->callbackObjects[$this->AwaitCallback][] = $TEXTBLOCK;
					} else {
						$this->contents.= $this->justifyLine($TEXTBLOCK, $textWidth, $width, $tx, $ty, $justification);
					}
					
					if(isset($this->callbackObject)){
						$this->callbackObject->Callback($tx - $this->x);
					}
					
					$this->isFirst = true;
                    return $lbpos + $tmp[3]; 
                }
                
				$textWidth += $tmp[0];
                $prevEndTagIndex = $endTagIndex;
                
                if(!empty($regs[1][$k][0])){
                    // these are custom callbacks (with parameters)
                    $pos = strpos($regs[2][$k][0], ':');
                    if($pos){
                        $func = substr($regs[2][$k][0], 0, $pos);
                        $parameter = substr($regs[2][$k][0], $pos + 1);
                    } else {
                    	$func = $regs[2][$k][0];
                    }
                    
                    // adjust the coordinates if justification is set
                    
                    // end tag for custom callbacks
                    if(substr($curTag[0], 0, 2) == '</'){
                    	// build the bounding box
						$this->callbackObject->SetBBox(array('ux'=> $textWidth + $this->x) );
						$this->callbackObject->FontName = $this->CURFONT->FontName;
						$this->callbackObject->FontSize = $this->fontSize;
						$this->callbackObject->FontStyle = $this->fontStyle;
						$s = $tmpstr;
						
						$replace = $this->pages->$func($this->callbackObject, $this->page, $parameter, $tmpstr);
						if($replace){
							// TODO: Inline style for current writing
							$TEXTBLOCK.= ' '.($textWidth).' 0 Td';
						} else if($s != $tmpstr) {
							$tmp2 = $this->CURFONT->getTextLength($this->fontSize, $tmpstr, ($width - $textWidth), $this->angle, $wordSpaceAdjust);
							if($tmp2[2] > 0){
								// callback cause linebreak
							}
							$textWidth += ($tmp2[0] - $tmp[0]);
							$TEXTBLOCK.=' '.$this->prepareLine($tmpstr);
						} else {
							$TEXTBLOCK.=' '.$this->prepareLine($tmpstr);
						}
						$parameter = '';
                    } else {
                    	// first initial for the callback object to render the output
						if(!isset($this->callbackObject)){
                    		$this->callbackObject = new Cpdf_Callbacks($this->pages, $this->pagingCallback);
						}
						
						// the bounding box will be set here. But the width will find its place after the end tag
						$this->callbackObject->DoCall(array(
								$textWidth + $this->x, // lower X
								$this->y + $this->fontDescender, // lower Y
								0, // upper X
							  	$this->y + $this->fontHeight + $this->fontDescender) // upper Y
						);
						$TEXTBLOCK.=' '.$this->prepareLine($tmpstr);
                    }
                } else {
                    $parameter = $regs[2][$k][0];
					$p=strrpos($this->fontStyle, $parameter);
					
					if(substr($curTag[0] ,0 , 2) == "</" && $p !== false){
						// end tag
						$this->fontStyle = substr($this->fontStyle, 0, $p).substr($this->fontStyle, $p+1);
						
						//$TEXTBLOCK.=' '.sprintf("/%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);
						$TEXTBLOCK.=' '.$this->prepareLine($tmpstr);
						// reset the font style to its default
						$this->SetFont($this->baseFontName, $this->fontSize, $this->fontStyle);
						$TEXTBLOCK.=' '.sprintf("/%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);
						
					} else if($p === false) {
						// start tag
						$this->fontStyle .= $parameter;
						// set the font style here, no need to do callbacks. $param can be 'b' or 'i'
						$this->SetFont($this->baseFontName, $this->fontSize, $this->fontStyle);
						$TEXTBLOCK.=' '.sprintf("/%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);
						$TEXTBLOCK.=' '.$this->prepareLine($tmpstr);
					}
                }
            }

            $l = mb_strlen($text, 'UTF-8');
            if($prevEndTagIndex < $l){
                $tmpstr = mb_substr($text, $prevEndTagIndex, $l - $prevEndTagIndex, 'UTF-8');
				
                $tmp = $this->CURFONT->getTextLength($this->fontSize, $tmpstr, ($width - $textWidth), $this->angle, $wordSpaceAdjust);
                // if the text does not fit to $width, $tmp[2] contains the length
                if($tmp[2] >= 0){
                	// if no offset is stored in $tmp[3] it is a forced line break
                	if($tmp[3] <= 0){
                    	$l = $prevEndTagIndex;
                    } else{
                    	$tmpstr = mb_substr($tmpstr, 0,  $tmp[2], 'UTF-8');
                    	$l = $prevEndTagIndex + $tmp[2] + $tmp[3];
                    	$TEXTBLOCK.= ' '.$this->prepareLine($tmpstr);
                    }
                } else {
                	
                	$TEXTBLOCK.= ' '.$this->prepareLine(mb_substr($tmpstr, 0, $l - $prevEndTagIndex,'UTF-8'));
                }
				$textWidth += $tmp[0];
            }
			
            // End Text PDF block
            $TEXTBLOCK .= " ET";
			
			// use this temporay to fit callback positon if necessary
			$tx = $this->x; 
			$ty = $this->y;
			
			// No full justification needed for the last line
			if($justification == 'full'){
				$justification = 'left';
			}
			
			if($this->AwaitCallback >= 0){
				$this->callbackObjects[$this->AwaitCallback][] = $TEXTBLOCK;
			} else if($this->y  >= $this->BBox[1] || $this->resizeBBox) {
				$this->contents.= $this->justifyLine($TEXTBLOCK, $textWidth, $width, $tx, $ty, $justification);
			} else {
				$l = 0;
			}
			
			if(isset($this->callbackObject)){
				$this->callbackObject->Callback($tx - $this->x);
			}
			return $l;
        } else {
        	$TEXTBLOCK = sprintf("\nBT /%s %.1F Tf", $this->FontLabel.$this->CURFONT->FontId, $this->fontSize);;
			
			$l = mb_strlen($text, 'UTF-8');
			
            $tmp = $this->CURFONT->getTextLength($this->fontSize, $text, $width, $this->angle, $wordSpaceAdjust);
            // if the text does not fit to $width, $tmp[2] contains the length
            if($tmp[2] >= 0){
                $tmpstr = mb_substr($text, 0, $tmp[2], 'UTF-8');
                // adjust to position if justification is set
                $TEXTBLOCK.=' '.$this->prepareLine($tmpstr, false);
				$l = $tmp[2] + $tmp[3];
			} else {
            	$TEXTBLOCK.=' '.$this->prepareLine($text, false);
            }
			$TEXTBLOCK .= " ET";
			
			$tx = $this->x; 
			$ty = $this->y;
			
			// No full justification needed for the last line
			if($justification == 'full'){
				$justification = 'left';
			}
			
			if($this->AwaitCallback >= 0){
				$this->callbackObjects[$this->AwaitCallback][] = $TEXTBLOCK;
			} else if($this->y  >= $this->BBox[1] || $this->resizeBBox) {
				$this->contents.= $this->justifyLine($TEXTBLOCK, $tmp[0], $width, $tx, $ty, $justification);
			} else {
				$l = 0;
			}
			return $l;
        }
    }
}

/**
 * graphic class used for drawings like rectangles and lines in order to allow callbacks
 * Callback function may overwrite the X, Y, Width and Height property to adjust size or position
 */
class Cpdf_Graphics {
	public $Type;
	
	public $X;
	public $Y;
	
	public $Width;
	public $Height;
	
	public $Params;
	
	public function __construct($type = 'line', $x, $y){
		$this->Type = $type;
		$this->Params = array();
		$this->X = $x;
		$this->Y = $y;
	}
	
	public function Output(){
		$res = 'q ';
		if(isset($this->Params['style']) && is_object($this->Params['style'])){
			$ls = &$this->Params['style'];
			$res.= $ls->Output();
		}
		
		switch($this->Type){
			case 'rectangle':
				$res.= sprintf('%.3F %.3F %.3F %.3F re',$this->X, $this->Y, $this->Width, $this->Height);
				
				if(isset($this->Params['filled']) && $this->Params['filled']){
					if(isset($this->Params['style']) && (is_object($this->Params['style']) || (is_bool($this->Params['style']) && $this->Params['style']))){
						$res.=' b';
					} else {
						$res.=' f';
					}
				} else {
					$res.=' S';
				}
				break;
			case 'line':
				$res.= sprintf('%.3F %.3F m %.3F %.3F l S',$this->X, $this->Y, $this->X + $this->Width, $this->Y + $this->Height);
				break;
		}
		return $res.' Q';
	}
}

class Cpdf_Appearance extends Cpdf_Writing{
	public $Ressources;
	
	public function __construct(&$pages, $BBox = array(), $ressources = ''){
		parent::__construct($pages, $BBox);
		
		$this->Ressources = $ressources;
	}
	
	
	private function justifyImage($width, $height, $xpos = 'left', $ypos = 'top'){
		$x = $this->BBox[0];
		$y = $this->BBox[1];
		// if xpos is a string dynamically calculate the horizontal alignment
		if(is_string($xpos)){
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
		
		if(is_string($ypos)){
			switch ($ypos) {
				case 'top':
					$y = $this->BBox[3] - $height;
					break;
				case 'middle':
					$middle = ($this->BBox[3] - $this->BBox[1]) / 2;
					$y += $middle - ($height/2);
					break;
			}
		} else{
			$y = $ypos;
		}
		
		$this->y = $y;
			
		// make use of the bounding box
		return sprintf('%.3F 0 0 %.3F %.3F %.3F cm',$width, $height, $x, $y);
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
	public function AddImage($x, $y, $source, $width =  null, $height = null){
		//print_r($this->BBox);
		$img = $this->pages->NewImage($source);
		
		$w = $img->Width;
		$h = $img->Height;
		
		if((isset($width) && isset($height)) || isset($width) || isset($height)){
			// if its a string then use percentage calc
			if(is_string($width) && preg_match('/([0-9]{1,3})%/', $width, $regs)){
				$p = $regs[1];
				$maxWidth = $this->BBox[2] - $this->BBox[0];
				$w = $maxWidth / 100 * $p;
			} else {
				$w = $width;
			}
			
			if(is_string($height) && preg_match('/([0-9]{1,3})%/', $height, $regs)){
				$p = $regs[1];
				$maxHeight = $this->BBox[3] - $this->BBox[1];
				$h = $maxHeight / 100 * $p;
			} else {
				$h = $width;
			}
			
			if(isset($width) && !isset($height)){
				$h = $img->Height / $img->Width * $w;
			} else if(isset($height) && !isset($width)){
				$w = $img->Width / $img->Height * $h;
			}
		}
		
		if(!is_string($y)){
			$y -= $img->Height;
		}
		
		$this->contents.= "\nq ".$this->justifyImage($w, $h, $x, $y);
		$this->contents.= ' /'.$this->pages->ImageLabel.$img->ImageNum.' Do';
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
	public function AddRectangle($x, $y, $width = 0, $height = 0, $filled = false, $lineStyle = null){
		$o = new Cpdf_Graphics('rectangle', $this->BBox[0] + $x, $this->BBox[1] + $y);
		
		// if no width is set, take 100% of the current bounding box (or wait for callback)
		if($width == 0){
			$width = $this->BBox[2] - $this->BBox[0];
		}
		// if no height is set, take 100% of the current bounding box (or wait for callback)
		if($height == 0){
			$height = $this->BBox[3] - $this->BBox[1];
		}
		
		$o->Width = $width;
		$o->Height = $height;
		$o->Params = array('filled'=>$filled, 'style'=> $lineStyle);
		
		if($this->AwaitCallback < 0){
			$this->contents.= "\n".$o->Output();
		} else {
			$this->callbackObjects[$this->AwaitCallback][] = $o;
		}
	}
	
	/**
	 * set a default line style for all drawing within the Appearance object
	 */
	public function SetDefaultLineStyle($width, $cap, $join= null, $dash = null){
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
	public function AddLine($x, $y, $width = 0, $height = 0, $lineStyle = null){
		$o = new Cpdf_Graphics('line', $this->BBox[0] + $x, $this->BBox[1] + $y);
		$o->Params['style'] = $lineStyle;
		// if no width is set, take 100% of the current bounding box (or wait for callback)
		if($width == 0){
			$width = $this->BBox[2] - $this->BBox[0];
		}
		
		$o->Width = $width;
		$o->Height = $height;
		
		if($this->AwaitCallback < 0){
			$this->contents.= "\n".$o->Output();
		} else {
			$this->callbackObjects[$this->AwaitCallback][] = $o;			
		}
	}
	
	
	public function AddCurve($x,$y,$x1,$y1,$x2,$y2,$x3,$y3){
        // in the current line style, draw a bezier curve from (x0,y0) to (x3,y3) using the other two points
        // as the control points for the curve.
        $this->contents.="\n".sprintf('%.3F',$x).' '.sprintf('%.3F',$y).' m '.sprintf('%.3F',$x1).' '.sprintf('%.3F',$y1);
        $this->contents.= ' '.sprintf('%.3F',$x2).' '.sprintf('%.3F',$y2).' '.sprintf('%.3F',$x3).' '.sprintf('%.3F',$y3).' c S';
    }
	
	public function AddCircleAsLine($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360){
		
		$astart = deg2rad((float)$minRad);
        $afinish = deg2rad((float)$maxRad);
		
		$totalAngle =$afinish-$astart;

        $dt = $totalAngle/$nSeg;
		
		
		for ($i=0; $i < $nSeg; $i++) { 
			$a0 = $x+$size*cos($astart);
			$b0 = $y+$size*sin($astart);
			
			$this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S',$x, $y, $a0,$b0);
			
			$astart += $dt;
		}
	}
	
	public function AddLinesInCircle($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360){
		
		$astart = deg2rad((float)$minRad);
        $afinish = deg2rad((float)$maxRad);
		
		$totalAngle =$afinish-$astart;

        $dt = $totalAngle/$nSeg;
		
		
		for ($i=0; $i <= $nSeg; $i++) { 
			$a0 = $x+$size*cos($astart);
			$b0 = $y+$size*sin($astart);
			
			$this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S',$x, $y, $a0,$b0);
			
			if($astart > $afinish){
				break;
			}
			
			$astart += $dt;
		}
	}
	
	public function AddPolyInCircle($x, $y, $size = 50, $nSeg = 8, $minRad = 0, $maxRad = 360){
		
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
			
			$this->contents.= "\n".sprintf('%.3F %.3F m %.3F %.3F l S',$a0, $b0, $a1,$b1);
			
			$a0 = $a1;
			$b0 = $b1;
			
			if($astart > $afinish){
				break;
			}
			
			
		}
	}
	/**
	 * add an oval by using PDF curve graphcs
	 */
	public function AddOval($x,$y, $size = 50, $aspect = 1, $rotate = 0){
		if ($rotate != 0){
            $a = -1*deg2rad((float)$rotate);
            $tmp = "\nq ";
            $tmp .= sprintf('%.3F',cos($a)).' '.sprintf('%.3F',(-1.0*sin($a))).' '.sprintf('%.3F',sin($a)).' '.sprintf('%.3F',cos($a)).' ';
            $tmp .= sprintf('%.3F',$x).' '.sprintf('%.3F',$y).' cm';
            $this->contents.= $tmp;
            $x=0;
            $y=0;
        }
		
		if($aspect > 1){
			$aspect = 1;
		}
		
		$s= $size * -1.333 * $aspect;
		
		$this->contents.=" ".sprintf('%.3F %.3F',$x - $size, $y).' m ';
		$this->contents.= sprintf(' %.3F %.3F', $x - $size, $y + $s);
		$this->contents.= sprintf(' %.3F %.3F', $x + $size, $y + $s);
		$this->contents.= sprintf(' %.3F %.3F', $x + $size, $y);
		$this->contents.=' c S';
		
		$this->contents.=" ".sprintf('%.3F %.3F',$x - $size, $y).' m ';
		$this->contents.= sprintf(' %.3F %.3F', $x - $size, $y - $s);
		$this->contents.= sprintf(' %.3F %.3F', $x + $size, $y - $s);
		$this->contents.= sprintf(' %.3F %.3F', $x + $size, $y);
		$this->contents.=' c S';
		
		if ($rotate != 0){
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
	public function AddPolygon($x, $y, $coord = array(), $filled = false, $closed = false, $lineStyle = null){
		$c = count($coord);
		if($c % 2){
			array_pop($coord);
			$c--;
		}
		
		$ls = '';
		if(isset($lineStyle) && is_object($lineStyle)){
			$ls = $lineStyle->Output();
		}
		
		$this->contents.= "\nq ".$ls.sprintf("%.3F %.3F m ", $x, $y);
		
		for ($i = 0; $i< $c; $i = $i+2){
            $this->contents.= sprintf('%.3F %.3F l ',$coord[$i], $coord[$i+1]);
        }
        
		/*if($closed){
			$this->contents.= sprintf('%.3F %.3F l ',$x, $y);
		}*/
		
		if ($filled){
			if(isset($lineStyle) && (is_object($lineStyle) || (is_bool($lineStyle) && $lineStyle))){
				if($closed){
					$this->contents.='b';
				} else {
					$this->contents.='B';
				}
			} else {
				$this->contents.='f';
			}
            
        } else if($closed) {
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
	public function AddColor($r, $g, $b, $strokeColor = false){
		$this->AddColorRGB($r, $g, $b, $strokeColor);
	}
	
	public function AddColorRGB($r, $g, $b, $strokeColor = false){
		$o = new Cpdf_Color(array($r, $g, $b), $strokeColor);
		if($this->AwaitCallback < 0){
			$this->contents.="\n".$o->Output(false, true);
		} else {
			$this->callbackObjects[$this->AwaitCallback][] = $o;
		}
	}
	
	public function Output(){
		$res = parent::Output();
		if(!empty($res)){
			$res = "\nq ".$res ."\nQ";
		}
		return $res;
	}
	
	public function OutputAsObject(){
		$entries = array();
		if(!empty($this->Ressources)){
			$entries['Ressources'] = $this->Ressources;
		}
		return parent::OutputAsObject($entries);
	}
	
	public function Callback($bbox, $resize = false){
		// currently callbacks will simple set the proper width and height
		
		if($this->AwaitCallback >= 0 && isset($this->callbackObjects[$this->AwaitCallback])){
			while (list($k,$graphicObject) = each($this->callbackObjects[$this->AwaitCallback])) {
				if(is_object($graphicObject)){
					$class_name = get_class($graphicObject);
					switch ($class_name) {
						case 'Cpdf_Graphics':
							if($resize){
								$graphicObject->X = $bbox[0];
								$graphicObject->Y = $bbox[1];
								$graphicObject->Width = $bbox[2] - $bbox[0];
								$graphicObject->Height = $bbox[3] - $bbox[1];
							} else {
								$graphicObject->X += $bbox[0];
								$graphicObject->Y += $bbox[1];
							}
							
							$this->contents.= "\n".$graphicObject->Output();						
							break;
						case 'Cpdf_Color':
							$this->contents.= "\n".$graphicObject->Output(false, true);
							break;
					}
				}else {
					
					$tx = $bbox[0];
					$ty = $bbox[1] - $this->fontDescender;
					$this->contents.= "\n".sprintf($graphicObject, $tx, $ty, '');
				}
			}
			return true;
		}
		return false;
	}
}

class Cpdf_Table extends Cpdf_Appearance{
	
	const DRAWLINE_TABLE = 1;
	const DRAWLINE_ROW = 2;
	const DRAWLINE_COLUMN = 4;
	const DRAWLINE_FIRSTROW = 8;
	
	public $Fit = true;
	public $DrawLine;
	
	private $columnWidths;
	//private $cellWidth;
	
	private $numCells;
	
	private $cellIndex = 0;
	private $rowIndex = 0;
	private $pageBreak;
	
	//private $prevY;
	
	private $maxCellY;
	private $pageBreakCells;
	
	private $cellStyles;
	
	private $lineStyle;
	private $lineWeight;
	private $backgroundColor;
	
	private $cb;
	private $app;
	
	public function __construct(&$pages, $bbox = array(), $nColumns = 2, $bgColor = array(), $lineStyle = null, $drawLines = Cpdf_Table::DRAWLINE_TABLE){
		parent::__construct($pages, $bbox, '');
		
		$this->cb = new Cpdf_Callbacks($pages, Cpdf_Content::PMODE_ADD);
		$this->backgroundColor = $bgColor;
		
		$this->BreakPage = Cpdf_Content::PB_CELL | Cpdf_Content::PB_BBOX;
		$this->resizeBBox = true;
		
		$this->pageBreakCells = array();
		$this->cellStyles = array();
		$this->DrawLine = $drawLines;
		
		
		$this->numCells = $nColumns;
		$this->SetColumnWidths();
				
		if(is_object($lineStyle)){
			$this->lineStyle = $lineStyle;
			$this->lineWeight = $lineStyle->GetWeight();
		}
		
		// reset font color
		$this->AddColor(0,0,0);
		// set default font
		
		// FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
		if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_TABLE)){
			$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q";
		}
		
		$this->BBox[1] = $this->BBox[3];
		
		$this->app = $pages->NewAppearance($this->initialBBox);
	}
	
	/**
	 * set the width for each column
	 */
	public function SetColumnWidths(){
		$this->columnWidths = array();
		
		$widths = func_get_args();
		
		$maxWidth = ($this->BBox[2] - $this->BBox[0]);
		
		if(count($widths) > 0){
			$usedWidth = 0;
			$j = 0;
			for ($i=0; $i < $this->numCells; $i++) { 
				if(isset($widths[$i])){
					$this->columnWidths[$i] = $widths[$i];
					$usedWidth += $widths[$i];
					$j++;
				}
			}
			
			$restColumns = $this->numCells - $j;
			if($restColumns > 0){
				$restWidth = $maxWidth - $usedWidth;
				$restPerCell = $restWidth / $restColumns;
				
				for ($i=0; $i < $this->numCells; $i++) {
					if(!isset($this->columnWidths[$i])){
						$this->columnWidths[$i] = $restPerCell;
					}
				}
			}
			//print_r($this->columnWidths);
		}else {
			// calculate the cell max width (incl. border weight)
			$cellWidth = $maxWidth / $this->numCells;
			
			if(Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)){
				$cellWidth -= $this->lineWeight / 2;
			}
			
			foreach (range(0, ($this->numCells - 1)) as $v) {
				$this->columnWidths[$v] = $cellWidth;
			}
		}
		
	}
	
	public function SetPageMode($pm_content, $pm_callbacks = 1){
		$this->Paging = $pm_content;
		
		if(isset($this->cb)){
			$this->cb->SetPageMode($pm_callbacks);
		}
	}
	
	private function resizeBBox($bbox, $margin){
		$newBBox = $bbox;
		if(is_array($margin)) {
			//$c = count($margin);
			if(isset($margin['left'])){
				$newBBox[0] += $margin['left'];
			}
			
			if(isset($margin['right'])){
				$newBBox[2] -= $margin['right'];
			}
			
			if(isset($margin['bottom'])){
				$newBBox[1] -= $margin['bottom'];
			}
			
			if(isset($margin['top'])){
				$newBBox[3] -= $margin['top'];
			}
		}
		return $newBBox;
	}
	
	public function AddCell($text, $backgroundColor = array(), $padding = array()){
		
		$paddingBBox = $this->resizeBBox($this->BBox, $padding);
		
		
		if(!isset($this->CURFONT)){
			$this->SetFont("Helvetica");
		}
		
		if(!isset($this->maxCellY)) {
			$this->y += $this->fontHeight + $this->fontDescender - $this->fontDescender;
		}
		
		$this->y = $paddingBBox[3];
		
		$this->y -= $this->fontHeight;
		
		
		
		// store the cell style, if page break is required
		$this->cellStyles[$this->cellIndex] = array('backgroundColor'=>$backgroundColor, 'padding'=>$padding);
		
		// force page break before writting any text content as it does not fit to the current font size
		if($this->y < $this->initialBBox[1] && Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_CELL) ){
			$this->pageBreak = true;
			$this->pageBreakCells[$this->cellIndex] = $text;
			$this->cellIndex++;
			if($this->cellIndex >= $this->numCells){
				$this->endRow(true);
			}
			return;
		}
		
		$this->x = $this->BBox[0];
		$this->BBox[2] = $this->x + $this->columnWidths[$this->cellIndex];
		
		// amend the margin to display table border completely
		if(Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE)){
			if(isset($this->lineWeight)){
				$lw = $this->lineWeight / 2;
			} else {
				$lw = 0.5;
			}
			
			if($this->cellIndex == 0){
				$this->BBox = $this->resizeBBox($this->BBox, array('left'=>$lw));
				$this->x += $lw;
				if($this->rowIndex == 0){
					$this->BBox = $this->resizeBBox($this->BBox, array('top'=>$lw));
					$this->y -= $lw;
				}
			} else if($this->cellIndex + 1 >= $this->numCells){
				$this->BBox = $this->resizeBBox($this->BBox, array('right'=>$lw));
			}
			
			/*if($this->rowIndex == 0){
				$this->y -= $lw;
			}*/
		}

		$p = $this->AddText($text);
		
		if(isset($p)){
			$t = substr($text, $p);
			if(!empty($t)){
				$this->pageBreak = true;
				
				$this->pageBreakCells[$this->cellIndex] = $t;
			}
		}
		
		if(is_array($backgroundColor) && count($backgroundColor) >= 3){
			$this->cb->DoCall($this->BBox);
			$app = $this->cb->NewAppearance(array());
			$app->ZIndex = -1;
			
			$app->AddColor($backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
			
			$app->AddRectangle(0, 0, 0, 0, true);
		}
		
		if(!isset($this->maxCellY) || $paddingBBox[1] < $this->maxCellY){
			$this->maxCellY = $paddingBBox[1];
		}
		
		
		$this->cellIndex++;
		if($this->cellIndex >= $this->numCells){
			$this->endRow();
		} else {
			$this->y = $this->BBox[3] - $this->fontDescender;
			$this->BBox[0] = $this->BBox[2]; //$this->columnWidths[$this->cellIndex - 1];
		}
		
	}
	
	private function endRow($endOfTable = false){
		//echo "\nENDROW: ".$this->maxCellY;
		$this->cb->SetAllBBox(array(
					'ly'=> $this->maxCellY + $this->fontDescender
				)
		);
		
		// reset cell counter
		$this->cellIndex = 0;
		// increase the row number
		$this->rowIndex++;
		
		// reset x position
		$this->BBox[0] = $this->initialBBox[0];

		// draw the row border
		if(isset($this->lineWeight) && !$endOfTable && ( (Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_ROW) && $this->rowIndex > 1) || Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_FIRSTROW) && $this->rowIndex == 2) ){
			$lw = $this->lineWeight / 2;
			
			if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_ROWS)){
				$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q % DEBUG OUTPUT";
			}
			
			$bbox = $this->BBox;
			$bbox[1] = $bbox[3];
			
			$this->cb->DoCall($bbox);
			$app = $this->cb->NewAppearance();
			$app->AddLine(0, 0, 0, 0, $this->lineStyle);
		}
		
		// draw the column border
		if(isset($this->lineWeight) && Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_COLUMN) ){
			$lw = $this->lineWeight / 2;
			
			$nx = $this->BBox[0];
			$bbox = $this->BBox;
			$bbox[1] = $this->maxCellY + $this->fontDescender;
			
			for ($i=0; $i < ($this->numCells - 1); $i++) {
				$nx += $this->cellWidth;
				$bbox[0] = $nx;
				$bbox[2] = $nx;
				
				
				$this->cb->DoCall($bbox);
				$app = $this->cb->NewAppearance();
				$app->AddLine(0, 0, 0, 0, $this->lineStyle);
			}
		}
		$this->cb->Callback(0, 0, true);
		
		// set the Y position for the next row
		$this->BBox[3] = $this->maxCellY + $this->fontDescender;
		$this->y = $this->maxCellY;

		if($this->pageBreak){
			$bbox = $this->setBackground();
			
			$obj = $this->DoClone($this);
			$this->pages->addObject($obj, true);
			
			$this->contents = '';
			
			$this->BBox = $this->initialBBox;
					
			$p = $this->pages->NewPage($this->page->Mediabox);
			$this->page = $p;
			$this->cb = new Cpdf_Callbacks($this->pages, 'add');
			
			
			if(Cpdf_Common::IsDefined($this->BreakPage, Cpdf_Content::PB_BLEEDBOX) ){
				$this->initialBBox[1] = $this->page->Bleedbox[1];
				$this->initialBBox[3] = $this->page->Bleedbox[3];
				$this->BBox[3] = $this->initialBBox[3];
				$this->BBox[1] = $this->initialBBox[1];
			}
			
			if(Cpdf_Common::IsDefined($this->pages->DEBUG, Cpdf_Common::DEBUG_TABLE)){
				$this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re',$this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q % DEBUG OUTPUT";
			}
			
			$this->BBox[3] = $this->initialBBox[3];
			
			// force to rsize the BBox
			$this->BBox[1] = $this->BBox[3];
			
			$this->x = $this->initialBBox[0];
			$this->y = $this->BBox[3] - $this->fontDescender;
			
			$this->maxCellY = $this->BBox[3];
			
			$this->app = $this->pages->NewAppearance($this->initialBBox);
			
			$this->pageBreak = false;
			
			if(count($this->pageBreakCells) > 0){
				$pcells = $this->pageBreakCells;
				$this->pageBreakCells= array();
				
				for($i = 0; $i < $this->numCells; $i++){
					if(isset($pcells[$i])){
						$this->AddCell($pcells[$i], $this->cellStyles[$i]['backgroundColor'], $this->cellStyles[$i]['padding']);
					}else{
						$this->AddCell("",$this->cellStyles[$i]['backgroundColor'], $this->cellStyles[$i]['padding']);
					}
				}
			}
		}
	}
	
	private function setBackground(){
		$bbox = $this->initialBBox;
		if(!isset($this->app)){
			return $bbox;
		}
		
		$this->app->ZIndex = -2;
		$this->app->SetPageMode($this->Paging);
		
		$filled = false;
		if(is_array($this->backgroundColor) && count($this->backgroundColor) == 3){
			$filled = true;
			$this->app->AddColor($this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
		}
		// width and height can be set to zero as it will use the BBox to calculate max widht and max height
		if($this->Fit){
			// TODO: correct the table border 
			$newY = $this->maxCellY - $this->initialBBox[1] - ($this->lineWeight * 2) + $this->fontDescender;
			$height = $this->initialBBox[3] - $this->maxCellY + $this->lineWeight - $this->fontDescender;
			
			if(Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE) ){			
				$this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, $this->lineStyle);
			} else if($filled) {
				$this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, $this->lineStyle);
			}
			$bbox[1] = $this->BBox[1] + $this->fontDescender;
		} else {
			if(Cpdf_Common::IsDefined($this->DrawLine, Cpdf_Table::DRAWLINE_TABLE) ){	
				$this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
			}else if($filled){
				$this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
			}
		}
		
		if(strlen($this->app->Output()) <= 0){
			$this->app->Paging = Cpdf_Content::PMODE_NONE;
		}
		
		return $bbox;
	}
	
	/**
	 * End the table and return bounding box to define next Appearance or text object
	 */
	public function EndTable(){
		$this->pageBreakCells = array();
		$this->endRow(true);
		
		$bbox = $this->setBackground();
		$this->x = $bbox[0];
		$this->y = $bbox[1];
	}
	
}


/**
 * Callback class
 * Used for custom text directives, like <c:alink> or any other other user defined callback function
 */
class Cpdf_Callbacks {
	private $paging;
	 
	private $callbacks;
	private $callbackIndex;
	
	private $pages;
	
	private $annots;
	private $ap;

	public $FontName;
	public $FontSize;
	public $FontStyle;
	
	public function __construct(&$pages, $paging = 1){
		$this->pages = &$pages;
		
		$this->callbacks = array();
		$this->callbackIndex = -1;
		$this->annots = array();
		
		$this->SetPageMode($paging);
	}
	
	public function SetPageMode($pm){
		$this->paging = $pm;
	}
	
	public function SetBBox($bbox){
		$current = &$this->callbacks[$this->callbackIndex];
		if(is_array($bbox)){
			if(count($bbox) == 4){ // update the whole BBox
				$current = $bbox;
			}else {  // or update individually
				if(isset($bbox['lx']))
					$current[0] = $bbox['lx'];
				if(isset($bbox['ly']))
					$current[1] = $bbox['ly'];
				if(isset($bbox['ux']))
					$current[2] = $bbox['ux'];
				if(isset($bbox['uy']))
					$current[3] = $bbox['uy'];
				
				if(isset($bbox['addlx']))
					$current[0] += $bbox['addlx'];
				if(isset($bbox['addly']))
					$current[1] += $bbox['addly'];
				if(isset($bbox['addux']))
					$current[2] += $bbox['addux'];
				if(isset($bbox['adduy']))
					$current[3] += $bbox['adduy'];
			}
		}else{
			Cpdf_Common::DEBUG("Failed to set Bounding box (Cpdf_Callbacks)", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
		}
	}
	
	public function SetAllBBox($bbox){
		$c = $this->callbackIndex;
		while($this->callbackIndex >= 0){
			$this->SetBBox($bbox);
			$this->callbackIndex--;
		}
		
		$this->callbackIndex = $c;
	}
	
	public function GetBBox(){
		return $this->callbacks[$this->callbackIndex];
	}
	
	public function NewAppearance($bbox = null){
		if(!isset($this->ap)){
			// DEBUGGING: DO NOT SHOW BBOX for callback appearances
			$tmp = $this->pages->DEBUG;
			$this->pages->DEBUG = $this->pages->DEBUG ^ ($this->pages->DEBUG & Cpdf_Common::DEBUG_BBOX);
			
			$this->ap = &$this->pages->NewAppearance($bbox);
			// DEBUGGING: reset the DEBUG setting
			$this->pages->DEBUG = $tmp;
		}
		$this->ap->SetPageMode($this->paging);
		
		$this->ap->AwaitCallback = $this->callbackIndex;
		
		return $this->ap;
	}
	
	public function NewAnnotation($annoType, $bbox = null, $border, $color){
		if(!isset($bbox)){
			$bbox = $this->callbacks[$this->callbackIndex];
		}
		$annot = &$this->pages->NewAnnotation($annoType, $bbox, $border, $color);
		$annot->SetPageMode($this->paging);
		$this->annots[$this->callbackIndex] = &$annot; 
		return $annot;
	}
	
	public function DoCall($bbox){
		$this->callbackIndex++;
		$this->SetBBox($bbox);
	}
	
	public function Callback($offsetX = 0, $offsetY = 0, $resize = false){
		$c = $this->callbackIndex;
		for($i = 0; $i <= $c; $i++){
			
			$curBBox = &$this->callbacks[$i];
			// DEBUG
			//print_r($curBBox);
			
			$curBBox[0] += $offsetX;
			$curBBox[2] += $offsetX;
			
			$curBBox[1] += $offsetY;
			$curBBox[3] += $offsetY;
			
			if(isset($this->annots[$i])){
				$this->annots[$i]->Callback($curBBox);
				unset($this->annots[$i]);
			}
			
			if(is_object($this->ap)){
				$this->ap->AwaitCallback = $i;
				$this->ap->Callback($curBBox, $resize);
			}
		}
		
		$this->callbackIndex = -1;
		$this->ap = null;
		//$this->ap->AwaitCallback = $this->callbackIndex;
	}
}

/**
 * Class object to provide Annotations, like Links, text and freetext
 * 
 * TODO: Audio and video annotations
 */
class Cpdf_Annotation extends Cpdf_Content{
	public $Type = '/Annot';
	
	private $annotation;
	
	private $rectangle;
	private $border;
	private $color;
	
	private $title;
	
	private $destTarget;
	
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
	
	public function __construct(&$page,$annoType,$rectangle, $border = null, $color = null, $flags = array()){
		parent::__construct($page);
		
		$this->annotation = strtolower($annoType);
		$this->rectangle = $rectangle;
		$this->color = $color;
		$this->border = $border;
		
		$f = 0;
		// set bitflags for annotation properties
		if(is_array($flags)){
            foreach($flags as $v){
                switch(strtolower($v)){
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
	
	public function SetText($text, $title = ''){
		$this->contents = $text;
		$this->title = $title;
	}
	
	public function SetUrl($url){
		$this->url = $url;
	}
	
	/**
	 * set the annotation to an internal destination either as name or page number
	 * Name requires to hace Cpdf_Content->Name set to a unqiue string value
	 * 
	 * @param mixed destination name or page number
	 */
	public function SetDestination($targetName){
		$this->destTarget = $targetName;
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
	public function SetAppearance(&$normal, &$rollover = null, &$down = null){
		$this->Appearances = array('N'=>$normal, 'R'=>$rollover, 'D'=>$down);
	}
	
	public function Callback($bbox){
		// update the bounding box
		$this->rectangle = $bbox;
	}
	
	public function Output($noKey = false){
		$res='';
		if(is_array($this->rectangle) && count($this->rectangle) == 4){
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
			foreach($this->rectangle as $v){
				$res.= sprintf("%.4F ", $v);
			}
			$res.=']';
			
			// its an external url or an internal destination link
			if(!empty($this->url)){
				$res.= ' /A << /S /URI /URI ('.$this->url.') >>';
			} else if(($pageNum=intval($this->destTarget)) > 0){
				$page = $this->pages->GetPageByNo($pageNum);
				if(is_object($page)){
					$res.=' /Dest ['.$page->ObjectId.' 0 R /Fit]';
				}
			} else if(!empty($this->destTarget)) {
				$res.=' /Dest /'.$this->destTarget;
			}
			
			if(!empty($this->title)){
				$res.=' /T ('.$this->title.')';
			}
			
			if(!empty($this->contents)){
				$res.=' /Contents ('.$this->contents.')';
			}
			
			// set the color via object class Cpdf_Color
			if(is_object($this->color)){
				$c = $this->color;
				$res.=' /C '.$c->Output();
			}
			
			// PDF-1.1 hide the old border
			$res.=' /Border [0 0 0]';
			// set the border style via object class Cpdf_BorderStyle
			if(is_object($this->border)){
				$b = $this->border;
				$res.= ' /BS <<'.$b->Output().' >>';
			}
			
			// put the AP (appearance streams) as reference into the annot
			if(isset($this->Appearances)){
				$res.= ' /AP <<';
				foreach($this->Appearances as $k=>$v){
					if(isset($v)){
						$res.= " /$k ".$v->ObjectId." 0 R";
					}
				}
				$res.=' >>';
			}
			
			if($this->flags > 0){
				$res.= ' /F '.$this->flags;
			}
		} else {
			Cpdf_Common::DEBUG("Invalid ractangle - array must contain 4 elements", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
		}
		return $res;
	}
	
	public function OutputAsObject(){
		$res = "\n$this->ObjectId 0 obj\n<< ".$this->Output(true)." >>\nendobj";
		$this->page->pages->AddXRef($this->ObjectId, strlen($res));
		return $res;
	}
}

/**
 * Class object to support JPEG and PNG images
 */
class Cpdf_Image extends Cpdf_Content {
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
	 * Constructor to build a image object
	 * 
	 * @param Cpdf_Pages $pages object of the main pdf_Pages object
	 * @param string $filepath can be either a file or an url path of an image
	 */
	public function __construct(&$pages, $filepath){
		parent::__construct($pages);
		
		if (stristr($filepath, '://')) { //copy to temp file
			// PHP5: file_get_contents
            $cont = file_get_contents($filepath);
            
            $filepath = tempnam($pages->TempPath, "Cpdf_Image");
            $fp2 = @fopen($filepath, "w");
            fwrite($fp2, $cont);
            fclose($fp2);
        }
		
		if(file_exists($filepath)){
			$this->source = $filepath;
			$imginfo = getimagesize($filepath);
			
			$this->orgWidth = $imginfo[0];
			$this->orgHeight = $imginfo[1];
			$this->ImageType = $imginfo[2];
			
			if(isset($imginfo['channels'])){
				$this->channels = $imginfo['channels'];
			}
			
			$this->bits = $imginfo['bits'];
			
			$this->Width = $this->orgWidth;
			$this->Height = $this->orgHeight;
			$this->parseImage();
		} else {
			Cpdf_Common::DEBUG("Image file could not be found '$filepath'", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
		}
	}
	
	/**
	 * resize the image and restore it by using gdlib
	 */
	public function Resize($width = null, $height = null){
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
	
	private function parseImage(){
		switch ($this->ImageType) {
			case IMAGETYPE_JPEG:
				$this->data = file_get_contents($this->source);
				
				if($this->channels == 1){
					$this->colorspace = '/DeviceGray';
				}else {
					$this->colorspace = '/DeviceRGB';
				}
				
				$entries['Filter'] = '/DCTDecode';
				
				break;
			case IMAGETYPE_PNG:
				$data = file_get_contents($this->source);
				
				$iChunk = $this->readPngChunks($data);
				

				
								
				if(!$iChunk['haveHeader']){
					Cpdf_Common::DEBUG("Info header missing for PNG image", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
					return;
				}
				
				if(!isset($iChunk['info'])){
					Cpdf_Common::DEBUG("Additional Info missing for PNG image", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
					return;
				}
				
				if(isset($iChunk['info']['interlaceMethod']) && $iChunk['info']['interlaceMethod']){
					Cpdf_Common::DEBUG("No support for interlaces png images for PDF", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
					return;
				}
				
				if($iChunk['info']['bitDepth'] > 8){
					Cpdf_Common::DEBUG("Only bit depth of 8 or lower is supported for PNG", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
					return;
				}
				
				if($iChunk['info']['colorType'] == 1 || $iChunk['info']['colorType'] == 5 || $iChunk['info']['colorType']== 7){
					Cpdf_Common::DEBUG("Unsupported  color type for PNG", Cpdf_Common::DEBUG_MSG_WARN, $this->pages->DEBUG);
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
				
				$this->data = $iChunk['idata'];
				$this->palette = $iChunk['pdata'];
				$this->transparency = $iChunk['transparency'];
				
				break;
			case IMAGETYPE_GIF:
				break;
			default:
				Cpdf_Common::DEBUG("Unsupported image type", Cpdf_Common::DEBUG_MSG_ERR, $this->pages->DEBUG);
				break;
		}
	}
	
	
	/**
     * extract an integer from a position in a byte stream
     *
     * @access private
     */
    private function getBytes(&$data,$pos,$num){
        // return the integer represented by $num bytes from $pos within $data
        $ret=0;
        for ($i=0;$i<$num;$i++){
            $ret=$ret*256;
            $ret+=ord($data[$pos+$i]);
        }
        return $ret;
    }
	
	/**
     * reads the PNG chunk
     * @param $data - binary part of the png image
     * @access private
     */
    private function readPngChunks(&$data){
        $default = array('info'=> array(), 'transparency'=> null, 'idata'=> null, 'pdata'=> null, 'haveHeader'=> false);
        // set pointer
        $p = 8;
        $len = strlen($data);
        // cycle through the file, identifying chunks
        while ($p<$len){
            $chunkLen = $this->getBytes($data,$p,4);
            $chunkType = substr($data,$p+4,4);
            
            switch($chunkType){
                case 'IHDR':
                //this is where all the file information comes from
                $default['info']['width']=$this->getBytes($data,$p+8,4);
                $default['info']['height']=$this->getBytes($data,$p+12,4);
                $default['info']['bitDepth']=ord($data[$p+16]);
                $default['info']['colorType']=ord($data[$p+17]);
                $default['info']['compressionMethod']=ord($data[$p+18]);
                $default['info']['filterMethod']=ord($data[$p+19]);
                $default['info']['interlaceMethod']=ord($data[$p+20]);
                
                $default['haveHeader'] = true;
                
                if ($default['info']['compressionMethod']!=0){
                	Cpdf_Common::DEBUG("unsupported compression method for PNG image", Cpdf_Common::DEBUG_MSG_ERR, $this->pages->DEBUG);
                }
                if ($default['info']['filterMethod']!=0){
                	Cpdf_Common::DEBUG("unsupported filter method for PNG image", Cpdf_Common::DEBUG_MSG_ERR, $this->pages->DEBUG);
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
                    for ($i=$chunkLen;$i>=0;$i--){
                        if (ord($data[$p+8+$i])==0){
                            $trans=$i;
                        }
                    }
                    $default['transparency']['data'] = $trans;
        
                } elseif($default['info']['colorType'] == 0) { // grayscale
                    // corresponding to entries in the plte chunk
                    // Gray: 2 bytes, range 0 .. (2^bitdepth)-1
        
                    // $transparency['grayscale']=$this->getBytes($data,$p+8,2); // g = grayscale
                    $default['transparency']['type']='indexed';
                    $default['transparency']['data'] = ord($data[$p+8+1]);
                } elseif($default['info']['colorType'] == 2) { // truecolor
                    // corresponding to entries in the plte chunk
                    // Red: 2 bytes, range 0 .. (2^bitdepth)-1
                    // Green: 2 bytes, range 0 .. (2^bitdepth)-1
                    // Blue: 2 bytes, range 0 .. (2^bitdepth)-1
                    $default['transparency']['r']=$this->getBytes($data,$p+8,2); // r from truecolor
                    $default['transparency']['g']=$this->getBytes($data,$p+10,2); // g from truecolor
                    $default['transparency']['b']=$this->getBytes($data,$p+12,2); // b from truecolor
                } else if($default['info']['colorType'] == 6 || $default['info']['colorType'] == 4) {
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
                    $tmpfile_alpha=tempnam($this->pages->TempPath,'Cpdf_Image');
                    
                    imagepng($imgalpha, $tmpfile_alpha);
                    imagedestroy($imgalpha);
                    
                    $alphaData = file_get_contents($tmpfile_alpha);
                    // nested method call to receive info on alpha image
                    $alphaImg = $this->readPngChunks($alphaData);
                    // use 'pdate' to fill alpha image as "palette". But it s the alpha channel
                    $default['pdata'] = $alphaImg['idata'];
                    
                    // generate true color image with no alpha channel
                    $tmpfile_tt=tempnam($this->pages->TempPath,'Cpdf_Image');
                    
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
                    $default['pdata'] = substr($data,$p+8,$chunkLen); 
                break;
                case 'IDAT':
                    $default['idata'] .= substr($data,$p+8,$chunkLen);
                break;
                case 'tRNS': // this HEADER info is optional. More info: rfc2083 (http://tools.ietf.org/html/rfc2083)
                    // error_log('OPTIONAL HEADER -tRNS- exist:');
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
	
	public function OutputAsObject(){
		
		$res = "\n$this->ObjectId 0 obj";
		$res.="\n<< /Subtype /Image";
		
		$entries = array(
						'Width' => $this->orgWidth,
						'Height' => $this->orgHeight,
					);
		
		$paletteObj = '';
		
		switch ($this->ImageType) {
			case IMAGETYPE_JPEG:
				if($this->channels == 1){
					$entries['ColorSpace'] = '/DeviceGray';
				}else{
					$entries['ColorSpace'] = '/DeviceRGB';
				}
				$entries['Filter'] = '/DCTDecode';
				$entries['BitsPerComponent'] = $this->bits;
				break;
			case IMAGETYPE_PNG:
				if(strlen($this->palette)){
					$pId = ++$this->pages->objectNum;
					$paletteObj = "\n".$pId." 0 obj";
					$paletteObj.= "\n<< /Subtype /Image /Width ".$this->orgWidth." /Height ".$this->orgHeight;
					
					$paletteObj.=' /Filter /FlateDecode';
					$paletteObj.= ' /Length '.strlen($this->palette);
					
					$paletteObj.= ' /ColorSpace /DeviceGray';
					
					$paletteObj.= ' /BitsPerComponent '.$this->bits;
					$paletteObj.= ' /DecodeParms << /Predictor 15 /Colors 1 /BitsPerComponent '.$this->bits.' /Columns '.$this->orgWidth.' >>';
					$paletteObj.= " >>\n";
					
					$paletteObj.= "stream\n".$this->palette."\nendstream";
					$paletteObj.= "\nendobj";
					
					$this->pages->AddXRef($pId, strlen($paletteObj));
					
					if(isset($this->transparency)){
						switch ($this->transparency['type']) {
							case 'indexed':
								$tmp=' ['.$this->transparency['data'].' '.$this->transparency['data'].'] ';
								$entries['Mask'] = $tmp;
								$entries['ColorSpace'] = '[/Indexed /DeviceRGB '.(strlen($this->palette)/3-1).' '.$pId.' 0 R]';
								break;
							case 'alpha':
								$entries['SMask'] = $pId.' 0 R';
								$entries['ColorSpace'] = '/'.$this->colorspace;
								break;
						}
					}
				} else {
						$entries['ColorSpace'] = '/'.$this->colorspace;
					}
				
				$entries['BitsPerComponent'] = $this->bits;
				$entries['Filter'] = '/FlateDecode';
				$entries['DecodeParms'] = '<< /Predictor 15 /Colors '.$this->numColors.' /Columns '.$this->orgWidth.' /BitsPerComponent '.$this->bits.'>>';
				break;
		}

		$tmp = $this->data;
		// gzcompress
		if(function_exists('gzcompress') && $this->Compression && $this->ImageType != IMAGETYPE_PNG){
			if(isset($entries['Filter'])){
				$entries['Filter'] = '[/FlateDecode '.$entries['Filter'].']';
			} else {
				$entries['Filter']= '/FlateDecode';
			}
			$tmp = gzcompress($tmp, $this->Compression);
		}
		// encryption
		if(isset($this->page->pages->encryptionObject)){
			$encObj = &$this->page->pages->encryptionObject;
			$encObj->encryptInit($this->ObjectId);
			$tmp = $encObj->ARC4($tmp);
		}
		
		foreach ($entries as $k => $v) {
			$res.= " /$k $v";
		}
		$res.=' /Length '.strlen($tmp).' >>';
		$res.= "\nstream\n".$tmp."\nendstream";
		$res.= "\nendobj";
		
		$this->pages->AddXRef($this->ObjectId, strlen($res));
		
		return $res.$paletteObj;
	}
}

class Cpdf_Color{
    public $colorArray;
    public $stroke;
	
    public function __construct($color = array(), $stroke = true){
        $this->colorArray = $color;
		$this->stroke = $stroke;
    }
    
    public function Output($asArray = true, $withColorspace = false){
        $res='';
        
        if(is_array($this->colorArray)){
            foreach($this->colorArray as $v){
                $res.= sprintf("%.3F ",$v);
            }
            
            if($withColorspace){
                if(count($this->colorArray) >= 4){ // DeviceCMYK
                    $res.= ($this->stroke)?'K':'k';
                } else if(count($this->colorArray) >= 3){ // DeviceRGB
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
class Cpdf_BorderStyle{
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
    public function __construct($weight = 0, $style = 'solid', $dashArray = array()){
        $this->Weight = $weight;
        $this->Style = $style;
        $this->dashArray = $dashArray;
    }
    
    public function Output(){
        $res='';
        if($this->Weight > 0 && $this->Style != 'none'){
            $res = " /Type $this->Type /W ".sprintf("%.3F",$this->Weight);
            switch(strtolower($this->Style)){
            	case 'underline':
                case 'underlined':
                    $res .= ' /S /U';
                    break;
                case 'dash':
                    $res .= ' /S /D /D [';
                    if(is_array($this->dashArray) && count($this->dashArray) > 0){
                    	foreach($this->dashArray as $v){
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

class Cpdf_LineStyle{
	private $weight;
	private $capStyle;
	private $joinStyle;
	private $dashStyle;
	
	public function __construct($weight = 0, $cap='', $join='', $dash=array()){
		$this->weight = $weight;
		$this->SetCap($cap);
		$this->SetJoin($join);
		
		if(is_array($dash)){
			if(count($dash) == 3){
				$this->SetDashes($dash[0], $dash[1], $dash[2]);
			} else if(count($dash) == 2){
				$this->SetDashes($dash[0], $dash[1]);
			} else if(count($dash) == 1) {
				$this->SetDashes($dash[0], $dash[0]);
			}
			
		}
	}
	
	public function GetWeight(){
		return $this->weight;
	}
	
	public function SetCap($name = 'butt'){
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

	public function SetJoin($name = 'miter'){
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
	
	public function SetDashes($on, $off, $phase = 0){
		$this->dashStyle = array($on, $off, $phase);
	}
	
	public function Output(){
		$res = '';
		
		$res.= sprintf("%.3F w",$this->weight);
		
		if(isset($this->capStyle)){
			$res.= ' '.$this->capStyle.' J';
		}
		if(isset($this->joinStyle)){
			$res.= ' '.$this->joinStyle.' j';
		}
		if(is_array($this->dashStyle) && count($this->dashStyle) == 3){
			if($this->dashStyle[0] > 0){
				$res.= ' ['.$this->dashStyle[0];
				if($this->dashStyle[1] != $this->dashStyle[0]){
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

?>