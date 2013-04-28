<?php

class Cg_Template extends Ac_Template {
    
    var $_init = false;
    
    var $_filesList = false;
    
    /**
     * @var Cg_Generator
     */
    var $generator = false;
    
    /**
     * @var Cg_Model
     */
    var $model = false;
    
    /**
     * @var Cg_Domain
     */
    var $domain = false;
    
    /**
     * @var Cg_Strategy
     */
    var $strategy = false;
    
    var $plugins = array();
    
    var $language = 'en';
    
    /**
     * Should return info on files that are generated with this template.
     * Keys of return array are:
     * - relPath - path to file relative to output root , 
     * - isEditable - whether generated file should be edited by user and is not intended to be overwritten by the generator
     * - templatePart - name of template part that renders this file's contents
     * 
     * @return array (i => array('relPath' => $relPath, 'isEditable' => true/false, 'templatePart' => $templatePart))
     */
    function _generateFilesList() {
        return array();
    }
    
    function listFiles() {
        $this->init();
        if ($this->_filesList === false) {
            $this->_filesList = $this->_generateFilesList();
        }
        return array_keys($this->_filesList);
    }
    
    function fileIsUserEditable($id) {
       if (!in_array($id, $this->listFiles())) trigger_error ("No such file: '$id' in template ".get_class($this), E_USER_ERROR);
       $res = $this->_filesList[$id]['isEditable'];
       return $res; 
    }
    
    function getFilePath($id, $basePath = false) {
       if (!in_array($id, $this->listFiles())) trigger_error ("No such file: '$id' in template ".get_class($this), E_USER_ERROR); 
       $res = $this->_filesList[$id]['relPath'];
       if ($basePath) $res = Ac_Util::stripTrailingSlash($basePath, '/\\') . '/' . $res; 
       return $res; 
    }
    
    function outputFile($id, $basePath) {
        if (!in_array($id, $this->listFiles())) trigger_error ("No such file: '$id' in template ".get_class($this), E_USER_ERROR);
        $partName = $this->_filesList[$id]['templatePart'];
        $path = $this->getFilePath($id, $basePath);
        Cg_Util::createDirPath(dirname($path));
        if (method_exists($this, $mtdName = 'show'.$partName)) {
             $f = fopen($path, "w");
             if ($f === false) trigger_error ("Cannot open file '$path' for write", E_USER_ERROR);
             if (($bytes = fputs($f, $this->fetch($partName))) === false) {
                 trigger_error("Cannot write to file '$path''", E_USER_ERROR);
                 @fclose($f);
                 @unlink($path); 
             }
             if (fclose($f) === false) {
                 trigger_error("Cannot close file '$path''", E_USER_ERROR);
                 @unlink($path);
             }
             chmod($path, 0666);
             $this->generator->addOutputStats(1, $bytes);
        } else {
            trigger_error ("No template part '{$partName}' for file '".basename($path)."' is not defined in tempalte ".get_class($this), E_USER_ERROR);
        }
    }
    
    // here should come some useful functions for php output
    
    function init() {
        if ($this->_init === false) {
            $this->_init = true;
            $this->doInit();
        }
    }
    
    function doInit() {
        
    }
    
    
    /**
     * Shows php open tag
     */
    function phpOpen() {
        echo "<"."?php";
    }
    
    /**
     * Shows php closing tag
     */
    function phpClose() {
        echo "?".">";
    }
    
    function _replaceIndent($match) {
        return "\n".str_repeat(" ", strlen($match[0])/2*4);
    }
    
    function export($foo, $return = false, $indent = 0) {
        if (is_array($foo)) $res = Cg_Template::exportArray($foo, $indent, true, true, true);
        elseif ($foo === 0) $res = '0';
        else $res = $this->myVarExport($foo, true);
        
        if ($return) return $res; 
            else echo $res;
    }
    
    function myVarExport($array, $return = false, $lvl=0)
    {
        // Common output variables
        $indent      = '  ';
        $doublearrow = ' => ';
        $lineend     = ",\n";
        $stringdelim = '\'';

        // Check the export isn't a simple string / int
        if (is_object($array) && is_a($array, 'Cg_Php_Expression')) {
            $out = $array->getExpression();
        } elseif (is_string($array)) {
            $out = $stringdelim . str_replace('\'', '\\\'', str_replace('\\', '\\\\', $array)) . $stringdelim;
        } elseif (is_int($array) || is_float($array)) {
            $out = (string)$array;
        } elseif (is_bool($array)) {
            $out = $array ? 'true' : 'false';
        } elseif (is_null($array)) {
            $out = 'NULL';
        } elseif (is_resource($array)) {
            $out = 'resource';
        } else {
            // Begin the array export
            // Start the string
            $out = "array (\n";

            // Loop through each value in array
            foreach ($array as $key => $value) {
                // If the key is a string, delimit it
                if (is_string($key)) {
                    $key = str_replace('\'', '\\\'', str_replace('\\', '\\\\', $key));
                    $key = $stringdelim . $key . $stringdelim;
                }

                $val = Cg_Template::myVarExport($value, true, $lvl+1);

                // Piece together the line
                for ($i = 0; $i <= $lvl; $i++)
                    $out .= $indent;
                $out .= $key . $doublearrow . $val . $lineend;
            }

            // End our string
            for ($i = 0; $i < $lvl; $i++)
                $out .= $indent;
            $out .= ")";
        }

        // Decide method of output
        if ($return) {
            return $out;
        } else {
            echo $out;
            return;
        }
    }
    
    /**
     * Returns code for initializing given PHP array
     */
    function exportArray($foo, $indent = 0, $withNumericKeys = false, $oneLine = false, $return = false) {
        $vx = Cg_Template::myVarExport($foo, 1);
        $vx = preg_replace("/=> \n([ ]+)array \\(/", "=> array (\\1", $vx);
        $vx = preg_replace_callback("/\n[ ]+/", array(& $this, '_replaceIndent'), $vx);
        if ($indent) {
            $ind = str_repeat(" ", $indent);
            $vx = preg_replace("/\n/", "\n".$ind, $vx);
        }
        if (!$withNumericKeys) $vx = preg_replace ("/(\n[ ]+) \\d+ =>/", "\\1", $vx);
        if ($oneLine) {
            $vx = preg_replace("/\n[ ]*/", " ", $vx);
        }
        if (!$return) echo $vx; 
            else return $vx;
            
    }
    
    /**
     * Returns escaped and quoted PHP string
     */
    function str ($string, $return = false) {
        $res = "'".addcslashes($string, "'")."'";
        if ($return) return $res; 
            else echo $res;
    }
    
    /**
     * Returns twice-escaped-and-quoted string (useful for strings in generated PHP code that runs SQL operators) 
     */
    function str2 ($string, $return = false) {
        $res = addcslashes("'".addcslashes($string, "'")."'", "'");
        if ($return) return $res; 
            else echo $res;
    }
    
    /**
     * Shows language string stored in the Strategy
     */

    function lng($strName, $default = '(Language string missing: ~)') {
        echo $this->strategy->getLanguageString($strName, $default);
    }
    
    function dict ($string, $return = false) {
        $str = $this->domain->dictionary->translate($string, $this->language);
        if ($this->domain->dictionary->isConstant($str)) $out = $str;
           else $out = "'".addcslashes($str, "'")."'";
        if ($return) return $out;
           else echo $out;
    }
    
}

?>