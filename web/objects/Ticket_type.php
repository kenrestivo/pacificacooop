<?php
/**
 * Table Definition for ticket_type
 */
require_once 'DB/DataObject.php';

class Ticket_type extends DB_DataObject 
{
    ###START_AUTOCODE
    /* the code below is auto generated do not remove the above tag */

    var $__table = 'ticket_type';                     // table name
    var $ticket_type_id;                  // int(32)  not_null primary_key unique_key auto_increment
    var $description;                     // string(255)  
    var $paid_flag;                       // string(3)  enum
    var $jointable_hack;                  // string(255)  

    /* ZE2 compatibility trick*/
    function __clone() { return $this;}

    /* Static get */
    function staticGet($k,$v=NULL) { return DB_DataObject::staticGet('Ticket_type',$k,$v); }

    /* the code above is auto generated do not remove the tag below */
    ###END_AUTOCODE

	var $fb_enumFields = array('paid_flag');
	var $fb_linkDisplayFields = array('description');
	var $fb_fieldLabels = array ('description' => "Description",
								 'paid_flag' => 'Paid?');
	var $fb_formHeaderText = "Springfest Reservation Types";

	function getTypes($paid = 'both')
		{
//			$opts[''] = 'None';

			if($paid != 'both'){
				$this->whereAdd(sprintf('paid_flag = %d',
										$paid == 'no' ? 0 : 1 ));
			}
			$this->orderBy('description');
			$this->find();
			while($this->fetch()){
				$opts[$this->ticket_type_id] = 
					sprintf("%s (%s)",
							$this->description,
							$this->paid_flag == 'Yes' ? "Payment Required" : "Free");
			}
			return $opts;
		}

}
