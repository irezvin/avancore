<?php

class Ac_Response implements Ac_I_WithOutput {
    
    /**
     * @var Ac_Content
     */
    protected $content = false;

    function setContent(Ac_Content $content) {
        $this->content = $content;
    }

    /**
     * @return Ac_Content
     */
    function getContent() {
        return $this->content;
    }    
    
    function output($callback = null) {
        $this->content->output($callback);
    }
    
}