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
require_once('utils.inc');


//////////////////////////////////////////
/////////////////////// THANKYOU CLASS
class Enhancement
{
	var $schoolYear; // cache of this year's, um, year.

	// month number, hour number
	var lateStarts = array(
		9 => 4,
		10 => 3,
		11 => 2,
		12 => 1,
		// i don't separate out fall/spring here
		1 => 4, 
		3 => 3,
		4 => 2
		5 => 1
		);



	function Enhancement ($schoolYear = false)
		{
			// guess it and cache it
			$this->schoolYear = findSchoolYear($schoolYear);

		}
	


} // END ENHANCEMENT CLASS


////KEEP EVERTHANG BELOW

?>
<!-- END ENHANCEMENT -->


