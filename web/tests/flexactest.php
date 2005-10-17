<?php

chdir('../'); // for test folder

require_once('CoopPage.php');

$cp = new CoopPage($debug);
print $cp->pageTop(); 
print $cp->topNavigation(); 

//XXX STUPID ../ only for test dir
$flexacpath = '../lib/flexac';

printf('<script src="%s/flexac.js"></script>
<style type="text/css">
@import url(%s/flexac.css);
</style>', $flexacpath, $flexacpath);

// IMPORTANT! go set up the paths in the flexac object
printf('<script type="text/javascript">
flexac.configure(); flexac.config.script="%s/flexac.php";
</script>', $flexacpath);



print '<form method="post" action="/phpwork/generaldebug.php">
<p>
<label>Choose a Province:</label>
<input type="text" name="provincia" autocomplete="off" 
        onfocus="flexacOn(this, \'province\', false);">
</p>
<p>
<input type="submit" value="Press here">
</p>
<p id="debug"></p>
</form>';





$cp->done();

?>