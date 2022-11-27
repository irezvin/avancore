<?php

class Ac_Model_Condition_MultiCondition extends Ac_Model_Condition_AbstractCondition {

    /**
     * @var Ac_Model_Condition_AbstractCondition[]
     */
    protected $conditions = [];
    
    var $not = false;
    
    /**
     * matchAll = false: "OR"
     * matchAll = true: "AND"
     */
    var $matchAll = false;
    
    protected $defaultConditionsClass = null;
    
    function setConditions(array $conditions) {
        if ($this->defaultConditionsClass) {
            foreach ($conditions as $k => $v) if (is_array($v)) {
                if (isset($v['class'])) continue;
                $conditions[$k]['class'] = $this->defaultConditionsClass;
            }
        }
        $this->conditions = Ac_Prototyped::factoryCollection(
            $conditions, 
            Ac_Model_Condition_AbstractCondition::class,
            ['parent' => $this], 
            false, 
            false, 
            true
        );
    }
    
    /**
     * @return Ac_Model_Condition_AbstractCondition[]
     */
    function getConditions() {
        return $this->conditions;
    }
    
    function test($value) {
        if (!count($this->conditions)) {
            $res = false;
        } else if ($this->matchAll) {
            $res = true;
            foreach ($this->conditions as $cmp) {
                if (!$cmp->test($value)) {
                    $res = false;
                    break;
                }
            }
        } else {
            $res = false;
            foreach ($this->conditions as $cmp) {
                if ($cmp->test($value)) {
                    $res = true;
                    break;
                }
            }
        }
        if ($this->not) $res = !$res;
        return $res;
    }
    
}