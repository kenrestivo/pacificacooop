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

require_once('CoopPDF.php');

// XXX PHP IS BROKEN? YOU CANNNOT CANNOT CANNOT HAVE LONG CLASS NAMES!?
// i originally named his class GiftCertificateREport, but PHP puked on it
class GCR extends CoopPDF
{
    var $template_file = 'gift-certificates.pdml';
    
// specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            /// set some defaults
            $this->fpdf->AddFont('bernhard-modern');
            $this->fpdf->font_size = array('18');
            $this->fpdf->font_face = array('bernhard-modern');

            // let the template know all about it

            $this->title = 'Springfest Packaging Gift Certificates';


            ////////////// GIFT CERTIFICATES 
            $giftcerts =& new CoopView(&$this, 'packages', &$nothing);
            $pt =& new CoopView(&$this, 'package_types', &$giftcerts);
            $giftcerts->protectedJoin($pt);
            $giftcerts->obj->whereAdd('item_type = "Gift Certificate"');
            $giftcerts->obj->whereAdd('(package_type_short = "Live" or package_type_short = "Silent")');

            $giftcerts->fullText= 1; // gotta have it

            if(devSite() && $_REQUEST['limit']){
                // XXX TEMPORARY HACK FOR TESTING
                $giftcerts->obj->limit($_REQUEST['limit']);
            }

            $giftcerts->find(true);
            $this->template->setRef('giftcerts', $giftcerts);

            // simple year.
            list($crap, $year) = explode('-', $giftcerts->getChosenSchoolYear());
            $year = 'Springfest ' . $year;
            $this->template->setRef('eventdate', $year);
    
        }
}


    $r =& new GCR($debug);
    $r->run();


?>