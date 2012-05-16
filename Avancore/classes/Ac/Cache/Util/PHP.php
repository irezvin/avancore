<?php

class Ac_Cache_Util_PHP implements Ac_Cache_Util {
    
    /**
     * @var Ac_Cache
     */
    protected $cache = null;
    
    var $debug = false;
    
    protected $deletedFiles = array();
    
    function debugGetDeletedFiles() {
        return $this->deletedFiles;
    }
    
    function setCache(Ac_Cache $cache) {
        $this->cache = $cache;
    }
    
    function mkDirRecursive($dir) {
        return is_dir($dir)? true : mkdir($dir, 0777, true);
    }
    
    protected function cleanRecursive($dir, $purgeTimeout = false, $depth = 0) {
        if ($this->debug) $this->deletedFiles = array();
        $dirDeleted = false;
        if (!$dir instanceof RecursiveIteratorIterator) $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST); 
        if ($dir->isDir()) {
            $n = 0;
            $path = false;
            foreach ($dir as $file) {
                if ($file->isDir()) {
                    if (!in_array($file->getBasename(), array('.', '..'))) {
                        if (!$n) {
                            if ($this->debug) $this->deletedFiles[] = $file->getPathname();
                            if ($this->debug || @rmdir($file->getPathname())) $n = 0;
                                else $n = 1;
                        } else {
                            $n = 1;
                        } 
                    }
                } else {
                    if ($file->isFile() && (($purgeTimeout === false) || (time() - $file->getMTime()) >= $purgeTimeout)) {
                        if ($this->debug) $this->deletedFiles[] = $file->getPathname();
                        if (!($this->debug || unlink($file->getPathname()))) $n++;
                    } else $n++;
                }
            }
        }
        return $dirDeleted;
    }
    
    function deleteRecursive($dir) {
        return $this->cleanRecursive($dir, false);
    }
    
    function purgeRecursive($dir, $lifetime) {
        return $this->cleanRecursive($dir, $lifetime);
    }
    
    function getStatsRecursive($dir) {
        return array();
    }
    
    
}
