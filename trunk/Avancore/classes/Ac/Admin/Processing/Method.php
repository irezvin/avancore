<?php

class Ac_Admin_Processing_Method extends Ac_Admin_Processing {

    const SAVE_ALWAYS = 1;
    const SAVE_IF_CHANGED = 2;
    
    var $method = false;
    
    var $isCallback = false;
    
    var $saveAfter = false;
    
    var $setProperties = array();
    
    /**
     * @access protected
     * @param Ac_Model_Object $record
     */
    function _doProcessRecord($record) {
        if (!$this->method && !$this->setProperties) throw new Exception ("\$method not provided");
        if ($this->setProperties) {
            Ac_Accessor::setObjectProperty($record, $this->setProperties);
        }
        if ($this->method) {
            if ($this->isCallback) {
                call_user_func($this->method, $record);
            } else {
                $m = $this->method;
                $record->$m();
            }
        }
        $shouldStore = false;
        if ($this->saveAfter == self::SAVE_ALWAYS) $shouldStore = true; 
        elseif ($this->saveAfter == self::SAVE_IF_CHANGED && $record->getChanges()) $shouldStore = true;
        if ($shouldStore) $record->store();
    }
}

