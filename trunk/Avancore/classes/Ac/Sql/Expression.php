<?php

class Ac_Sql_Expression {

    var $expression = '';
    
    function __construct($expression) {
        $this->expression = $expression;
    }
    
    function __toString() {
        $db = null;
        return $this->getExpression($db);
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function getExpression(& $db) {
        return $this->expression;
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function nameQuote(& $db) {
        return $this->expression;
    }
    
}

?>