<?php

class Ac_Legacy_Output_Joomla3 extends Ac_Legacy_Output_Joomla15 {
    
    var $mergeGlobalResponse = false;
    
    var $registerDocHandler = true;
    
    protected static $handlerRegistered = false;
    
    function outputResponse(Ac_Legacy_Controller_Response_Html $response, $asModule = false) {
        $res = parent::outputResponse($response, $asModule);
        if ($this->registerDocHandler) $this->registerHandler();
        return $res;
    }
    
    function registerHandler() {
        if (self::$handlerRegistered) return true;
        self::$handlerRegistered = true;
        $disp = JEventDispatcher::getInstance();
        $disp->register('onBeforeCompileHead', array(__CLASS__, 'dumpDefaultResponse'));
    }
    
    static function dumpDefaultResponse() {
        $r = Ac_Legacy_Controller_Response_Global::getInstance()->getResponse();
        $r->content = '';
        $o = new Ac_Legacy_Output_Joomla3;
        $o->mergeGlobalResponse = false;
        $o->registerDocHandler = false;
        $o->outputResponse($r);        
    }
    
}