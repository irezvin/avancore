<?php

class Ac_Cr_Result_Response_Decision implements Ac_I_ResultResponseDecision {

    /**
     * @return Ac_Cr_Response
     */
    function getResponseFor($methodReturnValue) {
        if (is_object($methodReturnValue) && $methodReturnValue instanceof Ac_I_RedirectTarget) {
            $res = new Ac_Response_Http();
            $res->setHeaders($methodReturnValue->getTargetUrl(), 'Location');
        } elseif (is_object($methodReturnValue) || is_array($methodReturnValue)) {
            $res = new Ac_Response_Http();
            $res->setHeaders('text/javascript; charset=utf-8', 'Content-type');
            $res->setNoHtml(true);
            $res->setContent(new Ac_Js_Val($methodReturnValue));
        } else {
            $res = new Ac_Response();
            $res->setRegistry(''.$methodReturnValue, 'content');
        }
        return $res;
    }
    
}