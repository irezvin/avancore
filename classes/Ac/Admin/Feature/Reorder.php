<?php

class Ac_Admin_Feature_Reorder extends Ac_Admin_Feature {
    
    var $colName = false;
    
    var $fieldName = false;
    
    var $saveOrderColName = false;
    
    var $columnPrototype = [];
    
    var $saveOrderColumnPrototype = [];
    
    var $orderUpProcessing = [];
    
    var $orderDownProcessing = [];
    
    var $saveOrderProcessing = [];
    
    var $groupProperty = false;
    
    function doCanBeApplied() {
        return strlen($this->colName) || strlen($this->saveOrderColName);
    }
    
    function getFieldName() {
        if ($this->fieldName) return $this->fieldName;
        if ($this->colName) return $this->colName;
        return $this->saveOrderColName;
    }
    
    function getColName() {
        if ($this->colName) return $this->colName;
        return $this->fieldName;
    }
    
    function getSaveOrderColName() {
        $res = $this->saveOrderColName;
        if (!$res && ($c = $this->getColName())) {
            $res = $c.'SaveOrder';
        }
        return $res;
    }
    
    function getColumnSettings() {
        $c = $this->getColName();
        $f = $this->getFieldName();
        
        $orderUpTask = false;
        $orderDownTask = false;
        
        if ($c && is_array($this->orderUpProcessing)) {
            $orderUpTask = $c.'OrderUp';
        }
        
        if ($c && is_array($this->orderDownProcessing)) {
            $orderDownTask = $c.'OrderDown';
        }
        
        $res = [];
        
        $colName = $this->getColName();
        
        if ($colName && is_array($this->columnPrototype)) {
            $res[$colName] = Ac_Util::m([
                'class' => 'Ac_Admin_Column_Reorder',
                'orderUpTask' => $orderUpTask,
                'orderDownTask' => $orderDownTask,
                'orderProperty' => $this->groupProperty? $this->groupProperty : false,
            ], $this->columnPrototype);
        }
        
        if (($sc = $this->getSaveOrderColName()) && is_array($this->columnPrototype)) {
            $res[$sc] = Ac_Util::m([
                    'class' => 'Ac_Admin_Column_SaveOrder',
                    'taskName' => $sc.'SaveOrder',
            ], $this->saveOrderColumnPrototype);
        }
        return $res;
    }
    
    function getProcessings() {
        $res = [];
        if (($f = $this->getFieldName()) && ($c = $this->getColName())) {
            if (is_array($this->orderUpProcessing)) {
                $res[$c.'OrderUp'] = Ac_Util::m([
                    'class' => 'Ac_Admin_Processing_Reorder',
                    'fieldName' => $this->getFieldName(),
                    'direction' => 'up',
                ], $this->orderUpProcessing);
            }
            if (is_array($this->orderDownProcessing)) {
                $res[$c.'OrderDown'] = Ac_Util::m([
                    'class' => 'Ac_Admin_Processing_Reorder',
                    'fieldName' => $this->getFieldName(),
                    'direction' => 'down',
                ], $this->orderDownProcessing);
            }
        }
        if (($so = $this->getSaveOrderColName()) && is_array($this->saveOrderProcessing)) {
            $res[$so.'SaveOrder'] = Ac_Util::m([
                'class' => 'Ac_Admin_Processing_SaveOrder',
                'fieldName' => $this->getFieldName(),
            ], $this->saveOrderProcessing);
        }
        return $res;
    }
    
}