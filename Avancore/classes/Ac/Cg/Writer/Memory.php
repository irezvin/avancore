<?php

class Ac_Cg_Writer_Memory extends Ac_Cg_Writer_Abstract {

    protected $output = array();
    
    function begin() {
        parent::begin();
        $this->output = array();
    }
    
    function getOutput() {
        return $this->output;
    }

    protected function doWriteContent($reativePath, $content) {
        $this->output[$reativePath] = $content;
    }

}
