<?php

/**
 * 
 */

/**
 * value format: array(srcValues => array, destValues => array)
 * if improper value is provided (arrray without src- or destValues), input can be converted depending on 
 * $this->plainInputMode
 */
abstract class Ac_Sql_Filter_NNCriterion extends Ac_Sql_Filter {
    
    /**
     * Throw error if input is not an associative array with one or both items (array('srcValues' => array, 'destValues' => array'))
     */
    const PLAIN_INPUT_DENIED = 0;
    
    /**
     * When input value is array($keys), convert it to array('srcValues' => array($keys))
     */
    const PLAIN_INPUT_SRC_VALUES = 1;
    
    /**
     * When input value is array($keys), convert it to array('destValues' => array($keys))
     */
    const PLAIN_INPUT_DEST_VALUES = 2;
    
    /**
     * @var int
     * One of Ac_Sql_Filter::PLAIN_INPUT_* constants
     */
    var $plainInputMode = self::PLAIN_INPUT_SRC_VALUES;
    
    /**
     * Alias of n-n table (MUST be provided, otherwise will fail if $srcValues are provided)
     * @var string
     */
    var $midTableAlias = false;
    
    /**
     * Alias of table that has referenced records.
     * If not provided, primary alias of SQL select will be used (if any)
     */
    var $destAlias = false;
    
    /**
     * If only $srcValues are provided, stricten join of NN table
     * @var bool
     */
    var $useInnerJoinIfPossible = true;
    
    var $nnRestriction = array(); // TODO: use nnRestriction

    protected $srcValues = false;
    
    protected $destValues = false;
    
    function _doGetAppliedAliases() {
        $res = Ac_Util::toArray($this->aliases);
        if ($this->srcValues) {
            if (!strlen($this->midTableAlias)) 
                throw new Ac_E_InvalidUsage("\$srcValues are provided, but \$midTableAlias property is not set");
            $res[] = $this->midTableAlias;
        }
        if (strlen($this->destAlias)) $res[] = $this->destAlias;
        return $res;
    }
    
    function _doGetAppliedWhere() {
        $res = array();
        if ($this->destValues) {
            $destAlias = strlen($this->destAlias)? $this->destAlias : $this->currentSelect->getPrimaryAlias();
            $res['dest'] = $this->_doGetDestValuesCriterion($destAlias);
        }
        if ($this->srcValues) {
            $res['src'] = $this->_doGetSrcNotNullCriterion();
        }
        return $res;
    }
    
    function _doBeforeExpandPaths(& $input) {
        return false; // DOES NOT expand paths, 'll save us some time
    }
    
    function _doApplyToSelect($select) {
        if ($this->srcValues) {
            $midTable = $select->getTable($this->midTableAlias);
            if ($this->destValues) {
                $tmp = $midTable->joinsOn;
                $joinsOn = $midTable->getJoinsOn();
                $midTable->joinsOn = "(".$joinsOn.") AND (".$this->_doGetSrcValuesCriterion().")";
                $select->joinOverrides[$this->midTableAlias] = $midTable->getJoinClausePart();
                $midTable->joinsOn = $tmp;
            } elseif ($this->useInnerJoinIfPossible) {
                $tmp = $midTable->joinType;
                $midTable->joinType = "INNER JOIN";
                $select->joinOverrides[$this->midTableAlias] = $midTable->getJoinClausePart();
                $midTable->joinType = $tmp;
            }
        }
        parent::_doApplyToSelect($select);
    }
    
    function bind($input) {
        $this->srcValues = $this->destValues = false;
        return parent::bind($input);
    }
    
    function _doBind($input) {
        if (!is_array($input)) $input = Ac_Util::toArray($input);
        if (!(isset($input['srcValues']) || isset($input['destValues']))) {
            if ($this->plainInputMode == self::PLAIN_INPUT_DENIED) {
                throw new Ac_E_InvalidUsage("\$input must be an array with one or two keys: 'srcValues' => array, 'destValues' => array");
            } elseif ($this->plainInputMode == self::PLAIN_INPUT_SRC_VALUES) {
                $input = array('srcValues' => $input);
            } elseif ($this->plainInputMode == self::PLAIN_INPUT_DEST_VALUES) {
                $input = array('destValues' => $input);
            }
        }
        if (isset($input['srcValues'])) $this->srcValues = Ac_Util::toArray($this->srcValues);
        if (isset($input['destValues'])) $this->destValues = Ac_Util::toArray($this->destValues);
    }
    
    /**
     * srcCol IN (srcValue1, srcValue2...)
     */
    abstract function _doGetSrcValuesCriterion();

    /**
     * destCol IN (destValue1, destValue2...)
     */
    abstract function _doGetDestValuesCriterion($destAlias);
    
    /**
     * srcCol IS NOT NULL
     */
    abstract function _doGetSrcNotNullCriterion();
    
}