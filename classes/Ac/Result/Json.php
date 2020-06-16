<?php

class Ac_Result_Json extends Ac_Result_Http implements Ac_I_Jsable {
    
    protected $data = array();
    
    protected $contentType = 'text/json';
    
    protected $strict = true;
    
    function setData($data) {
        $this->data = $data;
    }

    function getData() {
        return $this->data;
    }    

    function merge(array $overrideWith) {
        Ac_Util::ms($data, $this->data);
    }
    
    function getContent() {
        if ($this->strict) return json_encode($this->data);
        $v = new Ac_Js_Val($this);
        return $v->__toString();
    }
    
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
        return json_encode($this->data);
    }
    
}