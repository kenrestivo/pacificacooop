<?php
/* +-------------------------------------------------------------------+
 * | This file is part of flexac                                       |
 * | Copyright (c) 2005 Claudio Cicali <claudio@cicali.org>            |
 * +-------------------------------------------------------------------+
 * | flexac is free software; you can redistribute it and/or           |
 * | modify it under the terms of the GNU General Public License       |
 * | as published by the Free Software Foundation; either version 2    |
 * | of the License, or (at your option) any later version.            |
 * | flexac is distributed in the hope that it will be useful,         |
 * | but WITHOUT ANY WARRANTY; without even the implied warranty of    |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the     |
 * | GNU General Public License for more details.                      |
 * | You should have received a copy of the GNU General Public License |
 * | along with this program; if not, write to the:                    |
 * | Free Software Foundation, Inc., 59 Temple Place - Suite 330,      |
 * |                           Boston, MA 02111-1307, USA.             |
 * +-------------------------------------------------------------------+
 * | Authors: Claudio Cicali <claudio@cicali.org>                      |
 * +-------------------------------------------------------------------+
*/
chdir('../../');
require_once('CoopPage.php');
require_once('CoopView.php');
require_once('Services/JSON.php');

$cp = new CoopPage($debug);
$cp->pageTop(); 

class DataFetcher{
    var $json;
    var $page;
    
    function DataFetcher(&$page)
        {
            $this->page =& $page;
            $this->json =& new Services_JSON();        
        }

    function scram()
        {
            print $this->json->encode(array("???" => "???"));
            $this->page->flushBuffer();
            exit;
        }

    function process($vars)
        {

            if( isset($vars["f"])){
                $longfield = $vars['f'];
                list($table, $fieldname) = explode('-', $longfield);
                $co =& new CoopObject(&$this->page, $table, &$nothing);
                if(!is_callable(array($co, 'getData'))){
                    $this->scram();
                }
            } else {
                $this->scram();
            }
            
            $query = "";
            if(isset($vars["q"])) 
                $query = $vars["q"];
            
            $limit = 0;
            if(isset($vars["l"])) 
                $limit = $vars["l"];
            
            $beginsWith = false;
            if(isset($vars["b"])) 
                $beginsWith = ($vars["b"] == "0" ? false : true);
            
            
            return $this->json->encode($co->getData($query, 
                                                    $limit, 
                                                    $beginsWith));
        }
} // END DATAFETCHER


$frun =& new DataFetcher(&$cp);
print $frun->process(&$_GET);

$cp->flushBuffer();
    
    


?>