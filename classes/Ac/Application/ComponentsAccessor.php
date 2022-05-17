<?php

/**
 * Utility class to provide access to Mappers (and other components) using 
 * $this->app->data->{"mapperName"}
 */

class Ac_Application_ComponentsAccessor extends Ac_Application_Component {
    
    function & __get($varName) {
        return $this->app->getComponent($varName);
    }
    
}