<?php
	//<!-- $Id$ -->

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

	require_once("first.inc");
	require_once("shared.inc");

	require_once("auth.inc");
	require_once("auctionfuncs.inc");
	require_once("financefuncts.inc");
	require_once("roster.inc");
	require_once("10names.inc");
	//require_once("insurancefuncs.inc");
	//require_once("calendarfuncs.inc");
	//require_once("adminfuncs.inc");
	require_once("pkg_checkin_funcs.inc");
	require_once("solicit_company.inc");
	require_once("solicit_auction.inc");
	require_once("solicit_cash.inc");
	require_once("raffle_finance.inc");
	require_once("raffle_locations.inc");
	require_once("flyer_company.inc");
	require_once("flyer_delivery.inc");
	require_once("nag.inc");
	require_once("indulgences.inc");
	require_once("invitation_cash.inc");
 	require_once("packages.inc");
 	require_once("ticket_sales.inc");

	print "<HTML>
		<HEAD>
			<TITLE>Data Entry</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School Data Entry</h2>
	";
	warnDev();

	$pv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;


	$auth = logIn($pv);

	if($auth['state'] != 'loggedin'){
		done();
	}

	//OK, i am logged in!
	
	$u = getUser($auth['uid']);

	topNavigation($auth, $u);
	print "\n<hr>\n";

	print "<p>Please choose an action:</p>";

	print "\n\n<table border=1>\n";
	tdArray( array ("Description", "Summary", "Actions"), 'align=center');

	//and heeere they are!
	$menu = array($auctioncallbacks, 
				  $invitationcallbacks, $invitation_cash_callbacks,
				  $incomecallbacks, $pkgcheckincallbacks,  
				  $solicit_company_callbacks, $solicit_auction_callbacks,
				  $solicit_cash_callbacks, 
				  $ticket_sales_callbacks, 
				  $raffle_location_callbacks, $raffle_income_callbacks, 
				  $flyer_company_callbacks,  $flyer_delivery_callbacks ,
				  $nag_callbacks, $indulgence_callbacks
		);

	
    while ( list( $key, $val ) = each($menu)) {
		user_error(sprintf("main(): showing row for %s", $val['description']),
			E_USER_NOTICE);
		// hack around the $callbacks not yet including fields, which it SHOULD
		showMenuRow($auth, $u, $val, ${$val['fields']});
	}
	
	/* package management
	 $pkg_mgmt_callbacks);
	*/



	/* admin 
	XXX can't use standard showMenuRow? 
		it uses FAMILYID, but admin wants USERID
	showMenuRow($auth, $u, 'User Administration', 
		'adminSummary', 'user', 'admin.php');
	*/

	print "\n</table>\n\n";

	familyDetail($u['familyid']);

	done();
?>
<!-- END INDEX -->
