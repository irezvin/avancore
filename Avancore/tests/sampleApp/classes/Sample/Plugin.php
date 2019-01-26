<?php

class Sample_Plugin implements Ac_I_Mixable {
    
    
    public function getMixableId() {
    }

    public function listMixinMethods(Ac_I_Mixin $mixin) {
        return array();
    }

    public function listMixinProperties(Ac_I_Mixin $mixin) {
        return array();
    }

    public function registerMixin(Ac_I_Mixin $mixin) {
        if ($mixin instanceof Ac_Application) {
            $mixin->addEventListener($this, Ac_Application::EVENT_ON_INITIALIZE);
            $mixin->addEventListener($this, Ac_Application::EVENT_ON_GET_COMPONENT_PROTOTYPES);
        }
    }
    
    function onInitialize() {
        $app = Ac_Event::getCurrent()->getIssuer();
        if ($app instanceof Ac_Application) {
            //var_dump("App init");
        }
    }
    
    function onGetComponentPrototypes(& $prototypes) {
        Ac_Util::ms($prototypes, array('otherPeople' => array(
            'class' => 'Ac_Model_Mapper',
            'tableName' => '#__people'
        )));
    }

    public function unregisterMixin(Ac_I_Mixin $mixin) {
        
    }

}