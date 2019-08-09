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
        if (!$this->id && !$this->method && !$this->setProperties) throw new Exception ("\$method not provided");
        if ($this->setProperties) {
            Ac_Accessor::setObjectProperty($record, $this->setProperties);
        }
        
        $method = $this->method;
        if ($method === false && !$this->setProperties) $method = $this->id;
        if ($method) {
            if ($this->isCallback) {
                call_user_func($method, $record);
            } else {
                $record->$method();
            }
        }
        
        $shouldStore = false;
        if ($this->saveAfter == self::SAVE_ALWAYS) $shouldStore = true; 
        elseif ($this->saveAfter == self::SAVE_IF_CHANGED && $record->getChanges()) $shouldStore = true;
        if ($shouldStore) $record->store();
    }
}

