<?php

class Ac_Finder_Criterion_SortByProperty extends Ac_Finder_Criterion {

    protected $propName = false;

    protected $direction = true;

    /**
     * If sorting is done by several columns, $propName must be an array (to properly reverse the sorting direction) 
     * @param string|array $propName
     */
    function setPropName($propName) {
        if ($propName !== ($oldPropName = $this->propName)) {
        	if (!$this->canSortByProperty($propName)) trigger_error("Can't sort by property {$propName}; check with canSortByProperty() first", E_USER_ERROR);
            $this->propName = $propName;
        }
    }

    function getPropName() {
        return $this->propName;
    }

    function setDirection($direction) {
        if ($direction !== ($oldDirection = $this->direction)) {
            $this->direction = $direction;
        }
    }

    function getDirection() {
        return $this->direction;
    }
    	
    /**
     * @param string $propName
     * @return Ac_Sql_Select_Expression
     */
    protected function createSelectExpression($propName) {
    	if ($propName instanceof Ac_Sql_Select_Expression) $res = clone $propName;
    	else {
    		$path = Ac_Util::pathToArray($propName);
    		if (count($path) > 1) {
    			$fieldName = $path[count($path) - 1];
    			$alias = Ac_Util::arrayToPath(array_slice($path, 0, count($path) - 1));
    		} else {
    			$fieldName = $path[0];
    			$alias = $this->finder->getPrimaryAlias();
    			if ($alias === false) $alias = array();
    		}
    		$res = new Ac_Sql_Select_Expression ($fieldName, $alias, true);
    	}
    	return $res;
    }
    
	function canSortByProperty($propName) {
		$expr = $this->createSelectExpression($propName);
		if (count($expr->aliases)) {
			$sel = $this->finder->createSqlSelect();
			$res = $sel->hasTable($expr->aliases[0]);
		}  else {
			$res = true;
		}
		return $res;
	}
	
    /**
     * @param Ac_Sql_Select $select
     */
    function applyToSelect(Ac_Sql_Select & $select) {
 		$db = new Ac_Sql_Db_Ae();
 		if (is_array($this->propName)&&($this->propName) || strlen($this->propName)) {
    		$selectPart = & Ac_Sql_Part::factory(array(
    			'class' => 'Ac_Sql_Order_Simple',
 			));
            if (is_string($this->propName) || is_object($this->propName)) {
                $expr = $this->createSelectExpression($this->propName);
                $selectPart->aliases = $expr->aliases;                
                
                $p = $expr->getExpression($db);
                if (preg_match($pat = '#\sASC\s*$#i', $p)) $d = preg_replace($pat, ' DESC', $p);
                elseif (preg_match($pat = '#\sDESC\s*$#i', $p)) $d = preg_replace($pat, ' ASC', $p);
                else $d = $p.' DESC';
                
                $selectPart->order = $p;
                $selectPart->orderIfDesc = $d;
                
            } else { // it's an array
                $items = array();
                $descItems = array();
                foreach ($this->propName as $p) {
                    if (preg_match($pat = '#\sASC\s*$#i', $p)) $d = preg_replace($pat, ' DESC', $p);
                    elseif (preg_match($pat = '#\sDESC\s*$#i', $p)) $d = preg_replace($pat, ' ASC', $p);
                    else $d = $p.' DESC';
                    $items[] = $p;
                    $descItems[] = $d;
                }
                $selectPart->order = implode(", ", $items);
                $selectPart->orderIfDesc = implode(", ", $descItems); 
            } 
 			$selectPart->bind($this->direction > 0? 1 : -1);
        	$selectPart->applyToSelect($select);
 		}   	 
    }

    function setValue($value = null) {
    	if (!is_null($value) && !is_array($value)) {
    		$value = array('propName' => $value, 'direction' => $this->direction);
    	}
    	if (is_array($value)) {
    		if (isset($value['propName'])) $this->setPropName($value['propName']);
    		if (isset($value['direction'])) $this->setDirection($value['direction']);	
    	}            
    	parent::setValue($value);
    }
    
    function getValue() {
    	if ($this->value === null) $res = null;
    	else $res = array('propName' => $this->propName, 'direction' => $this->direction);
    	return $res;
    }
	
}