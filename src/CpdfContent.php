<?php

namespace ROSPDF;

class CpdfContent extends CpdfEntry
{
    /**
     * page mode 'NOPAGE' used for general objects, like background appearances (or images).
     */
    const PMODE_NOPAGE = 0;
    /**
     * add the content to the current page.
     */
    const PMODE_ADD = 1;
    /**
     * add the content to all pages.
     */
    const PMODE_ALL = 2;
    /**
     * repeat the content at runtime (only works for AddText) - useful to display page number on every page.
     */
    const PMODE_REPEAT = 8;
    /**
     * output the content after page object id are defined.
     */
    const PMODE_LAZY = 16;

    const PB_BLEEDBOX = 1;
    const PB_BBOX = 2;
    const PB_CELL = 4;
    const PB_COLUMN = 8;

    public $Paging;
    /**
     * @property int individual compression strengh for this objects (0 = skip, 9 = highest)
     */
    public $Compression;

    public $ObjectId;

    /**
     * @property string the name of the current object
     */
    public $Name;
    /**
     * @property int possible options on how to break pages
     */
    public $BreakPage;

    /**
     * @property int numeric value to set this object to foreground or background
     */
    public $ZIndex;

    /**
     * @property CpdfPage the current page object
     */
    public $page;

    /**
     * @property Cpdf the Cpdf object
     */
    public $pages;

    /**
     * @property string the pdf content
     */
    protected $contents;

    /**
     * @property array page names to be ignored by this object
     */
    protected $ignorePages = [];

    public function __construct(&$pages)
    {
        $this->pages = &$pages;
        $this->page = $pages->CURPAGE;
        $this->Compression = $pages->Compression;

        $this->contents = '';
        $this->ZIndex = 0;

        $this->BreakPage = self::PB_BLEEDBOX;

        $this->SetPageMode(self::PMODE_ADD);
    }

    public function AddRaw($str)
    {
        $this->contents .= $str;
    }

    /**
     * Set page option for content and callbacks to define when the object should be displayed.
     *
     * @param string $content      paging mode for content objects (default: PMODE_ADD)
     * @param mixed  $ignoreInPage page names to be ignored by this object
     */
    public function SetPageMode($pm_content, $ignoreInPage = null)
    {
        $this->Paging = $pm_content;
        $this->ignorePages = (array) $ignoreInPage;
    }

    public function IsIgnored(&$page)
    {
        return in_array($page->Name, $this->ignorePages);
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

        $res = $this->OutputEntries();

        if ($l > 0) {
            $res .= "\nstream\n".$tmp."\nendstream";
        }
        $res = "\n".$this->ObjectId." 0 obj\n".$res."\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
