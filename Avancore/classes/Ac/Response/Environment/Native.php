<?php

class Ac_Response_Environment_Native implements Ac_I_Response_Environment, Ac_I_Prototyped {

//    var $defaultCookieExpiration = false;
//    
//    var $defaultCookieDomain = false;
//    
//    var $defaultCookieProtocol = false;
    
    function hasPublicVars() {
        return true;
    }
    
    function begin() {
    }
    
    function cancelBuffering() {
        while (ob_get_level()) ob_end_clean();
    }
    
    function acceptHeaders(array $headers) {
        foreach ($headers as $header) {
            if (is_object($header) && $header instanceof Ac_I_Http_Header) {
                $header->processNatively();
            } else {
                // TODO: better headers handling
                header($header);
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
    
    function acceptResponseText($text) {
        echo $text;
    }

    function finishOutput() {
        // should probably die() here
    }
    
}