<?php

class Ac_Decorator_Map extends Ac_Decorator {
    
    static $mapYes = array(
        1 => 'Yes',
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
        if (array_key_exists($key, $this->map)) $value = $this->map[$key];
        elseif ($this->useDefault) $value = $this->default;
        return $value;
    }
    
}