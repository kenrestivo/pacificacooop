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
}


?>