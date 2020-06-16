<?php

class Ac_Sql_Expression implements Ac_I_Sql_Expression {

    var $expression = '';
    
    function __construct($expression) {
        $this->expression = $expression;
    }
    
    function __toString() {
        //$db = Ac_Application::getDefaultInstance()->getDb();
        $db = null;
        return $this->getExpression($db);
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function getExpression($db) {
        if (is_object($this->expression) && $this->expression instanceof Ac_I_Sql_Expression) {
            return $this->expression->getExpression($db);
        }
        return $this->expression;
    }
    
    /**
     * @param Ac_Legacy_Database|Ac_Sql_Db $db
     */
    function nameQuote($db) {
        if (is_object($this->expression) && $this->expression instanceof Ac_I_Sql_Expression) {
            return $this->expression->nameQuote($db);
        }
        return $this->expression;
    }
    
}

