<?php

class Ae_File_Manager extends Ae_Autoparams {

    protected $features = array();
    
    /**
     * @var Ae_File_Manager
     */
    private static $defaultInstance = null;

    
    function setFeatures(array $features) {
        $this->features = Ae_Autoparams::factoryCollection($features, 'Ae_File_Feature', array('manager' => $this), 'id', true, $dummy, true);
    }
    
    function addFeature(Ae_File_Feature $feature) {
        if (is_numeric($id = $feature->getId()) || !strlen($id)) {
            $this->features[] = $feature;
        } else {
            $this->features[$id] = $feature;
        }
    }
    
    /**
     * Returns NULL if feature not found
     * 
     * @param type $id
     * @return Ae_File_Feature
     */
    function getFeature($id) {
        if (isset($this->features[$id])) $res = $this->features[$id];
            else $res = null;
        return $res;
    }
    
    function getFeatures() {
        return $this->features;
    }
    
    function getMimeFeature() {
        $id = Ae_File_Feature_MimeInfo::id;
        if (!($f = $this->getFeature($id))) {
            $f = Ae_File_Feature_MimeInfo::createDefault();
            if ($f) $this->addFeature($f);
        }
        return $f;
    }
    
    static function setDefaultInstance(Ae_File_Manager $defaultInstance) {
        self::$defaultInstance = $defaultInstance;
    }

    /**
     * @return Ae_File_Manager
     */
    static function getDefaultInstance() {
        if (!self::$defaultInstance) {
            self::$defaultInstance = new Ae_File_Manager();
        }
        return self::$defaultInstance;
    }    
    
    /**
     * @param type $optionsOrFilename
     * @return Ae_File
     */
    function createFile($optionsOrFilename) {
        if (!is_array($optionsOrFilename)) $optionsOrFilename = array('path' => $optionsOrFilename);
        $optionsOrFilename['manager'] = $this;
        $res = $this->doCreateFile($optionsOrFilename);
        return $res;
    }
    
    protected function doCreateFile(array $optionsOrFilename) {
        $res = Ae_Autoparams::factory($optionsOrFilename, 'Ae_File');
        return $res;
    }
    
    function addFeaturesToFile(Ae_File $file) {
        foreach ($this->features as $feat) {
            if ($feat->detect($file)) $file->addFeature($feat->getForFile($file));
        }
    }
    
}