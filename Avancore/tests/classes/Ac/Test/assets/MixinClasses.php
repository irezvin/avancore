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