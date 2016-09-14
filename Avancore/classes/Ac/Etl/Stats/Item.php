<?php

class Ac_Etl_Stats_Item {
    
    protected $id = false;

    protected $caption = false;

    protected $value = false;

    function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }

    function setCaption($caption) {
        $this->caption = $caption;
    }

    function getCaption() {
        if ($this->caption === false) return $this->id;
        return $this->caption;
    }

    function setValue($value) {
        if (!is_int($value)) throw new Exception("\$value must be a number");
        $this->value = $value;
    }

    function getValue() {
        return $this->value;
    }    
    
    function add($value) {
        $this->value += $value;
    }
    
    function reset() {
        $this->value = 0;
    }
    
}