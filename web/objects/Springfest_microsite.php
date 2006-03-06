<?php
/**
 * Table Definition for springfest_microsite
 */
require_once 'CoopDBDO.php';

class Springfest_microsite extends CoopDBDO 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'springfest_microsite';            // table name
    var $springfest_microsite_id;         // int(32)  not_null primary_key unique_key auto_increment
    var $url_fragment;                    // string(50)  
    var $name;                            // string(255)  
    var $content_summary;                 // blob(16777215)  blob
    var $_cache_content_summary;          // string(255)  
    var $content_continued;               // blob(16777215)  blob
    var $_cache_content_continued;        // string(255)  
    var $school_year;                     // string(50)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Springfest_microsite',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


    function get_menu_structure(&$co)
        {
            // XXX note, this isn't in ync with the rest, which take $sy as arg
            $co->schoolYearChooser();
            
            $res = array();

            $co->obj->query(
                sprintf('select url_fragment, name from %s 
                      where school_year = "%s" order by display_order',
                        $co->table,
                        $co->getChosenSchoolYear()));
            
            while($co->obj->fetch()){
                $res[$co->obj->url_fragment] = array(
                    'class' => 'nav',
                    'content' => $co->obj->name);
            }
            return $res;
        }

}
