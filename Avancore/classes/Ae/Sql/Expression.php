<?php

class Ae_Sql_Expression {

    var $expression = '';
    
    function __construct($expression) {
        $this->expression = $expression;
    }
    
    function __toString() {
        $db = null;
        return $this->getExpression($db);
    }
    
    /**
     * @param Ae_Legacy_Database|Ae_Sql_Db $db
     */
    function getExpression(& $db) {
        return $this->expression;
    }
    
    /**
     * @param Ae_Legacy_Database|Ae_Sql_Db $db
     */
    function nameQuote(& $db) {
        return $this->expression;
    }
    
}

?>