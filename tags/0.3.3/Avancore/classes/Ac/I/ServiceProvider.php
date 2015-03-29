<?php

interface Ac_I_ServiceProvider {
    
    function listServices();
    
    function setServices(array $services, $removeExisting = false);
    
    function setService($id, $prorotypeOrInstance, $overwrite = false);
    
    function deleteService($id, $dontThrow = false);
    
    function getServices();
    
    function getService($id, $dontThrow = false);
    
}