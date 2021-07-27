<?php

abstract class Ac_Controller_Output extends Ac_Prototyped {
 
    abstract function outputResponse(Ac_Controller_Response_Html $r, $asModule = false); 
    
    function exitPhp() {
        if (isset($_SESSION)) session_write_close();
        die();
    }

    
    
}