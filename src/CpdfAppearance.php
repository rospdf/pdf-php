<?php
namespace ROSPDF;

class CpdfAppearance extends CpdfContent
{
    /**
     * the current CpdfFont object as reference
     * Use SetFont('fontname'[, ...]) to change it
     * @var CpdfFont
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
     * Use Cpdf::SetBBox([changeBBox], $this->BBox) to change the BBox
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

    public $IsCallback;
    public $CallbackNo;
    protected $callbackObjects;

    public $JustifyCallback;

    public function __construct(&$pages, $BBox = array(), $color = null)
    {
        parent::__construct($pages, $BBox);

        $this->JustifyCallback = true;

        if (!empty($color)) {
            $this->fontColor = new CpdfColor($color, false);
        }

        $this->setColor();
        // make sure this is not a callback object
        $this->IsCallback = false;
        $this->CallbackNo = 0;
        $this->callbackObjects = array();

        $this->BBox = $pages->CURPAGE->Bleedbox;
        Cpdf::SetBBox($BBox, $this->BBox);

        if (isset($this->BBox)) {
            $this->x = $this->BBox[0];
            $this->y = $this->BBox[3];

            $this->initialBBox = $this->BBox;
        }

        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_BBOX)) {
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
        Cpdf::SetBBox($bbox, $this->BBox);

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
            Cpdf::DEBUG("Could not find either base font or style for '$fontName'", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
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
        if ($this->Paging == CpdfContent::PMODE_REPEAT || $this->Paging == CpdfContent::PMODE_LAZY) {
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
                        if ($this->BBox[1] <= $this->initialBBox[1] && Cpdf::IsDefined($this->BreakPage, CpdfContent::PB_CELL)) {
                            $this->BBox[1] += $this->fontHeight + $this->fontDescender;
                            break 2;
                        }
                    } elseif ($this->BreakColumn && ($this->BBox[2] + $width) <= $this->page->Bleedbox[2]) {
                        $obj = Cpdf::DoClone($this);
                        $this->pages->addObject($obj, true);
                        $this->contents = '';

                        $this->BBox[0] = $this->BBox[2];
                        $this->BBox[2] += $width;

                        $this->x = $this->BBox[0];
                        $this->y = $this->BBox[3];
                        $this->y -= $this->fontHeight + $this->fontDescender;
                    } elseif ($this->BreakPage > 0 && !$this->IsCallback) {
                        $obj = Cpdf::DoClone($this);
                        $this->pages->addObject($obj, true);

                        // reset the current object to initial values
                        $this->contents = '';
                        // reset the font color for the next page
                        $this->setColor();

                        //$this->BBox = $this->initialBBox;

                        $p = $this->pages->GetPageByNo($this->page->PageNum + 1);
                        if (!isset($p) || $this->pages->IsInsertMode()) {
                            $p = $this->pages->NewPage($this->page->Mediabox, $this->page->Cropbox, $this->page->Bleedbox);
                            // put background as reference to the new page
                            $p->Background = $this->page->Background;
                        }

                        $this->page = $p;

                        if (Cpdf::IsDefined($this->BreakPage, CpdfContent::PB_BLEEDBOX)) {
                            $this->initialBBox[1] = $this->page->Bleedbox[1];
                            $this->initialBBox[3] = $this->page->Bleedbox[3];
                        }

                        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
                        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_BBOX)) {
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
        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_TEXT)) {
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
        $this->contents.= ' /'.Cpdf::$ImageLabel.$img->ImageNum.' Do';
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
        $o = new CpdfGraphic('rectangle', $this->BBox[0] + $x, $this->BBox[1] + $y);

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
        $o = new CpdfLineStyle($width, $cap, $join, $dash);

        $this->contents.= "\n".$o->Output();
    }

    /**
     * Draw a line (usable as callback)
     *
     * Example: $app->AddLine(10, 800, 300, -300, new CpdfLineStyle(2, 'round','', array(5,3)));
     *
     * @param float $x initial x coordinate
     * @param float $y initial y coordinate
     * @param float $width width of the line
     * @param float $height height is used to set the end y coordinate
     * @param CpdfLineStyle $lineStyle defines the style of the line by using the CpdfLineStyle object
     *
     */
    public function AddLine($x, $y, $width = 0, $height = 0, $lineStyle = null)
    {
        $o = new CpdfGraphic('line', $this->BBox[0] + $x, $this->BBox[3] + $y);
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
     * @param CpdfLineStyle/bool $lineStyle can be either an object of CpdfLineStyle or boolean to set the default line style
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
        $o = new CpdfColor(array($r, $g, $b), $strokeColor);
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
            $this->SetPageMode(CpdfContent::PMODE_ADD, $this->pagingCallback);

            //$this->contents = '';
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
                                Cpdf::DEBUG("Character width 'space' not found while justifying", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
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
        $text = Cpdf::filterText($this->CURFONT, $text);

        return sprintf(" (%s) Tj", $text);
    }

    public function ColoredTj($text, $color = array())
    {
        $c = new CpdfColor($color, false);
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
        return sprintf(" /%s %.1F Tf", Cpdf::$FontLabel.$this->CURFONT->FontId, $this->fontSize);
    }

    protected function checkDirective($text)
    {
        $tagStart = mb_strpos($text, '<', 0, 'UTF-8');
        if ($tagStart === false) {
            return;
        }

        $tagEnd = mb_strpos($text, '>', $tagStart, 'UTF-8');
        $fullTag = mb_substr($text, $tagStart, $tagEnd - $tagStart + 1, 'UTF-8');

        $regex = "/<\/?([cC]:|)(".$this->pages->AllowedTags.")>/";
        
        if (!preg_match($regex, $fullTag, $regs)) {
            return;
        }

        $p = strpos($regs[2], ":");
        if ($p !== false) {
            $func = substr($regs[2], 0, $p);
            $parameter = substr($regs[2], $p + 1);
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
                $tm = $this->CURFONT->getTextLength($this->fontSize, $textPart, ($width - $lineWidth), $this->angle, 0);
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
        $TEXTBLOCK = sprintf(" /%s %.1F Tf %s", Cpdf::$FontLabel.$tmpFontId, $this->fontSize, $ws) . $TEXTBLOCK;

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
        Cpdf::DEBUG("-- ".count($this->callbackObjects)." CallbackObjects | STEPS : ".$this->CallbackNo, Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

        if ($this->IsCallback && count($this->callbackObjects) > 0) {
            for ($i = 0; $i < $this->CallbackNo; $i++) {
                $cbObject = array_shift($this->callbackObjects);
                if (is_object($cbObject)) {
                    $class_name = get_class($cbObject);
                    switch ($class_name) {
                        case 'ROSPDF\CpdfGraphic':
                            if ($this->JustifyCallback) {
                                $cbObject->X = $bbox[0];
                                $cbObject->Y = $bbox[1];
                            }

                            $this->contents.= "\n".$cbObject->Output();
                            break;
                        case 'ROSPDF\CpdfColor':
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
?>