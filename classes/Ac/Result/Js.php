<?php

class Ac_Result_Js extends Ac_Result_Json {
    
    protected $contentType = 'text/javascript';
    
    protected $data = array();
    
    /**
     * @param bool $strict
     */
    function setStrict($strict) {
        $this->strict = $strict;
    }

    /**
     * @return bool
     */
    function getStrict() {
        return $this->strict;
    }    
    
    function toJs(Ac_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        return "(function() { return ".$js->toJs($this->data, $indent, $indentStep, $newLines)." }) ()";
    }
    
    
    
}