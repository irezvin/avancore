<?php

class Ae_Admin_Action {
    
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
    
    function & factory($options = array()) {
        if (!is_array($options)) trigger_error ('$options must be an array, E_USER_ERROR');
        if (isset($options['class'])) $class = $options['class'];
            else $class = 'Ae_Admin_Action';
        Ae_Dispatcher::loadClass($class);
        $res = new $class ($options);
        return $res;
    }
    
    function Ae_Admin_Action ($options) {
        Ae_Util::simpleBind($options, $this);
    }
    
    function getImagePrefix() {
        $imagePrefix = 'images';
        $conf = Ae_Dispatcher::getInstance()->config;
        if (isset($conf->managerImagesUrl) && strlen($u = $conf->managerImagesUrl)) {
            $imagePrefix = $conf->managerImagesUrl;
        }
        $imagePrefix = rtrim($imagePrefix, '/').'/';
        return $imagePrefix;
    }
    
    function getJson() {
        $res = array();
        $imagePrefix = $this->getImagePrefix();
        foreach (array_keys(get_object_vars($this)) as $k) {
            if ($k{0} != '_' && strlen($this->$k)) $res[$k] = & $this->$k;
        }
        foreach (array('image', 'hoverImage', 'disabledImage') as $k) {
            if (strlen($this->$k) && !preg_match('#^http(s?)://#', $this->$k)) {
                $res[$k] = $imagePrefix.($this->$k);
                $res[$k] = Ae_Dispatcher::getInstance()->adapter->unfoldAssetString($res[$k]);
            }
        }
        return $res;
    }
    
}

?>