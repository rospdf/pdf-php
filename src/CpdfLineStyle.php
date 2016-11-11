<?php
namespace ROSPDF;
/**
 * Class object allowing the use of lines in any Appearance object
 */
class CpdfLineStyle
{
    /**
     * stores the line weight
     * @var float
     */
    private $weight;
    /**
     * stores the cap style
     * @var String
     */
    private $capStyle;
    /**
     * stores the join style
     * @var String
     */
    private $joinStyle;
    /**
     * stores the dash style
     * @var String
     */
    private $dashStyle;

    /**
     * Contructor call
     * @param float $weight line weight
     * @param String $cap cap style, see $this->SetCap()
     * @param String $join join style, see $this->SetJoin()
     * @param Array $dash dash format, see $this->SetDashes();
     */
    public function __construct($weight = 0, $cap = '', $join = '', $dash = array())
    {
        $this->weight = $weight;
        $this->SetCap($cap);
        $this->SetJoin($join);

        if (is_array($dash)) {
            if (count($dash) == 3) {
                $this->SetDashes($dash[0], $dash[1], $dash[2]);
            } elseif (count($dash) == 2) {
                $this->SetDashes($dash[0], $dash[1]);
            } elseif (count($dash) == 1) {
                $this->SetDashes($dash[0], $dash[0]);
            }
        }
    }

    /**
     * get the line weight out of the class object
     */
    public function GetWeight()
    {
        return $this->weight;
    }

    /**
     * Set the cap style of a line
     * @param String $name possible styles are butt, round and square
     */
    public function SetCap($name = 'butt')
    {
        switch ($name) {
            default:
            case 'butt':
                $this->capStyle = 0;
                break;
            case 'round':
                $this->capStyle = 1;
                break;
            case 'square':
                $this->capStyle = 2;
                break;
        }
    }

    /**
     * set the join style of a line
     * @param String $name possible styles are butt, round and bevel
     */
    public function SetJoin($name = 'miter')
    {
        switch ($name) {
            default:
            case 'miter':
                $this->joinStyle = 0;
                break;
            case 'round':
                $this->joinStyle = 1;
                break;
            case 'bevel':
                $this->joinStyle = 2;
                break;
        }
    }

    /**
     * Used to define the line spaces
     * @param int $on
     * @param int $off
     * @param int $phase
     */
    public function SetDashes($on, $off, $phase = 0)
    {
        $this->dashStyle = array($on, $off, $phase);
    }

    /**
     * PDF output of the line style
     */
    public function Output()
    {
        $res = '';

        $res.= sprintf("%.3F w", $this->weight);

        if (isset($this->capStyle)) {
            $res.= ' '.$this->capStyle.' J';
        }
        if (isset($this->joinStyle)) {
            $res.= ' '.$this->joinStyle.' j';
        }
        if (is_array($this->dashStyle) && count($this->dashStyle) == 3) {
            if ($this->dashStyle[0] > 0) {
                $res.= ' ['.$this->dashStyle[0];
                if ($this->dashStyle[1] != $this->dashStyle[0]) {
                    $res.= ' '.$this->dashStyle[1];
                }
                $res.='] '.$this->dashStyle[2].' d';
            }
        } else {
            $res.= ' [] 0 d';
        }
        return $res.' ';
    }
}

?>