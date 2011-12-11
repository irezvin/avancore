<?php

class Ae_Controller_Filter {

    function getIntArray($src, $path, $defaultValue = array(), $preserveKeys = false, $recursive = false, $makeArray = false) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if (!is_array($data)) $data = $makeArray? array($data) : false;
        if ($data !== false) {
            if (is_array($data)) {
                $res = array();
                foreach ($data as $k => $v) {
                    if (!$preserveKeys) $key = count($res);
                        else $key = $k;
                    if (is_numeric($v) && (intval($v) == $v)) {
                        $res[$key] = intval($v);
                    } elseif ($recursive && is_array($v)) {
                        $subRes = $this->getIntArray($data, $k, $defaultValue, $preserveKeys, $recursive, $makeArray);
                        if (is_array($subRes) && count($subRes) || is_numeric($subRes) && (intval($subRes) == $subRes)) {
                            $res[$key] = $subRes;
                        }
                    }
                }
            } else {
                $res = $defaultValue;
            }
        } else $res = $defaultValue;
        return $res;
    }
    
    function getInt($src, $path, $defaultValue = null) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if ($data !== false) {
            if (is_numeric($data) && intval($data) == $data) $res = intval($data); 
                else $res = $defaultValue;
        } else $res = $defaultValue;
        return $res;
    }
    
    function getDate($src, $path, $defaultValue = null, $format = null) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if ($data !== false) {
            if (strlen($d = Ae_Util::date($data, $format))) $res = $d; 
                else $res = $defaultValue;
        } else $res = $defaultValue;
        return $res;
    }
    
    function getOneOf($src, $path, $values, $defaultValue = null, $trim = true) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if ($data !== false) {
            if ($trim) $data = trim($data);
            if (in_array($data, $values)) 
                $res = $data; 
            else
                $res = $defaultValue;
        } else $res = $defaultValue;
        return $res;
    }
    
    function getSomeOf($src, $path, $values, $defaultValue = array(), $trim = true, $preserveKeys = false, $allowDuplicates = false, $makeArrayIfOneElement = false) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if (!is_array($data)) {
            if ($makeArrayIfOneElement) $data = array($data);
            else $data = false;
        }
        if ($data !== false) {
            $arr = array();
            foreach ($data as $k => $v) {
                $key = $preserveKeys? $k : count($arr);  
                if ($trim) $v = trim($v);
                if (in_array($v, $values)) 
                    $arr[$key] = $v; 
            }
            $res = $allowDuplicates? $arr : array_unique($arr);
        } else $res = $defaultValue;
        return $res;
    }
    
    function getBool($src, $path, $defaultValue = false) {
        $data = Ae_Util::getArrayByPath($src, Ae_Util::pathToArray($path), false);
        if ($data !== false) {
            $res = $data != 0;
        } else $res = $defaultValue;
        return $res;
    }
    
    function filterRequestArray($array, $stripTags = true) {
        $res = array();
        $hasToStripSlashes = get_magic_quotes_gpc();
        foreach ($array as $k => $v) {
            if (is_scalar($v)) {
                $res[$k] = $stripTags? strip_tags($res) : true;
            } elseif (is_array($v)) {
                $res[$k] = $this->filterRequestArray($array, $stripTags);
            }
        }
    }
    
}

?>