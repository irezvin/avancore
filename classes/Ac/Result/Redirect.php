<?php

class Ac_Result_Redirect extends Ac_Result_Http {
    
    protected $url = null;
    
    const REDIR_PERMANENT = 301;
    const REDIR_FOUND = 302;
    const REDIR_SEE_OTHER = 303;
    const REDIR_TEMPORARY = 307;
    
    protected static $reasonPhrases = array(
        self::REDIR_PERMANENT => 'Moved Permanently',
        self::REDIR_FOUND => 'Found',
        self::REDIR_SEE_OTHER => 'See Other',
        self::REDIR_TEMPORARY => 'Temporary Redirect',
    );
 
    function setUrl($url = null, $statusCode = false) {
        $this->url = $url;
        if (is_null($url)) {
            $this->statusCode = false;
        } else {
            if ($statusCode === false) {
                if (!$this->statusCode) {
                    $statusCode = self::REDIR_FOUND;
                    $this->setStatusCode($statusCode);
                }
            } else {
                $this->setStatusCode($statusCode);
            }
        }
        if ($this->merged) $this->setDefaultHeaders();
    }
    
    function setStatusCode($statusCode, $reasonPhrase = false) {
        if (!isset(self::$reasonPhrases[$statusCode]))
            throw Ac_E_InvalidCall::outOfConst ('statusCode', $statusCode, 'REDIR_', __CLASS__);
        if ($reasonPhrase === false) $reasonPhrase = self::$reasonPhrases[$statusCode];
        parent::setStatusCode($statusCode, $reasonPhrase);
    }
    
    /**
     * @return Ac_Url
     */
    function getUrl() {
        return $this->url;
    }
    
    protected function setDefaultHeaders() {
        parent::setDefaultHeaders();
        if (strlen($u = ''.$this->url)) {
            $this->getHeaders()->replaceHeader('location: '.$u);
        } else {
            $this->getHeaders()->removeHeader('location');
        }
    }
    
}