<?php

chdir('../'); // for test folder

require_once('CoopPage.php');

$cp = new CoopPage($debug);
print $cp->pageTop(); 

print '<script type="text/javascript" src="/lib/jsolait/init.js"></script>';

$cp->done();

?>