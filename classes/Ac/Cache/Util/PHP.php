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
        if (!is_dir($dir)) return false;
        $rp = realpath($dir).'/';
        $fileIterFlags = FilesystemIterator::KEY_AS_PATHNAME | FilesystemIterator::CURRENT_AS_FILEINFO | FilesystemIterator::SKIP_DOTS;
        $iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, $fileIterFlags), RecursiveIteratorIterator::CHILD_FIRST); 
        $n = 0;
        $path = false;
        foreach ($iter as $file) {
            // file not in parent dir... i.e. via symlink or something
            if (strncmp($file->getRealPath(), $rp, strlen($rp))) continue; 
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
                    if ($file->getBasename() === '.cleanup') continue;
                    if ($this->debug) $this->deletedFiles[] = $file->getPathname();
                    if (!($this->debug || unlink($file->getPathname()))) $n++;
                } else $n++;
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
