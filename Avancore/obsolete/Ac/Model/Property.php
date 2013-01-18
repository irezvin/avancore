<?php

class Ac_Model_Property {
    
    var $propName = null;
    
    /**
     * @var Ac_Model_Data
     */
    var $srcObject = null;
    var $srcClass = null;
    
    /**
     * @var Ac_Model_Data
     */
    var $implObject = null; 
    var $implClass = null;
    
    var $isStatic = null;
    var $isAbstract = null;
    
    var $plural = null;
    var $arrayValue = null;
    var $assocClass = null;
    
    var $value = null;
    var $error = null;
    
    var $required = null;
    var $readOnly = null;
    var $dataType = null;
    var $defaultValue = null;    
    
    var $caption = null;
    var $controlType = null;
    var $controlClass = null;
    
    var $internalDateFormat = null;
    var $outputDateFormat = null;
    
    var $noBind = null;
    var $noExport = null;
    
    var $allowHtml = null;
    
    var $restrictToListOnConvert = false;
    
    var $allowValuesOutOfLists = null;
    
    /**
     * Whether this property should be checked by object that is not owner of the property
     * @var bool
     */
    var $overCheck = null;
    
    /**
     * Whether this association property should be loaded when owner is check()-ed (if it's not already loaded)
     */
    var $loadToCheck = null;
    
    /**
     * Whether this association property should be checked if it was not bind()-ed before
     */
    var $checkIfUnbound = null;
    
    var $lt = null;
    var $gt = null;
    var $le = null;
    var $ge = null;
    var $nz = null;
    
    var $maxLength = null;
    
    var $skipValidation = false;
    
    function Ac_Model_Property (& $srcObject, $propName, $isStatic, $formOptions = array()) {
        foreach (array_keys($formOptions) as $optionName) $this->$optionName = $formOptions[$optionName];

        $this->srcObject = $srcObject;
        $this->propName = $propName;
        $this->isStatic = $isStatic;
    }
    
    function _updateData($formOptions = array()) {
        foreach (array_diff(array_keys($formOptions), array('srcObject', 'implObject', 'propName', 'isStatic')) as $optionName) $this->$optionName = $formOptions[$optionValue];
    }
    
    function toFormOptions() {
        $res = array();
        foreach (array_keys(get_object_vars($this)) as $varName) {
            if ($varName{0} != '_' && $varName != 'srcObject' && $varName != 'implObject' && !is_null($this->$varName)) $res[$varName] = $this->$varName;  
        }
        return $res;
    }
    
    function getValue() {
        trigger_error (__FUNCTION__.' is not implemented yet', E_USER_ERROR);
    }
    
    function getAssoc() {
        trigger_error (__FUNCTION__.' is not implemented yet', E_USER_ERROR);
    }
    
    function getErrors() {
        trigger_error (__FUNCTION__.' is not implemented yet', E_USER_ERROR);
    }
    
    function getDynamicInfo() {
        if ($this->isAbstract) trigger_error('Cannot retrieve dynamic info from abstract property info '.$this->srcClass.'::'.$this->$propName, E_USER_ERROR);
        if ($this->isStatic) {
            $dynFormOptions = $this->srcObject->getFormOptions($this->propName, false);
            $this->_updateData($dynFormOptions);
        }
        return true;
    }
    
}

?>