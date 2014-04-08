<?php

class Ac_Mixable implements Ac_I_Mixable {
    
    protected $mixableId = false;
    
    protected $autoEventPrefix = 'event_';
    
    /**
     * @var Ac_I_Mixin
     */
    protected $mixin = false;

    protected $mixinClass = false;
    
    protected static $introducedPublicMethods = array();
    
    protected static $introducedPublicVars = array();
    
    protected static $eventHandlerMethods = array();
    
    function setMixableId($mixableId) {
        if ($this->mixableId !== false && $mixableId !== $this->mixableId)
            throw Ac_E_InvalidCall::canRunMethodOnce($this, __FUNCTION__);
        $this->mixableId = $mixableId;
    }
    
    function getMixableId() {
        return $this->mixableId;
    }
    
    public function listMixinMethods() {
        $c = get_class($this);
        if (!isset(self::$introducedPublicMethods[$c])) {
            self::$introducedPublicMethods[$c] = array_diff(
                Ac_Util::getPublicMethods($c), 
                Ac_Util::getPublicMethods('Ac_Mixable')
            );
            if (strlen($this->autoEventPrefix)) 
                self::$introducedPublicMethods[$c] = array_diff(self::$introducedPublicMethods[$c], 
                    $this->listEventHandlerMethods());
        }
        return self::$introducedPublicMethods[$c];
    }
    
    function listEventHandlerMethods() {
        $c = get_class($this);
        if (!isset(self::$eventHandlerMethods[$c])) {
            self::$eventHandlerMethods[$c] = array();
            if ($l = strlen($this->autoEventPrefix)) {
                foreach (Ac_Util::getPublicMethods($c) as $m)
                    if (!strncasecmp($m, $this->autoEventPrefix, $l))
                        self::$eventHandlerMethods[$c][substr($m, $l)] = $m;
            }
        }
        return self::$eventHandlerMethods[$c];
    }

    public function listMixinProperties() {
        $c = get_class($this);
        if (!isset(self::$introducedPublicVars[$c])) {
            self::$introducedPublicVars[$c] = array_diff(
                Ac_Util::getPublicVars($c), 
                Ac_Util::getPublicVars('Ac_Mixable')
            );
        }
        return self::$introducedPublicVars[$c];
    }

    public function registerMixin(Ac_I_Mixin $mixin) {

        if ($this->mixinClass !== false)
            throw Ac_E_InvalidCall::wrongClass ('mixin', $mixin, $this->mixinClass);
        
        $this->mixin = $mixin;
        if (strlen($this->autoEventPrefix) && $mixin instanceof Ac_I_WithEvents) 
            $this->autoRegisterEvents($mixin);
    }

    public function unregisterMixin(Ac_I_Mixin $mixin) {
        if ($mixin !== $this->mixin)
            throw new Ac_E_InvalidUsage("unregisterMixin: parameter does not mixin previously registered");
        $this->mixin = null;
        if (strlen($this->autoEventPrefix && $mixin instanceof Ac_I_WithEvents))
            $mixin->deleteEventListener($this);
    }
    
    protected function autoRegisterEvents($mixin) {
        if ($mixin instanceof Ac_I_WithEvents) {
            foreach ($this->listEventHandlerMethods() as $event => $method) {
                $mixin->addEventListener(array($this, $method), $event);
            }
        }
    }

}