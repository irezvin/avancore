<?php

interface Ae_Cache_Util {
    
    function setCache(Ae_Cache $cache);
    
    function mkDirRecursive($dir);
    
    function deleteRecursive($dir);
    
    function purgeRecursive($dir, $lifetime);
    
    function getStatsRecursive($dir);
    
}