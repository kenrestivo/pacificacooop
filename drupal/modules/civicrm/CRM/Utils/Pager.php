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
 * This class extends the PEAR pager object by substituting standard default pager arguments
 * We also extract the pageId from either the GET variables or the POST variable (since we
 * use a POST to jump to a specific page). At some point we should evaluate if we want
 * to use Pager_Jumping instead. We've changed the format to allow navigation by jumping
 * to a page and also First, Prev CURRENT Next Last
 *
 */

define( 'CRM_UTILS_PAGER_ROWCOUNT',50);
define( 'CRM_UTILS_PAGER_PAGE_ID','crmPID');
define( 'CRM_UTILS_PAGER_PAGE_ID_TOP','crmPID');
define( 'CRM_UTILS_PAGER_PAGE_ID_BOTTOM','crmPID_B');
define( 'CRM_UTILS_PAGER_PAGE_ROWCOUNT','crmRowCount');

require_once 'Pager/Sliding.php';

class CRM_Utils_Pager extends Pager_Sliding {

    /**
     * constants for static parameters of the pager
     */
    
                  
                   
               
            
             

    /**
     * the output of the pager. This is a name/value array with various keys
     * that an application could use to display the pager
     * @var array
     */
    var $_response;
  
    /**  
     * The pager constructor. Takes a few values, and then assigns a lot of defaults
     * to the PEAR pager class
     * We have embedded some html in this class. Need to figure out how to export this
     * to the top level at some point in time
     *
     * @param int     total        the total count of items to be displayed
     * @param int     currentPage  the page currently being displayed
     * @param string  status       the status message to be displayed. It embeds a token
     *                             %%statusMessage%% that will be replaced with which items
     *                             are currently being displayed
     * @param string  csvString    the title of the link to be displayed for the export
     * @param int     perPage      the number of items displayed per page
     *
     * @return object              the newly created and initialized pager object
     *
     * @access public
     *
     */
    function CRM_Utils_Pager( $params ) {
        if( $params['status'] === null ) {
            $params['status'] = ts('Contacts %%StatusMessage%%');
        }

        $this->initialize( $params );

        $this->Pager_Sliding( $params);

        list( $offset, $limit ) = $this->getOffsetAndRowCount( );
        $start = $offset + 1;
        $end   = $offset + $limit;
        if ( $end > $params['total'] ) {
            $end = $params['total'];
        }

        if ( $params['total'] == 0 ) {
            $statusMessage = '';
        } else {
            $statusMessage = ts('%1 - %2 of %3', array(1 => $start, 2 => $end, 3 => $params['total']));
        }
        $params['status'] = str_replace( '%%StatusMessage%%', $statusMessage, $params['status'] );

        $this->_response = array(
                                 'first'        => $this->_printFirstPage(),
                                 'back'         => str_replace('&nbsp;', '', $this->_getBackLink()),
                                 'next'         => str_replace('&nbsp;', '', $this->_getNextLink()),
                                 'last'         => $this->_printLastPage(),
                                 'currentPage'  => $this->getCurrentPageID(),
                                 'numPages'     => $this->numPages(),
                                 'csvString'    => $params['csvString'],
                                 'status'       => $params['status'],
                                 'buttonTop'    => $params['buttonTop'],
                                 'buttonBottom' => $params['buttonBottom'],
                                 'twentyfive'   => $this->getPerPageLink(25),
                                 'fifty'        => $this->getPerPageLink(50),
                                 'onehundred'   => $this->getPerPageLink(100),
                                 );


        /**
         * A page cannot have two variables with the same form name. Hence in the 
         * pager display, we have a form submission at the top with the normal
         * page variable, but a different form element for one at the bottom
         *
         */
        $this->_response['titleTop']    = ts('Page %1 of %2', array(1 => '<input size="2" maxlength="3" name="' . CRM_UTILS_PAGER_PAGE_ID. '" type="text" value="' . $this->_response['currentPage'] . '" />', 2 => $this->_response['numPages']));
        $this->_response['titleBottom']    = ts('Page %1 of %2', array(1 => '<input size="2" maxlength="3" name="' . CRM_UTILS_PAGER_PAGE_ID_BOTTOM. '" type="text" value="' . $this->_response['currentPage'] . '" />', 2 => $this->_response['numPages']));

    }


    /**
     * helper function to assign remaining pager options as good default
     * values
     *
     * @param array   $params      the set of options needed to initialize the parent
     *                             constructor
     *
     * @access public
     * @return void
     *
     */
    function initialize( &$params ) {
        /* set the mode for the pager to Sliding */
        $params['mode']       = 'Sliding';

        /* also set the urlVar to be a crm specific get variable */
        $params['urlVar']     = CRM_UTILS_PAGER_PAGE_ID;
    
        /* set this to a small value, since we dont use this functionality */
        $params['delta']      = 1;

        $params['totalItems'] = $params['total'];
        $params['append']     = true;
        $params['separator']  = '';
        $params['spacesBeforeSeparator'] = 1;
        $params['spacesAfterSeparator']  = 1;
        $params['extraVars']             = array( 'force' => 1 );
        $params['excludeVars']           = array( 'reset' => 1 );

        // set previous and next text labels
        $params['prevImg']    = ' ' . ts('&lt; Previous');
        $params['nextImg']    = ts('Next &gt;') . ' ';


        // set first and last text fragments
        $params['firstPagePre']  = '';
        $params['firstPageText'] = ' ' . ts('&lt;&lt; First');
        $params['firstPagePost'] = '';

        $params['lastPagePre']   = '';
        $params['lastPageText']  = ts('Last &gt;&gt;') . ' ';
        $params['lastPagePost']  = '';

        $params['currentPage'] = $this->getPageID      ( $params['pageID'], $params );
        $params['perPage']     = $this->getPageRowCount( $params['rowCount'] );

        return $params;
    }

    /**
     * Figure out the current page number based on value of
     * GET / POST variables. Hierarchy rules are followed,
     * POST over-rides a GET, a POST at the top overrides
     * a POST at the bottom (of the page)
     *
     * @param int defaultPageId   current pageId
     *
     * @return int                new pageId to display to the user
     * @access public
     *
     */
    function getPageID( $defaultPageId = 1, &$params ) {
        // POST has higher priority than GET vars
        // else if a value is set that has higher priority and finally the GET var
        if ( ! empty( $_POST ) ) {
            if ( isset( $_POST[ $params['buttonTop'] ] ) && isset( $_POST[ CRM_UTILS_PAGER_PAGE_ID] ) ) {
                $currentPage = max( (int ) @$_POST[ CRM_UTILS_PAGER_PAGE_ID], 1 );
            } else if ( isset( $_POST[ $params['buttonBottom'] ] ) && isset( $_POST[ CRM_UTILS_PAGER_PAGE_ID_BOTTOM] ) ) {
                $currentPage = max( (int ) @$_POST[ CRM_UTILS_PAGER_PAGE_ID_BOTTOM], 1 );
            } else if ( isset( $_POST[ CRM_UTILS_PAGER_PAGE_ID] ) ) {
                $currentPage = max( (int ) @$_POST[ CRM_UTILS_PAGER_PAGE_ID], 1 );
            } else if ( isset( $_POST[ CRM_UTILS_PAGER_PAGE_ID_BOTTOM] ) ) { 
                $currentPage = max( (int ) @$_POST[ CRM_UTILS_PAGER_PAGE_ID_BOTTOM] );
            }
        } else if ( isset( $_GET[ CRM_UTILS_PAGER_PAGE_ID] ) ) {
            $currentPage = max( (int ) @$_GET[ CRM_UTILS_PAGER_PAGE_ID], 1 );
        } else {
            $currentPage = $defaultPageId;
        }
        return $currentPage;
    }

    /**
     * Get the number of rows to display from either a GET / POST variable
     *
     * @param int $defaultPageRowCount the default value if not set
     * 
     * @return int                     the rowCount value to use
     * @access public
     *
     */
    function getPageRowCount( $defaultPageRowCount = CRM_UTILS_PAGER_ROWCOUNT) {
        // POST has higher priority than GET vars
        if ( isset( $_POST[CRM_UTILS_PAGER_PAGE_ROWCOUNT] ) ) {
            $rowCount = max ( (int ) @$_POST[CRM_UTILS_PAGER_PAGE_ROWCOUNT], 1 );
        } else if ( isset( $_GET[CRM_UTILS_PAGER_PAGE_ROWCOUNT] ) ) {
            $rowCount = max ( (int ) @$_GET[CRM_UTILS_PAGER_PAGE_ROWCOUNT], 1 );
        } else {
            $rowCount = $defaultPageRowCount;
        }
        return $rowCount;
    }

    /**
     * Use the pager class to get the pageId and Offset
     *
     * @param void
     *
     * @return array: an array of the pageID and offset
     *
     * @access public
     *
     */
    function getOffsetAndRowCount( ) {
        $pageId = $this->getCurrentPageID( );
        if ( ! $pageId ) {
            $pageId = 1;
        }

        $offset = ( $pageId - 1 ) * $this->_perPage;

        return array( $offset, $this->_perPage );
    }

    /**
     * given a number create a link that will display the number of
     * rows as specified by that link
     *
     * @param int $perPage the number of rows
     *
     * @return string      the link
     * @access void
     */
    function getPerPageLink( $perPage ) {
        if ( $perPage != $this->_perPage ) {
            $href = CRM_Utils_System::makeURL( CRM_UTILS_PAGER_PAGE_ROWCOUNT) . $perPage;
            $link = sprintf('<a href="%s" %s>%s</a>',
                            $href,
                            $this->_classString,
                            $perPage )
                . $this->_spacesBefore . $this->_spacesAfter;
        } else {
            $link = $this->_spacesBefore . $perPage . $this->_spacesAfter;
        }
        return $link;
    }

}

?>
