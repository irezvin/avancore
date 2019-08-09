<?php

interface Ac_Etl_I_TablePair {
    
    /**
     * @return FALSE or string
     */
    function getLeftTableName();
    
    /**
     * @return FALSE or string
     */
    function getRightTableName();
    
    function getLeftDbName();
    
    function getRightDbName();    
    
    function getLeftDbPrefix();
    
    function getRightDbPrefix();
    
}