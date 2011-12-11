<?php

class Ae_Js_Call implements Ae_I_Jsable {
    
    var $funcId = false;
    
    var $params = array();
    
    var $withNew = false;
    
    function __construct($funcId, $params = array(), $withNew = false) {
        $this->funcId = $funcId;
        $this->params = is_array($params)? $params : array($params);
        $this->withNew = $withNew;
    }
    
    function toJs(Ae_Js $js, $indent = 0, $indentStep = 4, $newLines = true) {
        $p = array();
        foreach ($this->params as $param) $p[] = $js->toJs($param, $indent + $indentStep, $indentStep, $newLines);
        $res = '';
        if ($this->withNew && strncasecmp($res, 'new ', 4)) $res .= 'new ';
        $funcId = $this->funcId;
        if (is_array($funcId)) $funcId = implode('.', $funcId);
        $res .= $funcId.' ('.implode(', ', $p).')';
        return $res;
    }
    
    function __toString() {
        $js = new Ae_Js();
        return $js->toJs($this);
    }
    
}

?>