<?php

namespace ROSPDF;

/**
 * Used to create the pdf object entries.
 */
class CpdfEntry
{
    /**
     * @property array stores the entries of a content objects being added.
     */
    protected $entries = [];

    /**
     * Add an entry to the content object.
     *
     * @param string $k the entry key shown as "/<Key>"
     * @param mixed $value the value of the key
     */
    public function AddEntry($k, $value)
    {
        $this->entries[$k] = $value;
    }

    /**
     * Add the Resource dictionary.
     *
     * @param string resource key (E.g. Font or XObject)
     * @param mixed resource value
     */
    public function AddResource($k, $v)
    {
        if (!isset($this->entries['Resources'])) {
            $this->entries['Resources'] = [];
        }

        $this->entries['Resources'][$k] = $v;
    }

    /**
     * Check if entries are available.
     */
    public function HasEntries()
    {
        return (count($this->entries) > 0) ? true : false;
    }

    /**
     * Get the value of an entry.
     */
    public function GetEntry($name)
    {
        return isset($this->entries[$name]) ? $this->entries[$name] : null;
    }

    /**
     * process the entries and return a valid pdf output.
     */
    private function processEntries($entries)
    {
        $res = '<<';
        if (is_array($entries)) {
            foreach ($entries as $k => $v) {
                if (is_array($v)) {
                    $res .= " /$k ".$this->processEntries($v);
                } else {
                    $res .= " /$k $v";
                }
            }
        }
        $res .= ' >>';

        return $res;
    }

    /**
     * Used to output the entries stored in the current content object.
     */
    public function OutputEntries()
    {
        return $this->processEntries($this->entries);
    }
}
