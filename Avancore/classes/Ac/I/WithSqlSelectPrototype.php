<?php

interface Ac_I_WithSqlSelectPrototype {
    
    /**
     * @return array
     */
    function getSqlSelectPrototype($primaryAlias = 't');
    
}