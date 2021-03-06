<?php
/**
 * Table Definition for blog_entry
 */
require_once 'DB/DataObject.php';

class Blog_entry extends CoopDBDO 
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
    var $_cache_body;                     // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Blog_entry',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE
	var $fb_enumFields = array ('show_on_members_page', 
								'show_on_public_page');
	var $fb_linkDisplayFields = array('short_title');
	var $fb_fieldLabels = array (
        'short_title' => 'Headline',
        'body' => 'Story',
		'family_id' => 'Entered by Co-Op Family',
        'show_on_members_page' => 'OK to show on members-only site?',
        'show_on_public_page' => 'OK to show on public (non-member) home page?'
		);
	var $fb_fieldsToRender = array('family_id', 'short_title', 'body', 
                                   'show_on_members_page', 
								   'show_on_public_page');
	var $fb_formHeaderText =  'Bulletin Board';
	var $fb_shortHeader =  'Bulletin Board';
	var $fb_textFields = array('body');
	var $fb_requiredFields = array('family_id', 'short_title', 'body');
	var $fb_defaults = array('show_on_members_page' => 'Yes',
                             'show_on_public_page' => 'No');


    var $fb_sizes = array('short_title' => 50, 
                          'body' => 100);

//     function linkConstraints(&$co)
//         {
//             $aud =& $this->factory('audit_trail');
//             $this->orderBy('updated desc');
//         }




    function homepage_summary(&$co, $publiconly = false)
        {
            if($co->page->auth['token'] && !$publiconly){
                $clause = 'members'; 
            } else {
                $clause = 'public'; 
            }

            $this->query(sprintf('select blog_entry.*,
date_format(audits.updated, "%%a %%m/%%d/%%Y %%l:%%i%%p") as update_human,
users.name
from (select * from audit_trail as aud1,
    (select table_name, index_id, max(updated) as updated
        from audit_trail
        group by table_name, index_id) as audmax
    where aud1.table_name = "blog_entry"
          and audmax.table_name = aud1.table_name 
          and audmax.index_id = aud1.index_id
          and aud1.updated = audmax.updated) as audits
left join blog_entry
on audits.index_id = blog_entry.blog_entry_id  
and audits.table_name = "blog_entry"
left join users on audits.audit_user_id = users.user_id
where show_on_%s_page = "yes"
 group by blog_entry_id
order by updated desc
limit 4', 
                                 $clause));
            while($this->fetch()){
                $res .= sprintf("<div><b>%s</b>&nbsp;%s</div><div class=\"actions\">%s</div><p class=\"small\">Posted %s by %s</p><br />\n\n", 
                                $this->short_title, $this->body, 
                                $publiconly ? '' : 
                                $co->recordButtons(&$this, false),
                                $this->update_human, 
                                $this->name
                    );
                // TODO: put a selfurl to the family details, IFF $clause members
            }
            return $res;

        }


}
