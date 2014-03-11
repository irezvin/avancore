<?php

class Ac_Cr_Router {
    
    /**
     * @param Ac_Request $request 
     */
    function translateRequest(Ac_Request $request) {
        return $request;
    }
    
    function getStrUrl(Ac_Cr_Url $url, $withQuery = false) {
        return $url->toStringUnrouted($withQuery);
    }
    
}