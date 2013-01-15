<?php

class Ac_Admin_Action {
    
    var $id = false;
    
    var $caption = false;
    
    var $description = false;
    
    var $image = false;
    
    var $hoverImage = false;
    
    var $disabledImage = false;
    
    var $confirmationText = false;
    
    var $scope = 'some';
    
    var $needDialog = false;
    
    var $formOnly = false;
    
    var $listOnly = false;
    
    var $managerAction = false;
    
    var $managerProcessing = false;
    
    var $kind = false;
    
    function & factory($options = array()) {
        if (!is_array($options)) trigger_error ('$options must be an array, E_USER_ERROR');
        if (isset($options['class'])) $class = $options['class'];
            else $class = 'Ac_Admin_Action';
        $res = new $class ($options);
        return $res;
    }
    
    function Ac_Admin_Action ($options) {
        Ac_Util::simpleBind($options, $this);
    }
    
    function getJson() {
        
        $adapter = Ac_Dispatcher::getInstance()->adapter;
        
        $res = array();
        $imagePrefix = $adapter->getImagePrefix();
        foreach (array_keys(get_object_vars($this)) as $k) {
            if ($k{0} != '_' && strlen($this->$k)) $res[$k] = & $this->$k;
        }
        
        $kind = $this->kind === false? $this->id : $this->kind;
        $images = $adapter->getToolbarImagesMap($kind);
        
        foreach (array('image', 'hoverImage', 'disabledImage') as $k) {
            if ($this->$k === false && isset($images[$k])) $res[$k] = $images[$k];
            if (isset($res[$k]) && strlen($res[$k]) && !preg_match('#^http(s?)://#', $this->$k)) {
                $res[$k] = $imagePrefix.($res[$k]);
                $res[$k] = $adapter->unfoldAssetString($res[$k]);
            }
        }
        
        return $res;
    }
    
}

?>