<?php

abstract class Ac_Result_Environment implements Ac_I_Result_Environment {

    protected static $default = null;

    static function setDefault(Ac_I_Result_Environment $default = null) {
        self::$default = $default;
    }

    /**
     * @return Ac_I_Result_Environment
     */
    static function getDefault() {
        if (self::$default === null) {
            self::$default = new Ac_Result_Environment_Native();
        }
        return self::$default;
    }
    
}