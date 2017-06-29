<?php

namespace ROSPDF;

/**
 * Encryption support for PDF up to version 1.4.
 *
 * TODO: Extend the encryption for PDF 1.4 to use a user defined key length up to 128bit
 */
class CpdfEncryption
{
    public $ObjectId;

    /**
     * internal encryption mode (1 - 40bit, 2 = upto 128bit).
     */
    private $encryptionMode;
    /**
     * default padding string for PDF encryption.
     */
    private $encryptionPad;
    /**
     * internal encryption key.
     */
    private $encryptionKey;
    /**
     * internal permission set.
     */
    private $permissionSet;
    /**
     * current Cpdf class object.
     */
    private $pages;
    /**
     * user password.
     */
    private $userPass;
    /**
     * owner password.
     */
    private $ownerPass;

    /**
     * Constructor to enable PDF encryption.
     *
     * More details, see Cpdf->SetEncryption(args)
     */
    public function __construct(&$pages, $mode, $user = '', $owner = '', $permission = null)
    {
        $this->pages = &$pages;
        $this->userPass = $user;
        $this->ownerPass = $owner;
        $this->encryptionPad = "\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A";

        if ($mode > 1) {
            // increase the pdf version to support 128bit encryption
            if ($pages->PDFVersion < 1.4) {
                $pages->PDFVersion = 1.4;
            }
            $p = bindec('01111111111111111111000011000000'); // revision 3 is using bit 3 - 6 AND 9 - 12
        } else {
            $mode = 1; // make sure at least the 40bit encryption is set
            $p = bindec('01111111111111111111111111000000'); // while revision 2 is using bit 3 - 6 only
        }

        $options = ['print' => 4, 'modify' => 8, 'copy' => 16, 'add' => 32, 'fill' => 256, 'extract' => 512, 'assemble' => 1024, 'represent' => 2048];

        if (is_array($permission)) {
            foreach ($permission as $k => $v) {
                if ($v && isset($options[$k])) {
                    $p += $options[$k];
                } elseif (isset($options[$v])) {
                    $p += $options[$v];
                }
            }
        }

        $this->permissionSet = $p;
        // set the encryption mode to either RC4 40bit or RC4 128bit
        $this->encryptionMode = $mode;

        if (strlen($this->ownerPass) == 0) {
            $this->ownerPass = $this->userPass;
        }

        $this->init();
    }

    /**
     * internal method to initialize the encryption.
     */
    private function init()
    {
        // Pad or truncate the owner password
        $this->ownerPass = substr($this->ownerPass.$this->encryptionPad, 0, 32);
        $this->userPass = substr($this->userPass.$this->encryptionPad, 0, 32);

        // convert permission set into binary string
        $permissions = sprintf('%c%c%c%c', ($this->permissionSet & 255), (($this->permissionSet >> 8) & 255), (($this->permissionSet >> 16) & 255), (($this->permissionSet >> 24) & 255));

        $this->ownerPass = $this->encryptOwner();
        $this->userPass = $this->encryptUser($permissions);
    }

    /**
     * encryption algorithm 3.4.
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
            $uvalue = $this->ARC4($userHash);

            $len = strlen($this->encryptionKey);
            for ($i = 1; $i <= 19; ++$i) {
                $ek = '';
                for ($j = 0; $j < $len; ++$j) {
                    $ek .= chr(ord($this->encryptionKey[$j]) ^ $i);
                }
                $this->ARC4_init($ek);
                $uvalue = $this->ARC4($uvalue);
            }
            $uvalue .= substr($this->encryptionPad, 0, 16);
        } else { // if it is the RC4 40bit encryption
            $this->ARC4_init($this->encryptionKey);
            $uvalue = $this->ARC4($this->encryptionPad);
        }

        return $uvalue;
    }

    /**
     * encryption algorithm 3.3.
     */
    private function encryptOwner()
    {
        $keylength = 5;
        if ($this->encryptionMode > 1) {
            $keylength = 16;
        }

        $ownerHash = $this->md5_16($this->ownerPass); // PDF 1.4 - repeat this 50 times in revision 3
        if ($this->encryptionMode > 1) { // if it is the RC4 128bit encryption
            for ($i = 0; $i < 50; ++$i) {
                $ownerHash = $this->md5_16($ownerHash);
            }
        }

        $ownerKey = substr($ownerHash, 0, $keylength); // PDF 1.4 - Create the encryption key (IMPORTANT: need to check Length)

        $this->ARC4_init($ownerKey); // 5 bytes of the encryption key (hashed 50 times)
        $ovalue = $this->ARC4($this->userPass); // PDF 1.4 - Encrypt the padded user password using RC4

        if ($this->encryptionMode > 1) {
            $len = strlen($ownerKey);
            for ($i = 1; $i <= 19; ++$i) {
                $ek = '';
                for ($j = 0; $j < $len; ++$j) {
                    $ek .= chr(ord($ownerKey[$j]) ^ $i);
                }
                $this->ARC4_init($ek);
                $ovalue = $this->ARC4($ovalue);
            }
        }

        return $ovalue;
    }

    /**
     * initialize the ARC4 encryption.
     */
    private function ARC4_init($key = '')
    {
        $this->arc4 = '';
        // setup the control array
        if (strlen($key) == 0) {
            return;
        }
        
        $s = array();
        for ($i = 0; $i < 256; $i++) {
            $s[$i] = $i;
        }
        $j = 0;
        for ($i = 0; $i < 256; $i++) {
            $j = ($j + $s[$i] + ord($key[$i % strlen($key)])) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
        }
        $this->arc4 = $s;
    }

    /**
     * initialize the encryption for processing a particular object.
     */
    public function encryptInit($id)
    {
        $tmp = $this->encryptionKey;
        $hex = dechex($id);
        if (strlen($hex) < 6) {
            $hex = substr('000000', 0, 6 - strlen($hex)).$hex;
        }
        $tmp .= chr(hexdec(substr($hex, 4, 2))).chr(hexdec(substr($hex, 2, 2))).chr(hexdec(substr($hex, 0, 2))).chr(0).chr(0);
        $key = $this->md5_16($tmp);
        if ($this->encryptionMode > 1) {
            $this->ARC4_init(substr($key, 0, 16)); // use max 16 bytes for RC4 128bit encryption key
        } else {
            $this->ARC4_init(substr($key, 0, 10)); // use (n + 5 bytes) for RC4 40bit encryption key
        }
    }

    /**
     * calculate the 16 byte version of the 128 bit md5 digest of the string.
     */
    private function md5_16($string)
    {
        return pack('H*', md5($string));
    }

    /**
     * internal method to convert string to hexstring (used for owner and user dictionary).
     *
     * @param $string - any string value
     */
    public function strToHex($string)
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); ++$i) {
            $hex .= sprintf('%02x', ord($string[$i]));
        }

        return $hex;
    }

    public function hexToStr($hex)
    {
        $str = '';
        for ($i = 0; $i < strlen($hex); $i += 2) {
            $str .= chr(hexdec(substr($hex, $i, 2)));
        }

        return $str;
    }

    public function ARC4($text)
    {
        $i = 0;
        $j = 0;
        $s = $this->arc4;
        $res = '';
        for ($y = 0; $y < strlen($text); $y++) {
            $i = ($i + 1) % 256;
            $j = ($j + $s[$i]) % 256;
            $x = $s[$i];
            $s[$i] = $s[$j];
            $s[$j] = $x;
            $res .= $text[$y] ^ chr($s[($s[$i] + $s[$j]) % 256]);
        }
        
        return $res;
    }

    public function OutputAsObject()
    {
        $res = "\n".$this->ObjectId." 0 obj\n<<";
        $res .= ' /Filter /Standard';
        if ($this->encryptionMode > 1) { // RC4 128bit encryption
            $res .= ' /V 2 /R 3 /Length 128';
        } else {
            $res .= ' /V 1 /R 2';
        }
        $res .= ' /O <'.$this->strToHex($this->ownerPass).'>';
        $res .= ' /U <'.$this->strToHex($this->userPass).'>';
        $res .= ' /P '.$this->permissionSet;
        $res .= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
