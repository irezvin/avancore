<?php

class Ac_Admin_Feature_Reorder extends Ac_Admin_Feature {
    
    var $colName = false;
    
    var $columnPrototype = array();
    
    var $saveOrderColumnPrototype = array();
    
    var $orderUpProcessing = array();
    
    var $orderDownProcessing = array();
    
    var $saveOrderProcessing = array();
    
    var $groupProperty = false;
    
    function doCanBeApplied() {
        return (bool) strlen($this->colName);
    }
    
    function getColumnSettings() {
        $orderUpTask = $this->colName.'OrderUp';
        $orderDownTask = $this->colName.'OrderDown';
        
        if (!is_array($this->orderUpProcessing)) {
            $orderUpTask = false;
        }
        
        if (!is_array($this->orderDownProcessing)) {
            $orderDownTask = false;
        }
        
        $res = array(
            $this->colName => array(
                'class' => 'Ac_Admin_Column_Reorder',
                'orderUpTask' => $orderUpTask,
                'orderDownTask' => $orderDownTask,
                'orderProperty' => $this->groupProperty
            ),
            $this->colName.'SaveOrder' => array(
                'class' => 'Ac_Admin_Column_SaveOrder',
                'taskName' => $this->colName.'SaveOrder',
                'orderProperty' => $this->groupProperty
            ),
        );
        if (is_array($this->columnPrototype)) {
            Ac_Util::ms($res[$this->colName], $this->columnPrototype);
        } else {
            unset($res[$this->colName]);
        }
        if (is_array($this->saveOrderColumnPrototype)) {
            Ac_Util::ms($res[$this->colName.'SaveOrder'], $this->saveOrderColumnPrototype);
        } else {
            unset($res[$this->colName.'SaveOrder']);
        }
        return $res;
    }
    
    /*function getActions() {
        $res = array();
        $res = array(
            ($pc = $this->colName.'OrderUp') => array(
                'id' => $pc,
                'scope' => 'some',
                'image' => 'icon-32-orderup.png', 
                'caption' => 'Включить',
                'managerProcessing' => $pc,
                'listOnly' => true,
            ), 
            ($upc = $this->colName.'OrderDown') => array(
                'id' => $upc,
                'scope' => $this->colName.'some',
                'image' => 'icon-32-unorderup.png', 
                'caption' => 'Выключить',
                'managerProcessing' => $upc,
                'listOnly' => true,
            ),
        );
        if (is_array($this->orderupAction)) Ac_Util::ms($res[$pc], $this->orderupAction);
        if (!is_array($this->orderupProcessing) || !is_array($this->orderupAction))
            unset($res[$pc]);
        if (is_array($this->unorderupAction)) Ac_Util::ms($res[$upc], $this->unorderupAction);
        if (!is_array($this->unorderupProcessing) || !is_array($this->unorderupAction))
            unset($res[$upc]);
        return $res;
    }*/
    
    function getProcessings() {
        $res = array();
        $res = array(
            ($pc = $this->colName.'OrderUp') => array(
                'class' => 'Ac_Admin_Processing_Reorder',
                'fieldName' => $this->colName,
                'direction' => 'up',
            ), 
            ($upc = $this->colName.'OrderDown') => array(
                'class' => 'Ac_Admin_Processing_Reorder',
                'fieldName' => $this->colName,
                'direction' => 'down',
            ),
            ($so = $this->colName.'SaveOrder') => array(
                'class' => 'Ac_Admin_Processing_SaveOrder',
                'fieldName' => $this->colName,
                'mode' => 'save',
            ),
        );
        if (is_array($this->orderUpProcessing)) Ac_Util::ms($res[$pc], $this->orderUpProcessing);
            else unset($res[$pc]);
        if (is_array($this->orderDownProcessing)) Ac_Util::ms($res[$upc], $this->orderDownProcessing);
            else unset($res[$upc]);
        if (is_array($this->saveOrderProcessing)) Ac_Util::ms($res[$so], $this->saveOrderProcessing);
            else unset($res[$so]);
        return $res;
    }
    
}