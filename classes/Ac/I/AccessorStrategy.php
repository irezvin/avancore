<?php

interface Ac_I_AccessorStrategy {
    
    function getPropertyOf($object, $name);
    
    function testPropertyOf($object, $name);
    
    function setPropertyOf($object, $name, $value);
    
    function listPropertiesOf($object);
    
}