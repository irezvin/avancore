<?php

class Ac_Sql_Order_Switcher extends Ac_Sql_Order {
     
    /**
     * @var array modeId => sqlExpression 
     */
    var $modeMap = array();

    var $aliasesForModes = array();
    
    var $value = false;
    
    /**
     * Mode if out-of-range or false value is specified 
     * @var string
     */
    var $fallbackMode = false;
    
    function _doBind($input) {
        $this->value = $this->fallbackMode;
        if (is_string($input)) {
            $input = trim($input);
            if (isset($this->modeMap[$input])) $this->value = $input;
        }
        $this->applied = ($this->value !== false) && ($this->modeMap[$this->value] !== false);
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedOrderBy () {
        if (($this->value !== false) && ($this->modeMap[$this->value] !== false)) {
            $res = $this->modeMap[$this->value];
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {        
        $res = $this->aliases;
        if ($this->value !== false && isset($this->aliasesForModes[$this->value])) {
            $res = array_unique(array_merge($res, Ac_Util::toArray($this->aliasesForModes[$this->value])));
        }
        return $res;
    }
  
}

