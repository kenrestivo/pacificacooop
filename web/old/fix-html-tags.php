<?php

//$Id$

chdir("../");                   // XXX only for "test" dir hack!


require_once("CoopObject.php");

// strip eeeevil crap out of the html longtext fiels
function makeColumns(&$cp)
{
 
    $ignore = array('audit_trail');

    // get tables
    $tab =& new CoopObject(&$cp, 'table_permissions', &$nothing);
    $tab->obj->query("show tables");
    while($tab->obj->fetch()){
        $table  = $tab->obj->Tables_in_coop;
        if(in_array($table, $ignore)){
            continue;
        }
        $lucy =& new CoopObject(&$cp, 'table_permissions', &$nothing);
        $lucy->obj->query("explain $table");
        while($lucy->obj->fetch()){
            //confessArray($row, "row $table");
            if($lucy->obj->Type == 'longtext'){
                $fieldname = $lucy->obj->Field;
                    print "updating $table ...<br />";
                    $targ =& new CoopObject(&$cp, $table, &$nothing);
                    $targ->obj->find();
                    while($targ->obj->fetch()){
                        $hack =& new CoopObject(&$cp, $table, &$nothing);
                        $hack->obj->get($targ->obj->{$targ->pk});
                        
                        // these are the regexps that are the heart of the thing
                        $new = preg_replace(array('/align="(\w+?)"/',
                                                  '/<meta.+?>/',
                                                  '/<title.+?>/',
                                                  '/<style>.*?<\/style>/',
                                                  '/type=".+?"/',
                                                  '/start=".+?"/'), 
                                            array('style="text-align: $1"',
                                                  '', 
                                                  '',
                                                  '',
                                                  '',
                                                  ''), 
                                            $hack->obj->$fieldname);

                        if($new != $hack->obj->$fieldname){
                            printf('change %s <br> to %s<br>', 
                                   htmlentities($hack->obj->$fieldname),
                                   htmlentities($new));
                            if($_REQUEST['doit']){
                                /// XXX this is a stupid way to do it
                                /// i'm bypassing DataOBject->update()  because
                                ///  i forgot that i need to update the schema
                                /// so it wasn't working unless i forced it this way
                                $hack->obj->query(
                                    sprintf('update %s set %s = "%s" where %s = %d limit 1', 
                                            $table, $fieldname, 
                                            mysql_real_escape_string($new),
                                            $targ->pk,
                                            $targ->obj->{$targ->pk}));
                            
                            }
                            
                        }
                    }
            }
        }
    }
}


////KEEP EVERTHANG BELOW
$cp =& new CoopPage($debug);

print $cp->selfURL(array('value' => 'Refresh'));


makeColumns($cp);

print $cp->selfURL(array('value' => 'Click here to commit these changes',
                         'inside' => array('doit' => 'yes')));

?>


