<?php

class Ac_Cache_Memory extends Ac_Cache_Abstract {
    
    var $data = array();
    
    protected function implHas($id, $group, & $howOld, $lifetime, & $filename) {
        if (!isset($this->data[$group.'/'.$id]) || !isset($this->data[$group][$id]))
            return false;
        $howOld = time() - $this->data[$group][$id][0];
        if ($lifetime === false) $lifetime = $this->lifetime;
        if ($lifetime && $howOld > $lifetime) return false;
        return true;
    }
    
    protected function implGet($id, $group, $default, $evenOld) {
        if (!isset($this->data[$group]) || !isset($this->data[$group][$id]))
            return $default;
        $howOld = time() - $this->data[$group][$id][0];
        if (!$evenOld && $this->lifetime && $howOld > $this->lifetime)
            return $default;
        return $this->data[$group][$id][1];
    }
    
    protected function implPut($id, $content, $group) {
        $this->data[$group][$id] = array(time(), $content);
    }

    protected function implDelete($id, $group) {
        if (!isset($this->data[$group])) return false;
        if (!isset($this->data[$group][$id])) return false;
        unset($this->data[$group][$id]);
        return null;
    }
    
    function deleteAll() {
        $this->data = array();
    }
    
}
