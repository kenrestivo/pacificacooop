<?php

//$Id$
// nifty little util to grab the latest page and display it where's i can see it

print '<a href="latest.php">back to latest logs</a><br />';

print "test results so far:<br />";

print '<pre>';
readfile('tests.log');
print '</pre>';


if ($handle = opendir('.')) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if(strstr($file, 'w3c_report')){
            list($num, $crap) = explode('-', $file);
			printf('<a href="%s">%s</a>&nbsp;<a href="%s">%s</a><br />', 
                   $file, $file,
                   $num . '-death.html',
                   $num . '-death.html');
        }
    }
    closedir($handle);

}


?>