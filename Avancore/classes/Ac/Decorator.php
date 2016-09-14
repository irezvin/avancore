<?php

class Ac_Decorator extends Ac_Prototyped implements Ac_I_Decorator_Model {
    
    protected $model = false;
    
    protected static $modelStack = array();
    
    /**
     * Two forms: a) ::proto(array, true) <- 1st arg has decorators; b) ::proto(dec1, dec2, ...)
     */
    static function Multi(array $items, $isArray = null) {
        if ($isArray === true)
            return array('class' => 'Ac_Decorator_Multi', 'decorators' => $items);
        else {
            $args = func_get_args();
            return array('class' => 'Ac_Decorator_Multi', 'decorators' => $args);
        }
    }

    function hasPublicVars() {
        return true;
    }

    function setModel($model = null) {
        if ($model !== ($oldModel = $this->model)) {
            $this->model = $model;
            $this->doOnSetModel();
        }
    }
    
    protected function doOnSetModel() {
    }

    function getModel() {
        if ($this->model === false) return self::topModel();
        return $this->model;
    }

    function apply($value) {
        return $value;
    }
    
    static function pushModel($model) {
        array_push(self::$modelStack, $model);
    }
    
    static function topModel() {
        $top = array_slice(self::$modelStack, -1);
        if (count($top)) $res = $top[0];
        else $res = null;
        return $res;
    }
    
    static function popModel() {
        if (count(self::$modelStack)) $res = array_pop(self::$modelStack);
        else {
            throw new Ac_E_InvalidUsage("Call to Ac_Decorator::popModel() without corresponding Ac_Decorator::pushModel()");
        }
        return $res;
    }
    
    static final function decorate($decorator, $value, & $instance = null, $model = false) {
        if ($decorator !== false && !(is_array($decorator) && !$decorator)) {
            if ($model !== false) self::pushModel($model);
            $instance = self::instantiate($decorator);
            if ($instance) $value = $instance->apply($value);
            if ($model !== false) self::popModel();
        }
        return $value;
    }
    
    /**
     * @param $decorator
     * @return Ac_I_Decorator
     */
    static function instantiate($decorator) {
        if (is_object($decorator) && $decorator instanceof Ac_I_Decorator) {
            $res = $decorator;
        } elseif ($decorator) {
            if (!is_array($decorator)) $decorator = array('class' => $decorator);
            $res = Ac_Prototyped::factory($decorator, 'Ac_I_Decorator');
        } else {
            $res = $decorator;
        }
        return $res;
    }
    
    /**
     * @TODO: implement it
     * - for non-stream decorators with getIsDistributive()===false capture the buffer, then apply
     * - for non-stream decorators with isDistributive() set callback to decorator func
     * - for Ac_I_StreamDecorator: call begin(), write(), end()
     */    
    
    static function decorateStreamable(Ac_I_Streamable $streamable, $decorator, $callback = null) {
    }
    
    
}
