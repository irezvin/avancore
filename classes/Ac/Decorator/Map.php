<?php

class Ac_Decorator_Map extends Ac_Decorator {
    
    static $mapYes = array(
        1 => 'Yes',
        '' => '',
        0 => '',
    );
    
    static $mapYesNo = array(
        1 => 'Yes',
        '' => 'No',
    );
    
    static $mapDaNet = array(
        1 => 'Да',
        '' => 'Нет',
    );
    
    var $map = array();
    
    var $useDefault = false;
    
    // map keys beginning with "/" are treated as Regexes (to ease matching)
    var $useRegexes = false;
    
    protected $default = false;
    
    var $keyDecorator = false;
    
    function setDefault($default) {
        $this->default = $default;
        $this->useDefault = true;
    }
    
    function getDefault() {
        return $this->default;
    }
    
    function apply($value) {
        if ($this->keyDecorator) {
            $key = Ac_Decorator::decorate($this->keyDecorator, $value, $this->keyDecorator);
        } else {
            $key = (string) $value;
        }
        if ($this->useRegexes) {
            foreach ($this->map as $k => $v) {
                if (Ac_Util::isRegex($k) && preg_match($k, $value)) return $v;
                elseif ($k == $value) return $v;
            }
            if ($this->useDefault) return $this->default;
            return $value;
        }
        if (array_key_exists($key, $this->map)) $value = $this->map[$key];
        elseif ($this->useDefault) $value = $this->default;
        return $value;
    }
    
}