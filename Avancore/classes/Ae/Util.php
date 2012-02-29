<?php

/**
 * Large number of general purpose static methods
 */

if (!defined('AE_PHP_VERSION_MAJOR') && !defined('AE_PHP_VERSION_MINOR')) {
    $__phpversion = explode('.', PHP_VERSION, 2);
    define('AE_PHP_VERSION_MAJOR', intval($__phpversion[0]));
    define('AE_PHP_VERSION_MINOR', floatval($__phpversion[1]));
    unset($__phpversion);
}

if (!defined('AE_UTIL_DEFAULT_CHARSET')) define('AE_UTIL_DEFAULT_CHARSET', 'utf-8');

class Ae_Util {
    
    static function m($paArray1, $paArray2, $preserveNumeric = false) {
        return Ae_Util::array_merge_recursive2 ($paArray1, $paArray2, $preserveNumeric);
    }

    static function ms(& $paArray1, $paArray2, $preserveNumeric = false) {
        return $paArray1 = Ae_Util::array_merge_recursive2 ($paArray1, $paArray2, $preserveNumeric);
    }

    static function array_merge_recursive2($paArray1, $paArray2, $preserveNumeric  = false) {
        if (!is_array($paArray1) or !is_array($paArray2)) { return $paArray2; }
            foreach ($paArray2 AS $sKey2 => $sValue2) {
                if (!isset($paArray1[$sKey2])) $paArray1[$sKey2] = null;
                if (is_int($sKey2) && !$preserveNumeric) {
                    $paArray1[] = Ae_Util::array_merge_recursive2($paArray1[$sKey2], $sValue2);
                }
                else $paArray1[$sKey2] = Ae_Util::array_merge_recursive2($paArray1[$sKey2], $sValue2, $preserveNumeric);
            }
        return $paArray1;
    }
    
    static function mkAttribs ($attribs = array(), $quote='"', $quoteStyle = ENT_QUOTES, $charset = false, $doubleEncode = true) {
        if (!$attribs) return "";
        if (isset($attribs['style']) && is_array($attribs['style'])) {
            $style = array();
            foreach ($attribs['style'] as $k => $v) $style[] = $k.": ".$v;
            $attribs['style'] = implode('; ', $style);
        }
        if ($charset === false) $charset = AE_UTIL_DEFAULT_CHARSET;
        $res = array();
        foreach ($attribs as $k => $v) {
            if (is_bool($v)) {
                if (!$v) continue;
                else $v = $k;
            }
            $res[] = $k."=".$quote.Ae_Util::htmlspecialchars($v, $quoteStyle, $charset, $doubleEncode).$quote;
        }
        return implode(" ", $res);
    }
    
    static function mkElement($tagName, $tagBody = false, $attribs = array(), $quote='"', $quoteStyle = ENT_QUOTES, $charset = false, $doubleEncode = true) {
        $res = '<'.$tagName;
        if ($attribs) $res .= ' '.Ae_Util::mkAttribs($attribs, $quote, $quoteStyle, $charset, $doubleEncode = true);
        if ($tagBody !== false) $res .= '>'.$tagBody.'</'.$tagName.'>';
            else $res .= ' />';
        return $res;
    }
    
    static function getEmailRx() {
        $qtext = '[^\\x0d\\x22\\x5c\\x80-\\xff]';
        $dtext = '[^\\x0d\\x5b-\\x5d\\x80-\\xff]';
        $atom = '[^\\x00-\\x20\\x22\\x28\\x29\\x2c\\x2e\\x3a-\\x3c'.
        '\\x3e\\x40\\x5b-\\x5d\\x7f-\\xff]+';
        $quoted_pair = '\\x5c\\x00-\\x7f';
        $domain_literal = "\\x5b($dtext|$quoted_pair)*\\x5d";
        $quoted_string = "\\x22($qtext|$quoted_pair)*\\x22";
        $domain_ref = $atom;
        $sub_domain = "($domain_ref|$domain_literal)";
        $word = "($atom|$quoted_string)";
        $domain = "$sub_domain(\\x2e$sub_domain)*";
        $local_part = "$word(\\x2e$word)*";
        $addr_spec = "$local_part\\x40$domain";
        return "!^$addr_spec$!";
    }

    /**
     * Can be used to obfuscate emails and any text - generates javascript that writes it
     * 
     * @param type $str
     * @return string 
     */
    static function jsEncode($str) {
        $rnd = rand(1, 1000);
        $res = "";
        $len = strlen($str);
        for ($i=$len-1; $i >= 0; $i--) 
            $res .= (ord($str{$i}) + $rnd).($i > 0? "," : "");
        $jsRes = "<script type='text/javascript'>";
            $jsRes .= "var a = new Array($res);";
            $jsRes .= "for (var i = a.length; i >= 0; i--) {";
                $jsRes .= "document.write(String.fromCharCode(a[i] - $rnd));";
            $jsRes .= "}";
        $jsRes .= "</script>";
        return $jsRes;
    }

    static function implode_r($glue, $array, $array_name = NULL) {
        if (is_string($array)) return $array;
        $return = array();
        while(list($key,$value) = @each($array)) {
            if(is_array($value)) $return[] = Ae_Util::implode_r($glue, $value, (string) $key);
            else { 
                if($array_name != NULL) $return[] = $value;
                else $return[] = $value;
            }
        }
        return(is_array($return)? implode($glue, $return) : $return);
    }
    
    static function getCsvLine($line,$delimiter=",",$enclosure="\"") {
        // Build the string
        $string = "";
         
        $writeDelimiter = FALSE;
        
        foreach($line as $dataElement){ 
            $dataElement=str_replace($enclosure, $enclosure.$enclosure, $dataElement);
            if($writeDelimiter) $string .= $delimiter;
            $string .= $enclosure . $dataElement . $enclosure;
            $writeDelimiter = TRUE;
        }
        $string .= "\n";
        
        return $string;
    }

    static function date ($src, $format = null, $useGmt = false) {
        return Ae_Model_DateTime::date($src, $format, $useGmt);
    }

    static function createDirPath($dirPath, $rights = 0777) {
        $dirs = explode('/', $dirPath);
        $dir='';
        foreach ($dirs as $part) {
            $dir.=$part.'/';
            if (!is_dir($dir) && strlen($dir)>0)
                if (!mkdir($dir, $rights)) return false;
        }
        return true;
    }
    
    static function createFilePath($filePath, $rights = 0777) {
        $dir = dirname($filePath);
        Ae_Util::createDirPath($dir, $rights);
        $handle = fopen($filePath, "w");
        return $handle;
    }

    static function listDirContents($dirPath, $recursive = false, $files = array(), $fileRegex = false, $dirRegex = false) {
        if(!($res = opendir($dirPath))) trigger_error("$dirPath doesn't exist!", E_USER_ERROR);
        while($file = readdir($res)) {
            if($file != "." && $file != "..") {
                if($recursive && is_dir("$dirPath/$file") && (!$dirRegex || preg_match($dirRegex, "$dirPath/$file"))) 
                    $files=Ae_Util::listDirContents("$dirPath/$file", $recursive, $files, $fileRegex, $dirRegex);
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

    /**
     * Transforms foo[bar][baz] into array('foo','bar','baz')
     */
    static function pathToArray($path) {
        if (is_array($path)) return $path;
        if (!strlen($path)) return array();
        if (strpos($path, '[') === false) return array($path);
        return explode('[', str_replace(']', '', $path));
    }
    
    /**
     * Transforms array('foo', 'bar', 'baz') into foo[bar][baz]
     */
    static function arrayToPath($array) {
        if ($c = count($array)) {
            $res = $array[0];
            for ($i = 1; $i < $c; $i++) $res .= '['.$array[$i].']';
        } else $res = '';
        return $res;
    }
    
    /**
     * Splits array path 'foo[bar][baz]' into head ('foo') and tail ('bar[baz]')
     * @param string $path array path to split 
     * @return array (head, tail) tail is '' when no tail ;-))
     */
    static function pathHeadTail ($path) {
        if (!strlen($path)) return array('', '');
        if (($pos = strpos($path, '[')) === false) return array($path, '');
        $array = explode('[', str_replace(']', '', $path));
        $head = substr($path, 0, $pos);
        list ($head, $tail) = explode('[', $path, 2);
        $tail = implode('', explode(']', $tail, 2));
        return array($head, $tail);
    }
    
    /**
     * Splits array path 'foo[bar][baz]' into body ('foo[bar]') and tail ('baz')
     * @param string $path array path to split 
     * @return array (body, tail) body is '' when only one segment is present
     */
    static function pathBodyTail ($path) {
        $array = array_reverse(Ae_Util::pathToArray($path));
        if ($c = count($array)) {
            $tail = $array[$c - 1];
            $body = Ae_Util::arrayToPath(array_slice($array, 0, $c - 1));    
        } else $body = $tail = '';
        return array($body, $tail);
    }
    
    static function concatPaths ($path1, $path2) {
        if (!strlen($path2)) return $path1;
        if (!strlen($path1)) return $path2;
        $arr2 = Ae_Util::pathToArray($path2);
        $res = $path1.'['.implode('][', $arr2).']';
        return $res; 
    }
    
    static function concatManyPaths ($path1, $path2) {
        $res = array();
        if (is_array($path1)) {
            foreach ($path1 as $p1) $res = array_merge($res, Ae_Util::concatManyPaths($p1, $path2));        
        } 
        elseif (is_array($path2)) {
            foreach ($path2 as $p2) $res = array_merge($res, Ae_Util::concatManyPaths($path1, $p2));
        } else {
            $res[] = Ae_Util::concatPaths($path1, $path2);
        }
        return $res;
    }
    
    static function getObjectProperty(& $object, $property, $default = false) {
        if (is_callable($getter = array(& $object, 'get'.ucFirst($property)))) $res = call_user_func($getter);
            elseif (isset($object->$property)) $res = $object->$property;
                else $res = $default;
        return $res;
    }

    static function stripSlashes( &$value ) {
        $res = '';
        if (is_string( $value )) {
            $res = stripslashes( $value );
        } else {
            if (is_array( $value )) {
                $res = array();
                    foreach ($value as $key => $val) {
                        $res[$key] = Ae_Util::stripSlashes( $val );
                    }
                } else {
                    $res = $value;
            }
        }
        return $res;
    }
    
    static function bindArrayToObject ($array, &$obj, $ignore='', $prefix=NULL, $checkSlashes=false) {
        if (!is_array($array) || !is_object($obj)) return (false);
        if (!is_array($ignore)) $ignore = explode(" ", $ignore);
        $gmq = get_magic_quotes_gpc();
        foreach (array_keys(get_object_vars($obj)) as $k) {
            if ($k{0} != '_') {
                if (!in_array($k, $ignore)) {
                    if ($prefix) {
                        $ak = $prefix . $k;
                    } else {
                        $ak = $k;
                    }
                    if (isset($array[$ak])) {
                        $obj->$k = ($checkSlashes && $gmq) ? Ae_Util::stripSlashes($array[$ak]) : $array[$ak];
                    }
                }
            }
        }
        return true;
    }
    
    static function simpleBind ($array, & $obj) {
        foreach (array_keys($array) as $k) if (($k{0} !== '_') && isset($obj->$k)) {
            $obj->$k = & $array[$k];
        }
    }
    
    static function simpleBindAll ($array, & $obj, $onlyKeys = false) {
        if (is_array($onlyKeys)) $keys = array_intersect(array_keys($array), $onlyKeys);
            else $keys = array_keys($array);
        foreach ($keys as $k) if (($k{0} !== '_')) {
            $obj->$k = & $array[$k];
        }
    }
    
    static function simpleBindVeryAll ($array, & $obj, $onlyKeys = false) {
        if (is_array($onlyKeys)) $keys = array_intersect(array_keys($array), $onlyKeys);
            else $keys = array_keys($array);
        foreach ($keys as $k) {
            $obj->$k = & $array[$k];
        }
    }
    
    static function smartBind ($array, & $obj) {
        foreach (array_keys($array) as $k) if ($k{0} !== '_') {
            if (isset($obj->$k)) $obj->$k = & $array[$k];
            elseif (method_exists($obj, $setter = 'set'.ucfirst($k))) $obj->$setter($array[$k]);
        }
    }
    
    static function filterValue($value, $noTrim = false, $allowHtml = false) {
        if (!is_scalar($value)) return false;
        if (get_magic_quotes_gpc()) $value = stripslashes($value);
        if (!$allowHtml) $value = strip_tags($value);
        if (!$noTrim) $value = trim($value);
        return $value;
    }
    
    /**
     * Returns item from nested arrays using specified set of keys.
     * 
     * If $path is array('foo', 'bar'), $arr['foo']['bar'] will be returned. 
     * 
     * @param array $arr           Array with information source
     * @param string|array $path  Keys that we are interested in (string 'path' will be converted to array('path'))
     * @param mixed $defaultValue Value that will be returned when corresponding entry is not found
     */
    static function getArrayByPath($arr, $arrPath, $defaultValue = null) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        while ($arrPath) {
            $key = array_pop($arrPath);
            if (is_array($src) && isset($src[$key])) $src = & $src[$key];
                else return $defaultValue;
        }
        return $src;
    }
    
    /**
     * Synonim for getArrayByPath that also automatically converts string paths to arrays
     * 
     * @param array $arr Source array
     * @param string|array $path Path in array
     * @param mixed $defaultValue Value to return if any of path keys isn't found
     * 
     * @return mixed
     */
    static function gap(array $arr, $path, $defaultValue = null) {
        if (!is_array($path)) $path = Ae_Util::pathToArray($path);
        return Ae_Util::getArrayByPath($arr, $path, $defaultValue);
    }
    
    /**
     * Sets element of nested arrays using specified set of keys.
     * 
     * If $arrPath is array('foo', 'bar'), $arr['foo']['bar'] will be set to $value. 
     * 
     * @param array $arr             Array which we are going to modify
     * @param string|array $arrPath      Keys that we are interested in (string 'path' will be converted to array('path'))
     * @param mixed $value           Value that we want to set
     * @param bool|callback $overwriteNonArrays  If one of mid-segments of path points to non-array, how conflict should be resolved
     * @param array $extraParams    Extra params to pass to callback static function
     * @return bool              Whether operation succeeded or not (depends on $overwrite and current $arr values)
     * 
     * Example of how $overwriteNonArrays works:
     * <code>
     *      $arr = array('x' => array('x1' => 'valX1', 'x2' => 'valX2'));
     *      $arrPath = array('x', 'x1', 'x11');
     *      $overwrite = false;
     *      var_dump(Ae_Util::setArrayByPath($arr, $arrPath, 'foo', $overwrite)); // Will return false; $arr will remain unchanged
     * 
     *      $arr = array('x' => array('x1' => 'valX1', 'x2' => 'valX2'));
     *      $arrPath = array('x', 'x1', 'x11');
     *      $overwrite = false;
     *      var_dump(Ae_Util::setArrayByPath($arr, $arrPath, 'foo', $overwrite)); // Will return true; 
     *      var_dump($arr); // array('x' => array('x1' => array('x11' => 'foo'), 'x2' => 'valX2'));
     * 
     *      static function overwriteCallback($currPath, & $element, $value) {
     *          if ($element == 'valX1') {
     *              $element = array($element);
     *          } elseif ($element == 'valX2') {
     *              return false;   
     *          }
     *      }
     * 
     *      $arr = array('x' => array('x1' => 'valX1', 'x2' => 'valX2'));
     *      $overwrite = 'overwriteCallback';
     *      var_dump(Ae_Util::setArrayByPath($arr, array('x', 'x1', 'x11'), 'foo', $overwrite)); // Will return true
     *      var_dump($arr); // array('x' => array('x1' => array('valX1', 'x11' => 'foo')), 'x2' => 'valX2');
     * 
     *      var_dump(Ae_Util::setArrayByPath($arr, array('x2', 'x21'), 'foo', $overwrite)); // Will return false; $arr will remain unchanged
     * </code> 
     */
    static function setArrayByPath(& $arr, $arrPath, $value, $overwrite = false, $extraParams = array()) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $src = & $arr;
        if ($arrPath) {
            $arrPath = array_reverse($arrPath);
            $key = array_pop($arrPath);
            while ($arrPath) {
                if (!isset($src[$key])) $src[$key] = array();
                elseif (!is_array($src[$key])) {
                    if ($overwrite === true) $src[$key] = array();
                    elseif ($overwrite === false) return false;
                    elseif (is_callable($overwrite)) {
                        $oRes = call_user_func_array($overwrite, array_merge(array(array_reverse(array_merge($arrPath, array($key))), & $src[$key], $value), $extraParams));
                        if ($oRes === false) return false;
                        if (!is_array($src[$key])) $src[$key] = array();
                    }
                }
                $src = & $src[$key];
                $key = array_pop($arrPath);
            }
            $src[$key] = $value;
        } else {
            $src = $value;
        }
        return true;
    }

    /**
     * Unsets item of nested arrays using specified set of keys.
     * 
     * If $path is array('foo', 'bar'), $arr['foo']['bar'] will be unset. 
     * 
     * @param array $arr           Array with information source
     * @param string|array $path  Keys of element that we want to unset (string 'path' will be converted to array('path'))
     * @return bool                Whether we have found (and unset) our element, or not 
     */
    static function unsetArrayByPath(& $arr, $arrPath) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        while ($arrPath) {
            $key = array_pop($arrPath);
            if (is_array($src) && isset($src[$key])) {
                if ($arrPath) $src = & $src[$key];
                else {
                    unset($src[$key]);                  
                }
            } else return false;
        }
        return true;
    }
    
    static function simpleSetArrayByPath(& $arr, $arrPath, & $value, $unique = true) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        if (count($arrPath) && !strlen($arrPath[0])) {
            $unique = false;
            array_splice($arrPath, 0, 1);
        }
        $key = array_pop($arrPath);
        while ($arrPath) {
            if (!strlen($key)) {
                if (!count($src)) $key = 0;
                else $key = max(0, (int) max(array_keys($src)));
            }
            if (!isset($src[$key])) $src[$key] = array();
            elseif (!is_array($src[$key])) {
                $src[$key] = array();
            }
            $src = & $src[$key];
            $key = array_pop($arrPath);
        }

        if ($unique) $src[$key] = & $value;        
            else $src[$key][] = & $value;
    }
    
    static function simpleSetArrayByPathNoRef(& $arr, $arrPath, $value, $unique = true) {
    	return Ae_Util::simpleSetArrayByPath($arr, $arrPath, $value, $unique);
    }
    
    static function & simpleGetArrayByPath($arr, $arrPath, $defaultValue = null) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        while ($arrPath) {
            $key = array_pop($arrPath);
            if (is_array($src) && isset($src[$key])) $src = & $src[$key];
                else return $defaultValue;
        }
        return $src;
    }
    
    static function getIntArray($varName, $srcArr) {
        if (!isset($srcArr[$varName])) return array();
        $res = array();
        if (!is_array($srcArr[$varName])) $s = array($srcArr[$varName]); else $s = $srcArr[$varName];
        foreach ($s as $v) {
            if (is_numeric($v)) $res[] = (int) $v;
        }
        return $res;
    }

    static function makeCsvLine($line,$delimiter=";",$enclosure="\"", $addNewLine = true, $forceText = false) {
        $string = "";
        $writeDelimiter = FALSE;
        foreach($line as $dataElement){
            $dataElement = str_replace($enclosure, $enclosure.$enclosure, $dataElement);
            if($writeDelimiter) $string .= $delimiter;
            if ($forceText && (strpos($dataElement, $delimiter) === false)) $string .= '='; 
            $string .= $enclosure . $dataElement . $enclosure;
            $writeDelimiter = TRUE;
        }
        if ($addNewLine) $string .= "\n";
        return $string;
    }
    
    static function d(& $s) {
        echo '<pre>'.htmlspecialchars(print_r($s, 1)).'</pre>';
    }
    
    static function array_values($array) {
        foreach ($array as $v) $res[] = $v;
        return $res;
    }
    
    /**
     * @param array $keys list of keys of return array
     * @return array ('key1' => false, 'key2' => false...)
     */
    static function lazyArray($keys) {
        foreach ($keys as $k) $res[$k] = false;
        return $res;
    }
    
    static function stripTrailingSlash($s, $slash = '/') {
        if (!strlen($s)) return $s;
        $l = strlen($s);
        if (strpos($slash, substr($s, $l - 1, 1)) !== false) $s = substr($s, 0, $l - 1);
        return $s;
    }
    
    static function flattenArray($array, $level = -1) {
        $res = array();
        foreach ($array as $k => $v) {
            if (is_array($v) && ($level != 0)) $res = array_merge($res, Ae_Util::flattenArray($v, $level-1));
            else $res[] = & $array[$k];
        }
        return $res;
    }
    
    /**
     * @return bool True if $object1 and $object2 are both references to the same object
     */
    static function sameObject(& $object1, & $object2) {
        
        if (AE_PHP_VERSION_MAJOR >= 5) return $object1 === $object2;
        
        if (!is_object($object1) || !is_object($object2)) return false;
        $p = $v = '_p_'.md5(microtime());
        $object1->$p = $v;
        if (isset($object2->$p) && $object2->$p === $v) {
            $res = true;
        } else $res = false;
        unset($object1->$p);
        return $res;
    }
    
    static function array_unique($arr) {
        $res = array();
        foreach ($arr as $i) if (!in_array($i, $res)) $res[] = $i;
        return $res;
    }

    /**
     * Converts 'foo_bar' to 'Foo_Bar'
     */
    static function fixClassName($className) {
        /*$className = str_replace("_", " ", $className);
        $className = ucwords($className);
        $className = str_replace(" ", "_", $className);*/
        return $className;
    }
    
    static function sameInArray(& $obj, $array) {
        $res = false;
        foreach(array_keys($array) as $k) {
            if (Ae_Util::sameObject($obj, $array[$k])) {
                $res = true;
                break;
            }
        }
        return $res;
    }

    /**
     * @static 
     */
    static function bindAutoparams(& $obj, $options, $alsoSimpleBind = true, $firstOnes = false) {
        $bind = true;
        if (method_exists($obj, 'bindAutoparams')) { 
            if ($obj->bindAutoparams($options, $alsoSimpleBind, $firstOnes) === false) $bind = false;
        }
        if ($bind) {
            $ov = get_object_vars($obj);
            $keys = array_keys($options);
            if (is_array($firstOnes)) {
                $keys = array_unique(array_merge(array_intersect($firstOnes, $keys), $keys));
            }
            foreach ($keys as $k) {
                if (is_callable($call = array($obj, 'set'.ucfirst($k))) || is_callable($call = array($obj, '_set'.ucfirst($k)))) { 
                    call_user_func($call, $options[$k]);
                }
                elseif ($alsoSimpleBind && ($k{0} !== '_') && array_key_exists ($k, $ov)) $obj->{$k} = $options[$k]; 
            }
        }
    }
    
    static function & factoryWithOptions ($options = array(), $baseClass, $classParam = 'class', $ensureBaseClass = true, $loadWithUnderscores = false) {
        $className = $baseClass;
        if (strlen($classParam) && isset($options[$classParam]) && strlen($options[$classParam])) 
            $className = $options[$classParam];
        if (class_exists('Ae_Dispatcher')) Ae_Dispatcher::loadClass($className);
        if (!class_exists($className)) {
        	if ($loadWithUnderscores) {
        		require($fName = str_replace('_', DIRECTORY_SEPARATOR, trim($className,'_').'.php'));
        		if (!class_exists($className)) trigger_error ("Class '$className' not found in '{$fName}'", E_USER_ERROR);
        	}
        	trigger_error ("Class '$className' not found", E_USER_ERROR);
        }
        $res = new $className ($options);
        if ($ensureBaseClass && !is_a($res, $baseClass)) trigger_error("Class '{$className}' is not a descendant of '{$baseClass}'", E_USER_ERROR);
        return $res;
    }
    
    static function getUploadedFilesByHierarchy() {
        $newOrdering = array();
        if (isset($_FILES)) {
            foreach ($_FILES as $var => $info) {
                foreach (array_keys($info) as $attr) {
                    self::groupFileInfoByVariable($newOrdering[$var], $info[$attr], $attr);
                }
            }
        }
        return $newOrdering;
    }

    protected static function groupFileInfoByVariable(&$top, $info, $attr) {
       if (is_array($info)) {
           foreach ($info as $var => $val) {
               if (is_array($val)) {
                   Ae_Util::_groupFileInfoByVariable($top[$var], $val, $attr);
               } else {
                   $top[$var][$attr] = $val;
               }
           }
       } else {
           $top[$attr] = $info;
       }
       return true;
    }
    
    static function showCoolTable($tableData, $colTitles, $rowTitles, $defaultRow = array(), $returnCells = false) {
        $colsList = array_unique(array_merge(array_keys($colTitles), array_keys($defaultRow)));
        foreach (array_keys($colTitles) as $k) if (!isset($defaultRow[$k])) $defaultRow[$k] = '';
        foreach ($tableData as $k => $row) {
            $colsList = array_unique(array_merge ($colsList, array_keys($row)));
            $tableData[$k] = array_merge($defaultRow, $row);
        }
        $cells = array();
        $headerRow = array('');
        foreach ($colsList as $col) {
            $headerRow[] = isset($colTitles[$col])? $colTitles[$col] : '';
        }
        $cells[] = $headerRow;
        foreach ($tableData as $k => $row) {
            $rowCells = array();
            if (isset($rowTitles[$k])) $rowCells[] = $rowTitles[$k]; else $rowCells[] = '';
            foreach ($colsList as $col) $rowCells[] = isset($row[$col])? $row[$col] : '';
            $cells[] = $rowCells;
        }
        if ($returnCells) return $cells;
        echo '<table>'; foreach ($cells as $cellRow) {
            echo '<tr>'; foreach ($cellRow as $cell) {
                if (substr($cell, 0, 3) != '<td') echo '<td>'; 
                echo strlen(trim($cell))? $cell : '&nbsp;';
                if (substr($cell, 0, 3) != '<td') echo '</td>';
            }
            echo '</tr>'; 
        }
        echo '</table>';
    }
    
    static function getClassVars($className) {
        if (is_object($className)) $className = get_class($className);
        return get_class_vars($className);
    }
    
    static function htmlEntityDecode($stringOrArray, $quoteStyle = ENT_QUOTES, $charset = null) {
        if (is_array($stringOrArray)) {
            $res = array();
            foreach ($stringOrArray as $k => $v) $res[$k] = Ae_Util::htmlEntityDecode($v, $quoteStyle, $charset);
            return $res;
        }
        return html_entity_decode($stringOrArray, $quoteStyle, $charset);
    }
        
    static function htmlspecialchars($string, $quote_style = null, $charset = null, $double_encode = true)
    {
        
        if (!strlen($charset)) $charset = null;
        if ($double_encode) return htmlspecialchars($string, $quote_style, $charset);
        elseif ((AE_PHP_VERSION_MAJOR >= 5) && (AE_PHP_VERSION_MINOR >= 2.3)) {
            return htmlspecialchars($string, $quote_style, $charset, $double_encode);
        }
        else {
            if (!$double_encode) {
                $res = htmlspecialchars($string, $quote_style, $charset);
                $res = preg_replace("/\&amp;((#[0-9][0-9]*)|(#[xX][0-9a-fA-F]+)|([a-z][0-9a-zA-Z]*));/",'&\\1;', $res);
                return $res;
            } else {
                return htmlspecialchars($string, $quote_style, $charset);
            }
        }
    }
    
    static function addTrailingSlash($string, $slashes = '/\\', $slashType = DIRECTORY_SEPARATOR) {
        $res = Ae_Util::removeTrailingSlash($string, $slashes).$slashType;
        return $res;
    }
    
    static function addLeadingSlash($string, $slashes = '/\\', $slashType = DIRECTORY_SEPARATOR) {
        $res = $slashType.Ae_Util::removeLeadingSlash($string, $slashes);
        return $res;
    }
    
    static function removeTrailingSlash($string, $slashes = '/\\') {
        $res = rtrim($string, $slashes);
        return $res;
    }
    
    static function removeLeadingSlash($string, $slashes = '/\\') {
        $res = ltrim($string, $slashes);
        return $res;
    }
    
    static function flatArray2tree ($flatArray, $keys, $lastLevelIsArray = true, $unsetKeys = false) {
    	$res = array();
    	if (!is_array($keys)) $keys = array($keys);
    	$unique = !$lastLevelIsArray;
    	foreach ($flatArray as $key => & $item) {
    		$path = array();
    		foreach ($keys as $k) {
    			if (is_null($k)) $path[] = $key;
    			elseif (isset($item[$k])) {
    				$path[] = $item[$k];
    				if ($unsetKeys) unset($item[$k]);
    			}
    		}
    		if (count($path)) Ae_Util::simpleSetArrayByPath($res, $path, $item, $unique);
    	}
    	return $res;
    }
    
    /**
     * Replaces objects in the array with the output of their $methodName() method.
     * DO NOT USE until it's finished 
     * 
     * @param mixed $src 							Data source. For objects their $methodName() value is returned; arrays are processed recursively and other values are returned as-is. 
     * @param string $methodName 					Method name to convert objects. 
     * @param array $arguments 						Arguments for called method (if any).
     * @param bool $descendIntoCollectedResults		If $src->$methodName() returns an array or an object, have collectRecursive() applied to the result before return 
     * @param bool $rejectNonCompatible				Remove objects that don't have $methodName() method from output (also return NULL if such object is provided as $src) 
     * @return mixed
     */
    static function & collectRecursive(& $src, $methodName, $arguments = array(), $descendIntoCollectedResults = true, $rejectNonCompatible = false) {
    	if (is_array($src)) {
    		$res = array();
    		foreach (array_keys($src) as $k) {
    			if (is_object($src[$k]) && (!$rejectNonCompatible || is_callable(array($src[$k], $methodName)))) {
    				$res[$k] = & $src[$k];
    			}
    		}
    	} elseif (is_object($src)) {
    		//  NOTE: unfinished function!!! 
    	} else {
    		$res = $src;
    	}
    	return $res;
    }

    /**
     * Converts any non-array value into an array.
     * Array will contain one element (with index 0) if value is not NULL and not FALSE. NULL and FALSE are converted to an empty array.
     * Array parameters will be returned as-is.
     * 
     * @param mixed $something
     * @return array 
     */
    static function toArray($something) {
        if (!is_array($something)) {
            if (is_null($something) || $something === false) $res = array();
            else $res = array($something);
        } else $res = $something;
        return $res;
    }

    /**
     * This obfuscated function obfuscates email
     * 
     * Borrowed from http://www.maurits.vdschee.nl/php_hide_email/ (license: Public Domain)
     * 
     * @param string $email Email to obfuscate
     * @return string HTML+javascript code 
     */
    static function obfuscateEmail($email) {
        $character_set = '+-.0123456789@ABCDEFGHIJKLMNOPQRSTUVWXYZ_abcdefghijklmnopqrstuvwxyz';
        $key = str_shuffle($character_set); $cipher_text = ''; $id = 'e'.rand(1,999999999);
        for ($i=0;$i<strlen($email);$i+=1) $cipher_text.= $key[strpos($character_set,$email[$i])];
        $script = 'var a="'.$key.'";var b=a.split("").sort().join("");var c="'.$cipher_text.'";var d="";';
        $script.= 'for(var e=0;e<c.length;e++)d+=b.charAt(a.indexOf(c.charAt(e)));';
        $script.= 'document.getElementById("'.$id.'").innerHTML="<a href=\\"mailto:"+d+"\\">"+d+"</a>"';
        $script = "eval(\"".str_replace(array("\\",'"'),array("\\\\",'\"'), $script)."\")"; 
        $script = '<script type="text/javascript">/*<![CDATA[*/'.$script.'/*]]>*/</script>';
        return '<span id="'.$id.'">[javascript protected email address]</span>'.$script;
    }
    
}

/**
 * Used to register as unserialize handler (some version of PHP didn't allow to provide a static call)
 * @param type $className 
 */
function aeDispatcherLoadClass($className) {
    Ae_Dispatcher::loadClass(Ae_Util::fixClassName($className));
}