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

require_once('CoopTALPage.php');
require_once('CoopForm.php');
require_once('lib/dbdo_iterator.php');  // NASTY!

class Vote extends CoopTALPage
{
    var $template_file = 'vote.xhtml';
    var $question_id; /// XXX HACK

    function makeForm($question_id)
        {
            $question_id = $this->question_id;

            $form = new HTML_QuickForm('vote', false, false, false, 
                                       array('id' => 'vote'), true);
            $form->removeAttribute('name'); // make XTHML happy



            $quest =& new CoopObject(&$this, 'questions', &$none);
            $quest->fullText = 1; // making their lives easier
            $quest->obj->whereAdd("question_id = $question_id");
            $quest->obj->find(true);
            $form->addElement('header', null, $quest->obj->question);
            
            $sel =& $form->addElement('select', 'answer_id', '', 
                                        $nothing);
            $sel->addOption('-- CHOOSE ONE --', '0');

            $ans =& new CoopObject(&$this, 'answers', &$none);
            $ans->obj->whereAdd("question_id = $question_id");
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
                
            } else {
                $f = $form->toHTML();
            }



            $this->template->setRef('form', $f);

        }



    // specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            //XXX HACK
            $this->userStruct =  getUser($this->auth['uid']);

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
                // XXX ANSTY
                $this->template->setRef('form', $nothing);
            } else {
                $this->makeForm();
            }


            $this->template->setRef('status', $this->status);

            // TODO: show count of how many families have voted so far
                
        }
}


$r =& new Vote($debug);
$r->run();


?>