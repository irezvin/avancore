<?php

class Ae_Cache {
    
    protected $cacheDir = false;
    
    var $enabled = true;
    
    var $lifetime = 3600;
    
    var $ignoreUserAbort = true;
    
    var $useLocking = true;
    
    var $globalPrefix = 'ae_';
    
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
    
    var $defaultGroup = 'default';
    
    /**
     * Cache will be cleaned every $cleanupInterval seconds
     * @var int
     */
    var $cleanupInterval = 3600;
    
    protected $triedCleanup = false;
    
    /**
     * @var Ae_Cache_Util
     */
    protected $util = null;

    static function getDefaultPrototype() {
        $res = array();
        if (class_exists('Ae_Dispatcher') && Ae_Dispatcher::hasInstance()) {
            $d = Ae_Dispatcher::getInstance();
            $res = array(
                'class' => 'Ae_Cache',
            	'cacheDir' => $d->config->cachePath . '/aeCache',
            );
            if (($l = (int) $d->config->getValue('cacheLifeTime'))) $res['lifetime'] = $l;
        }
        return $res;
    }
    
    function __construct() {
        if (class_exists('Ae_Dispatcher', false) && Ae_Dispatcher::hasInstance()) $this->cacheDir = Ae_Dispatcher::getInstance()->getCacheDir();
        elseif (defined('_DEPLOY_CACHE_PATH') && strlen(_DEPLOY_CACHE_PATH)) $this->cacheDir = _DEPLOY_CACHE_PATH;
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

    function setUtil($util = 'Ae_Cache_Util_PHP') {
        $this->util = Ae_Autoparams::factory($util, 'Ae_Cache_Util');
        $this->util->setCache($this);
    }
    
    /**
     * @return Ae_Cache_Util
     */
    function getUtil() {
        if (!$this->util) $this->setUtil();
        return $this->util;
    }
    
	function has($id, $group = false, & $howOld = null, $lifetime = false, & $filename = null) {
		$res = false;
		$howOld = false;
		if ($lifetime === false) $lifetime = $this->lifetime;
		if ($this->enabled) {
			$filename = $this->getFilename($id, $group);
			if (file_exists($filename)) {
				$howOld = time() - filemtime($filename);
				if (!$lifetime || $howOld <= $lifetime) $res = true;
			}
		}
		return $res;
	}
	
	function get($id, $group = false, $default = null, $evenOld = false) {
	    
	    if (!$this->triedCleanup) $this->tryCleanup();
	    
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
	
	function put($id, $content, $group = false) {
        
        if ($this->enabled) {

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
            
        } else {
            $res = false;
        }
			
		return $res;
	}
	
	function delete($id, $group = false) {
	    
        if ($this->enabled) {

            if (!$this->triedCleanup) $this->tryCleanup();

            $fn = $this->getFilename($id, $group);
            if (is_file($fn)) {
                $res = unlink($fn);
            } else {
                $res = false;
            }
        } else {
            $res = false;
        }
	    return $res;
	}
	
	function hasToCleanup() {
	    $res = false;
	    if (is_dir($this->cacheDir)) {
	        $fn = $this->cacheDir.DIRECTORY_SEPARATOR.$this->globalPrefix.'.cleanup';
	        if (is_file($fn)) { 
	            $t = filemtime($fn);
	            if ((time() - $t) >= $this->cleanupInterval) $res = $fn;   
	        } else touch($fn);
	    }
	    return $res;
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
