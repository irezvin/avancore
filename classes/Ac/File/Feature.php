<?php

/**
 * Ac_File_Feature instances are held if following instances:
 * - in Ac_File_Manager for detection (their $file is null)
 * - in Ac_File
 */
abstract class Ac_File_Feature extends Ac_Prototyped {

    /**
     * @var Ac_File
     */
    protected $file = null;

    protected $id = false;

    protected $allowedExtensions = array();
    
    /**
     * @var Ac_File_Manager
     */
    protected $manager = false;
    

    function hasPublicVars() {
        return true;
    }
    
    protected function setFile(Ac_File $file = null) {
        $this->file = $file;
        if ($file) $this->manager = $file->getManager();
    }

    /**
     * @return Ac_File
     */
    function getFile() {
        return $this->file;
    }
    
    function detect(Ac_File $file) {
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
    
    function getForFile(Ac_File $file) {
        if ($this->file === $file) $res = $this;
        else $res = $this->doCreateForFile($file);
        return $res;
    }
    
    protected function listClonedProps() {
        return array('id');
    }
    
    protected function doCreateForFile(Ac_File $file) {
        
        if (($cp = $this->listClonedProps())) $proto = Ac_Accessor::getObjectProperty($this, $cp);
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
    
    protected function isMe(Ac_File_Feature $feature) {
        $c = get_class($this);
        return $feature instanceof $c;
    }

    protected function setId($id) {
        $this->id = $id;
    }

    function getId() {
        return $this->id;
    }    

    function setManager(Ac_File_Manager $manager) {
        $this->manager = $manager;
    }

    /**
     * @return Ac_File_Manager
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