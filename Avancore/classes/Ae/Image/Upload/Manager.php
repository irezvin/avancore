<?php

class Ae_Image_Upload_Manager extends Ae_Upload_Manager {

    var $_uploadClass = 'Ae_Image_Upload';
    
    var $_withThumbnails = true;
    
    var $_thumbLink = false;
    
    var $_thumbWidth = 200;
    
    var $_thumbHeight = 200;
    
    var $_imageProcessorConfig = array(
        'class' => 'Ae_Image_Processor_Gd',
    );
    
    /**
     * @var Ae_Image_Processor
     */
    var $_imageProcessor = false;
    
    function _setImageProcessorConfig($config) {
        if (!is_array($ipc)) trigger_error('$config must be an array', E_USER_ERROR);
        $this->_imageProcessorConfig = $config;
        $this->_imageProcessor = false;
    }
    
    function getImageProcessorConfig() {
        return $this->_imageProcessorConfig;
    }
    
    /**
     * @return Hw_Image_Processor
     */
    function getImageProcessor() {
        if ($this->_imageProcessor === false) {
            $this->_imageProcessor = & Ae_Util::factoryWithOptions($this->_imageProcessorConfig, 'Ae_Image_Processor_Gd');
        }
        return $this->_imageProcessor;
    }
    
    /**
     * @param Ae_Image_Processor $imageProcesor
     */
    function setImageProcessor(& $imageProcesor) {
        $this->_imageProcessor = & $imageProcesor;
    }
    
    function setThumbLink($v) {
        $this->_thumbLink = $v;
    }
    
    function getThumbLink() {
        return $this->_thumbLink;
    }
    
    function setThumbWidth($v) {
        $this->_thumbWidth = $v;
    }
    
    function getThumbWidth() {
        return $this->_thumbWidth;
    }
    
    function setThumbHeight($v) {
        $this->_thumbHeight = $v;
    }
    
    function getThumbHeight() {
        return $this->_thumbHeight;
    }
    
    function setWithThumbnails($withThumbnails) {
        $this->withThumbnails = $withThumbnails;
    }
    
    function getWithThumbnails() {
        return $this->_withThumbnails;
    }
    
}