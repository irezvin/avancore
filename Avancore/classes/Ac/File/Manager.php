<?php

class Ac_File_Manager extends Ac_Prototyped {

    protected $features = array();
    
    /**
     * @var Ac_File_Manager
     */
    private static $defaultInstance = null;

    
    function setFeatures(array $features) {
        $this->features = Ac_Prototyped::factoryCollection($features, 'Ac_File_Feature', array('manager' => $this), 'id', true, $dummy, true);
    }
    
    function addFeature(Ac_File_Feature $feature) {
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
     * @return Ac_File_Feature
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
        $id = Ac_File_Feature_MimeInfo::id;
        if (!($f = $this->getFeature($id))) {
            $f = Ac_File_Feature_MimeInfo::createDefault();
            if ($f) $this->addFeature($f);
        }
        return $f;
    }
    
    static function setDefaultInstance(Ac_File_Manager $defaultInstance) {
        self::$defaultInstance = $defaultInstance;
    }

    /**
     * @return Ac_File_Manager
     */
    static function getDefaultInstance() {
        if (!self::$defaultInstance) {
            self::$defaultInstance = new Ac_File_Manager();
        }
        return self::$defaultInstance;
    }    
    
    /**
     * @param type $optionsOrFilename
     * @return Ac_File
     */
    function createFile($optionsOrFilename) {
        if (!is_array($optionsOrFilename)) $optionsOrFilename = array('path' => $optionsOrFilename);
        $optionsOrFilename['manager'] = $this;
        $res = $this->doCreateFile($optionsOrFilename);
        return $res;
    }
    
    protected function doCreateFile(array $optionsOrFilename) {
        $res = Ac_Prototyped::factory($optionsOrFilename, 'Ac_File');
        return $res;
    }
    
    function addFeaturesToFile(Ac_File $file) {
        foreach ($this->features as $feat) {
            if ($feat->detect($file)) $file->addFeature($feat->getForFile($file));
        }
    }
    
}