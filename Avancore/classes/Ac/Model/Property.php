<?php

class Ac_Model_Property implements Ac_I_Prototyped {
    
    var $propName = null;
    
    var $key = null;
    
    /**
     * @var Ac_Model_Data
     */
    var $srcObject = null;
    var $srcClass = null;
    
    var $isStatic = true;
    var $isAbstract = null;
    
    var $plural = null;
    var $arrayValue = null;
    var $assocClass = null;
    
    var $required = null;
    var $readOnly = null;
    var $dataType = null;
    var $defaultValue = null;    
    
    var $caption = null;
    var $colCaption = null;
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
    
    function hasPublicVars() {
        return true;
    }
    
    function __construct (array $prototype = array()) {
        if (Ac_Prototyped::$countInstances) Ac_Debug::reportConstruct($this, Ac_Prototyped::$countInstances);
        foreach ($prototype as $k => $v) $this->$k = $v;
    }
    
    function __clone() {
        if (Ac_Prototyped::$countInstances) Ac_Debug::reportConstruct($this, Ac_Prototyped::$countInstances);
    }
    
    function __get($name) {
        $m = 'get'.$name;
        if ($name == 'value' || $name == 'errors' || method_exists($this, $m)) {
            $res = $this->$m();
        } else {
            echo trigger_error("Undefined property: ".get_class($this)."::\$name", E_USER_NOTICE);
            $res = null;
        }
        return $res;
    }
    
    /**
     * @return Ac_Model_Property
     */
    function cloneToNonStatic($srcObject, $propertyName = null) {
        $res = clone $this;
        $res->isStatic = false;
        $res->srcObject = $srcObject;
        if(!is_null($propertyName)) $res->propName = $propertyName;
        return $res;
    }
    
    function __destruct () {
        if (Ac_Prototyped::$countInstances) Ac_Debug::reportDestruct($this, Ac_Prototyped::$countInstances);
    }
    
    /**
     * @return array
     */
    function toFormOptions() {
        $res = array();
        foreach (get_object_vars($this) as $k => $v) {
            if ($k{0} != '_' && $k != 'srcObject' && !is_null($v)) $res[$k] = $v;  
        }
        return $res;
    }
    
    function getValue() {
        if ($this->isStatic) {
            trigger_error (__FUNCTION__.' has no effect when \$isStatic == true', E_USER_NOTICE);
            return false;
        }
        $res = strlen($this->assocClass)? $this->srcObject->getAssoc($this->propName) : $this->srcObject->getField($propName);
        return $res;
    }
    
    function getErrors($concat = false, $forceCheck = false) {
        if ($this->isStatic) {
            trigger_error (__FUNCTION__.' has no effect when \$isStatic == true', E_USER_NOTICE);
            return false;
        }
        $res = $this->srcObject->getErrors($this->propName, $concat, $forceCheck);
        return $res;
    }
    
}