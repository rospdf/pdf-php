<?php
namespace ROSPDF;
/**
 * Class object to provide Annotations, like Links, text and freetext
 *
 * TODO: Audio and video annotations
 */
class CpdfAnnotation extends CpdfContent
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

    public function __construct(&$pages, $annoType, $rectangle, $border = null, $color = null, $flags = array())
    {
        parent::__construct($pages);

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
     * Name requires to hace CpdfContent->Name set to a unqiue string value
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
            } elseif (($objectId=intval($this->target)) > 0) {
                $res.=' /Dest ['.$objectId.' 0 R /Fit]';
            } elseif (!empty($this->target)) {
                $res.=' /Dest /'.$this->target;
            }

            if (!empty($this->title)) {
                $res.=' /T ('.$this->title.')';
            }

            if (!empty($this->contents)) {
                $res.=' /Contents ('.$this->contents.')';
            }

            // set the color via object class CpdfColor
            if (is_object($this->color)) {
                $c = $this->color;
                $res.=' /C '.$c->Output();
            }

            // PDF-1.1 hide the old border
            $res.=' /Border [0 0 0]';
            // set the border style via object class CpdfBorderStyle
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
            Cpdf::DEBUG("Invalid ractangle - array must contain 4 elements", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
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
?>