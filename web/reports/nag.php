<?php
	# $Id$

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

	print "
		<HTML>
		<HEAD>
			<TITLE>Springfest Reminder List</TITLE>
		</HEAD>

		<BODY>

		<h2>Pacifica Co-Op Nursery School</h2>
		<h2>Springfest Leads Summary</h2>
	";
	#useful for form debugging. 
	#some host's security policies tend to break globals. use this to check.
	#confessVars();

	printf("<form method=POST ACTION='%s'>\n", $_SERVER['PHP_SELF']);

	$gv = $HTTP_POST_VARS ? $HTTP_POST_VARS : $HTTP_GET_VARS;
	
	//i'm sorry. it was too convenient not to include here as well.
	javaSubmit();

	$nagonlychecked =  $gv['nagonly'] ? "checked" : "";
	printf("\t<input type='checkbox' onchange='javascript:submitForm()' 
				name='nagonly' %s>", $nagonlychecked);
	
	print "Show only families that need nagging<br>\n";
	print "\t<input type='submit' name='subbutton' value='refresh'>\n";
	print "</form>\n";

	print "<font size=10>\n";
	print "<table border='0'>";

	// let people sort as they wish
	$sortby = $gv['sortby'] ? $gv['sortby'] : 'families.name';
	$sortdir = $gv['sortdir'] ? $gv['sortdir'] : 'asc';

	#TODO: semester is hard-coded for next year, let the user choose it
	$query = "select families.name, families.familyid, families.phone, 
				count(leads.leadsid) as cntlead, enrol.sess
	       	from families 
       			left join leads on families.familyid = leads.familyid
			left join kids on kids.familyid = families.familyid
			left join attendance on attendance.kidsid = kids.kidsid
			left join enrol on enrol.enrolid = attendance.enrolid
		where enrol.semester like \"2003-2004\" 
			and attendance.dropout is NULL
		group by enrol.sess, families.name
		order by enrol.sess, $sortby $sortdir\n";

	$list = mysql_query($query);
	
	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	print "<tr>\n";
	sortColumns('Family Name', 'families.name', 'asc', $nagonlychecked);
	sortColumns('Leads Submitted', 'cntlead', 'desc', $nagonlychecked);
	print "\t<td align='center'><em><u>Quilt Fee Paid</u></em></td>\n";
	print "\t<td align='center'><em><u>Auction Donated</u></em></td>\n";
	print "\t<td align='center'><em><u>Auction Inventory</u></em></td>\n";
	print "\t<td align='center'><em><u>Session</u></em></td>\n";
	print "\t<td align='center'><em><u>Phone</u></em></td>\n";
	print "</tr>\n";
	
	while($row = mysql_fetch_array($list))
	{
		$tennamespaid = checkPayments($row['familyid'], '10names');
		$quiltpaid = checkPayments($row['familyid'], 'quilt');
		$auction = checkAuction($row['familyid']);
		//TODO: delivery for solicitation too-- separate column.
		$delivery = checkDelivery($row['familyid']);
		$tennamesdone = ($row[cntlead] >= 10);
		#some nifty running totals
		$total['leads'] += $row[cntlead];
		$total['tennames'] += $tennamespaid['amount'];
		$total['quilt'] += $quiltpaid['amount'];
		$total['auction'] += $auction['total']; //symmetry!
		$total['undelivered'] += $delivery['undelivered']; 
		$total['delivered'] += $delivery['delivered']; 

		#don't print this row if it's already complete, or indulgence granted
		if ($nagonlychecked && 
				(($tennamespaid['amount'] >= 50) || $tennamesdone || 
					$tennamespaid['indulgences']) && 
				($quiltpaid['amount'] >=45 || $quiltpaid['indulgences']) && 
				($auction['total'] >= 50 || $auction['indulgences']) && 
				!$delivery['undelivered'] || $delivery['indulgences'])
		{
			continue;
		}
	
		print "<tr><td>\n";
		print $row[name];
		print "</td><td align='center'>";
		print $row[cntlead] ? $row['cntlead'] : "";
		if ($tennamespaid['amount'] > 0){
			printf("$%01.2f", $tennamespaid['amount']);
		}
		if($tennamespaid['notes'])
			printf("<br>%s",$tennamespaid['notes']);
		if($tennamespaid['indulgences'])
			printf("<br>%s",$tennamespaid['indulgences']);
		print "</td><td align='center'>";
		if ($quiltpaid['amount'] > 0)
			printf("$%01.2f", $quiltpaid['amount']);
		if($quiltpaid['notes'])
			printf("<br>%s",$quiltpaid['notes']);
		if($quiltpaid['indulgences'])
			printf("<br>%s",$quiltpaid['indulgences']);
		print "</td><td align='center'>";
		if ($auction['total'] > 0)
			printf("$%01.2f", $auction['total']);
		if($auction['indulgences'])
			printf("<br>%s",$auction['indulgences']);
		print "</td><td align='center'>";
		if ($delivery['undelivered'] > 0)
			printf("%d missing", $delivery['undelivered']);
		if($undelivered['indulgences'])
			printf("<br>%s",$undelivered['indulgences']);
		print "</td><td align='center'>";
		print $row[sess];
		print "</td><td align='center'>";
		print $row[phone];
		print "</td></tr>\n";
	}

	if(!$nagonlychecked){
		tdArray(array (
				"TOTAL",
				$total['leads'],
				sprintf("$%01.2f", $total['quilt']),
				sprintf("$%01.2f", $total['auction']),
				sprintf("%d missing<br>(%d received)", $total['undelivered'], 
					$total['delivered']),
				"",
				""
			),
			"align='center'"
		);
	}

	print "</table>\n";
	print "</font>\n";
	# end of inner php code


#############################
#	CHECKPAYMENTS
#	checks how much this family has paid up.
#	inputs: familyid
#	returns: array with amount (numbers) and notes (text)
#############################
function checkPayments($familyid, $type)
{

	//XXX hack. i hate having to map these manually.
	$feetypes = array(
		'10names' => 1,
		'quilt' => 2,
		'auction' => 3
	);
		
	$acctnum = $feetypes[$type];
	if(!$acctnum){
		user_error("checkPayments() called with invalid type [$type] for familyid [$familyid]", E_USER_ERROR);
	}

	$query = "
		select families.familyid, inc.amount, inc.note
			from families
				left join figlue on families.familyid = figlue.familyid
				left join inc on figlue.incid = inc.incid
			where inc.acctnum = $acctnum
				and families.familyid = $familyid
		";
		#print "DEBUG <$query>";
		$list = mysql_query($query);
		
		$err = mysql_error();
		if($err){
			user_error("[$query] errored with $err", E_USER_ERROR);
		}

		$i = 0;
		while($row = mysql_fetch_array($list))
		{
			$total['amount'] += $row['amount'];
			$total['notes'] .= sprintf("%s%s",
					$i ? "<br>" : "",
					$row['note']
					);
			$row['note'] && $i++;
		}

		return $total;

} #END CHECKPAYMENTS


/******************
	CHECKAUCTION
	totals up the auction amounts for this family, including forfiets
	inputs: familyid
	returns: the total amount of their auction donations. 
******************/
function
checkAuction($familyid)
{
	$query = "
		select families.name, sum(auction.amount) as amount
			from families
				left join faglue on families.familyid = faglue.familyid
				left join auction on faglue.auctionid = auction.auctionid
			where families.familyid = $familyid
			group by families.familyid
		";
	#print "DEBUG <$query>";
	$list = mysql_query($query);
	
	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$result['total'] += $row['amount'];
	}

	/* keep these separate, someone may want to know which was which
	*/
	$result['total'] += $result['donated'];


	// check if they paid in any forfiet fees!
	$tmp = checkPayments($familyid, 'auction'); //returns array.
	$result['forfeit'] = $tmp['amount'];
	$result['total'] +=  $result['forfeit'];
	
	$result['indulgences'] = checkIndulgences($familyid,  'auction');

	return $result;
}/* END CHECKAUCTION */


/******************
	CHECKDELIVERY
	checks how many auction items they have outstanding,
		yet to be delivered to the storage garage
******************/
function
checkDelivery($familyid)
{
	$query = "
		select count(auction.auctionid) as counter
			from auction
				left join faglue on faglue.auctionid = auction.auctionid
			where faglue.familyid = $familyid
				and (auction.date_received is null 
					or auction.date_received < '2004-01-01')
		";
	#print "DEBUG <$query>";
	$list = mysql_query($query);
	
	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$result['undelivered'] += $row['counter'];
	}

	//now the DELIVERED ones!
	$query = "
		select count(auction.auctionid) as counter
			from auction
				left join faglue on faglue.auctionid = auction.auctionid
			where faglue.familyid = $familyid
				and auction.date_received is not null 
				and auction.date_received > '2004-01-01'
		";
	#print "DEBUG <$query>";
	$list = mysql_query($query);
	
	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$result['delivered'] += $row['counter'];
	}


	return $result;
}/* END CHECKDELIVERY */



/******************
	SORTCOLUMNS
	show a clickable sort column
	inputs: text to show, field to use as sortby, default direction
		and also pass through nag status.
	outputs: a sort column header
******************/
function
sortColumns($text, $sortby, $sortdir, $nag)
{
	printf("\t<td align='center'><em><u>
			<a href='%s?sortby=%s&sortdir=%s%s'>%s</a></u></em></td>\n",
			 $_SERVER['PHP_SELF'], $sortby, $sortdir, 
			$nag ? "&nagonly=checked" : "",
			$text);
}/* END SORTCOLUMNS */


/********************
	CHECKINDULGENCES
	returns a <br> formatted string with indulgences for this family.
*****************/
function
checkIndulgences($familyid, $uglytype)
{

	//XXX yet another hacky-hack
	$mapping = array (
			'general' => 'Everything', 
			'auction' => 'Family Auctions', 
			'quilt' => 'Raffle Fee', 
			'solicitauction' => 'Solicitation Auctions'
		);

	$type = $mapping[$uglytype];

	$query = sprintf("
		select note, granted_date 
			from nag_indulgences
			where familyid = %d
				and indulgence_type = '%s'
			order by granted_date desc ",
			$familyid, mysql_escape_string($type));

	user_error("checkINdulgences(): doing [$query] ", E_USER_NOTICE);
	$list = mysql_query($query);

	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$total .= sprintf("Excused %s%s%s%s",
							$i ? "<br>" : "",
						  $row['granted_date'],
						  $row['note'] ? ": " : "",
							$row['note']
				);
		$total && $i++;
	}

	return $total;

} /* END CHECKINDULGENCES */

?>

</BODY>
</HTML>

<!-- END NAG -->

