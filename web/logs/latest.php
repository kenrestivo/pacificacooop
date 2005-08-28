<?php

//$Id$
// nifty little util to grab the latest page and display it where's i can see it

if ($handle = opendir('.')) {
    /* This is the correct way to loop over the directory. */
    while (false !== ($file = readdir($handle))) {
        if(strstr($file, 'html')){
            $saver = $file;
        }
    }
    closedir($handle);

    echo "LATEST.php: $saver is most recent<br>";
    $fd = fopen($saver, 'r');
    $contents = "";
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