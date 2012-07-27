<?php

class Ac_Admin_ReportEntry {
    
    var $recordKey = false;
    var $recordTitle = false;
    var $recordIsAvailable = false;
    var $dateTime = false;
    var $description = false;
    
    /**
     * @var string message|warning|error
     */
    var $type = 'message';

    var $_entries = array();
    
    function Ac_Admin_ReportEntry ($description = false, $type = 'message', $dateTime = false, $recordKey = false, $recordTitle = false, $recordIsAvailable = false) {
        if (func_num_args() == 1 && is_array($description)) Ac_Util::simpleBind($description, $this);
        else {
            $this->description = $description;
            $this->dateTime = $dateTime;
            $this->recordKey = $recordKey;
            $this->recordTitle = $recordTitle;
            $this->recordIsAvailable = $recordIsAvailable;
            $this->type = $type;
        }
    }
    
    /**
     * @param Ac_Admin_ReportEntry $entry
     */
    function addChildEntry(& $entry) {
        $this->_entries[] = & $entry;
    }
    
    function listChildEntries() {
        return array_keys($this->_entries);
    }
    
    function getChildEntry($index) {
        if (!in_array($index, $this->listChildEntries())) trigger_error ("Unknown node: '{$index}'", E_USER_ERROR);
        $res = & $this->_entries[$index];
        return $res;
    }
    
    function removeChildEntry($index) {
        if (!in_array($index, $this->listChildEntries())) trigger_error ("Unknown node: '{$index}'", E_USER_ERROR);
        unset($this->_entries[$index]);
    }
    
    function clearChildEntries() {
        $this->_entries = array();
    }
    
    function hasChildEntries ($type = false, $recursive = true) {
        $res = false;
        if (!$type) {
            $res = count($this->_entries) > 0;
        } else {
            $res = false;
            foreach (array_keys($this->_entries) as $i) {
                if (
                        ($this->_entries[$i]->type == $type) 
                    ||  ($recursive && $this->_entries[$i]->hasChildEntries($type, true))
                ) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }
    
}

?>