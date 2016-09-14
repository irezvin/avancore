<?php

class Ac_Cg_Writer_File extends Ac_Cg_Writer_Abstract {

    protected $basePath = false;

    function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    function getBasePath() {
        return $this->basePath;
    }

    protected function doWriteContent($reativePath, $content, $overwrite = false) {
        $path = $reativePath;
        if (strlen($this->basePath))
            $path = rtrim($this->basePath, '/') . '/' . $path;
        if ($content === Ac_Cg_Generator::CONTENT_DIR) {
            Ac_Cg_Util::createDirPath($path);
            return true;
        }
        Ac_Cg_Util::createDirPath(dirname($path));
        if (!$overwrite && is_file($path)) return false;
        $f = fopen($path, "w");
        if ($f === false)
            throw new Exception("Cannot open file '$path' for write");
        fputs($f, $content);
        fclose($f);
        chmod($path, 0666);
        return true;
    }
    
    function deploy($srcDir, $destDir, $editable, & $err, $deleteSrc = false) {
        $err = implode("\n", Ac_Cg_Util::copyDirRecursive($srcDir, $destDir, !$editable, !$editable));
        $res = !$err;
        if ($res && $deleteSrc) {
            Ac_Cg_Util::cleanDir($srcDir);
            rmdir($srcDir);
        }
        return $res;
    }
    
}
