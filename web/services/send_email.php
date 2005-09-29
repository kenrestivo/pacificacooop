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
    var $body; //cache
    var $subject; //cache


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
    
    function makeEmail($audit_id)
        {
            $this->body = '';

            $aud =& new CoopView(&$this->page, 'audit_trail', &$nothing);
            $aud->obj->get($audit_id);
            $aud->find(false);
            // my find does not return the number
            if($aud->obj->N < 1){
                PEAR::raiseError("audit trail [$audit_id] doesn't exist!", 666);
            }
            $audheaders = $aud->makeHeader();
            $tmp = $aud->toArray($audheaders['keys']);
            // fucking array_combine, dude
            foreach($audheaders['keys'] as $key){
                $audformatted[$key] = array_shift($tmp);
            }

            // TODO: get the formatted edit date and family, i.e. in public blog
            //confessArray($audformatted, 'audformatted');
            
            $rec =& new CoopView(&$this->page, $aud->obj->table_name,
                                 &$nothing);
            $rec->obj->get($aud->obj->index_id);

            $this->subject .= sprintf("%s NOTICE for %s: %s", 
                                      $aud->obj->details ? 'CHANGE' : 'ADD',
                                      $rec->obj->fb_formHeaderText,
                                      $rec->concatLinkFields());

            
            $this->body .= sprintf("%s (%s by %s)\n\n", 
                                   $this->subject,
                                   $audformatted['updated'],
                                   $audformatted['audit_user_id']);
            

            
            // NOTE! the formatted version may have 'no details found'
            // so test the obj version
            if($aud->obj->details){
                $this->body .= 'The following changes were made: ';
                $this->body .= $audformatted['details'];
            } else {
                $rec->fullText = 1; // XXX nasty hack!
                $headers = $rec->makeHeader();
                $recformatted = $rec->toArray($headers['keys']);
                //confessArray($recformatted, 'recformatted');
                foreach($headers['keys'] as $key){
                    $val = array_shift($recformatted);
                    $title = array_shift($headers['titles']);
                    $this->body .= sprintf("   %s: %s\n\n", $title, $val);
                }
            }

            ///XXX broken until i fix the REQUEST crap in generic
            // REMEMBER! i can't use selfurl here: it adds SID
//             $this->body .= sprintf('http://%s%s?table=%s&%s=%d',
//                             $_SERVER['SERVER_NAME'],
//                             '/generic.php',
//                             $aud->obj->table_name,
//                             $rec->prependTable($rec->pk),
//                             $aud->obj->index_id);
            

            $this->body .= sprintf('%sYou have chosen to receive these updates via email. You may change or cancel this by visiting: http://%s/members/ under "Subscriptions:Settings"',
                            "\n\n",
                            $_SERVER['SERVER_NAME']);
        }
    
} // END SENDEMAIL CLASS


/////////MAIN

$cp = new coopPage( $debug);


//TODO: foreach through the users
$em =& new EmailChanges (&$cp);

// TODO: FORCE EACH USER! log them in forcibly
$em->makeEmail($_REQUEST['audit_id']);

///XXX for testing
global $coop_sendto;
$to =  $coop_sendto['email_address'];
print '<pre>'.$em->body.'</pre>';



////KEEP EVERTHANG BELOW

?>


<!-- END SENDEMAIL -->
