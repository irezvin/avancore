<?php

class Ae_Decorator extends Ae_Autoparams implements Ae_I_Decorator_Model {
    
    protected $model = null;
    
    function hasPublicVars() {
        return true;
    }

    function setModel(Ae_Model_Data $model = null) {
        if ($model !== ($oldModel = $this->model)) {
            $this->model = $model;
            $this->doOnSetModel();
        }
    }
    
    protected function doOnSetModel() {
    }

    function getModel() {
        return $this->model;
    }

    function apply($value) {
        return $value;
    }
    
    static function decorate($decorator, $value, & $instance = null) {
        $instance = self::instantiate($decorator);
        if ($instance) $value = $instance->apply($value);
        return $value;
    }
    
    /**
     * @param $decorator
     * @return Ae_I_Decorator
     */
    static function instantiate($decorator) {
        if ($decorator instanceof Ae_I_Decorator) {
            $res = $decorator;
        } elseif ($decorator) {
            if (!is_array($decorator)) $decorator = array('class' => $decorator);
            $res = Ae_Autoparams::factory($decorator, 'Ae_I_Decorator');
        } else {
            $res = $decorator;
        }
        return $res;
    }
    
}