<?php
namespace ROSPDF;
/**
 * Page class object
 */
class CpdfPage
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
            Cpdf::SetBBox(array('addlx'=> 30, 'addly' => 30, 'addux' => -30, 'adduy' => -30), $bleedbox);
        }

        $this->Mediabox = $mediabox;
        $this->Cropbox = $cropbox;
        $this->Bleedbox = $bleedbox;

        $this->pages = &$pages;
        $this->entries = array();
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

        $app = $this->pages->NewAppearance($mb);
        $app->page = null;
        $app->SetPageMode(CpdfContent::PMODE_NOPAGE);
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
        // the Object Id of the page will be set in CpdfPages->OutputAll()
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
                if ($mode[0] != CpdfContent::PMODE_NOPAGE && $mode[0] != CpdfContent::PMODE_REPEAT) {
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
?>