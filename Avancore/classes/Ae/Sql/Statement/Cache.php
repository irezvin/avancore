<?php

class Ae_Sql_Statement_Cache {
	
	var $defaults = array();
	var $enabled = true;
	
	var $_stmtCache = array();
	
	function Ae_Sql_Statement_Cache($options) {
		Ae_Util::bindAutoparams($this, $options);
	}
	
	function clear() {
		$this->_stmtCache = array();
	}
	
    function getStatement($parts, $params = array(), $useCache = null) {
        if (is_null($useCache)) $useCache = $this->enabled;
        if ($useCache) {
            $md = md5(serialize($parts));
            if (!isset($this->_stmtCache[$md])) {
                $this->_stmtCache[$md] = Ae_Sql_Statement::factory($parts, $this->defaults); 
            }
            $this->_stmtCache[$md]->applyParams($params);
            return $this->_stmtCache[$md];
        } else {
            $res = Ae_Sql_Statement::factory($parts, $this->defaults);
            return $res;
        }
    }
	
	
}
