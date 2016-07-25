<?php

interface Ac_Etl_I_Matcher {
    
    /**
     * @return array
     */
    function getColMatches();
    
    function setSqlDb(Ac_Sql_Db $sqlDb);
    
    function setTablePair(Ac_Etl_I_TablePair $tablePair);
    
}