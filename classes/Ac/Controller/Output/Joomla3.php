<?php

class Ac_Controller_Output_Joomla3 extends Ac_Controller_Output_Joomla15 {
    
    var $mergeGlobalResponse = false;
    
    var $registerDocHandler = true;
    
    protected static $handlerRegistered = false;
    
    function outputResponse(Ac_Controller_Response_Html $response, $asModule = false) {
        $tmp = $this->mergeGlobalResponse;
        if ($asModule) $this->mergeGlobalResponse = false;
        $res = parent::outputResponse($response, $asModule);
        if ($this->registerDocHandler) $this->registerHandler();
        $this->mergeGlobalResponse = $tmp;
        return $res;
    }
    
    function registerHandler() {
        if (self::$handlerRegistered) return true;
        self::$handlerRegistered = true;
        $disp = JEventDispatcher::getInstance();
        $disp->register('onBeforeCompileHead', array(__CLASS__, 'dumpDefaultResponse'));
    }
    
    static function dumpDefaultResponse() {
        $r = Ac_Controller_Response_Global::getInstance()->getResponse();
        $r->content = '';
        $o = new Ac_Controller_Output_Joomla3;
        $o->mergeGlobalResponse = false;
        $o->registerDocHandler = false;
        $o->outputResponse($r);        
    }
    
}
