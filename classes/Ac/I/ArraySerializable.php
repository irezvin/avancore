<?php

interface Ac_I_ArraySerializable {
    
    function serializeToArray();
    
    function unserializeFromArray($array);
    
}