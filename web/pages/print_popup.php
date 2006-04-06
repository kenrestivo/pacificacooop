<?php

	#  Copyright � 2004-2006  ken restivo <ken@restivo.org>
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

//$debug = 4;

require_once('CoopPDF.php');
require_once('ThankYou.php');


class ThankYouNote extends CoopPDF
{

    var $template_file = 'thank-you-note.pdml';


    function build()
        {
            // TODO: phish this font out of the database!
            // i.e. "use springfest font for this year!"
            $this->fpdf->AddFont('bernhard-modern');
            $this->fpdf->font_face = array('bernhard-modern');


            switch($_REQUEST['set']){
            case 'printed':
                $tid = $SOMETHING;
                $ty =& new ThankYou(&$this);
                $ty->recoverExisting($tid);
                $ty->substitute();
                print $ty->toHTML();
                break;
            case 'one':
                $ty =& new ThankYou(&$this);
                if(!$ty->findThanksNeeded($_REQUEST['pk'], $_REQUEST['id'])){
                    $this->thank_you_notes = array(
                        'This thank-you has already been entered. 
			Close this window and check &quot;View/Edit&quot; in the other window.');
                    break;
                }
                $ty->findThanksNeeded($_REQUEST['pk'], $_REQUEST['id']);
                $ty->substitute();
                $this->thank_you_notes = array($ty->toHTML());

                break;
            case 'needed':
            default:
                $tn =& new CoopView(&$this, 'thank_you', &$none);  
                
                $tn->obj->findThanksNeeded(&$tn);
                
                $this->thank_you_notes =& $tn;
                
                confessObj($this->thank_you_notes, 'wtf');

                break;
            }
        }


}



$r =& new ThankYouNote($debug);
$r->run();


?>
