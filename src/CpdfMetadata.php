<?php

namespace ROSPDF;

/**
 * PDF document info (Metadata).
 */
class CpdfMetadata
{
    public $ObjectId;

    private $pages;
    private $info;

    public function __construct(&$pages)
    {
        $this->pages = &$pages;

        $this->info = array(
            'Title' => 'PDF Document Title',
            'Author' => 'ROS pdf class',
            'Producer' => 'ROS for PHP',
            'Description' => '',
            'Subject' => '',
            'Creator' => 'ROS pdf class',
            'CreationDate' => time(),
            'ModDate' => time(),
            'Trapped' => 'False',
        );
    }

    public function SetInfo($key = 'Title', $value = 'PDF document title')
    {
        $this->info[$key] = $value;
    }

    private function outputInfo()
    {
        $res = "\n<<";
        if (count($this->info) > 0) {
            $encObj = &$this->pages->encryptionObject;

            if (isset($encObj)) {
                $encObj->encryptInit($this->ObjectId);
            }

            foreach ($this->info as $key => $value) {
                switch ($key) {
                    case 'Trapped':
                        $res .= " /$key /$value";
                        break;
                    case 'ModDate':
                    case 'CreationDate':
                        $value = $this->getDate($value);
                    default:
                        if (isset($encObj)) {
                            $dummyAsRef = null;
                            $res .= " /$key (".$this->pages->filterText($dummyAsRef, $encObj->ARC4($value)).')';
                        } else {
                            $res .= " /$key ($value)";
                        }
                        break;
                }
            }
        }
        $res .= ' >>';

        return $res;
    }

    /**
     * TODO: build up the XML metadata object which is avail since PDF version 1.4.
     */
    private function outputXML()
    {
        $res = "\n<< /Type /Metadata /Subtype /XML";
        // dummy output for XMP
        $tmp = "\n".'<?xpacket begin="" id="W5M0MpCehiHzreSzNTczkc9d"?>
		<x:xmpmeta xmlns:x="adobe:ns:meta/" x:xmptk="Adobe XMP Core 4.2.1-c043 52.372728, 2009/01/18-15:08:04">
			<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
				<rdf:Description rdf:about="" xmlns:dc="http://purl.org/dc/elements/1.1/">
					<dc:format>application/pdf</dc:format>
					<dc:title>
						<rdf:Alt>
							<rdf:li xml:lang="x-default">'.$this->info['Title'].'</rdf:li>
						</rdf:Alt>
					</dc:title>
					<dc:creator>
						<rdf:Seq>
							<rdf:li>'.$this->info['Creator'].'</rdf:li>
						</rdf:Seq>
					</dc:creator>
					<dc:description>
						<rdf:Alt>
							<rdf:li xml:lang="x-default">'.$this->info['Description'].'</rdf:li>
						</rdf:Alt>
					</dc:description>
					<dc:subject>
						<rdf:Bag>
							<rdf:li>'.$this->info['Subject'].'</rdf:li>
						</rdf:Bag>
					</dc:subject>
				</rdf:Description>
				<rdf:Description rdf:about="" xmlns:xmp="http://ns.adobe.com/xap/1.0/">
					<xmp:CreateDate>'.$this->getDate($this->info['CreationDate'], 'XML').'</xmp:CreateDate>
					<xmp:CreatorTool>'.$this->info['Creator'].'</xmp:CreatorTool>
					<xmp:ModifyDate>'.$this->getDate($this->info['ModDate'], 'XML').'</xmp:ModifyDate>
				</rdf:Description>
				<rdf:Description rdf:about="" xmlns:pdf="http://ns.adobe.com/pdf/1.3/">
					<pdf:Producer>'.$this->info['Producer'].'</pdf:Producer>
				</rdf:Description>
			</rdf:RDF>
		</x:xmpmeta>
		<?xpacket end="w"?>';

        $res .= ' /Length '.strlen($tmp).' >>';
        $res .= "\nstream".$tmp."\n\nendstream";

        return $res;
    }

    private function getDate($t, $type = 'PLAIN')
    {
        switch (strtoupper($type)) {
            default:
            case 'PLAIN':
                return 'D:'.date('YmdHis', $t)."+00'00'";
                break;
            case 'XML':
                return date('Y-m-d', $t).'T'.date('H:i:s').'Z';
                break;
        }
    }

    public function OutputAsObject($type = 'PLAIN')
    {
        $res = "\n$this->ObjectId 0 obj";

        switch (strtoupper($type)) {
            case 'PLAIN':
                $res .= $this->outputInfo();
                break;
            case 'XML':
                $res .= $this->outputXML();
                break;
        }

        $res .= "\nendobj";
        $this->pages->AddXRef($this->ObjectId, strlen($res));

        return $res;
    }
}
