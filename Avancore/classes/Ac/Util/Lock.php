<?php

class Ac_Util_Lock extends Ac_Prototyped {

    /**
     * Lock was successfully acquired some time ago for current process
     */
    const STATUS_OWN = 0;
    
    /**
     * Other process has successfully acquired the lock some time ago
     */
    const STATUS_OTHER = 1;
    
    /**
     * Our process' lock was expired and deleted
     */
    const STATUS_OWN_EXPIRED = 2;
    
    /**
     * Other process has acquired the lock but it was expired and thus deleted
     */
    const STATUS_OTHER_EXPIRED = 3;
    
    /**
     * Other process has acquired the lock but it was expired and thus deleted
     */
    const STATUS_DELETED = 4;
    
    /**
     * Lock file doesn't exist
     */
    const STATUS_NONE = 5;
    
    /**
     * name of the directory with lock files (*false* to use default path)
     * @var string
     */
    protected $dirName = false;

    /**
     * name of the file relative to the $dirName
     * @var string
     */
    protected $fileName = false;
    
    /**
     * process ID stored in the lock file (*false* to guess randrom number). 
     * If Pid does't match, lock is considered to be created by other process
     */
    protected $pid = false;
    
    protected $pidSet = false;

    /**
     * timeout in second when lock file is considered obsolete (*false* to last forever)
     * @var int
     */
    protected $lifetime = 3600;

    /**
     * suffix of the lock file name (will be added to dir & file name)
     */
    protected $suffix = '.lock';
    
    /**
     * whether lock should be released on PHP script shutdown
     * @var bool
     */
    protected $releaseOnShutdown = false;

    /**
     * time to re-check lock file on disk since last check
     * (*false* = always check the file on disk)
     * @var float
     */
    protected $checkTimeout = false;
    
    /**
     * whether lock should be released if lock path or PID changed
     * @var bool
     */
    protected $autoRelease = false;
    
    protected $lockPid = false;
    
    protected $lockStatus = false;

    protected $lockTime = false;
    
    protected $acquireTime = false;
    
    protected $shutdownRegistered = false;
    
    protected $checked = false;
    
    /**
     * Sets name of the directory with lock files (*false* to use default path)
     * @param string $dirName
     */
    function setDirName($dirName) {
        $this->dirName = $dirName;
    }

    /**
     * Returns name of the directory with lock files (*false* to use default path)
     * @return string
     */
    function getDirName($guess = true) {
        if ($this->dirName === false  && $guess) {
            if (class_exists('Ac_Application', false) && ($app = Ac_Application::getDefaultInstance())) {
                $res = $app->getAdapter()->getVarFlagsPath();
            } else {
                $res = sys_get_temp_dir();
            }
        } else {
            $res = $this->dirName;
        }
        return $res;
    }

    /**
     * Sets name of the file relative to the $dirName
     * @param string $fileName
     */
    function setFileName($fileName) {
        $this->fileName = $fileName;
    }

    /**
     * Returns name of the file relative to the $dirName
     * @return string
     */
    function getFileName() {
        return $this->fileName;
    }
    
    function getPath() {
        if (!strlen($this->getFileName())) 
            throw new Ac_E_InvalidUsage("setFileName() first");
        $res = rtrim($this->getDirName(), '\\/').'/'.$this->fileName.$this->suffix;
        return $res;
    }

    /**
     * Sets process ID stored in the lock file (*false* to guess randrom number). 
     * If Pid does't match, lock is considered to be created by other process
     */
    function setPid($pid) {
        if ($pid !== ($oldPid = $this->pid)) {
            $this->pid = $pid;
            $this->pidSet = true;
            $this->reset();
        }
    }

    /**
     * Sets process ID stored in the lock file (*false* to guess randrom number). 
     * If Pid does't match, lock is considered to be created by other process
     */
    function getPid() {
        if ($this->pid === false) {
            $this->pid = uniqid();
        }
        return $this->pid;
    }
    
    /**
     * Sets timeout in second when lock file is considered obsolete (*false* to last forever)
     * @param int $lifetime
     */
    function setLifetime($lifetime) {
        if ($lifetime == false) $this->lifetime = false;
        elseif (!is_numeric($lifetime) || $lifetime < 0) 
            throw Ac_E_InvalidCall::wrongType('lifetime', $lifetime, array('false', 'number'));
    }

    /**
     * Returns timeout in second when lock file is considered obsolete (*false* to last forever)
     * * @return int
     */
    function getLifetime() {
        return $this->lifetime;
    }

    /**
     * Sets suffix of the lock file name (will be added to dir & file name)
     */
    function setSuffix($suffix) {
        if ($suffix !== ($oldSuffix = $this->suffix)) {
            $this->suffix = $suffix;
            $this->reset();
        }
    }    

    /**
     * Returns suffix of the lock file name (will be added to dir & file name)
     */
    function getSuffix() {
        return $this->suffix;
    }    

    /**
     * Sets time to re-check lock file on disk since last check
     * (*false* = always check the file on disk)
     * @param float|bool $checkTimeout
     */
    function setCheckTimeout($checkTimeout) {
        if ($checkTimeout == false) {
            $this->checkTimeout = false;
        } elseif (is_numeric($checkTimeout) && $checkTimeout > 0) {
            $this->checkTimeout = (float) $checkTimeout;
        } else {
            throw new Ac_E_InvalidCall("\$checkTimeout should be FALSE or a number > 0");
        }
    }

    /**
     * Returns time to re-check lock file on disk since last check
     * (*false* = always check the file on disk)
     * @return int
     */
    function getCheckTimeout() {
        return $this->checkTimeout;
    }    

    /**
     * Sets whether lock should be released if lock path or PID changed
     * @param bool $autoRelease
     */
    function setAutoRelease($autoRelease) {
        $this->autoRelease = (bool) $autoRelease;
    }

    /**
     * Returns whether lock should be released if lock path or PID changed
     * @return bool
     */
    function getAutoRelease() {
        return $this->autoRelease;
    }    
    

    /**
     * Sets whether lock should be released on PHP script shutdown
     * @param bool $releaseOnShutdown
     */
    function setReleaseOnShutdown($releaseOnShutdown) {
        $this->releaseOnShutdown = $releaseOnShutdown;
        if ($releaseOnShutdown && !$this->shutdownRegistered) {
            register_shutdown_function(array($this, '_shutdownHandler'));
        }
    }

    /**
     * Returns whether lock should be released on PHP script shutdown
     * @return bool
     */
    function getReleaseOnShutdown() {
        return $this->releaseOnShutdown;
    }
    
    function _shutdownHandler() {
        if ($this->releaseOnShutdown) $this->release();
    }
    
    function getLockPid() {
        $this->check();
        return $this->lockPid;
    }
    
    function getLockStatus() {
        $this->check();
        return $this->lockStatus;
    }
    
    function getLockTime() {
        $this->check();
        return $this->lockTime;
    }
    
    function acquire($refresh = false, $force = false) {
        if ($this->acquireTime && !$refresh) {
            $res = true; // Already acquired
        } else {
            $res = false;
            $this->check(true);
            $ls = $this->lockStatus;
            $path = $this->getPath();
            $pid = $this->getPid();
            if ($ls === self::STATUS_OWN) {
                $res = $refresh? 1 : true;
            } elseif ($ls === self::STATUS_OTHER) {
                $res = $force? 1 : false;
            } else {
                $res = 1;
            }
            if ($res === 1) {
                file_put_contents($path, $pid);
                $res = (file_get_contents($path) === $pid);
            }
        }
        return $res;
    }
    
    function has() {
        $res = $this->getLockStatus() === self::STATUS_OWN;
        return $res;
    }
    
    function release($force = false) {
        $this->check(true);
        if (($force || $this->lockStatus !== self::STATUS_OTHER) && $this->lockStatus !== self::STATUS_NONE) {
            unlink($this->getPath());
        }
        return true;
    }
    
    protected function check($force = false) {
        if ($force || !$this->checked || !$this->checkTimeout || ((microtime(true) - $this->checked) > $this->checkTimeout)) {
            $this->checked = microtime(true);
            // do the stuff
            $path = $this->getPath();
            clearstatcache(false, $path);
            if (is_file($path)) { // we have lock file
                $this->lockPid = file_get_contents($path);
                $this->lockTime = filemtime($path);
                $expired = $this->lifetime && (time() - $this->lockTime) > $this->lifetime;
                if ($this->lockPid === $this->pid) { // our lock?
                    if ($expired) {
                        $this->lockStatus = self::STATUS_OWN_EXPIRED;
                    } else {
                        $this->lockStatus = self::STATUS_OWN;
                    }
                    $this->acquireTime = $this->lockTime;
                } else {
                    if ($expired) {
                        $this->lockStatus = self::STATUS_OTHER_EXPIRED;
                    } else {
                        $this->lockStatus = self::STATUS_OTHER;
                    }
                }
            } else {
                $this->lockTime = false;
                $this->lockPid = false;
                if ($this->acquireTime) { // the lock was acquired before...
                    $this->lockStatus = self::STATUS_DELETED; // ... and it's not in place. It means it was deleted!
                } else {
                    $this->lockStatus = self::STATUS_NONE; // it's not there!
                }
            }
        }
            
    }
    
    protected function reset() {
        if ($this->checked && $this->lockStatus === self::STATUS_OWN 
            && $this->autoRelease)
            $this->release();
        
        if (!$this->pidSet) $this->pid = false;
        $this->checked = false;
    }
    
}