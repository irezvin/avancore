<?php

class Ac_Result_Writer_HttpOut extends Ac_Result_Writer_WithCharset {
    
    /**
     * @var Ac_I_Result_Environment
     */
    protected $environment = false;

    function setEnvironment(Ac_I_Result_Environment $environment = null) {
        $this->environment = $environment;
    }

    /**
     * @return Ac_I_Result_Environment
     */
    function getEnvironment() {
        if ($this->environment === false) return Ac_Result_Environment::getDefault();
        return $this->environment;
    }        
    
    protected function implWriteNoCharset(Ac_Result $r, Ac_Result $t = null, Ac_Result_Stage $s = null) {
        if ($t) throw new Ac_E_InvalidCall(__CLASS__." can work only without target response");
        if (!($e = $this->getEnvironment())) throw new Ac_E_InvalidCall("setEnvironment() first");
        
        if ($e) $e->begin();
        if ($r instanceof Ac_Result_Http_Abstract && $e) {
            $e->acceptHeaders($r->getHeaders()->getItems());
        }
        if ($r instanceof Ac_Result_Http && $e) {
            if (($c = $r->getStatusCode() !== false)) {
                $e->acceptHttpStatusCode($r->getStatusCode(), $r->getReasonPhrase());
            }
        }
        $r->echoContent();
        if ($e) $e->finishOutput();
    }
    
}