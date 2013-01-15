<?php

class Ac_Sql_Order_Simple extends Ac_Sql_Order {
    
    var $order = false;
    
    var $orderIfDesc = false;
    
    /**
     * If $direction is > 0, order is considered ascending; if $direction < 0, order is considered descending; if $direction is 0 or FALSE, order isn't applied
     * @var int
     */
    var $direction = 1;
    
    function _doBind($input) {
        $this->direction = (int) $input;
    }
    
    function doesApply() {
        return $this->applied && $this->enabled && $this->direction;
    }
    
    function _doGetAppliedOrderBy() {
        $res = array();
        if (($this->orderIfDesc !== false) && $this->direction < 0) $res = is_array($this->orderIfDesc)? $this->orderIfDesc : array($this->orderIfDesc); 
        elseif ($this->order !== false) $res = is_array($this->order)? $this->order : array($this->order);
        return $res;
    }
    
}

?>