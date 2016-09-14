<?php

abstract class Ac_Cg_Util {
    
    static function createDirPath($dir, $mode = 0777) {
        if (!is_dir($dir)) {
            mkdir($dir, $mode, true);
            chmod($dir, $mode);
        }
    }
    
    static function createFilePath($filePath, $dirRights = 0777, $fileRights = 0666) {
        $dir = dirname($filePath);
        Ac_Cg_Util::createDirPath($dir, $dirRights);
        touch($filePath);
        chmod($filePath, $fileRights);
        $handle = fopen($filePath, "w");
        return $handle;
    }
    
    static function listDirContents($dirPath, $recursive = false, $files = array(), $fileRegex = false, $dirRegex = false, $includeDirNames = false) {
        if(!($res = opendir($dirPath))) trigger_error("$dirPath doesn't exist!", E_USER_ERROR);
        while($file = readdir($res)) {
            if($file != "." && $file != ".." && $file != ".svn") {
                if($recursive && is_dir("$dirPath/$file") && (!$dirRegex || preg_match($dirRegex, "$dirPath/$file"))) { 
                    if ($includeDirNames) array_push($files, "$dirPath/$file");
                    $files=Ac_Cg_Util::listDirContents("$dirPath/$file", $recursive, $files, $fileRegex, $dirRegex, $includeDirNames);
                }
                else {
                    if (!$fileRegex || preg_match($fileRegex, "$dirPath/$file")) {
                        array_push($files,"$dirPath/$file");
                    }
                } 
            }
        }
        
        closedir($res);
        
        return $files;
    }

    static function cleanDir($dirName) {
        $dc = Ac_Cg_Util::listDirContents($dirName, true, array(), false, false, true);
        sort($dc);
        $dc = array_reverse($dc);
        foreach ($dc as $item) {
            if (is_file($item)) unlink($item);
                else rmdir($item);
        }
    }
    
    static function addSpacesBeforeCamelCase($string) {
        $res = preg_replace('/\\B([A-Z])/', ' \\1', $string);
        return $res;
    }
    
    static function makeIdentifier($string, $ofClass = false) {
        if ($ofClass) {
            $res = preg_replace('/ +/', '_', ucwords($string));
        } else {
            $res = str_replace(' ', '', ucwords($string));
            if (strlen($res)) $res{0} = strtolower($res{0});
        }
        return $res;
    }
    
    static function className2fileName($className) {
        return 'classes/'.str_replace('_', '/', $className).'.php';
    }
    
    static function makeCaption ($string) {
        $c = str_replace("_", " ", $string);
        $c = Ac_Cg_Util::addSpacesBeforeCamelCase($c);
        $c = preg_replace ("/ +/", " ", $c);
        $c = ucwords($c);
        return $c;
    }
    
    static function copyDirRecursive($src, $dest, $overwrite = false, $deleteNotSync = false) {
        $ds = new Ac_Cg_DirSync();
        $ds->srcDir = $src;
        $ds->destDir = $dest;
        $ds->overwriteDest = $overwrite;
        $ds->dryRun = false;
        $ds->deleteFromDest = $deleteNotSync;
        $ds->run();
        return $ds->getErr();
    }
    
    static function findCommonPrefix(array $strings, $roundToWordBoundary = true) {
        $res = '';
        if (count($strings)) {
            do {
                $match = true;
                $s = false;
                foreach ($strings as $string) {
                    $s1 = substr($string, 0, strlen($res) + 1);
                    if ($s === false) $s = $s1;
                    elseif ($s != $s1) {
                        $match = false;
                        break;
                    }
                }
                if ($match) $res = $s;
            } while ($match);
        }
        
        // Now we should handle the cases when 'TheCoolRelation' and 'TheCookRelation' 
        // should have 'The' and not 'TheCoo' common prefix. We will round it to nearest 
        // Capital letter or underscore
        if ($roundToWordBoundary && strlen($res) && preg_match('/[a-z]$/', $res)) { // common prefix ends with lcase letter
            $startingWithLowercase = false;
            foreach ($strings as $s) {
                $remainder = substr($s, strlen($res));
                if (strlen($remainder) && preg_match('/^[a-z]/', $remainder{0})) {
                    $startingWithLowercase = true;
                    break;
                }
            }
            if ($startingWithLowercase) { // there will be subjects like 'ool'
                $items = preg_split('/(_|[A-Z][a-z]+)/', $res, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
                if (count($items) > 1) {
                    $last = array_pop($items);
                    $res = substr($res, 0, -strlen($last));
                }
            }
        }
        return $res;
    }
    
    /**
     * Compares key => value pairs of two arrays
     * Leaves only keys & values of $array1 which are missing or different from ones in $array2
     * Items with numeric keys are considered matching if value is in both arrays and with numeric keys
     * in both arrays
     */
    static function arrayDiffWithKeys(array $array1, array $array2) {
        $res = array();
        foreach ($array1 as $k => $v) {
            $diff = true;
            if (is_numeric($k) && in_array($v, $array2)) {
                foreach ($array2 as $k2 => $v2) {
                    if ($v == $v2) {
                        if (is_numeric($k2)) $diff = false;
                        break;
                    }
                }
            } else {
                if (array_key_exists($k, $array2)) {
                    if (is_array($array2[$k]) && is_array($v)) {
                        if (!$v && !$array2[$k]) $diff = false;
                        else {
                            $v = self::arrayDiffWithKeys($v, $array2[$k]);
                            if (!$v) $diff = false;
                        }
                    } else {
                        $diff = $array2[$k] !== $array1[$k];
                    }
                }
            }
            if ($diff) $res[$k] = $v;
        }
        return $res;
    }
    
    function indent($text, $indent = 4, $char = ' ') {
        preg_match_all("/^ +/m", $text, $matches);
        if ($matches) {
            $leftPart = str_repeat(' ', min(array_map('strlen', $matches[0])));
        } else $leftPart = '';
        $res = preg_replace("/^{$leftPart}/m", str_repeat($char, $indent), $text);
        return $res;
    }
    
}

