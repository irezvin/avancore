<?php

class Ac_Admin_Processing_MapperMethod extends Ac_Admin_Processing {
    
    var $method = false;
    
    var $provideRecordKeys = false;
    
    function executeProcess() {
        if ($this->provideRecordKeys) {
            $args = array($this->_getRecordKeysFromRequest());
        } else {
            $args = array();
        }
        if (is_array($this->method)) $m = $this->method;
            else $m = array($this->_getMapper(), $this->method);
        call_user_func_array($m, $args);
    }
    
}

?>