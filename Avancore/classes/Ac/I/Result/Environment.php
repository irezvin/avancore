<?php

interface Ac_I_Result_Environment {
    
    function begin();
    
    function cancelBuffering();
    
    function acceptHeaders(array $headers);
    
    function acceptCookies(array $cookies);
    
    function acceptSessionData(array $sessionData);
    
    function destroySession();
    
    function acceptResponseText($text);
    
    function acceptHttpStatusCode($statusCode, $reasonPhrase = false);
    
    function finishOutput();
    
}