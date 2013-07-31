<?php

class Ac_Result_Placeholder_Template extends Ac_Prototyped implements Ac_I_Result_PlaceholderTemplate {
    
    protected $glue = "";
    
    protected $prefix = "";

    protected $suffix = "";
    
    protected $textIfEmpty = "";

    
    
    function setGlue($glue) {
        $this->glue = $glue;
    }

    function getGlue() {
        return $this->glue;
    }    
    
    function setPrefix($prefix) {
        $this->prefix = $prefix;
    }

    function getPrefix() {
        return $this->prefix;
    }

    function setSuffix($suffix) {
        $this->suffix = $suffix;
    }

    function getSuffix() {
        return $this->suffix;
    }
    
    function setTextIfEmpty($textIfEmpty) {
        $this->textIfEmpty = $textIfEmpty;
    }

    function getTextIfEmpty() {
        return $this->textIfEmpty;
    }
    
    protected function getStrings(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        return $placeholder->getItemsForWrite($writer);
    }
    
    function writePlaceholder(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        $strings = $this->getStrings($placeholder, $writer);
        if ($strings) {
            echo $this->prefix.implode($this->glue, $strings).$this->suffix;
        } else {
            echo $this->textIfEmpty;
        }
    }
    
    
    
}