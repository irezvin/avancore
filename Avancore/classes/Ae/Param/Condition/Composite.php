<?php

abstract class Ae_Param_Condition_Composite extends Ae_Param_Condition {

    protected $conditions = array();

    function setConditions(array $conditions) {
        $this->conditions = $conditions;
    }

    function getConditions($asIs = false) {
        if ($asIs) $res = $this->conditions;
        else {
            $res = array();
            foreach ($this->listConditions() as $i)
                    $res[$i] = $this->getCondition($i);
        }
        return $res;
    }

    function listConditions() {
        return array_keys($this->conditions);
    }

    /**
     * @param string|int $index
     * @return Ae_Param_Condition
     */
    function getCondition($index) {
        if (!isset($this->conditions[$index])) throw new Exception("No such condition", $index);
        else {
            if (!(is_object($this->conditions[$index]) && ($this->conditions[$index] instanceof Ae_I_Param_Condition))) {
                $this->conditions[$index] = Ae_Autoparams::factory($this->conditions[$index], 'Ae_I_Param_Condition');
            }
        }
        return $this->conditions[$index];
    }

}
