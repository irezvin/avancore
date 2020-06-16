<?php

class Ac_Cg_DirSync extends Ac_Prototyped {
    
    var $srcDir = false;
    
    var $destDir = false;
    
    var $overwriteDest = false;
    
    var $deleteFromDest = false;
    
    var $fileRegex = false;
    
    var $dryRun = true;
    
    var $dirMode = 0777;
    
    protected $err = array();
    
    protected $log = array();
    
    protected $workList = array();
    
    protected $workBase = '';
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * Lists directory. Adds "/" after dir names. Dir names are AFTER file names. 
     * Doesn't prefix with dir path.
     */
    protected function recDir($prefix, $path = '', $files = array(), $pattern = false) { 
        clearstatcache();
        $fullPath = rtrim($prefix, "/")."/".$path;
        if(!($dir = opendir($fullPath))) throw new Ac_E_InvalidCall("$fullPath doesn't exist!");
        while($file = readdir($dir)) {
            if($file != "." && $file != "..") {
                $d = false;
                if($d = is_dir("$fullPath/$file")) { 
                    array_push($files, ltrim("$path/$file/", "/"));
                    $files = $this->recDir($prefix, "$path/$file", $files, $pattern);
                }
                else {
                    if (!$pattern || preg_match($pattern, "$fullPath/$file")) {
                        array_push($files, ltrim("$path/$file", "/"));
                    }
                }
            }
        }
        closedir($dir);
        return $files;
    }
    
    function listSrc() {
        return $this->recDir($this->srcDir);
    }
    
    function listDest() {
        return $this->recDir($this->destDir, '', array(), $this->fileRegex);
    }
    
    protected function isEmpty($path) {
        $path = rtrim($path, "/")."/";
        $rx = "/^".preg_quote($path, "/")."./";
        return !preg_grep($rx, $this->workList);
    }
    
    protected function delete($path, $dir = null) {
        if ($dir === null) $dir = substr($path, -1) == "/";
        $empty = $dir && $this->isEmpty($path);
        $full = rtrim($this->workBase, "/")."/".trim($path, "/");
        if (!$this->dryRun) {
            if ($empty) $ok = rmdir($full) !== false;
            elseif (!$dir) $ok = unlink($full);
            else $ok = false;
        } else {
            $ok = $empty || !$dir;
        }
        if ($ok) {
            $p = trim($path, "/");
            if ($dir) $p .= "/";
            $this->workList = array_diff($this->workList, array($p));
            $this->log[] = "D\t".$full;
        } else {
            $this->err[] = "!D\t".$full;
        }
        return $ok;
    }
    
    static function identical($a, $b) {
        $res = filesize($a) === filesize($b) 
            && (fileperms($a) & 0777) === (fileperms($b) & 0777)
            && md5_file($a) === md5_file($b);
        return $res;
    }
    
    protected function copy($srcBase, $path) {
        $fullSrc = rtrim($srcBase, "/")."/".ltrim($path, "/");
        $fullDest = rtrim($this->workBase, "/")."/".ltrim($path, "/");
        if (!$this->dryRun) {
            if (!is_file($fullDest) || !self::identical($fullSrc, $fullDest)) {
                $ok = copy($fullSrc, $fullDest) !== false;
                if ($ok) @chmod($fullDest, fileperms($fullSrc) & 0777);
            } else {
                $ok = true;
            }
        }
        else $ok = true;
        if ($ok) {
            $this->log[] = "C\t".$fullSrc."\t".$fullDest;
            $p = trim($path, "/");
            if (!in_array($p, $this->workList))
                $this->workList[] = $p;
        } else {
            $this->err[] = "!C\t".$fullSrc."\t".$fullDest;
        }
        return $ok;
    }
    
    protected function mkdir($path) {
        $full = rtrim($this->workBase, "/")."/".trim($path, "/");
        if (!$this->dryRun) {
            if (!is_dir($full)) $ok = mkdir($full, $this->dirMode, true) !== false;
            else $ok = true;
        } else {
            $ok = true;
        }
        if ($ok) {
            $this->log[] = "M\t".$full;
            $this->workList[] = trim($path, "/")."/";
        } else {
            $this->err[] = "!M\t".$full;
        }
    }
    
    function clearLogs() {
        $this->log = $this->err = array();
    }
    
    protected function delAll(array $list) {
        $dirs = preg_grep("#/$#", $list);
        $files = array_diff($list, $dirs);
        foreach ($files as $path) $this->delete($path, false);
        sort($dirs);
        $dirs = array_reverse($dirs); // longest paths first
        foreach ($dirs as $dir) if ($this->isEmpty($dir)) $this->delete($dir, true);
    }
    
    function clearSrc() {
        $this->checkDir('srcDir', true);
        $this->workList = $this->listSrc();
        $this->workBase = $this->srcDir;
        $this->delAll($this->workList);
    }
    
    function run($clearLogs = false) {
        $this->checkDir('srcDir');
        $this->checkDir('destDir', true);
        $src = $this->listSrc();
        $dest = $this->listDest();
        if ($clearLogs) {
            $this->clearLogs();
        }
        $this->workBase = $this->destDir;
        $this->workList = $dest;
        foreach ($src as $path) {
            $dir = substr($path, -1) == "/";
            $exists = in_array($path, $this->workList);
            if ($dir) {
                if (!$exists) $this->mkdir($path);
            } else {
                if (!$exists || $this->overwriteDest) {
                    $this->copy($this->srcDir, $path);
                }
            }
        }
        if ($this->deleteFromDest) {
            $bogus = array_diff($this->workList, $src);
            if ($bogus) $this->delAll($bogus);
        }
        return !$this->err;
    }
    
    protected function checkDir($param, $checkWritable = false) {
        $dir = $this->$param;
        if (!is_dir($dir)) throw new Ac_E_InvalidCall("Directory $param '{$dir}' doesn't exist");
        $p = str_replace(DIRECTORY_SEPARATOR, "/", $dir);
        $dirname = str_replace(DIRECTORY_SEPARATOR, "/", dirname($dir));
        $isRoot = $dirname === $p;
        if ($isRoot) throw new Ac_E_InvalidCall("$param '{$dir}' is root");
        if ($checkWritable && !is_writable($dir)) {
            if (!is_dir($dir)) throw new Ac_E_InvalidCall("Directory $param '{$dir}' isn't writeable by the script");
        }
    }
    
    function getLog() {
        return $this->log;
    }
    
    function getErr() {
        return $this->err;
    }
    
    function getWorkList() {
        return $this->workList;
    }
    
}
