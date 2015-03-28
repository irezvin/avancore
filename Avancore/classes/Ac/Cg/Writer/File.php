<?php

class Ac_Cg_Writer_File extends Ac_Cg_Writer_Abstract {

    protected $basePath = false;

    function setBasePath($basePath) {
        $this->basePath = $basePath;
    }

    function getBasePath() {
        return $this->basePath;
    }

    protected function doWriteContent($reativePath, $content) {
        $path = $reativePath;
        if (strlen($this->basePath))
            $path = rtrim($this->basePath, '/') . '/' . $path;
        Ac_Cg_Util::createDirPath(dirname($path));
        $f = fopen($path, "w");
        if ($f === false)
            trigger_error("Cannot open file '$path' for write", E_USER_ERROR);
        if (($bytes = fputs($f, $content)) === false) {
            trigger_error("Cannot write to file '$path''", E_USER_ERROR);
            fclose($f);
            unlink($path);
        }
        if (fclose($f) === false) {
            trigger_error("Cannot close file '$path''", E_USER_ERROR);
            unlink($path);
        }
        chmod($path, 0666);
    }

}
