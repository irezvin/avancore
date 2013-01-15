<?php

class Ac_Admin_Feature_Trigger extends Ac_Admin_Feature {
    
    var $colName = false;
    
    var $columnPrototype = array();
    
    var $publishAction = array();
    
    var $unpublishAction = array();
    
    var $publishProcessing = array();
    
    var $unpublishProcessing = array();
    
    function doCanBeApplied() {
        return (bool) strlen($this->colName);
    }
    
    function getColumnSettings() {
        $publishTask = $this->colName.'Publish';
        $unpublishTask = $this->colName.'Unpublish';
        
        if (!is_array($this->publishProcessing)) {
            $publishTask = false;
        }
        
        if (!is_array($this->unpublishProcessing)) {
            $unpublishTask = false;
        }
        
        $res = array(
            $this->colName => array(
                'class' => 'Ac_Admin_Column_Published',
                'publishTask' => $publishTask,
                'unpublishTask' => $unpublishTask,
            )
        );
        if (is_array($this->columnPrototype)) {
            Ac_Util::ms($res[$this->colName], $this->columnPrototype);
        } else {
            unset($res[$this->colName]);
        }
        return $res;
    }
    
    function getActions() {
        $res = array();
        $res = array(
            ($pc = $this->colName.'Publish') => array(
                'id' => $pc,
                'scope' => 'some',
                'image' => 'publish_f2.png', 
                'disabledImage' => 'publish.png',
                'caption' => 'Включить',
                'managerProcessing' => $pc,
                'listOnly' => true,
            ), 
            ($upc = $this->colName.'Unpublish') => array(
                'id' => $upc,
                'scope' => $this->colName.'some',
                'image' => 'unpublish_f2.png', 
                'disabledImage' => 'unpublish.png',
                'caption' => 'Выключить',
                'managerProcessing' => $upc,
                'listOnly' => true,
            ),
        );
        if (is_array($this->publishAction)) Ac_Util::ms($res[$pc], $this->publishAction);
        if (!is_array($this->publishProcessing) || !is_array($this->publishAction))
            unset($res[$pc]);
        if (is_array($this->unpublishAction)) Ac_Util::ms($res[$upc], $this->unpublishAction);
        if (!is_array($this->unpublishProcessing) || !is_array($this->unpublishAction))
            unset($res[$upc]);
        return $res;
    }
    
    function getProcessings() {
        $res = array();
        $res = array(
            ($pc = $this->colName.'Publish') => array(
                'class' => 'Ac_Admin_Processing_Publish',
                'fieldName' => $this->colName,
                'mode' => 'publish',
            ), 
            ($upc = $this->colName.'Unpublish') => array(
                'class' => 'Ac_Admin_Processing_Publish',
                'fieldName' => $this->colName,
                'mode' => 'unpublish',
            ),
        );
        if (is_array($this->publishProcessing)) Ac_Util::ms($res[$pc], $this->publishProcessing);
            else unset($res[$pc]);
        if (is_array($this->unpublishProcessing)) Ac_Util::ms($res[$upc], $this->unpublishProcessing);
            else unset($res[$upc]);
        return $res;
    }
    
}