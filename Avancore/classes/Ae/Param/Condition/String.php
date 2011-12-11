<?php

class Ae_Param_Condition_String extends Ae_Param_Condition {

    var $encoding = 'utf-8';
    
    var $maxLength = false;
    
    var $minLength = false;
    
    var $regex = false;
    
    var $regexDescr = false;
    
    var $exactString = false;

    function getTranslations() {
        return array_merge(parent::getTranslations(), Ae_Autoparams::getObjectProperty($this, array('maxLength', 'minLength', 'regexDescr')));
    }
    
    static function getEmailRegex() {
        return Ae_Util::getEmailRx();
    } 
    
    static function getIdentifierRegex() {
        $res = '@^[\w+]$@u';
        return $res;
    }
    
    static function getUrlRegex() {
        $res = '@^((https?://)?([-\w]+\.[-\w\.]+)+\w(:\d+)?(/([-\w/_\.]*(\?\S+)?)?)*)$@';
        return $res;
    }
    
    function match($value, & $errors = array(), Ae_I_Param $param = null) {
        $px = 'ae_param_condition_string';
        if (!$this->exactString && is_scalar($value)) $value = (string) $value;
        if (!is_string($value)) $this->regError($errors, 'non_string', $px, '{param} must be a string');
        else {
            $len = $this->encoding? mb_strlen($value, $this->encoding) : strlen($value);
            if ($this->minLength !== false && $len < $this->minLength) 
                $this->regError($errors, 'min_length', $px, '{param} must be at least {minLength} characters long');
            if ($this->maxLength !== false && $len > $this->maxLength) 
                $this->regError($errors, 'max_length', $px, '{param} cannot be longer than {maxLength} characters');
            if ($this->regex && !preg_match($this->regex, $value)) {
                if ($this->regexDescr) 
                    $this->regError($errors, 'regex_with_descr', $px, '{param} must be a valid {regexDescr}');
                else 
                    $this->regError($errors, 'regex', $px, 'Invalid {param} value');
            }
        }
        return !$errors;        
    }
    
}