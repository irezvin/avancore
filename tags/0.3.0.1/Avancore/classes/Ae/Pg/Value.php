<?php

class Ae_Pg_Value {
    
    var $value = false;
    
    function Ae_Pg_Value($value) {
        $this->value = $value;
    }
    
    function getQuoted($connection = null) {
        return "'".pg_escape_string($connection, $this->value)."'";
    }
    
}

?>