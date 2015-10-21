<?php

class Ac_Admin_Column_Switch extends Ac_Table_Column {

    /**
     * @var Ac_Admin_Manager
     */
    var $manager = false;
    
    function getData($record, $rowNo, $fieldName) {
        $realData = $this->getRecordProperty($record, $fieldName);
        $decoratedData = Ac_Table_Column::getData($record, $rowNo, $this->fieldName);
        $task = $this->getSwitchTask();
        if (!$this->canSwitch($record)) $task = false;
        
        $jsCall = new Ac_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
            $task,
            array($this->manager->getIdentifierOf($record))
        ));
        
        $inner = $decoratedData;
        
        if (strlen($task))
            $res =  "<a ".
                        Ac_Util::mkAttribs(array(
                            'href' => "javascript: void(0);", 
                            'onclick' => "return {$jsCall};"
                        )).
                    "> ".$inner."</a>";
            else $res = $inner;
            
        return $res; 
    }
    
    function canSwitch($record) {
        if (strlen($p = $this->getCanSwitchProperty())) 
            $res = (bool) Ac_Accessor::getObjectProperty ($record, $p);
        else $res = true;
        return $res;
    }
    
    function getCanSwitchProperty() {
        return isset($this->settings['canSwitchProperty'])? $this->settings['canSwitchProperty'] : false;
    }
    
    function getSwitchTask() {
        if (isset($this->settings['switchTask'])) $res = $this->settings['switchTask'];
            else $res = false;
        return $res;
    }
    
}