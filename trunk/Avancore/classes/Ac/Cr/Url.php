<?php

class Ac_Cr_Url extends Ac_Url {
    
    protected $router = null;
    
    function setRouter(Ac_Cr_Router $router = null) {
        $this->router = $router;
    }
    
    /**
     * @return Ac_Cr_Router
     */
    function getRouter() {
        return $this->router;
    }
    
    function toStringUnrouted($withQuery = true) {
        $res = parent::toString($withQuery);
        return $res;
    }
    
    /**
     * Guesses and returns URL of current script optionally using data from $request object
     * If $request is not provided, works exactly as Ac_Url::guess
     * 
     * @param boolean $withPathInfo Whether to populate $pathInfo part
     * @param Ac_Request $request Request object to get protocol, host, script_name etc
     * 
     * @return Ac_Cr_Url
     */
    static function guess($withPathInfo = false, Ac_Request $request = null) {
        if ($request === null) $res = new Ac_Cr_Url(Ac_Url::guess($withPathInfo));
        else {
            $s = $request->server;
            $protocol = explode('/', strtolower($s->serverProtocol));
            $res = new Ac_Url($protocol[0].'://'.$s->httpHost.$s->requestUri);
            if ($withPathInfo) {
                if (isset($s->pathInfo)) {
                    $myPathInfo = $s->pathInfo;
                    $res->path = $s->scriptName;
                    if (isset($s->pathInfo)) $res->pathInfo = $s->pathInfo;
                } else {
                    $sn = explode('/', $s->scriptName);
                    $ru = $s->requestUri;
                    if (isset($s->queryString) && ($l = strlen($s->queryString))) $ru = substr($ru, 0, strlen($ru) - $l - 1);
                    $ru = explode('/', $ru);
                    $path = array();
                    $pathInfo = array();
                    $maxLen = min(count($sn), count($ru));
                    for ($i = 0; $i < $maxLen && ($sn[$i] == $ru[$i]); $i++) {
                        $path[] = $sn[$i];
                        unset($ru[$i]);
                    }
                    $res->path = implode('/', $path);
                    if (count($ru)) $res->pathInfo = implode('/', $ru);
                    if (substr($res->path, -1) !== '/' && ($res->pathInfo !== false && substr($res->pathInfo, 0, 1) !== '/')) {
                        $res->path = $res->path. '/';
                    }
                }
            }
        }
        return $res;
    }    
    
}