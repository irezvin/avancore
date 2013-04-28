<?php

class Cg_Property {
    
    var $enabled = '?';
    
    /**
     * @var Cg_Model
     */
    var $_model = false;

    var $isPrivateVar = false;
    
    var $name = false;
    
    var $caption = false;
    
    var $varName = false;
    
    /**
     * @var bool|string If false or emprty string, property is not a list. If none-empty string, property is a list with corresponding plural form.
     */
    var $pluralForList = false;
    
    var $extraPropertyInfo = array();
    
    function Cg_Property ($model, $name, $config = array()) {
        $this->_model = $model;
        $this->name = $name;
        Ac_Util::simpleBindAll($config, $this);
        $this->_init();
    }
    
    /**
     * Computes unfilled meta-property members
     */
    function _init() {
    }
    
    function resolveConflicts() {
    }
    
    /**
     * Property info for function Ac_Model::getOwnPropertiesInfo() -- same as $formOptions in Ac_Model_Property. If $this->hasSeveralProperties() returns true,
     * should return associative array (propName => array(propInfo...))
     *  
     * @return array
     */
    function getAeModelPropertyInfo() {
        $res = array();
        foreach ($this->listPassthroughVars() as $ptv) {
            $val = false;
            if (method_exists($this, $getter = 'get'.$ptv)) $val = $this->$getter();
                else $val = $this->$ptv;
            if ($val && (!is_array($val) || count($val)))
            $res[$ptv] = $val;
        }
        if (!$this->isEnabled()) $res = array_merge($res, array('isEnabled' => $this->isEnabled()));
        $res = array_merge($res, $this->extraPropertyInfo);
        return $res;
    }
    
    function listPassthroughVars() {
        return array('caption');
    }
    
    function getClassMemberName() {
        return $this->isPrivateVar? '_'.$this->varName : $this->varName;
    }
    
    function getLangStringName() {
        $res = strtolower(Cg_Inflector::definize($this->_model->_domain->appName.'_'.$this->_model->single.'_'.$this->varName));
        return $res;
    }
    
    function getAllClassMembers() {
        return array($this->getClassMemberName() => false);
    }

    function isEnabled() {
        return $this->enabled !== false;
    }
    
    /**
     * Template function to indicate that getAeModelPropertyInfo() returns several associative array with info for several properies instead of one.
     * @return bool
     */
    function hasSeveralProperties() {
        return false;
    }
    
    function getCaption() {
        if ($this->_model->_domain->captionsToPmtLangStrings) {
            $res = new Cg_Php_Expression("new Pmt_Lang_String('".$this->getLangStringName()."')");
        } else {
            $res = $this->caption;
        }
        return $res;
    }
    
}

?>