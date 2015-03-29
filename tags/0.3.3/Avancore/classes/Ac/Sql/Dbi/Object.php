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
    
    function Ac_Sql_Dbi_Object ($inspector, $name) {
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
    
    /**
     * @return array(parentClass, parentMemberName)
     */
    protected function getSerializationParentInfo() {
        return array(null, null);
    }
    
    /**
     * @return array (myProperty => array(arrayKey, defaultClass, crArgs))
     * crArgs => array(keyA, keyB, keyC) <- constructor args map
     * crArgs = false -- just copy $this->$myProperty to/from $array[$arrayKey]
     */
    protected function getSerializationMap() {
        $res = array();
        return $res;
    }
    
    public function unserializeFromArray($array) {
        $vars = Ac_Impl_ArraySerializer::getUnserializationVars($this, $array);
        $allowed = array_unique(array_merge(array_keys(Ac_Util::getPublicVars($this)), array_keys($this->getSerializationMap())));
        foreach (array_intersect_key($vars, array_flip($allowed)) as $k => $v) $this->$k = $v;
    }
    
    public function serializeToArray() {
        $allowed = array_unique(array_merge(array_keys(Ac_Util::getPublicVars($this)), array_keys($this->getSerializationMap())));
        $pub = array_intersect_key(get_object_vars($this), $allowed);
        $res = Ac_Impl_ArraySerializer::serializeToArray($this, $pub);
        return $res;
    }
    
}

