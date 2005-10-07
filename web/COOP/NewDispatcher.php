<?php 

//$Id$

/*
	Copyright (C) 2005  ken restivo <ken@restivo.org>
	 
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


//////////////////////////////////////////
/////////////////////// COOP DISPATCHER CLASS
class CoopNewDispatcher
{
	var $page;  				// cached coopPage object

	function CoopNewDispatcher(&$page)
		{
			$this->page = $page;
		}




    function view()
        {
            
            $atd =& new CoopView(&$this->page, 
                                 $this->page->vars['last']['table'], $none);
            //$atd->debugWrap(2);
            
            $res .= '<div><!-- status alert div -->';
            
            $atd2 =& new CoopView(&$this->page, 
                                  $this->page->vars['last']['table'], $none);
            // alert  and/or summary does a find, so i need a separate obj for it
            
            if(is_callable(array($atd2->obj, 'fb_display_summary'))){
                $atd2->page->printDebug('calling callback for summary', 2);
                $res .= $atd2->obj->fb_display_summary(&$atd2);
            }
            if(is_callable(array($atd2->obj, 'fb_display_alert'))){
                $atd2->page->printDebug('calling callback for alert', 2);
                $res .= $atd2->obj->fb_display_alert(&$atd2);
            }
            $res .= '</div><!-- end status alert div -->';
            

            if(is_callable(array($atd->obj, 'fb_display_view'))){
                $this->page->printDebug('calling callback for view', 2);
                return $atd->obj->fb_display_view(&$atd);
            }
            

            //TODO: some variation on the old "perms display" from auth.inc
            //maybe at or top of doc? with editor to change them? ;-)
            
            $res .= $atd->simpleTable();
            
            return $res;
         			
        }













} // END NEW COOP DISPATCHER CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END NEW COOP DISPATCHER -->


