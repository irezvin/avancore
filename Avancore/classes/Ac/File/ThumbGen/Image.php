<?php

class Ac_File_ThumbGen_Image {

    /**
     * @var string
     */
    protected $imagePath = false;
    
    /**
     * @var Ac_File_ThumbGen
     */
    protected $thumbGen = false;

    /**
     * @var Ac_File
     */
    protected $file = false;
    
    /**
     * @param type $imagePath
     * @param Ac_File_ThumbGen $thumbGen
     */
    function __construct($imagePath, Ac_File_ThumbGen $thumbGen) {
        $this->imagePath = $imagePath;
        $this->thumbGen = $thumbGen;
        $this->file = new Ac_File(array(
            'manager' => $this->thumbGen->getFileManager(),
            'path' => rtrim($this->thumbGen->getImagePath(), '/').'/'.$imagePath
        ));
        if ($this->thumbGen->getThrowIfFileNotFound()) {
            if (!$this->file->exists()) throw new Exception("Image file not found: {$imagePath} (full path: '{".$this->file->getPath().'})');
        }
    }

    protected function createThumbPath($width, $height) {
        $ext = $this->file->getExtension();
        $repl = array(
            '{d}' => dirname($this->imagePath),
            '{b}' => basename($this->imagePath, $ext),
            '{w}' => $width,
            '{h}' => $height,
            '{x}' => $ext,
        );
        $res = strtr($this->thumbGen->getThumbFilenameTemplate(), $repl);
        return $res;
    }
    
    function getThumbPath($width = false, $height = false) {
        if ($width === false) $width = $this->thumbGen->getThumbWidth();
        if ($height === false) $height = $this->thumbGen->getThumbHeight();
        $thumbPath = rtrim($this->thumbGen->getThumbPath(), '/').'/'.$this->createThumbPath($width, $height);
        if (!is_file($thumbPath)) {
            if ($this->thumbGen->getGenerateThumbnails()) {
                if ($this->file->exists()) {
                    $feat = $this->file->getFeature('image');
                    $thumbSize = $feat->guessThumbSize($width, $height);
                    $thumbDir = dirname($thumbPath);
                    if (!is_dir($thumbDir)) {
                        if (!mkdir ($thumbDir, 0777, true)) {
                            Ac_Debug::savageMode();
                            var_dump($thumbDir, $thumbPath);
                            throw new Exception("Cannot create thumbnail direcctory '{$thumbDir}'");
                        }
                    }
                    $this->file->getFeature('image')->getThumbnail(array($thumbSize['width'], $thumbSize['height']), true, false, $thumbPath);
                    clearstatcache();
                    if (!is_file($thumbPath)) {
                        throw new Exception("Cannot create thumbnail; check if directory '{$thumbDir}' is writeable");
                    }
                    $res = $thumbPath;
                } else {
                    if ($this->thumbGen->getThrowIfFileNotFound()) {
                        throw new Exception("Image file not found: {$imagePath} (full path: '{".$this->file->getPath().'})');
                    }
                    $res = false;
                }
            } else {
                $res = $this->file->getPath();
            }
        } else {
            $res = $thumbPath;
        }
        return $res;        
    }
    
    function getThumbUrl($width = false, $height = false) {
        $path = $this->getThumbPath($width, $height);
        if ($path !== false) {
            $url = $this->thumbGen->getWebMapper()->getFileUrl($path);
        }
        return $url;
    }
    
    function getImagePath($full = false) {
        if ($full) return $this->file->getPath();
            else return $this->imagePath;
    }
    
    function getImageUrl() {
        $url = $this->thumbGen->getWebMapper()->getFileUrl($this->getImagePath(true));
        return $url;
    }
    
    function getImageTag($isThumb = true, array $extraAttribs = array(), $maxWidth = false, $maxHeight = false) {
        if (!$isThumb) {
            $url = $this->getImageUrl();
        } else {
            $url = $this->getThumbUrl($maxWidth, $maxHeight);
        }
        $attribs = $this->file->getFeature('image')->guessThumbSize($maxWidth, $maxHeight);
        $attribs['src'] = $url;
        $attribs = array_merge($attribs, $extraAttribs);
        $res = Ac_Util::mkElement('img', false, $attribs);
        return $res;
    }
    
}