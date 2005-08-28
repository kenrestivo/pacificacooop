<?php                                                                               
                                                                                    
$newsitems = array(array('title'=>'Fall Session starts September 12',               
                                'body' => 'There may still be a very few openings f\
or Fall. Call 355-3272 for more information. Now is a good time to fill out a <a hr\
ef="documents/Wait_list_application_2005.pdf">wait-list application</a>.'));        
                                                                                    
                                                                                    
if($_REQUEST['summary']){                                                           
    foreach($newsitems as  $item){                                                  
        printf("<p><b>%s</b><p>%s</p>", $item['title'], $item['body']);             
    }                                                                               
} else {                                                                            
                                                                                    
    print "Blog interface still under construction";                                
                                                                                    
}                                                                                   
                                                                                    
?> 