<?php

class Ac_Sql_Extension {
	
	var $id = false;
	
	var $_parentObject = null;
	
	var $_knownProperties = array();
	
	function factory($options = array(), $class = 'Ac_Sql_Extension') {
		$res = Ac_Util::factoryWithOptions($options, $class);
		return $res;
	}
	
	function Ac_Sql_Extension($options = array()) {
		Ac_Util::bindAutoparams($this, $options);
		$this->_knownProperties = array_keys($options);
	}
	
	function getKnownProperties() {
    	$res = array();
    	$vars = get_object_vars($this);
    	foreach ($this->knownProperties as $prop) {
    		if (is_callable($call = array($this, 'get'.ucfirst($prop)))) $res[$prop] = call_user_func($call);
    		elseif (array_key_exists($prop, $vars)) $res[$prop] = $vars[$prop];
    	}
    	return $res;
	}
	
	function _setParentObject($parentObject) {
		$this->_parentObject = $parentObject;
	}
	
	function getParentObject() {
		return $this->_parentObject;
	}
	
}