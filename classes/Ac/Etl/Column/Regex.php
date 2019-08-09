<?php

class Ac_Etl_Column_Regex extends Ac_Etl_Column {
    
    protected $matchToColumnMap = array();
    
    protected $matchDecorators = array();
    
    protected $regex = false;

    function setRegex($regex) {
        $this->regex = $regex;
    }

    function getRegex() {
        return $this->regex;
    }    
    
    /**
     * @var bool
     */
    protected $leaveOriginalValue = false;

    function setMatchToColumnMap(array $matchToColumnMap) {
        $this->matchToColumnMap = $matchToColumnMap;
    }

    function getMatchToColumnMap() {
        return $this->matchToColumnMap;
    }

    function setMatchDecorators($matchDecorators) {
        $this->matchDecorators = $matchDecorators;
    }

    function getMatchDecorators() {
        return $this->matchDecorators;
    }
    
    function apply(Ac_I_Param_Source $source, array & $destRecords, array & $errors = array()) {
        
        if (!strlen($this->regex)) throw new Exception("'{$this->id}' column: \$regex property not set");
        
        $map = $this->matchToColumnMap;
        $deco = & $this->matchDecorators;
        
        $res = parent::apply($source, $destRecords, $errors);
        
        $dc = $this->getDestColName();
        $t = $this->getDestTableId();
        
        $val = Ac_Util::getArrayByPath($destRecords, array($t, 0, $dc));
        
        if (!$this->leaveOriginalValue) {
            unset($destRecords[$t][0][$dc]);
        }
        
        if (!is_null($val)) {
            if(preg_match($this->regex, $val, $matches)) {
                foreach ($matches as $id => $text) {
                    if (isset($map[$id])) {
                        if (isset($deco[$id]))
                            $matches[$id] = Ac_Decorator::decorate($deco[$id], $matches[$id], $deco[$id]);
                        $this->putData($matches[$id], $destRecords, $map[$id]);
                    }
                }
            }
        }
        
        return $res;
    }    

    function setLeaveOriginalValue($leaveOriginalValue) {
        $this->leaveOriginalValue = (bool) $leaveOriginalValue;
    }

    /**
     * @return bool
     */
    function getLeaveOriginalValue() {
        return $this->leaveOriginalValue;
    }    
    
    
}