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

    function makeForm()
        {

        }



    // specific to this page. when i dispatch with REST, i'll need several
    function build()
        {

            $this->title = 'Vote';

            $question_id = 1;



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
            $sel->addOption('VOTE', '0');

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

            // Define filters and validation rules
            $form->addRule('answer_id', 'Please choose something', 
                           'required', null, 'client');


            if ($form->validate()) {
                // save it!
            } else {
                $f = $form->toHTML();
            }



            $this->template->setRef('form', $f);
            

        }
}


$r =& new Vote($debug);
$r->run();


?>