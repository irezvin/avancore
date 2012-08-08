<?php

class Ac_Content_Text extends Ac_Content_WithCharset implements Ac_I_Streamable {
    
    protected $text = false;
    
    /**
     * @TODO support decorators using Ac_Decorator::decorateStreamable
     */
    function output($callback = null) {
        if ($this->getTextIsStream()) {
            $this->text->output($callback);
        } else {
            if ($callback !== null) call_user_func($callback, $this->text);
                else echo $this->text;
        }
    }
    
    function getTextIsStream() {
        return is_object($this->text) && $this->text instanceof Ac_I_Streamable;
    }
    
    function getEvaluated() {
        if ($this->getTextIsStream()) {
            $res = stream_get_contents($this->text);
        } else {
            $res = ''.$this->text;
        }
        return $res;
    }
    
    function getStream() {
        if ($this->getTextIsStream()) {
            $res = $this->text->getStream();
        } else {
            $res = fopen('data://text/plain;base64,'.base64_encode(''.$this->text), 'r');
        }
        return $res;
    }

    function setText($text) {
        if (is_object($text) && !($text instanceof Ac_I_Streamable || method_exists($text, '__toString'))) {
            throw new Ac_E_InvalidCall("Object value provided as \$text param should either be Ac_I_Streamable or have __toString() method; ".get_class($text)." was provided instead");
        }
        $this->text = $text;
    }

    function getText() {
        return $this->text;
    }    
    
}
