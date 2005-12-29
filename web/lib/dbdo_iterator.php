<?php

class DBDO_Iterator
{
    var $_src;
    var $_end   = false;
    var $_index = -1;
    var $_value = null;

    // constructor
    //
    // This constructor takes an array as first argument
    //
    function DBDO_Iterator(&$obj)
        {
            // store array reference
            $this->_src =& $obj
            // reset iterator to first position
            $this->reset();
        }

    // reset iterator for next pass
    //
    function reset()
        {
            // unset value
            unset($this->_value);

            // reset index
            $this->_index = 0;

            /// NOTE how can you reset a DBDO? impossible?
            if($this->_index > 0){
                return PEAR::raiseError("ResetError");
            }
        }

    // access next item
    //
    function &next()
        {
            // end already reached, do nothing
            if ($this->_end) {
                return;
            }

            $this->_index++;

            // end reached
            if(!$this->_src->fetch()){
                $this->_end = true;
                return;
            }

            // unset reference to item value
            unset($this->_value);

            // make reference to current item
            $this->_value =& $this->_src->toArray();

            // return value
            return $this->_value;
        }


    // returns true if end not reached
    //
    function isValid()
        {
            return !$this->_end;
        }


    //
    // getters
    //

    // retrieve current value by reference
    //
    // throws error if end reached
    //
    function &value()
        {
            if ($this->_end) {
                return PEAR::raiseError("Iterator Out Of Bounds");
            }
            return $this->_value;
        }

    // retrieve current item index
    //
    function index()
        {
            return $this->_index;
        }


} // END ITERATOR CLASS



?>