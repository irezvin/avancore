<?php

class Cg_Util {
    
    
    
    function createDirPath($dirPath, $rights = 0777) {
        $dirs = explode('/', $dirPath); 
        $dir='';
        foreach ($dirs as $part) {
            $dir.=$part.'/';
            if (!is_dir($dir) && strlen($dir)>0)
                mkdir($dir);
                chmod($dir, $rights);
        }
    }
    
    function createFilePath($filePath, $dirRights = 0777, $fileRights = 0666) {
        $dir = dirname($filePath);
        Cg_Util::createDirPath($dir, $dirRights);
        touch($filePath);
        chmod($filePath, $fileRights);
        $handle = fopen($filePath, "w");
        return $handle;
    }
    
    function listDirContents($dirPath, $recursive = false, $files = array(), $fileRegex = false, $dirRegex = false, $includeDirNames = false) {
        if(!($res = opendir($dirPath))) trigger_error("$dirPath doesn't exist!", E_USER_ERROR);
        while($file = readdir($res)) {
            if($file != "." && $file != ".." && $file != ".svn") {
                if($recursive && is_dir("$dirPath/$file") && (!$dirRegex || preg_match($dirRegex, "$dirPath/$file"))) { 
                    if ($includeDirNames) array_push($files, "$dirPath/$file");    
                    $files=Cg_Util::listDirContents("$dirPath/$file", $recursive, $files, $fileRegex, $dirRegex, $includeDirNames);
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

    function cleanDir($dirName) {
        $dc = Cg_Util::listDirContents($dirName, true, array(), false, false, true);
        sort($dc);
        $dc = array_reverse($dc);
        foreach ($dc as $item) {
            if (is_file($item)) unlink($item);
                else rmdir($item);
        }
    }
    
    function addSpacesBeforeCamelCase($string) {
        $res = preg_replace('/\\B([A-Z])/', ' \\1', $string);
        return $res;
    }
    
    function makeIdentifier($string, $ofClass = false) {
        if ($ofClass) {
            $res = preg_replace('/ +/', '_', ucwords($string));
        } else {
            $res = str_replace(' ', '', ucwords($string));
            if (strlen($res)) $res{0} = strtolower($res{0});
        }
        return $res;
    }
    
    function className2fileName($className) {
        return 'classes/'.str_replace('_', '/', $className).'.php';
    }
    
    function makeCaption ($string) {
        $c = str_replace("_", " ", $string);
        $c = Cg_Util::addSpacesBeforeCamelCase($c);
        $c = preg_replace ("/ +/", " ", $c);
        $c = ucwords($c);
        return $c;
    }
    
    function copyDirRecursive($src, $dest, $overwrite = false, $move = false) {
        if (PATH_SEPARATOR == ';') { // it's WINDOWS
            $cmd = 'xcopy '.escapeshellarg($src).' '.escapeshellarg($dest).' /E';
            if ($overwrite === false ) {
                $tmpf = tmpfile();
                fputs($tmpf, str_repeat('n', 10240), 10240);
                fclose($tmpf);
                $p = realpath($tmpf);
                $cmd .=  ' < '.escapeshellarg($p);
            } else {
                $cmd .= ' /Y';
            }
        } else {
            $cmd = 'cp -R'.($overwrite? 'f' : 'n').' '.escapeshellarg($src).'/* '.escapeshellarg($dest)/*.' --preserve=mode'*/;
        }
        var_dump($cmd);
        exec($cmd, $output, $res);
        $output = implode("\n", $output);
        
        if (!$res && $move) {

            if (PATH_SEPARATOR == ';') {
                $tmpf2 = tempnam('.', 'cg');
                file_put_contents($tmpf2, str_repeat('y', 10240));
                $p = realpath($tmpf2);
                
                $cmd2 = 'del /S '.escapeshellarg($src).' < '.escapeshellarg($p); 
            } else {
                $cmd2 = 'rm -Rf '.escapeshellarg($src);
            }
            exec($cmd2, $output2, $res);
            $cmd .= "\n".$cmd2;
            $output2 = implode("\n", $output2);
            $output .= "\n\n".$output2;
        }
        
        return array($cmd, $output, $res);
    }
    
}

?>