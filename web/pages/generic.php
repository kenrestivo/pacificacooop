<?php

//$Id$

//$debug = -1;


require_once('CoopPage.php');
require_once('CoopMenu.php');
require_once('CoopNewDispatcher.php');


PEAR::setErrorHandling(PEAR_ERROR_PRINT);


$cp = new coopPage( $debug);
$cp->buffer($cp->pageTop());


$menu =& new CoopMenu(&$cp);
$cp->buffer($menu->topNavigation());




////////////////{{{STACK HANDLING. move to cooppage?
if(!empty($_REQUEST['table'])){
    $atd =& new CoopView(&$cp, $_REQUEST['table'], $none);
    $formatted = array('table'=>$_REQUEST['table'], 
                       'action' =>$_REQUEST['action'] ? 
                       $_REQUEST['action'] : 
                       'view', 
                       'pop' => @$_REQUEST['pop'], 
                       'id' =>$_REQUEST[$atd->prependTable($atd->pk)],
                       'realm' => $_REQUEST['realm'] ? $_REQUEST['realm'] : 
                       $cp->vars['last']['realm']);
}
if(isset($_REQUEST['push'])){
    $cp->printDebug('PUSHING onto the stack!', 1);
    $cp->vars['stack'][] = $cp->vars['last'];
} 

// ALWAYS use formatted as last.... if it exists, that is
// it won't exist in cases where i'm coming back from a header location
if(!empty($formatted)){
    $cp->vars['last'] =  $formatted;
}


/// XXX this is wrong. last should EQUAL request at this stage!
if(isset($cp->vars['last']['pop']) || isset($_REQUEST['pop'])){
    $prev = $cp->popOff();
}

//////////////}}} END STACK HANDLING



// TODO: Move to cooppage. and call it everywhere
//in case of bug
if(!$cp->vars['last']['table']){
    if(devSite()){
        PEAR::raiseError('unspecified table', 555);
    }
    $cp->headerLocation(
                $this->page->selfURL(
                    array('par' => false,
                          'host' => true,
                          'value'=> 'Unspecified table. Go back to home.')));
}

/// XXX i think this is the WRONG place for this
if($sp= $cp->stackPath()){
   $cp->buffer(sprintf('<p>Navigation: %s %s</p>',
                       $sp, 
                       $cp->selfURL(
                           array('value'=>'Go Back',
                                 'par' => false,
                                 'inside' => array(
                                     'pop' => 'true')))));
}


/// SHOW STATUS
// if i popped, my result gets clobberd. so keep track of it here
if(!empty($prev)){
    $status =& $prev;
} else {
    $status =& $cp->vars['last'];
}

if(!empty($status['result'])){
    $cp->buffer("<p>STATUS: {$status['result']}</p>");
}




/////// FINALLY, the page
if(empty($atd)){
    $atd =& new CoopView(&$cp, $cp->vars['last']['table'], $none);
}
$cp->buffer(sprintf("<h3>%s</h3>",$atd->obj->fb_formHeaderText));


$cp->buffer("\n<hr></div><!-- end header div -->\n"); //ok, we're logged in. show the rest of the page
$cp->buffer('<div id="centerCol">');

$disp =& new CoopNewDispatcher(&$cp);
$cp->buffer($disp->dispatch());
print $cp->flushBuffer();

$cp->done();

////KEEP EVERTHANG BELOW

?>


<!-- END GENERIC -->


