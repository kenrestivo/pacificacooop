<?php 

#  Copyright (C) 2004  ken restivo <ken@restivo.org>
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
require_once("CoopObject.php");
require_once("HTML/Table.php");

PEAR::setErrorHandling(PEAR_ERROR_PRINT);


function sponsors(&$cp)
{


// now a word from our sponsors
	$res .= '<div class="sponsor">';
	$res .= "<p><b>Thanks to our generous sponsors:</b></p>";
	$tab =& new HTML_Table();
//TODO: a weird merge of all solicitation and leads, by sponsor level
// get sponsor levels, then do the search for each
// first step: just get the damn companies
	$sp =& new CoopObject(&$cp, 'sponsorship_types', &$nothing);
	$sp->obj->school_year = findSchoolYear();
	$sp->obj->orderBy('sponsorship_price desc');
	$sp->obj->find();
	$previous = 1000000000; 		// XXX hack
	while($sp->obj->fetch()){
		
		$co =& new CoopObject(&$cp, 'companies', &$nothing);
		// XXX make this check the sponsorship table, not calculate

		$co->obj->query(sprintf("
select company_name,
        sum(inc.payment_amount) as cash_donations
from companies
left join 
    (select  sum(payment_amount) as payment_amount, company_id
     from companies_income_join as cinj
     left join income 
              on cinj.income_id = 
                income.income_id
        where school_year = '2004-2005'
        group by cinj.company_id) 
    as inc
        on inc.company_id = companies.company_id
group by companies.company_id
having cash_donations >= %d and cash_donations < %d
order by company_name
", 
								$sp->obj->sponsorship_price,
								$previous));
		$previous = $sp->obj->sponsorship_price;

		$sponsors = ''; // because HTML sucks
		while($co->obj->fetch()){

			// when i redo it, this is where the test for existing goes
			if($co->obj->url > ''){
				$thing = sprintf('<a href="%s">%s</a>', 
								 $cp->fixURL($co->obj->url),
								 $co->obj->company_name);
			} else {
				$thing = $co->obj->company_name;
			}
			$sponsors .= sprintf("<li>%s</li>", $thing);
		}
		if($co->obj->N){
			$res .= sprintf(
				'<p><b>%s Contributors</b> ($%.0f and above)</p><ul>%s</ul>', 
				$sp->obj->sponsorship_name,
				$sp->obj->sponsorship_price,
				$sponsors);
		}


		
	}
	
	$res .= "</div><!-- end sponsor -->";
	return $res;
} // end sponsors

function auctionItems(&$cp, $sy)
{
	$res .= sprintf('	<h2>Springfest Auction Items</h2>

	<p>Here are some fabulous items that will be auctioned off 
			at  Springfest!</p>');

	$tab =& new HTML_Table();
	$tab->addRow(array('Item Number',
					   'Item Name',
					   'Description',
					   'Value'), 
				 'bgcolor=#aabbff align=left', 'TH');


	$q = sprintf('select package_number, package_title, package_description,
        package_value
        from packages
		where display_publicly = "Yes"
				and school_year = "%s"
        order by package_type, package_number, package_title, 
			package_description', $sy);

	$listq = mysql_query($q);
	$i = 0;
	$err = mysql_error();
	if($err){
		user_error("public_auction($title): [$q]: $err", E_USER_ERROR);
	}
	while($row = mysql_fetch_assoc($listq)){
		$tdrow = array();
		while ( list( $key, $val ) = each($row)) {
			if($key == 'package_value'){
				if($val < 1){
					$tdrow[] = "Priceless";
				} else {
					$tdrow[] = sprintf("$%0.2f",$val);
				}
			} else {
				$tdrow[] = $val;
			}
		}
		$tab->addRow($tdrow, 'bgcolor="#aabbff" align="left"');
	}
	$tab->altRowAttributes(1, 'bgcolor="#dddddd"', 
						   'bgcolor="#ccccff"');

	return $res . $tab->toHTML();
} // end auctions


///MAIN
$sy = findSchoolYear();
$tmp = explode('-', $sy);
$sfyear = $tmp[1];

$cp =& new CoopPage();
$_SESSION['foo'] = 'foo';		// keep auth.inc happy


print $cp->header("Springfest $sfyear", 
				  "Join us for Springfest $sfyear!"); 
print "\n<hr></div> <!-- end header div -->\n";

print '<div id="leftCol">';
print sponsors(&$cp);
print '</div><!-- end leftcol div -->';


///// the main stuff
print '<div id="rightCol">';

// show year-specific HTML
$prettyname = sprintf("static/%s-springfest.template.html", 
					  $sfyear);
if(file_exists($prettyname)){
	print '<div id="springfestpretty">';
	include($prettyname);
	print "</div><!-- end springfestpretty div -->";
}


print auctionItems(&$cp, $sy);


print "<p><a href='../index.html'>Home</a></p>
";

print "</div><!-- end rightcol div -->";

print "</body></html>";
?>

<!-- PUBLIC AUCTION -->
