<?php

class Ac_File_ThumbGen extends Ac_Prototyped {
    
    protected $imagePath = false;

    protected $imageUrl = false;

    protected $thumbPath = false;

    protected $thumbUrl = false;

    protected $thumbFilenameTemplate = false;

    protected $thumbWidth = 0;

    protected $thumbHeight = 0;
    
    /**
     * @var Ac_File_Manager
     */
    protected $fileManager = false;
    
    /**
     * @var Ac_File_Feature_WebMapping
     */
    protected $webMapper = false;
    
    protected $throwIfFileNotFound = true;
    
    const defaultThumbFilenameTemplate = '{b}-{w}-{h}{x}';

    /**
     * @var Ac_Image_Processor
     */
    protected $imageProcessor = false;

    /**
     * @var bool
     */
    protected $generateThumbnails = true;

    /**
     * Path to images directory (on the disk)
     * @param string $thumbPath
     */
    function setImagePath($imagePath) {
        $this->imagePath = $imagePath;
        $this->updateWebMapper();
    }

    /**
     * @see setImagePath
     * @return string
     */
    function getImagePath() {
        return $this->imagePath;
    }

    /**
     * URL of the images directory (on the web)
     * @param string $imageUrl
     */
    function setImageUrl($imageUrl) {
        $this->imageUrl = $imageUrl;
        $this->updateWebMapper();
    }

    /**
     * @see setImageUrl
     * @return type
     */
    function getImageUrl() {
        return $this->imageUrl;
    }

    /**
     * Path to thumbnails directory (on the disk)
     * @param string $thumbPath
     */
    function setThumbPath($thumbPath) {
        $this->thumbPath = $thumbPath;
        $this->updateWebMapper();
    }

    /**
     * @see setThumbPath
     * @return string|false
     */
    function getThumbPath() {
        return $this->thumbPath;
    }

    /**
     * URL of directory with thumbnails (in the web)
     * If $thumbUrl === false, will be guessed based on the thumbPath and imageUrl
     * @param string|false $thumbUrl
     */
    function setThumbUrl($thumbUrl) {
        $this->thumbUrl = $thumbUrl;
        $this->updateWebMapper();
    }

    /**
     * @see setThumbUrl
     * @return string 
     */
    function getThumbUrl() {
        return $this->thumbUrl;
    }

    /**
     * Themplate for thumbnail filename (relative to thumbPath). May contain subdirectories (which will be attempted to create).
     * Substitute strings: 
     * - {d} - dirname - part starting after $this->imagePath
     * - {b} - basename without extension;
     * - {w} - max width
     * - {h} - max height
     * - {x} - extension with leading "."
     * Defaults to self::defaultThumbFilenameTemplate
     * @see defaultThumbFilenameTemplate
     * @param string $thumbFilenameTemplate
     */
    function setThumbFilenameTemplate($thumbFilenameTemplate) {
        $this->thumbFilenameTemplate = $thumbFilenameTemplate;
    }

    /**
     * @see setThumbFilenameTemplate
     * @return string
     */
    function getThumbFilenameTemplate() {
        return $this->thumbFilenameTemplate;
    }

    /**
     * Default width for thumbnails (0 = no restriction)
     * @param int $thumbWidth
     */
    function setThumbWidth($thumbWidth) {
        $p = 'thumbWidth'; if (!is_numeric($$p) || ((int) $$p < 0)) throw new Exception("\$$p must be a number greater than or equal to 0");
        $this->thumbWidth = (int) $thumbWidth;
    }

    /**
     * @see getThumbWidth
     * @return int
     */
    function getThumbWidth() {
        return $this->thumbWidth;
    }

    /**
     * Default height for thumbnails (0 = no restriction)
     * @param int $thumbHeight
     */
    function setThumbHeight($thumbHeight) {
        $p = 'thumbHeight'; if (!is_numeric($$p) || ((int) $$p < 0)) throw new Exception("\$$p must be a number greater than or equal to 0");
        $this->thumbHeight = (int) $thumbHeight;
    }

    /**
     * @see getThumbHeight
     * @return int
     */
    function getThumbHeight() {
        return $this->thumbHeight;
    }

    /**
     * Image processor to get image size and generate thumbnails. If not provided, default one will be created
     * @param Ac_Image_Processor $imageProcessor
     */
    function setImageProcessor(Ac_Image_Processor $imageProcessor = null) {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @see setImageProcessor
     * @return Ac_Image_Processor
     */
    function getImageProcessor() {
        if (!$this->imageProcessor) {
            $this->imageProcessor = new Ac_Image_Processor_Gd();
        }
        return $this->imageProcessor;
    }

    /**
     * Whether thumbnails will be generated on-disk (and not resized in a browser)
     * Defaults to TRUE
     * 
     * @param bool $generateThumbnails
     */
    function setGenerateThumbnails($generateThumbnails) {
        $this->generateThumbnails = (bool) $generateThumbnails;
    }

    /**
     * @see getGenerateThumbnails
     * @return bool
     */
    function getGenerateThumbnails() {
        return $this->generateThumbnails;
    }

    protected function updateWebMapper() {
        if ($this->webMapper) {
            $this->webMapper->dirToWebMap = array();
            if ($this->imagePath !== false && $this->imageUrl !== false) $this->webMapper->dirToWebMap[$this->imagePath] = $this->imageUrl;
            if ($this->thumbPath !== false && $this->thumbUrl !== false) $this->webMapper->dirToWebMap[$this->thumbPath] = $this->thumbUrl;
        }
    }
    
    /**
     * @return Ac_File_Feature_WebMapping
     */
    function getWebMapper() {
        if ($this->webMapper === false) {
            $this->webMapper = new Ac_File_Feature_WebMapping();
            $this->updateWebMapper();
        }
        return $this->webMapper;
    }
    
    /**
     * @return Ac_File_ThumbGen_Image
     */
    function createImage($relativePath) {
        $res = new Ac_File_ThumbGen_Image($relativePath, $this);
        return $res;
    }

    function setFileManager(Ac_File_Manager $fileManager) {
        $this->fileManager = $fileManager;
    }

    /**
     * @return Ac_File_Manager
     */
    function getFileManager() {
        if ($this->fileManager === false) {
            $this->fileManager = new Ac_File_Manager;
            if (!$this->fileManager->getFeature('image')) {
                $this->fileManager->addFeature(new Ac_File_Feature_Image(array(
                    'id' => 'image',
                    'processor' => $this->getImageProcessor(),
                )));
            }
        }
        return $this->fileManager;
    }

    /**
     * Throw exceptions if Ac_File_ThumbGen_Image instance is created for non-existent file
     * Defaults to TRUE
     * @param bool $throwIfFileNotFound
     */
    function setThrowIfFileNotFound($throwIfFileNotFound) {
        $this->throwIfFileNotFound = (bool) $throwIfFileNotFound;
    }

    function getThrowIfFileNotFound() {
        return $this->throwIfFileNotFound;
    }    
    
}