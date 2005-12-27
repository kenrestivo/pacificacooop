<?php

//$Id$

//$debug = -1;


require_once('CoopPage.php');
require_once('CoopMenu.php');
require_once('CoopNewDispatcher.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$cp->buffer($cp->topNavigation());


$disp =& new CoopNewDispatcher(&$cp);

$atd =& $disp->handleStack();


// TODO: Move to cooppage. and call it everywhere
//in case of bug

/// XXX i think this is the WRONG place for this
$cp->buffer($sp= $cp->stackPath());


/////// FINALLY, the page
if(empty($atd)){
    $atd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
}

$cp->buffer(sprintf("<h3>%s</h3>",$atd->obj->fb_formHeaderText));
// NOT WORKING YET $cp->buffer($atd->titleJSHack());

$cp->buffer("\n<hr /></div><!-- end header div -->\n"); //ok, we're logged in. show the rest of the page
$cp->buffer('<div id="centerCol">');


$cp->buffer($disp->dispatch());


if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print $cp->flushBuffer();

$cp->done();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


