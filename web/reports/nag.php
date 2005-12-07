<?php

//  Copyright (C) 2003-2005  ken restivo <ken@restivo.org>
// 
//  This program is free software; you can redistribute it and/or modify
//  it under the terms of the GNU General Public License as published by
//  the Free Software Foundation; either version 2 of the License, or
//  (at your option) any later version.
// 
//  This program is distributed in the hope that it will be useful,
//  but WITHOUT ANY WARRANTY; without even the implied warranty of
//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//  GNU General Public License for more details. 
// 
//  You should have received a copy of the GNU General Public License
//  along with this program; if not, write to the Free Software
//  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

//$Id$

require_once('CoopPage.php');
require_once('CoopView.php');
require_once('CoopForm.php');
require_once('HTML/Table.php');



#############################
#	CHECKPAYMENTS
#	checks how much this family has paid up.
#	inputs: family_id
#	returns: array with amount (numbers) and notes (text)
#############################
function checkPayments($family_id, $type)
{

	//XXX hack. i hate having to map these manually.
	$feetypes = array(
		'10names' => 1,
		'quilt' => 2,
		'auction' => 3
	);
		
	$account_number = $feetypes[$type];
	if(!$account_number){
		user_error("checkPayments() called with invalid type [$type] for family_id [$family_id]", E_USER_ERROR);
	}

	$query = sprintf("
		select families.family_id, income.payment_amount, income.note
			from families
				left join families_income_join 
						on families.family_id = families_income_join.family_id
				left join income on 
						families_income_join.income_id = income.income_id
			where income.account_number = $account_number
				and families.family_id = $family_id and school_year = '%s'
		", findSchoolYear());
	#print "DEBUG <$query>";
	$list = mysql_query($query);

	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$total['payment_amount'] += $row['payment_amount'];
		$total['notes'] .= sprintf("%s%s",
				$i ? "<br>" : "",
				$row['note']
				);
		$row['note'] && $i++;
	}

	$total['indulgences'] = checkIndulgences($family_id,  $type);
	return $total;

} #END CHECKPAYMENTS


/******************
	CHECKAUCTION
	totals up the auction amounts for this family, including forfiets
	inputs: family_id
	returns: the total amount of their auction donations. 
******************/
function
checkAuction($family_id)
{
	$query = sprintf("
	select families.name, sum(auction_donation_items.item_value) as item_value
		from auction_donation_items
			left join auction_items_families_join on auction_donation_items.auction_donation_item_id = auction_items_families_join.auction_donation_item_id
			left join companies_auction_join 
				on auction_donation_items.auction_donation_item_id = companies_auction_join.auction_donation_item_id
			left join families 
				on coalesce(auction_items_families_join.family_id, companies_auction_join.family_id) =
				families.family_id
		where families.family_id = $family_id
				and school_year = '%s'
		group by families.family_id
		", findSchoolYear());
	#print "DEBUG <$query>";
	$list = mysql_query($query);
	
	$err = mysql_error();
	if($err){
		user_error("[$query] errored with $err", E_USER_ERROR);
	}

	$i = 0;
	while($row = mysql_fetch_array($list))
	{
		$result['total'] += $row['item_value'];
	}

	/* keep these separate, someone may want to know which was which
	*/
	$result['total'] += $result['donated'];


	// check if they paid in any forfiet fees!
	$tmp = checkPayments($family_id, 'auction'); //returns array.
	$result['forfeit'] = $tmp['payment_amount'];
	$result['indulgences'] = $tmp['indulgences'];
	$result['total'] +=  $result['forfeit'];
	

	return $result;
}/* END CHECKAUCTION */


/******************
	CHECKDELIVERY
	checks how many auction items they have outstanding,
		yet to be delivered to the storage garage
	XXX this function sucks. it does. full stop. it just sucks. 
******************/
function
checkDelivery($family_id, $glue)
{
	//user_error("HEY i am $family_id and $glue", E_USER_NOTICE);
	$query = sprintf("
		select count(auction_donation_items.auction_donation_item_id) 
						as counter
			from auction_donation_items
				left join $glue 
						on $glue.auction_donation_item_id = 
							   auction_donation_items.auction_donation_item_id
			where $glue.family_id = $family_id
			and school_year  = '%s'
				and (auction_donation_items.date_received is null 
					or auction_donation_items.date_received < '2004-01-01')
		", findSchoolYear());
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
	$query = sprintf("
		select count(auction_donation_items.auction_donation_item_id) 
				as counter
			from auction_donation_items
				left join $glue 
						on $glue.auction_donation_item_id = 
								auction_donation_items.auction_donation_item_id
			where $glue.family_id = $family_id
				and school_year = '%s'
				and auction_donation_items.date_received is not null 
				and auction_donation_items.date_received > '2004-01-01'
		", findSchoolYear());
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

	//TODO: track delivery-related indulgences, if that even makes sense.

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
sortColumns($text, $sortby, $sortdir, $showall)
{
	$url = htmlentities(sprintf('%s?%ssortby=%s&sortdir=%s%s',
								$_SERVER['PHP_SELF'], 
								SID ? SID . "&" : "", 
								$sortby, $sortdir, 
								$showall ? "&showall=checked" : ""));
	return sprintf(
        '<td align="center"><em><u><a href="%s">%s</a></u></em></td>', 
        $url, $text);
}/* END SORTCOLUMNS */


/********************
	CHECKINDULGENCES
	returns a <br> formatted string with indulgences for this family.
*****************/
function
checkIndulgences($family_id, $uglytype)
{

	//XXX yet another hacky-hack
	$mapping = array (
			'general' => 'Everything', 
			'10names' => 'Invitations', 
			'auction' => 'Family Auctions', 
			'quilt' => 'Quilt Fee', 
			'solicitauction' => 'Solicitation Auctions'
		);

	$type = $mapping[$uglytype];

	$query = sprintf("
		select note, granted_date 
			from nag_indulgences
			where family_id = %d
				and indulgence_type = '%s'
				and school_year = '%s'
			order by granted_date desc ",
			$family_id, mysql_escape_string($type), findSchoolYear());

	//user_error("checkINdulgences(): doing [$query] ", E_USER_NOTICE);
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


function checkAds($family_id)
{
	global $cp ; /// haaaack!
	$sy =findSchoolYear();


	$view = new CoopObject(&$cp , 'ads', &$top);
	//$view->obj->debugLevel(2);
	$view->obj->query(sprintf(
						  "select count(%s) as count from ads where school_year = '%s' and family_id = %d and (artwork_received is null or artwork_received < '2000-01-01')", 
						  $view->pk, $sy, $family_id
						  ));
	$view->obj->fetch();
	$total['undelivered'] = $view->obj->count;

	$view = new CoopObject(&$cp , 'ads', &$top);
	//$view->obj->debugLevel(2);
	$view->obj->query(sprintf(
						  "select count(distinct(%s)) as count from ads where school_year = '%s' and family_id = %d and artwork_received > '2000-01-01'", 
						  $view->pk, $sy, $family_id
						  ));
	$view->obj->fetch();
	$total['delivered'] = $view->obj->count;



	$total['indulgences'] = checkIndulgences($family_id,  'ads');
	//confessArray($total, "total for $family_id");
	return $total;
}





function viewHack(&$cp)
{



	$res .= '<table bgcolor="#ffffff" border="0">';

	// let people sort as they wish
	$sortby = $gv['sortby'] ? $gv['sortby'] : 'families.name';
	$sortdir = $gv['sortdir'] ? $gv['sortdir'] : 'asc';

	$top = new CoopView(&$cp, 'families', $nothing);


    $showall = $top->isPermittedField(null, true, true) > ACCESS_VIEW;


	$top->obj->query(
        sprintf("select families.name, families.family_id, families.phone, 
				count(distinct(invitations.invitation_id)) as cntlead, 
						enrollment.am_pm_session
	       	from families 
       			left join invitations 
                        on families.family_id = invitations.family_id
                            and invitations.school_year = '%s'
			left join kids on kids.family_id = families.family_id
			left join enrollment on enrollment.kid_id = kids.kid_id
		where enrollment.school_year = '%s' 
			and enrollment.dropout_date is NULL
		group by enrollment.am_pm_session, families.name
		order by enrollment.am_pm_session, $sortby $sortdir\n",
                $cp->currentSchoolYear,
                $cp->currentSchoolYear));


	$res .= '<tr bgcolor="#aabbff" align="center">';
	$res .= sortColumns('Family Name', 'families.name', 'asc', $showall);
	$res .= sortColumns('Leads Submitted', 'cntlead', 'desc', $showall);
	$res .= "\t<td ><em><u>Food/Raffle Fee Paid</u></em></td>\n";
	$res .= "\t<td ><em><u>Auction Donated and Solicited</u></em></td>\n";
	$res .= "\t<td ><em><u>Family Auction Inventory</u>
			</em></td>\n";
	$res .= "\t<td ><em><u>Solicitation Auction Inventory</u>
			</em></td>\n";
	$res .= "\t<td ><em><u>Program Ad Copy</u>
			</em></td>\n";
	$res .= "\t<td ><em><u>Session</u></em></td>\n";
	$res .= "\t<td ><em><u>Phone</u></em></td>\n";
	$res .= "\t<td ><em><u>Actions</u></em></td>\n";
	$res .= "\t<td></td>\n"; // a place for the 'everything' indulgences
	$res .= "</tr>\n";
	
	$rowcolor = 0;
	while($top->obj->fetch())
	{
        $row = $top->obj->toArray();
		$tennamespaid = checkPayments($row['family_id'], '10names');
		$quiltpaid = checkPayments($row['family_id'], 'quilt');
		$auction = checkAuction($row['family_id']);
		$delivery = checkDelivery($row['family_id'], 'auction_items_families_join');
		$solicitdelivery = checkDelivery($row['family_id'], 
					'companies_auction_join');
		$tennamesdone = ($row[cntlead] >= 10);
		$ad_delivery = checkAds($row['family_id']);
		#some nifty running totals
		$total['leads'] += $row[cntlead];
		$total['tennames'] += $tennamespaid['payment_amount'];
		$total['quilt'] += $quiltpaid['payment_amount'];
		$total['auction'] += $auction['total']; //symmetry!
		$total['undelivered'] += $delivery['undelivered']; 
		$total['delivered'] += $delivery['delivered']; 
		$total['ad_undelivered'] += $ad_delivery['undelivered']; 
		$total['ad_delivered'] += $ad_delivery['delivered']; 
		$generalindulgence = checkIndulgences($row['family_id'], 'general');

		#don't print this row if it's already complete, or indulgence granted
		//check out them parentheses! damn glad i've been learning lisp.
		if (!$showall && 
			($generalindulgence || 
			((($tennamespaid['payment_amount'] >= 50) || $tennamesdone || 
					$tennamespaid['indulgences']) && 
				($quiltpaid['payment_amount'] >=45 || $quiltpaid['indulgences']) && 
				($auction['total'] >= 50 || $auction['indulgences']) && 
				(!$delivery['undelivered'] || $delivery['indulgences']) &&
				(!$solicitdelivery['undelivered'] || 
						 $solicitdelivery['indulgences']) &&
				(!$ad_delivery['undelivered'] || 
						 $ad_delivery['indulgences'])
				)))
		{
			continue;
		}
	
		//FAMILYNAME
		$res .= sprintf('<tr bgcolor="%s"><td>', 
			   $rowcolor++ % 2 ? '#dddddd' : '#ccccff');
		$res .= $row['name'];
		//LEADS
		$res .= "</td><td align='center'>";
		//XXX bug lurking. if a cntlead AND a dollar amount, it'll look ugly.
		$res .= $row['cntlead'] ? $row['cntlead'] : "";
		if ($tennamespaid['payment_amount'] > 0){
			$res .= sprintf("$%01.2f", $tennamespaid['payment_amount']);
		}
		if($tennamespaid['notes'])
			$res .= sprintf("<br>%s",$tennamespaid['notes']);
		if($tennamespaid['indulgences']){
			$res .= sprintf("%s%s", 
				$tennamespaid['payment_amount'] > 0  || $row['cntlead'] > 0
				   ? "<br>" : "",
				$tennamespaid['indulgences']);
		}
		//QUILT FEE
		$res .= "</td><td align='center'>";
		if ($quiltpaid['payment_amount'] > 0){
			$res .= sprintf("$%01.2f", $quiltpaid['payment_amount']);
			if($quiltpaid['notes'])
				$res .= sprintf("<br>%s",$quiltpaid['notes']);
		} 
		/* if there's NOTHIGN in the field
				the br's are superfluous and should be gone!
		 */
		if($quiltpaid['indulgences']){
			$res .= sprintf("%s%s", 
			   $quiltpaid['payment_amount'] > 0 ? "<br>" : "",
				$quiltpaid['indulgences']);
		}
		//AUCTION (family auction)
		$res .= "</td><td align='center'>";
		if ($auction['total'] > 0){
			$res .= sprintf("$%01.2f", $auction['total']);
		}
		if($auction['indulgences']){
			$res .= sprintf("%s%s", 
				   $auction['total'] > 0 ? "<br>" : "",
				$auction['indulgences']);
		}
		//FAMILY AUCTION DELIVERY
		$res .= "</td><td align='center'>";
		if ($delivery['undelivered'] > 0)
			$res .= sprintf("%d missing", $delivery['undelivered']);
		if($delivery['indulgences'])
			$res .= sprintf("<br>%s",$delivery['indulgences']);	
		//SOLICITATION AUCTION DELIVERY
		$res .= "</td><td align='center'>";
		if ($solicitdelivery['undelivered'] > 0)
			$res .= sprintf("%d missing", $solicitdelivery['undelivered']);
		if($solicitdelivery['indulgences'])
			$res .= sprintf("<br>%s",$solicitdelivery['indulgences']);
		//PROGRAM AD DELIVERY
		$res .= "</td><td align='center'>";
		if ($ad_delivery['undelivered'] > 0)
			$res .= sprintf("%d missing", $ad_delivery['undelivered']);
		if($ad_delivery['indulgences'])
			$res .= sprintf("<br>%s",$ad_delivery['indulgences']);
		//SESSION, PHONE
		$res .= "</td><td align='center'>";
		$res .= $row['am_pm_session'];
		$res .= "</td><td align='center'>";
		$res .= $row['phone'];
		$res .= "</td>"; 
		// ACTIONS
		$res .= "<td align='center'>";
        $res .= $top->recordButtons(&$nothing);
		$res .= "</td><td align='center'>";
		//GENERAL INDULGENCES
		$res .= $generalindulgence;
		$res .= "</td></tr>\n";
	}

	if($showall){
		$res .= tdArray(array (
				"TOTAL",
				$total['leads'],
				sprintf("$%01.2f", $total['quilt']),
				sprintf("$%01.2f", $total['auction']),
				sprintf("%d missing<br>(%d received)", $total['undelivered'], 
					$total['delivered']),
				"",
				sprintf("%d missing<br>(%d received)", 
						$total['ad_undelivered'], 
						$total['ad_delivered']),
				"",
				"",
				"",
				""
			),
                "align='center'",
                '',
                true
		);
	}

	$res .= "</table>\n";



	return $res;
	 
}



////////////////////////MAIN


$cp = new coopPage( $debug);
print $cp->pageTop();
print $cp->topNavigation();


$atd = new CoopView(&$cp, 'nags', $none);


print "\n<hr></div><!-- end header div -->\n"; //ok, we're logged in. show the rest of the page
print '<div id="centerCol">';



// cheap dispatcher
//confessArray($_REQUEST,'req');
switch($_REQUEST['action']){
 
	 
//// EDIT AND NEW //////
 case 'new':
 case 'edit':
	 break;

 case 'details':
     print details(&$cp);
	 break;

//// DEFAULT (VIEW) //////
 default:
	 print viewHack(&$cp);

	 break;
}


done ();

////KEEP EVERTHANG BELOW

?>


<!-- END NAGREPORT -->

