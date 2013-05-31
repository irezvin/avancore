<?php

class Ae_Sql_Part {
    
    /**
     * @var Ae_Sql_Part
     */    
    var $_parentPart = false;
    
    /**
     * @var Ae_Sql_Db
     */
    var $_db = false;
    
    /**
     * Id of part is used to generate keys of SELECT statement subparts
     * @var string
     */
    var $id = false;
    
    /**
     * Whether this sql part will be applied to the statement (is changed with bind())
     * @var bool
     */
    var $applied = false;
    
    /**
     * Whether this sql part is applied to the statement at all (isn't changed with bind())
     * @var bool
     */
    var $enabled = true;
    
    /**
     * Aliases of tables that should be included in the statement
     * @var array
     */
    var $aliases = array();
    
    var $groupBy = array();
    
    var $defaultInput = array();
    
    var $appliedOnEmptyInput = false;
    
    var $_idWithPrefix = false;
    
    /**
     * @param array $options
     * @param string $baseClass
     * @return Ae_Sql_Part
     * @static
     */
    function & factory($options, $baseClass = 'Ae_Sql_Part') {
        if (isset($options['class'])) 
            $class = $options['class']; 
            else $class = $baseClass;
        if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass($class);
            else if (!class_exists($class)) require(str_replace('_', '/', $class).'.php');
        $res = new $class($options);
        assert(is_a($res, $baseClass));
        return $res;
    }
    
    function Ae_Sql_Part($options = array()) {
        if (isset($options['db'])) $this->setDb($options['db']);
        if (isset($options['parentPart'])) $this->setParentPart($options['parentPart']);
        foreach (array_intersect(array_keys(get_object_vars($this)), array_keys($options)) as $k)
            if ($k{0} != '_') $this->$k = $options[$k];
        $this->_doOnInitialize($options);
    }
    
    /**
     * @param Ae_Sql_Db $db
     */
    function setDb(& $db) {
        assert($db === false || is_a($db, 'Ae_Sql_Db'));
        $this->_db = & $db;
    }

    /**
     * @param Ae_Sql_Part $parentPart
     */
    function setParentPart(& $parentPart) {
        assert($parentPart === false || is_a($parentPart, 'Ae_Sql_Part'));
        $this->_parentPart = & $parentPart;
    }
    
    function getIdWithPrefix() {
        if ($this->_idWithPrefix === false) {
            $this->_idWithPrefix = '';
            if ($this->_parentPart) $this->_idWithPrefix = $this->_parentPart->getIdWithPrefix().'.';
            if (strlen($this->id)) $this->_idWithPrefix .= $this->id;
        }
        return $this->_idWithPrefix; 
    }
    
    function bind($input) {
        if (!$this->appliedOnEmptyInput && empty($input) && $input !== 0 && $input !== '0') {
            if (empty($input) && $input !== 0 && $input !== '0') {
                $this->applied = false;
            }
        } else {
            $this->applied = true;
        }
        if ($this->doesApply()) {
            if (is_array($input)) {
                if ($this->_doBeforeExpandPaths($input) !== false) $input = $this->_expandPaths($input);
            }
            $this->_doBind($input);
        }
    }
    
    function doesApply() {
        return $this->applied && $this->enabled;
    }
    
    function getAppliedGroupBy() {
        $res = array();
        if ($this->doesApply()) $res = $this->_doGetAppliedGroupBy();
        $res = $this->_applyPrefix($res);
        return $res;
    }
    
    function getAppliedAliases() {
        $res = array();
        if ($this->doesApply()) $res = $this->_doGetAppliedAliases();
        return $res;
    }
    
    /**
     * Applies statement part to Select statement
     *
     * @param Ae_Sql_Select $select
     */
    function applyToSelect(& $select) {
        assert(is_a($select, 'Ae_Sql_Select'));
        if (!$this->_db) $this->setDb($select->getDb());
        if ($this->doesApply()) {
            $this->_doApplyToSelect($select);
        }
    }
    
    // ---------------------------------- template methods ------------------------------
    
    /**
     * @access protected
     * @param Ae_Sql_Select $select
     */
    function _doApplyToSelect(& $select) {
        $select->groupBy = array_merge($select->groupBy, $this->getAppliedGroupBy());
        $select->useAlias($this->getAppliedAliases());
    }

    /**
     * @access protected
     * @param array $options
     */
    function _doOnInitialize($options) {
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedGroupBy() {
        return $this->groupBy? $this->groupBy : array();
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {
        return $this->aliases;
    }

    function _doBeforeExpandPaths(& $input) {
    }
    
    /**
     * Inintializes runtime parameters from input scalar or array. Is executed only if doesApply() is true.
     * 
     * @access protected
     * @param mixed $input If input is array, it's paths are already expanded.
     */
    function _doBind($input) {
    }
    
    // ---------------------------------- supplementary methods ------------------------------
    
    /**
     * @access protected
     */
    function _applyPrefix($array) {
        $px = $this->getIdWithPrefix();
        if (strlen($px)) {
            $res = array();
            foreach (array_keys($array) as $k) $res[$px.'.'.$k] = & $array[$k];
        } else {
            $res = $array;
        }
        return $res;
    }
    
    /**
     * @access protected
     */
    function _expandPaths($array) {
        $res = array();
        foreach (array_keys($array) as $k) {
            if (is_array($array[$k])) {
                $foo = false;
                $v = & $foo;
                $v = $this->_expandPaths($array[$k]);
            } else $v = & $array[$k];
            if (strpos($k, '.') !== false) $this->_setArrayByPath($res, $k, $v);
                else $res[$k] = & $v;
        }
        return $res;
    }
    
    function _setArrayByPath(& $array, $path, & $value) {
        $arrPath = explode('.', $path);
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        $key = array_pop($arrPath);
        while ($arrPath) {
            if (!isset($src[$key])) $src[$key] = array();
            elseif (!is_array($src[$key])) {
                $src[$key] = array();
            }
            $src = & $src[$key];
            $key = array_pop($arrPath);
        }
        if ($unique) $src[$key] = & $value;
            else $src[$key][] = & $value;
    }

}

?>