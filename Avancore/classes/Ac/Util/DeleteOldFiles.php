<?php

class Ac_Util_DeleteOldFiles extends Ac_Prototyped {

    protected $dirName = false;
    protected $lifetime = 3600;
    protected $fileRegex = false;
    protected $dirRegex = false;
    protected $deleteEmptyDirs = false;
    protected $callback = false;
    protected $time = false;
    protected $foundFiles = 0;
    protected $deletedFiles = 0;
    protected $deletedDirs = 0;
    protected $realDir = false;

    function __construct(array $prototype = array()) {
        parent::__construct($prototype);
    }

    function run() {
        $t = $this->time;
        if ($t === false)
            $this->time = time();
        if ($this->deleteEmptyDirs && $this->realDir === false) 
            $this->realDir = realpath($this->dirName);
        $dir = new RecursiveDirectoryIterator($this->dirName, 
            RecursiveDirectoryIterator::SKIP_DOTS | RecursiveDirectoryIterator::CURRENT_AS_FILEINFO
        );
        $iter = new RecursiveIteratorIterator(
            $dir, 
            $this->deleteEmptyDirs? 
                RecursiveIteratorIterator::CHILD_FIRST : 
                RecursiveIteratorIterator::LEAVES_ONLY
        );
        $cb = new CallbackFilterIterator($iter, array($this, 'filter'));
        $this->foundFiles = $this->deletedFiles = $this->deletedDirs = 0;
        iterator_apply($cb, array($this, 'delete'), array($cb));
        $this->time = $t;
        $res = array(
            'foundFiles' => $this->foundFiles,
            'deletedFiles' => $this->deletedFiles,
            'deletedDirs' => $this->deletedDirs,
        );
        return $res;
    }

    protected function delete(Iterator $iter) {
        $fileinfo = $iter->current();
        if ($this->callback) {
            $m = $this->callback;
            $res = $m($fileinfo);
        } else {
            $res = true;
        }
        if ($res)
            $this->doDelete($fileinfo);
        return true;
    }

    function filter(SplFileInfo $fileinfo) {
        $this->foundFiles++;
        if ($this->time === false)
            $t = time();
        else
            $t = $this->time;
        $res = false;
        if ($fileinfo->isFile()) {
            $res = true;
            if (strlen($this->fileRegex) && !(preg_match($this->fileRegex, $fileinfo->getBasename()))) {
                $res = false;
            }
            if ($res && strlen($this->dirRegex) && !(preg_match($this->dirRegex, dirname($fileinfo->getPathname())))) {
                $res = false;
            }
            if ($res && !(($t - $fileinfo->getMTime()) > $this->lifetime)) {
                $res = false;
            }
        } elseif ($this->deleteEmptyDirs && $fileinfo->isDir()) {
            if (($fileinfo->getRealPath() !== $this->realDir) && $this->isDirEmpty($fileinfo->getPathname())) {
                $res = true;
            }
        }
        return $res;
    }

    function isDirEmpty($dir) {
        $handle = opendir($dir);
        $res = true;
        while (false !== ($entry = readdir($handle))) {
            if ($entry != "." && $entry != "..") {
                $res = false;
                break;
            }
        }
        return $res;
    }

    protected function doDelete(SplFileInfo $fileinfo) {
        if ($fileinfo->isDir()) {
            rmdir($fileinfo->getPathname());
            $this->deletedDirs++;
        } else {
            unlink($fileinfo);
            $this->deletedFiles++;
        }
    }

    function setFileRegex($fileRegex) {
        $this->fileRegex = $fileRegex;
    }

    function getFileRegex() {
        return $this->fileRegex;
    }

    function setDirRegex($dirRegex) {
        $this->dirRegex = $dirRegex;
    }

    function getDirRegex() {
        return $this->dirRegex;
    }

    function setTime($time) {
        if (is_string($time)) {
            $time = strtotime($time);
        } elseif (is_numeric($time)) {
            $time = (int) $time;
        } elseif ($time === false) {
            
        } else {
            throw Ac_E_InvalidCall::wrongType('time', $time, array('string', 'numeric', 'false'));
        }
        $this->time = $time;
    }

    function getTime() {
        return $this->time;
    }

    function setCallback($callback) {
        $this->callback = $callback;
    }

    function getCallback() {
        return $this->callback;
    }

    function setLifetime($lifetime) {
        if (!is_numeric($lifetime)) 
            throw Ac_E_InvalidCall::wrongType ('lifetime', $lifetime, 'numeric');
        $this->lifetime = (int) $lifetime;
    }

    function getLifetime() {
        return $this->lifetime;
    }

    function setDirName($dirName) {
        $this->dirName = $dirName;
        $this->realDir = false;
    }

    function getDirName() {
        return $this->dirName;
    }

    function setDeleteEmptyDirs($deleteEmptyDirs) {
        $this->deleteEmptyDirs = (bool) $deleteEmptyDirs;
    }

    function getDeleteEmptyDirs() {
        return $this->deleteEmptyDirs;
    }    

}
