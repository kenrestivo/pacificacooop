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


	function postGenerateForm(&$uploadForm)
		{
			$uploadForm->setMaxFileSize(8388608); // 8MB s/b as big as i need
			$this->kenFile =& $uploadForm->addElement('file', 'original_filename', 'File:');
			$uploadForm->addRule('original_filename', 'You must select a file', 'uploadedfile');
		}

	function preProcessForm(&$formValues)
		{
			$path = "../../files"; // NOTE! for the tests folder
			$fileValues =& $this->kenFile->getValue();
		
			print_r_html($this->kenFile);
			print_r_html($fileValues);
		
			
			$unique_filename = sprintf("%d-%s", rand(1,200), $fileValues['name']);
			if ($this->kenFile->isUploadedFile()) {
				$this->kenFile->moveUploadedFile($path, $unique_filename);
				$this->mime_type = $fileValues['type'];
				print_r_html($this);
				print "file uploaded!";
				// TODO: add the original_filename['name'] unique_file and to the db's field
			}
			else {
				print "No file uploaded";
			}
		} // end preprocess
	
} 
