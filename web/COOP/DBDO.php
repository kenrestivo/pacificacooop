<?php

/*
        $Id$

	Copyright (C) 2004  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once('DB/DataObject.php');


///clobal func
function coopDebug($class, $message, $logtype = 0, $level = 1) 
{

    if($level > 2){
        return;
    }
    //print_r(debug_backtrace());

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
        //dump('debuglevel set by <pre>'. print_r(debug_backtrace(), true) . '</pree>');
        if($v){
            $this->_debuglevel = $v;
        }
        return $this->_debuglevel;
    }  



    function setFrom(&$from)
    {

        global $_DB_DATAOBJECT;
        $keys  = $this->keys();
        $items = $this->table();
        $fields = array_keys($items);
        if (!$items) {
            $this->raiseError(
                "setFrom:Could not find table definition for {$this->__table}", 
                DB_DATAOBJECT_ERROR_INVALIDCONFIG);
            return;
        }

        
        foreach($from as $key => $val){
            if(in_array($key, $keys)){
                $this->CoopObject->page->printDebug(
                    "CoopDBDO::setFrom($key) cowardly refusing to set primary key to $from[$key]", 2); 
                continue;
            }

            if(!in_array($key, $fields)){
                $this->CoopObject->page->printDebug(
                    "CoopDBDO::setFrom($key) $from[$key] isn't in the dbdo, skipping", 2);
                continue;
            }
            // finally, DO IT
            $this->$key = $val;
        }

        return true;
    }



} /// END COOPDBDO CLASS


?>