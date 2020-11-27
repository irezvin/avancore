<?php

abstract class Ac_Legacy_Output {
 
    abstract function outputResponse(Ac_Legacy_Controller_Response_Html $r, $asModule = false); 
    
    function exitPhp() {
        if (isset($_SESSION)) session_write_close();
        die();
    }

    
    
}