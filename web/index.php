<!-- $Id$ -->
<?php

# main sprintfest page

#  Copyright (C) 2003  ken restivo <ken@restivo.org>
# 
#  This program is free software; you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation; either version 2 of the License, or
#  (at your option) any later version.
# 
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details. 
# 
#  You should have received a copy of the GNU General Public License
#  along with this program; if not, write to the Free Software
#  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

require_once("shared.php");

print "<HTML><HEAD><TITLE>Springfest Fundraising</TITLE></HEAD><BODY>";

#TODO this is most of the stuff that needs to be prettyfied
print "<h2>Pacifica Co-Op Nursery School Springfest Invitation Entry</h2>";
print "<p>&nbsp;</p>";

/*		if no familyid, 
				draw the chooser,
				done
			else if there is form stuff (how to tell?)
				do the datachecking	
			else draw 
				the basic parentid,kids, names
				and the form!
*/
	
if(!$HTTP_POST_VARS['familyid']){
	#there is no familyid, let the user select one
	familyPopup(0);  #0 means, don't pre-select any family. just CHOOSE ONE
} elseif($HTTP_POST_VARS['Save']){
	#we are trying to save form data. this is what life is all about.
	#TODO: good stuff here!
} else {
	#show a family's info, and giving them a form to enter data
	printf ("<p>DEBUG: the id you chose was %d</p>\n", 
			$HTTP_POST_VARS['familyid']);
	familyDetail($HTTP_POST_VARS['familyid']);


}





print "</BODY></HTML>";

?>
<!-- END SHARED -->
