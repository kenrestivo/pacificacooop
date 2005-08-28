<?php

$newsitems = array(array('title'=>'Fall Session starts September 12',
                         'body' => 'There may still be a very few openings for Fall. Call 355-3272 for more information. Now is a good time to fill out a <a href="documents/Wait_list_application_2005.pdf">wait-list application</a>.',
                         'updated' => '08/05/2005 12:00PM'));

if($_REQUEST['summary']){
    foreach($newsitems as  $item){
        printf("<p><b>%s</b><p>%s (Posted %s)</p>", 
               $item['title'], $item['body'], $item['updated']);  
    }
} else {
    print "Blog interface still under construction";
}

?> 