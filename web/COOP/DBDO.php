<?php

require_once('DB/DataObject.php');


///clobal func
function coopDebug($class, $message, $logtype = 0, $level = 1) 
{
    if($level > 2){
        return;
    }

   if (!is_string($message)) {
        $message = print_r($message,true);
    }
   //PEAR::raiseError('wtf', 777);
 

    //TODO: store the apge in here, and user printDebug instead
    dump("<code><B>$class: $logtype: $level</B> $message</code><BR>\n");
    return;
}



class CoopDBDO extends DB_DataObject {
    var $_debuglevel;
    
/// used instead of what's built in
    function debugLevel($v = null)
    {
        print "AIIII";
        if($v){
            $this->_debuglevel = $v;
        }
        return $this->_debuglevel;
    }  
}


?>