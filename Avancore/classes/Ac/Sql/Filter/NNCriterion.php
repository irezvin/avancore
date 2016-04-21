<?php

/**
 * 
 */

/**
 * value format: array(leftValues => array, rightValues => array)
 * if improper value is provided (arrray without left- or rightValues), input can be converted depending on 
 * $this->plainInputMode
 */
abstract class Ac_Sql_Filter_NNCriterion extends Ac_Sql_Filter {
    
    /**
     * Throw error if input is not an associative array with one or both items (array('leftValues' => array, 'rightValues' => array'))
     */
    const PLAIN_INPUT_DENIED = 0;
    
    /**
     * When input value is array($keys), convert it to array('leftValues' => array($keys))
     */
    const PLAIN_INPUT_LEFT_VALUES = 1;
    
    /**
     * When input value is array($keys), convert it to array('rightValues' => array($keys))
     */
    const PLAIN_INPUT_RIGHT_VALUES = 2;
    
    /**
     * @var int
     * One of Ac_Sql_Filter::PLAIN_INPUT_* constants
     */
    var $plainInputMode = self::PLAIN_INPUT_LEFT_VALUES;
    
    /**
     * Alias of n-n table (MUST be provided, otherwise will fail if $leftValues are provided)
     * @var string
     */
    var $nnTableAlias = false;
    
    /**
     * Alias of table that has referenced records.
     * If not provided, primary alias of SQL select will be used (if any)
     */
    var $rightAlias = false;
    
    /**
     * If only $leftValues are provided, stricten join of NN table
     * @var bool
     */
    var $useInnerJoinIfPossible = true;
    
    var $nnRestriction = array(); // TODO: use nnRestriction

    protected $leftValues = false;
    
    protected $rightValues = false;
    
    function _doGetAppliedAliases() {
        $res = Ac_Util::toArray($this->aliases);
        if ($this->leftValues) {
            if (!strlen($this->nnTableAlias)) 
                throw new Ac_E_InvalidUsage("\$leftValues are provided, but \$nnTableAlias property is not set");
            $res[] = $this->nnTableAlias;
        }
        if (strlen($this->rightAlias)) $res[] = $this->rightAlias;
        return $res;
    }
    
    function _doGetAppliedWhere() {
        $res = array();
        if ($this->rightValues) {
            $rightAlias = strlen($this->rightAlias)? $this->rightAlias : $this->currentSelect->getPrimaryAlias();
            $res['right'] = $this->_doGetRightValuesCriterion($rightAlias);
        }
        if ($this->leftValues) {
            $res['left'] = $this->_doGetLeftNotNullCriterion();
        }
        return $res;
    }
    
    function _doBeforeExpandPaths(& $input) {
        return false; // DOES NOT expand paths, 'll save us some time
    }
    
    function _doApplyToSelect($select) {
        if ($this->leftValues) {
            $nnTable = $select->getTable($this->nnTableAlias);
            if ($this->rightValues) {
                $tmp = $nnTable->joinsOn;
                $joinsOn = $nnTable->getJoinsOn();
                $nnTable->joinsOn = "(".$joinsOn.") AND (".$this->_doGetLeftValuesCriterion().")";
                $select->joinOverrides[$this->nnTableAlias] = $nnTable->getJoinClausePart();
                $nnTable->joinsOn = $tmp;
            } elseif ($this->useInnerJoinIfPossible) {
                $tmp = $nnTable->joinType;
                $nnTable->joinType = "INNER JOIN";
                $select->joinOverrides[$this->nnTableAlias] = $nnTable->getJoinClausePart();
                $nnTable->joinType = $tmp;
            }
        }
        parent::_doApplyToSelect($select);
    }
    
    function bind($input) {
        $this->leftValues = $this->rightValues = false;
        return parent::bind($input);
    }
    
    function _doBind($input) {
        if (!is_array($input)) $input = Ac_Util::toArray($input);
        if (!(isset($input['leftValues']) || isset($input['rightValues']))) {
            if ($this->plainInputMode == self::PLAIN_INPUT_DENIED) {
                throw new Ac_E_InvalidUsage("\$input must be an array with one or two keys: 'leftValues' => array, 'rightValues' => array");
            } elseif ($this->plainInputMode == self::PLAIN_INPUT_LEFT_VALUES) {
                $input = array('leftValues' => $input);
            } elseif ($this->plainInputMode == self::PLAIN_INPUT_RIGHT_VALUES) {
                $input = array('rightValues' => $input);
            }
        }
        if (isset($input['leftValues'])) $this->leftValues = Ac_Util::toArray($this->leftValues);
        if (isset($input['rightValues'])) $this->rightValues = Ac_Util::toArray($this->rightValues);
    }
    
    /**
     * leftCol IN (leftValue1, leftValue2...)
     */
    abstract function _doGetLeftValuesCriterion();

    /**
     * rightCol IN (rightValue1, rightValue2...)
     */
    abstract function _doGetRightValuesCriterion($rightAlias);
    
    /**
     * leftCol IS NOT NULL
     */
    abstract function _doGetLeftNotNullCriterion();
    
}