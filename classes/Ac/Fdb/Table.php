<?php

class Ac_Fdb_Table extends Ac_Prototyped {

    /**
     * name of the table
     * @var string
     */
    protected $name = false;
    
    /**
     * name of dir with .json files
     * @var string
     */
    protected $dirName = false;

    /**
     * create dir if it doesn't exist
     * @var bool
     */
    protected $createIfNotExists = false;

    /**
     * never cache filelist
     * @var bool
     */
    protected $dontCacheList = false;
    
    /**
     * whether table is open for reading/writing
     * @var bool
     */
    protected $isOpen = false;


    /**
     * Sets name of the table
     * @param string $name
     */
    function setName($name) {
        if ($name !== ($oldName = $this->name)) {
            $this->name = $name;
        }
    }

    /**
     * Returns name of the table
     * @param bool $dontCalc Don't guess table name from dirName if it's empty
     * @return string
     */
    function getName($dontCalc = false) {
        if (!$dontCalc && ($this->name === false) && strlen($this->dirName))
            $this->name = basename($this->dirName);
        return $this->name;
    }    
    
    /**
     * Sets name of dir with .json files
     * @param string $dirName
     */
    function setDirName($dirName) {
        if ($this->dirName !== $dirName) {
            if ($this->isOpen)
                throw new Ac_E_InvalidUsage("Cannot setDirName() when isOpen() - setIsOpen(false) or close() first");
        }
        $this->dirName = $dirName;
    }

    /**
     * Returns name of dir with .json files
     * @return string
     */
    function getDirName() {
        return $this->dirName;
    }

    /**
     * Sets create dir if it doesn't exist
     * @param bool $createIfNotExists
     */
    function setCreateIfNotExists($createIfNotExists) {
        $this->createIfNotExists = $createIfNotExists;
    }

    /**
     * Returns create dir if it doesn't exist
     * @return bool
     */
    function getCreateIfNotExists() {
        return $this->createIfNotExists;
    }

    /**
     * Sets never cache filelist
     * @param bool $dontCacheList
     */
    function setDontCacheList($dontCacheList) {
        $this->dontCacheList = $dontCacheList;
    }

    /**
     * Returns never cache filelist
     * @return bool
     */
    function getDontCacheList() {
        return $this->dontCacheList;
    }
    
    /**
     * Sets whether table is open for reading/writing
     * @param bool $isOpen
     */
    function setIsOpen($isOpen) {
        $this->isOpen = $isOpen;
    }

    /**
     * Returns whether table is open for reading/writing
     * @return bool
     */
    function getIsOpen() {
        return $this->isOpen;
    }    
    
    function close() {
        $this->setIsOpen(false);
    }
    
    function select($where = false, $order = false) {
        
    }
    
    function has($key = false) {
    
    }
    
    function fetch($keys = false) {
        
    }
    
    function getKeys() {
        
    }
    
    function delete(array $record) {
    }
    
    function update(array $record, $many = false) {
        
    }
    
    function insert(array $record, $many = false, $updateOnKeyMatch = false) {
        
    }
    
    protected function doGetExists() {
        
    }
    
    protected function doCreate() {
        
    }
    
    function getExists() {
        return $this->isOpen || $this->doGetExists();
    }
    
    function create() {
        if ($this->getExists()) {
            throw new Ac_E_InvalidUsage("Cannot create() table that already exists; check with getExists() first");
        }
        $this->doCreate();
    }
    
    function drop() {
        
    }
    
}