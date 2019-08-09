<?php

abstract class Ac_Cache_Null extends Ac_Cache_Abstract {

    var $enabled = false;

    function implHas($id, $group, & $howOld, $lifetime, & $filename) {
        return false;
    }
    
    function implGet($id, $group, $default, $evenOld) {
        return null;
    }
    
    function implPut($id, $content, $group) {
        return;
    }

    function implDelete($id, $group) {
        
    }
    
    function deleteAll() {
        
    }
    
}
