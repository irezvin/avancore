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
    
    var $processingParams = null;
    
    var $managerParams = null;
    
    /**
     * @var Ac_Admin_Manager
     */
    protected $manager = false;

    function setManager(Ac_Admin_Manager $manager) {
        $this->manager = $manager;
    }

    /**
     * @return Ac_Admin_Manager
     */
    function getManager() {
        return $this->manager;
    }
    
    static function factory($options = array()) {
        if (!is_array($options)) trigger_error ('$options must be an array, E_USER_ERROR');
        if (isset($options['class'])) $class = $options['class'];
            else $class = 'Ac_Admin_Action';
        $res = new $class ($options);
        return $res;
    }
    
    function Ac_Admin_Action ($options) {
        if (isset($options['manager'])) $this->setManager($options['manager']);
        Ac_Util::simpleBind($options, $this);
    }
    
    function getJson() {
        
        $cs = $this->manager->getConfigService();
        
        $res = array();
        $imagePrefix = $cs->getImagePrefix();
        foreach (array_keys(Ac_Util::getPublicVars($this)) as $k) {
            if ($k{0} != '_' && is_object($this->$k) || is_array($this->$k) || is_scalar($this->$k) && strlen($this->$k)) $res[$k] = $this->$k;
        }
        
        if ($this->manager->getMethodName() === 'executeDetails' && $this->managerProcessing) {
            $proc = $this->manager->getProcessing($this->managerProcessing);
            if ($proc->returnToDetails) {
                $res['managerParams']['returnUrl'] = ''.$this->manager->getManagerUrl();
            }
        }
        
        $kind = $this->kind === false? $this->id : $this->kind;
        $images = $cs->getToolbarImagesMap($kind);
        
        foreach (array('image', 'hoverImage', 'disabledImage') as $k) {
            if ($this->$k === false && isset($images[$k])) $res[$k] = $images[$k];
            if (isset($res[$k]) && strlen($res[$k]) && !preg_match('#^http(s?)://#', $this->$k)) {
                $res[$k] = $imagePrefix.($res[$k]);
                $res[$k] = $this->unfoldAssetString($res[$k]);
            }
        }
        
        return $res;
    }
    
    function unfoldAssetString($string) {
        // Ugly...
        return Ac_Legacy_Controller_Response_Html::unfoldAssetString($string, $this->manager->getApplication()->getAssetPlaceholders());
    }
    
}

