<?php

interface Ac_I_Accessor {
    
    function getProperty($name);
    
    function hasProperty($name);
    
    function setProperty($name, $value);
    
    function listProperties();
    
    
}