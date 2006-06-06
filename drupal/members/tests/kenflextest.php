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
<script type="text/javascript">
combobox.serverPage="%s/kenflex.php";
%s;
</script>' , 
       $flexacpath, $flexacpath,
       SID ? 'combobox.SID = "' . SID .'"' : '');


print '<form method="post" action="/phpwork/generaldebug.php">
<p>
<label>Contact:</label>

<div><input type="text" name="search-invitations-lead_id" autocomplete="off" 
onchange="coopSearch(this, \'search-invitations-lead_id\', \'invitations-lead_id\', \'leads\')"/>

<input  type="button" onClick="coopSearch(this, \'search-invitations-lead_id\', \'invitations-lead_id\', \'leads\')" value="Search"/>
&nbsp;<p class="inline" id="status-invitations-lead_id"></p>
<br>

<!-- this is the only part that is already in my qf -->
<select name="invitations-lead_id" onClick="setStatus(\'\')" size=10>
</select>
<!-- end what was already in QF -->
</div>

</p>
<p>
<input type="submit" value="Press here">
</p>
<p id="debug"></p>
</form>';





$cp->done();

?>