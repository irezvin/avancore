<?php

class Ac_Sql_Quoter implements Ac_I_Autoparams {
    
    var $nameQuoteChar = '"';
    
    var $ifNullFunction = 'IFNULL';
 
    function hasPublicVars() {
        return true;
    }
    
    function nameQuote($name) {
        return $this->nameQuoteChar.str_replace($this->nameQuoteChar, "\\".$this->nameQuoteChar, $name).$this->nameQuoteChar;
    }
    
    function nameUnquote($name) {
        if ($this->isNameQuoted($name)) {
            return str_replace("\\".$this->nameQuoteChar, $this->nameQuoteChar, substr($name, 1, strlen($name) - 2));
        } else {
            return $name;
        }
        
    }
    
    function isNameQuoted($name) {
        return $name{0} == $this->nameQuoteChar && $name{strlen($name) - 1} == $this->nameQuoteChar;
    }
    
    function getIfNullFunction() {
        return $this->ifNullFunction;
    }
    
}