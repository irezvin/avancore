<?php

class Ac_Sql_Order_Columns extends Ac_Sql_Order {
     
    /**
     * @var array colId => columnName 
     */
    var $colMap = array();

    var $allowMultipleColumns = true;
    
    var $allowDirection = true;
    
    var $allowDirectionForColumns = array();
    
    var $aliasesForColumns = array();
    
    var $values = array();
    
    var $_reverse = array();
    
    /**
     * @access protected
     */
    function _doGetAppliedOrderBy() {
        $res = array();
        $res = $this->values;
        if ($this->_reverse) {
            foreach (array_keys($this->_reverse) as $k) $res[$k] = $this->_db->reverseOrderDirection($res[$k]);
        }
        if (count($res)) $res = Ac_Util::flattenArray($res);
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {
        $res = $this->aliases;
        if (count($this->aliasesForColumns) && count($this->values)) {
            foreach (array_intersect(array_keys($this->aliasesForColumns), array_keys($this->values)) as $k) $res = array_merge($res, $this->aliasesForColumns[$k]); 
        }
        $res = array_unique($res);
        return $res;
    }
    
    function _doBind($input)  {
        if ($input === true) $input = $this->defaultInput;
        if (!is_array($input)) $input = array($input);
        $values = array();
        foreach ($input as $k => $v) {
            if (is_numeric($k)) {
                $k = $v;
                $v = true; 
            }
            $this->_reverse = array();
            if (isset($this->colMap[$k])) {
                $col = $this->colMap[$k];
                if (!$v && ($this->allowDirection || isset($this->allowDirectionForColumns[$k]))) $this->_reverse[$k] = true;
                $values[$k] = $col;
                if (!$this->allowMultipleColumns) break;
            }
        }
        $this->values = $values;
        $this->applied = count($this->values) > 0; 
    }
    
}

?>