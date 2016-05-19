<?php
namespace ROSPDF;

/**
 * graphic class used for drawings like rectangles and lines in order to allow callbacks
 * Callback function may overwrite the X, Y, Width and Height property to adjust size or position
 */
class CpdfGraphic
{
    public $Type;

    public $X;
    public $Y;

    public $Width;
    public $Height;

    public $Params;

    public function __construct($type = 'line', $x, $y)
    {
        $this->Type = $type;
        $this->Params = array();
        $this->X = $x;
        $this->Y = $y;
    }

    public function Output()
    {
        $res = 'q ';
        if (isset($this->Params['style']) && is_object($this->Params['style'])) {
            $ls = &$this->Params['style'];
            $res.= $ls->Output();
        }

        switch ($this->Type) {
            case 'rectangle':
                $res.= sprintf('%.3F %.3F %.3F %.3F re', $this->X, $this->Y, $this->Width, $this->Height);

                if (isset($this->Params['filled']) && $this->Params['filled']) {
                    if (isset($this->Params['style']) && (is_object($this->Params['style']) || (is_bool($this->Params['style']) && $this->Params['style']))) {
                        $res.=' b';
                    } else {
                        $res.=' f';
                    }
                } else {
                    $res.=' S';
                }
                break;
            case 'line':
                $res.= sprintf('%.3F %.3F m %.3F %.3F l S', $this->X, $this->Y, $this->X + $this->Width, $this->Y + $this->Height);
                break;
        }
        return $res.' Q';
    }
}
?>