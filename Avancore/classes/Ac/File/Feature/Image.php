<?php

class Ac_File_Feature_Image extends Ac_File_Feature {
    
    var $allowedExtensions = array(
        '.gif', '.jpg', '.png', '.jpeg',
    );
    
    var $thumbsDir = false;
    
    var $thumbsHashSeed = '';
    
    /**
     * @var Ac_Image_Processor
     */
    protected $processor = false;
    
    /**
     * Example: array('small' => array(400, 400), 'medium' => array(500, 500), 'large' => array(800, 800))
     * @var array
     */
    var $thumbSizes = array();
    
    
    protected function listClonedProps() {
        return array_merge(parent::listClonedProps(), array('allowedExtensions', 'thumbsDir', 'processor', 'thumbSizes'));
    }
    
    function getWidth() {
        $this->getProcessor()->setFile($this->file->getTranslatedPath());
        return $this->getProcessor()->getWidth();
    }
    
    function getHeight() {
        $this->getProcessor()->setFile($this->file->getTranslatedPath());
        return $this->getProcessor()->getHeight();
    }
    
    /**
     * @return string Path to the thumbnail file
     * @param string|array $size Key in $this->thumbSizes array or array(width, height)
     * @param bool $generate Generate thumbnail if necessary (if no file exists or it is older than the original)
     * @param bool $force Force thumbnail refresh
     * @param string $path Override path value
     */
    function getThumbnail($size, $generate = true, $force = false, $path = false) {
        if (!is_array($size)) {
            if (isset($this->thumbSizes[$size])) $size = $this->thumbSizes[$size];
                else throw new Exception("No such thumbnail size: {$size}");
        }
        list ($width, $height) = $size;
        $tWidth = (int) $width;
        $tHeight = (int) $height;
        if ($tWidth <= 0) throw new Exception("Invalid width value: ".$width);
        if ($tHeight <= 0) throw new Exception("Invalid height value: ".$height);
        if ($path === false) $path = md5($this->file->getTranslatedPath().'-'.$tWidth.'-'.$tHeight.$this->thumbsHashSeed).$this->file->getExtension();
        if (strlen($this->thumbsDir)) $path = rtrim($this->thumbsDir,'/').'/'.$path;
        if ($generate) {
            if (!$force) {
                if (!is_file($path) || filemtime($path) < $this->file->getMTime()) {
                    $this->writeThumbnail($path, $width, $height);
                }
            }
        }
        return $path;
    }
    
    function writeThumbnail($thumbPath, $width, $height) {
        $this->getProcessor()->setFile($this->file->getTranslatedPath());
        $this->getProcessor()->makeThumbnail($thumbPath, $width, $height);
    }

    function setProcessor(Ac_Image_Processor $processor = null) {
        $this->processor = $processor;
    }

    /**
     * @return Ac_Image_Processor
     */
    function getProcessor() {
        if (!$this->processor) {
            $this->processor = new Ac_Image_Processor_Gd();
        }
        return $this->processor;
    }    
    
}