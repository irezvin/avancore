<?php

class Ac_Template_Helper {
    /**
     * @var Ac_Template
     */
    var $template = false;
    
    function Ac_Template_Helper(& $template) {
        $this->template = & $template;
    }
}
?>