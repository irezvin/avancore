<?php

class Ac_Image_Processor {
    
    var $_error = false;
    
    var $_filePath = false;
    
    var $_type = false;
    
    var $_width = false;
    
    var $_height = false;
    
    var $_thumbWidth = false;
    
    var $_thumbHeight = false;
    
    var $_thumbPath = false;
    
    function setFile($filePath) {
        $nf = false;
        if ($this->_filePath !== $filePath) {
            $nf = true;
            $this->_reset();
        }
        $this->_filePath = $filePath;
        if ($nf) $this->_doOnSetFile();
    }
    
    function getType() {
        if ($this->_type === false) $this->_type = $this->_doGetType();
        return $this->_type;
    }
    
    function getWidth() {
        if ($this->_width === false) $this->_width = $this->_doGetWidth();
        return $this->_width;
    }
    
    function getHeight() {
        if ($this->_height === false) $this->_height = $this->_doGetHeight();
        return $this->_height;
    }
    
    function makeThumbnail($thumbPath, $thumbWidth = false, $thumbHeight = false) {
        if ($thumbPath !== $this->_thumbPath 
            || $this->_thumbWidth !== $thumbWidth 
            || $this->_thumbHeight !== $thumbHeight) {
                $this->_doMakeThumbnail($thumbPath, $thumbWidth, $thumbHeight);
            }
    }
    
    function getThumbWidth() {
        return $this->_thumbWidth;
    }
    
    function getThumbHeight() {
        return $this->_thumbHeight;
    }
    
    function _reset() {
        $this->error = $this->_type = $this->_width = $this->_height = $this->_thumbPath = $this->_thumbHeight = $this->_thumbWidth = false;
    }
    
    function getError() {
        return $this->_error;
    }
    
    function _doOnSetFile() {
    }
    
    function _doGetWidth() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function doGetHeight() {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    function doMakeThumbnail($thumbPath, $thumbWidth, $thumbHeight) {
        trigger_error("Call to abstract method", E_USER_ERROR);
    }
    
    
    
}

?>