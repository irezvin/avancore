<?php

abstract class Ac_Sql_Part extends Ac_Prototyped {
    
    static $strictParams = Ac_Prototyped::STRICT_PARAMS_WARNING;
    
    /**
     * @var Ac_Sql_Part
     */    
    var $_parentPart = false;
    
    /**
     * @var Ac_Sql_Db
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
    
    var $inputDecorator = null;
    
    var $_idWithPrefix = false;
    
    /**
     * The Select that current part is being applied to
     * @var Ac_Sql_Select
     */
    protected $currentSelect = null;
    
    protected $value = null;
    
    function __construct($options = array()) {
        $hasValue = false;
        $value = null;
        if (array_key_exists('value', $options)) {
            $value = $options['value'];
            $hasValue = true;
        } else if (array_key_exists('bind', $options)) {
            $value = $options['bind'];
            $hasValue = true;
        }
        $this->initOptionsFirst(['db', 'parentPart'], $options);
        parent::__construct($options);
        if ($hasValue) $this->setValue($value);
    }
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @param Ac_Sql_Db $db
     */
    function setDb($db) {
        assert($db === false || is_a($db, 'Ac_Sql_Db'));
        $this->_db = $db;
    }

    /**
     * @param Ac_Sql_Part $parentPart
     */
    function setParentPart($parentPart) {
        assert($parentPart === false || is_a($parentPart, 'Ac_Sql_Part'));
        $this->_parentPart = $parentPart;
        $this->_idWithPrefix = false;
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
        if ($this->inputDecorator) $input = Ac_Decorator::decorate ($this->inputDecorator, $input, $this->inputDecorator);
        $this->value = $input;
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
    
    function setValue($value = null) {
        if (!func_num_args()) { // special format: call without arguments disables the part
            $this->applied = false;
            $this->value = null;
            return;
        }
        return $this->bind($value);
    }
    
    function getValue() {
        return $this->value;
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
     * @param Ac_Sql_Select $select
     */
    function applyToSelect(Ac_Sql_Select $select) {
        $tmp = $this->currentSelect = null;
        $this->currentSelect = $select;
        if (!$this->_db) $this->setDb($select->getDb());
        if ($this->doesApply()) {
            $this->_doApplyToSelect($select);
        }
        $this->currentSelect = $tmp;
    }
    
    // ---------------------------------- template methods ------------------------------
    
    /**
     * @access protected
     * @param Ac_Sql_Select $select
     */
    function _doApplyToSelect($select) {
        $select->groupBy = Ac_Util::m($select->groupBy, $this->getAppliedGroupBy());
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
        return Ac_Util::toArray($this->aliases);
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
    function _applyPrefix($value) {
        $px = $this->getIdWithPrefix();
        if (strlen($px)) {
            if (is_array($value)) {
                $res = array();
                foreach (array_keys($value) as $k) $res[$px.'.'.$k] = & $value[$k];
            } else {
                $res = array($px => $value);
            }
        } else {
            $res = $value;
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
                $v = $this->_expandPaths($array[$k]);
            } else $v = & $array[$k];
            if (strpos($k, '.') !== false) $this->_setArrayByPath($res, $k, $v);
                else $res[$k] = $v;
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
    
    function __clone() {
    }

}

