<?php

class Ac_Admin_Processing_Method extends Ac_Admin_Processing {
    
    var $method = false;
    
    var $isCallback = false;
    
    var $saveAfter = false;
    
    /**
     * @access protected
     * @param Ac_Model_Object $record
     */
    function _doProcessRecord($record) {
        if (!strlen($this->method)) throw new Exception ("\$method not provided");
        if ($this->isCallback) {
            call_user_func($this->method, $record);
        } else {
            $m = $this->method;
            $record->$m();
        }
        if ($this->saveAfter) $record->store();
    }
}

