<?php

//$Id$
// nifty little util to grab the latest page and display it where's i can see it

if ($handle = opendir('.')) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if(strstr($file, '-debug.')){
            $secondprev = $prev;
            $prev = $saver;
            $saver = $file;
        }
    }
    closedir($handle);

    
    print '<a href="testresults.php">back to test results</a><br />';


    printf('LATEST.php: %s is most recent. <a href="%s">%s</a> is previous. <a href="%s">%s</a> is third previous<br><br>',
           $saver, $prev, $prev, $secondprev, $secondprev);
    $fd = fopen($saver, 'r');
    do {
        $data = fread($fd, 8192);
        if (strlen($data) == 0) {
            break;
        }
        echo $data;
    } while(true);
    fclose ($fd);

}


?>