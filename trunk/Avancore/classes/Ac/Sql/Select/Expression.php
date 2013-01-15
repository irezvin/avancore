<?php

/**
 * @author Nivzer
 */

/**
 * Expression that contains info about aliases mentioned in it
 */
class Ac_Sql_Select_Expression extends Ac_Sql_Expression {
	
	var $aliases = array();
	
	var $isColumn = false;
	
	/**
	 * Examples: 
	 * <code>
	 *    $simple = new Ac_Sql_Select_Expression('aCol');
	 *    echo $simple->getExpression(); // '`aCol`'
	 *    
	 *    $withAliases = new Ac_Sql_Select_Expression('foo.col1 + bar.col2', array('foo', 'bar'));
	 *    echo $withAliases->getExpression(); // foo.col1 + bar.col2
	 *    
	 *    $column = new Ac_Sql_Select_Expression('col1', 'foo', true);
	 *    echo $column->getExpression(); // foo.col1
	 *    
	 *    $autoAlias = new Ac_Sql_Select_Expression('foo.col1', true); // currently only such simple case is parsed
	 *    echo $column->getExpression(); // foo.col1
	 *    echo implode(", ", $column->aliases); // foo
	 * </code>
	 * @param string $expression Expression
	 * @param array|string|bool $aliases Alias or several aliases to be added to the table or TRUE to automatically detect aliases (see examples) 
	 * @param bool $isColumn Whether expression contains only column name and first alias should be added automatically
	 */
	function Ac_Sql_Select_Expression($expression, $aliases = array(), $isColumn = false) {
		$this->expression = $expression;
		$this->isColumn = $isColumn;
		if (!is_array($aliases)) {
			if ($aliases === true) {
				$colExpr = explode(".", $expression, 2);
				if (isset($colExpr[1])) {
					$this->expression = $colExpr[1];
					$aliases = $colExpr[0];
					$this->isColumn = true;
				} else {
					$aliases = false;
				}
			}
			$aliases = strlen($aliases)? array($aliases) : array();
		}
		$this->aliases = $aliases;
	}
	
	function getExpression(& $db) {
		if ($this->isColumn) {
			if ($this->aliases) {
				if ($db) {
					$res = $db->nameQuote($this->aliases[0]).'.'.$db->nameQuote($this->expression);
				} else $res = $this->aliases[0].'.'.$this->expression;
			} else {
				if ($db) $res = $db->nameQuote($this->expression);
					else $res = $this->expression;
			}
		} else {
            if (is_array($this->expression)) $res = implode(", ", $this->expression);
            else $res = $this->expression;
        }
		return $res;
	}
    
	function nameQuote(& $db) {
		return $this->getExpression($db);
	}
	
}