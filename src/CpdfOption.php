<?php

namespace ROSPDF;

class CpdfOption
{
    public $ObjectId;

    private $pages;

    private $preferences;
    private $pageLayout;

    private $oPage;
    private $oAction;

    private $names;

    private $metadataId;
    private $destinationId;
    private $intentsId;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;
        $this->preferences = array();
        $this->names = array();
    }

    public function OpenAction(&$page, $action = 'Fit')
    {
        $this->oPage = &$page;
        $this->oAction = $action;
    }

    public function AddName($name, $pageId, $y = null)
    {
        $this->names[$name] = array('pageId' => $pageId, 'y' => $y);
    }

    public function SetPageLayout($name = 'SinglePage')
    {
        $this->pageLayout = $name;
    }

    public function SetPreferences($key, $value)
    {
        $this->preferences[$key] = $value;
    }

    public function SetMetadata($id)
    {
        $this->metadataId = $id;
    }

    /**
     * TODO: implement outlines.
     */
    public function SetOutlines()
    {
    }

    private function outputDestinations()
    {
        $this->destinationId = ++$this->pages->objectNum;
        $res = "\n$this->destinationId 0 obj";
        $res .= "\n<< ";
        foreach ($this->names as $k => $v) {
            $res .= "\n  ";
            if (isset($v['y'])) {
                $res .= "/$k [".$v['pageId'].' 0 R /FitH '.$v['y'].']';
            } else {
                $res .= "/$k [".$v['pageId'].' 0 R /Fit]';
            }
        }
        $res .= " \n>>";
        $res .= "\nendobj";

        $this->pages->AddXRef($this->destinationId, strlen($res));

        return $res;
    }

    private function outputIntents()
    {
        $this->intentsId = ++$this->pages->objectNum;
        $res = "\n$this->intentsId 0 obj";
        $res .= "\n<< /Type /OutputIntent /S /GTS_PDFX /OutputConditionIdentifier (CGATS TR 001) /RegistryName (www.color.org) >>";

        $res .= "\nendobj";
        $this->pages->AddXRef($this->intentsId, strlen($res));

        return $res;
    }

    public function OutputAsObject()
    {
        $res = "\n$this->ObjectId 0 obj";
        $res .= "\n<< /Type /Catalog";
        if (count($this->preferences) > 0) {
            $res .= ' /ViewerPreferences <<';
            foreach ($this->preferences as $key => $value) {
                $res .= " /$key $value";
            }
            $res .= ' >>';
        }

        $res .= ' /Pages 2 0 R';

        if (isset($this->pageLayout)) {
            $res .= ' /PageLayout /'.$this->pageLayout;
        }

        if (isset($this->oAction)) {
            $res .= ' /OpenAction ['.$this->oPage->ObjectId.' 0 R /'.$this->oAction.']';
        }

        if (isset($this->metadataId)) {
            $res .= ' /Metadata '.$this->metadataId.' 0 R';
        }

        $intents = '';
        //$intents = $this->outputIntents();
        //$res.= ' /OutputIntents ['.$this->intentsId.' 0 R]';

        $dests = '';
        if (count($this->names) > 0) {
            $dests = $this->outputDestinations();
            $res .= ' /Dests '.$this->destinationId.' 0 R';
        }

        $res .= " >>\nendobj";

        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res.$intents.$dests;
    }
}
