<?php
/**
 * Static class that contains facilies to work with paths within hierarchical objects' compositions.
 *  
 * @package Avancore
 * @subpackage Params
 * @static
 */
class Ac_Param_Impl_Paths {

    /**
     * Since the class is static, this method *always* throws an exception.
     */
    function Ac_Param_Impl_Paths() {
        trigger_error("Attempt to instantiate static class", E_USER_ERROR);
    }
    
    /**
     * @static
     * @param string $strPath
     * @return string
     */
    function getPath(& $item, $asString = false, $id = 'id', $parent = 'getParent') {
        $res = array();
        $curr = & $item;
        while (($p = & $curr->$parent)) {
            $res = array_merge(array($curr->$id), $res);
            $curr = & $p;
        }
        $res = array_merge('', $res);
        return $res;
    }
    
    /**
     * @static
     * @param string $strPath
     * @return string
     */
    function pathToArray($strPath) {
        if (!is_array($path)) $res = explode('/', $strPath);
            else $res = $strPath;
        return $res;
    }
    
    /**
     * @static
     * @param $arrPath
     * @return unknown_type
     */
    function pathToString($arrPath) {
        if (!is_array($arrPath)) $res = $arrPath;
            else $res = implode('/', $arrPath);
        return $res;
    }
    
    /**
     * @static
     * @param $item
     * @param $parent
     * @return unknown_type
     */
    function & getRoot(& $item, $parent = '_parent') {
        $res = & $item;
        while ($p = & $res->$parent) $res = & $p;
        return $res; 
    }
    
    /**
     * @static
     * @param $item
     * @param $path
     * @param $parent
     * @param $getChildMethod
     * @param $hasChildMethod
     * @return unknown_type
     */
    function & getByPath(& $item, $path, $parent = '_parent', $getChildMethod, $hasChildMethod = false) {
        $path = Param_Impl_Paths::pathToArray($path);
        $curr = & $item;
        $res = false;
        while ($curr && count($path)) {
            $segment = $path[0];
            $path = array_splice($path, 0, 1);
            if (!strlen($segment)) {
                $curr = & Param_Impl_Paths::getRoot($item, $parent);
            } elseif ($segment === '..') {
                $curr = & $curr->$parent;
            } else {
                if (strlen($hasChildMethod))
                    $hasChild = $curr->$hasChildMethod($segment); 
                else
                    $hasChild = true;
                if ($hasChild) {
                    $curr = & $curr->$getChildMethod($segment);
                } else {
                    $curr = null;
                }
            }
        }
        if (!count($path) && $curr) $res = & $curr;
        return $res;
    }
    
}

?>