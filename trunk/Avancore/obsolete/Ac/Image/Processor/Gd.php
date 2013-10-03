<?php

class Ac_Image_Processor_Gd extends Ac_Image_Processor {
    
    /**
     * @var Resizeimage
     */
    var $_resizer = false;
    
    /**
     * {w} is width, {h} is height, {s} is src path, {d} is dest path
     * @var string
     */
    var $magickCommand = false;    
    
    /**
     * @return Resizeimage
     */
    function _getResizer() {
        if (!class_exists('Resizeimage', false)) {
            $disp = Ac_Dispatcher::getInstance();
            require($disp->getVendorDir().'/resizeimage.inc.php');
        }
        if ($this->_resizer === false) $this->_resizer = new Resizeimage();
        return $this->_resizer;
    }
    
    function _doOnSetFile() {
        $r = $this->_getResizer();
        $r->setImage($this->_filePath);
        if (strlen($e = $r->error())) $this->_error = $e;
        $this->_type = $r->imgType;
        $this->_width = $r->imgWidth;
        $this->_height = $r->imgHeight;
    }
    
    function _doGetWidth() {
    }
    
    function _doGetHeight() {
    }
    
    function _doMakeThumbnail($thumbPath, $thumbWidth, $thumbHeight) {
        
        if (strlen($this->magickCommand)) {
            
            $command = strtr($this->magickCommand, array(
                '{w}' => escapeshellarg($thumbWidth),
                '{h}' => escapeshellarg($thumbHeight),
                '{s}' => escapeshellarg($this->_filePath),
                '{d}' => escapeshellarg($thumbPath)
            ));
            exec($command, $output, $return);
            
        } else  {
            $r = $this->_getResizer();
            $r->resize_limitwh($thumbWidth, $thumbHeight, $thumbPath);
            $this->_thumbWidth = $r->newWidth;
            $this->_thumbHeight = $r->newHeight;
        }
    }
    
}

