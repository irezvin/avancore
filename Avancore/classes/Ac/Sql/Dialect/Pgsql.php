<?php

class Ac_Sql_Dialect_Pgsql extends Ac_Sql_Dialect_Mysql {

    protected $nameQuoteChar = '"';    
    
    protected $inspectorClass = 'Ac_Sql_Dbi_Inspector_PgSql';
    
}