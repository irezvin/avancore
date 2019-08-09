<?php

interface Ac_I_WithMapper {
    
    function getMapper();
    
    function setMapper(Ac_Model_Mapper $mapper = null);
    
}