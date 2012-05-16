<?php

class Ac_Sql_Blocker {
    
    var $_isLocked = false;
    
    function lock() {
        if ($this->_isLocked) $res = false;
            else {
                $res = true;
                $this->_isLocked = true;
            }
        return $res;
    }
    
    
    function isLocked() {
        return $this->_isLocked;
    }
    
    
    function release() {
        if ($this->_isLocked) {
            $res = true;
            $this->_isLocked = false;
        } else {
            $res = false;
        }
        return $res;
    }
    
}

?>