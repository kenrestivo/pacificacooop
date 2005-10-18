<?php

chdir('../'); // for test folder

require_once('CoopPage.php');

$cp = new CoopPage($debug);
print $cp->pageTop(); 
print $cp->topNavigation(); 


print $cp->selfURL(array('value'=> 'Refresh'));

//XXX STUPID ../ only for test dir
$flexacpath = '../lib/flexac';

printf('<script src="%s/kenflex.js"></script>
<style type="text/css">
@import url(%s/flexac.css);
</style>', $flexacpath, $flexacpath);

// IMPORTANT! go set up the paths in the flexac object
printf('<script type="text/javascript">
flexac.configure(); flexac.config.script="%s/flexac.php";
</script>', $flexacpath);



print '<form method="post" action="/phpwork/generaldebug.php">
<p>
<label>Choose a Contact:</label>

<input type="text" name="lead" autocomplete="off" size="50"
        onfocus="flexacOn(this, \'leads\', false);">

<select name="lead_id[]" size=10 multiple>
</select>

</p>
<p>
<input type="submit" value="Press here">
</p>
<p id="debug"></p>
</form>';





$cp->done();

?>