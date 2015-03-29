<?php

interface Ac_I_Response_Writer {
    
    function setResponse(Ac_Response $response);
    
    function writeResponse(Ac_Response $response = null);
    
}