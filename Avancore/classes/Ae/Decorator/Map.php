<?php

class Ae_Decorator_Map extends Ae_Decorator {
    
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
            $key = Ae_Decorator::decorate($this->keyDecorator, $value, $this->keyDecorator);
        } else {
            $key = (string) $value;
        }
        if (array_key_exists($key, $this->map)) $value = $this->map[$key];
        elseif ($this->useDefault) $value = $this->default;
        return $value;
    }
    
}