<?php

class Ac_Cg_Property extends Ac_Cg_Base {
    
    var $enabled = '?';
    
    /**
     * @var Ac_Cg_Model
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
    
    /**
     * This property is inherited by the owner model from the parent model
     * @var bool
     */
    var $inherited = false;
    
    var $ignoreInDescendants = false;
    
    function __construct (array $options = array()) {
        $init = false;
        if (isset($options['_init']) && $options['_init']) $init = true;
        unset($options['_init']);
        $this->initOptionsFirst(['name', 'model'], $options);
        parent::__construct($options);
        if ($init) $this->init();
    }
    
    protected function setModel(Ac_Cg_Model $model) {
        $this->_model = $model;
    }
    
    /**
     * Computes unfilled meta-property members
     */
    function init() {
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
        $res = strtolower(Ac_Cg_Inflector::definize($this->_model->getLangStringPrefix().'_'.$this->varName));
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
        if ($this->_model->getUseLangStrings()) {
            $res = new Ac_Cg_Php_Expression("new Ac_Lang_String('".$this->getLangStringName()."')");
        } else {
            $res = $this->caption;
        }
        return $res;
    }
    
    function getIsInherited() {
        $res = $this->inherited || $this->_model->isPropertyInherited($this->name);
        return $res;
    }
    
    function applyToSqlSelectPrototype(array & $prototype) {
        
    }
    
    function unserializeFromArray($array) {
        $this->_model = Ac_Impl_ArraySerializer::getParent('Ac_Cg_Model');
        parent::unserializeFromArray($array);
        $this->_init = true;
    }
    
    
}

