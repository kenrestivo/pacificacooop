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
			//confessObject($actual, "actual_file");
			$unique_filename = sprintf("%d-%s", rand(1,200), 
                                       $actual['name']);
            // TODO also check for a size < 1... zero-byte file. or, js?
            if (is_uploaded_file($actual['tmp_name']) &&
				move_uploaded_file($actual['tmp_name'],
                                   $this->kenPath . $unique_filename) &&
                chmod($this->kenPath . $unique_filename, 0644) )
            {
                
                //OK dump all the data about the file into the db
                $this->disk_filename = $unique_filename;
                $this->original_filename = $actual['name'];
                //$this->school_year = findSchoolYear(); // generify?
                $this->mime_type = $actual['type'];
                $this->upload_date = date("Y-m-d H:m:s");
                $this->file_size = $actual['size'];
                return parent::insert();
            }
            
            return false;  // uh oh. boo boo.
		} // end insert

    function delete()
		{
			$result = parent::delete();
                  
            //print "RESULT[$result]";
			if ($result) {
				unlink($this->kenPath . $this->disk_filename);
			}
                               
			return $result;
		}
	
} 
