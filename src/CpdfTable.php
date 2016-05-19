<?php

class CpdfTable extends CpdfAppearance
{

    const DRAWLINE_ALL = 31;
    const DRAWLINE_DEFAULT = 29;
    const DRAWLINE_TABLE = 24;
    const DRAWLINE_TABLE_H = 16;
    const DRAWLINE_TABLE_V = 8;
    const DRAWLINE_HEADERROW = 4;
    const DRAWLINE_ROW = 2;
    const DRAWLINE_COLUMN = 1;

    public $Fit = true;
    public $DrawLine;

    private $columnWidths;

    private $numCells;

    private $cellIndex = 0;
    private $rowIndex = 0;
    private $pageBreak;

    private $maxCellY;
    private $pageBreakCells;

    private $columnStyle;

    private $lineStyle;
    private $lineWeight;
    private $backgroundColor;

    private $app;

    public function __construct(&$pages, $bbox = array(), $nColumns = 2, $bgColor = array(), $lineStyle = null, $drawLines = CpdfTable::DRAWLINE_TABLE)
    {
        parent::__construct($pages, $bbox, '');

        $this->backgroundColor = $bgColor;

        $this->BreakPage = CpdfContent::PB_CELL | CpdfContent::PB_BBOX;
        $this->resizeBBox = true;

        $this->pageBreakCells = array();
        $this->columnStyle = array();
        $this->DrawLine = $drawLines;

        $this->numCells = $nColumns;
        $this->SetColumnWidths();

        $this->lineWeight = 0;
        if (is_object($lineStyle)) {
            $this->lineStyle = $lineStyle;
            $this->lineWeight = $lineStyle->GetWeight();
        }

        // reset font color
        $this->AddColor(0, 0, 0);
        // set default font

        // FOR DEBUGGING - DISPLAY A RED COLORED BOUNDING BOX
        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_TABLE)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], $this->BBox[1] - $this->BBox[3])." S Q";
        }

        $this->BBox[1] = $this->BBox[3];

        $this->app = $pages->NewAppearance($this->initialBBox);
        $this->app->ZIndex = -5;
    }

    /**
     * set the width for each column
     */
    public function SetColumnWidths()
    {
        $this->columnWidths = array();

        $widths = func_get_args();

        $maxWidth = ($this->BBox[2] - $this->BBox[0]);

        if (count($widths) > 0) {
            $usedWidth = 0;
            $j = 0;
            for ($i=0; $i < $this->numCells; $i++) {
                if (isset($widths[$i])) {
                    $this->columnWidths[$i] = $widths[$i];
                    $usedWidth += $widths[$i];
                    $j++;
                }
            }

            $restColumns = $this->numCells - $j;
            if ($restColumns > 0) {
                $restWidth = $maxWidth - $usedWidth;
                $restPerCell = $restWidth / $restColumns;

                for ($i=0; $i < $this->numCells; $i++) {
                    if (!isset($this->columnWidths[$i])) {
                        $this->columnWidths[$i] = $restPerCell;
                    }
                }
            }
        } else {
            // calculate the cell max width (incl. border weight)
            $cellWidth = $maxWidth / $this->numCells;

            if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_TABLE)) {
                $cellWidth -= $this->lineWeight / 2;
            }

            foreach (range(0, ($this->numCells - 1)) as $v) {
                $this->columnWidths[$v] = $cellWidth;
            }
        }
    }

    private function getHalfLineWeight($drawMode = 0)
    {
        if ($this->getLineWeight($drawMode) > 0) {
            return ($this->lineWeight / 2);
        }
        return 0;
    }

    private function getLineWeight($drawMode = 0)
    {
        if (!isset($drawMode)) {
            $drawMode = $this->DrawLine;
        }
        if (Cpdf::IsDefined($this->DrawLine, $drawMode)) {
            return $this->lineWeight;
        }
        return 0;
    }

    public function AddCell($text, $justify = 'left', $backgroundColor = array(), $padding = array())
    {
        $paddingBBox = $this->BBox;

        if (isset($padding['top'])) {
            Cpdf::SetBBox(array('adduy' => -$padding['top']), $paddingBBox);
        }

        if (isset($padding['bottom'])) {
            Cpdf::SetBBox(array('addly' => -$padding['bottom']), $paddingBBox);
        }

        if (!isset($this->CURFONT)) {
            $this->SetFont("Helvetica");
        }

        if (!isset($this->maxCellY)) {
            $this->y += $this->fontHeight + $this->fontDescender - $this->fontDescender;
        }

        $this->y = $paddingBBox[3];
        $this->y -= $this->fontHeight + $this->fontDescender;

        // to recover the column style on page break, store it globally
        $this->columnStyle[$this->cellIndex] = array('justify' => $justify,'backgroundColor'=>$backgroundColor, 'padding'=>$padding);

        // force page break before writting any text content as it does not fit to the current font size
        if ($this->y < $this->initialBBox[1] && Cpdf::IsDefined($this->BreakPage, CpdfContent::PB_CELL)) {
            $this->pageBreak = true;
            $this->pageBreakCells[$this->cellIndex] = $text;
            $this->cellIndex++;
            if ($this->cellIndex >= $this->numCells) {
                $this->endRow(true);
            }
            return;
        }

        //$this->x = $this->BBox[0];
        $this->BBox[2] = $this->BBox[0] + $this->columnWidths[$this->cellIndex];

        $lw = $this->getLineWeight();
        // amend the margin to display table border completely
        if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_TABLE)) {
            if ($this->cellIndex == 0) {
                Cpdf::SetBBox(array('addlx'=> $lw), $this->BBox);
            } elseif ($this->cellIndex + 1 >= $this->numCells) {
                Cpdf::SetBBox(array('addux'=> -$lw), $this->BBox);
            }
        }

        // some text offset if column line is shown
        if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_COLUMN) && $this->cellIndex + 1 < $this->numCells) {
            Cpdf::SetBBox(array('addux'=> -$lw), $this->BBox);
        }

        $p = $this->AddText($text, 0, $justify);

        // recover BBox UX when column line has been printed
        if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_COLUMN) && $this->cellIndex + 1 < $this->numCells) {
            Cpdf::SetBBox(array('addux'=> $lw), $this->BBox);
        }

        if (isset($p)) {
            $t = substr($text, $p);
            if (!empty($t)) {
                $this->pageBreak = true;

                $this->pageBreakCells[$this->cellIndex] = $t;
            }
        }

        if (!isset($this->maxCellY) || $paddingBBox[1] < $this->maxCellY) {
            $this->maxCellY = $paddingBBox[1];
        }

        $this->cellIndex++;
        if ($this->cellIndex >= $this->numCells) {
            $this->endRow();
        } else {
            //$this->y = $this->BBox[3] - $this->fontDescender;
            $this->BBox[0] = $this->BBox[2]; //$this->columnWidths[$this->cellIndex - 1];
        }

    }

    private function endRow($endOfTable = false)
    {
        // a bit more space between rows when line is shown
        if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_ROW)) {
            $this->maxCellY -= $this->getLineWeight();
        }

        $maxCellY = $this->maxCellY + $this->fontDescender;

        // reset cell counter
        $this->cellIndex = 0;

        $this->parsedRowIndex = $this->rowIndex;

        // increase the row number
        if (!$endOfTable) {
            $this->rowIndex++;
        }

        // reset x position
        $this->BBox[0] = $this->initialBBox[0];

        if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_ROWS)) {
            $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->BBox[0], $this->BBox[3], $this->BBox[2] - $this->BBox[0], ($maxCellY) - $this->BBox[3])." S Q % DEBUG OUTPUT";
        }

        // draw the row border
        if (!$endOfTable && ( (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_ROW) && $this->rowIndex > 2) || Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_HEADERROW) && $this->rowIndex == 2)) {
            $tmp = $this->app->BBox;

            //$offset = $this->BBox[1] - $this->BBox[3];
            $width = $this->BBox[2] - $this->BBox[0];

            $this->app->BBox = $this->BBox;

            $this->app->AddLine(0, 0, $width, 0, null);
            $this->app->BBox = $tmp;
        }

        // draw cell background color
        if (!$endOfTable) {
            $cellBBox = $this->BBox;
            $cellxstart = $cellBBox[0] + $this->getHalfLineWeight(CpdfTable::DRAWLINE_TABLE_V);

            for ($i=0; $i < $this->numCells; $i++) {
                $cellxend = $cellxstart + $this->columnWidths[$i];

                $columnStyle = &$this->columnStyle[$i];

                if (is_array($columnStyle['backgroundColor']) && count($columnStyle['backgroundColor']) >= 3) {
                    if ($i + 1 == $this->numCells) {
                        $cellxend -= $this->getLineWeight(CpdfTable::DRAWLINE_TABLE_V);
                    }

                    $this->pages->DoCall($this, 'background', $this->BBox, $this->columnStyle[$i]);
                    Cpdf::SetBBox(array( 'ly'=> $maxCellY,
                                                'lx' => $cellxstart,
                                                'ux' => $cellxend
                                            ), $cellBBox);
                    $this->pages->DoTrigger($this, 'background', $cellBBox);
                }
                $cellxstart = $cellxend;
            }
        }

        // draw the column border
        if (!$endOfTable && Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_COLUMN)) {
            $tmp = $this->app->BBox;
            $this->app->BBox = $this->BBox;
            $height = $this->getFontDescender() - $this->getFontHeight();

            $nx = 0;
            for ($i=0; $i < ($this->numCells - 1); $i++) {
                $nx += $this->columnWidths[$i];
                $this->app->AddLine($nx, 0, 0, $height, null);
            }
            $this->app->BBox = $tmp;
        }

        $this->pages->Callback(0, 0, true);

        // set the Y position for the next row
        $this->BBox[3] = $maxCellY;
        // XXX: is this correct? or plus fontDescender?
        $this->y = $maxCellY;

        // if its a page break - set in AddCell method
        if ($this->pageBreak) {
            $bbox = $this->setBackground();

            $obj = Cpdf::DoClone($this);
            $this->pages->addObject($obj, true);

            $this->contents = '';

            $this->BBox = $this->initialBBox;

            $p = $this->pages->NewPage($this->page->Mediabox);
            $this->page = $p;

            if (Cpdf::IsDefined($this->BreakPage, CpdfContent::PB_BLEEDBOX)) {
                $this->initialBBox[1] = $this->page->Bleedbox[1];
                $this->initialBBox[3] = $this->page->Bleedbox[3];
                $this->BBox[3] = $this->initialBBox[3];
                $this->BBox[1] = $this->initialBBox[1];
            }

            if (Cpdf::IsDefined(Cpdf::$DEBUGLEVEL, Cpdf::DEBUG_TABLE)) {
                $this->contents.= "\nq 1 0 0 RG ".sprintf('%.3F %.3F %.3F %.3F re', $this->initialBBox[0], $this->initialBBox[3], $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[1] - $this->initialBBox[3])." S Q % DEBUG OUTPUT";
            }

            $this->BBox[3] = $this->initialBBox[3];

            // force to rsize the BBox
            $this->BBox[1] = $this->BBox[3];

            $this->BBox[0] = $this->initialBBox[0];
            $this->y = $this->BBox[3] - $this->fontDescender;

            $this->maxCellY = $this->BBox[3];

            $this->app = $this->pages->NewAppearance($this->initialBBox);
            $this->app->ZIndex = -5;

            $this->pageBreak = false;

            if (count($this->pageBreakCells) > 0) {
                $pcells = $this->pageBreakCells;
                $this->pageBreakCells= array();

                for ($i = 0; $i < $this->numCells; $i++) {
                    $columnStyle = &$this->columnStyle[$i];
                    if (isset($pcells[$i])) {
                        $this->AddCell($pcells[$i], $columnStyle['justify'], $columnStyle['backgroundColor'], $columnStyle['padding']);
                    } else {
                        $this->AddCell("", $columnStyle['justify'], $columnStyle['backgroundColor'], $columnStyle['padding']);
                    }
                }
            }
        }
    }

    private function setBackground()
    {
        $bbox = $this->initialBBox;
        if (!isset($this->app)) {
            return $bbox;
        }

        $this->app->SetPageMode($this->Paging);

        $filled = false;
        if (is_array($this->backgroundColor) && count($this->backgroundColor) == 3) {
            $filled = true;
            $this->app->AddColor($this->backgroundColor[0], $this->backgroundColor[1], $this->backgroundColor[2]);
        }
        // width and height can be set to zero as it will use the BBox to calculate max widht and max height
        if ($this->Fit) {
            $newY = $this->maxCellY - $this->initialBBox[1] - ($this->lineWeight / 2) + $this->fontDescender;
            $height = $this->initialBBox[3] - $this->maxCellY + $this->lineWeight - $this->fontDescender;

            if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_TABLE)) {
                $this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, $this->lineStyle);
            } elseif ($filled) {
                $this->app->AddRectangle(0, $newY, $this->initialBBox[2] - $this->initialBBox[0], $height, $filled, null);
            }
            $bbox[1] = $this->BBox[1] + $this->fontDescender;
        } else {
            if (Cpdf::IsDefined($this->DrawLine, CpdfTable::DRAWLINE_TABLE)) {
                $this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
            } elseif ($filled) {
                $this->app->AddRectangle(0, 0, $this->initialBBox[2] - $this->initialBBox[0], $this->initialBBox[3] - $this->initialBBox[1], $filled, $this->lineStyle);
            }
        }

        $tmp = $this->app->BBox;

        return $bbox;
    }

    /**
     * End the table and return bounding box to define next Appearance or text object
     */
    public function EndTable()
    {
        $this->pageBreakCells = array();
        $this->endRow(true);

        $bbox = $this->setBackground();
        $this->x = $bbox[0];
        $this->y = $bbox[1];
    }
}
?>