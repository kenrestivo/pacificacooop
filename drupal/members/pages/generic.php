<?php

//$Id$

//$debug = -1;


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/Menu.php');
require_once('COOP/NewDispatcher.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$cp->buffer($cp->topNavigation());


$disp =& new CoopNewDispatcher(&$cp);

$atd =& $disp->handleStack();


// TODO: Move to cooppage. and call it everywhere
//in case of bug

/// XXX i think this is the WRONG place for this
// it should get inserted into the page template, then get rendered
// really belongs at the very top header
$cp->buffer($cp->stackPath());


/////// FINALLY, the page
if(empty($atd)){
    $atd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
}

// TODO: bust out topnavigation, then stick title in header
$cp->buffer(sprintf("<h3>%s</h3>",$atd->obj->fb_formHeaderText));

$cp->buffer("\n<hr /></div><!-- end header div -->\n"); //ok, we're logged in. show the rest of the page
$cp->buffer('<div class="centerCol">');


$cp->buffer($disp->dispatch());


if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print $cp->flushBuffer();

$cp->done();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


