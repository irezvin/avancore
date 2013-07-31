<?php

class Ac_Result_Writer_HttpOut extends Ac_Result_Writer_WithCharset {
    
    /**
     * @var Ac_I_Response_Environment
     */
    protected $environment = false;

    function setEnvironment(Ac_I_Response_Environment $environment = null) {
        $this->environment = $environment;
    }

    /**
     * @return Ac_I_Response_Environment
     */
    function getEnvironment() {
        if ($this->environment === false) $this->environment = new Ac_Response_Environment_Native();
        return $this->environment;
    }        
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if ($t) throw new Ac_E_InvalidCall(__CLASS__." can work only without target response");
        if (!($e = $this->getEnvironment())) throw new Ac_E_InvalidCall("setEnvironment() first");
        
        $e->begin();
        if ($r instanceof Ac_Result_Http_Abstract) {
            $e->acceptHeaders($r->getHeaders()->getItems());
        }
        $r->echoContent();
        $e->finishOutput();
    }
    
}