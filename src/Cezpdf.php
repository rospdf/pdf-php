<?php
/**
 * Create pdf documents without additional modules
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see http://www.gnu.org/licenses/
 *
 * @category Documents
 * @package  Cezpdf
 * @version  0.13.0
 * @author   Ole Koeckemann <ole1986@users.sourceforge.net>
 *
 * @copyright 2013 The author(s)
 * @license  GNU General Public License v3
 * @link     http://pdf-php.sf.net
 */
require_once 'Cpdf.php';

/**
 * draw all lines to ezTable output
 */
define('EZ_GRIDLINE_ALL', 31);
/**
 * draw default set of lines to ezTable output, so EZ_GRIDLINE_TABLE, EZ_GRIDLINE_HEADERONLY and EZ_GRIDLINE_COLUMNS
 */
define('EZ_GRIDLINE_DEFAULT', 29); // same as EZ_GRIDLINE_TABLE + EZ_GRIDLINE_HEADERONLY + EZ_GRIDLINE_COLUMNS
/**
 * draw the outer lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE', 24);
/**
 * draw the outer horizontal lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE_H', 16);
/**
 * draw the outer vertical lines of the ezTable
 */
define('EZ_GRIDLINE_TABLE_V', 8);
/**
 * draw a horizontal line between header and first data row
 */
define('EZ_GRIDLINE_HEADERONLY', 4);
/**
 * draw a horizontal line for each row
 */
define('EZ_GRIDLINE_ROWS', 2);
/**
 * draw a vertical line for each column
 */
define('EZ_GRIDLINE_COLUMNS', 1);

/**
 * Helper class to create pdf documents via ROS PDF class called 'Cpdf'
 *
 * This class will take the basic interaction facilities of the Cpdf class
 * and make more useful functions so that the user does not have to
 * know all the ins and outs of pdf presentation to produce something pretty.
 * <pre>
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see http://www.gnu.org/licenses/
 * </pre>
 * @category Documents
 * @package Cpdf
 * @version $Id: Cezpdf.php 266 2014-01-13 08:13:42Z ole1986 $
 * @author Wayne Munro, R&OS Ltd, <http://www.ros.co.nz/pdf>
 * @author Ole Koeckemann <ole1986@users.sourceforge.net>
 * @copyright 2014 The authors
 * @license GNU General Public License v3
 * @link http://pdf-php.sf.net
 */
 class Cezpdf extends Cpdf_Extension {

    /**
     * used to store most of the page configuration parameters
     */
    public $ez=array('fontSize'=>10);
    /**
     * stores the actual vertical position on the page of the writing point, very important
     */
    public $y;
    
    public $ezPage;
    /**
     * keep an array of the ids of the pages, making it easy to go back and add page numbers etc.
     */
    public $ezPages=array();
    /**
     * stores the number of pages used in this document
     */
    public $ezPageCount=0;


	protected $ezTable;
	
	protected $ezAppearance;
    /**
     * background color/image information
     */
    protected $ezBackground = array();
    /**
     * Assuming that people don't want to specify the paper size using the absolute coordinates
     * allow a couple of options:
     * orientation can be 'portrait' or 'landscape'
     * or, to actually set the coordinates, then pass an array in as the first parameter.
     * the defaults are as shown.
     *
     * 2002-07-24 - Nicola Asuni (info@tecnick.com):
     * Added new page formats (45 standard ISO paper formats and 4 american common formats)
     * paper cordinates are calculated in this way: (inches * 72) where 1 inch = 2.54 cm
     *
     * **$options**<br>
     * if $type equals to 'color'<br>
     *   $options[0] = red-component   of backgroundcolour ( 0 <= r <= 1)<br>
     *   $options[1] = green-component of backgroundcolour ( 0 <= g <= 1)<br>
     *   $options[2] = blue-component  of backgroundcolour ( 0 <= b <= 1)<br>
     * if $type equals to 'image':<br>
     *   $options['img']     = location of image file; URI's are allowed if allow_url_open is enabled in php.ini<br>
     *   $options['width']   = width of background image; default is width of page<br>
     *   $options['height']  = height of background image; default is height of page<br>
     *   $options['xpos']    = horizontal position of background image; default is 0<br>
     *   $options['ypos']    = vertical position of background image; default is 0<br>
     *   $options['repeat']  = repeat image horizontally (1), repeat image vertically (2) or full in both directions (3); default is 0<br>
     *
     * highly recommend to set this->hashed to true when using repeat function<br>
     * 
     * @since [0.11.3] added repeat option for images
     *
     * @param mixed $paper paper format as string ('A4', 'A5', 'B5', ...) or an array with two/four elements defining the size
     * @param string $orientation either portrait or landscape
     * @param string $type background type - 'none', 'image' or 'color'
     * @param array $options see options from above
     */
    public function __construct($paper='a4',$orientation='portrait', $type = 'none', $options = array()){
        if (!is_array($paper)){
        	$size = Cpdf_Common::$Layout[strtoupper($paper)];
            
            switch (strtolower($orientation)){
                case 'landscape':
                    $a=$size[3];
                    $size[3]=$size[2];
                    $size[2]=$a;
                    break;
            }
        } else {
            if (count($paper)>2) {
                // then an array was sent it to set the size
                $size = $paper;
            }
            else { //size in centimeters has been passed
                $size[0] = 0;
                $size[1] = 0;
                $size[2] = ( $paper[0] / 2.54 ) * 72;
                $size[3] = ( $paper[1] / 2.54 ) * 72;
            }
        }
        
        $this->ez['pageWidth']=$size[2];
        $this->ez['pageHeight']=$size[3];

		$bleedbox = $this->calcBleedbox($size, 30, 30, 30,30);
	
		parent::__construct($size, null, $bleedbox);

        // set the current writing position to the top of the first page
        $this->y = $this->ez['pageHeight']-$this->ez['topMargin'];
        // and get the ID of the page that was created during the instancing process.
        //$this->ezPages[1]=$this->getFirstPageId();
        //$this->ezPageCount=1;

        switch ($type) {
            case 'color'  :
            case 'colour' :
				$this->CURPAGE->SetBackground($options);
                break;
            case 'image'  :
				$this->CURPAGE->SetBackground(null, $options['img']);
                break;
        }
    }
    
	private function calcBleedbox($mediabox, $leftMargin, $bottomMargin, $rightMargin, $topMargin){
		if(!is_array($mediabox)){
			Cpdf::DEBUG("Mediabox is not an array", Cpdf::DEBUG_MSG_WARN, $this->DEBUG);
			return;
		}
		
		if(count($mediabox) != 4){
			Cpdf::DEBUG("Mediabox array is does not contain exact four elements", Cpdf::DEBUG_MSG_WARN, $this->DEBUG);
			return;
		}
		
		// XXX: Backward compability
		$this->ez['leftMargin'] = $leftMargin;
		$this->ez['bottomMargin'] = $bottomMargin;
		$this->ez['rightMargin'] = $rightMargin;
		$this->ez['topMargin'] = $topMargin;
		
		return array( 	$mediabox[0] + $leftMargin, 
						$mediabox[1] + $bottomMargin, 
						$mediabox[2] - $rightMargin,
						$mediabox[3] - $topMargin );
	}
    
    /**
     * setup a margin on document page
     * 
     * **Example**<br>
     * <pre>
     * $pdf->ezSetMargins(50,50,50,50)
     * </pre>
     *
     * @param float $top top margin
     * @param float $bottom botom margin
     * @param float $left left margin
     * @param float $right right margin
     */
    public function ezSetMargins($topMargin, $bottomMargin, $leftMargin, $rightMargin){
    	if(!is_object($this->CURPAGE)){
    		Cpdf::DEBUG("Current page not found", Cpdf::DEBUG_MSG_ERR, $this->DEBUG);
			return;
    	}
		
    	$this->CURPAGE->Bleedbox = $this->calcBleedbox($this->CURPAGE->Mediabox, $leftMargin, $bottomMargin, $rightMargin, $topMargin);
    }

    /**
     * setup a margin on document page
     * 
     * @author 2002-07-24: Nicola Asuni (info@tecnick.com)
     * @param float $top top margin in cm
     * @param float $bottom botom margin in cm
     * @param float $left left margin in cm
     * @param float $right right margin in cm
     */
    public function ezSetCmMargins($top,$bottom,$left,$right){
        $top = ( $top / 2.54 ) * 72;
        $bottom = ( $bottom / 2.54 ) * 72;
        $left = ( $left / 2.54 ) * 72;
        $right = ( $right / 2.54 ) * 72;
        $this->ezSetMargins($top,$bottom,$left,$right);
    }

    /**
     * create a new page
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezNewPage()
     * </pre>
     */
    public function ezNewPage(){
    	if(!is_object($this->CURPAGE)){
    		Cpdf::DEBUG("Current page not found", Cpdf::DEBUG_MSG_ERR, $this->DEBUG);
			return;
    	}
		
		$tmpBackground = $this->CURPAGE->Background;
		
    	$this->ezPage = &$this->NewPage(null, null, $this->CURPAGE->Bleedbox);
		
        // set the same background as before
		$newPage->Background = $tmpBackground;
    }

    /**
     * starts to flow text into columns
     * @param $options array with option for gaps and number of columns - default: array('gap'=>10, 'num'=>2)
     */
    /*public function ezColumnsStart($options=array()){
        // start from the current y-position, make the set number of columne
        if (isset($this->ez['columns']) && $this->ez['columns']==1){
            // if we are already in a column mode then just return.
            return;
        }
        $def=array('gap'=>10,'num'=>2);
        foreach ($def as $k=>$v){
            if (!isset($options[$k])){
                $options[$k]=$v;
            }
        }
        // setup the columns
        $this->ez['columns']=array('on'=>1,'colNum'=>1);

        // store the current margins
        $this->ez['columns']['margins']=array(
            $this->ez['leftMargin'],
            $this->ez['rightMargin'],
            $this->ez['topMargin'],
            $this->ez['bottomMargin']
        );
        // and store the settings for the columns
        $this->ez['columns']['options']=$options;
        // then reset the margins to suit the new columns
        // safe enough to assume the first column here, but start from the current y-position
        $this->ez['topMargin']=$this->ez['pageHeight']-$this->y;
        $width=($this->ez['pageWidth']-$this->ez['leftMargin']-$this->ez['rightMargin']-($options['num']-1)*$options['gap'])/$options['num'];
        $this->ez['columns']['width']=$width;
        $this->ez['rightMargin']=$this->ez['pageWidth']-$this->ez['leftMargin']-$width;

    }*/

    /**
     * stops the multi column mode
     */
    /*public function ezColumnsStop(){
        if (isset($this->ez['columns']) && $this->ez['columns']['on']==1){
            $this->ez['columns']['on']=0;
            $this->ez['leftMargin']=$this->ez['columns']['margins'][0];
            $this->ez['rightMargin']=$this->ez['columns']['margins'][1];
            $this->ez['topMargin']=$this->ez['columns']['margins'][2];
            $this->ez['bottomMargin']=$this->ez['columns']['margins'][3];
        }
    }*/

    /**
     * puts the document into insert mode. new pages are inserted until this is re-called with status=0
     * by default pages will be inserted at the start of the document
     *
     * @param $status
     * @param $pageNum
     * @param $pos
     */
    /*public function ezInsertMode($status=1,$pageNum=1,$pos='before'){
        switch($status){
            case '1':
                if (isset($this->ezPages[$pageNum])){
                    $this->ez['insertMode']=1;
                    $this->ez['insertOptions']=array('id'=>$this->ezPages[$pageNum],'pos'=>$pos);
                }
                break;
            case '0':
                $this->ez['insertMode']=0;
                break;
        }
    }*/

    /**
     * sets the Y position of the document.
     * If Y reaches the bottom margin a new page is generated
     * @param float $y Y position
     */
    public function ezSetY($y){
        // used to change the vertical position of the writing point.
        $this->y = $y;
        if ( $this->y < $this->ez['bottomMargin']){
            // then make a new page
            $this->ezNewPage();
        }
    }

    /**
     * changes the Y position of the document by writing positive or negative numbers.
     * If Y reaches the bottom margin a new page is generated
     * @param $dy
     * @param $mod
     */
    public function ezSetDy($dy,$mod=''){
        // used to change the vertical position of the writing point.
        // changes up by a positive increment, so enter a negative number to go
        // down the page
        // if $mod is set to 'makeSpace' and a new page is forced, then the pointed will be moved
        // down on the new page, this will allow space to be reserved for graphics etc.
        $this->y += $dy;
        if ( $this->y < $this->ez['bottomMargin']){
            // then make a new page
            $this->ezNewPage();
            if ($mod=='makeSpace'){
                $this->y += $dy;
            }
        }
    }

    /**
     * put page numbers on the pages from here.
     * place then on the 'pos' side of the coordinates (x,y).
     * use the given 'pattern' for display, where (PAGENUM} and {TOTALPAGENUM} are replaced
     * as required.
     * Adjust this function so that each time you 'start' page numbers then you effectively start a different batch
     * return the number of the batch, so that they can be stopped in a different order if required.
     *
     * @param float $x X-coordinate
     * @param float $y Y-coordinate
     * @param $size
     * @param string $pos use either right or left
     * @param string $pattern pattern where {PAGENUM} is the current page number and {TOTALPAGENUM} is the page count in total
     * @param int $num optional. make the first page this number, the number of total pages will be adjusted to account for this.
     *
     * @return int count of ez['pageNumbering']
     */
    /*public function ezStartPageNumbers($x,$y,$size,$pos='left',$pattern='{PAGENUM} of {TOTALPAGENUM}',$num=''){
        if (!$pos || !strlen($pos)){
            $pos='left';
        }
        if (!$pattern || !strlen($pattern)){
            $pattern='{PAGENUM} of {TOTALPAGENUM}';
        }
        if (!isset($this->ez['pageNumbering'])){
            $this->ez['pageNumbering']=array();
        }
        $i = count($this->ez['pageNumbering']);
        $this->ez['pageNumbering'][$i][$this->ezPageCount]=array('x'=>$x,'y'=>$y,'pos'=>$pos,'pattern'=>$pattern,'num'=>$num,'size'=>$size);
        return $i;
    }*/

    /**
     * returns the number of a page within the specified page numbering system
     * @param $pageNum
     * @param $i
     * @return int page number
     */
    /*public function ezWhatPageNumber($pageNum,$i=0){
    	return $this->CURPAGE->PageNum;
        // given a particular generic page number (ie, document numbered sequentially from beginning),
        // return the page number under a particular page numbering scheme ($i)
        $num=0;
        $start=1;
        $startNum=1;
        if (!isset($this->ez['pageNumbering'])) {
            $this->addMessage('WARNING: page numbering called for and wasn\'t started with ezStartPageNumbers');
            return 0;
        }
        foreach ($this->ez['pageNumbering'][$i] as $k=>$v){
            if ($k<=$pageNum){
                if (is_array($v)){
                    // start block
                    if (strlen($v['num'])){
                        // a start was specified
                        $start=$v['num'];
                        $startNum=$k;
                        $num=$pageNum-$startNum+$start;
                    }
                } else {
                    // stop block
                    $num=0;
                }
            }
        }
        return $num;
    }*/
    
    /**
     * receive the current page number
     * @return int page number
     */
    public function ezGetCurrentPageNumber(){
        return $this->CURPAGE->PageNum;
    }

    /**
     * stops the custom page numbering
     * @param $stopTotal
     * @param $next
     * @param $i
     */
    /*public function ezStopPageNumbers($stopTotal=0,$next=0,$i=0){
        // if stopTotal=1 then the totalling of pages for this number will stop too
        // if $next=1, then do this page, but not the next, else do not do this page either
        // if $i is set, then stop that particular pagenumbering sequence.
        if (!isset($this->ez['pageNumbering'])){
            $this->ez['pageNumbering']=array();
        }
        if ($next && isset($this->ez['pageNumbering'][$i][$this->ezPageCount]) && is_array($this->ez['pageNumbering'][$i][$this->ezPageCount])){
            // then this has only just been started, this will over-write the start, and nothing will appear
            // add a special command to the start block, telling it to stop as well
            if ($stopTotal){
                $this->ez['pageNumbering'][$i][$this->ezPageCount]['stoptn']=1;
            } else {
                $this->ez['pageNumbering'][$i][$this->ezPageCount]['stopn']=1;
            }
        } else {
            if ($stopTotal){
                $this->ez['pageNumbering'][$i][$this->ezPageCount]='stopt';
            } else {
                $this->ez['pageNumbering'][$i][$this->ezPageCount]='stop';
            }
            if ($next){
                $this->ez['pageNumbering'][$i][$this->ezPageCount].='n';
            }
        }
    }*/

    /**
     * calculate the maximum width, taking into account until text may be broken
     *
     * @param $size
     * @param $text
     * @return float text width
     */
    /*public function ezGetTextWidth($size,$text){
        $mx=0;
        //$lines = explode("\n",$text);
        $lines = preg_split("[\r\n|\r|\n]",$text);
        foreach ($lines as $line){
            $w = $this->getTextWidth($size,$line);
            if ($w>$mx){
                $mx=$w;
            }
        }
        return $mx;
    }*/

    /**
     *  add a table of information to the pdf document
     *
     * **$options**
     * <pre>
     * 'showHeadings' => 0 or 1
     * 'shaded'=> 0,1,2,3 default is 1 (1->alternate lines are shaded, 0->no shading, 2-> both shaded, second uses shadeCol2)
     * 'showBgCol'=> 0,1 default is 0 (1->active bg color column setting. if is set to 1, bgcolor attribute ca be used in 'cols' 0->no active bg color columns setting)
     * 'shadeCol' => (r,g,b) array, defining the colour of the shading, default is (0.8,0.8,0.8)
     * 'shadeCol2' => (r,g,b) array, defining the colour of the shading of the other blocks, default is (0.7,0.7,0.7)
     * 'fontSize' => 10
     * 'textCol' => (r,g,b) array, text colour
     * 'titleFontSize' => 12
     * 'rowGap' => 2 , the space added at the top and bottom of each row, between the text and the lines
     * 'colGap' => 5 , the space on the left and right sides of each cell
     * 'lineCol' => (r,g,b) array, defining the colour of the lines, default, black.
     * 'xPos' => 'left','right','center','centre',or coordinate, reference coordinate in the x-direction
     * 'xOrientation' => 'left','right','center','centre', position of the table w.r.t 'xPos'
     * 'width'=> <number> which will specify the width of the table, if it turns out to not be this wide, then it will stretch the table to fit, if it is wider then each cell will be made proportionalty smaller, and the content may have to wrap.
     * 'maxWidth'=> <number> similar to 'width', but will only make table smaller than it wants to be
     * 'cols' => array(<colname>=>array('justification'=>'left','width'=>100,'link'=>linkDataName,'bgcolor'=>array(r,g,b) ),<colname>=>....) allow the setting of other paramaters for the individual columns
     * 'minRowSpace'=> the minimum space between the bottom of each row and the bottom margin, in which a new row will be started if it is less, then a new page would be started, default=-100
     * 'innerLineThickness'=>1
     * 'outerLineThickness'=>1
     * 'splitRows'=>0, 0 or 1, whether or not to allow the rows to be split across page boundaries
     * 'protectRows'=>number, the number of rows to hold with the heading on page, ie, if there less than this number of rows on the page, then move the whole lot onto the next page, default=1
     * 'nextPageY'=> true or false (eg. 0 or 1) Sets the Y Postion of the Table of a newPage to current Table Postion
     * </pre>
     *
     * **since 0.12-rc9** added heading shade.
     * <pre>
     * 'shadeHeadingCol'=>(r,g,b) array, defining the colour of the backgound of headings, default is transparent (empty array)
     * </pre>
     * 
     * **since 0.12-rc11** applied patch #19 align all header columns at once
     * <pre>
     * 'gridlines'=> EZ_GRIDLINE_* default is EZ_GRIDLINE_DEFAULT, overrides 'showLines' to provide finer control
     * 'alignHeadings' => 'left','right','center'
     * </pre>
     *
     * **deprecated in 0.12-rc11** 
     * <pre>'showLines' in $options - use 'gridline' instead</pre>
     *
     * Note that the user will have had to make a font selection already or this will not // produce a valid pdf file.
     *
     * **Example**
     *
     * <pre>
     * $data = array(
     *    array('num'=>1,'name'=>'gandalf','type'=>'wizard')
     *   ,array('num'=>2,'name'=>'bilbo','type'=>'hobbit','url'=>'http://www.ros.co.nz/pdf/')
     *   ,array('num'=>3,'name'=>'frodo','type'=>'hobbit')
     *   ,array('num'=>4,'name'=>'saruman','type'=>'bad dude','url'=>'http://sourceforge.net/projects/pdf-php')
     *   ,array('num'=>5,'name'=>'sauron','type'=>'really bad dude')
     *   );
     * $pdf->ezTable($data);
     * </pre>
     *
     * @param array $data the data to fill the table cells as a two dimensional array
     * @param array $cols (optional) is an associative array, the keys are the names of the columns from $data to be presented (and in that order), the values are the titles to be given to the columns
     * @param string $title (optional) is the title to be put on the top of the table
     * @param array $options all possible options, see description above
     * @return float the actual y position
     */
    public function ezTable(&$data,$cols='',$title='',$options=''){
        if (!is_array($data)){
            return;
        }
		
		$defaults = array('shaded'=>1,'showBgCol'=>0,'shadeCol'=>array(0.8,0.8,0.8),'shadeCol2'=>array(0.7,0.7,0.7),'fontSize'=>10,'titleFontSize'=>12,
        'titleGap'=>5,'lineCol'=>array(0,0,0),'gap'=>5,'xPos'=>'centre','xOrientation'=>'centre',
        'showHeadings'=>1,'textCol'=>array(0,0,0),'width'=>0,'maxWidth'=>0,'cols'=>array(),'minRowSpace'=>-100,'rowGap'=>2,'colGap'=>5,
        'innerLineThickness'=>1,'outerLineThickness'=>1,'splitRows'=>0,'protectRows'=>1,'nextPageY'=>0,
        'shadeHeadingCol'=>array(), 'gridlines' => EZ_GRIDLINE_DEFAULT
        );

        foreach ($defaults as $key=>$value){
            if (is_array($value)){
                if (!isset($options[$key]) || !is_array($options[$key])){
                    $options[$key]=$value;
                }
            } else {
                if (!isset($options[$key])){
                    $options[$key]=$value;
                }
            }
        }
		
		reset($data);
		$numColumns = 1;
		
		if(is_array($cols) && count($cols) > 0){
			$numColumns = count($cols);
		} else {
			$cols = array();
			$firstRow = current($data);
			foreach($firstRow as $k => $v){
				$cols[$k] = $k;
			}
			
			$numColumns = count($firstRow);
		}
		
		$bbox = $this->CURPAGE->Bleedbox;
		$width = $bbox[2] - $bbox[0];
		
		if($options['width'] > 0 && $options['width'] <= $width){
			$changeBBox = array();
			
			//$width = $options['width'];
			
			$changeBBox['ux'] = $options['width'] + $bbox[0]; 
			
			// only set orientation when smaller width is set
			switch($options['xOrientation']){
				case 'right':
					$changeBBox['addlx'] = $width - $options['width'];
					$changeBBox['addux'] = $width - $options['width'];
					break;
				case 'center':
				case 'centre':
					$changeBBox['addlx'] = ($width - $options['width']) / 2;
					$changeBBox['addux'] = ($width - $options['width']) / 2;
					break;
			}
			Cpdf_Common::SetBBox($changeBBox, $bbox);
		}
		
		if(!empty($title)){
			$this->ezText($title, $options['titleFontSize'], array('justification' => 'center'));
			$h = $this->ezAppearance->GetFontHeight();
			Cpdf_Common::SetBBox(array('adduy' => -$h), $bbox);
		}
		
		$ls = new Cpdf_LineStyle(1, 'butt', 'miter');
		$this->ezTable = $this->NewTable($bbox, $numColumns, null, $ls, $options['gridlines']);
		
		
		
		if($options['fontSize'] > 0){
			$font = (empty($this->ez['fontName']))?'Helvetica':$this->ez['fontName'];
			$this->ezTable->SetFont($font, $options['fontSize']);
			$this->ez['fontSize'] = $options['fontSize'];
		}
			
		// apply header row
		if($options['showHeadings']){
			foreach($cols as $k => $v){
				$bg = null;
				$justify = null;
				if(isset($options['cols'][$k])){
					$coption = &$options['cols'][$k];
					
					$bg = (isset($coption['bgcolor']))?$coption['bgcolor']:null;
					$justify =  (isset($coption['justification']))?$coption['justification']:null;
				}
				
				if(isset($options['showBgCol']) && $options['showBgCol'] > 0){
					$bg = $options['shadeHeadingCol'];
				}
					
				
				$this->ezTable->AddCell($v, $justify, $bg);
			}
		}
		
		$i = 1;
		foreach($data as $field){
			foreach($field as $k => $v){
				// display only the columns shich are defined in cols
				if(isset($cols) && !isset($cols[$k])) continue;
				
				$bg = null;
				$justify = null;
				if(isset($options['cols'][$k])){
					$coption = &$options['cols'][$k];
					
					$bg = (isset($coption['bgcolor']))?$coption['bgcolor']:null;
					$justify =  (isset($coption['justification']))?$coption['justification']:null;
				}
				
				if(!$bg && $options['shaded'] > 0){
					if(!($i % 2))
						$bg = $options['shadeCol'];
					if($options['shaded'] > 1 && ($i % 2))
						$bg = $options['shadeCol2'];
						
				}
				$this->ezTable->AddCell($v, $justify, $bg);
			}
			$i++;
		}
		
		// required to display table border and background color
		$this->ezTable->EndTable();
		
		return;
    }

	public function openHere($loc = 'Fit'){
		$this->Options->OpenAction($this->CURPAGE, $loc);
		// dummy
	}

	public function selectFont($fontName){
		$this->ez['fontName'] = $fontName;
	}
	
	public function getFontHeight(){
		return $this->ezAppearance->GetFontHeight();
	}
	
	public function getFontDecender(){
		return $this->ezAppearance->GetFontDescender();
	}
	
	public function getTextWidth($size, $text){
		return $this->ezAppearance->GetTextWidth($text, $size);
	}
	
	
	

    /**
     * this will add a string of text to the document, starting at the current drawing
     * position.
     * it will wrap to keep within the margins, including optional offsets from the left
     * and the right, if $size is not specified, then it will be the last one used, or
     * the default value (12 I think).
     * the text will go to the start of the next line when a return code "\n" is found.
     * possible options are:
     *
     * 'left'=> number, gap to leave from the left margin<br>
     * 'right'=> number, gap to leave from the right margin<br>
     * 'aleft'=> number, absolute left position (overrides 'left')<br>
     * 'aright'=> number, absolute right position (overrides 'right')<br>
     * 'justification' => 'left','right','center','centre','full'<br>
     *
     * only set one of the next two items (leading overrides spacing)<br>
     * 'leading' => number, defines the total height taken by the line, independent of the font height.<br>
     * 'spacing' => a real number, though usually set to one of 1, 1.5, 2 (line spacing as used in word processing)<br>
     *
     * if $test is set then this should just check if the text is going to flow onto a new page or not, returning true or false
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezText('This is a text string\nplus next line', 12, array('justification'=> 'center'));
     * </pre>
     * 
     * @param string $text text string
     * @param float $size font size
     * @param array $options options from above
     * @param bool $test is this test output only (to check if it fit to the page for instance)
     * @return float|bool Y position or true/false if $test parameter is set
     */
    public function ezText($text,$size=0,$options=array()){
    	if(!isset($this->ezAppearance) || $this->ezAppearance->page !== $this->CURPAGE){
    		$this->ezAppearance = $this->NewAppearance();
		}
        
        $margin = array();
        if(isset($options['left']))
            $margin['addlx'] = $options['left'];
        if(isset($options['right']))
            $margin['addux'] = $options['right'];
        
        if(isset($options['aleft']))
            $margin['lx'] = $options['aleft'];
        if(isset($options['aright']))
            $margin['ux'] = $options['aright'];
        
        $tmp = $this->ezAppearance->GetBBox();
        
        $this->ezAppearance->UpdateBBox($margin);
		
		if(!isset($options['justification']))
			$options['justification'] = 'left';
		
		if($options['justification'] == 'centre')
			$options['justification'] = 'center';
		
		if(!isset($options['spacing']))
			$options['spacing'] = 0;
		
		if(isset($this->ezTable) && $this->ezTable->y <= $this->ezTable->y){
			$this->ezAppearance->y = $this->ezTable->y - $this->ezAppearance->GetFontDescender();
			$this->ezAppearance->y -= $this->ezAppearance->GetFontHeight();
		}
		
		if($size <= 0)
			$size = 10;
		
		$this->ezAppearance->SetFont($this->ez['fontName'], $size);
		$this->ez['fontSize'] = $size;
			
		$this->ezAppearance->AddText($text, 0, $options['justification'], $options['spacing']);
		$this->ezAppearance->UpdateBBox($tmp);
    }

    /**
     * Used to display images
     * supported images are:
     *  - JPEG
     *  - PNG (transparent)
     *  - GIF (but internally converted into JPEG)
     *
     * **Example**<br>
     * <pre>
     * $pdf->ezImage('file.jpg', 5, 100, 'full', 'right', array('color'=> array(0.2, 0.4, 0.4), 'width'=> 2, 'cap'=>'round'));
     * </pre>
     * 
     * @param string $image image file or url path
     * @param float $pad image padding
     * @param float $width max width
     * @param $resize
     * @param string $just justification of the image ('left', 'right', 'center')
     * @param array $border border array - see example 
     */
    public function ezImage($image, $pad = 5, $width = 0, $resize = 'full', $just = 'center', $border = '') {
		$w = null;
		if($resize == 'full'){
			$w = '15%';
		}
		
		$this->ezAppearance->AddImage($just, $this->ezAppearance->y, $image, $w);
    }

    /**
     * Output the PDF content as stream
     * 
     * $options
     *
     * 'compress' => 0/1 to enable compression. For compression level please use $this->options['compression'] = <level> at the very first point. Default: 1<br>
     * 'download' => 0/1 to display inline (in browser) or as download. Default: 0<br>
	 * 'filename' => 'output.pdf' pdf file name when user is downloading the content
     *
     * @param array $options options array from above
     */
    public function ezStream($options = array()){
    	if(isset($options['compress']))
    		$this->Compression = $options['compress'];
		
		if(!isset($options['filename']))
			$options['filename'] = "ezoutput.pdf";
    	$this->Stream($options['filename']);
    }

    /**
     * return the pdf output as string
     *
     * @param bool $debug uncompressed output for debugging purposes
     * @return string pdf document
     */
    public function ezOutput($debug = FALSE){
    	if($debug == TRUE)
			$this->Compression = 0;
    	return $this->OutputAll();
    }
}
?>