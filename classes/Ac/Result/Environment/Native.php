<?php

class Ac_Result_Environment_Native implements Ac_I_Result_Environment, Ac_I_Prototyped {

//    var $defaultCookieExpiration = false;
//    
//    var $defaultCookieDomain = false;
//    
//    var $defaultCookieProtocol = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function e() {
    }
    
    function cancelBuffering() {
        while (ob_get_level()) ob_end_clean();
    }
    
    function acceptHeaders(array $headers) {
        if (!headers_sent()) {
            foreach ($headers as $header) {
                if (is_object($header) && $header instanceof Ac_I_Http_Header) {
                    $header->processNatively();
                } else {
                    // TODO: better headers handling
                    header($header);
                }
            }
        }
    }
    
    function acceptCookies(array $cookies) {
        foreach ($cookies as $key => $cookie) {
            if (is_object($cookie) && $cookie instanceof Ac_I_Http_Cookie) {
                $cookie->processNatively();
            } else {
                // TODO: implement better cookie handling
                setcookie($key, $cooke);
            }
        }
    }
    
    function acceptSessionData(array $sessionData) {
        if (!isset($_SESSION)) session_start();
        foreach ($sessionData as $k => $v) {
            $_SESSION[$k] = $v;
        }
    }
    
    function destroySession() {
        session_destroy();
    }
    
    
    function acceptHttpStatusCode($statusCode, $reasonPhrase = false) {
        $s = $statusCode;
        if (strlen($reasonPhrase)) $s .= " ".$reasonPhrase;
        $sapi_type = php_sapi_name();
        if (substr($sapi_type, 0, 3) == 'cgi') {
            header("Status: ".$s, true, $statusCode);
        } else {
            header($_SERVER['SERVER_PROTOCOL']." ".$s, true, $statusCode);
        }
    }
    
    
    function acceptResponseText($text) {
        echo $text;
    }

    function finishOutput() {
        // should probably die() here
    }
    
    function begin() {
    }
    
    
}