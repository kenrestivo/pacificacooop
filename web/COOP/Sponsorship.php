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

require_once('CoopObject.php');
require_once('DB/DataObject.php');
require_once('object-config.php');
require_once('DB/DataObject/Cast.php');



//////////////////////////////////////////
/////////////////////// SPONSORSHIP CLASS
class Sponsorships
{
	var $cp ;  // alias to coop page object

	function Sponsorships(&$cp)
		{
			if(!is_object($cp)){
				PEAR::raiseError('must pass coop object in', 888);
			}
			$this->cp = $cp;
		}


} // END SPONSORSHIP CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END SPONSORSHIPCLASS -->


