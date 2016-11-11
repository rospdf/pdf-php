<?php

/**
 * ROS PDF Import class (RPDI) - Experimental
 *
 * This class provides an import of existing PDF documents into a ROS PDF class environment (>= version 0.13.0)
 *
 * Currently  only graphic object (described in Chapter 4.1 of pdf reference  1.4) are supported
 * Non-Linearized (network optimized) PDF files are supported as well
 * More details about Linearized PDF, see Appendix F in PDF reference 1.3
 *
 * Example:
 * $pdf = new RPDI('template.pdf', Cpdf::$Layout['A4']);
 * $pdf->Compression = 0;
 * $pdf->ImportPage(1);
 * $t = $pdf->NewText();
 * $t->AddText('Hello World');
 * $pdf->Stream('template_test.pdf');
 *
 * @category Documents
 * @package  RPDI
 * @version  0.0.1
 * @author   Ole Koeckemann <ole1986@users.sourceforge.net>
 *
 * @copyright 2014 The author(s)
 * @license  GNU General Public License v3
 * @link     http://pdf-php.sf.net
 */
class RPDI extends CpdfExtension
{
    const EOFOffset = 50;
    
    const ENTRYTYPE_INDIRECT = 1;
    const ENTRYTYPE_BOOL = 2;
    const ENTRYTYPE_INT = 3;
    const ENTRYTYPE_FLOAT = 4;
    const ENTRYTYPE_STRING = 5;
    const ENTRYTYPE_LABEL = 6;
    const ENTRYTYPE_ARRAY = 7;
    const ENTRYTYPE_DICTIONARY = 8;
    
    /**
     * file stream of the imported pdf document
     */
    private $filestream;
    /**
     * contains the trailer entries only
     */
    private $trailer;
    /**
     * entries of the catalog object
     */
    private $catalog;
    /**
     * entries of the pages object
     */
    private $pages;
    
    /**
     * contains the page entries - key ordered by page object id
     */
    private $page;
    /**
     * used for page numbers
     */
    private $pageToObject;
    
    /**
     * array of all object being loaded
     */
    private $objects;
    
    /**
     * object XRef
     */
    private $objectRef;
    
    /**
     * associated array
     */
    private $objectAssoc;
    /**
     * rearranged object ids
     */
    private $rearranged;
     
    private $indirectObjects;
     
    public function __construct($file = 'template.pdf', $mediabox, $cropbox = null, $bleedbox = null)
    {
        parent::__construct($mediabox, $cropbox, $bleedbox);
        
        $this->page = array();
        $this->pageToObject = array();
        $this->objects = array();
        $this->objectAssoc = array();
        $this->rearranged = array();
        
        // open the pdf file
        $this->filestream = fopen($file, 'r');
        // set the file pointer to the end minus EOFOffset (default: 50)
        fseek($this->filestream, -self::EOFOffset, SEEK_END);
        // read exact EOFOffset bytes to reach EOF
        $data = fread($this->filestream, self::EOFOffset);
        // locate the integer verify the start of XRef table
        $res = preg_match("/\n([0-9]+)/m", $data, $regs);
        
        if ($res) {
            // start reading the XRef from exact position
            $res = $this->readXRefAndTrailer($regs[1]);
            if ($res && isset($this->trailer['Root'])) {
                $t = $this->parseType($this->trailer['Root']);
                $obj = $this->GetObject($t['value'], false);
                $this->catalog = $obj['entries'];
                
                // initialize pages
                $this->initPages();
            } else {
                Cpdf::DEBUG("RPDI: An error occured - No template is used", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            }
        }
    }
    
    private function initPages()
    {
        if (isset($this->catalog) && isset($this->catalog['Pages'])) {
            $t = $this->parseType($this->catalog['Pages']);
            $obj = $this->GetObject($t['value']);
            $this->pages = $obj['entries'];
            
            $t = $this->parseType($this->pages['Kids']);
            
            if ($t['type'] == self::ENTRYTYPE_ARRAY) {
                $i = 1;
                foreach ($t['value'] as $v) {
                    if ($v['type'] == self::ENTRYTYPE_INDIRECT) {
                         $pageObject = $this->GetObject($v['value']);
                         $this->page[$v['value']] = $pageObject['entries'];
                         $this->pageToObject[$i] = $v['value'];
                         $i++;
                    }
                }
            }
        }
    }
    
    
    public function ImportPage($pageNumber)
    {
        // contains the full page including all indirect object references in ONE object
        $fullPage = $this->GetPage($pageNumber);
        //print_r($fullPage);
        
        if (!isset($fullPage)) {
            Cpdf::DEBUG("RPDI: Page not found", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            return;
        }
        
        if (!isset($fullPage['Contents'])) {
            Cpdf::DEBUG("RPDI: No Content found for page $pageNumber", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            return;
        }
        
        $this->parseContent($fullPage);
        if (isset($this->pageToObject[$pageNumber])) {
            //$page = &$this->page[$this->pageToObject[$pageNumber]];
            $page = $fullPage;
            
            foreach ($this->indirectObjects as $entry) {
                $obj = $this->GetObject($entry['value'], true);
                
                if (isset($obj)) {
                    $cObject = $this->NewContent();
                    $cObject->SetPageMode(CpdfContent::PMODE_NOPAGE);
                    $cObject->Name = "PDFIMPORT_".$entry['value'];
                    
                    if (isset($obj['stream'])) {
                        $cObject->AddRaw($obj['stream']);
                    }
                    
                    foreach ($obj['entries'] as $key => $value) {
                        $cObject->AddEntry($key, $value);
                    }
                    
                    // fill the assocation array
                    $this->objectAssoc[$entry['value']] = $cObject->Name;
                }
            }
        }
        //print_r($this->objectAssoc);
    }
    
    private function parseContent(&$fullPage)
    {
        if (!is_array($fullPage['Contents']) || count($fullPage['Contents']) <= 0) {
            Cpdf::DEBUG("RPDI: Empty content", Cpdf::DEBUG_MSG_WARN, Cpdf::$DEBUGLEVEL);
        }
        
        foreach ($fullPage['Contents'] as $key => $value) {
            $cObject = $this->NewContent();
            $cObject->SetPageMode(CpdfContent::PMODE_ALL);
            $cObject->Name = "PDFIMPORT_$key";
            
            $cObject->AddRaw($value['stream']);
            // fill the assocation array
            $this->objectAssoc[$key] = $cObject->Name;
        }
    }
    
    public function GetPage($pageNumber)
    {
        if (isset($this->pageToObject[$pageNumber])) {
            return $this->GetPageByObjectId($this->pageToObject[$pageNumber]);
        } else {
            // ERROR: page not found
        }
    }
    
    public function GetPageByObjectId($objectId)
    {
        return $this->getFullPage($objectId);
    }
    
    public function GetObject($objectId, $withStream = false)
    {
        if (isset($this->objects[$objectId])) {
            return $this->objects[$objectId];
        }
        
        
        $res = array('stream'=> null, 'entries'=> null);
        if (!isset($this->objectRef[$objectId])) {
            return null;
        }
        
        $offset = $this->objectRef[$objectId];
        fseek($this->filestream, $offset, SEEK_SET);
        
        $buffer = stream_get_line($this->filestream, 4096, "endobj");
        $entries = array();
        
        $a = preg_split("/(stream|endstream)|[0-9]+ 0 obj(\r\n|\n|\r)/", $buffer);
        
        if (count($a) > 2 && $withStream) {
            $res['entries'] = $this->parseEntry($a[1]);
            //print_r($res);
            if (isset($res['entries']['Filter'])) {
                $res['stream'] = gzuncompress(trim($a[2]));
                unset($res['entries']['Filter']);
            } else {
                $res['stream'] = $a[2];
            }
            //print_r($res);
        } else {
            $t=$this->parseType($a[1]);
            if ($t['type'] == self::ENTRYTYPE_ARRAY) {
                $res = $a[1];
            } else {
                $res['entries'] = $this->parseEntry($a[1]);
            }
            //print_r($res);
        }
        $this->objects[$objectId] = $res;
        return $res;
    }
    
    public function OnCallbackObject(&$cObject)
    {
        if (($key=array_search($cObject->Name, $this->objectAssoc)) !== false) {
            $this->rearranged[$key] = $cObject->ObjectId;
            // reset the temporary name
            $cObject->Name = null;
        }
    }
    
    public function OnCallbackPage(&$page)
    {
        // TODO: add imported page number here
        $pageObjectId = &$this->pageToObject[1];
        if (isset($pageObjectId)) {
            $importedPage = &$this->page[$pageObjectId];
            //print_r($importedPage);
            if (isset($importedPage) && $importedPage['Resources']) {
                // add the imported page Resource entry into global Pages Resource
                //print_r($importedPage);
                foreach ($importedPage['Resources'] as $key => $value) {
                    if (!isset($this->resources[$key])) {
                        $ret = $this->valueToDictionary($value);
                        $this->AddResource($key, $ret);
                    }
                }
            }
        }
    }
    
    private function valueToDictionary($entryValue)
    {
        if (isset($entryValue) && is_array($entryValue)) {
            $res = '<< ';
            foreach ($entryValue as $key => $value) {
                if (is_array($value)) {
                    $res.= " /$key ".$this->valueToDictionary($value);
                } else {
                    $t = $this->parseType($value);
                    
                    if ($t['type'] == self::ENTRYTYPE_INDIRECT) {
                        if (isset($this->rearranged[$t['value']])) {
                            $value = $this->rearranged[$t['value']].' 0 R';
                        }
                    } elseif ($t['type'] == self::ENTRYTYPE_ARRAY) {
                        $m = preg_match_all("/\/(\w+)\s+(\d+) 0 R/", $value, $regs);
                        if ($m) {
                            $tmp = '[';
                            foreach ($regs[2] as $k => $v) {
                                if (isset($this->rearranged[$v])) {
                                    $tmp .= '/'.$regs[1][$k].' '.$this->rearranged[$v].' 0 R';
                                }
                            }
                            $tmp .= ']';
                            $value = $tmp;
                        }
                    }
                    $res.= "/$key $value";
                }
            }
            $res.= ' >>';
            return $res;
        } else {
            return $entryValue;
        }
    }
    
    private function findIndirectObjects(&$entryArray, $ignoreKeys = array(), $deep = false, $withStream = false)
    {
        $res = array();
        foreach ($entryArray as $key => &$value) {
            // ignore some keys
            if (in_array($key, $ignoreKeys)) {
                continue;
            }
            
            if (is_array($value)) {
                $r = $this->findIndirectObjects($value, $ignoreKeys, $deep, $withStream);
                $res = array_merge($res, $r);
            } else {
                $t = $this->parseType($value);
                if ($t['type'] == self::ENTRYTYPE_INDIRECT) {
                    array_push($res, $t);
                    
                    $tmp = $this->GetObject($t['value'], $withStream);
                    // if GetObject returned a string, it seems to be any datatype of an dictionary
                    if ($deep && is_string($tmp)) {
                        $tmp2 = $this->parseType($tmp);
                        
                        if (isset($tmp2['type'])) {
                            array_pop($res);
                            switch ($tmp2['type']) {
                                case 7: // array entry (which may contain indirect entries)
                                    foreach ($tmp2['value'] as $av) {
                                        if ($av['type'] == self::ENTRYTYPE_INDIRECT) {
                                            array_push($res, $av);
                                        }
                                    }
                                    break;
                                case 1: // indirect entry
                                    array_push($res, $tmp);
                                    break;
                            }
                            $value = $tmp;
                        } else {
                            $r = $this->findIndirectObjects($tmp['entries'], $ignoreKeys, $deep, $withStream);
                            $res = array_merge($res, $r);
                        }
                    }
                    
                    if ($withStream) {
                        $value = $tmp;
                    }
                }
            }
        }
        return $res;
    }
    
    private function fillIndirectObjects(&$entryArray, $ignoreKeys = array())
    {
        return $this->findIndirectObjects($entryArray, $ignoreKeys, true, true);
    }
    
    private function getFullPage($pageObjectId)
    {
        if (isset($this->page[$pageObjectId])) {
            $page = $this->page[$pageObjectId];
            //print_r($page);
            
            if (isset($page['Contents'])) {
                $t = $this->parseType($page['Contents']);
                if ($t['type'] == self::ENTRYTYPE_INDIRECT) {
                    $res = $this->GetObject($t['value'], true);
                    if (!isset($res['entries'])) {
                        $t = $this->parseType($res);
                        foreach ($t['value'] as $v) {
                            $page['Contents'][$v['value']] = $this->GetObject($v['value'], true);
                        }
                    } else {
                        $page['Contents'] = [$t['value'] => $res];
                    }
                } elseif ($t['type'] == self::ENTRYTYPE_ARRAY) {
                    $page['Contents'] = array();
                    foreach ($t['value'] as $v) {
                        $page['Contents'][$v['value']] = $this->GetObject($v['value'], true);
                    }
                }
            }
            
            if (isset($page['MediaBox'])) {
                $t = $this->parseType($page['MediaBox']);
                $page['MediaBox'] = $t['value'];
            }
            if (isset($page['CropBox'])) {
                $t = $this->parseType($page['CropBox']);
                $page['CropBox'] = $t['value'];
            }
            if (isset($page['TrimBox'])) {
                $t = $this->parseType($page['TrimBox']);
                $page['TrimBox'] = $t['value'];
            }
            if (isset($page['BleedBox'])) {
                $t = $this->parseType($page['BleedBox']);
                $page['BleedBox'] = $t['value'];
            }
                       
            $this->indirectObjects = $this->findIndirectObjects($page, array('Parent', 'Contents'), true);
            return $page;
        }
        return null;
    }
    
    private function readXRefAndTrailer($xrefpos, $isLinearized = false)
    {
        fseek($this->filestream, $xrefpos, SEEK_SET);
        
        $buffer = stream_get_line($this->filestream, 4028, 'startxref');
        
        $xrefhead = array();
        if (!$isLinearized) {
            if (substr($buffer, 0, 4) != 'xref') {
                Cpdf::DEBUG("RPDI: xref position not found", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                return false;
            }
            
            $r = preg_match("/([0-9]+) ([0-9]+)/", $buffer, $xrefhead);
            if (!$r) {
                Cpdf::DEBUG("RPDI: Failed to receive XRef header", Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
                return false;
            }
            
            if ($xrefhead[1] <= 0) {
                $xrefhead[1] = 1;
                $xrefhead[2] -= 1;
            }
        } elseif (count($this->objectRef) > 0) {
            // some checks for linearized xref
            $k = array_keys($this->objectRef);
            
            $xrefhead[1] = 1;
            $xrefhead[2] = min($k) - 1;
        }
        
        // start object number
        $i = $xrefhead[1];
        
        if (preg_match_all("/^([0-9]{10}) ([0-9]{5}) (n)/m", $buffer, $regs)) {
            foreach ($regs[0] as $k => $v) {
                $this->objectRef[$i] = (int)$regs[1][$k];
                $i++;
            }
        }
        
        if ($i != ($xrefhead[1] + $xrefhead[2])) {
            //print_r($xrefhead);
            Cpdf::DEBUG("RPDI: XRef table mismatch: $i != ".($xrefhead[1] + $xrefhead[2]), Cpdf::DEBUG_MSG_ERR, Cpdf::$DEBUGLEVEL);
            return false;
        }
        
        if ($xrefhead[1] > 1 && !$isLinearized) {
            // might be a Linearized PDF
            $lin = $this->GetObject($xrefhead[1]);
            if (isset($lin) && isset($lin['entries']['Linearized'])) {
                $this->readXRefAndTrailer($lin['entries']['T'], true);
            }
        }
        if (!$isLinearized && ($pos = strpos($buffer, 'trailer')) !== false) {
            $this->trailer = $this->parseEntry(substr($buffer, $pos));
        }
        
        return true;
    }
       
    
    const TOKEN_NONE = 0;
    const TOKEN_NAME = 1;
    const TOKEN_VALUE = 2;
    const TOKEN_BRACKET = 3;
        
    private function parseEntry($entry, &$offset = 0)
    {
        $fields = [];
        $entry = str_replace("\n", " ", $entry);

        $length=strlen($entry);
        $i = 0;
        $level = 0;
        $bracketOpened = 0;
        
        $token = self::TOKEN_NONE;
              
        $name = '';
        $value = '';
        while ($i < $length) {
            if ($token == self::TOKEN_NONE && substr($entry, $i, 2) == '<<') {
                $level++;
                //echo "LEVEL UP $level - " . substr($entry, $i, 25) . "\n\n";
                $i++;
            } elseif ($token == self::TOKEN_NONE && $bracketOpened <= 0 && substr($entry, $i, 2) == '>>') {
                $level--;
                //echo "LEVEL DOWN $level - " . substr($entry, $i, 20) . "\n\n";
                $token = self::TOKEN_NONE;
                if ($level <= 0) {
                    $i++;
                    break;
                }
            } elseif ($token == self::TOKEN_NAME && substr($entry, $i, 2) == '<<') {
                $o = 0;
                $fields[$name] = $this->parseEntry(substr($entry, $i), $o);
                $i += $o;
                $token = self::TOKEN_NONE;
            } elseif (($token == self::TOKEN_NAME || $token == self::TOKEN_BRACKET) && $entry[$i] == '[') {
                $bracketOpened++;
                $value.= $entry[$i];
                $token = self::TOKEN_BRACKET;
            } elseif ($token == self::TOKEN_BRACKET && $bracketOpened > 0 && $entry[$i] == ']') {
                $bracketOpened--;
                if ($bracketOpened <= 0) {
                    $bracketOpened = 0;
                    $value .= $entry[$i];
                    $token = self::TOKEN_VALUE;
                } else {
                    $value .= $entry[$i];
                }
            } elseif ($token == self::TOKEN_BRACKET) {
                $value .= $entry[$i];
            } elseif ($token == self::TOKEN_VALUE) {
                $fields[$name] = $value;
                $name = $value = '';
                $token = self::TOKEN_NONE;
                $i--;
            } elseif ($token == self::TOKEN_NONE && $bracketOpened <= 0 && preg_match("/^\/([A-Z0-9]+)/i", substr($entry, $i), $regs)) {
                $name = $regs[1];
                $token = self::TOKEN_NAME;
                $i += strlen($regs[0]) - 1;
            } elseif ($token == self::TOKEN_NAME && $bracketOpened <= 0 && preg_match("/^([0-9]+ 0 R|[0-9]+|true|false|\/[A-Z0-9]+)/i", substr($entry, $i), $regs)) {
                $value = $regs[1];
                $token = self::TOKEN_VALUE;
                
                $i += strlen($regs[0]) - 1;
            }
            $i++;
        }
        
        $offset = $i;
        
        //print_r($fields);
        return $fields;
    }
    
    private function parseType($subject)
    {
        $parsed = array('type'=> null, 'value' => null);
        if (preg_match("/^([0-9]+) 0 R/", $subject, $regs)) {
            $parsed['value'] = (int)$regs[1];
            $parsed['type'] = self::ENTRYTYPE_INDIRECT;
        } elseif (preg_match("/^([0-9]+.[0-9]+)/", $subject, $regs)) {
            $parsed['value'] = floatval($regs[1]);
            $parsed['type'] = self::ENTRYTYPE_FLOAT;
        } elseif (preg_match("/^([0-9]+)/", $subject, $regs)) {
            $parsed['value'] = (int)$regs[1];
            $parsed['type'] = self::ENTRYTYPE_INT;
        } elseif (preg_match("/^(true|false)/i", $subject, $regs)) {
            if (strtolower(substr($regs[1], 1, 1)) == 't') {
                $parsed['value'] = true;
            } else {
                $parsed['value'] = false;
            }
            $parsed['type'] = self::ENTRYTYPE_BOOL;
        } elseif (preg_match("/^\/(\w+)/i", $subject, $regs)) {
            $parsed['value'] = $regs[1];
            $parsed['type'] = self::ENTRYTYPE_LABEL;
        } elseif (preg_match("/^\[(.*?)\]/i", $subject, $regs)) {
            // fetch all indirect array values
            $res = array();
            if (preg_match_all("/([0-9]+ 0 R|[0-9.]+)/", $regs[1], $r)) {
                foreach ($r[1] as $v) {
                    $t = $this->parseType($v);
                    array_push($res, $t);
                }
            }
            $parsed['value'] = $res;
            $parsed['type'] = self::ENTRYTYPE_ARRAY;
        } elseif (preg_match("/^\<\<(.*)\>\>/i", $subject)) {
            $parsed['value'] = $subject;
            $parsed['type'] = self::ENTRYTYPE_DICTIONARY;
        } else {
            $parsed['value'] = $subject;
            $parsed['type'] = self::ENTRYTYPE_STRING;
        }
        return $parsed;
    }

    public function __destruct()
    {
        fclose($this->filestream);
    }
}
