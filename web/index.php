<!-- $Id$ -->

<!-- TODO this is most of the stuff that needs to be prettyfied -->
<!-- i've built this page as an html shell, with php stuck in the middle. -->

<HTML>
<HEAD>
	<TITLE>Springfest Fundraising</TITLE>
</HEAD>

<BODY>

<h2>Pacifica Co-Op Nursery School Springfest Invitation Entry</h2>
<!--<p>&nbsp;</p>-->

<?php
	# main sprintfest page
	# NOTE to matt: this is the page where most of the graphical stuff is designed to go
	# all the grody inner workings are encapsulated in functions
	# the outer HTML shell is pretty much fair game

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
	require_once("10names.php");

	#useful for form debugging. 
	#some host's security policies tend to break globals. use this to check.
	#confessVars();

	#fish $id out of the globals and keep it here nice and close. 
	#because, if i have to type that monster one more time...
	$id = $HTTP_POST_VARS['familyid'];
		
	if(!$id){
		#there is no familyid, let the user select one
		familyPopup(0);  #0 means, don't pre-select any family. just CHOOSE ONE

	} elseif($HTTP_POST_VARS['savenames']){
		#we are trying to save form data. this is what life is all about.

		if(processNames($HTTP_POST_VARS) == 0){
			#*whew* ok, everything went well. confirm this, and let them do more

			#show a family's info
			happyFriendlyHello($id);

			nameSummary($id);

			print "<br>Feel free to enter more names if you like!<br>";

			#finally, give them a form to enter data!
			oneNameForm($id);
		}


	} else {
		#ok we know what family we are, so give 'em the main form entry screen
		#printf ("<p>DEBUG: the id you chose was %d</p>\n", $id);

		#show a family's info
		happyFriendlyHello($id);

		#TODO : get the cutoff date from the database, 
		#so it someone can change it next year without having to edit this code
		print "<p>Every family must provide the names of 10 people who 
				should be invited to attend or donate to Springfest. 
				These can be family, friends, business associates, etc. 
				They will be sent formal invitations on behalf of the School. 
				You must enter at least 10 names by $cutoffdate</p>";

		#show them what they've already got, don pardo
		nameSummary($id);

		print "<P>Enter more names here.
					You may enter less than 10 if you wish, then 
					come back later and enter more if you like.
					Remember to click 'Save Names' 
					at the bottom of this screen when you are done!</p>";
		#finally, give them a form to enter data!
		oneNameForm($id);

	}
	# end of inner php code
?>

</BODY>
</HTML>

<!-- END INDEX -->
