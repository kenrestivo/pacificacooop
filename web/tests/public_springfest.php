<?php

	#  Copyright (C) 2004-2006  ken restivo <ken@restivo.org>
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

//$Id$

chdir('../');

require_once('CoopPage.php');
require_once('CoopNewDispatcher.php');
require_once "HTML/Template/PHPTAL.php";
require_once "lib/phptal_filters.php";
require_once('CoopIterator.php');  // XXX hack, around problems on nfsn

// yeah, this is ugly, legacy stuff. i must normalise this DB!
function sponsors(&$cp)
{

// now a word from our sponsors
	$res .= '<div class="sponsor">';
	$res .= "<p><b>Thanks to our generous sponsors:</b></p>";

		// check the sponsorship table, not calculate

	$st =& new CoopView(&$cp, 'sponsorship_types', &$nothing);
    //XXX MISERABLE HACK!!
    $sy = $cp->vars['last']['chosenSchoolYear'];
	$st->obj->whereAdd("school_year = '$sy'");
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
			/// normalise to 3NF and combine companies and leads into one!
			
			$table = $sp->obj->lead_id> 0 ? 'leads' : 'companies';
			$co =& new CoopObject(&$cp, $table, &$nothing);
			$co->obj->{$co->pk} = $sp->obj->{$co->pk};
			$co->obj->find(true);
			//confessObj($co->obj, 'co');
			// when i redo it, this is where the test for existing goes
			if($co->obj->url > ''){
                // ummmm, use selfurl?
				$thing = sprintf(
                    '<a href="%s">%s</a>', 
                    $cp->fixURL($co->obj->url),
                    $co->obj->listing? $co->obj->listing : $co->obj->company_name);
			} else {
				//XXX cheap congeal: company-lead hack
				$thing = $co->obj->listing ? $co->obj->listing : $co->obj->company_name . $co->obj->company;
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
			'<p><b>%s Contributors</b><br /><span class="small">($%.0f and above)</span></p><ul>%s</ul>', 
			$level, $data['price'], $sponsors);
		$sponsors ='';
	}
	
	$res .= "</div><!-- end sponsor -->";
	return $res;
} // end sponsors


/// again, truly disgusting, due to the fact that the db is not normalised
function donors(&$cp)
{
	$res .= '<div class="sponsor">';
	$res .= "<p><b>And Our Donors:</b></p>";
	$companies =& new CoopObject(&$cp, 'companies', &$nothing);
    $sy = $cp->vars['last']['chosenSchoolYear'];
	$companies->obj->query(
"select distinct companies.*
from companies
left join companies_auction_join 
on companies_auction_join.company_id = companies.company_id 
left join auction_donation_items 
on companies_auction_join.auction_donation_item_id = auction_donation_items.auction_donation_item_id
and auction_donation_items.school_year = '$sy'
left join companies_income_join 
on companies_income_join.company_id = companies.company_id
left join income
on companies_income_join.income_id = income.income_id
and income.school_year = '$sy'
left join companies_in_kind_join 
on companies_in_kind_join.company_id = companies.company_id
left join in_kind_donations
on companies_in_kind_join.in_kind_donation_id = in_kind_donations.in_kind_donation_id
and in_kind_donations.school_year = '$sy'
left join sponsorships on sponsorships.company_id = companies.company_id
left join ads on ads.company_id = companies.company_id
where
(income.payment_amount > 0
or auction_donation_items.item_value > 0
or in_kind_donations.item_value > 0)
and ads.ad_id is null
and sponsorships.sponsorship_id is null
order by if(companies.listing is not null, companies.listing, companies.company_name), companies.last_name");
	$res .= "<ul>";
	while($companies->obj->fetch()){
		if($companies->obj->url > ''){
			$res .= sprintf('<li><a href="%s">%s</a></li>', 
							 $cp->fixURL($companies->obj->url),
							 $companies->obj->listing? $companies->obj->listing : $companies->obj->company_name);
		} else {
			$res .= sprintf("<li>%s</li>", 
                            $companies->obj->listing? $companies->obj->listing : $companies->obj->company_name);
		}
	}
	$res .= "</ul></div><!-- end ad div -->";

	return $res;
}




// specific to this page. when i dispatch with REST, i'll need several
function &build(&$page)
{

    ///////////// handle the no-year-equals-this-year navigation
    // must do this before choosign template
    $path = explode('/', $_SERVER['PATH_INFO']);
    if(preg_match('/^\d{4}$/', $path[1])){
        $sy = $path[1];
        $nav = $path[2];
    } else {
        $nav = $path[1];
        list($nothing, $sy) = explode('-', $page->currentSchoolYear);
    }
    // bah! gotta put it in vars, because that's where view fishes it out of
    $page->vars['last']['chosenSchoolYear'] = sprintf('%d-%d', $sy -1, $sy);


    // let the template know all about it
    $template = new PHPTAL('springfest-microsite-shell.xhtml');

    
    /// menu
    $menu = array('home' => array('class' => 'nav',
                                  'content' => 'Overview'),
                  'event' => array('class' => 'nav',
                                   'content' => 'Where and When'),
                  'sponsorship' => array('class' => 'nav',
                                         'content' => 'Sponsorship'),
                  'auction' => array('class' => 'nav',
                                     'content' => 'Auction'),
                  'raffle' => array('class' => 'nav',
                                    'content' => 'Surfboard Raffle'),
                  'about' => array('class' => 'nav',
                                   'content' => 'About Us')
        );

    /// split up the nav sizes
    $css = '<style type="text/css">';
    $menu_width = 100.0/count(array_keys($menu));
    foreach(array('a.nav:link', 'a.nav:visited', 'a.nav:hover', 
                  'a.nav:active', 'a.navcurrent') as $selector)
    {
        $css .= sprintf('%s { width: %0.2f%% } ', 
                        $selector, $menu_width);
    }
    $css .= '</style>';
    $template->setRef('extra_css', $css);
    

    /////////////// set current nav
    if(in_array($nav, array_keys($menu))){
        $menu[$nav]['class'] = 'navcurrent';
    } else {
        $menu['home']['class'] = 'navcurrent';
    }
    
    $template->setRef('nav', $menu);


    // every page has sponsorships, so do it
    $spons = sponsors(&$page);
    $spons .= donors(&$page);
    $template->setRef('sponsors', $spons);


    ////////////// object time. PLACEHOLDER
    $families =& new CoopView(&$page, 'families', &$nothing);
    $families->find(true);
    $page->title = 'Springfest ' . $sy;
    $template->setRef('families', $families);

    $page->printDebug("sy $sy nav $nav ". $families->getChosenSchoolYear(), 1);


    return $template;
}



//////// MAIN
$cp =& new coopPage( $debug);


// got to RUN certain things before anything makes sense
$cp->logIn();


$template =& build(&$cp);

// NOTE: if this ref is unavailable, the whole page fails except done()
$template->setRef('page', $cp);


//confessObj($template->getContext(), 'context');


$template->addOutputFilter(new XML_to_HTML());

if(headers_sent($file, $line)){
    PEAR::raiseError("headers sent at $file $line ", 666);
}
print  $template->execute();
$cp->finalDebug();


?>