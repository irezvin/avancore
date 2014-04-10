<?php

abstract class Ac_Debug_Log_AbstractWriter extends Ac_Prototyped {
    
    var $autoRegister = true;
    
    protected $handler = false;
    
    protected $argFormatDecorator = false;
    
    function __construct(array $options = array()) {
        parent::__construct($options);
        if ($this->autoRegister) {
            $this->register();
        }
    }

    function setArgFormatDecorator($argFormatDecorator) {
        $this->argFormatDecorator = $argFormatDecorator;
    }

    function getArgFormatDecorator() {
        return $this->argFormatDecorator;
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

    function format($message, $many = true, $dontDecorate = false) {
        $res = '';
        if ($many) {
            foreach ($message as $m) {
                if (!$dontDecorate &&  $this->argFormatDecorator) 
                    $m = Ac_Decorator::decorate($this->argFormatDecorator, $m, 
                        $this->argFormatDecorator);
                if (is_scalar($m)) {
                    if ($m === false) $m = '(bool) FALSE';
                    elseif ($m === true) $m = '(bool) TRUE';
                    elseif ($m === null) $m = '(object) NULL';
                    elseif ($m === '') $m = "(string) ''";
                    $res .= strlen($res)? '; '.$m : $m; 
                } else {
                    $res .= $this->format($m, false, true);
                }
            }
        } else {
            if (!$dontDecorate && $this->argFormatDecorator) 
                $message = Ac_Decorator::decorate($this->argFormatDecorator, $message, 
                    $this->argFormatDecorator);
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