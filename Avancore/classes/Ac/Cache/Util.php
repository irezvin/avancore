<?php

interface Ac_Cache_Util {
    
    function setCache(Ac_Cache $cache);
    
    function mkDirRecursive($dir);
    
    function deleteRecursive($dir);
    
    function purgeRecursive($dir, $lifetime);
    
    function getStatsRecursive($dir);
    
}