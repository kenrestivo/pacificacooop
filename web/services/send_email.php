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

            $this->subject = sprintf("%s NOTICE for %s: %s", 
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
            $this->body .= "\n\n";
        }
    
} // END SENDEMAIL CLASS


/////////MAIN

$cp = new coopPage( $debug);




$sub =& new CoopObject(&$cp, 'subscriptions', &$nothing);
$sub->obj->query(
    sprintf('
select subscriptions.*, upriv.user_id, upriv.family_id,
table_permissions.table_name, table_permissions.field_name,
max(if((upriv.max_user <= table_permissions.user_level or
table_permissions.user_level is null), 
upriv.max_user, table_permissions.user_level)) as cooked_user,
max(if((upriv.max_group >=  table_permissions.group_level or
table_permissions.group_level is null), 
upriv.max_group, NULL )) as cooked_group,
 max(if((upriv.max_user > table_permissions.menu_level or
table_permissions.menu_level is null), 
upriv.max_user, NULL)) as cooked_menu,
max(if((upriv.max_year > table_permissions.user_level or table_permissions.year_level is null),
upriv.max_year, table_permissions.year_level)) as cooked_year
from subscriptions
left join table_permissions 
    on table_permissions.realm_id = subscriptions.realm_id
left join 
(select enrolled.user_id, enrolled.family_id,
    max(user_level) as max_user, max(group_level) as max_group, 
    max(year_level) as max_year,
    user_privileges.realm_id
    from 
    (select distinct users.user_id, families.family_id, families.email
        from users
             left join families on families.family_id = users.family_id
            left join kids on families.family_id = kids.family_id 
            left join enrollment on kids.kid_id = enrollment.kid_id 
        where enrollment.school_year = "%s"
        and (enrollment.dropout_date < "1900-01-01"
            or enrollment.dropout_date is null)
        group by families.family_id
        order by families.name) as enrolled
    left join user_privileges
        on enrolled.user_id = user_privileges.user_id
             or user_privileges.group_id in 
                 (select group_id 
                   from users_groups_join 
                   where user_id = enrolled.user_id)
     group by enrolled.user_id, realm_id
     order by enrolled.user_id, realm_id) as upriv
on upriv.realm_id = table_permissions.realm_id 
    and upriv.user_id = subscriptions.user_id
where  table_name = "blog_entry" 
    and field_name is null 
    and subscription_id is not null
group by subscriptions.user_id,table_name
', 
            // assuming they'll never be able to choose, to go retroactive
$sub->page->currentSchoolYear));
while($sub->obj->fetch()){
    //confessObj($sub, 'subs');
    $fam =& new CoopObject(&$cp, 'families', &$sub);
    $fam->obj->get($sub->obj->family_id);
    

    $em =& new EmailChanges (&$cp);
// TODO: FORCE EACH USER! log them in forcibly
    //$em->page->
    $em->makeEmail($_REQUEST['audit_id']);
    
///XXX for testing
    $em->body .= "----- FAKE MAILING TO  {$fam->obj->email} ({$fam->obj->name})---";
    global $coop_sendto;
    $to =  $coop_sendto['email_address'];
    print '<pre>'.$em->body.'</pre>';
 
}

////KEEP EVERTHANG BELOW

?>


<!-- END SENDEMAIL -->
