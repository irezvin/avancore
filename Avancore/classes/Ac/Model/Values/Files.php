<?php

/**
 * Implements values list from the records
 */
class Ac_Model_Values_Files extends Ac_Model_Values {

    /**
     * name of directory to search files in
     * @var string
     */
    protected $dirName = false;

    /**
     * callback to retrieve dirName (set first val to TRUE to use $this->data)
     */
    protected $dirNameCallback = false;

    /**
     * regex to filter file names
     * @var string
     */
    protected $fileNameRegex = false;

    /**
     * regex to filter dir names during recursive traversal
     * @var string
     */
    protected $dirNameRegex = false;

    /**
     * whether to remove extensions from filenames
     * @var bool
     */
    protected $stripExtensions = false;

    /**
     * whether to search files recursively
     * @var bool
     */
    protected $recursive = false;
    
    /**
     * whether to include directories in the list (those that match fileRegex)
     * @var bool
     */
    protected $includeDirs = false;
    
    /**
     * true to asort() result or callback to uasort() it
     */
    protected $sort = false;
    
    protected function doDefaultGetValueList() {
        $dirName = $this->getDirName(true);
        $baseDir = realpath($dirName).'/';
        if (DIRECTORY_SEPARATOR == '\\') $baseDir = str_replace(DIRECTORY_SEPARATOR, "/", $baseDir);
        $files = Ac_Util::listDirContents($dirName, $this->recursive, array(), $this->fileNameRegex, $this->dirNameRegex, $this->includeDirs);
        
        if ($this->stripExtensions) foreach ($files as $k => $v) {
            $pi = pathinfo($v);
            $files[$k] = basename($pi['basename'], strlen($pi['extension'])? '.'.$pi['extension'] : '');
            if (strlen($pi['dirname'])) $files[$k] = $pi['dirname'] .'/'.$files[$k];
        }
        
        $res = array();
        foreach ($files as $k => $v) {
            if (!strncmp($v, $baseDir, $l = strlen($baseDir))) $v = substr($v, $l);
            $res[$v] = $v;
        }

        if ($this->sort) {
            if ($this->sort === true) asort($res);
            else {
                if (is_array($this->sort)) {
                    if (isset($this->sort[0]) && $this->sort[0] === true) $this->sort[0] = $this->data;
                }
                uasort($res, $this->sort);
            }
        }
        
        return $res;
        
    }

    /**
     * Sets name of directory to search files in.
     * Will obsoletize dirNameCallback property if non-empty.
     * 
     * @see Ac_Model_Values_Files::setDirNameCallback()
     * @param string $dirName
     */
    function setDirName($dirName) {
        $this->dirName = $dirName;
        if (strlen($this->dirName)) $this->dirNameCallback = false;
    }

    /**
     * Returns name of directory to search files in
     * @param bool $calculated Return actual directory name if dirnameCallback is provided
     * @return string
     */
    function getDirName($calculated = false) {
        $res = $this->dirName;
        if ($calculated) {
            if ($res === false && $this->dirNameCallback) {
                $cb = $this->dirNameCallback;
                if (is_array($cb) && $cb[0] === true) $cb[0] = $this->data;
                $res = call_user_func($cb, $this);
            }
        }
        return $res;
    }

    /**
     * Sets callback to retrieve dirName (set first val to TRUE to use $this->data)
     * Will obsoletize dirName property if non-empty.
     * 
     * @see Ac_Model_Values_Files::setDirName()
     */
    function setDirNameCallback($dirNameCallback) {
        $this->dirNameCallback = $dirNameCallback;
        if ($this->dirNameCallback) $this->dirName = false;
    }

    /**
     * Returns callback to retrieve dirName (set first val to TRUE to use $this->data)
     */
    function getDirNameCallback() {
        return $this->dirNameCallback;
    }

    /**
     * Sets regex to filter file names
     * @param string $fileNameRegex
     */
    function setFileNameRegex($fileNameRegex) {
        $this->fileNameRegex = $fileNameRegex;
    }

    /**
     * Returns regex to filter file names
     * @return string
     */
    function getFileNameRegex() {
        return $this->fileNameRegex;
    }

    /**
     * Sets regex to filter dir names during recursive traversal
     * @param string $dirNameRegex
     */
    function setDirNameRegex($dirNameRegex) {
        $this->dirNameRegex = $dirNameRegex;
    }

    /**
     * Returns regex to filter dir names during recursive traversal
     * @return string
     */
    function getDirNameRegex() {
        return $this->dirNameRegex;
    }

    /**
     * Sets whether to remove extensions from filenames
     * @param bool $stripExtensions
     */
    function setStripExtensions($stripExtensions) {
        $this->stripExtensions = $stripExtensions;
    }

    /**
     * Returns whether to remove extensions from filenames
     * @return bool
     */
    function getStripExtensions() {
        return $this->stripExtensions;
    }

    /**
     * Sets whether to search files recursively
     * @param bool $recursive
     */
    function setRecursive($recursive) {
        $this->recursive = $recursive;
    }

    /**
     * Returns whether to search files recursively
     * @return bool
     */
    function getRecursive() {
        return $this->recursive;
    }    

    /**
     * Sets whether to include directories in the list (those that match fileRegex)
     * @param bool $includeDirs
     */
    function setIncludeDirs($includeDirs) {
        $this->includeDirs = $includeDirs;
    }

    /**
     * Returns whether to include directories in the list (those that match fileRegex)
     * @return bool
     */
    function getIncludeDirs() {
        return $this->includeDirs;
    }

    /**
     * Sets true to asort() result or callback to uasort() it
     */
    function setSort($sort) {
        $this->sort = $sort;
    }

    /**
     * Returns true to asort() result or callback to uasort() it
     */
    function getSort() {
        return $this->sort;
    }    
    
}

