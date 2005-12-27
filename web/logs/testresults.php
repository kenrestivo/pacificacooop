<?php

//$Id$
// nifty little util to grab the latest page and display it where's i can see it

if ($handle = opendir('.')) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if(strstr($file, 'w3c_report')){
			printf('<a href="%s">%s</a><br />', $file, $file);
        }
    }
    closedir($handle);

}


?>