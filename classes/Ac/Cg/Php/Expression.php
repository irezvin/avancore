<?php

class Ac_Cg_Php_Expression extends Ac_Cg_Base implements Ac_I_PhpExpression_Extended {
    
    var $expression = false;
    
    var $key = false;
    
    var $comment = false;
    
    var $exportValue = false;
    
    function __construct ($expression = '', $key = false, $comment = false, $exportValue = false) {
        $this->expression = $expression;
        $this->key = $key;
        $this->comment = $comment;
        $this->exportValue = $exportValue;
    }
    
    function getExpression($indent = 0) {
        $res = '';
        $ind = str_repeat(" ", $indent);
        if (strlen($this->comment)) {
            if ($this->key !== false) {
                foreach (preg_split("/\s*(\n\r|\r\n|\n)\s*/u", $this->comment) as $line) {
                    $res .= $ind."// ".$line."\n";
                }
            } else {
                $res = '/* '.$this->comment.' */ ';
            }
        }
        if ($this->key !== false) {
            $res .= $ind.Ac_Util_Php::export($this->key, true).' => ';
        }
        $suff = $this->exportValue? Ac_Util_Php::export($this->expression, true, $indent, false) : $this->expression;
        $res .= $suff;
        return $res;
    }
    
    function export($arrayKey = false, $indent = 0) {
        if ($arrayKey === false) return $this->getExpression($indent);
        
        $tmp = $this->key;
        if ($this->key === false) $this->key = $arrayKey;
        $res = $this->getExpression($indent);
        $this->key = $tmp;
        
        return $res;
    }
    
}