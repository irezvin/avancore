<?php

class Ac_Admin_Feature_Switch extends Ac_Admin_Feature {
    
    var $colName = false;
    
    var $columnPrototype = array();
    
    var $switchProcessing = array();
    
    function doCanBeApplied() {
        return (bool) strlen($this->colName) && is_array($this->switchProcessing);
    }
    
    function getColumnSettings() {
        $switchTask = $this->colName.'Switch';
        
        $res = array(
            $this->colName => array(
                'class' => 'Ac_Admin_Column_Switch',
                'switchTask' => $switchTask,
            )
        );
        if (is_array($this->columnPrototype)) {
            Ac_Util::ms($res[$this->colName], $this->columnPrototype);
        } else {
            unset($res[$this->colName]);
        }
        return $res;
    }
    
    function getProcessings() {
        $res = array();
        $res = array(
            ($pc = $this->colName.'Switch') => array(
                'class' => 'Ac_Admin_Processing_Switch',
                'fieldName' => $this->colName,
            ), 
        );
        if (is_array($this->switchProcessing)) Ac_Util::ms($res[$pc], $this->switchProcessing);
            else unset($res[$pc]);
        return $res;
    }
    
}