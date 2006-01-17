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


function sponsors(&$cp, $sy)
{

// now a word from our sponsors
	$res .= '<div class="sponsor">';
	$res .= "<p><b>Thanks to our generous sponsors:</b></p>";
	$tab =& new HTML_Table();

		// check the sponsorship table, not calculate

	$st =& new CoopObject(&$cp, 'sponsorship_types', &$nothing);
	$st->obj->school_year = $sy;
	$st->obj->orderBy('sponsorship_price desc');
	$st->obj->find();
	while($st->obj->fetch()){
		//confessObj($st->obj, 'st obh');
		$sp =& new CoopObject(&$cp, 'sponsorships', &$nothing);
		$sp->obj->{$st->pk} = $st->obj->{$st->pk};
		$sp->obj->find();
		while($sp->obj->fetch()){
			//confessObj($sp->obj, 'sponb');
			/// XXX i hate hate hate this database layout.
			/// renormalise and group companies and leads into one!
			
			$table = $sp->obj->lead_id> 0 ? 'leads' : 'companies';
			$co =& new CoopObject(&$cp, $table, &$nothing);
			$co->obj->{$co->pk} = $sp->obj->{$co->pk};
			$co->obj->find(true);
			//confessObj($co->obj, 'co');
			// when i redo it, this is where the test for existing goes
			if($co->obj->url > ''){
				$thing = sprintf('<a href="%s">%s</a>', 
								 $cp->fixURL($co->obj->url),
								 $co->obj->company_name);
			} else {
				//XXX cheap congeal: company-lead hack
				$thing = $co->obj->company_name . $co->obj->company;
				if(!$thing){
					$thing = sprintf("%s %s", $co->obj->first_name,
									 $co->obj->last_name);
				}
				
				$spons[$st->obj->sponsorship_name]['price'] = 
					$st->obj->sponsorship_price;
				$spons[$st->obj->sponsorship_name]['names'][] = $thing;
			}
			
		}
	}
	
	// gah. whew. all done
	//confessArray($spons, 'spns');
	foreach($spons as $level => $data){
		sort($data['names']);
		//confessArray($data, 'data');
		foreach($data['names'] as $name){
			$sponsors .= sprintf("<li>%s</li>", $name);
		}
		$res .= sprintf(
			'<p><b>%s Contributors</b> <span class="small">($%.0f and above)</span></p><ul>%s</ul>', 
			$level, $data['price'], $sponsors);
		$sponsors ='';
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
	if(mysql_num_rows($listq) < 1){
		return "<p>Coming soon! Watch this space for fabulous auction items.</p>";
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
		$tab->addRow($tdrow, 'style="tableheader"');
	}
    $tab->altRowAttributes(1, 'class="altrow1"', 
                           'class="altrow2"');


	return $res . $tab->toHTML();
} // end auctions

function ads(&$cp, $sy)
{
	$res .= '<div class="sponsor">';
	$res .= "<p><b>And our advertisers:</b></p>";
	$ad =& new CoopObject(&$cp, 'ads', &$nothing);
	$ad->obj->query("select distinct * from ads left join companies on companies.company_id = ads.company_id left join sponsorships on companies.company_id = sponsorships.company_id where ads.school_year = '$sy' and sponsorship_id is null order by company_name");
	$res .= "<ul>";
	while($ad->obj->fetch()){
		if($ad->obj->url > ''){
			$res .= sprintf('<li><a href="%s">%s</a></li>', 
							 $cp->fixURL($ad->obj->url),
							 $ad->obj->company_name);
		} else {
			$res .= sprintf("<li>%s</li>", $ad->obj->company_name);
		}
	}
	$res .= "</ul></div><!-- end ad div -->";

	return $res;
}



///MAIN
$sy = findSchoolYear();
$tmp = explode('-', $sy);
$sfyear = $tmp[1];

$cp =& new CoopPage();
$_SESSION['foo'] = 'foo';		// keep auth.inc happy




$cp->title = "Springfest $sfyear"; 
print $cp->header();
print "<h2>Join us for Springfest $sfyear!</h2>"; 
print "\n<hr /></div> <!-- end header div -->\n";

print '<div id="leftCol">';
print sponsors(&$cp, $sy);
print ads(&$cp, $sy);
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


print "<p><a href='../'>Home</a></p>
";

print "</div><!-- end rightcol div -->";

print "</body></html>";
?>

<!-- PUBLIC AUCTION -->
