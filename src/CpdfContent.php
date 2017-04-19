<?php
namespace ROSPDF;

class CpdfContent extends CpdfEntry
{
    public $Paging;
    /**
     * Individual Compression for every single Content output
     */
    public $Compression;
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

    const PMODE_LAZY = 16;

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
     * @var CpdfPage
     */
    public $page;

    protected $contents;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;
        $this->page = $pages->CURPAGE;
        $this->Compression = $pages->Compression;
        //$this->transferGlobalSettings();

        $this->contents = '';
        $this->ZIndex = 0;

        $this->BreakPage = self::PB_BLEEDBOX;
        $this->BreakColumn = false;

        $this->SetPageMode(self::PMODE_ADD, self::PMODE_ADD);
    }

    public function AddRaw($str)
    {
        $this->contents .= $str;
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
    
    public function Output()
    {
        return $this->contents;
    }

    public function OutputAsObject()
    {
        $l = 0;
        $tmp = $this->Output();
        if (!empty($tmp)) {
            // make sure compression is included and declare it properly
            if (function_exists('gzcompress') && $this->pages->Compression != 0 && $this->Compression != 0) {
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
        }

        $res = $this->outputEntries($this->entries);

        if ($l > 0) {
            $res.= "\nstream\n".$tmp."\nendstream";
        }
        $res = "\n".$this->ObjectId." 0 obj\n".$res."\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
?>