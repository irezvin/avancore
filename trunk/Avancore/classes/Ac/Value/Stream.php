<?php

class Ac_Value_Stream implements Ac_I_Streamable, Ac_I_WithOutput {
    
    protected $stream = false;
    
    protected $blockSize;
    
    function __construct($stream, $blockSize = 102400) {
        if (!is_string($stream) && !is_resource($stream)) 
            throw Ac_E_InvalidCall::wrongType('stream', $stream, array('string', 'resource'));
        $this->stream = $stream;
        $this->setBlockSize($blockSize);
    }
    
    function __toString() {
        ob_start();
        $this->output();
        return ob_get_clean();
    }
    
    function getBlockSize() {
        return $this->blockSize;
    }
    
    function setBlockSize($blockSize) {
        if (! (int) $blockSize) new Ac_E_InvalidCall("\$blockSize must be a number greater than 0");
        $this->blockSize = (int) $blockSize;
    }
    
    function getStream() {
        if (is_string($this->stream)) return fopen($this->stream, 'r');
            else return $this->stream;
    }
    
    function output($callback = null) {
        $s = $this->getStream();
        if ($callback !== null) {
            while (!feof($s)) 
                call_user_func($callback, fread($s, $this->blockSize));
        } else {
            while (!feof($s)) echo fread($s, $this->blockSize);
        }
    }
    
}