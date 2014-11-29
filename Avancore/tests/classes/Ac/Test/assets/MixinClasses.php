<?php

class Dimensions implements Ac_I_Mixable {

    var $length = 0;
    var $width = 0; 
    var $height = 0;
    
    public function getMixableId() {
        return '';
    }

    public function listMixinMethods() {
        return array('getVolume');
    }
    
    public function getVolume() {
        return $this->length * $this->width * $this->height;
    }
    
    public function listMixinProperties() {
        return array('length', 'width', 'height');
    }

    public function registerMixin(Ac_I_Mixin $mixin) {
        
    }

    public function unregisterMixin(Ac_I_Mixin $mixin) {
        
    }

}

class Weight implements Ac_I_Mixable {

    var $weight = 0;
    
    public function getMixableId() {
        return '';
    }

    public function listMixinMethods() {
        return array();
    }
    
    public function listMixinProperties() {
        return array('weight');
    }

    public function registerMixin(Ac_I_Mixin $mixin) {
    }

    public function unregisterMixin(Ac_I_Mixin $mixin) {
    }
    
}

class Body extends Ac_Mixin {

    protected $protVar = false;
    
    protected function doGetCoreMixables() {
        return array(new Weight, new Dimensions());
    }
    
    function getDensity() {
        $res = null;
        if ($v = $this->getVolume()) $res = $this->weight / $v;
        return $res;
    }
    
    protected function protMethod() {
    }
    
    private function privMethod() {
    }
    
}

class ModelProp implements Ac_I_Mixable {
    
    protected $extraProp = false;
    
    var $extraPublicProp = false;
    
    public function getMixableId() {
        return '';
    }

    public function listMixinMethods() {
        return array('getExtraProp', 'setExtraProp');
    }
    
    function setExtraProp($extraProp) {
        $this->extraProp = $extraProp;
    }

    function getExtraProp() {
        return $this->extraProp;
    }
   
    function onListProperties(& $properties) {
        $properties[] = 'extraProp';
        $properties[] = 'extraPublicProp';
    }
    
    public function listMixinProperties() {
        return array('extraPublicProp');
    }

    public function registerMixin(Ac_I_Mixin $mixin) {
        if ($mixin instanceof Ac_Model_Data) $mixin->addEventListener ($this,
            Ac_Model_Data::EVENT_ON_LIST_PROPERTIES);
    }

    public function unregisterMixin(Ac_I_Mixin $mixin) {
        if ($mixin instanceof Ac_Model_Data) $mixin->deleteEventListener ($this);
    }    
    
}

class ExtraPropCatcher extends Ac_Mixable implements Ac_I_Mixable_WithInit {
    
    var $extraProps = array();
    
    protected function listNonMixedProperties() {
        return array('extraProps');
    }
    
    public function handleMixinInit(array $extraProperties, Ac_I_Mixin $mixin) {
        $this->extraProps = $extraProperties;
        return array_keys($this->extraProps);
    }
    
}

class IncompleteBody extends Ac_Mixin {

    protected $protVar = false;
    
    protected function doGetCoreMixables() {
        return array(new Dimensions());
    }
    
}


/*class ModelAggregate implements Ac_I_Mixable {
    
    
    
}*/