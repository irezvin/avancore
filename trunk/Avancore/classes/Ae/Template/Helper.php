<?php

class Ae_Template_Helper {
    /**
     * @var Ae_Template
     */
    var $template = false;
    
    function Ae_Template_Helper(& $template) {
        $this->template = & $template;
    }
}
?>