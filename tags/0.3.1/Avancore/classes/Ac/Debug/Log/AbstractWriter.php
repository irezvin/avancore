<?php

abstract class Ac_Debug_Log_AbstractWriter extends Ac_Prototyped {
    
    var $autoRegister = true;
    
    protected $handler = false;
    
    function __construct(array $options = array()) {
        parent::__construct($options);
        if ($this->autoRegister) {
            $this->register();
        }
    }
    
    function hasPublicVars() {
        return true;
    }
    
    abstract function write(Ac_Debug_Log $log = null, array $args);
    
    function register() {
        if ($this->handler === false) {
            $this->handler = Ac_Callbacks::getInstance()->addHandler('CALLBACK_LOG_WRITE', array($this, 'write'));
        }
        return $this->handler;
    }
    
    function unregister() {
        if ($this->handler !== false) {
            Ac_Callbacks::getInstance()->removeHandlerById('CALLBACK_LOG_WRITE', $this->handler);
            $this->handler = false;
        }
    }

    function format($message, $many = true) {
        $res = '';
        if ($many) {
            foreach ($message as $m) {
                if (is_scalar($m)) $res .= strlen($res)? '; '.$m : $m; else {
                    $res .= $this->format($m, false);
                }
            }
        } else {
            if (!is_string($message)) {
                ob_start();
                $he = ini_get('html_errors');
                if ($he) {ini_set('html_errors', 0); }
                var_dump($message);
                if ($he) {ini_set('html_errors', 1); }
                $res = ob_get_clean();
            } else {
                $res = $message;
            }
        }
        $res = "\n".date("Y-m-d H:i:s")."\t".$res;
        return $res;
    }
    
}