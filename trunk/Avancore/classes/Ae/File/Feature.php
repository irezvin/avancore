<?php

/**
 * Ae_File_Feature instances are held if following instances:
 * - in Ae_File_Manager for detection (their $file is null)
 * - in Ae_File
 */
abstract class Ae_File_Feature extends Ae_Autoparams {

    /**
     * @var Ae_File
     */
    protected $file = null;

    protected $id = false;

    protected $allowedExtensions = array();
    
    /**
     * @var Ae_File_Manager
     */
    protected $manager = false;
    

    function hasPublicVars() {
        return true;
    }
    
    protected function setFile(Ae_File $file = null) {
        $this->file = $file;
        if ($file) $this->manager = $file->getManager();
    }

    /**
     * @return Ae_File
     */
    function getFile() {
        return $this->file;
    }
    
    function detect(Ae_File $file) {
        $res = true;
        if ($this->allowedExtensions) {
            $res = false;
            $x = $file->getExtension();
            foreach ($this->allowedExtensions as $ext) {
                if (!strcasecmp($ext, $x)) {
                    $res = true;
                    break;
                }
            }
        }
        return $res;
    }
    
    function getForFile(Ae_File $file) {
        if ($this->file === $file) $res = $this;
        else $res = $this->doCreateForFile($file);
        return $res;
    }
    
    protected function listClonedProps() {
        return array('id');
    }
    
    protected function doCreateForFile(Ae_File $file) {
        
        if (($cp = $this->listClonedProps())) $proto = Ae_Autoparams::getObjectProperty($this, $cp);
            else $proto = array();
            
        $proto['file'] = $file;
        
        $c = get_class($this);
        $res = new $c($proto);
        return $res;
    }
    
    /**
     * TODO: SIMPLIFY!!! Add backlink from feature clone and check it
     * Add tests
     * 
     * @param type $file
     * @return type 
     */
    function alreadyHas($file) {
        $res = false;
        if (strlen($this->id) && !is_numeric($this->id)) {
            if (in_array($this->id, $file->listFeatures())) {
                $res = $file->getFeature($this->id);
            } else $res = false;
        }
        else {
           foreach ($file->getFeatures() as $f) {
               if ($this->isMe($f)) {
                   $res = $f;
                   break;
               }
           }
        }
        return $res;
    }
    
    protected function isMe(Ae_File_Feature $feature) {
        $c = get_class($this);
        return $feature instanceof $c;
    }

    protected function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }    

    function setManager(Ae_File_Manager $manager) {
        $this->manager = $manager;
    }

    /**
     * @return Ae_File_Manager
     */
    function getManager() {
        return $this->manager;
    }    

    function setAllowedExtensions($allowedExtensions) {
        $this->allowedExtensions = $allowedExtensions;
    }

    function getAllowedExtensions() {
        return $this->allowedExtensions;
    }
    
}