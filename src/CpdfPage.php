<?php
namespace ROSPDF;
/**
 * Page class object
 */
class CpdfPage extends CpdfEntry
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
    public $Objects;

    public $Name;

    public function __construct(&$pages, $mediabox, $cropbox = null, $bleedbox = null)
    {
        $this->AddEntry('Type', '/Page');

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
        //$app->page = &$this;
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

    public function OutputAsObject()
    {
        // the Object Id of the page will be set in CpdfPages->OutputAll()
        $res = "\n".$this->ObjectId . " 0 obj\n";

        $this->AddEntry('Parent', $this->pages->ObjectId . ' 0 R');

        if (is_array($this->Mediabox)) {
            $this->AddEntry('MediaBox', sprintf("[%.3F %.3F %.3F %.3F]", $this->Mediabox[0], $this->Mediabox[1], $this->Mediabox[2], $this->Mediabox[3]));
        }
        if (is_array($this->Cropbox)) {
            $this->AddEntry('CropBox', sprintf("[%.3F %.3F %.3F %.3F]", $this->Cropbox[0], $this->Cropbox[1], $this->Cropbox[2], $this->Cropbox[3]));
        }
        if (is_array($this->Bleedbox)) {
            $this->AddEntry('BleedBox', sprintf("[%.3F %.3F %.3F %.3F]", $this->Bleedbox[0], $this->Bleedbox[1], $this->Bleedbox[2], $this->Bleedbox[3]));
        }

        $allObjects = $this->Objects + $this->pages->GetGlobalObjects();       

        if(count($allObjects) > 0) {
            $contentRefs = [];

            if($this->Background) {
                $contentRefs[] = $this->Background->ObjectId . ' 0 R';
            }

            $annotRefs = [];
            Cpdf::DEBUG("### Page {$this->PageNum} Id {$this->ObjectId}", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

            foreach($allObjects as &$o) {
                if($o->IsIgnored($this)) continue;
                Cpdf::DEBUG("- ".get_class($o)." ObjectId {$o->ObjectId} | Paging: {$o->Paging} | Length: {$o->Length()}", Cpdf::DEBUG_OUTPUT, Cpdf::$DEBUGLEVEL);

                if($o instanceof CpdfAppearance) {
                    $contentRefs[] = $o->ObjectId . ' 0 R';
                } elseif($o instanceof CpdfAnnotation) {
                    $annotRefs[] = $o->ObjectId . ' 0 R';
                }
            }
        }

        if(!empty($contentRefs))
            $this->AddEntry('Contents', '[' . implode(' ', $contentRefs) . ']');
        if(!empty($annotRefs))
            $this->AddEntry('Annots', '[' . implode(' ', $annotRefs) . ']');

        $res.= $this->outputEntries($this->entries);

        $res.=" >>\nendobj";
        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
?>