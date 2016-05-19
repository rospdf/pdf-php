<?php
namespace ROSPDF;
/**
 * PDF border style object used in annotations
 */
class CpdfBorderStyle
{
    /**
     * static type name used in PDF object
     */
    public $Type = '/Border';
    /**
     * Width of the border in points
     * @default number 1
     */
    public $Width;
    /**
     * Style of the border
     * @default string 'S'
     */
    public $Style;
    /**
     * dash arrays
     */
    public $dashArray = array();

    /**
     * Borderstyle used in Annotations. Can be shown differently dependent on the PDF Viewer
     *
     * @param float $weight define the weight of the border
     * @param string $style define a style type - 'solid', 'dash', 'underline' or 'bevel'
     * @param array $dashArray used to define the gaps of a dashed line
     */
    public function __construct($weight = 0, $style = 'solid', $dashArray = array())
    {
        $this->Weight = $weight;
        $this->Style = $style;
        $this->dashArray = $dashArray;
    }

    /**
     * PDF output of the border style
     */
    public function Output()
    {
        $res='';
        if ($this->Weight > 0 && $this->Style != 'none') {
            $res = " /Type $this->Type /W ".sprintf("%.3F", $this->Weight);
            switch (strtolower($this->Style)) {
                case 'underline':
                case 'underlined':
                    $res .= ' /S /U';
                    break;
                case 'dash':
                    $res .= ' /S /D /D [';
                    if (is_array($this->dashArray) && count($this->dashArray) > 0) {
                        foreach ($this->dashArray as $v) {
                            $res.= sprintf("%d", $v);
                        }
                    } else {
                        $res.='3';
                    }
                    $res.=']';
                    break;
                case 'bevel':
                    $res .= ' /S /B';
                    break;
            }
        }
        return $res;
    }
}
?>