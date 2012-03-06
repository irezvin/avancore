<?php

class Ae_Sql_Filter_Switcher extends Ae_Sql_Filter {
     
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
    function _doGetAppliedWhere() {
        if (!$this->isHaving && ($this->value !== false) && ($this->modeMap[$this->value] !== false)) {
            $res = array($this->modeMap[$this->value]);
        } else {
            $res = array();
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        if ($this->isHaving && ($this->value !== false) && ($this->modeMap[$this->value] !== false)) {
            $res = array($this->modeMap[$this->value]);
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
            $res = array_unique(array_merge($res, $this->aliasesForModes[$this->value]));
        }
        return $res;
    }
    
}

?>
