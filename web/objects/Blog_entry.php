<?php
/**
 * Table Definition for blog_entry
 */
require_once 'DB/DataObject.php';

class Blog_entry extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'blog_entry';                      // table name
    var $blog_entry_id;                   // int(32)  not_null primary_key unique_key auto_increment
    var $family_id;                       // int(32)  
    var $short_title;                     // string(255)  
    var $body;                            // blob(16777215)  blob
    var $show_on_members_page;            // string(7)  enum
    var $show_on_public_page;             // string(7)  enum

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Blog_entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('show_on_members_page', 
								'show_on_public_page');
	var $fb_linkDisplayFields = array('short_title');
	var $fb_fieldLabels = array ('family_id' => 'Entered by Co-Op Family',
								 'short_title' => 'Headline',
								 'body' => 'Story',
								 'show_on_members_page' => 'OK to show on members-only page?',
								 'show_on_public_page' => 'OK to show on public web-site'
		);
	var $fb_fieldsToRender = array('family_id', 'short_title', 'body', 
                                   'show_on_members_page', 
								   'show_on_public_page');
	var $fb_formHeaderText =  'Breaking News';
	var $fb_shortHeader =  'Blog';
	var $fb_textFields = array('body');
	var $fb_requiredFields = array('family_id', 'short_title', 'body');
	var $fb_defaults = array('show_on_members_page' => 'Yes',
                             'show_on_public_page' => 'No');

    function postGenerateForm(&$form)
        {
            //confessObj($form, 'form');
            //confessArray(get_class_methods($form->CoopForm), 
            //get_class($form->CoopForm));
            $el =& $form->getElement(
                $form->CoopForm->prependTable('body'));
            $el->setRows(25);
            $el->setCols(80);
        }

    function fb_display_summary($publiconly = false)
        {
            if($this->CoopView->page->auth['token'] && !$publiconly){
                $clause = 'members'; 
            } else {
                $clause = 'public'; 
            }

            $this->query(sprintf("select distinct blog_entry.*,
                date_format(max(audit_trail.updated), '%%a %%m/%%d/%%Y %%l:%%i %%p') 
                        as update_human,
            users.name
			from blog_entry 
			left join audit_trail 
                   on audit_trail.index_id = blog_entry.blog_entry_id  
                        and audit_trail.table_name = 'blog_entry'
            left join users on audit_trail.audit_user_id = users.user_id
            where show_on_%s_page = 'yes'
             group by blog_entry_id
			order by updated desc
			limit 4", 
                                 $clause));
            while($this->fetch()){
                $res .= sprintf("<p><b>%s</b>&nbsp;%s</p><p>%s</p><p class=\"small\">(Posted %s by %s)</p><br>", 
                                $this->short_title, $this->body, 
                                $publiconly ? '' : 
                                $this->CoopView->recordButtons(&$this, false),
                                $this->update_human, $this->name
                    );
            }
            return $res;

        }


}
