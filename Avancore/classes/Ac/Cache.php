<?php

class Ac_Cache extends Ac_Cache_Abstract {
    
    protected $cacheDir = false;
    
    var $ignoreUserAbort = true;
    
    var $useLocking = true;
    
    var $globalPrefix = 'ae_';
    
    var $autoCleanup = true;
    
    /**
     * Number of subdirectories that cache files reside in (subdirectory names are created by first letters of hash.
     * For example cache value with ID '12a45b' and $subdirs = 3 will be stored in {$cacheDir}{$group}/1/2/a/12a45b
     * 
     * @var false|int
     */
    var $subdirs = 2;
    
    /**
     * Means that group name will specify cache subdirectory instead of prepending cache ID (files will be stored in {$cacheDir}/{$group}/{$id} instead of {$cacheDir}/{$group}_{$id}
     * Works automatically if $subdirs > 0
     * 
     * @var bool
     */
    var $groupsToDirs = true;
    
    /**
     * Cache will be cleaned every $cleanupInterval seconds
     * @var int
     */
    var $cleanupInterval = 3600;
    
    protected $triedCleanup = false;
    
    /**
     * @var Ac_Cache_Util
     */
    protected $util = null;
    
    function hasPublicVars() {
        return true;
    }

    /**
     * @return Ac_Cache_Accessor
     */
    function accessor($id, $group = false) {
        return new Ac_Cache_Accessor($id, $this, $group);
    }

    static function getDefaultCacheDir() {
        if (Ac_Application::getDefaultInstance()) {
            $ac = Ac_Application::getDefaultInstance();
            $res = $ac->getAdapter()->getVarCachePath();
        } else {
            if (defined('_DEPLOY_CACHE_PATH') && strlen(_DEPLOY_CACHE_PATH)) $res = _DEPLOY_CACHE_PATH;
            $res = false;
        }
        return $res;
    }

    static function getDefaultCacheLifetime() {
        if (Ac_Application::getDefaultInstance()) {
            $ac = Ac_Application::getDefaultInstance();
            $res = $ac->getAdapter()->getConfigValue('cacheLifetime', false);
        } else {
            $res = false;
        }
        return $res;
    }
    
    static function getDefaultPrototype() {
        $res = array();
        if (($cd = self::getDefaultCacheDir()) !== false) {
            $res = array(
                'class' => 'Ac_Cache',
                'cacheDir' => $cd,
            );
            if (($l = (int) self::getDefaultCacheLifeTime())) $res['lifetime'] = $l;
        }
        return $res;
    }
    
    function __construct(array $options = array()) {
        $this->cacheDir = self::getDefaultCacheDir();
        parent::__construct($options);
    }
    
    function setCacheDir($cacheDir) {
        if ($cacheDir !== ($oldCacheDir = $this->cacheDir)) {
            $this->cacheDir = $cacheDir;
        }
    }

    function getCacheDir() {
        return $this->cacheDir;
    }
    
    /**
     * @return array('group' => array('nFiles' => int, 'totalSize' => int)
     */
    function calculateGroupStats() {
        $res = $this->getUtil()->getStatsRecursive($this->cacheDir);
        return $res;
    }

    function setUtil($util = 'Ac_Cache_Util_PHP') {
        $this->util = Ac_Prototyped::factory($util, 'Ac_Cache_Util');
        $this->util->setCache($this);
    }
    
    /**
     * @return Ac_Cache_Util
     */
    function getUtil() {
        if (!$this->util) $this->setUtil();
        return $this->util;
    }
    
    protected function implHas($id, $group, & $howOld, $lifetime, & $filename) {
        $res = false;
        $filename = $this->getFilename($id, $group);
        if (file_exists($filename)) {
            $howOld = time() - filemtime($filename);
            if (!$lifetime || $howOld <= $lifetime) $res = true;
        }
        return $res;
    }
    
    protected function implGet($id, $group, $default, $evenOld) {
        
        if ($this->autoCleanup && !$this->triedCleanup) $this->tryCleanup();
        
        if ($this->has($id, $group, $howOld, $evenOld? $this->lifetime : 0, $filename)) {
            $res = file_get_contents($filename);
        } else {
            $res = $default;
        }
        return $res;
    }
    
    function getFilename($id, $group = false) {
        
        $fn = $this->cacheDir.DIRECTORY_SEPARATOR.$this->globalPrefix;
        
        if (!is_scalar($id)) $id = md5(serialize(id));
            else $id = (string) $id;
        
        if ($group === false) $group = $this->defaultGroup;
        
        if ($this->subdirs || $this->groupsToDirs) $fn .= $group.DIRECTORY_SEPARATOR;
            else $fn .= $group.'_';
            
        $sd = min($this->subdirs, strlen($id));
        for ($i = 0; $i < $sd; $i++) {
            $fn .= substr($id, $i, 1).DIRECTORY_SEPARATOR;
        }

        $id = str_replace('/\\', '-', $id);
            
        $fn .= $id;
        
        return $fn;
    }
    
    function implPut($id, $content, $group) {
        
        $res = false;

        if (!$this->triedCleanup) $this->tryCleanup();

        $fn = $this->getFilename($id, $group);

        $this->getUtil()->mkDirRecursive(dirname($fn));

        if ($this->useLocking) {
            if (is_file($lock = $fn.'.lock')) return false;
            else touch($lock); 
        }

        if ($this->ignoreUserAbort) {
            $ia = ini_get('ignore_user_abort');
            if (!$ia) ini_set('ignore_user_abort', true);
        }

        if (file_put_contents($fn, $content)) 
            $res = $fn;
        else
            $res = false;

        if ($this->ignoreUserAbort && !$ia) ini_set('ignore_user_abort', false);
        if ($this->useLocking) unlink($lock);
            
        return $res;
    }
    
    protected function implDelete($id, $group) {
        
        $res = false;
        if (!$this->triedCleanup) $this->tryCleanup();

        $fn = $this->getFilename($id, $group);
        if (is_file($fn)) {
            $res = unlink($fn);
        }
        return $res;
    }

    protected function getCleanupFileName() {
        $fn = $this->cacheDir.DIRECTORY_SEPARATOR.$this->globalPrefix.'.cleanup';
        return $fn;
    }
    
    function hasToCleanup() {
        $res = false;
        if (is_dir($this->cacheDir)) {
            $fn = $this->getCleanupFileName();
            if (is_file($fn)) { 
                $t = filemtime($fn);
                if ((time() - $t) >= $this->cleanupInterval) $res = $fn;   
            } else touch($fn);
        }
        return $res;
    }

    function cleanup() {
        if (is_dir($this->cacheDir)) {
            $this->getUtil()->purgeRecursive($this->cacheDir, $this->lifetime);
            touch($this->getCleanupFileName());
        }
    }
    
    function tryCleanup($force = false) {
        $this->triedCleanup = true;
        if (($fn = $this->hasToCleanup()) || $force) {
            $this->getUtil()->purgeRecursive($this->cacheDir, $this->lifetime);
            if (strlen($fn)) touch($fn);
        }
    }
    
    function deleteAll() {
        $this->getUtil()->deleteRecursive($this->cacheDir);
    }
    
}
