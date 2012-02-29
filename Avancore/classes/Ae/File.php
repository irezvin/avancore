<?php

class Ae_File extends Ae_Autoparams {
    
    /**
     * @var Ae_File_Manager
     */
    protected $manager = false;
    
    protected $path = false;
    
    /**
     * @var SplFileInfo
     */
    protected $fileInfo = null;
    
    protected $features = false;

    protected function setPath($path) {
        $this->path = $path;
    }

    function getPath() {
        return $this->path;
    }
    
    /**
     * @return SplFileInfo
     */
    function getFileInfo() {
        if (!is_object($this->fileInfo)) $this->fileInfo = new SplFileInfo($this->getTranslatedPath());
        return $this->fileInfo;
    }
    
    function getExtension() {
        if (preg_match('/(\.[^\.]+)$/', $this->getTranslatedPath(), $matches)) {
            $res = $matches[1];
        } else {
            $res = false;
        }
        return $res;
    }
    
    function exists() {
        return file_exists($this->getTranslatedPath());
    }
    
    function getTranslatedPath() {
        return $this->path;
    }
    
    function addFeature(Ae_File_Feature $feature) {
        $this->initFeatures();
        if ($feature->getFile() !== $this) $feature = $feature->getForFile($this);
        if (is_numeric($id = $feature->getId()) || !strlen($id)) {
            $this->features[] = $feature;
        } else {
            $this->features[$id] = $feature;
        }
    }
    
    function getFeatures() {
        $this->initFeatures();
        return $this->features;
    }
    
    function listFeatures() {
        $this->initFeatures();
        return array_keys($this->features);
    }
    
    protected function initFeatures() {
        if ($this->features === false) {
            $this->features = array();
            $this->getManager()->addFeaturesToFile($this);
        }
    }
    
    /**
     * Returns NULL if feature not found
     * 
     * @param type $id
     * @return Ae_File_Feature
     */
    function getFeature($id) {
        $this->initFeatures();
        if (isset($this->features[$id])) $res = $this->features[$id];
            else $res = null;
        return $res;
    }

    protected function setManager(Ae_File_Manager $manager) {
        $this->manager = $manager;
    }

    /**
     * @return Ae_File_Manager
     */
    function getManager() {
        if ($this->manager === false) {
            $this->manager = Ae_File_Manager::getDefaultInstance();
        }
        return $this->manager;
    }
    
    function getMTime() {
        return $this->getFileInfo()->getMTime();
    }
    
}