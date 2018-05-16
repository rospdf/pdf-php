<?php
/*
    TTFdump.php: TrueType font file dump as PNG and PDF
    Copyright (C) 2012 Thanos Efraimidis (4real.gr)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

require_once('TTF.php');

$ttfFileName = $argv[1];
$fontName = $argv[2];

//echo "Dumping PNG...\n";
//TTFdump::dumpPNG($ttfFileName, $fontName . '.png');
echo "Dumping PDF...\n";
TTFdump::dumpPDF($fontName, $ttfFileName, $fontName . '.pdf');

class TTFdump {
    static function dumpPNG($ttfFileName, $pngFileName) {
	putenv('GDFONTPATH=' . realpath('.'));

	// Open an parse the TTF file
	$ttf = new TTF(file_get_contents($ttfFileName));

	// Unmarshal 'head' table, collect 'indexToLocFormat' and 'unitsPerEm'
	$head = $ttf->unmarshalHead();
	$indexToLocFormat = $head['indexToLocFormat'];
	$unitsPerEm = $head['unitsPerEm'];
	
	// Unmarshal 'maxp' table, collect 'numGlyphs'
	$maxp = $ttf->unmarshalMaxp();
	$numGlyphs = $maxp['numGlyphs'];
	
	// Unmarshal 'loca' table
	$loca = $ttf->unmarshalLoca($indexToLocFormat, $numGlyphs);
	
	// Unmarshal 'glyf' table
	$glyf = $ttf->unmarshalGlyf($loca);
	
	// Unmarshal 'cmap' table, collect unicode encoding table
	$cmap = $ttf->unmarshalCmap();
	$unicodeTable = $ttf->getEncodingTable($cmap, 3, 1); // (3,1) stands for Unicode
	
	// Collect startCount and endCount arrays
	$startCountArray = $unicodeTable['startCountArray'];
	$endCountArray = $unicodeTable['endCountArray'];
	
	// Set num equal to number of mapped characters
	$count = count($startCountArray);
	$num = 0;
	for ($i = 0; $i < $count; $i++) {
	    $start = $startCountArray[$i];
	    $end = $endCountArray[$i];
	    if ($end != 65535) {
		$num += $end - $start + 1;
	    }
	}

	$WHITE = 0xffffff; // White color
	$BLACK = 0x000000; // Black color
	$FONT_SIZE = 32.0;
	$ANGLE = 0.0;

	$CELL_SIZE = 96; // Pixels per cell
	$WIDTH = 8; // Number of characters per row
	$HEIGHT = floor(($num + 7) / 8); // Number of rows

	// Create the image
	$im = imagecreatetruecolor($WIDTH * $CELL_SIZE + 1, $HEIGHT * $CELL_SIZE + 1);
	imagefill($im, 0, 0, $WHITE);
	// Draw the grid
	for ($r = 0; $r < $HEIGHT + 1; $r++) {
	    imageline($im, 0, $r * $CELL_SIZE, $WIDTH * $CELL_SIZE, $r * $CELL_SIZE, $BLACK);
	}
	for ($c = 0; $c < $WIDTH + 1; $c++) {
	    imageline($im, $c * $CELL_SIZE, 0, $c * $CELL_SIZE, $HEIGHT * $CELL_SIZE, $BLACK);
	}
	
	// Draw the characters
	$x = $y = 0;
	for ($i = 0; $i < $count; $i++) {
	    $start = $startCountArray[$i];
	    $end = $endCountArray[$i];
	    if ($end != 65535) {
		for ($ch = $start; $ch <= $end; $ch++) {
		    // For character 'ch', calculate the width and height
		    $index = $ttf->characterToIndex($unicodeTable, $ch);
		    $g = $glyf[$index];
		    if (strlen($g) > 0) {
			$description = $ttf->getGlyph($g);
			$charWidth = intval($description['xMax'] - $description['xMin']);
			$charHeight = intval($description['yMax'] - $description['yMin']);
		    } else {
			$charWidth = $charHeight = 0;
		    }
		    
		    // Draw the character (horizontally centered
		    imagettftext($im, $FONT_SIZE, $ANGLE, $x + ($CELL_SIZE - $FONT_SIZE * $charWidth / $unitsPerEm) / 2, $y + $CELL_SIZE * 2 / 3, $BLACK, $ttfFileName, sprintf('&#%d;', $ch));
		    
		    // Advance x,y
		    $x += $CELL_SIZE;
		    if ($x >= $WIDTH * $CELL_SIZE) {
			$x = 0;
			$y += $CELL_SIZE;
		    }
		}
	    }
	}
	// Flush the image
	imagepng($im, $pngFileName);
    }

    // Constants for PDF dump
    const MARGIN_LEFT = 72; // One inch
    const MARGIN_RIGHT = 72;
    const MARGIN_TOP = 72;
    const MARGIN_BOTTOM = 72;
    const PAGE_WIDTH = 595; // For A4 paper
    const PAGE_HEIGHT = 842; // For A4 paper

    const CELLS_PER_ROW = 8;
    const FONT_SIZE = 36;

    static function dumpPDF($fontName, $ttfFileName, $pdfFileName) {
	$objects = array(); // Hold the PDF objects
	$objectNumber = 1;

	$font = self::initFont($fontName, $ttfFileName);

	$numGlyphs = $font['maxp']['numGlyphs'];
	$cellWidth = floor((self::PAGE_WIDTH - self::MARGIN_LEFT - self::MARGIN_RIGHT) / self::CELLS_PER_ROW);
	$cellHeight = $cellWidth;
	$rowsPerPage = floor((self::PAGE_HEIGHT - self::MARGIN_TOP - self::MARGIN_BOTTOM) / $cellHeight);
	$cellsPerPage = self::CELLS_PER_ROW * $rowsPerPage;

	$numPages = intval(ceil($numGlyphs / $cellsPerPage));
	
	// Collect object numbers
	$catalog = $objectNumber++;
	$info = $objectNumber++;
	$pages = $objectNumber++;
	$resources = $objectNumber++;
	$font['objectNumber'] = $objectNumber++;
	$font['fontName'] = $fontName; //XXX - This can be collected from the TTF
	$helveticaObjectNumber = $objectNumber++;
	$page0 = $objectNumber;
	$objectNumber += $numPages;
	$contents0 = $objectNumber;
	$objectNumber += $numPages;

	// Catalog object
	$sb = sprintf("<</Type/Catalog/Pages %d 0 R>>", $pages);
	$objects[] = array('num' => $catalog, 'data' => $sb);

	// Info object
	$sb = sprintf("<</Title(%s)/Author(4real.gr)>>", $fontName);
	$objects[] = array('num' => $info, 'data' => $sb);

	// Resources object
	$sb = sprintf("<</ProcSet[/PDF/Text]/Font<</F0 %d 0 R/F1 %d 0 R>>>>", $font['objectNumber'], $helveticaObjectNumber);
	$objects[] = array('num' => $resources, 'data' => $sb);

	// Pages object
	$sb = sprintf("<</Type/Pages/Kids[%s]/Count %d>>", self::dumpPageReferences($page0, $numPages), $numPages);
	$objects[] = array('num' => $pages, 'data' => $sb);

	for ($i = 0; $i < $numPages; $i++) {
	    // Page object (for i-th page)
	    $sb = sprintf("<</Type/Page/Parent %d 0 R/MediaBox[0 0 595 842]/Resources %d 0 R/Contents %d 0 R>>", $pages, $resources, $contents0 + $i);
	    $objects[] = array('num' => $page0 + $i, 'data' => $sb);

	    // Contents object (for i-th page)
	    $stream = self::constructContents($font, $i, $cellWidth, $cellHeight, self::CELLS_PER_ROW, $rowsPerPage);
	    $sb = sprintf("<</Filter/FlateDecode/Length %d>> stream\n%s\nendstream", strlen($stream), $stream);
	    $objects[] = array('num' => $contents0 + $i, 'data' => $sb);
	}

	// Font object(s)
	self::embedType0Font($font, $objects, $objectNumber);
	// Helvetica font object
	$sb = "<</BaseFont/Helvetica/Subtype/Type1/Type/Font/Encoding/WinAnsiEncoding>>";
	$objects[] = array('num' => $helveticaObjectNumber, 'data' => $sb);

	// Create the PDF
	$pdf = '';
	// Dump the prologue
	$pdf .= "%PDF-1.7\n";
	$pdf .= "%\342\342\342\342\n";
	// Dump the objects
	for ($num = 0; $num < count($objects); $num++) {
	    $objects[$num]['offset'] = strlen($pdf);
	    $pdf .= sprintf("%d 0 obj\n%s\nendobj\n", $objects[$num]['num'], $objects[$num]['data']);
	}
	// Dump the xref
	$startXref = strlen($pdf);
	$pdf .= "xref\n";
	$pdf .= sprintf("%d %d\n", 0, count($objects) + 1);
	$pdf .= sprintf("0000000000 65535 f \n");
	for ($num = 0; $num < count($objects); $num++) {
	    if (($object = self::locateObject($objects, $num + 1)) !== null) {
		$pdf .= sprintf("%010d %05d n \n", $object['offset'], 0);
	    }
	}
	// Dump the trailer
	$pdf .= "trailer\n";
	$pdf .= sprintf("<</Size %d/Root %d 0 R/Info %d 0 R/ID[<5cd30e78323ed62aaf235ccafebabe5c> <5cd30e78323ed62aaf235ccafebabe5c>]>>\n",
			count($objects) + 1, $catalog, $info); //XXXX something random here
	// Dump the epilogue
	$pdf .= "startxref\n";
	$pdf .= sprintf("%d\n", $startXref);
	$pdf .= "%%EOF\n";

	file_put_contents($pdfFileName, $pdf);
    }

    private function initFont($fontName, $fontFile) {
	$ret = array();

	$fileContents = file_get_contents($fontFile);
	
	$ttf = new TTF($fileContents);
	
	// Unmarshal tables
	$head = $ttf->unmarshalHead();
	$unitsPerEm = $head['unitsPerEm'];
	$indexToLocFormat = $head['indexToLocFormat'];

	$hhea = $ttf->unmarshalHhea();
	$numberOfHMetrics = $hhea['numberOfHMetrics'];
	
	$maxp = $ttf->unmarshalMaxp();
	$numGlyphs = $maxp['numGlyphs'];

	$cmap = $ttf->unmarshalCmap();

	$loca = $ttf->unmarshalLoca($indexToLocFormat, $numGlyphs);
	$glyf = $ttf->unmarshalGlyf($loca);

	$hmtx = $ttf->unmarshalHmtx($numberOfHMetrics, $numGlyphs);

	// Save unmarshalled tables
	$ret['head'] = $head;
	$ret['hhea'] = $hhea;
	$ret['maxp'] = $maxp;
	$ret['cmap'] = $cmap;
	$ret['loca'] = $loca;
	$ret['glyf'] = $glyf;
	$ret['hmtx'] = $hmtx;

	$ret['xMin'] = round($head['xMin'] * 1000 / $unitsPerEm);
	$ret['yMin'] = round($head['yMin'] * 1000 / $unitsPerEm);
	$ret['xMax'] = round($head['xMax'] * 1000 / $unitsPerEm);
	$ret['yMax'] = round($head['yMax'] * 1000 / $unitsPerEm);
	$ret['ascent'] = round($hhea['ascender'] * 1000 / $unitsPerEm);
	$ret['descent'] = round($hhea['descender'] * 1000 / $unitsPerEm);
	$ret['capHeight'] = TTF2PDFUtils::calculateCapHeight($cmap, $glyf, $unitsPerEm);

	$widths = TTF2PDFUtils::constructW($hmtx, $unitsPerEm);
	$ret['advanceWidths'] = $widths['advanceWidths'];
	$ret['w'] = $widths['w'];

	$maps = TTF2PDFUtils::constructCidGidMaps($cmap);
	$ret['CIDToGIDMap'] = $maps['CIDToGIDMap'];
	$ret['GIDToCIDMap'] = $maps['GIDToCIDMap'];
	$ret['ToUnicode'] = TTF2PDFUtils::constructToUnicode($maps['GIDToCIDMap']);
	$ret['fileContents'] = $fileContents;
	return $ret;
    }

    private function embedType0Font($font, &$objects, &$objectNumber) {
	$fontObjectNumber = $font['objectNumber'];

	$cidFontObjectNumber = $objectNumber++;
	$toUnicodeObjectNumber = $objectNumber++;
	$cidSystemInfoObjectNumber = $objectNumber++;
	$fontDescriptorObjectNumber = $objectNumber++;
	$wObjectNumber = $objectNumber++;
	$fontFile2ObjectNumber = $objectNumber++;

	// Type0 font
	$sb = sprintf("<</Type/Font/Subtype/Type0/BaseFont/%s/Encoding/Identity-H/DescendantFonts [%d 0 R]/ToUnicode %d 0 R>>",
		      $font['fontName'],
		      $cidFontObjectNumber,
		      $toUnicodeObjectNumber);
	$objects[] = array('num' => $fontObjectNumber, 'data' => $sb);

	// Dump CIDfont
	$sb = sprintf("<</BaseFont/%s/Subtype/CIDFontType2/Type/Font/CIDToGIDMap/Identity/CIDSystemInfo %d 0 R/FontDescriptor %d 0 R/W %d 0 R>>",
		      $font['fontName'],
		      $cidSystemInfoObjectNumber,
		      $fontDescriptorObjectNumber,
		      $wObjectNumber);
	$objects[] = array('num' => $cidFontObjectNumber, 'data' => $sb);

	// ToUnicode
	$uncompressed = $font['ToUnicode'];
	$compressed = gzcompress($uncompressed);
	$sb = '';
	$sb .= sprintf("<</Filter/FlateDecode/Length %d>>\n", strlen($compressed));
	$sb .= sprintf("stream\n");
	$sb .= $compressed;
	$sb .= sprintf("\n");
	$sb .= sprintf("endstream");
	$objects[] = array('num' => $toUnicodeObjectNumber, 'data' => $sb);

	// CIDSystemInfo
	$sb = sprintf("<</Ordering(Identity) /Registry(Adobe) /Supplement 0>>\n");
	$objects[] = array('num' => $cidSystemInfoObjectNumber, 'data' => $sb);

	// FontDescriptor
	$sb = sprintf("<</Type/FontDescriptor/FontName/%s/Flags %d/FontBBox [%d %d %d %d]/ItalicAngle %d/Ascent %d/Descent %d/CapHeight %d/Stemv %d/FontFile2 %d 0 R>>",
		      $font['fontName'],
		      32, // Flags (NonSymbolic)
		      $font['xMin'],
		      $font['yMin'],
		      $font['xMax'],
		      $font['yMax'],
		      0,
		      $font['ascent'],
		      $font['descent'],
		      $font['capHeight'],
		      50, // StemV - this is hardcoded
		      $fontFile2ObjectNumber);
	$objects[] = array('num' => $fontDescriptorObjectNumber, 'data' => $sb);

	// W
	$sb = sprintf("%s\n", $font['w']);
	$objects[] = array('num' => $wObjectNumber, 'data' => $sb);

	// FontFile2
	$uncompressed = $font['fileContents'];
	$compressed = gzcompress($uncompressed);
	$sb = '';
	$sb .= sprintf("<</Filter/FlateDecode/Length %d/Length1 %d>>\n", strlen($compressed), strlen($uncompressed));
	$sb .= sprintf("stream\n");
	$sb .= $compressed;
	$sb .= sprintf("\n");
	$sb .= sprintf("endstream");
	$objects[] = array('num' => $fontFile2ObjectNumber, 'data' => $sb);
    }

    private function dumpPageReferences($page0, $numPages) {
	$sb = '';
	for ($i = 0; $i < $numPages; $i++) {
	    $sb .= sprintf(' %d 0 R', $page0 + $i);
	}
	return substr($sb, 1); // Remove first space
    }

    private function constructContents($font, $pgNo, $cellWidth, $cellHeight, $cellsPerRow, $rowsPerPage) {
	$numGlyphs = $font['maxp']['numGlyphs'];
	$unitsPerEm = $font['head']['unitsPerEm'];

	// To display the Unicode value (if available)
	$unicodeEncodingTable = TTF::getEncodingTable($font['cmap'], 3, 1);

	$sb = '';
	
	$sb .= "1 J 0 j\n"; // Set line join and line cap style
	$sb .= "0.6 w\n"; // Set line width

	// gid0/gid1 will be first/last+1 glyphID to draw
	$gid0 = $pgNo * $cellsPerRow * $rowsPerPage;
	$gid1 = ($pgNo + 1) * $cellsPerRow * $rowsPerPage;
	if ($gid1 > $numGlyphs) {
	    $gid1 = $numGlyphs;
	}
	// Number of rows in this page
	$rows = intval(ceil(($gid1 - $gid0) / $cellsPerRow));
	
	// Upper left and lower right corners
	$x0 = (self::PAGE_WIDTH - $cellWidth * $cellsPerRow) / 2;
	$y0 = self::PAGE_HEIGHT - self::MARGIN_TOP;
	$x1 = $x0 + $cellsPerRow * $cellWidth;
	$y1 = $y0 - $rows * $cellHeight;

	// Draw the horizontal lines
	for ($r = 0; $r < $rows + 1; $r++) {
	    $y = $y0 - $r * $cellHeight;
	    $sb .= sprintf("%d %d m %d %d l\n", $x0, $y, $x1, $y);
	}
	// Draw the vertical lines
	for ($c = 0; $c < $cellsPerRow + 1; $c++) {
	    $x = $x0 + $c * $cellWidth;
	    $sb .= sprintf("%d %d m %d %d l\n", $x, $y0, $x, $y1);
	}
	// Stroke the path
	$sb .= "S\n";

	// Draw the glyphs
	$sb .= "BT\n"; // Begin text object

	$x = $x0;
	$y = self::PAGE_HEIGHT - self::MARGIN_TOP - 2 * $cellHeight / 3;

	for ($gid = $gid0; $gid < $gid1; $gid++) {
	    $description = $font['glyf'][$gid];
	    if (strlen($description) == 0) {
		$glyphWidth = 0;
	    } else {
		$glyph = TTF::getGlyph($description);
		$glyphWidth = ($glyph['xMax'] - $glyph['xMin'] + 1) / $unitsPerEm * self::FONT_SIZE;
	    }
	    
	    $sb .= sprintf("/F0 %s Tf\n", self::FONT_SIZE); // Set font and font size
	    $sb .= sprintf("1 0 0 1 %s %s Tm\n", $x + ($cellWidth - $glyphWidth) / 2, $y);
	    $sb .= sprintf("<%04X>Tj\n", $gid);

	    $sb .= sprintf("/F1 6.0 Tf\n"); // Helvetica font at 6pt (display gid at left bottom corner)
	    $sb .= sprintf("1 0 0 1 %s %s Tm\n", $x + 2.0, $y - $cellHeight / 3 + 2.0);

	    $unicodeValue = $unicodeEncodingTable == null ? null : TTF::indexToCharacter($unicodeEncodingTable, $gid);
	    if ($unicodeValue != null) {
		$sb .= sprintf("(%d, %s)Tj\n", $gid, $unicodeValue);
	    } else {
		$sb .= sprintf("(%d)Tj\n", $gid);
	    }

	    // Advance x and y
	    $x += $cellWidth;
	    if ($x >= $x1) {
		$x = $x0;
		$y -= $cellHeight;
	    }
	}

	$sb .= "ET\n"; // End text object
	
	return gzcompress($sb); // Compress and return
    }

    private function locateObject($objects, $num) {
	foreach ($objects as $object) {
	    if ($object['num'] === $num) {
		return $object;
	    }
	}
	throw new Exception(sprintf("Cannot locate object [%d]\n", $num));
    }
}

class TTF2PDFUTILS {
    static function calculateCapHeight($cmap, $glyf, $unitsPerEm) {
	// Get the Unicode encoding table
	if (($unicodeEncodingTable = TTF::getEncodingTable($cmap, 3, 1)) == null) {
	    throw new Exception("No Unicode encoding table");
	}
	$yMax = 0;
	$flatCapitalLetters = "BDEFPRTZ";
	$numFlatCapitalLetters = strlen($flatCapitalLetters);
	$numUsed = 0;
	for ($i = 0; $i < $numFlatCapitalLetters; $i++) {
	    $charCode = ord($flatCapitalLetters{$i});
	    $index = TTF::characterToIndex($unicodeEncodingTable, $charCode);
	    if ($index >= 0) {
		$description = $glyf[$index];
		$glyph = TTF::getGlyph($description);
		$yMax += $glyph['yMax'];
		$numUsed++;
	    }
	}
	if ($numUsed == 0) {
	    return 0;
	} else {
	    return round($yMax / $numUsed * 1000 / $unitsPerEm);
	}
    }

    static function constructToUnicode($gid2cidMap) {
	$sb = '';
	$sb .= "/CIDInit /ProcSet findresource begin\n";
	$sb .= "12 dict begin\n";
	$sb .= "begincmap\n";
	$sb .= "/CIDSystemInfo << /Registry (Adobe) /Ordering (UCS) /Supplement 0 >> def\n";
	$sb .= "/CMapName /Adobe-Identity-UCS def\n";
	$sb .= "/CMapType 2 def\n";
	$sb .= "1 begincodespacerange\n";
	$sb .= "<0000> <FFFF>\n";
	$sb .= "endcodespacerange\n";
	$sb .= sprintf("%d beginbfchar\n", count($gid2cidMap));
	foreach ($gid2cidMap as $gid=>$cid) { //XXX - This could be better
	    $sb .= sprintf("<%04x> <%04x>\n", $gid, $cid);
	}
	$sb .= "endbfchar\n";
	$sb .= "endcmap\n";
	$sb .= "CMapName currentdict /CMap defineresource pop\n";
	$sb .= "end\n";
	$sb .= "end\n";
	return $sb;
    }

    static function constructCidGidMaps($cmap) {
	// Get the Unicode encoding table
	if (($unicodeEncodingTable = TTF::getEncodingTable($cmap, 3, 1)) == null) {
	    throw new Exception("No Unicode encoding table");
	}
	if ($unicodeEncodingTable['format'] != 4) {
	    throw new Exception("Unicode encoding table not in format 4");
	}
	$segCount = $unicodeEncodingTable['segCount'];
	$endCountArray = $unicodeEncodingTable['endCountArray'];
	$startCountArray = $unicodeEncodingTable['startCountArray'];
	$idDeltaArray = $unicodeEncodingTable['idDeltaArray'];
	$idRangeOffsetArray = $unicodeEncodingTable['idRangeOffsetArray'];
	$glyphIdArray = $unicodeEncodingTable['glyphIdArray'];

	$CIDToGIDMap = array();
	$GID2CIDMap = array();

	for ($seg = 0; $seg < $segCount; $seg++) {
	    $startCount = $startCountArray[$seg];
	    $endCount = $endCountArray[$seg];
	    $idDelta = $idDeltaArray[$seg];
	    $idRangeOffset = $idRangeOffsetArray[$seg];
	    for ($cid = $startCount; $cid <= $endCount; $cid++) {
		if ($idRangeOffset != 0) {
		    $j = $cid - $startCount + $seg + $idRangeOffset / 2 - $segCount;
		    $gid = $glyphIdArray[$j];
		} else {
		    $gid = $idDelta + $cid;
		}
		$gid = $gid % 65536;
		$CIDToGIDMap[$cid] = $gid;
		$GIDToCIDMap[$gid] = $cid;
	    }
	}
	return array('CIDToGIDMap' => $CIDToGIDMap, 'GIDToCIDMap' => $GIDToCIDMap);
    }

    static function constructW($hmtx, $unitsPerEm) {
	// Collect advance widths for all glyphs
	$advanceWidths = array();
	foreach ($hmtx['metrics'] as $metric) {
	    $lastAdvanceWidth = $metric[0];
	    $advanceWidths[] = $lastAdvanceWidth;
	}
	foreach ($hmtx['lsbs'] as $lsb) {
	    $advanceWidths[] = $lastAdvanceWidth;
	}

	$THRESHOLD = 4;

	$sb = '';
	$i = 0;
	$len = count($advanceWidths);
	while ($i < $len) {
	    $j = self::locateSequence($advanceWidths, $i, $len, $THRESHOLD);
	    if ($j == $i) {
		while ($i < $len && $advanceWidths[$i] == $advanceWidths[$j]) {
		    $i++;
		}
		$sb .= sprintf(" %d %d %d", $j, $i - 1, round($advanceWidths[$j] * 1000 / $unitsPerEm));
		continue;
	    } else {
		$sb .= sprintf(" %d ", $i);
		$i0 = $i;
		while ($i < $j) {
		    $sb .= sprintf("%s%d", $i0 == $i ? "[" : " ", round($advanceWidths[$i] * 1000 / $unitsPerEm));
		    $i++;
		}
		$sb .= "]";
	    }
	}
	return array('advanceWidths' => $advanceWidths, 'w' => '[' . substr($sb, 1) . ']');
    }

    private static function locateSequence($aw, $i, $len, $threshold) {
	for (;;) {
	    if ($i >= $len - $threshold) {
		return $len;
	    }
	    for ($k = $i; $k < $i + $threshold; $k++) {
		if ($aw[$k] != $aw[$i]) {
		    break;
		}
	    }
	    if ($k >= $i + $threshold) {
		return $i;
	    }
	    $i++;
	}
    }
}

?>
