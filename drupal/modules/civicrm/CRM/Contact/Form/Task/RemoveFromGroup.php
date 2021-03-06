<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 1.4                                                |
 +--------------------------------------------------------------------+
 | Copyright (c) 2005 Donald A. Lobo                                  |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the Affero General Public License Version 1,    |
 | March 2002.                                                        |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the Affero General Public License for more details.            |
 |                                                                    |
 | You should have received a copy of the Affero General Public       |
 | License along with this program; if not, contact the Social Source |
 | Foundation at info[AT]socialsourcefoundation[DOT]org.  If you have |
 | questions about the Affero General Public License or the licensing |
 | of CiviCRM, see the Social Source Foundation CiviCRM license FAQ   |
 | at http://www.openngo.org/faqs/licensing.html                       |
 +--------------------------------------------------------------------+
*/

/**
 *
 * @package CRM
 * @author Donald A. Lobo <lobo@yahoo.com>
 * @copyright Donald A. Lobo (c) 2005
 * $Id$
 *
 */


require_once 'CRM/Contact/Form/Task.php';

/**
 * This class provides the functionality to delete a group of
 * contacts. This class provides functionality for the actual
 * addition of contacts to groups.
 */
class CRM_Contact_Form_Task_RemoveFromGroup extends CRM_Contact_Form_Task {
    /**
     * Build the form
     *
     * @access public
     * @return void
     */
    function buildQuickForm( ) {
        // add select for groups
        $group = array( '' => ts('- select group -')) + CRM_Core_PseudoConstant::group( );
        $groupElement = $this->add('select', 'group_id', ts('Select Group'), $group, true);

        CRM_Utils_System::setTitle( ts('Remove Members from Group') );
        $this->addDefaultButtons( ts('Remove From Group') );
    }

    /**
     * Set the default form values
     *
     * @access protected
     * @return array the default array reference
     */
    function &setDefaultValues() {
        $defaults = array();

        if ( $this->get( 'context' ) === 'smog' ) {
            $defaults['group_id'] = $this->get( 'gid' );
        }
        return $defaults;
    }


    /**
     * process the form after the input has been submitted and validated
     *
     * @access public
     * @return None
     */
     function postProcess() {
        $groupId  =  $this->controller->exportValue( 'RemoveFromGroup', 'group_id'  );
        $group    =& CRM_Core_PseudoConstant::group( );

        list( $total, $removed, $notRemoved ) = CRM_Contact_BAO_GroupContact::removeContactsFromGroup( $this->_contactIds, $groupId );
        $status = array(
                        ts('Removed Contact(s) from %1', array(1 => $group[$groupId])),
                        ts('Total Selected Contact(s): %1', array(1 => $total))
                        );
        if ( $removed ) {
            $status[] = ts('Total Contact(s) removed from group: %1', array(1 => $removed));
        }
        if ( $notRemoved ) {
            $status[] = ts('Total Contact(s) not in group: %1', array(1 => $notRemoved));
        }
        CRM_Core_Session::setStatus( $status );

    }//end of function


}

?>
