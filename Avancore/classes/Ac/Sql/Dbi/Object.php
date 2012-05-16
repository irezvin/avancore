<?php

/**
 * Base class for all database schema elements
 */

class Ac_Sql_Dbi_Object {
    
    /**
     * @var Ac_Sql_Dbi_Inspector
     */
    var $_inspector = false;
    
    var $name;
    
    var $knownProperties = array(); 
    
    var $extensions = array();
    
    function Ac_Sql_Dbi_Object (& $inspector, $name) {
        $this->_inspector = $inspector;
        $this->name = $name;
    }
    
    function _assignProperties ($properties = array()) {
    	Ac_Util::simpleBindAll($properties, $this, true);
    	$this->knownProperties = array_keys($properties);
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
    
}

?>