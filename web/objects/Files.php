<?php
/**
 * Table Definition for files
 */
require_once 'DB/DataObject.php';

class Files extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'files';                           // table name
    var $file_id;                         // int(32)  not_null primary_key unique_key auto_increment
    var $file_description;                // string(255)  
    var $original_filename;               // string(255)  
    var $disk_filename;                   // string(255)  
    var $school_year;                     // string(50)  
    var $file_date;                       // date(10)  
    var $upload_date;                     // datetime(19)  
    var $mime_type;                       // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Files',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE


	var	$kenPath = "../../files/"; // NOTE! for the tests folder

	function preGenerateForm()
		{

			$this->fb_preDefElements['original_filename'] =& 
				HTML_QuickForm::createElement('file', 
											   'original_filename', 
											   'File to upload:',
											  'maxfilesize=8388608');
                                        // 8MB s/b as big as i need
			// $uploadForm->addRule('original_filename', 
//                                  'You must select a file', 'uploadedfile');
		}

	function insert()
		{

			$actual =& $_FILES['original_filename'];			
			confessObject($actual, "actual_file");
			$unique_filename = sprintf("%d-%s", rand(1,200), 
                                       $actual['name']);
            // TODO also check for a size < 1... zero-byte file. or, js?
            if (is_uploaded_file($actual['tmp_name'])) {
				if(move_uploaded_file($actual['tmp_name'],
                                      $this->kenPath . $unique_filename)){
					//TODO add mimetype
					print "file uploaded!";
					// TODO: add the original_filename['name']
					// unique_file and to the db's field
					return parent::insert();
				}
			}
			
			return false;  // uh oh. boo boo.
		} // end insert

		function delete()
		{
			$result = parent::delete();
                               
			if ($result == false) {
				unlink($this->kenPath . $this->unique_filename);
			}
                               
			return $result;
		}
	
} 
