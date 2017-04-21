<?php

namespace ROSPDF;

/**
 * Class object to support JPEG and PNG images
 * <p>
 * Example usage:
 * </p>
 * <pre>
 * $pdf = new Cpdf(Cpdf::$Layout['A4']);
 * $app = $pdf->NewAppearance();
 * $app->AddImage('left', 'middle', 'images/test_indexed.png');
 * $pdf->Stream();
 * </pre>.
 */
class CpdfImage extends CpdfContent
{
    public $ImageNum;

    private $source;

    private $channels;
    private $bits;

    private $colorspace;

    private $numColors;

    private $data;
    /**
     * Used for PNG only.
     */
    private $palette;
    private $paletteObj;
    /**
     * Used for PNG only.
     */
    private $transparency;

    protected $orgWidth;
    protected $orgHeight;
    public $ImageType;

    public $Width;
    public $Height;

    /**
     * Constructor.
     *
     * @param CpdfPages $pages    object of the main pdf_Pages object
     * @param string    $filepath can be either a file or an url path of an image
     */
    public function __construct(&$pages, $filepath)
    {
        parent::__construct($pages);

        if (stristr($filepath, '://')) { //copy to temp file
            // PHP5: file_get_contents
            $cont = file_get_contents($filepath);

            $filepath = tempnam(ROSPDF_TEMPDIR, 'CpdfImage');
            $fp2 = @fopen($filepath, 'w');
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
            Cpdf::DEBUG("Image file could not be found '$filepath'", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }
    }

    /**
     * Resize the image (missing).
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
     * Parse the image content.
     */
    private function parseImage()
    {
        switch ($this->ImageType) {
            case IMAGETYPE_JPEG:
                $this->data = file_get_contents($this->source);
                break;
            case IMAGETYPE_PNG:
                $data = file_get_contents($this->source);

                $iChunk = $this->readPngChunks($data);

                if (!$iChunk['haveHeader']) {
                    Cpdf::DEBUG('Info header missing for PNG image', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

                    return;
                }

                if (!isset($iChunk['info'])) {
                    Cpdf::DEBUG('Additional Info missing for PNG image', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

                    return;
                }

                if (isset($iChunk['info']['interlaceMethod']) && $iChunk['info']['interlaceMethod']) {
                    Cpdf::DEBUG('No support for interlaces png images for PDF', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

                    return;
                }

                if ($iChunk['info']['bitDepth'] > 8) {
                    Cpdf::DEBUG('Only bit depth of 8 or lower is supported for PNG', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

                    return;
                }

                if ($iChunk['info']['colorType'] == 1 || $iChunk['info']['colorType'] == 5 || $iChunk['info']['colorType'] == 7) {
                    Cpdf::DEBUG('Unsupported  color type for PNG', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

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

                $this->applyPalette();

                break;
            case IMAGETYPE_GIF:
                break;
            default:
                Cpdf::DEBUG('Unsupported image type', Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                break;
        }
    }

    /**
     * Extract $num of bytes from $pos.
     */
    private function getBytes(&$data, $pos, $num)
    {
        // return the integer represented by $num bytes from $pos within $data
        $ret = 0;
        for ($i = 0; $i < $num; ++$i) {
            $ret = $ret * 256;
            $ret += ord($data[$pos + $i]);
        }

        return $ret;
    }

    /**
     * Read the PNG chunk.
     *
     * @param $data - binary part of the png image
     */
    private function readPngChunks(&$data)
    {
        $default = array('info' => array(), 'transparency' => null, 'idata' => null, 'pdata' => null, 'haveHeader' => false);
        // set pointer
        $p = 8;
        $len = strlen($data);
        // cycle through the file, identifying chunks
        while ($p < $len) {
            $chunkLen = $this->getBytes($data, $p, 4);
            $chunkType = substr($data, $p + 4, 4);

            switch ($chunkType) {
                case 'IHDR':
                //this is where all the file information comes from
                    $default['info']['width'] = $this->getBytes($data, $p + 8, 4);
                    $default['info']['height'] = $this->getBytes($data, $p + 12, 4);
                    $default['info']['bitDepth'] = ord($data[$p + 16]);
                    $default['info']['colorType'] = ord($data[$p + 17]);
                    $default['info']['compressionMethod'] = ord($data[$p + 18]);
                    $default['info']['filterMethod'] = ord($data[$p + 19]);
                    $default['info']['interlaceMethod'] = ord($data[$p + 20]);

                    $default['haveHeader'] = true;

                    if ($default['info']['compressionMethod'] != 0) {
                        Cpdf::DEBUG('unsupported compression method for PNG image', Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                    }
                    if ($default['info']['filterMethod'] != 0) {
                        Cpdf::DEBUG('unsupported filter method for PNG image', Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                    }

                    $default['transparency'] = array('type' => null, 'data' => null);

                    if ($default['info']['colorType'] == 3) { // indexed color, rbg
                        // corresponding to entries in the plte chunk
                        // Alpha for palette index 0: 1 byte
                        // Alpha for palette index 1: 1 byte
                        // ...etc...

                        // there will be one entry for each palette entry. up until the last non-opaque entry.
                        // set up an array, stretching over all palette entries which will be o (opaque) or 1 (transparent)
                        $default['transparency']['type'] = 'indexed';
                        //$numPalette = strlen($default['pdata'])/3;
                        $trans = 0;
                        for ($i = $chunkLen; $i >= 0; --$i) {
                            if (ord($data[$p + 8 + $i]) == 0) {
                                $trans = $i;
                            }
                        }
                        $default['transparency']['data'] = $trans;
                    } elseif ($default['info']['colorType'] == 0) { // grayscale
                        // corresponding to entries in the plte chunk
                        // Gray: 2 bytes, range 0 .. (2^bitdepth)-1

                        // $transparency['grayscale']=$this->getBytes($data,$p+8,2); // g = grayscale
                        $default['transparency']['type'] = 'indexed';
                        $default['transparency']['data'] = ord($data[$p + 8 + 1]);
                    } elseif ($default['info']['colorType'] == 2) { // truecolor
                        // corresponding to entries in the plte chunk
                        // Red: 2 bytes, range 0 .. (2^bitdepth)-1
                        // Green: 2 bytes, range 0 .. (2^bitdepth)-1
                        // Blue: 2 bytes, range 0 .. (2^bitdepth)-1
                        $default['transparency']['r'] = $this->getBytes($data, $p + 8, 2); // r from truecolor
                        $default['transparency']['g'] = $this->getBytes($data, $p + 10, 2); // g from truecolor
                        $default['transparency']['b'] = $this->getBytes($data, $p + 12, 2); // b from truecolor
                    } elseif ($default['info']['colorType'] == 6 || $default['info']['colorType'] == 4) {
                        // set transparency type to "alpha" and proceed with it in $this->o_image later
                        $default['transparency']['type'] = 'alpha';

                        $img = imagecreatefromstring($data);

                        $imgalpha = imagecreate($default['info']['width'], $default['info']['height']);
                        // generate gray scale palette (0 -> 255)
                        for ($c = 0; $c < 256; ++$c) {
                            imagecolorallocate($imgalpha, $c, $c, $c);
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
                        $tmpfile_alpha = tempnam(ROSPDF_TEMPDIR, 'CpdfImage');

                        imagepng($imgalpha, $tmpfile_alpha);
                        imagedestroy($imgalpha);

                        $alphaData = file_get_contents($tmpfile_alpha);
                        // nested method call to receive info on alpha image
                        $alphaImg = $this->readPngChunks($alphaData);
                        // use 'pdate' to fill alpha image as "palette". But it s the alpha channel
                        $default['pdata'] = $alphaImg['idata'];

                        // generate true color image with no alpha channel
                        $tmpfile_tt = tempnam(ROSPDF_TEMPDIR, 'CpdfImage');

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
                    $default['pdata'] = substr($data, $p + 8, $chunkLen);
                    break;
                case 'IDAT':
                    $default['idata'] .= substr($data, $p + 8, $chunkLen);
                    break;
                case 'tRNS': // this HEADER info is optional. More info: rfc2083 (http://tools.ietf.org/html/rfc2083)
                    // this chunk can only occur once and it must occur after the PLTE chunk and before IDAT chunk
                    // KS End new code
                    break;
                default:
                    break;
            }
            $p += $chunkLen + 12;
        }

        return $default;
    }

    private function applyPalette()
    {
        if (empty($this->palette)) {
            return;
        }
        $this->paletteObj = $this->pages->NewContent();
        $this->paletteObj->SetPageMode(self::PMODE_NOPAGE, self::PMODE_NOPAGE);

        $this->paletteObj->AddRaw($this->palette);
        // do not compress the palette as it already is compressed
        // when palette is used as alpha channel fir indexed PNG, ignore the compression
        $this->paletteObj->Compression = 0;

        $this->paletteObj->AddEntry('Subtype', '/Image');
        $this->paletteObj->AddEntry('Width', $this->orgWidth);
        $this->paletteObj->AddEntry('Height', $this->orgHeight);
        $this->paletteObj->AddEntry('ColorSpace', '/DeviceGray');
        $this->paletteObj->AddEntry('BitsPerComponent', $this->bits);
        $this->paletteObj->AddEntry('Filter', '/FlateDecode');
        $this->paletteObj->AddEntry('DecodeParms', '<< /Predictor 15 /Colors 1 /BitsPerComponent '.$this->bits.' /Columns '.$this->orgWidth.' >>');
    }

    /**
     * PDF Output of the Image.
     */
    public function OutputAsObject()
    {
        $res = "\n$this->ObjectId 0 obj";
        $res .= "\n<< /Subtype /Image";

        $this->AddEntry('Width', $this->orgWidth);
        $this->AddEntry('Height', $this->orgHeight);

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
                if (isset($this->transparency)) {
                    switch ($this->transparency['type']) {
                        case 'indexed':
                            // disable transparancy on indexed PNGs for time being
                            //$tmp=' ['.$this->transparency['data'].' '.$this->transparency['data'].'] ';
                            //$this->AddEntry('Mask', $tmp);
                            if ($this->paletteObj) {
                                $this->AddEntry('ColorSpace', '[/Indexed /DeviceRGB '.(strlen($this->palette) / 3 - 1).' '.$this->paletteObj->ObjectId.' 0 R]');
                            } else {
                                $this->AddEntry('ColorSpace', '/'.$this->colorspace);
                            }
                            break;
                        case 'alpha':
                            $this->AddEntry('SMask', $this->paletteObj->ObjectId.' 0 R');
                            $this->AddEntry('ColorSpace', '/'.$this->colorspace);
                            break;
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
        if (function_exists('gzcompress') && $this->Compression != 0 && $this->ImageType != IMAGETYPE_PNG) {
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
            $res .= " /$k $v";
        }
        $res .= ' /Length '.strlen($tmp).' >>';
        $res .= "\nstream\n".$tmp."\nendstream";
        $res .= "\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
