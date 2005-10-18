<?php

chdir('../'); // for test folder

require_once('CoopPage.php');

$cp = new CoopPage($debug);
print $cp->pageTop(); 
print $cp->topNavigation(); 


print $cp->selfURL(array('value'=> 'Refresh'));

//XXX STUPID ../ only for test dir
$flexacpath = '../lib/flexac';

printf('<script src="%s/kenflex.js"></script>' , 
       $flexacpath);


print '<form method="post" action="/phpwork/generaldebug.php">
<p>
<label>Contact:</label>
<div><input type="text" name="search-invitations-lead_id" autocomplete="off" />
<input  type="button" onClick="populateBox(this, \'search-invitations-lead_id\', \'invitations-lead_id[]\')" value="Search"/>
<br>
<select name="invitations-lead_id[]" size=10 multiple></div>
</select>

</p>
<p>
<input type="submit" value="Press here">
</p>
<p id="debug"></p>
</form>';





$cp->done();

?>