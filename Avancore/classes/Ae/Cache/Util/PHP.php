<?php

class Ae_Cache_Util_PHP implements Ae_Cache_Util {
    
    /**
     * @var Ae_Cache
     */
    protected $cache = null;
    
    function setCache(Ae_Cache $cache) {
        $this->cache = $cache;
    }
    
    function mkDirRecursive($dir) {
        return is_dir($dir)? true : mkdir($dir, 0777, true);
    }
    
    protected function cleanRecursive($dir, $purgeTimeout = false, $depth = 0) {
        $dirDeleted = false;
        if (!$dir instanceof RecursiveIteratorIterator) $dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir), RecursiveIteratorIterator::CHILD_FIRST); 
        if ($dir->isDir()) {
            ini_set('html_errors', 0);
            $n = 0;
            $path = false;
            foreach ($dir as $file) {
                if ($file->isDir()) {
                    if (($file->getBasename() != '.')) {
                        if (!$n) {
                            if (rmdir($file->getPathname())) $n = 0;
                                else $n = 1;
                        } else {
                            $n = 1;
                        } 
                    }
                } else {
                    if (($purgeTimeout === false) || (time() - $file->getMTime()) >= $purgeTimeout) {
                        if (!unlink($file->getPathname())) $n++;
                    } else $n++;
                }
            }
        }
        return $dirDeleted;
    }
    
    function deleteRecursive($dir) {
        return @$this->cleanRecursive($dir, false);
    }
    
    function purgeRecursive($dir, $lifetime) {
        return @$this->cleanRecursive($dir, $lifetime);
    }
    
    function getStatsRecursive($dir) {
        return array();
    }
    
    
}
