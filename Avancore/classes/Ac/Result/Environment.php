<?php

abstract class Ac_Response_Environment implements Ac_I_Response_Environment {

    protected static $default = null;

    static function setDefault(Ac_I_Response_Environment $default = null) {
        self::$default = $default;
    }

    /**
     * @return Ac_I_Response_Environment
     */
    static function getDefault() {
        if (self::$default === null) {
            self::$default = new Ac_Response_Environment_Native();
        }
        return self::$default;
    }
    
}