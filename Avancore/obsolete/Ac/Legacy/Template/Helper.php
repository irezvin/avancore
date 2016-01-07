<?php

class Ac_Legacy_Template_Helper {
    /**
     * @var Ac_Legacy_Template
     */
    var $template = false;
    
    function __construct($template) {
        $this->template = $template;
    }
}
