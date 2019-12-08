<?php

class Ac_Decorator_Text_Split extends Ac_Decorator {
    
    var $separator = "/\s*[,;]\s*/u";
    
    var $pregSplitOptions = PREG_SPLIT_NO_EMPTY;
    
    var $limit = -1;
    
    var $literal = false;
    
    function apply($value) {
        if ($this->literal) return explode($this->separator, $value, $this->limit === -1? PHP_INT_MAX : $this->limit);
        return preg_split($this->separator, $value, $this->limit, $this->pregSplitOptions);
    }
   
}