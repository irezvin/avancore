<?php

/**
 * tagging class for the value that will be ignored by Ac_Buffer
 */
final class Ac_Value_BufferIgnore {
    
    var $value = false;
    
    function __construct($value) {
        $this->value = $value;
    }
    
    function __toString() {
        Ac_Buffer::$ignoreNext = true;
        return ''.$this->value;
    }
    
}