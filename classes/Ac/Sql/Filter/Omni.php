<?php

class Ac_Sql_Filter_Omni extends Ac_Sql_Filter {
    
    /**
     * @var Ac_Model_Condition_AbstractCondition
     */
    protected $condition = null;

    protected $currentColumn = null;
    
    protected $propStack = [];
    
    protected $conditionAliases = [];
    
    protected $expression = [];
    
    var $fieldsNotation = false;
    
    var $defaultAlias = null;
    
    function _doBind($input) {
        $parser = new Ac_Model_Condition_Parser_OmniFilterParser;
        
        if ($this->fieldsNotation) $proto = $parser->parseFieldsNotation($input);
            else $proto = $parser->parseJsonNotation($input);
        
        $this->condition = Ac_Prototyped::factory($proto);
    }
    
    protected function rebuild() {
        $this->propStack = [];
        $this->currentColumn = null;
        $this->conditionAliases = [];
        $this->expression = $this->buildExpression($this->condition);
    }
    
    protected function pushProperty($property) {
        $arr = Ac_Util::pathToArray($property);
        $tail = array_pop($arr);
        $alias = null;
        if ($arr) {
            $alias = Ac_Util::arrayToPath($arr);
            if (!in_array($alias, $this->conditionAliases)) {
                $this->conditionAliases[] = $alias;
            }
        }
        $ht = [$tail];
        if (is_null($alias)) $alias = $this->defaultAlias;
        if (!is_null($alias)) array_unshift($ht, $alias);
        $colName = $this->_db->nameQuote($ht);
        if ($this->currentColumn) array_push($this->propStack, $this->currentColumn);
        $this->currentColumn = $colName;
    }
    
    protected function popProperty() {
        $this->currentColumn = array_pop($this->propStack);
    }
    
    protected function buildMultiExpression(Ac_Model_Condition_MultiCondition $condition) {
        $cond = $condition->getConditions();
        $h = array_map([$this, 'buildExpression'], $cond);
        if (count($h) > 1) {
            if ($condition->matchAll) array_unshift($h, " AND ");
            else array_unshift($h, " OR ");
            $res = $this->stringifyExpression($h);
        } else {
            $res = $this->stringifyExpression($h[0]);
        }
        if ($condition->not) {
            $res = "NOT ".$res;
        }
        return $res;
    }
    
    protected function buildExpression(Ac_Model_Condition_AbstractCondition $condition) {
        $db = $this->_db;
        if ($condition instanceof Ac_Model_Condition_PropertyCondition && strlen($condition->property)) {
            $this->pushProperty($condition->property);
            $res = $this->buildMultiExpression($condition);
            $this->popProperty();
            return $res;
        } else if ($condition instanceof Ac_Model_Condition_MultiCondition) {
            return $this->buildMultiExpression($condition);
        }
        if (!$this->currentColumn) {
            throw new Exception("Cannot convert ".get_class($condition)." to SQL outside of property context");
        }
        if ($condition instanceof Ac_Model_Condition_EqualsCondition) {
            return $this->currentColumn." = ".$db->quote($condition->value);
        }
        if ($condition instanceof Ac_Model_Condition_EmptyCondition) {
            return "(".$this->currentColumn." IS NULL OR NOT ".$this->currentColumn.")";
        } else if ($condition instanceof Ac_Model_Condition_RangeCondition) {
            if ($condition->min !== false && $condition->max !== false) {
                return "$this->currentColumn BETWEEN ".$db->q($condition->min)." AND ".$db->q($condition->max);
            } else if ($condition->min !== false) {
                return "$this->currentColumn >= ".$db->q($condition->min);
            } else if ($condition->max !== false) {
                return "$this->currentColumn <= ".$db->q($condition->max);
            }
        } else if ($condition instanceof Ac_Model_Condition_RegexpCondition) {
            return "$this->currentColumn RLIKE ".$db->q(preg_replace("#(^/)|(/\w*$)#", "", $condition->regexp));
        }
        throw new Exception("Unsupported condition class: ".get_class($condition));
    }
    
    /**
     * Expression format is [glue, piece1, piece2...]
     * If there is more than one piece, result is returned as "(piece1 glue piece2 glue piece3)"
     * Else result is returned as piece1
     * 
     * @param array $expression
     */
    
    protected function stringifyExpression($expression) {
        if (!is_array($expression)) return $expression;
        $glue = $expression[0];
        $pieces = array_map([$this, 'stringifyExpression'], array_slice($expression, 1));
        if (count($pieces) == 1) return $pieces[0];
        return "(".implode($glue, $pieces).")";
    }
    
    /**
     * @access protected
     */
    function _doGetAppliedAliases() {
        $res = parent::_doGetAppliedAliases($this->aliases);
        if ($this->conditionAliases) return array_unique(array_merge($res, $this->conditionAliases));
        return $res;
    }
    
    
    function getAppliedWhere() {
        if (!$this->doesApply()) return [];
        $this->rebuild();
        return $this->expression;
    }
    
}