<?php

Ae_Dispatcher::loadClass('Ae_Image_Processor');

class Ae_Image_Processor_Gd extends Ae_Image_Processor {
    
    /**
     * @var Resizeimage
     */
    var $_resizer = false;
    
    /**
     * @return Resizeimage
     */
    function & _getResizer() {
        if (!class_exists('Resizeimage', false)) {
            $disp = & Ae_Dispatcher::getInstance();
            require($disp->getVendorDir().'/resizeimage.inc.php');
        }
        if ($this->_resizer === false) $this->_resizer = new Resizeimage();
        return $this->_resizer;
    }
    
    function _doOnSetFile() {
        $r = & $this->_getResizer();
        $r->setImage($this->_filePath);
        if (strlen($e = $r->error())) $this->error = $e;
        $this->_type = $r->imgType;
        $this->_width = $r->imgWidth;
        $this->_height = $r->imgHeight;
    }
    
    function _doGetWidth() {
    }
    
    function _doGetHeight() {
    }
    
    function _doMakeThumbnail($thumbPath, $thumbWidth, $thumbHeight) {
        $r = & $this->_getResizer();
        $r->resize_limitwh($thumbWidth, $thumbHeight, $thumbPath);
        $this->_thumbWidth = $r->newWidth;
        $this->_thumbHeight = $r->newHeight;
    }
    
}

?>
