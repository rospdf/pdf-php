<?php
/**
 * Extension class allowing the use of callbacks and directives.
 * The following methods are being used for callbacks
 * - DoCall
 * - DoTrigger
 * - Callback
 *
 * By default this class provides paging, internal and external links, backgrounds and colored text.
 * Text directives, like strong and italic are dependent on the Font family set defined in Cpdf::$DefaultFontFamily
 */

namespace ROSPDF;

require_once 'Cpdf.php';

use ROSPDF\Cpdf;
use ROSPDF\CpdfLineStyle;
use ROSPDF\CpdfColor;

class CpdfExtension extends Cpdf
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
     * @param CpdfWriting $sender The sender class object
     * @param String $funcName function name to be called
     * @param Array $BBox First part of the Bounding Box containing lower X and lower Y coordinates
     * @param mixed $param optional parameters
     */
    public function DoCall(&$sender, $funcName, $BBox, $param)
    {
        if (!isset($this->callbackFunc[$funcName])) {
            Cpdf::DEBUG("Callback function '$funcName' not registered", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
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
     * @param CpdfWriting $sender The sender class object
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
            Cpdf::DEBUG("Callback function '$funcName' not registered in stack", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
            return;
        }

        $args = func_get_args();
        array_shift($args); // remove sender
        array_shift($args); // remove funcName
        array_shift($args); // remove BBox

        Cpdf::SetBBox($BBox, $func['bbox']);

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
     * Correct the BBox for all calls located in callbackStack by using Cpdf->Callback function call
     * @param Int $offsetX correction of the X coordinate
     * @param Int $offsetY correction of the Y coordinate
     * @param Bool $resize request a fully resize the Cpdf* object
     */
    public function Callback($offsetX = 0, $offsetY = 0)
    {
        if (count($this->callbackStack) <= 0) {
            return;
        }

        foreach ($this->callbackStack as $key => &$func) {
            Cpdf::DEBUG("---CALLBACK '".$func['funcName']."' offsetX = $offsetX, offsetY = $offsetY STARTED---", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

            if (!isset($func)) {
                Cpdf::DEBUG("No Callback found", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                continue;
            }
            $func['bbox'][0] += $offsetX;
            $func['bbox'][2] += $offsetX;
            $func['bbox'][1] += $offsetY;
            $func['bbox'][3] += $offsetY;

            foreach ($func as $k => &$cb) {
                if ($k == 'bbox' || $k == 'param' || $k == 'funcName' || $k == "done")
                    continue;
                
                if (is_object($cb)) {
                    $cb->Callback($func['bbox']);
                }
            }
            unset($this->callbackStack[$key]);
        }

        Cpdf::DEBUG("---CALLBACK '".$func['funcName']."' ENDED---", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);
    }

    /**
     * Used to start font style italic
     *
     * @param {CpdfWriting} $sender
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
     * @param CpdfWriting $sender
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
     * Give $sender object a background at BBox position by using CpdfAppearance->AddRectangle
     * @param CpdfWriting|CpdfTable $sender sender class object
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
     * Colorize the text output by using CpdfWriting->ColoredTj([...]);
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
     * @param {CpdfWriting} $sender class object from callback function
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
        $lineStyle = new CpdfLineStyle(0.5, 'butt', '');
        $app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);
        
        $annot = $sender->pages->NewAnnotation('link', $bbox, null, new CpdfColor([0,0,1]) );
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
     * @param {CpdfWriting} $sender class object from callback function
     * @param {Array} $cb
     * @param {Array} $bbox Bounding box
     * @param {Array} $params additional callback parameters
     * @return bool true to remove the previous text content, false to ignore
     */
    public function ilink(&$sender, &$cb, $bbox, $params)
    {
        $app = &$cb['appearance'];

        //$lineStyle = new CpdfLineStyle(0.5, 'butt', '', array(3,1));
        //$app->AddLine(0, 0, $bbox[2] - $bbox[0], 0, $lineStyle);

        $annot = $sender->pages->NewAnnotation('link', $bbox, null, new CpdfColor(array(0,0,1)));
        $annot->SetDestination($params[0]);

        //$c = count($cb);
        //$cb["link$c"] = $annot;
        return false;
    }
}
?>