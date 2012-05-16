<?php

/**
 * Usage example:
 * 
 * <code>
 *      $foo = new Ac_Sql_Filter_Custom(array(
 *                'params' => array(
 *                   'lifetime' => 150,
 *               ),
 *               'defaultParamName' => 'lifetime',
 *               'where' => 'ISNULL(views.versionId) OR (NOT ISNULL({lifetime}) AND (UNIX_TIMESTAMP(views.updateTime) - {lifetime} * 24 * 3600 > 0))',
 *               'aliases' => array('views')
 *      ));
 *      
 *      $foo->bind(10);                         // Will put '10' in place of {lifetime}
 *      $foo->bind(array('lifetime' => 10));    // Same as before 
 *      $foo->bind(array());                    // Will put NULL 150 in place of {lifetime} (a default value) 
 * </code>
 */

class Ac_Sql_Filter_Custom extends Ac_Sql_Filter {
    
    var $params = array(
        'value' => null,
    );
    var $defaultParamName = 'value';
    var $placeholderStart = '{';
    var $placeholderEnd = '}';
    var $where = false;
    var $having = false;
    
    function getDefaults() {
        $res = array();
        foreach ($this->params as $k => $v) {
            if (is_numeric($k)) {
                $k = $v;
                $v = null;
            }
            $res[$k] = $v;
        }
        return $res;
    }
    
    function substitute($str) {
        $s = array();
        foreach ($this->getDefaults() as $k => $v) {
            if (array_key_exists($k, $this->values)) $v = $this->values[$k];
            $s[$this->placeholderStart.$k.$this->placeholderEnd] = $this->_db->q($v);
        }
        $str = strtr($str, $s);
        return $str;
    }
    

    /**
     * @access protected
     */
    function _doBind($input) {
        $this->values = array();
        if (!is_array($input) || (count($this->params) == 1)) {
            if (strlen($this->defaultParamName)) $input = array($this->defaultParamName => $input);
                else $input = array();
        }
        foreach (array_keys($this->getDefaults()) as $k) {
            if (array_key_exists($k, $input)) $this->values[$k] = $input[$k];
        }
        if (!count($this->values)) $this->applied = false;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedWhere() {
        $res = array();
        if (strlen($this->where)) $res[] = $this->substitute($this->where);     
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedHaving() {
        $res = array();
        if (strlen($this->having)) $res[] = $this->substitute($this->having);       
        return $res;
    }
    
}

?>