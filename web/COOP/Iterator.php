<?php


/**
 * Iterator for DB_DataObject PEAR object.
 *
 * This class is an implementation of the Iterator Interface
 * for DB_DataObject objects produced by the usage of PEAR::DB package.
 * 
 * @author based on Laurent Bedubourg <laurent.bedubourg@free.fr>
   modified: Copyright (c) 2005 ken restivo <ken@restivo.org>
 */

require_once('Types/Iterator.php');

class CoopIterator extends Iterator
{
    var $_src;
    var $_index = -1;
    var $_end   = false;
    var $_value;
    
    /**
     * Iterator constructor.
     *
     * @param Coop $result
     *        The query result.
     */
    function CoopIterator(&$co)
    {
        $this->_src =& $co;
        $this->reset();
    }

    function reset()
    {
        // DBDO is not resettable AFAICT
        if($this->_index > 0){
            return PEAR::raiseError("ResetError");
        }
    }
    
    /**
     * Return the number of rows in this result.
     *
     * @return int
     */
    function size()
    {
        if (!isset($this->_size)) {
            // GAH! this stupid ref class uses obj, and so does coopobject
            $this->_size = $this->_src->obj->N;
        }
        return $this->_size;
    }

    /**
     * Returns true if end of iterator has not been reached yet.
     */
    function isValid()
    {
        return !$this->_end;
    }

    /**
     * Return the next row in this result.
     *
     * This method calls toArray() on the Coop, the return type depends
     * of the Coop->toArray. Please specify it before executing the 
     * template.
     *
     * @return mixed
     */
    function &next()
    {
        if ($this->_end || ++$this->_index >= $this->size()) {
            $this->_end = true;
            return false;
        }
        

        unset($this->_value);
        //note! this obj is the Ref class obj, which is the coopobject,
        //not the coopobject's obj, which is a DB_DataObject. gah.
        $this->_src->obj->fetch();
        $this->_value = $this->_src->toArrayWithKeys();
        //$this->_src->page->confessArray($this->_value, 'the value', 1);
        return $this->_value;
    }
    
    /**
     * Return current row.
     *
     * @return mixed
     */
    function &value()
    {
        return $this->_value;
    }

    /**
     * Return current row index in resultset.
     *
     * @return int
     */
    function index()
    {
        return $this->_index;
    }

    function key()
        {
            // this abomination gives me the PK, the id of this record
            // the coopview does the recovery  of the safe pk
            return $this->_src->obj->obj->{$this->_src->obj->pk};
        }

}




?>