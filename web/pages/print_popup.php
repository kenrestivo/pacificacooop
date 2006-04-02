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
require_once('ThankYou.php');


class ThankYouNote extends CoopPDF
{

    var $template_file = 'thank-you-note.pdml';


    function build()
        {
            // TODO: use that nice funky font?

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
                $ty =& new CoopObject(&$this , 'thank_you', &$none);
                $this->thank_you_notes = $ty->obj->thanksNeededSummary(&$ty, 
                                                                       'pdml');

                break;
            }
        }


}



$r =& new ThankYouNote($debug);
$r->run();


?>
