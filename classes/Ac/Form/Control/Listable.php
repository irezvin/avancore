<?php

/**
 * Control that supports lists editing
 */
class Ac_Form_Control_Listable extends Ac_Form_Control {
    
    var $isList = '?';
    
    var $listSeparator = false;
    
    var $removeDuplicatesFromList = true;
    
    var $removeEmptyStringsFromList = true;
    
    var $trim = true;
    
    var $inputCanBeArray = '?';

    var $allowHtml = '?';
    
    /**
     * @access protected
     */
    function _getControlTypesForList() {
        return array();
    }
    
    function canInputBeArray() {
        if ($this->inputCanBeArray !== '?') $res = $this->inputCanBeArray; 
        else {
            $res = $this->isList();
        }
        return $res;
    }
    
    function isHtmlAllowed() {
        if ($this->allowHtml === '?') {
            $p = $this->getModelProperty();
            if ($p && isset($p->allowHtml)) $res = $p->allowHtml;
                else $res = false;
        } else {
            $res = $this->allowHtml;
        }
        return $res;
    }
    
    function isList() {
        if ($this->isList === '?') {
            if (($p = $this->getModelProperty()) && ($p->plural || $p->arrayValue)) $res = true;
            elseif (is_array($this->getDefault())) $res = true;
            else $res = false;
        } else $res = $this->isList;
        return $res;
    }
    
    function getListSeparator() {
        $res = '';
        if ($this->listSeparator === false) {
            if ($p = $this->getModelProperty()) {
                if (isset($p->listSeparator) && strlen($p->listSeparator)) $res = $p->listSeparator;
                elseif($p->arrayValue && in_array($p->controlType, $this->_getControlTypesForList())) $res = "\n";
            }
            elseif ($this->isList()) $res = in_array($this->type, $this->_getControlTypesForList())? "\n" : '; ';
            else $res = '';
        } else $res = $this->listSeparator;
        return $res;
    }
    
    function _processArrValue($arrValue) {
        $res = array();
        foreach ($arrValue as $val) {
            if ($this->trim) $val = trim($val);
            if ($this->removeEmptyStringsFromList && !strlen($val)) continue;
            $res[] = $val;
        }
        if ($res && $this->removeDuplicatesFromList) $res = array_unique($res);
        return $res;
    }

    /** 
     * Processes internal value according to list settings
     */ 
    function formatValue($value) {
        $arrValue = false;
        if (strlen($sep = $this->getListSeparator())) {
            if ($sep != ' ') $sep = trim($sep, ' ');
            if (!is_array($value)) {
                $arrValue = explode($sep, $value);
            }
            else $arrValue = $value;
            $arrValue = $this->_processArrValue($arrValue);
            $res = implode($sep, $arrValue);
        } else {
            if ($this->trim && !is_null($value)) $value = trim($value);
            $res = $value;
        }
        return $res;
    }
    
    function processInputText($value) {
        if ($this->trim && !is_array($value)) $input = trim($value);
        if (strlen($sep = $this->getListSeparator())) {
            if ($sep != ' ') $sep = trim($sep, ' ');
            if (!is_array($value)) $arrValue = explode($sep, $value);
                else $arrValue = $value;
            $arrValue = $this->_processArrValue($arrValue);
            if (!$this->isList()) $res = implode($sep, $value);
                else $res = $arrValue;
        } else {
            $res = $value;
        }
        if (!is_array($res) && $this->isList()) $res = Ac_Util::toArray($res);
        return $res;
    }
    
    /**
     * Template method to work on input value
     * @access protected
     */
    function _doProcessInputValue(& $val) {
    }
    
    function _doGetValue() {
        if (!($this->readOnly === true) && isset($this->_rqData['value']) && ($this->canInputBeArray() || !is_array($this->_rqData['value']))) {
            $val = $this->_rqData['value'];
            $this->_doProcessInputValue($val);
            $val = $this->processInputText($val);
            $res = $val;
        } else {
            $res = $this->getDefault();
        }
        return $res;
    }
    
}

