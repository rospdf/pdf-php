<?php
namespace ROSPDF;
/**
 * Color class object for RGB and CYMK
 */
class CpdfColor
{
    public $colorArray;
    public $stroke;

    public function __construct($color = array(), $stroke = true)
    {
        $this->colorArray = $color;
        $this->stroke = $stroke;

        if (Cpdf::$ForceCMYK) {
            $this->rgb2cmyk();
        }
    }

    private function rgb2cmyk()
    {
        if (is_array($this->colorArray) && count($this->colorArray) == 3) {
            $tmp = $this->colorArray;
            // cyan (c)
            $c = 1.0 - $tmp[0];
            $m = 1.0 - $tmp[1];
            $y = 1.0 - $tmp[2];
            $k = min($c, $m, $y);

            $UCR = $k;
            $BG = $k;

            $this->colorArray[0] = min(1.0, max(0.0, $c - $UCR));
            // magenty (m)
            $this->colorArray[1] = min(1.0, max(0.0, $m - $UCR));
            // yellow (y)
            $this->colorArray[2] = min(1.0, max(0.0, $y - $UCR));
            // black (k)
            $this->colorArray[3] = min(1.0, max(0.0, $UCR));
        }
    }

    /**
     * PDF output of the color
     */
    public function Output($asArray = true, $withColorspace = false)
    {
        $res='';

        if (is_array($this->colorArray)) {
            foreach ($this->colorArray as $v) {
                $res.= sprintf("%.3F ", $v);
            }

            if ($withColorspace) {
                if (count($this->colorArray) >= 4) { // DeviceCMYK
                    $res.= ($this->stroke)?'K':'k';
                } elseif (count($this->colorArray) >= 3) { // DeviceRGB
                    $res.= ($this->stroke)?'RG':'rg';
                } else {
                    $res.= ($this->stroke)?'G':'g';
                }
            }
        } else {
            $res = '0';
        }
        $res = ($asArray)?'['.$res.']':$res;
        return $res;
    }
}
?>