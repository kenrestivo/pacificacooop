<HTML>
<HEAD>
	<TITLE>Springfest Reminder List</TITLE>
</HEAD>

<BODY>

<h2>Pacifica Co-Op Nursery School</h2>
<h2>Springfest Leads Summary</h2>

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

	require_once("shared.php");

	#useful for form debugging. 
	#some host's security policies tend to break globals. use this to check.
	#confessVars();

	printf("<form method=POST ACTION='%s'>\n", $_SERVER['PHP_SELF']);
	
	$latechecked =  $HTTP_POST_VARS['lateonly'] ? "checked" : "";
	printf("\t<input type='checkbox' name='lateonly' %s>", $latechecked);
	
	print "Show only families with fewer than 10 leads submitted<br>\n";
	print "\t<input type='submit' name='subbutton' value='refresh'>\n";
	print "</form>\n";

	
	print "<font size=10>\n";
	print "<table border='0'>";

	$query = "select families.name, families.familyid, families.phone, 
				count(leads.leadsid) as cntlead, enrol.sess
	       	from families 
       			left join leads on families.familyid = leads.familyid
			left join kids on kids.familyid = families.familyid
			left join keglue on keglue.kidsid = kids.kidsid
			left join enrol on enrol.enrolid = keglue.enrolid
		group by enrol.sess, families.name\n";
		
	if ($latechecked)
		$query = $query . "having cntlead < 10\n";

	$query = $query . "order by enrol.sess, cntlead desc, families.name;";

	$list = mysql_query($query);
	
	echo mysql_error();

	print "<tr>\n";
	print "\t<td><em><u>Family Name</u></em></td>\n";
	print "\t<td align='center'><em><u>Leads Submitted</u></em></td>\n";
	print "\t<td align='center'><em><u>Amount Paid</u></em></td>\n";
	print "\t<td align='center'><em><u>Session</u></em></td>\n";
	print "\t<td align='center'><em><u>Phone</u></em></td>\n";
	print "</tr>\n";
	
	while($row = mysql_fetch_array($list))
	{
		$paid = checkPayments($row['familyid']);

		print "<tr><td>\n";
		print $row[name];
		print "</td><td align='center'>";
		print $row[cntlead];
		print "</td><td align='center'>";
		if($paid){
			printf("$%01.2f", checkPayments($row['familyid']));
		}
		print "</td><td align='center'>";
		print $row[sess];
		print "</td><td align='center'>";
		print $row[phone];
		print "</td></tr>\n";
	}

	print "</table>\n";
	print "</font>\n";
	# end of inner php code


#############################
#	CHECKPAYMENTS
#	checks how much this family has paid up.
#	inputs: familyid
#	returns: total amount
#############################
function checkPayments($familyid)
{

	$query = "
		select families.familyid, sum(inc.amount) as total
			from families
				left join figlue on families.familyid = figlue.familyid
				left join inc on figlue.incid = inc.incid
			where (inc.acctnum = 1 or inc.acctnum = 2)
				and families.familyid = $familyid
			group by families.familyid
		";
		#print "DEBUG <$query>";
		$list = mysql_query($query);
		
		echo mysql_error();

		while($row = mysql_fetch_array($list))
		{
			$total += $row['total'];
		}

		return $total;

} #END CHECKPAYMENTS

?>

</BODY>
</HTML>

<!-- END INDEX -->

