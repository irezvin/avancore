<?php

/**
 * Large number of general purpose static methods
 */

if (!defined('AC_PHP_VERSION_MAJOR') && !defined('AC_PHP_VERSION_MINOR')) {
    $__phpversion = explode('.', PHP_VERSION, 2);
    define('AC_PHP_VERSION_MAJOR', intval($__phpversion[0]));
    define('AC_PHP_VERSION_MINOR', floatval($__phpversion[1]));
    unset($__phpversion);
}

if (!defined('AC_UTIL_DEFAULT_CHARSET')) define('AC_UTIL_DEFAULT_CHARSET', 'utf-8');

/**
 * @TODO optimize set/get by path for simple cases (length of path 1, 2, 3, 4)
 */
abstract class Ac_Util {

    protected static $autoLoadRegistered = null;
    
    protected static $classAliases = [];
    
    protected static $revClassAliases = [];
    
    /**
     * Adds one or more include paths
     * @param false|string|array $path Path(s) to add (FALSE means directory with 'classes' where current file resides)
     * @param bool $prepend Add this path to beginning of include_path list (not the end)
     */
    static function addIncludePath($path = false, $prepend = false) {
        if ($path === false) $path = array(dirname(dirname(__FILE__)), dirname(dirname(dirname(__FILE__))).'/obsolete');
        $paths = explode(PATH_SEPARATOR, ini_get('include_path'));
        if (!is_array($path)) $path = array($path);
        if ($prepend) $paths = array_merge($path, array_diff($paths, $path));
            else $paths = array_merge(array_diff($paths, $path), $path);
        ini_set('include_path', implode(PATH_SEPARATOR, $paths)); 
    }

    static function getSafeIncludePath() {        
        $bd = explode(PATH_SEPARATOR, ini_get('open_basedir'));
        $p = explode(PATH_SEPARATOR, ini_get('include_path'));
        if ($bd) {
            foreach ($p as $i => $dir) {
                $found = false;
                foreach ($bd as $dir2) {
                    if (!strncmp($dir, $dir2, strlen($dir2))) {
                        $found = true;
                        break;
                    } 
                }
                if (!$found) unset($p[$i]);
            }
        }
        return $p;
    } 
    
    static function loadClass($className) {
        
        if (isset(self::$classAliases[$className]) && !class_exists($className, false)) {
            $orig = self::$classAliases[$className];
            $res = class_exists($orig);
            if ($res && !class_exists($className)) {
                class_alias($orig, $className);
            }
            return $res;
        }
        
        $fileLoaded = false;
        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $className);
        $fileName = str_replace('_', DIRECTORY_SEPARATOR, $fileName).'.php';
        $classDir = dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        $f = $classDir.$fileName;
        $fileLoaded = false;
        $res = false;
        if (is_file($f)) {
            require($f);
            $res = true;
        } else {
            $p = self::getSafeIncludePath();
            foreach ($p as $dir) {
                if (is_file($f = $dir.DIRECTORY_SEPARATOR.$fileName)) {
                    require($f);
                    $res = true;
                    break;
                }
            }
        }
        if ($res && !class_exists($className, false)) $res = false;
        if ($res && isset(self::$revClassAliases[$className])) {
            foreach (self::$revClassAliases[$className] as $alias) if (!class_exists($alias, false)) {
                class_alias($className, $alias);
            }
        }
        return $res;
    }
    
    static function registerAutoload($addIncludePath = false) {
        $res = false;
        if (self::$autoLoadRegistered === null) {
            $f = spl_autoload_functions();
            $cb = array('Ac_Util', 'loadClass');
            if (!is_array($f) || !in_array($cb, $f)) { 
                spl_autoload_register($cb);
                $res = true;                
                if (function_exists('__autoload')) {
                    spl_autoload_register('__autoload');
                }
            }
            self::$autoLoadRegistered = $res;
        } else {
            $res = self::$autoLoadRegistered;
        }
        if ($addIncludePath) self::addIncludePath();
        return $res;
    }
    
    static function registerClassAliases(array $classAliases) {
        foreach ($classAliases as $alias => $orig) {
            self::$classAliases[$alias] = $orig;
            self::$revClassAliases[$orig][] = $alias;
        }
    }
    
    static function implementsInterface($classOrObject, $interfaceName) {
        static $cache = array();
        
        $res = false;
        if (!is_object($classOrObject)) {
            if (class_exists($classOrObject, true) && interface_exists($interfaceName, true)) {
                if (!isset($cache[$classOrObject])) {
                    $r = new ReflectionClass($classOrObject);
                    $cache[$classOrObject] = $r->getInterfaceNames();
                }
                $res = in_array($interfaceName, $cache[$classOrObject]);
            }
        } else {
            $res = $classOrObject instanceof $interfaceName;
        }
        return $res;
    }
    
    static function m($paArray1, $paArray2, $preserveNumeric = false) {
        if (!is_array($paArray1) || !is_array($paArray2)) { return $paArray2; }
        if (!$paArray1) return $paArray2;
        if (!$paArray2) return $paArray1;
        foreach ($paArray2 AS $sKey2 => $sValue2) {
            if (is_int($sKey2) && !$preserveNumeric) {
                $paArray1[] = $sValue2;
            }
            else {
                if (!isset($paArray1[$sKey2])) $paArray1[$sKey2] = null;
                $paArray1[$sKey2] = self::m($paArray1[$sKey2], $sValue2, $preserveNumeric);
            }
        }
        return $paArray1;
    }

    static function ms(& $paArray1, $paArray2, $preserveNumeric = false) {
        return $paArray1 = self::m($paArray1, $paArray2, $preserveNumeric);
    }

    static function array_merge_recursive2($paArray1, $paArray2, $preserveNumeric  = false) {
        return self::m($paArray1, $paArray2, $preserveNumeric);
    }
    
    static function mkAttribs ($attribs = array(), $quote='"', $quoteStyle = ENT_QUOTES, $charset = false, $doubleEncode = true, $addSpace = true) {
        if (!$attribs) return "";
        if (isset($attribs['style']) && is_array($attribs['style'])) {
            $style = array();
            foreach ($attribs['style'] as $k => $v) $style[] = $k.": ".$v;
            $attribs['style'] = implode('; ', $style);
        }
        if ($charset === false) $charset = AC_UTIL_DEFAULT_CHARSET;
        $res = array();
        foreach ($attribs as $k => $v) {
            if (is_bool($v)) {
                if (!$v) continue;
                else $v = $k;
            }
            $res[] = $k."=".$quote.self::htmlspecialchars($v, $quoteStyle, $charset, $doubleEncode).$quote;
        }
        if ($res && $addSpace) array_unshift ($res, '');
        return implode(" ", $res);
    }
    
    /**
     * Important: tagBody and attribs can be swapped (for better, HTML-like readability)
     */
    static function mkElement($tagName, $tagBody = false, $attribs = null, $quote='"', $quoteStyle = ENT_QUOTES, $charset = false, $doubleEncode = true) {
        $res = '<'.$tagName;
        if (is_array($tagBody) && !is_array($attribs)) {
            list($attribs, $tagBody) = array($tagBody, $attribs);
        }
        if ($attribs) $res .= self::mkAttribs($attribs, $quote, $quoteStyle, $charset, $doubleEncode = true);
        if ($tagBody !== false && $tagBody !== null) $res .= '>'.$tagBody.'</'.$tagName.'>';
            else $res .= ' />';
        return $res;
    }
    
    static function getEmailRx() {
        return Ac_Mail_Util::getEmailRegex();
    }

    static function implodeRecursive($glue, $array, $array_name = NULL) {
        if (is_string($array)) return $array;
        if (is_array($glue)) {
            $impl = array_shift($glue);
            if (!$glue) $glue = $impl;
        } else $impl = $glue;
        $return = array();
        foreach ($array as $key => $value) {
            if(is_array($value)) $return[] = self::implodeRecursive($glue, $value, (string) $key);
            else
                $return[] = $value;
        }
        return(is_array($return)? implode($impl, $return) : $return);
    }

    /**
     * @deprecated
     */
    static function implode_r($glue, $array, $array_name = NULL) {
        return self::implodeRecursive($glue, $array, $array_name);
    }

    static function date ($src, $format = null, $useGmt = false, & $wasZeroDate = false) {
        return Ac_Model_DateTime::date($src, $format, $useGmt, $wasZeroDate);
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
        self::createDirPath($dir, $rights);
        $handle = fopen($filePath, "w");
        return $handle;
    }

    static function listDirContents($dirPath, $recursive = false, $files = array(), $fileRegex = false, $dirRegex = false, $includeDirs = false) {
        if(!($res = opendir($dirPath))) trigger_error("$dirPath doesn't exist!", E_USER_ERROR);
        while($file = readdir($res)) {
            if($file != "." && $file != "..") {
                if(($dir = is_dir("$dirPath/$file")) && $recursive && (!$dirRegex || preg_match($dirRegex, "$dirPath/$file"))) {
                    if ($includeDirs) array_push($files, "$dirPath/$file");
                    $files = self::listDirContents("$dirPath/$file", $recursive, $files, $fileRegex, $dirRegex);
                } else {
                    if (!$dir || $includeDirs) {
                        if (!$fileRegex || preg_match($fileRegex, "$dirPath/$file")) {
                            array_push($files,"$dirPath/$file");
                        }
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
        if (is_array($array) && $c = count($array)) {
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
        $array = array_reverse(self::pathToArray($path));
        if ($c = count($array)) {
            $tail = $array[$c - 1];
            $body = self::arrayToPath(array_slice($array, 0, $c - 1));    
        } else $body = $tail = '';
        return array($body, $tail);
    }
    
    static function concatPaths ($path1, $path2) {
        if (is_array($path2)? !count($path2) : !strlen($path2)) return $path1;
        if (is_array($path1)? !count($path1) : !strlen($path1)) return $path2;
        $arr2 = self::pathToArray($path2);
        $res = $path1.'['.implode('][', $arr2).']';
        return $res; 
    }
    
    static function concatManyPaths ($path1, $path2) {
        $res = array();
        if (is_array($path1)) {
            foreach ($path1 as $p1) $res = array_merge($res, self::concatManyPaths($p1, $path2));        
        } 
        elseif (is_array($path2)) {
            foreach ($path2 as $p2) $res = array_merge($res, self::concatManyPaths($path1, $p2));
        } else {
            $res[] = self::concatPaths($path1, $path2);
        }
        return $res;
    }
    
    static function getObjectProperty($object, $property, $defaultValue = false, $treatArraysAsObjects = false) {
        $res = Ac_Accessor::getObjectProperty($object, $property, $defaultValue, $treatArraysAsObjects);
        return $res;
    }

    static function stripSlashes($value ) {
        $res = '';
        if (is_string( $value )) {
            $res = stripslashes( $value );
        } else {
            if (is_array( $value )) {
                $res = array();
                    foreach ($value as $key => $val) {
                        $res[$key] = self::stripSlashes( $val );
                    }
                } else {
                    $res = $value;
            }
        }
        return $res;
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
    static function getArrayByPath($arr, $arrPath, $defaultValue = null, & $found = false) {
        $res = Ac_Util::getArrayByPathRef($arr, $arrPath, $defaultValue, $found);
        return $res;
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
        if (!is_array($path)) $path = self::pathToArray($path);
        return self::getArrayByPath($arr, $path, $defaultValue);
    }
    
    /**
     * Sets element of nested arrays using specified set of keys.
     * 
     * If $arrPath is array('foo', 'bar'), $arr['foo']['bar'] will be set to $value. 
     * If $arrPath is array('foo', 'bar', ''), $arr['foo']['bar'][] will be set to $value. 
     */
    static function setArrayByPath(& $arr, $arrPath, $value, $unique = true) {
        return Ac_Util::setArrayByPathRef($arr, $arrPath, $value, $unique);
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
        if (!is_array($arrPath)) {
            unset($arr[$arrPath]);
            return true;
        }
        
        if (!is_array($arr)) {
            return false;
        }
        
        if (($c = count($arrPath)) <= 5) {
            if ($c === 1) {
                unset($arr[$arrPath[0]]);
                return true;
            }
            
            if ($c === 2) {
                if (isset($arr[$arrPath[0]]) 
                && is_array($arr[$arrPath[0]]) 
                ) {
                    unset($arr[$arrPath[0]][$arrPath[1]]);
                    return true;
                } else return false;
            }

            if ($c === 3) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                ) {
                    unset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]]);
                    return true;
                } else return false;
            }
            
            if ($c === 4) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                ) {
                    unset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]]);
                    return true;
                } else return false;
            }
            if ($c === 5) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]])
                ) {
                    unset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]]);
                    return true;
                } else return false;
            }
        }
        
        $src = & $arr;
        while ($arrPath) {
            $key = array_shift($arrPath);
            if (is_array($src) && isset($src[$key])) {
                if ($arrPath) $src = & $src[$key];
                else {
                    unset($src[$key]);                  
                }
            } else return false;
        }
        return true;
    }
    
    static function setArrayByPathRef(& $arr, $arrPath, & $value, $unique = true) {
        if (!is_array($arrPath)) $arrPath = array($arrPath);
        $c = count($arrPath);
        if ($c && (($l = $arrPath[$c - 1]) === '' || $l === null)) {
            $unique = false;
            unset($arrPath[--$c]);
        }
        if ($c <= 5) {
            
            // PHP will throw fatal error if we will try to do $foo[$bar][$baz] = & $quux when $foo[$bar] is a string. 
            // So we need to convert it to array first.
            
            if ($c > 0) {
                if (!is_array($arr)) {
                    $arr = array();
                } else if ($c > 1 && isset($arr[$arrPath[0]])) {
                    if (!is_array($arr[$arrPath[0]])) {
                        $arr = array();
                    } elseif ($c > 2 && isset($arr[$arrPath[0]][$arrPath[1]])) {
                        if (!is_array($arr[$arrPath[0]][$arrPath[1]])) {
                            $arr[$arrPath[0]][$arrPath[1]] = array();
                        } elseif ($c > 3 && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])) {
                            if (!is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])) {
                                $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]] = array();
                            } elseif ($c > 4 && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])) {
                                if (!is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])) {
                                    $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]] = array();
                                }
                            }
                        }
                    }
                }
            }
            
            if ($unique) {
                if ($c === 0) $arr = $value;
                if ($c === 1) $arr[$arrPath[0]] = & $value;
                if ($c === 2) $arr[$arrPath[0]][$arrPath[1]] = & $value;
                if ($c === 3) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]] = & $value;
                if ($c === 4) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]] = & $value;
                if ($c === 5) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]] = & $value;
                return;
            } else {
                if ($c === 0) $arr[] = & $value;
                if ($c === 1) $arr[$arrPath[0]][] = & $value;
                if ($c === 2) $arr[$arrPath[0]][$arrPath[1]][] = & $value;
                if ($c === 3) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][] = & $value;
                if ($c === 4) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][] = & $value;
                if ($c === 5) $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]][] = & $value;
                return;
            }
        }
        
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

    /**
     * @deprecated since version 0.3.4
     * Use setArrayByPath() instead
     */
    static function simpleSetArrayByPath(& $arr, $arrPath, & $value, $unique = true) {
        trigger_error(__METHOD__." is deprecated and will be removed in 0.3.5", E_USER_DEPRECATED);
        $res = self::setArrayByPath($arr, $arrPath, $value);
        return $res;
    }
    
    /**
     * @deprecated since version 0.3.4
     * Use setArrayByPathRef() instead
     */
    static function simpleSetArrayByPathNoRef(& $arr, $arrPath, $value, $unique = true) {
        trigger_error(__METHOD__." is deprecated and will be removed in 0.3.5", E_USER_DEPRECATED);
    	return self::setArrayByPathRef($arr, $arrPath, $value, $unique);
    }
    
    /**
     * @deprecated since version 0.3.4
     * Use getArrayByPathRef() instead
     */
    static function & simpleGetArrayByPath($arr, $arrPath, $defaultValue = null, & $found = false) {
        trigger_error(__METHOD__." is deprecated and will be removed in 0.3.5", E_USER_DEPRECATED);
        $res = self::getArrayByPathRef($arr, $arrPath, $defaultValue, $found);
        return $res;
    }
    
    static function & getArrayByPathRef(& $arr, $arrPath, $defaultValue = null, & $found = false) {
        
        $found = true;
        
        if (!is_array($arrPath)) {
            if (isset($arr[$arrPath])) return $arr[$arrPath]; 
            else {
                $found = false;
                return $defaultValue;
            }
        }
        $c = count($arrPath);
        
        // optimize small-sized arrays
        if ($c <= 5) {
            if ($c === 0) return $arr;
            
            if (!is_array($arr)) {
                $found = false;
                return $defaultValue;
            }
            
            if ($c === 1) {
                if (isset($arr[$arrPath[0]])
                ) return $arr[$arrPath[0]];
                else {
                    $found = false;
                    return $defaultValue;
                }
            }
            
            if ($c === 2) {
                if (isset($arr[$arrPath[0]]) 
                && is_array($arr[$arrPath[0]]) 
                && isset($arr[$arrPath[0]][$arrPath[1]])
                ) return $arr[$arrPath[0]][$arrPath[1]];
                else {
                    $found = false;
                    return $defaultValue;
                }
            }

            if ($c === 3) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                ) return $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]];
                else {
                    $found = false;
                    return $defaultValue;
                }
            }
            
            if ($c === 4) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                ) return $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]];
                else {
                    $found = false;
                    return $defaultValue;
                }
            }
            if ($c === 5) {
                if (isset($arr[$arrPath[0]]) 
                    && is_array($arr[$arrPath[0]]) 
                    && isset($arr[$arrPath[0]][$arrPath[1]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                    && is_array($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]])
                    && isset($arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]])
                ) return $arr[$arrPath[0]][$arrPath[1]][$arrPath[2]][$arrPath[3]][$arrPath[4]];
                else {
                    $found = false;
                    return $defaultValue;
                }
            }
        }
        
        $src = & $arr;
        $arrPath = array_reverse($arrPath);
        while ($arrPath) {
            $key = array_pop($arrPath);
            if (is_array($src) && isset($src[$key])) $src = & $src[$key];
            else {
                $found = false;
                return $defaultValue;
            }
        }
        return $src;
    }

    /**
     * @deprecated Use Ac_Util::makeCsvLine
     */
    static function getCsvLine($line, $delimiter=",", $enclosure="\"", $addNewLine = true, $forceText = false) {
        return self::makeCsvLine($line, $delimiter, $enclosure, $addNewLine,  $forceText);
    }
    
    static function makeCsvLine($line, $delimiter=";", $enclosure="\"", $addNewLine = true, $forceText = false) {
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
    
    static function makeCsvTable(array $twoDim, $delimiter = ";", $enclosure = "\"", $newLineChar = PHP_EOL) {
        $res = '';
        if (!$twoDim) return $res;
        $twoDim = array_values(Ac_Util::unifyArray($twoDim, ''));
        $res .= self::makeCsvLine(array_keys($twoDim[0]), $delimiter, $enclosure, $newLineChar);
        foreach ($twoDim as $row) $res .= self::makeCsvLine($row, $delimiter, $enclosure, $newLineChar);
        return $res;
    }
    
    static function d($s) {
        echo '<pre>'.htmlspecialchars(print_r($s, 1)).'</pre>';
    }
    
    static function array_values($array) {
        foreach ($array as $v) $res[] = $v;
        return $res;
    }
    
    static function stripTrailingSlash($string, $slash = '/\\') {
        return self::removeTrailingSlash($string, $slash);
    }
    
    static function flattenArray($array, $level = -1, $keyGlue = false, $key = '', $keySuffix = '') {
        //if (!is_array($array)) return array($array);
        $res = array();
        foreach ($array as $k => $v) {
            if (strlen($keyGlue)) {
                $tk = strlen($key)? $key.$keyGlue.$k.$keySuffix : $k;
            } else {
                $tk = false;
            }
            if (is_array($v) && ($level != 0)) $res = array_merge($res, self::flattenArray($v, $level-1, $keyGlue, $tk, $keySuffix));
            elseif (strlen($tk)) {
                $res[$tk] = & $array[$k];
            } else {
                $res[] = & $array[$k];
            }
        }
        return $res;
    }
    
    /**
     * @return bool True if $object1 and $object2 are both references to the same object
     */
    static function sameObject(& $object1, & $object2) {
        return $object1 === $object2;
    }
    
    static function array_unique($arr, $strict = false) {
        $res = array();
        foreach ($arr as $i) if (!in_array($i, $res, $strict)) $res[] = $i;
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
            if (self::sameObject($obj, $array[$k])) {
                $res = true;
                break;
            }
        }
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
                   self::_groupFileInfoByVariable($top[$var], $val, $attr);
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
    
    /**
     * @param object $object
     * @return array (property => value)
     */
    static function getPublicVars($object) {
        // Workaround to change scope to skip using Reflection
        $g = new _Ac_Util_ObjectVarGetter();
        return $g->getObjectVars($object);
    }
    
    /**
     * @param object $object
     * @return array
     */
    static function getPublicMethods($object) {
        // Workaround to change scope to skip using Reflection
        $g = new _Ac_Util_ObjectVarGetter();
        return $g->getObjectMethods($object);
    }
    
    static function isMethodOverridden($class, $baseClass, $method = '') {
        static $cache = array();
        $cid = "{$class}/{$baseClass}/{$method}";
        if (isset($cache[$cid])) return $cache[$cid];
        $refClass = new ReflectionClass($class);
        $allOverridden = array();
        foreach ($refClass->getMethods() as $m) {
            $methodName = $m->getName();
            $r = $m->getDeclaringClass()->getName();
            $isOverridden = ($r !== $baseClass);
            $cache["{$class}/{$baseClass}/{$methodName}"] = $isOverridden;
            if ($isOverridden) $allOverridden[] = $methodName;
        }
        $cache["{$class}/{$baseClass}/"] = $allOverridden;
        if (!strlen($method)) $isOverridden = $allOverridden;
        if (!isset($cache[$cid])) {
            throw Ac_E_InvalidCall::noSuchMethod($class, $method);
        }
        $isOverridden = $cache[$cid];
        return $isOverridden;
    }
    
    static function htmlEntityDecode($stringOrArray, $quoteStyle = ENT_QUOTES, $charset = null) {
        if (is_array($stringOrArray)) {
            $res = array();
            foreach ($stringOrArray as $k => $v) $res[$k] = self::htmlEntityDecode($v, $quoteStyle, $charset);
            return $res;
        }
        return html_entity_decode($stringOrArray, $quoteStyle, $charset);
    }
        
    static function htmlspecialchars($string, $quote_style = ENT_QUOTES, $charset = null, $double_encode = true)
    {
        
        if (!strlen($charset)) $charset = null;
        if ($double_encode) return htmlspecialchars($string, $quote_style, $charset);
        return htmlspecialchars($string, $quote_style, $charset, $double_encode);
    }
    
    static function addTrailingSlash($string, $slashes = '/\\', $slashType = DIRECTORY_SEPARATOR) {
        $res = self::removeTrailingSlash($string, $slashes).$slashType;
        return $res;
    }
    
    static function addLeadingSlash($string, $slashes = '/\\', $slashType = DIRECTORY_SEPARATOR) {
        $res = $slashType.self::removeLeadingSlash($string, $slashes);
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
    		if (count($path)) self::setArrayByPathRef($res, $path, $item, $unique);
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
    
    static function typeClass($item) {
        $res = gettype($item);
        if (is_object($item)) $res .=  ' '.get_class($item);
        return $res;
    }
    
    static function getClassConstants($class, $prefix = false) {
        $r = new ReflectionClass($class);
        $res = $r->getConstants();
        if ($prefix !== false) {
            $l = strlen($prefix);
            foreach ($res as $k => $v) if (strncmp($k, $prefix, $l)) unset($res[$k]);
        }
        return $res;
    }
    
    /**
     * Indexes array by keys and optionally replaces values by subset of them
     * 
     * Ac_Util::indexArray([['foo' => 'a'], ['foo' => 'b']], 'foo', true) 
     *  will return ['a' => ['foo' => 'a'], 'b' => ['foo' => 'b']]
     * 
     * Ac_Util::indexArray([['foo' => 'a'], ['foo' => 'b']], 'foo', false) 
     *  will return ['a' => [['foo' => 'a']], 'b' => [['foo' => 'b']]]
     * 
     * Ac_Util::indexArray([['foo' => 'a', 'bar' => 'b'], ['foo' => 'c', 'bar' => 'd'], ['foo', 'bar'], true)
     *  will return ['a' => ['b' => ['foo' => 'a', 'bar' => 'b']], 'c' => ['d' => ['foo' => 'c', 'bar' => 'd']]]
     * 
     * Ac_Util::indexArray([['foo' => 'a', 'bar' => 'b'], ['foo' => 'c', 'bar' => 'd'], 'foo', true, 'bar')
     *  will return array('a' => 'b', 'c' => 'd')
     * 
     * Ac_Util::indexArray([
     *          ['foo' => 'a', 'bar' => 'b', 'baz' => 'c'], 
     *          ['foo' => 'd', 'bar' => 'e', 'baz' => 'f'], 
     *  ], 'foo', true, array('bar', 'baz')) 
     *  will return ['a' => ['bar' => 'b', 'baz' => 'c'], ['bar' => 'e', 'baz' => 'f']]
     * 
     * @param array $flatArray Original array of members: arrays or objects
     * @param scalar|array $keyList Keys to index array
     * @param bool unique whether last level will be exact member of original list or array of members
     * @param false|scalar|array $valueKeys if provided, members will be replaced by extracted keys' or
     *      properties' values before placing into result array (scalar $valueKeys means sclar result member, 
     *      array $valueKeys means always array result member)
     * @param bool $retainOriginalKey when $unique === false, elements on deepest level of result array will have keys of items in $flatArray
     * 
     */
    static function indexArray(array $flatArray, $keyList, $unique = false, $valueKeys = false, $retainOriginalKey = false) {
        $res = array();
        if (is_array($keyList) && count($keyList) == 1) $keyList = array_shift($keyList);
        if (!is_array($keyList)) {
            // simple version
            foreach ($flatArray as $ok => $v) {
                if (is_object($v)) {
                    $k = Ac_Accessor::getObjectProperty($v, $keyList);
                }
                else {
                    $k = isset($v[$keyList])? $v[$keyList] : null;
                }
                if ($valueKeys !== false && !is_null($valueKeys))
                    $v = Ac_Accessor::getObjectProperty($v, $valueKeys, null, true);
                
                if ($unique) $res[$k] = $v;
                elseif ($retainOriginalKey) $res[$k][$ok] = $v; 
                else $res[$k][] = $v;
            }
        } else {
            foreach ($flatArray as $ok => $v) {
                if (is_object($v)) {
                    $path = array_values(Ac_Accessor::getObjectProperty($v, $keyList));
                }
                else {
                    $path = array();
                    foreach ($keyList as $i) if (isset($v[$i])) $path[] = $v[$i];
                        else $path[] = null;
                }
                if ($valueKeys !== false && !is_null($valueKeys))
                    $v = Ac_Accessor::getObjectProperty($v, $valueKeys, null, true);
                if (!$unique && $retainOriginalKey) {
                    $path[] = $ok;
                    Ac_Util::setArrayByPath ($res, $path, $v, true);
                } else {
                    Ac_Util::setArrayByPath ($res, $path, $v, $unique);
                }
            }
        }
        return $res;
    }
    
    static function lcFirst($string) {
        if (strlen($string)) $string[0] = strtolower($string[0]);
        return $string;
    }
    
    /**
     * Note: detects slash-based ("/") regexes only!
     */
    static function isRegex($string) {
        return strlen($string) > 2 && $string[0] == '/' && preg_match("#^/.+/\\w*$#", $string);
    }
    
    /**
     * Makes all keys in 2-dimensional array present and go in the same order. Sets missing values to $defaultValue
     */
    static function unifyArray($rows, $defaultValue = null, array $perColumnDefaults = null) {
        $allKeys = array();
        foreach ($rows as $row) {
            foreach (array_keys($row) as $k) $allKeys[$k] = $k;
        }
        $res = array();
        foreach ($rows as $k => $row) {
            $newRow = array();
            foreach ($allKeys as $key) {
                if (array_key_exists($key, $row)) $newRow[$key] = $row[$key];
                    elseif ($perColumnDefaults && array_key_exists($key, $perColumnDefaults)) {
                        $newRow[$key] = $perColumnDefaults[$key];
                    }
                    else $newRow[$key] = $defaultValue;
            }
            $res[$k] = $newRow;
        }
        return $res;
    }
    
}

class _Ac_Util_ObjectVarGetter {
    
    function getObjectVars($foo) {
        if (is_object($foo)) {
            return get_object_vars($foo);
        } else {
            return get_class_vars($foo);
        }
    }
    
    function getObjectMethods($foo) {
        if (is_object($foo)) $class = get_class($foo);
            else $class = $foo;
        return get_class_methods($class);
    }
    
}

/**
 * Used to register as unserialize handler (some version of PHP didn't allow to provide a static call)
 * @param type $className 
 */
function acUtilLoadClass($className) {
    Ac_Util::loadClass(Ac_Util::fixClassName($className));
}

if (!function_exists('is_countable')) {
    
    function is_countable($foo) {
        return is_array($foo) || is_object($foo) && $foo instanceof Countable;
    }
    
}
