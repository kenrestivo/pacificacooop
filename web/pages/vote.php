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

require_once('../includes/first.inc');
require_once('COOP/Page/TAL.php');
require_once('COOP/View.php');
require_once('COOP/Form.php');


class Vote extends CoopTALPage
{
    var $question_id; /// XXX HACK
    var $template_file = 'vote.xhtml';


    function makeForm()
        {

            $form = new HTML_QuickForm('vote', false, false, false, 
                                       array('id' => 'vote'), true);
            $form->removeAttribute('name'); // make XTHML happy



            $quest =& new CoopObject(&$this, 'questions', &$none);
            $quest->fullText = 1; // making their lives easier
            $quest->obj->whereAdd(sprintf('question_id = %d',
                                          $this->question_id));
            $quest->obj->find(true);
            $form->addElement('header', null, $quest->obj->question);
            
            $sel =& $form->addElement('select', 'answer_id', '', 
                                        $nothing);
            $sel->addOption('-- CHOOSE ONE --', '0');

            $ans =& new CoopObject(&$this, 'answers', &$none);
            $ans->obj->whereAdd(sprintf('question_id = %d', 
                                        $this->question_id));
            $ans->fullText = 1; // making their lives easier
            $ans->obj->find();
            $sel->loadDbResult($ans->obj->getDatabaseResult(), 
                               'answer', 'answer_id');

            if($sid = thruAuthCore($this->auth)){
                $form->addElement('hidden', 'coop', $sid); 
            }

            $form->addElement('submit', null, 'Vote');

            
            $form->addRule('answer_id', 'Please choose something', 
                                 'CustomRequired', NULL, 'client');


            $form->registerRule('customrequired', NULL, 
                                'CustomRequired', 
                                'lib/customrequired.php');
            

            $form->addRule('answer_id', 'Please choose something', 
                           'required', null, 'client');


            if ($form->validate()) {
                // save it!
                $votes =& new CoopObject(&$this, 'votes', &$none);
                $votes->obj->family_id = $this->userStruct['family_id'];
                $votes->obj->question_id = $this->question_id;
                $votes->obj->school_year =   $this->currentSchoolYear;
                $vals = $form->exportValues();
                $votes->obj->answer_id = $vals['answer_id'];
                $votes->obj->insert();
                //XXX need *some* audit trail!
                $this->status = 'Thanks! Your vote has been counted.';
                $this->buildVoteSummary();
            } else {
                $this->formtext = $form->toHTML();
            }



        }

    function buildVoteSummary()
        {
            //XXX temporary hacque
            //this will ultimately just be a simpletable of votes
            //since votes *are* summaries
            $res = array();

            $q =& new CoopObject(&$this, 'questions', &$none);
            $q->obj->find();
            while($q->obj->fetch()){
                $res[$q->obj->{$q->pk}] = array();
                $res[$q->obj->{$q->pk}]['question'] = $q->obj->question;
                $res[$q->obj->{$q->pk}]['votes'] = array();
                $votes =& new CoopObject(&$this, 'votes', &$none);
                $votes->obj->query(
                    sprintf('select answers.answer, answers.answer_id,
         count(votes.answer_id) as votecount 
from answers
left join votes on votes.answer_id = answers.answer_id
where answers.question_id = %d
group by answers.answer_id
order by votes.school_year asc,  votecount desc',
                            $q->obj->question_id));
                while($votes->obj->fetch()){
                    $res[$q->obj->{$q->pk}]['votes'][$votes->obj->answer_id] = 
                        array('answer' => $votes->obj->answer, 
                              'votecount' => $votes->obj->votecount);
                }

                //XXX nasty hack, until i fix perms: only if bored member
                $ug =& new CoopObject(&$this, 'users_groups_join', &$none);
                $ug->obj->whereAdd(sprintf('group_id = %d and user_id = %d',
                                           COOP_BOARD_MEMBER_GROUP_ID, 
                                           $this->auth['uid']));
                $ug->obj->find(true);
                if($ug->obj->N > 0){
                    $res[$q->obj->{$q->pk}]['families'] = array();
                    $fams =& new CoopObject(&$this, 'families', &$none);
                    $fams->obj->query(
                        sprintf('
select enrolled.*, this_question.question_id from
(select distinct  families.*
   from families
       left join kids on families.family_id = kids.family_id 
       left join enrollment on kids.kid_id = enrollment.kid_id 
   where enrollment.school_year = "%s"
   and ((enrollment.dropout_date < "1900-01-01"
       or enrollment.dropout_date is null)
       or enrollment.dropout_date > now())
   group by families.family_id
   order by families.name) as enrolled
left join  
(select * from votes where question_id = %d) as this_question
on this_question.family_id = enrolled.family_id
where this_question.question_id is null',
                                $q->getChosenSchoolYear(),
                                $q->obj->question_id));
                    while($fams->obj->fetch()){
                        $res[$q->obj->{$q->pk}]['families'][$fams->obj->{$fams->pk}] = $fams->obj->toArray();
                    
                    }
                }
            }
            
            $this->confessArray($res, 'results', 4);
            $this->results = $res;
            
        }


    // specific to this page. when i dispatch with REST, i'll need several
    function build()
        {
            $this->title = 'Membership Vote';
            $this->status = 'Please vote below on this important issue:';

            $this->question_id = 1; // XXX hack, make more flexible

            $votes =& new CoopObject(&$this, 'votes', &$none);
            $votes->obj->whereAdd(
                sprintf('family_id = %d and question_id = %d and school_year = "%s"',
                        $this->userStruct['family_id'],
                        $this->question_id,
                        $this->currentSchoolYear));
            $votes->obj->find();
            if($votes->obj->N > 0){
                $this->status = 'You have already voted.';
                $this->buildVoteSummary();
            } else {
                $this->makeForm();
            }
            
                
        }


}




$r =& new Vote($debug);
$r->run();



?>


<!-- END VOTE -->

