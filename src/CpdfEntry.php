<?php

namespace ROSPDF;

class CpdfEntry
{
    protected $entries = [];

    public function AddEntry($k, $value)
    {
        $this->entries[$k] = $value;
    }

    public function AddResource($k, $v)
    {
        if (!isset($this->entries['Resources'])) {
            $this->entries['Resources'] = [];
        }

        $this->entries['Resources'][$k] = $v;
    }

    public function ClearEntries()
    {
        $this->entries = array();
    }

    public function HasEntries()
    {
        return (count($this->entries) > 0) ? true : false;
    }

    public function GetEntry($name)
    {
        return isset($this->entries[$name]) ? $this->entries[$name] : null;
    }

    protected function outputEntries($entries)
    {
        $res = '<<';
        if (is_array($entries)) {
            foreach ($entries as $k => $v) {
                if (is_array($v)) {
                    $res .= " /$k ".$this->outputEntries($v);
                } else {
                    $res .= " /$k $v";
                }
            }
        }
        $res .= ' >>';

        return $res;
    }
}
