<?php

class Ae_Js_String {
    
    var $string = '';
    
    var $splitLines = false;
    
    function __construct($string = 'undefined', $splitLines = false) { 
        $this->string = (string) $string;
        $this->splitLines = $splitLines; 
    }
    
    function __toString() {
    	return $this->toJs();
    }
    
    function toJs() {
        
    	static $js = null;
    	if (is_null($js)) $js = new Ae_Js();
        if ($this->splitLines) {
            $lines = explode("\n", $this->string);
            if (($c = count($lines)) > 1) {
                $res = "\n   ".$js->toJs($lines[0]);
                for ($i = 1; $i < $c; $i++) {
                    $res .= "\n + ".$js->toJs("\n".$lines[$i]);
                }
                return $res;
            }
        }
        return $js->toJs($this->string);
    }
	
}