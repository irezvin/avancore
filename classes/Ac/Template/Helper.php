<?php

class Ac_Template_Helper {
    /**
     * @var Ac_Template
     */
    var $template = false;
    
    function __construct($template) {
        $this->template = $template;
    }
}
