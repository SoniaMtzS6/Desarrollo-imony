<?php
/*******************************************************************************
* tFPDF (based on FPDF)                                                        *
*                                                                              *
* Version: 1.33                                                                *
* Date:    2022-12-20                                                          *
* Author:  Ian Back                                                            *
* License: LGPL                                                                *
*******************************************************************************/

define('tFPDF_VERSION','1.33');

class tFPDF
{
protected $page;               // current page number
protected $n;                  // current object number
protected $offsets;            // array of object offsets
protected $buffer;             // buffer holding in-memory PDF
protected $pages;              // array containing pages
protected $state;              // current document state
protected $compress;           // compression flag
protected $k;                  // scale factor (number of points in user unit)
protected $DefOrientation;     // default orientation
protected $CurOrientation;     // current orientation
protected $StdPageSizes;       // standard page sizes
protected $DefPageSize;        // default page size
protected $CurPageSize;        // current page size
protected $CurPageFormat;      // current page format
protected $PageFormats;        // available page formats
protected $PageWidth;          // page width in points
protected $PageHeight;         // page height in points
protected $n_pages;            // number of pages in the document
protected $wPt;
protected $hPt;          // dimensions of current page in points
protected $w;
protected $h;              // dimensions of current page in user unit
protected $lMargin;            // left margin
protected $tMargin;            // top margin
protected $rMargin;            // right margin
protected $bMargin;            // page break margin
protected $cMargin;            // cell margin
protected $x;
protected $y;              // current position in user unit
protected $lasth;              // height of last printed cell
protected $LineWidth;          // line width in user unit
protected $fontpath;           // path containing fonts
protected $CoreFonts;          // array of core font names
protected $fonts;              // array of used fonts
protected $FontFiles;          // array of font files
protected $encodings;          // array of encodings
protected $cmaps;              // array of ToUnicode CMaps
protected $FontFamily;         // current font family
protected $FontStyle;          // current font style
protected $underline;          // underlining flag
protected $CurrentFont;        // current font info
protected $FontSizePt;         // current font size in points
protected $FontSize;           // current font size in user unit
protected $DrawColor;          // commands for drawing color
protected $FillColor;          // commands for filling color
protected $TextColor;          // commands for text color
protected $ColorFlag;          // indicates whether fill and text colors are different
protected $WithAlpha;          // indicates whether alpha channel is used
protected $ws;                 // word spacing
protected $images;             // array of used images
protected $PageLinks;          // array of links in pages
protected $links;              // array of internal links
protected $AutoPageBreak;      // automatic page breaking
protected $PageBreakTrigger;   // threshold used to trigger page breaks
protected $InHeader;           // flag set when processing header
protected $InFooter;           // flag set when processing footer
protected $AliasNbPages;       // alias for total number of pages
protected $ZoomMode;           // zoom display mode
protected $LayoutMode;         // layout display mode
protected $metadata;           // document properties
protected $PDFVersion;         // PDF version number

// Unicode properties
public $unicode;               // TRUE if current font is Unicode
protected $UTF8String;         // TRUE if the string has to be converted to UTF-16BE
public $Bidi;                  // TRUE if text is bidirectional
protected $CurrentFontIsTTF;
// For TTF unicode font files
protected $T128;
protected $subsets;
protected $font_files;
protected $CurrentFontFile;
protected $FontBBox;
protected $font_widths;
protected $unifontb;

/*******************************************************************************
*                                                                              *
*                               Public methods                                 *
*                                                                              *
*******************************************************************************/

function __construct($orientation='P', $unit='mm', $size='A4')
{
	// Some checks
	$this->_dochecks();
	// Initialization of properties
	$this->state = 0;
	$this->page = 0;
	$this->n = 2;
	$this->buffer = '';
	$this->pages = array();
	$this->PageFormats = array();
	$this->n_pages = 1;
	$this->offsets = array();
	$this->fonts = array();
	$this->FontFiles = array();
	$this->encodings = array();
	$this->cmaps = array();
	$this->images = array();
	$this->links = array();
	$this->InHeader = false;
	$this->InFooter = false;
	$this->lasth = 0;
	$this->FontFamily = '';
	$this->FontStyle = '';
	$this->FontSizePt = 12;
	$this->underline = false;
	$this->DrawColor = '0 G';
	$this->FillColor = '0 g';
	$this->TextColor = '0 g';
	$this->ColorFlag = false;
	$this->WithAlpha = false;
	$this->ws = 0;
	// Font path
	if(defined('FPDF_FONTPATH'))
	{
		$this->fontpath = FPDF_FONTPATH;
		if(substr($this->fontpath,-1)!='/' && substr($this->fontpath,-1)!='\\\\')
			$this->fontpath .= '/';
	}
	elseif(is_dir(dirname(__FILE__).'/font'))
		$this->fontpath = dirname(__FILE__).'/font/';
	else
		$this->fontpath = '';
	// Core fonts
	$this->CoreFonts = array('courier', 'helvetica', 'times', 'symbol', 'zapfdingbats');
	// Scale factor
	if($unit=='pt')
		$this->k = 1;
	elseif($unit=='mm')
		$this->k = 72/25.4;
	elseif($unit=='cm')
		$this->k = 72/2.54;
	elseif($unit=='in')
		$this->k = 72;
	else
		$this->Error('Incorrect unit: '.$unit);
	// Page sizes
	$this->StdPageSizes = array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
		'letter'=>array(612,792), 'legal'=>array(612,1008));
	$size = $this->_getpagesize($size);
	$this->DefPageSize = $size;
	$this->CurPageSize = $size;
	// Page orientation
	$orientation = strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation = 'P';
		$this->w = $size[0];
		$this->h = $size[1];
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation = 'L';
		$this->w = $size[1];
		$this->h = $size[0];
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation = $this->DefOrientation;
	$this->wPt = $this->w*$this->k;
	$this->hPt = $this->h*$this->k;
	// Page rotation
	$this->CurRotation = 0;
	// Page margins (1 cm)
	$margin = 28.35/$this->k;
	$this->SetMargins($margin,$margin);
	// Interior cell margin (1 mm)
	$this->cMargin = $margin/10;
	// Line width (0.2 mm)
	$this->LineWidth = .567/$this->k;
	// Automatic page break
	$this->SetAutoPageBreak(true,2*$margin);
	// Full width display mode
	$this->SetDisplayMode('fullwidth');
	// Enable compression
	$this->SetCompression(true);
	// Set default PDF version number
	$this->PDFVersion = '1.3';

	$this->unifontb = false;
	$this->subsets = array();
	$this->font_files = array();
	$this->UTF8String = false;
	$this->Bidi = false;
}

function SetMargins($left, $top, $right=null)
{
	// Set left, top and right margins
	$this->lMargin = $left;
	$this->tMargin = $top;
	if($right===null)
		$right = $left;
	$this->rMargin = $right;
}

function SetLeftMargin($margin)
{
	// Set left margin
	$this->lMargin = $margin;
	if($this->page>0 && $this->x<$margin)
		$this->x = $margin;
}

function SetTopMargin($margin)
{
	// Set top margin
	$this->tMargin = $margin;
}

function SetRightMargin($margin)
{
	// Set right margin
	$this->rMargin = $margin;
}

function SetAutoPageBreak($auto, $margin=0)
{
	// Set auto page break mode and triggering margin
	$this->AutoPageBreak = $auto;
	$this->bMargin = $margin;
	$this->PageBreakTrigger = $this->h-$margin;
}

function SetDisplayMode($zoom, $layout='default')
{
	// Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode = $zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode = $layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	// Set page compression
	if(function_exists('gzcompress'))
		$this->compress = $compress;
	else
		$this->compress = false;
}

function SetTitle($title, $isUTF8=false)
{
	// Title of document
	$this->metadata['Title'] = $isUTF8 ? $this->_UTF8toUTF16($title) : $title;
}

function SetAuthor($author, $isUTF8=false)
{
	// Author of document
	$this->metadata['Author'] = $isUTF8 ? $this->_UTF8toUTF16($author) : $author;
}

function SetSubject($subject, $isUTF8=false)
{
	// Subject of document
	$this->metadata['Subject'] = $isUTF8 ? $this->_UTF8toUTF16($subject) : $subject;
}

function SetKeywords($keywords, $isUTF8=false)
{
	// Keywords of document
	$this->metadata['Keywords'] = $isUTF8 ? $this->_UTF8toUTF16($keywords) : $keywords;
}

function SetCreator($creator, $isUTF8=false)
{
	// Creator of document
	$this->metadata['Creator'] = $isUTF8 ? $this->_UTF8toUTF16($creator) : $creator;
}

function AliasNbPages($alias='{nb}')
{
	// Define an alias for total number of pages
	$this->AliasNbPages = $alias;
}

function Error($msg)
{
	// Fatal error
	throw new Exception('tFPDF error: '.$msg);
}

function Close()
{
	// Terminate document
	if($this->state==3)
		return;
	if($this->page==0)
		$this->AddPage();
	// Page footer
	$this->InFooter = true;
	$this->Footer();
	$this->InFooter = false;
	// Close page
	$this->_endpage();
	// Close document
	$this->_enddoc();
}

function AddPage($orientation='', $size='', $rotation=0)
{
	// Start a new page
	if($this->state==3)
		$this->Error('The document is closed');
	$family = $this->FontFamily;
	$style = $this->FontStyle.($this->underline ? 'U' : '');
	$fontsize = $this->FontSizePt;
	$lw = $this->LineWidth;
	$dc = $this->DrawColor;
	$fc = $this->FillColor;
	$tc = $this->TextColor;
	$cf = $this->ColorFlag;
	if($this->page>0)
	{
		// Page footer
		$this->InFooter = true;
		$this->Footer();
		$this->InFooter = false;
		// Close page
		$this->_endpage();
	}
	// Start new page
	$this->_beginpage($orientation,$size,$rotation);
	// Set line cap style to square
	$this->_out('2 J');
	// Set line width
	$this->LineWidth = $lw;
	$this->_out(sprintf('%.2F w',$lw*$this->k));
	// Set font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Set colors
	$this->DrawColor = $dc;
	if($dc!='0 G')
		$this->_out($dc);
	$this->FillColor = $fc;
	if($fc!='0 g')
		$this->_out($fc);
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
	// Page header
	$this->InHeader = true;
	$this->Header();
	$this->InHeader = false;
	// Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth = $lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
	}
	// Restore font
	if($family)
		$this->SetFont($family,$style,$fontsize);
	// Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor = $dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor = $fc;
		$this->_out($fc);
	}
	$this->TextColor = $tc;
	$this->ColorFlag = $cf;
}

function Header()
{
	// To be implemented in your own inherited class
}

function Footer()
{
	// To be implemented in your own inherited class
}

function PageNo()
{
	// Get current page number
	return $this->page;
}

function SetDrawColor($r, $g=null, $b=null)
{
	// Set color for all stroking operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->DrawColor = sprintf('%.3F G',$r/255);
	else
		$this->DrawColor = sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

function SetFillColor($r, $g=null, $b=null)
{
	// Set color for all filling operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->FillColor = sprintf('%.3F g',$r/255);
	else
		$this->FillColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

function SetTextColor($r, $g=null, $b=null)
{
	// Set color for text
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->TextColor = sprintf('%.3F g',$r/255);
	else
		$this->TextColor = sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag = ($this->FillColor!=$this->TextColor);
}

function GetStringWidth($s)
{
	// Get width of a string in the current font
	$s = (string)$s;
	$cw = &$this->CurrentFont['cw'];
	$w = 0;
	if ($this->unicode) {
		$s = $this->UTF8String($s);
		$char_width = $this->font_widths;
		$w = 0;
		$l = strlen($s);
		for($i=0;$i<$l;$i++)
		{
			$c = $s[$i];
			if(isset($char_width[$c]))
				$w += $char_width[$c];
		}
		return $w/1000*$this->FontSize;
	}
	else
	{
		$l = strlen($s);
		for($i=0;$i<$l;$i++)
			$w += $cw[$s[$i]];
	}
	return $w*$this->FontSize/1000;
}

function SetLineWidth($width)
{
	// Set line width
	$this->LineWidth = $width;
	if($this->page>0)
		$this->_out(sprintf('%.2F w',$width*$this->k));
}

function Line($x1, $y1, $x2, $y2)
{
	// Draw a line
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='')
{
	// Draw a rectangle
	if($style=='F')
		$op = 'f';
	elseif($style=='FD' || $style=='DF')
		$op = 'B';
	else
		$op = 'S';
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddFont($family, $style='', $file='', $uni=false)
{
	// Add a TrueType, OpenType or Type1 font
	$family = strtolower($family);
	$style = strtoupper($style);
	if($style=='IB')
		$style = 'BI';
	if($file=='')
	{
		if ($uni)
			$file = str_replace(' ','',$family).strtolower($style).'.ttf';
		else
			$file = str_replace(' ','',$family).strtolower($style).'.php';
	}
	$fontkey = $family.$style;
	if(isset($this->fonts[$fontkey]))
		return;

	if ($uni) {
		if (defined('_SYSTEM_TTFONTS') && file_exists(_SYSTEM_TTFONTS.$file)) { $ttffilename = _SYSTEM_TTFONTS.$file; }
		else { $ttffilename = $this->fontpath.'unifont/'.$file; }
		if (!file_exists($ttffilename))
			$this->Error('TrueType font file not found: '.$file);
		$font_info = array('type'=>'TTF', 'name'=>$fontkey, 'desc'=>'', 'up'=>-100, 'ut'=>50, 'cw'=>'', 'enc'=>'', 'file'=>$ttffilename, 'ctg'=>'', 'T128'=>'', 'subsets'=>'');
		$this->fonts[$fontkey] = $font_info;
		$this->FontFiles[$fontkey] = array('length1'=>0);
	}
	else
	{
		$info = $this->_loadfont($file);
		$info['i'] = count($this->fonts)+1;
		if(!empty($info['subset']))
		{
			$this->Error('Font subsetting not supported in this version: '.$file);
		}
		if(!empty($info['cm']))
		{
			$info['cmi'] = count($this->cmaps)+1;
			$this->cmaps[$info['cmi']] = $info['cm'];
		}
		if(!empty($info['enc']))
		{
			$info['enci'] = count($this->encodings)+1;
			$this->encodings[$info['enci']] = $info['enc'];
		}
		$this->fonts[$fontkey] = $info;
	}
}

function SetFont($family, $style='', $size=0)
{
	// Select a font; size given in points
	if($family=='')
		$family = $this->FontFamily;
	else
		$family = strtolower($family);
	$style = strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline = true;
		$style = str_replace('U','',$style);
	}
	else
		$this->underline = false;
	if($style=='IB')
		$style = 'BI';
	if($size==0)
		$size = $this->FontSizePt;
	// Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;
	// Test if font is already loaded
	$fontkey = $family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		// Test if one of the core fonts
		if($family=='arial')
			$family = 'helvetica';
		if(in_array($family,$this->CoreFonts))
		{
			if($family=='symbol' || $family=='zapfdingbats')
				$style = '';
			$fontkey = $family.$style;
			if(!isset($this->fonts[$fontkey]))
				$this->AddFont($family,$style);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}

	// For Unicode fonts, define unicode property
	$this->unicode = ($this->fonts[$fontkey]['type']=='TTF');
	if ($this->unicode)
	{
		$this->CurrentFontIsTTF=true;
	}
	else {
		$this->CurrentFontIsTTF=false;
	}

	// Select it
	$this->FontFamily = $family;
	$this->FontStyle = $style;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	$this->CurrentFont = &$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));

}

function SetFontSize($size)
{
	// Set font size in points
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt = $size;
	$this->FontSize = $size/$this->k;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function AddLink()
{
	// Create a new internal link
	$n = count($this->links)+1;
	$this->links[$n] = array(0, 0);
	return $n;
}

function SetLink($link, $y=0, $page=-1)
{
	// Set destination of internal link
	if($y==-1)
		$y = $this->y;
	if($page==-1)
		$page = $this->page;
	$this->links[$link] = array($page, $y);
}

function Link($x, $y, $w, $h, $link)
{
	// Put a link on the page
	$this->PageLinks[$this->page][] = array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
}

function Text($x, $y, $txt)
{
	// Print a string
	if ($this->unicode)
		$txt = $this->UTF8String($txt);
	$s = sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
	if($this->underline && $txt!='')
		$s .= ' '.$this->_dounderline($x,$y,$txt);
	if($this->ColorFlag)
		$s = 'q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function AcceptPageBreak()
{
	// Accept automatic page break or not
	return $this->AutoPageBreak;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	// Output a cell
	$k = $this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		// Automatic page break
		$x = $this->x;
		$ws = $this->ws;
		if($ws>0)
		{
			$this->ws = 0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
		$this->x = $x;
		if($ws>0)
		{
			$this->ws = $ws;
			$this->_out(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$s = '';
	if($fill || $border==1)
	{
		if($fill)
			$op = ($border==1) ? 'B' : 'f';
		else
			$op = 'S';
		$s = sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}
	if(is_string($border))
	{
		$x = $this->x;
		$y = $this->y;
		if(strpos($border,'L')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'T')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(strpos($border,'R')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'B')!==false)
			$s .= sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}
	if($txt!=='')
	{
		if ($this->unicode)
			$txt = $this->UTF8String($txt);
		$width_txt = $this->GetStringWidth($txt);
		if($align=='R')
			$dx = $w-$this->cMargin-$width_txt;
		elseif($align=='C')
			$dx = ($w-$width_txt)/2;
		else
			$dx = $this->cMargin;
		if($this->ColorFlag)
			$s .= 'q '.$this->TextColor.' ';
		$s .= sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$this->_escape($txt));
		if($this->underline)
			$s .= ' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
		if($this->ColorFlag)
			$s .= ' Q';
		if($link)
			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$width_txt,$this->FontSize,$link);
	}
	if($s)
		$this->_out($s);
	$this->lasth = $h;
	if($ln>0)
	{
		// Go to next line
		$this->y += $h;
		if($ln==1)
			$this->x = $this->lMargin;
	}
	else
		$this->x += $w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	// Output text with automatic or explicit line breaks
	$cw = &$this->CurrentFont['cw'];
	if($w==0)
		$w = $this->w-$this->rMargin-$this->x;
	$wmax = ($w-2*$this->cMargin);

	if ($this->unicode) {
		$s = str_replace("\r",'',$txt);
		$nb = $this->uni_strlen($s);
		if($nb>0 && $s[$nb-1]=="\n")
			$nb--;
	} else {
		$s = str_replace("\r",'',$txt);
		$nb = strlen($s);
		if($nb>0 && $s[$nb-1]=="\n")
			$nb--;
	}

	$b = 0;
	if($border)
	{
		if($border==1)
		{
			$border = 'LTRB';
			$b = 'LRT';
			$b2 = 'LR';
		}
		else
		{
			$b2 = '';
			if(strpos($border,'L')!==false)
				$b2 .= 'L';
			if(strpos($border,'R')!==false)
				$b2 .= 'R';
			$b = (strpos($border,'T')!==false) ? $b2.'T' : $b2;
		}
	}
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$ns = 0;
	$nl = 1;
	while($i<$nb)
	{
		if ($this->unicode) {
			// Get next character
			$c = $this->uni_substr($s,$i,1);
		} else {
			// Get next character
			$c = $s[$i];
		}

		if($c=="\n")
		{
			// Explicit line break
			if($this->ws>0)
			{
				$this->ws = 0;
				$this->_out('0 Tw');
			}
			if ($this->unicode) {
				$this->Cell($w,$h,$this->uni_substr($s,$j,$i-$j),$b,2,$align,$fill);
			} else {
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}

			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = $b2;
			continue;
		}
		if($c==' ')
		{
			$sep = $i;
			$ls = $l;
			$ns++;
		}

		if ($this->unicode) {
			$l += $this->GetStringWidth($c);
		} else {
			$l += $cw[$c]*$this->FontSize/1000;
		}

		if($l>$wmax)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				if($this->ws>0)
				{
					$this->ws = 0;
					$this->_out('0 Tw');
				}
				if ($this->unicode)
					$this->Cell($w,$h,$this->uni_substr($s,$j,$i-$j),$b,2,$align,$fill);
				else
					$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				if($align=='J')
				{
					$this->ws = ($ns>1) ? ($wmax-$ls)/($ns-1) : 0;
					$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
				}
				if ($this->unicode)
					$this->Cell($w,$h,$this->uni_substr($s,$j,$sep-$j),$b,2,$align,$fill);
				else
					$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			$ns = 0;
			$nl++;
			if($border && $nl==2)
				$b = $b2;
		}
		else
			$i++;
	}
	// Last chunk
	if($this->ws>0)
	{
		$this->ws = 0;
		$this->_out('0 Tw');
	}
	if($border && strpos($border,'B')!==false)
		$b .= 'B';
	if ($this->unicode)
		$this->Cell($w,$h,$this->uni_substr($s,$j,$i-$j),$b,2,$align,$fill);
	else
		$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x = $this->lMargin;
}

function Write($h, $txt, $link='')
{
	// Output text in flowing mode
	$cw = &$this->CurrentFont['cw'];
	$w = $this->w-$this->rMargin-$this->x;

	if ($this->unicode) {
		$s = str_replace("\r",'',$txt);
		$nb = $this->uni_strlen($s);
	} else {
		$s = str_replace("\r",'',$txt);
		$nb = strlen($s);
	}
	
	$sep = -1;
	$i = 0;
	$j = 0;
	$l = 0;
	$nl = 1;
	while($i<$nb)
	{
		if ($this->unicode)
			$c = $this->uni_substr($s,$i,1);
		else
			$c = $s[$i];

		if($c=="\n")
		{
			// Explicit line break
			if ($this->unicode)
				$this->Cell($w,$h,$this->uni_substr($s,$j,$i-$j),0,2,'',false,$link);
			else
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
			$i++;
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl==1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
			}
			$nl++;
			continue;
		}
		if($c==' ')
			$sep = $i;
		
		if ($this->unicode)
			$cl = $this->GetStringWidth($c);
		else
			$cl = $cw[$c]*$this->FontSize/1000;
		$l += $cl;

		if($l>$w)
		{
			// Automatic line break
			if($sep==-1)
			{
				if($this->x>$this->lMargin)
				{
					// Move to next line
					$this->x = $this->lMargin;
					$this->y += $h;
					$w = $this->w-$this->rMargin-$this->x;
					$i--;
					continue;
				}
				if($i==$j)
					$i++;
				if ($this->unicode)
					$this->Cell($w,$h,$this->uni_substr($s,$j,$i-$j),0,2,'',false,$link);
				else
					$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',false,$link);
			}
			else
			{
				if ($this->unicode)
					$this->Cell($w,$h,$this->uni_substr($s,$j,$sep-$j),0,2,'',false,$link);
				else
					$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',false,$link);
				$i = $sep+1;
			}
			$sep = -1;
			$j = $i;
			$l = 0;
			if($nl==1)
			{
				$this->x = $this->lMargin;
				$w = $this->w-$this->rMargin-$this->x;
			}
			$nl++;
		}
		else
			$i++;
	}
	// Last chunk
	if($i!=$j)
	{
		if ($this->unicode)
			$this->Cell($l,$h,$this->uni_substr($s,$j,$i-$j),0,0,'',false,$link);
		else
			$this->Cell($l,$h,substr($s,$j,$i-$j),0,0,'',false,$link);
	}
}

function Ln($h=null)
{
	// Line feed; default value is last cell height
	$this->x = $this->lMargin;
	if($h===null)
		$this->y += $this->lasth;
	else
		$this->y += $h;
}

function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
{
	// Put an image on the page
	if($file=='')
		$this->Error('Image file name is empty');
	if(!isset($this->images[$file]))
	{
		// First use of this image, get info
		if($type=='')
		{
			$pos = strrpos($file,'.');
			if(!$pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type = substr($file,$pos+1);
		}
		$type = strtolower($type);
		if($type=='jpeg')
			$type = 'jpg';
		$mtd = '_parse'.$type;
		if(!method_exists($this,$mtd))
			$this->Error('Unsupported image type: '.$type);
		$info = $this->$mtd($file);
		$info['i'] = count($this->images)+1;
		$this->images[$file] = $info;
	}
	else
		$info = $this->images[$file];

	// Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		// Put image at 96 dpi
		$w = -96;
		$h = -96;
	}
	if($w<0)
		$w = -$info['w']*72/$w/$this->k;
	if($h<0)
		$h = -$info['h']*72/$h/$this->k;
	if($w==0)
		$w = $h*$info['w']/$info['h'];
	if($h==0)
		$h = $w*$info['h']/$info['w'];

	// Flowing mode
	if($y===null)
	{
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			// Automatic page break
			$x2 = $this->x;
			$this->AddPage($this->CurOrientation,$this->CurPageSize,$this->CurRotation);
			$this->x = $x2;
		}
		$y = $this->y;
		$this->y += $h;
	}

	if($x===null)
		$x = $this->x;
	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
	if($link)
		$this->Link($x,$y,$w,$h,$link);
}

function GetPageWidth()
{
	// Get page width
	return $this->w;
}

function GetPageHeight()
{
	// Get page height
	return $this->h;
}

function GetX()
{
	// Get x position
	return $this->x;
}

function SetX($x)
{
	// Set x position
	if($x>=0)
		$this->x = $x;
	else
		$this->x = $this->w+$x;
}

function GetY()
{
	// Get y position
	return $this->y;
}

function SetY($y, $resetX=true)
{
	// Set y position and optionally reset x
	if($y>=0)
		$this->y = $y;
	else
		$this->y = $this->h+$y;
	if($resetX)
		$this->x = $this->lMargin;
}

function SetXY($x, $y)
{
	// Set x and y positions
	$this->SetY($y,false);
	$this->SetX($x);
}

function Output($dest='', $name='', $isUTF8=false)
{
	// Output PDF to some destination
	$this->Close();
	if($isUTF8)
		$name = $this->_UTF8toUTF16($name);
	if($dest=='')
	{
		$dest = 'I';
		$name = 'doc.pdf';
	}
	switch(strtoupper($dest))
	{
		case 'I':
			// Send to standard output
			$this->_checkoutput();
			if(PHP_SAPI!='cli')
			{
				// We send to a browser
				header('Content-Type: application/pdf');
				header('Content-Disposition: inline; filename="'.$name.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
			}
			echo $this->buffer;
			break;
		case 'D':
			// Download file
			$this->_checkoutput();
			header('Content-Type: application/x-download');
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			echo $this->buffer;
			break;
		case 'F':
			// Save to local file
			if(!file_put_contents($name,$this->buffer))
				$this->Error('Unable to create output file: '.$name);
			break;
		case 'S':
			// Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/

protected function _dochecks()
{
	// Check for locale-related bug
	if(1.1==1)
		$this->Error('Don\'t alter the locale before including class file');
	// Check for mbstring extension
	if(function_exists('mb_strlen'))
	{
		if((ini_get('mbstring.func_overload') & 2) != 0)
			$this->Error('mbstring overloading must be disabled');
	}
	else
		$this->Error('mbstring extension is not available');
}

protected function _checkoutput()
{
	if(PHP_SAPI!='cli')
	{
		if(headers_sent($file,$line))
			$this->Error("Some data has already been output, can't send PDF file (output started at $file:$line)");
	}
	if(ob_get_length())
	{
		// The output buffer is not empty
		if(preg_match('/^(\xEF\xBB\xBF)?\s*$/',ob_get_contents()))
		{
			// It contains only a UTF-8 BOM and/or whitespace, let's clean it
			ob_clean();
		}
		else
			$this->Error("Some data has already been output, can't send PDF file");
	}
}

protected function _getpagesize($size)
{
	if(is_string($size))
	{
		$size = strtolower($size);
		if(!isset($this->StdPageSizes[$size]))
			$this->Error('Unknown page size: '.$size);
		$a = $this->StdPageSizes[$size];
		return array($a[0]/$this->k, $a[1]/$this->k);
	}
	else
	{
		if($size[0]>$size[1])
			return array($size[1], $size[0]);
		else
			return $size;
	}
}

protected function _beginpage($orientation, $size, $rotation)
{
	$this->page++;
	$this->pages[$this->page] = '';
	$this->state = 2;
	$this->x = $this->lMargin;
	$this->y = $this->tMargin;
	$this->FontFamily = '';
	// Check page size and orientation
	if($orientation=='')
		$orientation = $this->DefOrientation;
	else
		$orientation = strtoupper($orientation[0]);
	if($size=='')
		$size = $this->DefPageSize;
	else
		$size = $this->_getpagesize($size);
	if($orientation!=$this->CurOrientation || $size[0]!=$this->CurPageSize[0] || $size[1]!=$this->CurPageSize[1])
	{
		// New size or orientation
		if($orientation=='P')
		{
			$this->w = $size[0];
			$this->h = $size[1];
		}
		else
		{
			$this->w = $size[1];
			$this->h = $size[0];
		}
		$this->wPt = $this->w*$this->k;
		$this->hPt = $this->h*$this->k;
		$this->PageBreakTrigger = $this->h-$this->bMargin;
		$this->CurOrientation = $orientation;
		$this->CurPageSize = $size;
	}
	if($orientation!=$this->DefOrientation)
		$this->CurPageFormat['DefOrientation'] = $orientation;
	if($size[0]!=$this->DefPageSize[0] || $size[1]!=$this->DefPageSize[1])
		$this->CurPageFormat['DefPageSize'] = $size;
	// Page rotation
	if($rotation!=0)
	{
		if($rotation%90!=0)
			$this->Error('Incorrect rotation value: '.$rotation);
		$this->CurRotation = $rotation;
		$this->CurPageFormat['CurRotation'] = $rotation;
	}
}

protected function _endpage()
{
	$this->state = 1;
}

protected function _loadfont($font)
{
	// Load a font definition file from the font directory
	if(strpos($font,'/')!==false || strpos($font,"\\\\")!==false)
		$this->Error('Incorrect font definition file name: '.$font);
	include($this->fontpath.$font);
	if(!isset($name))
		$this->Error('Could not include font definition file');
	if(!isset($cw))
		$this->Error('Font is missing character widths');
	return get_defined_vars();
}

protected function _UTF8toUTF16($s)
{
	// Convert UTF-8 to UTF-16BE with BOM
	$res = "\xFE\xFF";
	$nb = strlen($s);
	$i = 0;
	while($i<$nb)
	{
		$c1 = ord($s[$i++]);
		if($c1>=224)
		{
			// 3-byte character
			$c2 = ord($s[$i++]);
			$c3 = ord($s[$i++]);
			$res .= chr((($c1 & 0x0F) << 4) + (($c2 & 0x3C) >> 2));
			$res .= chr((($c2 & 0x03) << 6) + ($c3 & 0x3F));
		}
		elseif($c1>=192)
		{
			// 2-byte character
			$c2 = ord($s[$i++]);
			$res .= chr(($c1 & 0x1C) >> 2);
			$res .= chr((($c1 & 0x03) << 6) + ($c2 & 0x3F));
		}
		else
		{
			// Single-byte character
			$res .= "\0".chr($c1);
		}
	}
	return $res;
}

protected function _escape($s)
{
	// Escape special characters in strings
	$s = str_replace('\\','\\\\',$s);
	$s = str_replace('(','\\(',$s);
	$s = str_replace(')','\\)',$s);
	$s = str_replace("\r",'\\r',$s);
	return $s;
}

protected function _putpages()
{
	$nb = $this->page;
	if(!empty($this->AliasNbPages))
	{
		// Replace number of pages
		for($n=1;$n<=$nb;$n++)
		{
			if($this->n_pages==1)
				$this->pages[$n] = str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
			else
			{
				$this->pages[$n] = str_replace($this->AliasNbPages,'{totalPages}',$this->pages[$n]);
				$this->n_pages = $nb;
			}
		}
	}
	if($this->DefOrientation=='P' && $this->DefPageSize[0]==$this->StdPageSizes['a4'][0]/$this->k && $this->DefPageSize[1]==$this->StdPageSizes['a4'][1]/$this->k)
		$this->PageFormats['1']['DefPageSize'] = $this->DefPageSize;
	if($this->DefOrientation=='L' && $this->DefPageSize[0]==$this->StdPageSizes['a4'][1]/$this->k && $this->DefPageSize[1]==$this->StdPageSizes['a4'][0]/$this->k)
		$this->PageFormats['1']['DefPageSize'] = $this->DefPageSize;
	
	$wPt = $this->DefPageSize[0]*$this->k;
	$hPt = $this->DefPageSize[1]*$this->k;

	// Recalculate page formats
	if (count($this->PageFormats)>0) {
		$this->page_formats = '';
		foreach ($this->PageFormats as $n=>$pf) {
			$this->page_formats.=$n.' 0 R ';
		}
		$this->page_formats = substr($this->page_formats,0,strlen($this->page_formats)-1);
	}


	for($n=1;$n<=$nb;$n++)
	{
		$this->_newobj();
		$this->_put('<</Type /Page');
		$this->_put('/Parent 1 0 R');
		if(isset($this->PageFormats[$n]))
		{
			$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageFormats[$n]['wPt'],$this->PageFormats[$n]['hPt']));
			if(isset($this->PageFormats[$n]['CurRotation']))
				$this->_put(sprintf('/Rotate %d', $this->PageFormats[$n]['CurRotation']));
		}
		$this->_put('/Contents '.($this->n+1).' 0 R');
		$this->_put('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			$s = '/Annots [';
			foreach($this->PageLinks[$n] as $pl)
			{
				$rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$s .= '<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(is_string($pl[4]))
					$s .= '/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l = $this->links[$pl[4]];
					if(isset($this->PageFormats[$l[0]]))
						$h = $this->PageFormats[$l[0]]['hPt'];
					else
						$h = $this->hPt;
					$s .= sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',2+$l[0],$h-$l[1]*$this->k);
				}
			}
			$this->_put($s.']');
		}
		if($this->WithAlpha)
			$this->_put('/Group <</Type /Group /S /Transparency /CS /DeviceRGB>>');
		$this->_put('>>');
		$this->_put('endobj');
		// Page content
		$p = ($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_newobj();
		$this->_put('<</Filter /FlateDecode /Length '.strlen($p).'>>');
		$this->_put($p);
		$this->_put('endobj');
	}
	// Pages root
	$this->offsets[1] = strlen($this->buffer);
	$this->_put('1 0 obj');
	$this->_put('<</Type /Pages');
	$kids = '/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids .= (3+2*$i).' 0 R ';
	$this->_put($kids.']');
	$this->_put('/Count '.$nb);
	$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
	$this->_put('>>');
	$this->_put('endobj');

	// Page Formats
	if (count($this->PageFormats)>0) {
		foreach ($this->PageFormats as $n=>$pf) {
			$this->_newobj($n);
			$this->_put('<</Type /Page');
			$this->_put('/Parent 1 0 R');
			$this->_put('/Resources 2 0 R');
			if(isset($pf['DefPageSize']))
				$this->_put(sprintf('/MediaBox [0 0 %.2F %.2F]',$pf['DefPageSize'][0]*$this->k,$pf['DefPageSize'][1]*$this->k));
			if(isset($pf['DefOrientation']))
				$this->_put('/Rotate '.$this->orients[$pf['DefOrientation']]);
			$this->_put('>>');
			$this->_put('endobj');
		}
	}
}

protected function _putfonts()
{
	$nf = $this->n;
	foreach($this->CoreFonts as $font)
	{
		if(isset($this->fonts[$font]))
		{
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$font);
			$this->_put('/Subtype /Type1');
			$this->_put('/Encoding /WinAnsiEncoding');
			$this->_put('>>');
			$this->_put('endobj');
		}
		if(isset($this->fonts[$font.'b']))
		{
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$font.'-Bold');
			$this->_put('/Subtype /Type1');
			$this->_put('/Encoding /WinAnsiEncoding');
			$this->_put('>>');
			$this->_put('endobj');
		}
		if(isset($this->fonts[$font.'i']))
		{
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$font.'-Oblique');
			$this->_put('/Subtype /Type1');
			$this->_put('/Encoding /WinAnsiEncoding');
			$this->_put('>>');
			$this->_put('endobj');
		}
		if(isset($this->fonts[$font.'bi']))
		{
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/BaseFont /'.$font.'-BoldOblique');
			$this->_put('/Subtype /Type1');
			$this->_put('/Encoding /WinAnsiEncoding');
			$this->_put('>>');
			$this->_put('endobj');
		}
	}
	foreach($this->fonts as $k=>$font)
	{
		if(!in_array($k,$this->CoreFonts) && (!isset($font['type']) || $font['type']!='TTF'))
		{
			// Type1 font
			$this->_newobj($font['i']);
			$this->_put('<</Type /Font');
			$this->_put('/Subtype /Type1');
			$this->_put('/BaseFont /'.$font['name']);
			$this->_put('/FirstChar 32 /LastChar 255');
			$this->_put('/Widths '.($this->n+1).' 0 R');
			$this->_put('/FontDescriptor '.($this->n+2).' 0 R');
			if(isset($font['cmi']))
				$this->_put('/ToUnicode '.($this->n+3+$font['cmi']-$font['enci']).' 0 R');
			if(isset($font['enci']))
				$this->_put('/Encoding '.($this->n+3).' 0 R');
			else
				$this->_put('/Encoding /WinAnsiEncoding');
			$this->_put('>>');
			$this->_put('endobj');
			// Widths
			$this->_newobj();
			$cw = &$font['cw'];
			$s = '[';
			for($i=32;$i<=255;$i++)
				$s .= $cw[chr($i)].' ';
			$this->_put($s.']');
			$this->_put('endobj');
			// Descriptor
			$this->_newobj();
			$s = '<</Type /FontDescriptor /FontName /'.$font['name'];
			foreach($font['desc'] as $k=>$v)
				$s .= ' /'.$k.' '.$v;
			if(!empty($font['file']))
				$s .= ' /FontFile'.($font['type']=='Type1' ? '' : '2').' '.($this->n+2).' 0 R';
			$this->_put($s.'>>');
			$this->_put('endobj');
			// Font file
			if(!empty($font['file']))
			{
				$this->_newobj();
				$this->_put('<</Length '.strlen($font['content']));
				$this->_put('/Length1 '.$font['size1']);
				if(isset($font['size2']))
					$this->_put('/Length2 '.$font['size2']);
				$this->_put('>>');
				$this->_put($font['content']);
				$this->_put('endobj');
			}
			// Encodings
			if(isset($font['enci']))
			{
				$this->_newobj();
				$this->_put($this->encodings[$font['enci']]);
				$this->_put('endobj');
			}
			// ToUnicode CMap
			if(isset($font['cmi']))
			{
				$this->_newobj();
				$this->_put($this->cmaps[$font['cmi']]);
				$this->_put('endobj');
			}
		}
		else if (isset($font['type']) && $font['type']=='TTF') {
			$this->unifontb = true;
			$this->FontFiles[$k]['n'] = $this->n + 1;
			// CIDFont
			$this->_newobj();
			$this->_put('<</Type /Font');
			$this->_put('/Subtype /CIDFontType2');
			$this->_put('/BaseFont /'.$font['name']);
			$this->_put('/CIDSystemInfo '.($this->n+1).' 0 R');
			$this->_put('/FontDescriptor '.($this->n+2).' 0 R');
			// Get W array
			$this->_newobj();
			$w_str = '';
			foreach ($font['cw'] as $cid=>$w) {
				$w_str .= $cid.' ['.(-$w).'] ';
			}
			$this->_put('/W [ '.$w_str.']');
			// Get DW
			if (isset($font['desc']['MissingWidth']))
				$this->_put('/DW '.round($font['desc']['MissingWidth']));
			$this->_put('>>');
			$this->_put('endobj');
			// CIDSystemInfo
			$this->_newobj();
			$this->_put('<</Registry (Adobe)');
			$this->_put('/Ordering (Identity)');
			$this->_put('/Supplement 0');
			$this->_put('>>');
			$this->_put('endobj');
			// Font descriptor
			$this->_newobj();
			$this->_put('<</Type /FontDescriptor');
			$this->_put('/FontName /'.$font['name']);
			$this->_put('/Flags 32');
			$this->_put('/FontBBox ['.$font['desc']['FontBBox'].']');
			$this->_put('/ItalicAngle '.$font['desc']['ItalicAngle']);
			$this->_put('/Ascent '.$font['desc']['Ascent']);
			$this->_put('/Descent '.$font['desc']['Descent']);
			$this->_put('/CapHeight '.$font['desc']['CapHeight']);
			$this->_put('/StemV '.$font['desc']['StemV']);
			$this->_put('/MissingWidth '.$font['desc']['MissingWidth']);
			$this->_put('/FontFile2 '.($this->n+1).' 0 R');
			$this->_put('>>');
			$this->_put('endobj');
			// Font file
			$this->_newobj();
			$this->_put('<</Length '.strlen($font['content']));
			$this->_put('/Filter /FlateDecode');
			$this->_put('/Length1 '.$font['originalsize']);
			$this->_put('>>');
			$this->_put($font['content']);
			$this->_put('endobj');
			// ToUnicode CMap
			$this->_newobj();
			$this->_put($font['uv']);
			$this->_put('endobj');
			// Font object
			$this->_newobj($font['i']);
			$this->_put('<</Type /Font');
			$this->_put('/Subtype /Type0');
			$this->_put('/BaseFont /'.$font['name']);
			$this->_put('/Encoding /Identity-H');
			$this->_put('/ToUnicode '.($this->n-1).' 0 R');
			$this->_put('/DescendantFonts ['.($this->n-5).' 0 R]');
			$this->_put('>>');
			$this->_put('endobj');

		}
	}
}

protected function _putimages()
{
	foreach(array_keys($this->images) as $file)
	{
		$this->_putimage($this->images[$file]);
		unset($this->images[$file]['data']);
		unset($this->images[$file]['smask']);
	}
}

protected function _putimage(&$info)
{
	$this->_newobj();
	$info['n'] = $this->n;
	$this->_put('<</Type /XObject');
	$this->_put('/Subtype /Image');
	$this->_put('/Width '.$info['w']);
	$this->_put('/Height '.$info['h']);
	if($info['cs']=='Indexed')
		$this->_put('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
	else
	{
		$this->_put('/ColorSpace /'.$info['cs']);
		if($info['cs']=='DeviceCMYK')
			$this->_put('/Decode [1 0 1 0 1 0 1 0]');
	}
	$this->_put('/BitsPerComponent '.$info['bpc']);
	if(isset($info['f']))
		$this->_put('/Filter /'.$info['f']);
	if(isset($info['dp']))
		$this->_put('/DecodeParms <<'.$info['dp'].'>>');
	if(isset($info['trns']) && is_array($info['trns']))
	{
		$trns = '';
		for($i=0;$i<count($info['trns']);$i++)
			$trns .= $info['trns'][$i].' '.$info['trns'][$i].' ';
		$this->_put('/Mask ['.$trns.']');
	}
	if(isset($info['smask']))
		$this->_put('/SMask '.($this->n+1).' 0 R');
	$this->_put('/Length '.strlen($info['data']).'>>');
	$this->_put($info['data']);
	$this->_put('endobj');
	// Soft mask
	if(isset($info['smask']))
	{
		$dp = '/Predictor 15 /Colors 1 /BitsPerComponent 8 /Columns '.$info['w'];
		$smask = array('w'=>$info['w'], 'h'=>$info['h'], 'cs'=>'DeviceGray', 'bpc'=>8, 'f'=>$info['f'], 'dp'=>$dp, 'data'=>$info['smask']);
		$this->_putimage($smask);
	}
	// Palette
	if($info['cs']=='Indexed')
	{
		$this->_newobj();
		$pal = ($this->compress) ? gzcompress($info['pal']) : $info['pal'];
		$this->_put('<</Filter /FlateDecode /Length '.strlen($pal).'>>');
		$this->_put($pal);
		$this->_put('endobj');
	}
}

protected function _putxobjectdict()
{
	foreach($this->images as $image)
		$this->_put('/I'.$image['i'].' '.$image['n'].' 0 R');
}

protected function _putresourcedict()
{
	$this->_put('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_put('/Font <<');
	foreach($this->fonts as $font)
		$this->_put('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_put('>>');
	$this->_put('/XObject <<');
	$this->_putxobjectdict();
	$this->_put('>>');
}

protected function _putresources()
{
	$this->_putfonts();
	$this->_putimages();
	// Resource dictionary
	$this->offsets[2] = strlen($this->buffer);
	$this->_put('2 0 obj');
	$this->_put('<<');
	$this->_putresourcedict();
	$this->_put('>>');
	$this->_put('endobj');
	if ($this->unifontb) {
		// Put font files
		foreach($this->FontFiles as $file=>$info)
		{
			if (!isset($info['type']) || $info['type']!='TTF')
				continue;

			// Font file embedding
			$this->_newobj();
			$this->FontFiles[$file]['n'] = $this->n;
			$font = file_get_contents($this->fontpath.'unifont/'.$file);
			$compressed = gzcompress($font);
			$this->fonts[$file]['content'] = $compressed;
			$this->fonts[$file]['originalsize'] = strlen($font);

			$this->_put('<</Length '.strlen($compressed));
			$this->_put('/Filter /FlateDecode');
			$this->FontFiles[$file]['length1'] = strlen($font);
			$this->_put('/Length1 '.strlen($font));
			$this->_put('>>');
			$this->_put($compressed);
			$this->_put('endobj');
		}
	}
}

protected function _putinfo()
{
	$this->metadata['Producer'] = 'tFPDF '.tFPDF_VERSION;
	$this->metadata['CreationDate'] = 'D:'.@date('YmdHis');
	foreach($this->metadata as $key=>$value)
		$this->_put('/'.$key.' '.$this->_textstring($value));
}

protected function _putcatalog()
{
	$this->_put('/Type /Catalog');
	$this->_put('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')
		$this->_put('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth')
		$this->_put('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')
		$this->_put('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))
		$this->_put('/OpenAction [3 0 R /XYZ null null '.sprintf('%.2F',$this->ZoomMode/100).']');
	if($this->LayoutMode=='single')
		$this->_put('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')
		$this->_put('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two')
		$this->_put('/PageLayout /TwoColumnLeft');
}

protected function _putheader()
{
	if ($this->n_pages > 1) {
		$this->PDFVersion = '1.4';
		$this->_put('%PDF-'.$this->PDFVersion);
	}
	else {
		$this->_put('%PDF-1.3');
	}
}

protected function _puttrailer()
{
	$this->_put('/Size '.($this->n+1));
	$this->_put('/Root '.$this->n.' 0 R');
	$this->_put('/Info '.($this->n-1).' 0 R');
}

protected function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	// Info
	$this->_newobj();
	$this->_put('<<');
	$this->_putinfo();
	$this->_put('>>');
	$this->_put('endobj');
	// Catalog
	$this->_newobj();
	$this->_put('<<');
	$this->_putcatalog();
	if ($this->n_pages > 1) {
		$this->_put('/PageLabels << /Nums [ 0 << /S /D /P ('.$this->AliasNbPages.')>> ] >>');
	}
	$this->_put('>>');
	$this->_put('endobj');
	// Cross-ref
	$o = strlen($this->buffer);
	$this->_put('xref');
	$this->_put('0 '.($this->n+1));
	$this->_put('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->_put(sprintf('%010d 00000 n ',$this->offsets[$i]));
	// Trailer
	$this->_put('trailer');
	$this->_put('<<');
	$this->_puttrailer();
	$this->_put('>>');
	$this->_put('startxref');
	$this->_put($o);
	$this->_put('%%EOF');
	$this->state = 3;
}
// SOME OF THE PUBLIC METHODS FROM FPDF, MODIFIED TO TAKE UTF-8 INPUT

function _dounderline($x, $y, $txt)
{
	// Underline text
	$up = $this->CurrentFont['up'];
	$ut = $this->CurrentFont['ut'];
	$w = $this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
}

protected function _parsejpg($file)
{
	// Extract info from a JPEG file
	$a = getimagesize($file);
	if(!$a)
		$this->Error('Missing or incorrect image file: '.$file);
	if($a[2]!=2)
		$this->Error('Not a JPEG file: '.$file);
	if(!isset($a['channels']) || $a['channels']==3)
		$colspace = 'DeviceRGB';
	elseif($a['channels']==4)
		$colspace = 'DeviceCMYK';
	else
		$colspace = 'DeviceGray';
	$bpc = isset($a['bits']) ? $a['bits'] : 8;
	$data = file_get_contents($file);
	return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
}

protected function _parsepng($file)
{
	// Extract info from a PNG file
	$f = fopen($file,'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	$info = $this->_parsepngstream($f,$file);
	fclose($f);
	return $info;
}

protected function _parsepngstream($f, $file)
{
	// Check signature
	if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);

	// Read header chunk
	$this->_readstream($f,4);
	if($this->_readstream($f,4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w = $this->_readint($f);
	$h = $this->_readint($f);
	$bpc = ord($this->_readstream($f,1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct = ord($this->_readstream($f,1));
	if($ct==0 || $ct==4)
		$colspace = 'DeviceGray';
	elseif($ct==2 || $ct==6)
		$colspace = 'DeviceRGB';
	elseif($ct==3)
		$colspace = 'Indexed';
	else
		$this->Error('Unknown color type: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f,4);
	$dp = '/Predictor 15 /Colors '.($colspace=='DeviceRGB' ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w;

	// Scan chunks looking for palette, transparency and image data
	$pal = '';
	$trns = '';
	$data = '';
	do
	{
		$n = $this->_readint($f);
		$type = $this->_readstream($f,4);
		if($type=='PLTE')
		{
			// Read palette
			$pal = $this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='tRNS')
		{
			// Read transparency info
			$t = $this->_readstream($f,$n);
			if($ct==0)
				$trns = array(ord(substr($t,1,1)));
			elseif($ct==2)
				$trns = array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
			else
			{
				$pos = strpos($t,chr(0));
				if($pos!==false)
					$trns = array($pos);
			}
			$this->_readstream($f,4);
		}
		elseif($type=='IDAT')
		{
			// Read image data block
			$data .= $this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='IEND')
			break;
		else
			$this->_readstream($f,$n+4);
	}
	while($n);

	if($colspace=='Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	$info = array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'dp'=>$dp, 'pal'=>$pal, 'trns'=>$trns);
	if($ct>=4)
	{
		// Extract alpha channel
		$data = gzuncompress($data);
		$color = '';
		$alpha = '';
		if($ct==4)
		{
			// Gray image
			$len = 2*$w;
			for($i=0;$i<$h;$i++)
			{
				$pos = (1+$len)*$i;
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data,$pos+1,$len);
				$color .= preg_replace('/(.)./s','$1',$line);
				$alpha .= preg_replace('/.(.)/s','$1',$line);
			}
		}
		else
		{
			// RGB image
			$len = 4*$w;
			for($i=0;$i<$h;$i++)
			{
				$pos = (1+$len)*$i;
				$color .= $data[$pos];
				$alpha .= $data[$pos];
				$line = substr($data,$pos+1,$len);
				$color .= preg_replace('/(.{3})./s','$1',$line);
				$alpha .= preg_replace('/.{3}(.)/s','$1',$line);
			}
		}
		unset($data);
		$data = gzcompress($color);
		$info['smask'] = gzcompress($alpha);
		if($this->PDFVersion<'1.4')
			$this->PDFVersion = '1.4';
	}
	$info['data'] = $data;
	return $info;
}

protected function _readstream($f, $n)
{
	// Read n bytes from stream
	$res = '';
	while($n>0 && !feof($f))
	{
		$s = fread($f,$n);
		if($s===false)
			$this->Error('Error while reading stream');
		$n -= strlen($s);
		$res .= $s;
	}
	if($n>0)
		$this->Error('Unexpected end of stream');
	return $res;
}

protected function _readint($f)
{
	// Read a 4-byte integer from stream
	$a = unpack('Ni',$this->_readstream($f,4));
	return $a['i'];
}

protected function _textstring($s)
{
	// Format a text string
	if(!isset($this->UTF8String))
		$this->UTF8String = false;
	if ($this->UTF8String)
		$s = $this->_UTF8toUTF16($s);
	return '('.$this->_escape($s).')';
}

protected function _newobj($n=null)
{
	// Begin a new object
	if($n===null)
		$n = ++$this->n;
	$this->offsets[$n] = strlen($this->buffer);
	$this->_put($n.' 0 obj');
}

protected function _put($s)
{
	// Add a line to the document
	$this->buffer .= $s."\n";
}

function _parseTTF($file) {

	//Read entire file
	$f=fopen($file,"rb");
	if (!$f) { $this->Error("Can't open font file."); }
	$this->font_files[$file]=fread($f,filesize($file));
	fclose($f);

	$this->CurrentFontFile = $file;

	$this->font_widths = array();
	$this->font_widths_str = '';
	$this->FontBBox = array();
	$this->subsets = array();
	$this->T128 = str_pad("",256*256/8,chr(0));

	$numTables = $this->read_ushort();
	$this->read_ushort();
	$this->read_ushort();
	$this->read_ushort();

	$this->seek(12);

	$this->tables = array();
	for ($i=0;$i<$numTables;$i++) {
		$tag = $this->read_tag();
		$this->read_ulong();
		$this->tables[$tag] = array('offset'=>$this->read_ulong(), 'length'=>$this->read_ulong());
	}
	$this->getDesc();
	$this->getCmap();
	$this->getBBox();
	$this->getCharWidths();
}

function seek($pos) {
	$this->unifontb_offset = $pos;
}

function skip($n) {
	$this->unifontb_offset += $n;
}

function read_tag() {
	return $this->read(4);
}

function read_short() {
	$s = $this->read(2);
	$a = (ord($s[0])<<8) + ord($s[1]);
	if ($a & (1 << 15) ) { $a = ($a - (1 << 16)); }
	return $a;
}

function read_ushort() {
	$s = $this->read(2);
	return (ord($s[0])<<8) + ord($s[1]);
}

function read_ulong() {
	$s = $this->read(4);
	// if large uInt32 as an integer, PHP converts it to float!
	return (ord($s[0])*16777216) + (ord($s[1])<<16) + (ord($s[2])<<8) + ord($s[3]);
}

function read($n) {
	if (strlen($this->font_files[$this->CurrentFontFile]) < $this->unifontb_offset+$n)
		$this->Error("Reading out of bounds ($this->CurrentFontFile)");
	$s = substr($this->font_files[$this->CurrentFontFile], $this->unifontb_offset, $n);
	$this->unifontb_offset += $n;
	return $s;
}

function getDesc() {
	$this->seek($this->tables['head']['offset']);
	$this->skip(18);
	$this->FontBBox[0] = $this->read_short();
	$this->FontBBox[1] = $this->read_short();
	$this->FontBBox[2] = $this->read_short();
	$this->FontBBox[3] = $this->read_short();
	$this->skip(26);
	$unitsPerEm = $this->read_ushort();
	$factor = 1000 / $unitsPerEm;
	for ($i=0;$i<4;$i++) { $this->FontBBox[$i] = round($this->FontBBox[$i]*$factor); }
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['FontBBox'] = $this->FontBBox[0]." ".$this->FontBBox[1]." ".$this->FontBBox[2]." ".$this->FontBBox[3];
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['MissingWidth'] = round(600*$factor);

	$this->seek($this->tables['hhea']['offset']+34);
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['Ascent'] = round($this->read_short() * $factor);
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['Descent'] = round($this->read_short() * $factor);

	$this->seek($this->tables['post']['offset']+4);
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['ItalicAngle'] = $this->read_short() + $this->read_ushort()/65536;
	$this->fonts[$this->FontFamily.$this->FontStyle]['up'] = round($this->read_short() * $factor);
	$this->fonts[$this->FontFamily.$this->FontStyle]['ut'] = round($this->read_short() * $factor);
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['CapHeight'] = $this->fonts[$this->FontFamily.$this->FontStyle]['desc']['Ascent'];

	$this->seek($this->tables['OS/2']['offset']+2);
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['StemV'] = round($this->read_short() * $factor);

}

function getBBox() {
	// Seems to get the wrong values for some fonts
	$this->seek($this->tables['head']['offset']+36);
	$xMin = $this->read_short();
	$yMin = $this->read_short();
	$xMax = $this->read_short();
	$yMax = $this->read_short();
	$this->FontBBox = array($xMin, $yMin, $xMax, $yMax);

	$this->seek($this->tables['head']['offset']+18);
	$unitsPerEm = $this->read_ushort();
	$factor = 1000/$unitsPerEm;
	for ($i=0;$i<4;$i++) {
		$this->FontBBox[$i] = round($this->FontBBox[$i]*$factor);
	}
	$this->fonts[$this->FontFamily.$this->FontStyle]['desc']['FontBBox'] = $this->FontBBox[0]." ".$this->FontBBox[1]." ".$this->FontBBox[2]." ".$this->FontBBox[3];
}

function getCmap() {
	$this->seek($this->tables['cmap']['offset']+2);
	$numTables = $this->read_ushort();
	$unicode_offset=0;
	for ($i=0;$i<$numTables;$i++) {
		$platformId = $this->read_ushort();
		$encodingId = $this->read_ushort();
		$offset = $this->read_ulong();
		if (($platformId == 3 && $encodingId == 1) || $platformId == 0) { // Microsoft, Unicode
			$unicode_offset = $offset;
		}
	}
	if ($unicode_offset == 0) { $this->Error("Font ($this->CurrentFontFile) does not have Unicode cmap."); return; }
	$this->seek($this->tables['cmap']['offset']+$unicode_offset);
	$format = $this->read_ushort();
	if ($format != 4) { $this->Error("Font ($this->CurrentFontFile) format $format - unsupported."); return; }
	$this->read_ushort();	// length
	$this->read_ushort();	// language
	$segCount = $this->read_ushort()/2;
	$this->read_ushort();	// searchRange
	$this->read_ushort();	// entrySelector
	$this->read_ushort();	// rangeShift

	for($i=0;$i<$segCount;$i++) { $this->endCount[] = $this->read_ushort(); }
	$this->read_ushort();	// reservedPad
	for($i=0;$i<$segCount;$i++) { $this->startCount[] = $this->read_ushort(); }
	for($i=0;$i<$segCount;$i++) { $this->idDelta[] = $this->read_short(); }	// ????
	$offset = $this->unifontb_offset;
	for($i=0;$i<$segCount;$i++) { $this->idRangeOffset[] = $this->read_ushort(); }
	$this->glyphIdArray_offset = $offset;

}

function getCharWidths() {
	$this->seek($this->tables['hhea']['offset']+34);
	$numberOfHMetrics = $this->read_ushort();
	if ($numberOfHMetrics == 0) {
		$this->Error("Font ($this->CurrentFontFile) does not have horizontal metrics"); return;
	}

	$this->seek($this->tables['hmtx']['offset']);
	$unitsPerEm = $this->tables['head']['offset']+18;
	$this->seek($this->tables['head']['offset']+18);
	$unitsPerEm = $this->read_ushort();
	$factor = 1000/$unitsPerEm;
	$this->font_widths = array();
	for( $i=0;$i<$numberOfHMetrics;$i++) {
		$this->font_widths[$i] = round($this->read_ushort() * $factor);
		$this->read_ushort(); // lsb
	}
	if ($numberOfHMetrics > 1) {
		$last_width = $this->font_widths[$numberOfHMetrics-1];
	}
	else { $last_width = $this->font_widths[0]; }
	// numGlyphs
	$this->seek($this->tables['maxp']['offset']+4);
	$numGlyphs = $this->read_ushort();
	for( $i=$numberOfHMetrics;$i<$numGlyphs;$i++) {
		$this->font_widths[$i] = $last_width;
	}
	$this->fonts[$this->FontFamily.$this->FontStyle]['cw'] = $this->font_widths;
}


function UTF8String($s) {
	if (isset($this->CurrentFont['subset'])) {
		foreach($this->CurrentFont['subset'] as $c=>$v) {
			$s = str_replace(code2utf($c),chr($v+128),$s);
		}
	}
	$this->UTF8String = true;
	$this->Bidi = true;
	if(isset($this->fonts[$this->FontFamily.$this->FontStyle]['cw']))
		$this->font_widths = $this->fonts[$this->FontFamily.$this->FontStyle]['cw'];
	return $s;
}

function getChar($s) {
	//Get next char
	$c = $s[$this->i];
	$this->i++;
	if(ord($c{0})<128)
		return $c;
	if((ord($c{0})>>5)==6)
	{
		$c2 = $s[$this->i];
		$this->i++;
		$c = (chr(ord($c{0}) - 192).chr(ord($c2) - 128));
		return $c;
	}
	if((ord($c{0})>>4)==14)
	{
		$c2 = $s[$this->i];
		$this->i++;
		$c3 = $s[$this->i];
		$this->i++;
		$c = (chr(ord($c{0}) - 224).chr(ord($c2) - 128).chr(ord($c3) - 128));
		return $c;
	}
	if((ord($c{0})>>3)==30)
	{
		$c2 = $s[$this->i];
		$this->i++;
		$c3 = $s[$this->i];
		$this->i++;
		$c4 = $s[$this->i];
		$this->i++;
		$c = (chr(ord($c{0}) - 240).chr(ord($c2) - 128).chr(ord($c3) - 128).chr(ord($c4) - 128));
		return $c;
	}
}


function uni_strlen($s)
{
	if(!function_exists('mb_strlen'))
	{
		// Fallback to self-made function
		$n = 0;
		$len = strlen($s);
		for($i=0;$i<$len;$i++)
		{
			$c = ord($s[$i]);
			if($c < 0x80)
				$n++;
			elseif(($c & 0xE0) == 0xC0)
				$i++;
			elseif(($c & 0xF0) == 0xE0)
				$i += 2;
			elseif(($c & 0xF8) == 0xF0)
				$i += 3;
			elseif(($c & 0xFC) == 0xF8)
				$i += 4;
			elseif(($c & 0xFE) == 0xFC)
				$i += 5;
		}
		return $n;
	}
	else
		return mb_strlen($s,'UTF-8');
}

function uni_substr($s,$i,$l)
{
	if(!function_exists('mb_substr'))
		return substr($s,$i,$l);
	else
		return mb_substr($s,$i,$l,'UTF-8');
}

protected function _puttruetypeunicode($font) {
	//Type0 Font
	//A root font object that contains a CIDFont
	$this->_newobj();
	$this->_put('<</Type /Font');
	$this->_put('/Subtype /Type0');
	$this->_put('/BaseFont /'.$font['name'].'-'.strtoupper($this->enc));
	$this->_put('/Encoding /'.$this->enc);
	$this->_put('/DescendantFonts ['.($this->n+1).' 0 R]');
	$this->_put('>>');
	$this->_put('endobj');

	//CIDFont
	$this->_newobj();
	$this->_put('<</Type /Font');
	$this->_put('/Subtype /CIDFontType2');
	$this->_put('/BaseFont /'.$font['name']);
	$this->_put('/CIDSystemInfo '.($this->n+1).' 0 R');
	$this->_put('/FontDescriptor '.($this->n+2).' 0 R');
	if(isset($font['desc']['MissingWidth']))
		$this->_put('/DW '.$font['desc']['MissingWidth']);
	$this->_put('/W ['.$this->font_widths_str.']');
	$this->_put('/CIDToGIDMap '.($this->n+3).' 0 R');
	$this->_put('>>');
	$this->_put('endobj');

	//CIDSystemInfo
	$this->_newobj();
	$this->_put('<</Registry (Adobe)');
	$this->_put('/Ordering ('.$this->registry['ordering'].')');
	$this->_put('/Supplement '.$this->registry['supplement']);
	$this->_put('>>');
	$this->_put('endobj');

	//Font descriptor
	$this->_newobj();
	$this->_put('<</Type /FontDescriptor');
	$this->_put('/FontName /'.$font['name']);
	$this->_put('/Flags '.$font['desc']['Flags']);
	$this->_put('/FontBBox ['.$this->FontBBox[0].' '.$this->FontBBox[1].' '.$this->FontBBox[2].' '.$this->FontBBox[3].']');
	$this->_put('/ItalicAngle '.$font['desc']['ItalicAngle']);
	$this->_put('/Ascent '.$font['desc']['Ascent']);
	$this->_put('/Descent '.$font['desc']['Descent']);
	$this->_put('/CapHeight '.$font['desc']['CapHeight']);
	$this->_put('/StemV '.$font['desc']['StemV']);
	$this->_put('/FontFile2 '.($this->n+2).' 0 R');
	$this->_put('>>');
	$this->_put('endobj');


	//Font file
	$this->_newobj();
	$this->_put('<</Length '.strlen($font['content']));
	$this->_put('/Filter /FlateDecode');
	$this->_put('/Length1 '.$font['originalsize']);
	$this->_put('>>');
	$this->_put($font['content']);
	$this->_put('endobj');


	//CIDToGIDMap
	// A stream that maps CIDs to the GID of the character shape.
	// We have a straight forward mapping of CIDs to GIDs
	// So we can use an Identity map
	$this->_newobj();
	$this->_put('<</Type /CIDToGIDMap');
	$this->_put('/Name /Identity');
	$this->_put('>>');
	$this->_put('endobj');

}

function AddFontUnicode($family, $style='', $file='')
{
	$this->AddFont($family, $style, $file, true);
}


}
// End of class
?> 