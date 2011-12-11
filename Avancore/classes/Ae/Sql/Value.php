<?php

class Ae_Sql_Value extends Ae_Sql_Expression {
    
    /**
     * @param Ae_Database|Ae_Sql_Db $db
     */
    function getExpression(& $db) {
        return $db->quote($this->expression);
    }
    
}