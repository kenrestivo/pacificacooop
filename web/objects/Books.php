<?php
/**
 * Table Definition for books
 */
require_once 'CoopDBDO.php';

class Books extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'books';                           // table name
    var $books_id;                        // int(32)  not_null primary_key unique_key auto_increment
    var $isbn;                            // string(10)  
    var $title;                           // string(255)  
    var $authors;                         // string(255)  
    var $book_color_id;                   // int(32)  
    var $primary_book_category_id;        // int(32)  
    var $secondary_book_category_id;      // int(32)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Books',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_linkDisplayFields = array('title', 'authors');

	var $fb_fieldLabels = array(
        'isbn' => 'ISBN Number',
        'title' => 'Book Title',
        'authors' => 'Authors (separated by commas)',
        'primary_book_category_id' => 'Primary Category',
        'secondary_book_category_id' => 'Secondary Category'
		);

	var $fb_formHeaderText =  'Library Books';

	var $fb_requiredFields = array('isbn',
                                   'title',
								   'authors');

    var $fb_shortHeader = 'Books';

    
    var $fb_dupeIgnore = array('title' , 'authors');


    var $fb_sizes = array(
        'title' => 100,
        'authors' => 50
        );

    function preGenerateForm(&$form)
        {
            require_once('lib/isbninput.php');
            require_once('lib/titlesearch.php');

            $isbn =& $form->createElement('isbninput', 
                                        $form->CoopForm->prependTable('isbn'),
                                        $this->fb_fieldLabels['isbn']);
            
            $isbn->prepare(&$form);
            $this->fb_preDefElements['isbn'] =& $isbn;



            $title =& $form->createElement('titlesearch', 
                                        $form->CoopForm->prependTable('title'),
                                        $this->fb_fieldLabels['title']);
            
            $title->prepare(&$form);
            $this->fb_preDefElements['title'] =& $title;
            

        }

    function postGenerateForm(&$form)
        {
            //XXX this is really ugly!
            //the size attribute from $fb_sizes should be respected
            //in cases where i have a predefelement, shouldn't it?
            $form->updateElementAttr(
                array($form->CoopForm->prependTable('title')),
                array('size' => '70'));
        }

}
