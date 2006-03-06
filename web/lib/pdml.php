<?php

require_once "PEAR.php";
require_once "fpdf.php";

class PDML extends FPDF {

    var $parserState=0;
    var $final;             // final PDF output.
    var $inPage = false;    // are we in a page yet?
    var $anchors = array(); // map anchor names to internal ids.
    var $href = array();    // stack of current URLs to link to. (shift/unshift)
    var $href_style = array(); // should links be underlined blue? 0/1
    var $font_color = array("000000");   // current text color
    var $font_size  = array("10");   // current font size
    var $font_face  = array("Helvetica");   // current font name
    var $font_mask  = array(); // keep track of stuff so that </font> works right.
    var $B = 0;
    var $I = 0;
    var $U = 0;
    var $left_margin = array( 28.35 ); // 1cm
    var $right_margin = array( 28.35 ); // 1cm
    var $bottom_margin = array( 28.35 ); // 1cm
  var $cell_margin;
    var $div_x = array();
    var $div_y = array();
    var $cell_info; // stuff to keep around until a </cell> shows up
    var $cell_text; // text to put in a cell.
    var $script;    // stuff to put in our script.
    var $header='';
    var $footer='';
    var $parserBreak; // when you want to break parsing, put a stop word here.
    var $multicol = array(); // stack of columns. you never know.
  var $stringAttrs = array();  // attributes of current cell string
  var $a_table = array();          // stack of tables
  var $table;          // current working table
  var $orientation;

  function PDML($orientation,$unit,$papersize)
  {
    // call parent constructor
    $this->FPDF($orientation,$unit,$papersize);
    $this->orientation = $orientation;
  }

    /* Rotation extension. Should go away with 1.6 */
    var $angle=0;
    function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;
        if($y==-1)
            $y=$this->y;
        if($this->angle!=0)
            $this->_out('Q');
        $this->angle=$angle;
        if($angle!=0)
        {
            $angle*=M_PI/180;
            $c=cos($angle);
            $s=sin($angle);
            $cx=$x*$this->k;
            $cy=($this->h-$y)*$this->k;
            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,   $s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }
    function _endpage()
    {
        if($this->angle!=0)
        {
            $this->angle=0;
            $this->_out('Q');
        }
        parent::_endpage();
    }
    /* End of Rotation Extension */

    /* Ellipse extension. Should go away with 1.6 */
    function Circle($x,$y,$r,$style='') {
        $this->Ellipse($x,$y,$r,$r,$style);
    }

    function Ellipse($x,$y,$rx,$ry,$style='D') {
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';
        $lx=4/3*(M_SQRT2-1)*$rx;
        $ly=4/3*(M_SQRT2-1)*$ry;
        $k=$this->k;
        $h=$this->h;
        $this->_out(sprintf('%.2f %.2f m %.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x+$rx)*$k,($h-$y)*$k,
            ($x+$rx)*$k,($h-($y-$ly))*$k,
            ($x+$lx)*$k,($h-($y-$ry))*$k,
            $x*$k,($h-($y-$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$lx)*$k,($h-($y-$ry))*$k,
            ($x-$rx)*$k,($h-($y-$ly))*$k,
            ($x-$rx)*$k,($h-$y)*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c',
            ($x-$rx)*$k,($h-($y+$ly))*$k,
            ($x-$lx)*$k,($h-($y+$ry))*$k,
            $x*$k,($h-($y+$ry))*$k));
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c %s',
            ($x+$lx)*$k,($h-($y+$ry))*$k,
            ($x+$rx)*$k,($h-($y+$ly))*$k,
            ($x+$rx)*$k,($h-$y)*$k,
            $op));
    }
    /* End of Ellipse Extension */

    /* Javascript extension. That may stick here a while. */
    var $javascript;
    var $n_js;
    function IncludeJS($script) {
        $this->javascript.=$script;
    }
    function _putjavascript() {
        $this->_newobj();
        $this->n_js=$this->n;
        $this->_out('<<');
        $this->_out('/Names [(EmbeddedJS) '.($this->n+1).' 0 R ]');
        $this->_out('>>');
        $this->_out('endobj');
        $this->_newobj();
        $this->_out('<<');
        $this->_out('/S /JavaScript');
        $this->_out('/JS '.$this->_textstring($this->javascript));
        $this->_out('>>');
        $this->_out('endobj');
    }
    function _putresources() {
        parent::_putresources();
        if (!empty($this->javascript)) {
            $this->_putjavascript();
        }
        $this->_putbookmarks();
    }
    function _putcatalog() {
        parent::_putcatalog();
        if (isset($this->javascript)) {
            $this->_out('/Names <</JavaScript '.($this->n_js).' 0 R>>');
        }
        if(count($this->outlines)>0)
        {
            $this->_out('/Outlines '.$this->OutlineRoot.' 0 R');
            $this->_out('/PageMode /UseOutlines');
        }
    }
    /* End of Javascript Extension */

    /* Bookmark Extension. Should go away with 1.6 */
    var $outlines=array();
    var $OutlineRoot;
    function Bookmark($txt,$level=0,$y=0)
    {
        if($y==-1)
            $y=$this->GetY();
        $this->outlines[]=array('t'=>$txt,'l'=>$level,'y'=>$y,'p'=>$this->PageNo());
    }
    function _putbookmarks()
    {
        $nb=count($this->outlines);
        if($nb==0)
            return;
        $lru=array();
        $level=0;
        foreach($this->outlines as $i=>$o)
        {
            if($o['l']>0)
            {
                $parent=$lru[$o['l']-1];
                //Set parent and last pointers
                $this->outlines[$i]['parent']=$parent;
                $this->outlines[$parent]['last']=$i;
                if($o['l']>$level)
                {
                    //Level increasing: set first pointer
                    $this->outlines[$parent]['first']=$i;
                }
            }
            else
                $this->outlines[$i]['parent']=$nb;
            if($o['l']<=$level and $i>0)
            {
                //Set prev and next pointers
                $prev=$lru[$o['l']];
                $this->outlines[$prev]['next']=$i;
                $this->outlines[$i]['prev']=$prev;
            }
            $lru[$o['l']]=$i;
            $level=$o['l'];
        }
        //Outline items
        $n=$this->n+1;
        foreach($this->outlines as $i=>$o)
        {
            $this->_newobj();
            $this->_out('<</Title '.$this->_textstring($o['t']));
            $this->_out('/Parent '.($n+$o['parent']).' 0 R');
            if(isset($o['prev']))
                $this->_out('/Prev '.($n+$o['prev']).' 0 R');
            if(isset($o['next']))
                $this->_out('/Next '.($n+$o['next']).' 0 R');
            if(isset($o['first']))
                $this->_out('/First '.($n+$o['first']).' 0 R');
            if(isset($o['last']))
                $this->_out('/Last '.($n+$o['last']).' 0 R');
            $this->_out(sprintf('/Dest [%d 0 R /XYZ 0 %.2f null]',1+2*$o['p'],($this->h-$o['y'])*$this->k));
            $this->_out('/Count 0>>');
            $this->_out('endobj');
        }
        //Outline root
        $this->_newobj();
        $this->OutlineRoot=$this->n;
        $this->_out('<</Type /Outlines /First '.$n.' 0 R');
        $this->_out('/Last '.($n+$lru[0]).' 0 R>>');
        $this->_out('endobj');
    }
    /* End of Bookmark Extension */

    function ParsePDML($pdml) {
        // default font.
        $this->SetFont($this->font_face[0],'',$this->font_size[0]);
        // apply margins
        $this->SetRightMargin($this->right_margin[0]);
        $this->SetLeftMargin($this->left_margin[0]);
        // bottom margin
        $this->SetAutoPageBreak(true, $this->bottom_margin[0]);

        $this->AliasNbPages('&pagecount;');

        $this->_parsePDML($pdml);
        //return $this->final;
    }

    function Header() {
        $this->_parsePDML($this->header);
    }

    function Footer() {
        $this->_parsePDML($this->footer);
    }

    function AcceptPageBreak() {
        if (count($this->multicol>0)) {
            // we need: starting y0, col_width, col_spacing, columns, height, break=page/line
            list($x0, $y0, $width, $height, $spacing, $break, $cols, $current) = $this->multicol[0];
            if ($current<$cols-1) {
                // next column
                $current++;
                $this->multicol[0][7] = $current;
                $x = $x0 + $current * $width;
                $this->SetLeftMargin($x);
                $this->SetX($x);
                $this->SetY($y0);
                return false;
            } else {
                // ok. we need to break.
                $this->SetLeftMargin($x0);
                $this->SetX($x0);
                $this->multicol[0][7] = 0; // $current
                if ($break=="line") {
                    $y0 += $spacing + $height; // y0
                    $this->multicol[0][1] = $y0;
                    $this->SetAutoPageBreak(true, $this->hPt - ( $y0 + $height));
                    $this->SetY($y0);
                    //$this->Write(10,"This sux0rs.");
                    return false;
                } else {
                    // page break.
                    return true;
                }
            }
        } else {
            // no multi-column tag. do the normal thing.
            return true;
        }
    }

    function _parsePDML($pdml) {
        $a = preg_split('/<([^<]*)\/?>/U',$pdml,-1,PREG_SPLIT_DELIM_CAPTURE);
        foreach ($a as $i=>$e) {
            if ($this->parserBreak) {
                if ($i%2==0) {
                    $this->ProcessText($e);
                    continue;
                }
                if (strcasecmp($e,$this->parserBreak)) {
                    $this->ProcessText('<'.$e.'>');
                    continue;
                }
            }
            if ($i%2==0) {
                // text
                $this->ProcessText(pdml_entity_decode(preg_replace(
                    array(
                        '/\s+/',
                        '/&pagenumber;/',
                        '/&title;/',
                        '/&author;/',
                        '/&subject;/',
                        '/&creator;/'
                        ),
                    array(
                        ' ',
                        $this->PageNo(),
                        $this->title,
                        $this->author,
                        $this->subject,
                        $this->creator
                    ) ,$e)));
            } else {
                // tag
                if ($e{0}=='/') {
                    $this->CloseTag(strtoupper(substr($e,1)));
                } else {
                    $a2 = explode(' ',$e,2);
                    $tag = strtoupper(array_shift($a2));
                    $e = substr($e, strlen($tag)+1);
                    // stolen from http://www.cs.sfu.ca/~cameron/REX.html
                    // bugified into uselessness. It's the intent that counts. right?
                    $NameStrt = "[A-Za-z_:]|[^\\x00-\\x7F]";
                    $NameChar = "[A-Za-z0-9_:.-]|[^\\x00-\\x7F]";
                    $Name = "(?:$NameStrt)(?:$NameChar)*";
                    $S = "[ \\n\\t\\r]+";
                    $AttValSE = "\"[^\"]*\"|'[^']*'|[^ \\n\\t\\r]+";
                    $ElemTagCE = "($Name)(?:$S)?=(?:$S)?($AttValSE)";

                    $attr = array();
                    preg_match_all("/$ElemTagCE/", $e, $matches, PREG_SET_ORDER);
                    for ($i=0;$i<count($matches);$i++) {
                        $val = $matches[$i][2];
                        if ((($val{0}=='"') and (substr($val,-1)=='"')) or
                            (($val{0}=="'") and (substr($val,-1)=="'")) ){
                            $val = substr($val, 1, strlen($val)-2);
                        }
                        $attr[strtoupper($matches[$i][1])] = $val;
                    }
                    $this->OpenTag($tag,$attr);
                }
            }
        }
    }

    function ProcessText($text) {
        switch ($this->parserState) {
        case 0:
        case 1:
        case 2:
                // ignore text/whitespace in there.
            break;
        case 3:
            $this->SetTitle($text);
            break;
        case 4:
            $this->SetSubject($text);
            break;
        case 5:
                $this->SetAuthor($text);
                break;
        case 6:
                $this->SetKeywords($text);
                break;
        case 51:
            $this->cell_text .= $text;
            break;
        case 54:
            $this->table['tr'][$this->table['rows']]
                [$this->table['current_col']]['text'] .= $text;
        case 10:
            $this->script .= $text;
            break;
        case 63:
            $this->header .= $text;
            break;
        case 64:
            $this->footer .= $text;
            break;
        default:
                // ignore pure whitespace
                if (preg_match("/^[ \\n\\t\\r]*$/",$text)) break;
                // auto-create a page if needed.
                if (!$this->inPage) {
                    $this->inPage = true;
                    $this->AddPage();
                }
                // write stuff.
                // do we have a link to use?
                if (sizeof($this->href)>0) {
                    if ($this->href_style[0]) {
                        $this->SetTextColor(0,0,255);
                        $this->_setStyle('U',true, true);
                    }
                    $this->Write($this->font_size[0],$text,$this->href[0]);
                    if ($this->href_style[0]) {
                        $this->_setStyle('U', $this->U>0, true);
                        $this->_setFontColor($this->font_color[0]);
                    }
                } else {
                    $this->Write($this->font_size[0],$text);
                }
        }
    }

    function OpenTag($tag, $attr) {
        switch ($tag) {
            case "PDML":
                $this->_enforceState(0,1);
                break;
            case "HEAD":
                $this->_enforceState(1,2);
                break;
            case "TITLE":
                $this->_enforceState(2,3);
                break;
            case "SUBJECT":
                $this->_enforceState(2,4);
                break;
            case "AUTHOR":
                $this->_enforceState(2,5);
                break;
            case "KEYWORDS":
                $this->_enforceState(2,6);
                break;
            case "SCRIPT":
                $this->_enforceState(2,10);
                $this->script='';
                $this->parserBreak="/script";
                break;
            case "BODY":
                $this->_enforceState(1,50);
                break;
            case "PAGE":
                $this->_enforceState(50,50);
                $o = "P";
                if (isset($attr["ORIENTATION"])) {
                    if (strcasecmp($attr["ORIENTATION"],"LANDSCAPE")==0) {
                        $o = "L";
                    }
                }
                $this->inPage=true;
                $this->AddPage($o);
        $this->orientation = $o; // save last orientation
                break;
            case "BR":
                // no enforcement.
                if ($this->parserState==51) {
                    $this->cell_text.="\n";
                    break;
                }
                if (isset($attr["HEIGHT"])) {
                    $h=$this->_getUnit($attr["HEIGHT"], $this->font_size[0]);
                    $this->Ln($h);
                } else {
                    $this->Ln($this->font_size[0]);
                }
                break;
            case "A":
                $this->_enforceState(50,50);
                // name=
                if (isset($attr["NAME"])) {
                    // local anchor
                    $name = $attr["NAME"];
                    if (!isset($this->anchors[$name])) {
                        $this->anchors[$name] = $this->AddLink();
                    }
                    $this->SetLink($this->anchors[$name], -1);
                }
                // href=
                if (isset($attr["HREF"])) {
                    $href = $attr["HREF"];
                    if ($href[0]=='#') {
                        // local anchor
                        $href = substr($href,1);
                        if (!isset($this->anchors[$href])) {
                            $this->anchors[$href] = $this->AddLink();
                        }
                        array_unshift ($this->href, $this->anchors[$href]);
                    } else {
                        array_unshift ($this->href, $href);
                    }
                    // we should set style to underlined blue. XXX
                    array_unshift ($this->href_style, isset($attr["HIDDEN"])?0:1);
                }
                break;
            case "B":
            case "I":
            case "U":
        // if we are processing a cell, record the formatting info
        // and add the bolded text
        if($this->parserState == 51) {
          $this->stringAttrs[] =
            array('start'=>strlen($this->cell_text), 'tag'=>$tag);
        } else if($this->parserState == 54) {
          $item = &$this->table['tr'][$this->table['rows']]
                         [$this->table['current_col']];
          $item['stringAttrs'][] = 
             array('tag'=>$tag, 'start'=>strlen($item['text']));
        } else {
                $this->_enforceState(50,50);
                $this->_setStyle($tag, true);
        }
                break;
            case "FONT":
                $this->_enforceState(50,50);
                $mask = 0;
                if (isset($attr["COLOR"])) {
                    // hex-encoded color. no space for long color name list.
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                    array_unshift($this->font_color, $color);
                    $this->_setFontColor($color);
                    $mask |= 1;
                }
                if (isset($attr["SIZE"])) {
                    $size = $this->_getUnit($attr["SIZE"], $this->font_size[0]);
                    array_unshift($this->font_size, $size);
                    $this->SetFontSize($size);
                    $mask |= 2;
                }
                if (isset($attr["FACE"])) {
                    $face = $attr["FACE"];
                    array_unshift($this->font_face, $face);
                    $this->_setFontFace($face);
                    $mask |= 4;
                }
                array_unshift($this->font_mask, $mask);
                break;
            case "IMG":
        if($this->parserState != 54) {
                $this->_enforceState(50,50);
        }

                if (!isset($attr["SRC"])) break;
                $src = $attr["SRC"];

        if($this->parserState != 54) {
                $x = $this->GetX();
                if (isset($attr["LEFT"])) {
                    $x = $this->_getUnit($attr["LEFT"], $this->wPt);
                }
                $y = $this->GetY();
                if (isset($attr["TOP"])) {
                    $y = $this->_getUnit($attr["TOP"], $this->hPt);
                }
                $width = 0;
                if (isset($attr["WIDTH"])) {
                    $width = $this->_getUnit($attr["WIDTH"], $this->wPt);
                }
                $height = 0;
                if (isset($attr["HEIGHT"])) {
                    $height = $this->_getUnit($attr["HEIGHT"], $this->hPt);
                }
        }
                // try to resolve src a bit if necessary.
                if (!file_exists($src)) {
                    $src1 = getenv("DOCUMENT_ROOT")."/".$src;
                    $src2 = dirname(getenv("SCRIPT_FILENAME"))."/".$src;
                    if (file_exists($src1)) {
                        $src = $src1;
                    } elseif (file_exists($src2)) {
                        $src = $src2;
                    }
                }
        // Draw immediately unless inside a table
        if($this->parserState != 54) {
                if (sizeof($this->href)>0) {
                    $this->Image($src, $x, $y, $width, $height, '', $this->href[0]);
                } else {
                    $this->Image($src, $x, $y, $width, $height);
                }
        } else {
          $this->table['tr'][$this->table['rows']][$this->table['current_col']]['img'] = array('src'=>$src);
        }
                break;
            case "LINE":
                $this->_enforceState(50,50);
                $x1 = $this->GetX();
                $y1 = $this->GetY();
                if (isset($attr["FROM"])) {
                    list($x1,$y1) = explode(',',$attr["FROM"]);
          if($x1 == '.') { $x1 = $this->GetX(); } 
          else { $x1 = $this->_getUnit($x1, $this->wPt); }
          if($y1 == '.') { $y1 = $this->GetY(); }
          else { $y1 = $this->_getUnit($y1, $this->hPt); }
                }
                $x2 = $this->wPt;
                $y2 = $y1;
                if (isset($attr["TO"])) {
                    list($x2,$y2) = explode(',',$attr["TO"]);
          if($x2 == '.') { $x2 = $this->GetX(); } 
          else { $x2 = $this->_getUnit($x2, $this->wPt); }
          if($y2 == '.') { $y2 = $this->GetY(); }
          else { $y2 = $this->_getUnit($y2, $this->hPt); }
                }
                $color = "000000";
                if (isset($attr["COLOR"])) {
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                }
                $lwidth = $this->_getUnit("0.2mm");
                if (isset($attr["WIDTH"])) {
                    $lwidth = $this->_getUnit($attr["WIDTH"], $lwidth);
                }
                $this->_setLineColor($color);
                $this->SetLineWidth($lwidth);
                $this->Line($x1,$y1,$x2,$y2);
                break;
            case "RECT":
                $this->_enforceState(50,50);
                $x1 = $this->GetX();
                $y1 = $this->GetY();
                if (isset($attr["FROM"])) {
                    list($x1,$y1) = explode(',',$attr["FROM"]);
                    $x1 = $this->_getUnit($x1, $this->wPt);
                    $y1 = $this->_getUnit($y1, $this->hPt);
                }
                if (isset($attr["LEFT"])) {
                    $x1 = $this->_getUnit($attr["LEFT"], $this->wPt);
                }
                if (isset($attr["TOP"])) {
                    $y1 = $this->_getUnit($attr["TOP"], $this->hPt);
                }
                $width= 144;
                $height= 36;
                if (isset($attr["TO"])) {
                    list($x2,$y2) = explode(',',$attr["TO"]);
                    $x2 = $this->_getUnit($x2, $this->wPt);
                    $y2 = $this->_getUnit($y2, $this->hPt);
                    $width  = $x2-$x1+1;
                    $height = $y2-$y1+1;
                }
                if (isset($attr["WIDTH"])) {
                    $width = $this->_getUnit($attr["WIDTH"], $this->wPt);
                }
                if (isset($attr["HEIGHT"])) {
                    $height = $this->_getUnit($attr["HEIGHT"], $this->hPt);
                }
                $style="";
                $color = "000000";
                if (isset($attr["COLOR"])) {
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                    $style.="D";
                }
                $fill = "000000";
                if (isset($attr["FILLCOLOR"])) {
                    $fill = $attr["FILLCOLOR"];
                    if ($fill[0]=="#") { $fill = substr($fill,1); }
                    $style.="F";
                }
                $border = $this->_getUnit("0.2mm");
                if (isset($attr["BORDER"])) {
                    $border = $this->_getUnit($attr["BORDER"], $border);
                }
                $this->_setLineColor($color);
                $this->SetLineWidth($border);
                $this->_setRectColor($fill);
                $this->Rect($x1,$y1,$width,$height,$style);
                break;
            case "CIRCLE":
            case "ELLIPSE":
                $this->_enforceState(50,50);
                $x1 = $this->GetX();
                $y1 = $this->GetY();
                if (isset($attr["FROM"])) {
                    list($x1,$y1) = explode(',',$attr["FROM"]);
                    $x1 = $this->_getUnit($x1, $this->wPt);
                    $y1 = $this->_getUnit($y1, $this->hPt);
                }
                $radius = $this->_getAttrUnit($this->font_size[0], $attr, "RADIUS", $this->font_size[0]);
                $xradius = $this->_getAttrUnit($radius, $attr, "XRADIUS", $this->font_size[0]);
                $yradius = $this->_getAttrUnit($radius, $attr, "YRADIUS", $this->font_size[0]);
                $style="";
                $color = "000000";
                if (isset($attr["COLOR"])) {
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                    $style.="D";
                }
                $fill = "000000";
                if (isset($attr["FILLCOLOR"])) {
                    $fill = $attr["FILLCOLOR"];
                    if ($fill[0]=="#") { $fill = substr($fill,1); }
                    $style.="F";
                }
                $border = $this->_getUnit("0.2mm");
                if (isset($attr["BORDER"])) {
                    $border = $this->_getUnit($attr["BORDER"], $border);
                }
                $this->_setLineColor($color);
                $this->SetLineWidth($border);
                $this->_setRectColor($fill);
                $this->Ellipse($x1, $y1, $xradius, $yradius, $style);
                break;
            case "DIV":
                $this->_enforceState(50,50);
                $save_x = $this->GetX();
                $x = $this->_getAttrUnit($save_x, $attr, "LEFT", $this->wPt);
                $save_y = $this->GetY();
                $y = $this->_getAttrUnit($save_y, $attr, "TOP", $this->hPt);
                $width = $this->_getAttrUnit($this->wPt-$x, $attr, "WIDTH", $this->wPt);
                $height = $this->_getAttrUnit($this->hPt-$y, $attr, "HEIGHT", $this->hPt);
                if ($x == $save_x) { $save_x+=$width; }
                array_unshift($this->left_margin, $x);
                array_unshift($this->right_margin, $this->wPt-$width-$x);
                array_unshift($this->div_x, $save_x);
                array_unshift($this->div_y, $save_y);
                // draw a rect, just to debug. XXX
                $style="";
                $color = "000000";
                if (isset($attr["COLOR"])) {
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                    $style.="D";
                }
                $fill = "000000";
                if (isset($attr["FILLCOLOR"])) {
                    $fill = $attr["FILLCOLOR"];
                    if ($fill[0]=="#") { $fill = substr($fill,1); }
                    $style.="F";
                }
                $border = $this->_getUnit("0.2mm");
                if (isset($attr["BORDER"])) {
                    $border = $this->_getUnit($attr["BORDER"], $border);
                }
                $this->_setLineColor($color);
                $this->SetLineWidth($border);
                $this->_setRectColor($fill);
                if ($style) {
                    $this->Rect($x,$y,$width,$height,$style);
                }
                $this->SetLeftMargin($this->left_margin[0]);
                $this->SetRightMargin($this->right_margin[0]);
                $this->SetXY($x,$y);
                break;
            case "MULTICELL":
            case "CELL":
                $this->_enforceState(50,51);
                $save_x = $this->GetX();
                $x = $this->_getAttrUnit($save_x, $attr, "LEFT", $this->wPt);
                $save_y = $this->GetY();
                $y = $this->_getAttrUnit($save_y, $attr, "TOP", $this->hPt);
                $width = $this->_getAttrUnit($this->wPt-$x, $attr, "WIDTH", $this->wPt);
                // used by multicell only
                $inter = $this->_getAttrUnit($this->font_size[0], $attr, "INTER", $this->font_size[0]);
                // used by cell only.
                $height = $this->_getAttrUnit($this->font_size[0], $attr, "HEIGHT", $this->font_size[0]);
                $next = 0;
                if (isset($attr["NEXT"])) {
                    $n = strtolower($attr["NEXT"]);
                    switch ($n) {
                        case "right": $next =0; break;
                        case "bottom": case "down": $next=2; break;
                        case "break": $next = 1; break;
                    }
                }
                $style="";
                $color = "000000";
                if (isset($attr["COLOR"])) {
                    $color = $attr["COLOR"];
                    if ($color[0]=="#") { $color = substr($color,1); }
                    $style.="D";
                }
                $fillflag = 0;
                $fill = "000000";
                if (isset($attr["FILLCOLOR"])) {
                    $fill = $attr["FILLCOLOR"];
                    if ($fill[0]=="#") { $fill = substr($fill,1); }
                    $fillflag=1;
                }
                $borderflag=0;
                $border = $this->_getUnit("0.2mm");
                if (isset($attr["BORDER"])) {
                    $border = $this->_getUnit($attr["BORDER"], $border);
                    $borderflag=1;
                }
                $align = ($tag=="CELL")?"L":"J";
                if (isset($attr["ALIGN"])) {
                    $al = strtolower($attr["ALIGN"]);
                    switch ($al){
                        case "left": $align="L"; break;
                        case "center": $align="C"; break;
                        case "right": $align="R"; break;
                        case "justify": $align="J"; break;
                    }
                }
                $this->_setLineColor($color);
                $this->SetLineWidth($border);
                $this->_setRectColor($fill);
                $this->SetXY($x,$y);
                $this->cell_info = array($width, $inter, $height, $borderflag, $align, $fillflag, $next);
                $this->cell_text = '';
                break;
            case "ROTATE":
                $angle=45;
                if (isset($attr["ANGLE"])) {
                    $angle = $attr["ANGLE"];
                }
                $x=-1;
                $y=-1;
                if (isset($attr["CENTER"])) {
                    list($x,$y) = explode(',',$attr["CENTER"]);
                    $x = $this->_getUnit($x, $this->wPt);
                    $y = $this->_getUnit($y, $this->hPt);
                }
                $this->rotate($angle, $x, $y);
                break;
            case "BOOKMARK":
                if (!isset($attr["TITLE"])) break;
                $title = $attr["TITLE"];
                $level = 0;
                if (isset($attr["LEVEL"])) {
                    $level = $attr["LEVEL"];
                }
                $top = -1;
                if (isset($attr["TOP"])) {
                    $top = $this->_getUnit($attr["TOP"], $this->hPt);
                }
                $this->Bookmark($title, $level, $top);
                break;
            case "HEADER":
                $this->_enforceState(50,63);
                $this->header='';
                $this->parserBreak='/header';
                break;
            case "FOOTER":
                $this->_enforceState(50,64);
                $this->footer='';
                $this->parserBreak='/footer';
                break;
            case "COLUMN":
                $this->_enforceState(50,50);
                $save_x = $this->GetX();
                $x = $this->_getAttrUnit($save_x, $attr, "LEFT", $this->wPt);
                $save_y = $this->GetY();
                $y = $this->_getAttrUnit($save_y, $attr, "TOP", $this->hPt);
                $count = 2;
                if (isset($attr["COUNT"])) {
                    $count = (int)$attr["COUNT"];
                }
                $width = $this->_getAttrUnit(($this->wPt-$x)/$count, $attr, "WIDTH", $this->wPt);
                $height = $this->_getAttrUnit($this->hPt-$y, $attr, "HEIGHT", $this->hPt);
                $spacing = $this->_getAttrUnit($this->font_size[0], $attr, "SPACING", $this->font_size[0]);
                $break = "page";
                if (isset($attr["BREAK"]) and (strtolower($attr["BREAK"])=="line")) {
                    $break = "line";
                }
                // store stuff for acceptPageBreak to make sense of, and stuff we're saving
                array_unshift($this->multicol, array($x,$y,$width,$height,$spacing, $break, $count, 0, ));
                array_unshift($this->left_margin, $x);
                array_unshift($this->bottom_margin, $this->hPt-($y+$height));
                // set margins to make things work.
                $this->SetLeftMargin($this->left_margin[0]);
                $this->SetAutoPageBreak(true, $this->bottom_margin[0]);
                break;
      case "TABLE":
        // nested table
        if($this->parserState == 54) {
          $this->_enforceState(54,52);
          array_push($this->a_table,$this->table);
        } else {
          $this->_enforceState(50,52);
        }
        $this->table = array('attr'=>$attr,'rows'=>0,'cols'=>0,
                             'current_col'=>0,'tr'=>array());
        break;
      case "TR":
        $this->_enforceState(52,53);
        $this->table['tr'][$this->table['rows']] = array('attr'=>$attr);
        $this->table['current_col'] = 0;
        break;
      case "TD":
        $this->_enforceState(53,54);
        $this->table['tr'][$this->table['rows']]
              [$this->table['current_col']]['attr'] = $attr;
        break;
        }
    }

    function CloseTag($tag) {
        switch ($tag) {
            case "PDML":
                $this->_enforceState(1,0);
                //$this->final = $this->Output("","S");
                break;
            case "HEAD":
                $this->_enforceState(2,1);
                break;
            case "TITLE":
                $this->_enforceState(3,2);
                break;
            case "SUBJECT":
                $this->_enforceState(4,2);
                break;
            case "AUTHOR":
                $this->_enforceState(5,2);
                break;
            case "KEYWORDS":
                $this->_enforceState(6,2);
                break;
            case "SCRIPT":
                $this->_enforceState(10,2);
                $this->IncludeJS($this->script);
                $this->parserBreak='';
                break;
            case "BODY":
                $this->Close();
                $this->_enforceState(50,1);
                break;
            case "A":
                array_shift ($this->href);
                array_shift ($this->href_style);
                // we should remove underline + blue.
                break;
            case "B":
            case "I":
            case "U":
        if($this->parserState == 51) {
          // find the last item of tag
          for($jj=count($this->stringAttrs)-1;$jj >= 0; $jj--) {
            if($this->stringAttrs[$jj]['tag'] == $tag) {
              $this->stringAttrs[$jj]['end'] = strlen($this->cell_text)-1;
              break;
            }
          }
        } else if($this->parserState == 54) {
          $item = &$this->table['tr'][$this->table['rows']][$this->table['current_col']];
          // find the last item of tag
          for($jj=count($item['stringAttrs'])-1;$jj >= 0; $jj--) {
            if($item['stringAttrs'][$jj]['tag'] == $tag) {
              $item['stringAttrs'][$jj]['end'] = strlen($item['text'])-1;
              break;
            }
          }
        } else {
                $this->_setStyle($tag, false);
        }
                break;
            case "FONT":
                if (sizeof($this->font_mask)>0) {
                    $mask = array_shift($this->font_mask);
                }
                if (($mask&1)==1) {
                    array_shift($this->font_color);
                    $this->_setFontColor($this->font_color[0]);
                }
                if (($mask&2)==2) {
                    array_shift($this->font_size);
                    $this->SetFontSize($this->font_size[0]);
                }
                if (($mask&4)==4) {
                    array_shift($this->font_face);
                    $this->_setFontFace($this->font_face[0]);
                }
                break;
            case "DIV":
                if (sizeof($this->div_x)<1) break;
                array_shift($this->left_margin);
                array_shift($this->right_margin);
                $this->SetLeftMargin($this->left_margin[0]);
                $this->SetRightMargin($this->right_margin[0]);
                $this->SetXY($this->div_x[0],$this->div_y[0]);
                array_shift($this->div_x);
                array_shift($this->div_y);
                break;
            case "MULTICELL":
                $this->_enforceState(51,50);
                // auto-create a page if needed.
                if (!$this->inPage) {
                    $this->inPage = true;
                    $this->AddPage();
                }
                $this->MultiCell(
                    $this->cell_info[0],
                    $this->cell_info[1],
                    $this->cell_text,
                    $this->cell_info[3],
                    $this->cell_info[4],
                    $this->cell_info[5]);

        // clear string attributes
        $this->stringAttrs = array();
                break;
            case "CELL":
                $this->_enforceState(51,50);
                // auto-create a page if needed.
                if (!$this->inPage) {
                    $this->inPage = true;
                    $this->AddPage();
                }
                // redo the link logic here. blah.
                if (sizeof($this->href)>0) {
                    if ($this->href_style[0]) {
                        $this->SetTextColor(0,0,255);
                        $this->_setStyle('U',true, true);
                    }
                    $this->Cell(
                        $this->cell_info[0],
                        $this->cell_info[2],
                        $this->cell_text,
                        $this->cell_info[3],
                        $this->cell_info[6],
                        $this->cell_info[4],
                        $this->cell_info[5],
            $this->href[0],
            $this->stringAttrs);
                    if ($this->href_style[0]) {
                        $this->_setStyle('U', $this->U>0, true);
                        $this->_setFontColor($this->font_color[0]);
                    }
                } else {
                    $this->Cell(
                        $this->cell_info[0],
                        $this->cell_info[2],
                        $this->cell_text,
                        $this->cell_info[3],
                        $this->cell_info[6],
                        $this->cell_info[4],
            $this->cell_info[5],
            '',
            $this->stringAttrs);
                }
        
        
        // clear string attributes
        $this->stringAttrs = array();
                break;
            case "ROTATE":
                $this->rotate(0);
                break;
            case "HEADER":
                $this->_enforceState(63,50);
                $this->parserBreak='';
                break;
            case "FOOTER":
                $this->_enforceState(64,50);
                $this->parserBreak='';
                break;
            case "COLUMN":
                $this->_enforceState(50,50);
                array_shift($this->multicol);
                array_shift($this->left_margin);
                array_shift($this->bottom_margin);
                $this->SetLeftMargin($this->left_margin[0]);
                $this->SetAutoPageBreak(true, $this->bottom_margin[0]);
                break;
      case "TABLE":
        if(count($this->a_table) > 0) {
          $this->_enforceState(52,54);
        } else {
          $this->_enforceState(52,50);
        }
        // render table
        if(!count($this->a_table)) {
          $this->render_table($this->table);
        } else {
          $table = &$this->a_table[count($this->a_table)-1];
          $table['tr'][$table['rows']][$table['current_col']]['table']
            = $this->table;
          $this->table = array_pop($this->a_table);
        }
        break;
      case "TR":
        $this->_enforceState(53,52);
        $this->table['rows']++;
        break;
      case "TD":
        $this->_enforceState(54,53);
        $colspan = max(intval(@$this->table['tr'][$this->table['rows']][$this->table['current_col']]['attr']['COLSPAN']),1);

        for($jj=0;$jj<$colspan-1;$jj++) {
          $this->table['current_col']++;
          $this->table['tr'][$this->table['rows']][$this->table['current_col']]
            = array();
        }
        $this->table['current_col']++;
        $this->table['cols'] = max($this->table['cols'],$this->table['current_col']);
        break;
        }
    }

    function _enforceState($from, $to) {
        if ($this->parserState!=$from) {
            PEAR::raiseError("unexpected tag (from $from to $to, but state=".$this->parserState.")", 999);
        //$this->Write("[unexpected tag (from $from to $to)]");
        }
        $this->parserState=$to;
    }

    // default is pt. works good for fonts, so yeah.
    function _getUnit($str, $max=100) {
        $str=rtrim($str);
        $v=(float)$str;
        if (substr($str,-1)=='%') {
            return $max * $v / 100;
        }
        $u=substr($str,-2);
        switch ($u) {
            default:
            case "pt": $m=1; break;
            case "mm": $m=72/25.4; break;
            case "cm": $m=72/2.54; break;
            case "in": $m=72; break;
        }
        return $v * $m;
    }

    function _getAttrUnit($default, $attr, $name, $ref) {
        if (isset($attr[$name])) {
            return $this->_getUnit($attr[$name], $ref);
        } else {
            return $default;
        }
    }

    function _setStyle($tag, $enable, $forget=0) {
        $this->$tag+=($enable ? 1: -1);
        $style='';
        foreach(array('B','I','U') as $s) {
            if ($this->$s>0) {
                $style.=$s;
            }
        }
        $this->SetFont('',$style);
        if ($forget) {
            $this->$tag-=($enable ? 1: -1);
        }
    }

    function _setFontFace($face) {
        $style='';
        foreach(array('B','I','U') as $s) {
            if ($this->$s>0) {
                $style.=$s;
            }
        }
        $this->SetFont($face,$style);
    }

    function _setFontColor($hex) {
        $this->SetTextColor(
            hexdec(substr($hex,0,2)),
            hexdec(substr($hex,2,2)),
            hexdec(substr($hex,4,2)));
    }

    function _setLineColor($hex) {
        $this->SetDrawColor(
            hexdec(substr($hex,0,2)),
            hexdec(substr($hex,2,2)),
            hexdec(substr($hex,4,2)));
    }

    function _setRectColor($hex) {
        $this->SetFillColor(
            hexdec(substr($hex,0,2)),
            hexdec(substr($hex,2,2)),
            hexdec(substr($hex,4,2)));
    }


  function describe_table(&$table)
  {
    $a_col_width = array();
    $a_row_height = array();
    $a_width = array(); // width of each cell

    for($jj=0; $jj<$table['cols']; $jj++) { 
      $a_col_width[$jj] = 0;
    }
    for($jj=0; $jj<$table['rows']; $jj++) { 
      $a_row_height[$jj] = 0;
    }

    if(isset($table['attr']['CELLPADDING'])) {
      $table['cMargin'] = $this->_getUnit($table['attr']['CELLPADDING']);
    } else {
      $table['cMargin'] = $this->cMargin;
    }
    
    // Determine column width and row height
    for($jj=0; $jj<$table['rows']; $jj++) {
      $a_width[$jj] = array();
      for($kk=0; $kk<$table['cols']; $kk++) {
        if(!isset($table['tr'][$jj][$kk])) continue;
        $cell = &$table['tr'][$jj][$kk];
        $width = 0;
        $this->createTextArray($cell['text'],
                               $cell['stringAttrs'],
                               $width);
        if($width) $width += 2 * $table['cMargin'];

        $colspan = min(max(1,intval($cell['attr']['COLSPAN'])),
                       $table['cols']-$kk);
        // TODO: calculate actual height
        $a_row_height[$jj] = max($a_row_height[$jj],
                                 $this->font_size[0] + 2*$table['cMargin']);

        if(isset($cell['table'])) 
        {
          $this->describe_table($cell['table']);
          $a_row_height[$jj] = max($a_row_height[$jj],
                                   $cell['table']['height']
				   + 2*$cell['table']['cMargin']);
          $width = max($width, $cell['table']['width']
	                       + 2*$cell['table']['cMargin']);
        }

        if(isset($cell['img'])) {
          $info = GetImageSize($cell['img']['src']);

          //Put image at 72 dpi
          $w=$info[0]/$this->k;
          $h=$info[1]/$this->k;
          $cell['img']['width'] = $w;
          $cell['img']['height'] = $h;

          $width = max($width, $w+2*$table['cMargin']);
          $a_row_height[$jj] = max($a_row_height[$jj], $h+2*$table['cMargin']);
        }
        if(isset($cell['attr']['WIDTH'])) {
          $width=$this->_getUnit($cell['attr']['WIDTH'],
                                 $this->wPt-$this->lMargin-$this->rMargin);
        }
        $a_width[$jj][$kk]['w'] = $width;
        $a_width[$jj][$kk]['c'] = $colspan;

        $a_col_width[$kk] = max($a_col_width[$kk], $width);
      }
      if($table['tr'][$jj]['attr']['UNDERLINE']) {
        $a_col_height[$jj] += 3;
      }
    }

    // Go back through table and handle colspan's
    for($jj=0; $jj<$table['rows']; $jj++) {
      for($kk=0; $kk<$table['cols']; $kk++) {
        if(!isset($table['tr'][$jj][$kk])) continue;
        $cell = &$table['tr'][$jj][$kk];

        $colspan = min(intval($cell['attr']['COLSPAN']),$table['cols']-$kk);
        if($colspan > 1) {
          // columns that are spanned should be readjusted for width

          // recompute widths of columns without this cell
          $a_w = array();
          for($ii=0; $ii<$table['rows']; $ii++) {
            for($nn=$kk; $nn<$colspan+$kk; $nn++) {
              if($ii != $jj || $nn != $kk) {
                $a_w[$nn] = max($a_w[$nn],$a_width[$ii][$nn]['w']);
              }
            }
          }

          $sum = 0;
          for($nn=$kk; $nn<$colspan+$kk; $nn++) {
            if(isset($a_w[$nn])) {
              $a_col_width[$nn] = $a_w[$nn];
              $sum += $a_w[$nn];
            }
          }
          // we need to distribute the slack across the other cells
          if($a_width[$jj][$kk]['w'] > $sum) {
            $slack = ($a_width[$jj][$kk]['w'] - $sum)/$colspan;
            for($nn=$kk; $nn<$colspan+$kk; $nn++) {
              $a_col_width[$nn] += $slack;
            }
          }
        }
      }
    }

    $width = 0;
    for($jj=0; $jj<$table['cols']; $jj++) { 
      $width += $a_col_width[$jj];
    }
    $height = 0;
    for($jj=0; $jj<$table['rows']; $jj++) { 
      $height += $a_row_height[$jj];
    }
    $table['width'] = $width;
    $table['height'] = $height;
    $table['described'] = true;
    $table['col_widths'] = $a_col_width;
    $table['row_heights'] = $a_row_height;
  }

  function render_table($table)
  {
    // auto-create a page if needed.
    if (!$this->inPage) {
      $this->inPage = true;
      $this->AddPage();
    }

    if(!$table['described']) {
      $this->describe_table($table);
    }

    // Actually render each cell
    for($jj=0; $jj<$table['rows']; $jj++) {
      $rowx = $this->GetX();

      // Determine if we should break the page

      // no room, so we have to break the page
      if(($this->GetY() + $table['row_heights'][$jj]
           + $this->bottom_margin[0]) > $this->hPt) {
        $this->AddPage($this->orientation);
        $this->render_table_header($table);
      }
      // check to see if this is a "suggested" page break
      if($table['tr'][$jj]['attr']["BREAK"]) {
        $h = 0;
        for($kk=$jj; $kk<$table['rows']; $kk++) {
          if($kk>$jj && $table['tr'][$kk]['attr']["BREAK"]) break;
          $h += $table['row_heights'][$kk];
        }
        if(($this->GetY() + $h + $this->bottom_margin[0]) > $this->hPt) {
          $this->AddPage($this->orientation);
          $this->render_table_header($table);
        }
      }
      // render this row
      $this->render_row($table, $jj);

    }
  }

  function render_table_header(&$table)
  {
    for($jj=0; $jj<$table['rows']; $jj++) {
      if($table['tr'][$jj]['attr']['HEADER']) {
        $this->render_row($table,$jj);
      }
    }
  }

  function render_row(&$table, $j_row)
  {
    $row = &$table['tr'][$j_row];
    $rowx = $this->GetX();

    for($kk=0; $kk<$table['cols']; $kk++) {
      if(!isset($row[$kk])) continue;
      $cell = &$row[$kk];
 
      if(!isset($cell['text']) && !isset($cell['table'])) continue;
        
      $col_width = $table['col_widths'][$kk];

      $colspan = min(intval($cell['attr']['COLSPAN']),$table['cols']-$kk);
      if($colspan > 1)
      {
        $col_width = 0;
        for($ii=$kk; $ii<$colspan+$kk; $ii++) {
          $col_width += $table['col_widths'][$ii];
        }
      }

      if(isset($cell['table']) || isset($cell['img'])) { 
        $x = $this->GetX();
        $y = $this->GetY();


        if(isset($cell['table'])) {
          $w = $cell['table']['width'];
          $h = $cell['table']['height'];
        } else {
          $w = $cell['img']['width'];
          $h = $cell['img']['height'];
        }

        // WARNING: it is important to set Y first!
        if(!strcasecmp($cell['attr']['VALIGN'],"bottom")) {
          $y2 = $y + $table['row_heights'][$j_row] - $h - $table['cMargin'];
        } else if(!strcasecmp($cell['attr']['VALIGN'],"middle")) {
          $y2 = $y + $table['row_heights'][$j_row]/2 - $h/2;
        } else {
          $y2 = $y + $table['cMargin'];
        }

        if(!strcasecmp($cell['attr']['ALIGN'],"right")) {
          $x2 = $x + $col_width - $w - $table['cMargin'];
        } else if(!strcasecmp($cell['attr']['ALIGN'],"center")) {
          $x2 = $x + $col_width/2 - $w/2;
        } else {
          $x2 = $x + $table['cMargin'];
        }

        if(isset($cell['table'])) {
          $this->SetXY($x2,$y2);
          $this->render_table($cell['table']);
          $this->SetXY($x,$y);
        } else {
          $this->Image($cell['img']['src'],$x2,$y2);
        }
      }
      $old_margin = $this->cMargin;
      $this->cMargin = $table['cMargin'];
      $this->Cell($col_width,
                  $table['row_heights'][$j_row],
                  $cell['text'],
                  $table['attr']['BORDER'],
                  0,
                  strtoupper(@$cell['attr']['ALIGN']{0}),
                  0,
                  '',
                  $cell['stringAttrs']);

      // restore original cell margin
      $this->cMargin = $old_margin;
    }
    $x = $this->GetX();
    $this->Ln($table['row_heights'][$j_row]);
    $this->SetX($rowx);
    if($row['attr']['UNDERLINE']) {
      $this->SetLineWidth(doubleval($row['attr']['UNDERLINE']));
      if(!(strpos(strtolower($row['attr']['USTYLE']),"dashed") === false)) {
        $dash = true;
      }
      $this->Line($this->GetX(),$this->GetY()+1,$x,$this->GetY()+1,$dash);
      $this->Ln(3);
    }
  }
}


function ob_pdml($buffer) {
  $pdml = new PDML('P','pt','Letter'); // P and Letter should be customizable. XXX
  $pdml->compress=0;
  $pdml->ParsePDML($buffer);
  $s = $pdml->Output("","S");
  Header('Content-Type: application/pdf');
  Header('Content-Length: '.strlen($s));
  Header('Content-disposition: inline; filename=doc.pdf');
  return $s;
}

function pdml_entity_decode( $given_html, $quote_style = ENT_QUOTES ) {
   $trans_table = array_flip(array_merge(
     get_html_translation_table( HTML_SPECIALCHARS, $quote_style ),
     get_html_translation_table( HTML_ENTITIES, $quote_style) ));
   $trans_table['&#39;'] = "'";
   return ( strtr( $given_html, $trans_table ) );
}



?>
