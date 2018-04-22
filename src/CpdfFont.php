<?php

namespace ROSPDF;

/**
 * Font program class object
 * - TTF  in ANSI or UNICODE
 * - AFM fonts.
 *
 * TODO: support for opentype fonts
 * TODO: AFM/PFB font embedding needs to be implemented
 */
class CpdfFont
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
     * Main Cpdf class.
     *
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
     * the font file without extension.
     *
     * @var string
     */
    public $fontFile;
    /**
     * To verify of this is a coreFont program.
     *
     * @var bool
     */
    public $IsCoreFont;
    /**
     * To verify if the is a unicode font program.
     *
     * @var bool
     */
    public $IsUnicode;

    /**
     * Used to determine if font program is embeded.
     *
     * @var bool
     */
    public $EmbedFont;
    /**
     * Used to determine if its a font subset.
     *
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

        if ($p = strrpos($fontfile, '.')) {
            $ext = substr($fontfile, $p);
            // file name gets a proper extension below
            $fontFile = substr($fontfile, 0, $p);
        }
        // check if fontfile is one of the coreFonts
        $found = preg_grep('/^'.$fontfile.'$/i', Cpdf::$CoreFonts);
        if (count($found) > 0) {
            // use font name fron CoreFont array as they are case sensitive
            $this->fontFile = end($found);
            $this->IsCoreFont = true;
            $ext = 'afm';
        } elseif (empty($ext)) { // otherwise use ttf by default
            $this->fontFile = $fontfile;
            $this->IsCoreFont = false;
            $ext = 'ttf';
        }

        if (file_exists($path.'/'.$fontfile.'.'.$ext)) {
            $this->fontpath = $path.'/'.$fontfile.'.'.$ext;
            $this->loadFont();
        } else {
            Cpdf::DEBUG("Font program '$path/$fontfile.$ext' not found", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            die;
        }
    }

    /**
     * generate a random string as font subset prefix.
     */
    private function randomSubset()
    {
        $length = 6;
        // can also have more then A-F, but should be enough
        $characters = 'ABCDEF';
        $randomString = '';
        for ($i = 0; $i < $length; ++$i) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString.'+';
    }

    /**
     * add chars to an array which is used for font subsetting.
     */
    public function AddChar($char)
    {
        $this->subsets[$char] = true;
    }

    /**
     * initial method to read and load (via OutputProgram) the font program.
     */
    private function loadFont()
    {
        $cachedFile = ROSPDF_TEMPNAM.'_cache.'.$this->fontFile.'.php';

        // use the temp folder to read/write cached font data
        if (file_exists(ROSPDF_TEMPDIR.'/'.$cachedFile) && filemtime(ROSPDF_TEMPDIR.'/'.$cachedFile) > strtotime('-'.Cpdf::$CacheTimeout)) {
            if (empty($this->props)) {
                $this->props = require ROSPDF_TEMPDIR.'/'.$cachedFile;
            }

            if (isset($this->props['_version_']) && $this->props['_version_'] == 4) {
                // USE THE CACHED FILE and exit here
                $this->IsUnicode = $this->props['isUnicode'];

                return;
            }
        }
        // read ttf font properties via TTF class
        if ($this->IsCoreFont == false && class_exists('TTF')) {
            // The selected font is a TTF font (any other is not yet supported)
            $this->readTTF($this->fontpath);
        } elseif ($this->IsCoreFont == true) {
            // The selected font is a core font. So use the afm file to read the properties
            $this->readAFM($this->fontpath);
        } else {
            // ERROR: No alternative found to read ttf fonts
        }

        $this->props['_version_'] = 4;
        $fp = fopen(ROSPDF_TEMPDIR.'/'.$cachedFile, 'w'); // use the temp folder to write cached font data
        fwrite($fp, '<?php /* R&OS php pdf class font cache file */ return '.var_export($this->props, true).'; ?>');
        fclose($fp);
    }

    /**
     * Include only such glyphs into the PDF document which are really in use.
     */
    private function subsetProgram()
    {
        if (class_exists('TTFsubset')) {
            $t = new \TTFsubset();
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
     * Fully embed the ttf font into PDF.
     */
    private function fullProgram()
    {
        $data = @file_get_contents($this->fontpath);

        // load the widths into $this->cidWidths
        $this->loadWidths();

        return $data;
    }

    /**
     * load the charachter widhts into $this->cidWidths[<int>] = width.
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
                    $this->cidWidths[$TTFchar->charCode] = (isset($this->props['C'][$TTFchar->charCode])) ? $this->props['C'][$TTFchar->charCode] : 700;
                }
            }
        }
    }

    /**
     * read the AFM (also core fonts are stored as .AFM) to calculate character width, height, descender and the FontBBox.
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
            $row = trim($row);
            $pos = strpos($row, ' ');
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
                        $this->props[$key] = trim(substr($row, $pos));
                        break;
                    case 'FontBBox':
                        $this->props[$key] = explode(' ', trim(substr($row, $pos)));
                        break;
                    case 'C':
                        // C 39 ; WX 222 ; N quoteright ; B 53 463 157 718 ;
                        // use preg_match instead to improve performace
                        // IMPORTANT: if "L i fi ; L l fl ;" is required preg_match must be amended
                        $r = preg_match('/C (-?\d+) ; WX (-?\d+) ; N (\w+) ; B (-?\d+) (-?\d+) (-?\d+) (-?\d+) ;/', $row, $m);
                        if ($r == 1) {
                            //$dtmp = array('C'=> $m[1],'WX'=> $m[2], 'N' => $m[3], 'B' => array($m[4], $m[5], $m[6], $m[7]));
                            $c = (int) $m[1];
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
     * The TTF.php class from Thanos Efraimidis (4real.gr) is used to read the TTF binary natively.
     *
     * @param string $fontpath - path of the *.ttf font file
     */
    private function readTTF($fontpath)
    {
        // set unicode to all TTF fonts by default
        $this->IsUnicode = true;

        $ttf = new \TTF(file_get_contents($fontpath));

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
            'IsFixedPitch' => ($post['isFixedPitch'] == 0) ? false : true,
            'Ascender' => $hhea['ascender'],
            'Descender' => $hhea['descender'],
            'LineGap' => $hhea['lineGap'],
            'FontName' => $uname['nameRecords'][2]['value'],
            'FamilyName' => $uname['nameRecords'][1]['value'],
        );

        // calculate the bounding box properly by using 'units per em' property
        $this->props['FontBBox'] = array(
                                    intval($head['xMin'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['yMin'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['xMax'] / ($head['unitsPerEm'] / 1000)),
                                    intval($head['yMax'] / ($head['unitsPerEm'] / 1000)),
                                );
        $this->props['UnitsPerEm'] = $head['unitsPerEm'];

        $encodingTable = array();

        $hmetrics = $ttf->unmarshalHmtx($hhea['numberOfHMetrics'], $maxp['numGlyphs']);

        // get format 6 or format 4 as primary cmap table map glyph with character
        foreach ($cmap['tables'] as $v) {
            if (isset($v['format']) && $v['format'] == '4') {
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

            for ($seg = 0; $seg < $segCount; ++$seg) {
                $endCount = $endCountArray[$seg];
                $startCount = $startCountArray[$seg];
                $idDelta = $idDeltaArray[$seg];
                $idRangeOffset = $idRangeOffsetArray[$seg];
                for ($charCode = $startCount; $charCode <= $endCount; ++$charCode) {
                    if ($idRangeOffset != 0) {
                        $j = $charCode - $startCount + $seg + $idRangeOffset / 2 - $segCount;
                        $gid0 = $glyphIdArray[$j];
                    } else {
                        $gid0 = $idDelta + $charCode;
                    }
                    $gid0 %= 65536;
                    if (in_array($gid0, $glyphsIndices)) {
                        $charToGlyph[sprintf('%d', $charCode)] = $gid0;
                    }
                }
            }

            $cidtogid = str_pad('', 256 * 256 * 2, "\x00");

            $this->props['C'] = array();
            foreach ($charToGlyph as $char => $glyphIndex) {
                $m = \TTF::getHMetrics($hmetrics, $hhea['numberOfHMetrics'], $glyphIndex);

                // calculate the correct char width by dividing it with 'units per em'
                $this->props['C'][$char] = intval($m[0] / ($head['unitsPerEm'] / 1000));

                // TODO: check if this mapping also works for non-unicode TTF fonts
                if ($char >= 0 && $char < 0xFFFF && $glyphIndex) {
                    $cidtogid[$char * 2] = chr($glyphIndex >> 8);
                    $cidtogid[$char * 2 + 1] = chr($glyphIndex & 0xFF);
                }
            }
        } else {
            Cpdf::DEBUG('Font file does not contain format 4 cmap', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }

        $this->props['CIDtoGID'] = base64_encode($cidtogid);
    }

    public function GetFontName()
    {
        if (!isset($this->props['FontName'])) {
            Cpdf::DEBUG('No font name found for {$this->fontFile}', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

            return;
        }

        return $this->props['FontName'];
    }
    public function GetFontFamily()
    {
        if (!isset($this->props['FamilyName'])) {
            Cpdf::DEBUG('No font family found for {$this->fontFile}', Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);

            return;
        }

        return $this->props['FamilyName'];
    }

    /**
     * calculate the font height by using the FontBBox.
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

        return $fontSize * $h / $unitsPerEm;
    }

    /**
     * read the font descender from font properties.
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

        return $fontSize * $h / $unitsPerEm;
    }

    /**
     * get the characters width.
     *
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
     * get the text length of a string and cut it if necessary it does not fit to $maxWidth.
     *
     * TODO: check if the length is calculated correctly when angle and word alignment is used
     *
     * Example of the returned array:
     * array(532, 0, 124, 1) - the text length is greater $maxWidth on position 124; its a 'normal' line break where a space was found
     * array(554, 0, 128, 0) - the text length is greate $maxWidth on position 128; force line break (no space found)
     *
     * @param float  $size     font size
     * @param string $text     text string to be calculated
     * @param float  $maxWidth max width of the text string (if zero - no calculation required)
     * @param float  $angle    angle of the text string
     * @param float  $wa       word align
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
        $spaces = array_merge($spaces, array(0x20, 0x1680), range(0x2000, 0x2009));

        $a = deg2rad((float) $angle);
        // get length of its unicode string
        $len = mb_strlen($text, 'UTF-8');

        $tw = $maxWidth / $size * 1000;
        $break = 0;
        $offset = 0;
        $w = 0;

        for ($i = 0; $i < $len; ++$i) {
            $c = mb_substr($text, $i, 1, 'UTF-8');

            $cOrd = Cpdf::uniord($c);
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
                $w += $this->props['C'][$cOrd2];
            }

            if ($cOrd2 == 45) {
                $break = $i + 1;
                $offset = 0;
                // TODO: set the default width if not char width is found
                $breakWidth = $w * $size / 1000;
            } elseif (in_array($cOrd2, $spaces)) {
                $break = $i;
                $correction = (isset($this->props['C'][$cOrd2]) ? $this->props['C'][$cOrd2] : 0);
                $offset = 1;
                // word spacing
                $w += ($wa > 0) ? $wa : 0;
                $breakWidth = ($w - $correction) * $size / 1000;
            }

            if ($maxWidth > 0 && (cos($a) * $w) > $tw && $break > 0) {
                return array(cos($a) * $breakWidth, -sin($a) * $breakWidth, $break, $offset);
            }
        }

        $tmpw = $w * $size / 1000;

        return array(cos($a) * $tmpw, -sin($a) * $tmpw, -1, 0);
    }

    /**
     * return the the font descriptor output (indirect object reference).
     */
    private function outputDescriptor()
    {
        $this->descriptorId = ++$this->pages->objectNum;

        $res = "\n$this->descriptorId 0 obj\n";
        $res .= '<< /Type /FontDescriptor /Flags 32 /StemV 70';

        if ($this->SubsetFont && $this->EmbedFont && $this->IsUnicode) {
            $res .= '/FontName /'.$this->prefix.$this->fontFile;
        } else {
            $res .= '/FontName /'.$this->fontFile;
        }

        $res .= ' /Ascent '.$this->props['Ascender'].' /Descent '.$this->props['Descender'];

        $bbox = &$this->props['FontBBox'];
        $res .= ' /FontBBox ['.$bbox[0].' '.$bbox[1].' '.$bbox[2].' '.$bbox[3].']';

        $res .= ' /ItalicAngle '.$this->props['ItalicAngle'];
        $res .= ' /MaxWidth '.$bbox[2];
        $res .= ' /MissingWidth 600';

        if ($this->EmbedFont) {
            $res .= ' /FontFile2 '.$this->binaryId.' 0 R';
        }

        $res .= " >>\nendobj";

        $this->pages->AddXRef($this->descriptorId, strlen($res));

        return $res;
    }

    /**
     * return the font descendant output (indirect object reference).
     */
    private function outputDescendant()
    {
        $this->descendantId = ++$this->pages->objectNum;

        $res = "\n$this->descendantId 0 obj\n";
        $res .= '<< /Type /Font /Subtype /CIDFontType2';
        if ($this->SubsetFont) {
            $res .= ' /BaseFont /'.$this->prefix.$this->fontFile;
        } else {
            $res .= ' /BaseFont /'.$this->fontFile;
        }

        $res .= ' /CIDSystemInfo << /Registry (Adobe) /Ordering (Identity) /Supplement 0 >>';

        $res .= " /FontDescriptor $this->descriptorId 0 R";
        $res .= " /CIDToGIDMap $this->cidmapId 0 R";

        $res .= ' /W [';
        $opened = false;

        foreach ($this->cidWidths as $k => $v) {
            $nextv = next($this->cidWidths);
            $nextk = key($this->cidWidths);

            if (($k + 1) == $nextk) {
                if (!$opened) {
                    $res .= " $k [$v";
                    $opened = true;
                } elseif ($opened) {
                    $res .= ' '.$v;
                }
            } else {
                if ($opened) {
                    $res .= " $v]";
                } else {
                    $res .= " $k [$v]";
                }
                $opened = false;
            }
        }

        if (isset($nextk) && isset($nextv)) {
            if ($opened) {
                $res .= ']';
            }
            $res .= " $nextk [$nextv]";
        }

        $res .= ' ]';
        $res .= ' >>';
        $res .= "\nendobj";

        $this->pages->AddXRef($this->descendantId, strlen($res));

        return $res;
    }

    /**
     * return the ToUnicode output (indirect object reference).
     */
    private function outputUnicode()
    {
        $this->unicodeId = ++$this->pages->objectNum;

        $res = "\n$this->unicodeId 0 obj\n";

        $stream = "/CIDInit /ProcSet findresource begin\n12 dict begin\nbegincmap\n/CIDSystemInfo <</Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n/CMapName /Adobe-Identity-UCS def\n/CMapType 2 def\n1 begincodespacerange\n<0000> <FFFF>\nendcodespacerange\n1 beginbfrange\n<0000> <FFFF> <0000>\nendbfrange\nendcmap\nCMapName currentdict /CMap defineresource pop\nend\nend\n";

        $res .= '<< /Length '.strlen($stream)." >>\n";
        $res .= "stream\n".$stream."\nendstream";
        $res .= "\nendobj";

        $this->pages->AddXRef($this->unicodeId, strlen($res));

        return $res;
    }

    /**
     * return the CID mapping output (as an indirect object reference).
     */
    private function outputCIDMap()
    {
        $this->cidmapId = ++$this->pages->objectNum;

        $res = "\n$this->cidmapId 0 obj";
        $res .= "\n<<";

        $stream = base64_decode($this->props['CIDtoGID']);
        // compress the CIDMap if compression is enabled
        if ($this->pages->Compression != 0) {
            $stream = gzcompress($stream, $this->pages->Compression);
            $res .= ' /Filter /FlateDecode';
        }

        $res .= ' /Length '.strlen($stream).' >>';

        $res .= "\nstream\n".$stream."\nendstream";
        $res .= "\nendobj";

        $this->pages->AddXRef($this->cidmapId, strlen($res));

        return $res;
    }

    /**
     * return the binary output, either as font subset or the complete font file.
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
        if ($this->pages->Compression != 0) {
            $data = gzcompress($data, $this->pages->Compression);
            $res .= ' /Filter /FlateDecode';
        }

        // make sure the compressed length is declared
        $l1 = strlen($data);

        $res .= "/Length1 $l /Length $l1 >>\nstream\n".$data."\nendstream\nendobj";

        $this->pages->AddXRef($this->binaryId, strlen($res));

        return $res;
    }

    /**
     * Output the font program.
     */
    public function OutputProgram()
    {
        $res = "\n".$this->ObjectId.' 0 obj';
        $res .= "\n<< /Type /Font /Subtype";

        $data = '';
        $unicode = '';
        $cidMap = '';
        $descr = '';
        $descendant = '';

        if ($this->IsCoreFont) {
            // core fonts (plus additionals?!)
            $res .= ' /Type1 /BaseFont /'.$this->fontFile;
            //$res.= " /Encoding /".$this->props['EncodingScheme'];
            $res .= ' /Encoding /WinAnsiEncoding';
        } else {
            $data = $this->outputBinary();

            $unicode = $this->outputUnicode();
            $cidMap = $this->outputCIDMap();

            $descr = $this->outputDescriptor();
            $descendant = $this->outputDescendant();

            // for Unicode fonts some additional info is required
            $res .= ' /Type0 /BaseFont';
            if ($this->SubsetFont) {
                $fontname = $this->prefix.$this->fontFile;
            } else {
                $fontname = $this->fontFile;
            }

            $res .= " /$fontname";
            $res .= ' /Name /'.Cpdf::$FontLabel.$this->FontId;
            $res .= ' /Encoding /Identity-H';
            $res .= " /DescendantFonts [$this->descendantId 0 R]";

            $res .= " /ToUnicode $this->unicodeId 0 R";
        }

        $res .= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res.$data.$unicode.$cidMap.$descr.$descendant;
    }
}
