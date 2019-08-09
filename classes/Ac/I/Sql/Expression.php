<?php

interface Ac_I_Sql_Expression {
    
    function getExpression($db);
    
    function nameQuote($db);
    
    
}