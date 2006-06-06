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

class DB_DataObjectIterator extends Iterator
{
    var $_src;
    var $_index = -1;
    var $_end   = false;
    var $_value;
    
    /**
     * dbdo Iterator constructor.

i am not in this case using any of that Ref class crap. 
the coopiterator does though.

     * @param DB_DataObject $result
     *        The query result.
     */
    function DB_DataObjectIterator(&$dataobject)
    {
        $this->_src =& $dataobject;
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
            $this->_size = $this->_src->N;
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
     * This method calls toArray() on the DB_DataObject, the return type depends
     * of the DB_DataObject->toArray. Please specify it before executing the 
     * template.
     *
     * @return mixed
     */
    function &next()
    {
        if ($this->_end || ++ $this->_index >= $this->size()) {
            $this->_end = true;
            return false;
        }

        unset($this->_value);
        $this->_src->fetch();
        $this->_value = $this->_src;
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
}




?>