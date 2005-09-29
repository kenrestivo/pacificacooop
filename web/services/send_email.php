<?php
/*
   $Id$

	Copyright (C) 2005  ken restivo <ken@restivo.org>
	 
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	 This program is distributed in the hope that it will be useful,
	 but WITHOUT ANY WARRANTY; without even the implied warranty of
	 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	 GNU General Public License for more details. 
	
	 You should have received a copy of the GNU General Public License
	 along with this program; if not, write to the Free Software
	 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


require_once('CoopPage.php');
require_once('CoopView.php');


class EmailChanges
{
    var $page; //cache of cooppage object


    function EmailChanges(&$page)
        {
            $this->page =&$page;
            
        }

    function mailIt($to, $body)
        {
            
            $headers['From']    = 'members@pacificacoop.org';
            $headers['To']      = 	$to;
            $headers['Subject'] = $subject;
            
            $mail_object =& Mail::factory('smtp', $params);
            
            $mail_object->send($to, 
                               $headers, 
                               $body);
        }
    
    function makeBody($audit_id)
        {
            $res = '';

            $aud =& new CoopView(&$this->page, 'audit_trail', &$nothing);
            $aud->obj->get($audit_id);
            $aud->find(false);
            // my find does not return the number
            if($aud->obj->N < 1){
                PEAR::raiseError("audit trail [$audit_id] doesn't exist!", 666);
            }
            $audformatted = $aud->toArray();

            // TODO: get the formatted edit date and family, i.e. in public blog
            //confessArray($audformatted, 'audformatted');
            
            $rec =& new CoopView(&$this->page, $aud->obj->table_name, 
                                 &$nothing);
            $rec->obj->get($aud->obj->index_id);
            $res .= sprintf("NOTICE for %s: %s (%s)\n\n", 
                            $rec->obj->fb_formHeaderText,
                            $rec->concatLinkFields(),
                            $aud->obj->updated);
            
            
            // NOTE! the formatted version may have 'no details found'
            // so test the obj version
            if($aud->obj->details){
                $res .= $audformatted['details'];
            } else {
                $rec->fullText = 1; // XXX nasty hack!
                $headers = $rec->makeHeader();
                $recformatted = $rec->toArray($headers['keys']);
                //confessArray($recformatted, 'recformatted');
                foreach($headers['keys'] as $key){
                    $val = array_shift($recformatted);
                    $title = array_shift($headers['titles']);
                    $res .= sprintf("%s: %s\n", $title, $val);
                }
            }
            

            //TODO: disclaimer? link? something?
            


            return $res;
        }
    
} // END SENDEMAIL CLASS


/////////MAIN

$cp = new coopPage( $debug);


//TODO: foreach through the users
$em =& new EmailChanges (&$cp);

// TODO: FORCE EACH USER! log them in forcibly
$body = $em->makeBody($_REQUEST['audit_id']);

///XXX for testing
global $coop_sendto;
$to =  $coop_sendto['email_address'];
print '<pre>'.$body.'</pre>';




////KEEP EVERTHANG BELOW

?>


<!-- END SENDEMAIL -->
