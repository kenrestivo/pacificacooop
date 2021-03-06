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


require_once('../includes/first.inc');
require_once('COOP/Page.php');
require_once('COOP/View.php');
require_once('lib/class.html2text.inc');


class EmailChanges
{
    var $page; //cache of cooppage object
    var $body; //cache
    var $subject; //cache
    var $type; // add, change, alert
    var $audit_co; // cache of reference to the audit coopobject
    var $record_co; // cache of the referene to the actual changed record coopobject

    function EmailChanges(&$page)
        {
            $this->page =&$page;

        }


    function get($audit_id)
        {
            // get audit details
            $this->audit_co =& new CoopView(&$this->page, 'audit_trail', 
                                            &$nothing);
            $this->audit_co->obj->get($audit_id);
            $this->audit_co->find(false);
            // my find does not return the number
            if($this->audit_co->obj->N < 1){
                PEAR::raiseError("audit trail [$this->audit_coit_id] doesn't exist!", 
                                 666);
            }
            $this->type = $this->audit_co->obj->details ? 'change' : 'add';
            
            //now get the record that was changed/added
            $this->record_co =& new CoopView(&$this->page, 
                                             $this->audit_co->obj->table_name, 
                                             &$nothing);
            $this->record_co->obj->get($this->audit_co->obj->index_id);
            
        }


    // returns TRUE if this is a valid audit,
    // FALSE if email has already been sent
    // other sanity checks may (should!) be added here later
    function sanityCheck()
        {

            // XXX should i check ->N instead?
            if(!$this->audit_co->obj->updated){
                printf("<p>THIS AUDIT %d IS INVALID! skipping.</p>",
                       $this->audit_co->obj->{$this->audit_co->pk});
                return false;
            }

            if($this->audit_co->obj->email_sent){
                printf("<p>THIS AUDIT %d HAS ALREADY BEEN EMAILED! skipping.</p>",
                       $this->audit_co->obj->{$this->audit_co->pk});
                return false;
            }
            return true;
        }


    function mailIt($to)
        {
            $user =& $this->audit_co->obj->getLink('audit_user_id');
            if($user->family_id){ // SOME USERS DON'T HAVE FAMILIES!
                $fam =& $user->getLink('family_id');
            }

            $headers['From'] = sprintf('%s <%s>',
                                       $user->name ? $user->name :
                                       'Pacifica Co-Op Nursery School',
                                       $fam->email ? $fam->email : 
                                       'members@pacificacoop.org');
            $headers['To'] = 	$to;
            $headers['Subject'] = $this->subject;
            
            $mail_object =& Mail::factory('smtp', &$params);

            /// XXX both expecterror and popexpect generate warnings

            // trap ugly adresses
            PEAR::pushErrorHandling(PEAR_ERROR_RETURN); // BEGINNING OF TRY
            $rv = $mail_object->send($to, 
                               $headers, 
                               $this->body);
            PEAR::popErrorHandling(); // not really catch, more like END OF TRY
            user_error("sent email to $to [{$this->subject}]", E_USER_NOTICE);
            // catch XXX this doesn't seem to work, i don't know why.
            if(PEAR::isError($rv)){
                confessObj($rv, 'mail error');
                $this->page->mailError('BAD EMAIL ADDRESS', print_r($rv, true));
            }
        }
    
    function makeEmail()
        {
            $this->body = '';
            // some shorthands to make typing easier
            $aud =& $this->audit_co;
            $rec =& $this->record_co;


            $audformatted = $aud->toArrayWithKeys();

            $this->subject = sprintf("[%s] %s %s: %s", 
                                     $this->page->devSite() ? 'TESTING': 'Pacifica Co-Op',
                                     strtoupper($this->type),
                                     $rec->obj->fb_formHeaderText,
                                     $rec->concatLinkFields());

            
            $this->body .= sprintf("%s\n\non %s by %s\n\n", 
                                   $this->subject,
                                   $audformatted['updated'],
                                   $audformatted['audit_user_id']);
            

            
            // NOTE! the formatted version may have 'no details found'
            // so test the obj version
            if($this->type == 'change'){
                $h2t =& new html2text($audformatted['details']);
                $this->body .= "The following changes were made:\n";
                $this->body .= $h2t->get_text();
            } else {
                // it's an add!
                $rec->fullText = 1; // XXX nasty hack!
                $headers = $rec->makeHeader();
                $recformatted = $rec->toArray($headers['keys']);
                //confessArray($recformatted, 'recformatted');
                foreach($headers['keys'] as $key){
                    $val = array_shift($recformatted);
                    $title = array_shift($headers['titles']);
                    $h2t =& new html2text($val);
                    $this->body .= sprintf("   %s: %s\n\n", 
                                           $title, 
                                           $h2t->get_text());
                }
            }

            // REMEMBER! i can't use selfurl here: it adds SID
            $this->body .= sprintf('%sFor more details, visit here: http://%s%s?action=details&table=%s&%s=%d',
                                   "\n\n",
                                   $_SERVER['HTTP_HOST'],
                                   COOP_GENERIC_TABLE_ENGINE_ABSOLUTE_URL_PATH,
                                   $this->audit_co->obj->table_name,
                                   $rec->prependTable($rec->pk),
                                   $this->audit_co->obj->index_id);

            

            $this->body .= sprintf('%sYou have chosen to receive these updates via email. You may change or cancel this by visiting: http://%s%s?table=subscriptions "',
                            "\n\n",
                                   $_SERVER['HTTP_HOST'],
                                   COOP_GENERIC_TABLE_ENGINE_ABSOLUTE_URL_PATH
                                   );
            $this->body .= "\n\n";
        }

    // saves flag in audit trail, showing that the email was in fact sent.
    function saveStatus()
        {
            $old = $this->audit_co->obj;
            $this->audit_co->obj->email_sent = 1;
            $this->audit_co->obj->update($old);
        }

     
} ///////////  END SENDEMAIL CLASS

function notifyAll(&$cp, $id)
{
/// THIS IS ONLY A HACK TO GET THE TABLENAME!
    $em =& new EmailChanges (&$cp);
    $em->get($id);

/// put on the condom!
    if(!$em->sanityCheck()){
        exit(1);
    }

    print "<p>--- Starting email notification for $id... ---</p>";

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
            or enrollment.dropout_date > "%s"
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
where  table_name = "%s" 
    and field_name is null 
    and subscription_id is not null
   and family_id > 0
group by subscriptions.user_id,table_name
', 
                // assuming they'll never be able to choose, to go retroactive
            $sub->page->currentSchoolYear,
            date('Y-m-d'),
            $em->audit_co->obj->table_name));


// XXX hack to deal with my slow spamassassin
 if($cp->devSite()){
     sleep(20);
 }


 print "<p>{$sub->obj->N} subscriptions found... </p>";


// TODO: if it is devsite, only fetch a few. really, limit is what i want
// unless i need the N to check that my query was ok
 while($sub->obj->fetch()){
     //confessObj($sub, 'subs');
     $fam =& new CoopObject(&$cp, 'families', &$sub);
     // condom
     if($sub->obj->family_id < 1){
         continue;
     }
     $fam->obj->get($sub->obj->family_id); // or just add email into query?

     if(!$fam->obj->email){
         $em->page->printDebug("skipping {$fam->obj->name}: no email address!", 
                               2);
         continue;
     }
    
     //might as well instantiate in loop. lightweight, and i need to get() anyway
     $em =& new EmailChanges (&$cp);

     // FORCE EACH USER! log them in forcibly, then re-get.
     // i don't like this at all.
     $em->page->forceUser($sub->obj->user_id);
     $em->get($id);

     // check if type is change/add, compare to subscription
     if(!(($em->type == 'change' && $sub->obj->changes) ||
          ($em->type == 'add' && $sub->obj->new_entries)))
     {
         $em->page->printDebug("skipping {$fam->obj->name}: not subscribed for {$em->type} on {$em->record_co->table}", 
                               2);
         continue;
     }

     // if they don't have view perms for this record, outtahere
     if($em->record_co->isPermittedField() < ACCESS_VIEW){
         $em->page->printDebug("user {$sub->obj->user_id} {$fam->obj->name} doesn't have perms to view this record at all, skipping", 4);
         continue;
     }

     // finally, if i should, let's do it.
     $em->makeEmail();

     ///for testing
     $crap = "\n----- MAILED TO  {$fam->obj->email} ({$fam->obj->name})---\n";

     if($cp->devSite()){
         global $coop_sendto;
         $to =  $coop_sendto['email_address'];
         $em->body .= $crap;
     } else {
         $to = $fam->obj->email;
         //PEAR::raiseError('force dev only', 888);
     }

     $em->mailIt($to);

    
     print '<pre>' . $crap . $em->body . '</pre>';

 }

 $em->saveStatus();
 print "<p>--- DONE with $id---</p>";

} // END NOTIFYALL


function gooseUnSent(&$cp)
{
    $auds =& new CoopObject(&$cp, 'audit_trail', &$nothing);
    $auds->obj->whereAdd('updated > "2005-10-27"');
    $auds->obj->whereAdd('email_sent is null');
    $found = $auds->obj->find();
    $msg = "$found un-notified changes found...";
    user_error($msg, E_USER_NOTICE);
    print "<p>$msg</p>";

    while($auds->obj->fetch()){
        notifyAll(&$cp, $auds->obj->audit_trail_id);
    }
    
}


/////////MAIN

ignore_user_abort(); // IMPORTANT!

$cp = new coopPage( $debug);
$cp->content_type = 'text/plain'; // VITALLY NECESSARY!
$cp->logIn(); //XXX HACK! to handle the cookies

if(!empty($_REQUEST['audit_id'])){
    notifyAll(&$cp, $_REQUEST['audit_id']);
} else {
    gooseUnSent(&$cp);
}

////KEEP EVERTHANG BELOW

?>


<!-- END SENDEMAIL -->
