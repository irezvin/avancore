<?php

class Ae_Finder_Criterion_SearchByProperties extends Ae_Finder_Criterion {

    var $patBefore = '';

    protected $propNames = false;

    protected $substring = true;

    function setPropNames($propNames) {
    	if (!is_array($propNames)) {
    		if (strlen($propNames)) $propNames = array($propNames);
    			else $propNames = array();
    	}
        if ($propNames !== ($oldPropNames = $this->propNames)) {
            $this->propNames = $propNames;
            if ($usp = $this->listUnsearchableProperties($propNames)) {
    			trigger_error("Can't search by properties '".implode("', '", $propNames)."'; check with listUnsearchableProperties() first", E_USER_ERROR);
    		} 
        }
    }

    function getPropNames() {
        return $this->propNames;
    }

    function setSubstring($substring) {
        if ($substring !== ($oldSubstring = $this->substring)) {
            $this->substring = $substring;
        }
    }

    function getSubstring() {
        return $this->substring;
    }
    	
    /**
     * @param string $propName
     * @return Ae_Sql_Select_Expression
     */
    protected function createSelectExpression($propName) {
    	if ($propName instanceof Ae_Sql_Select_Expression) $res = clone $propName;
    	else {
    		$path = Ae_Util::pathToArray($propName);
    		if (count($path) > 1) {
    			$fieldName = $path[count($path) - 1];
    			$alias = Ae_Util::arrayToPath(array_slice($path, 0, count($path) - 1));
    		} else {
    			$fieldName = $path[0];
    			$alias = $this->finder->getPrimaryAlias();
    			if ($alias === false) $alias = array();
    		}
    		$res = new Ae_Sql_Select_Expression ($fieldName, $alias, true);
    	}
    	return $res;
    }
    
	function listUnsearchableProperties(array $propNames) {
		$sel = $this->finder->createSqlSelect();
		$res = array();
		foreach ($propNames as $propName) {
			$expr = $this->createSelectExpression($propName);
			if (count($expr->aliases)) {
				if (!$sel->hasTable($expr->aliases[0])) $res[] = $propName;
			}
		}
		return $res;
	}
	
    /**
     * @param Ae_Sql_Select $select
     */
    function applyToSelect(Ae_Sql_Select & $select) {
    	if (count($this->propNames) && strlen($this->substring)) {
    		
 			$aliases = array();
    		$cols = array();
 			
            $db = $select->getDb();
    		
    		foreach($this->propNames as $p) {
    			$expr = $this->createSelectExpression($p);
    			$cols[] = $expr->getExpression($db);
    			$aliases = array_merge($aliases, $expr->aliases);	
    		}
    		
    		$selectPart = & Ae_Sql_Part::factory(array(
    			'class' => 'Ae_Sql_Filter_Substring',
    			'patBefore' => $this->patBefore,
    			'aliases' => array_unique($aliases),
    			'colNames' => $cols,
    			'useConcat' => false,
    			'ifNullFunction' => $db->getIfnullFunction() 
 			));
        	$selectPart->bind($this->substring);
        	$selectPart->applyToSelect($select);
 		}   	 
    }

    function setValue($value = null) {
    	if (!is_null($value) && !is_array($value)) {
    		$value = array('propNames' => $value, 'substring' => $this->substring);
    	}
    	if (is_array($value)) {
    		if (isset($value['propNames'])) $this->setPropNames($value['propNames']);
    		if (isset($value['substring'])) $this->setSubstring($value['substring']);	
    	}            
    	parent::setValue($value);
    }
    
    function getValue() {
    	if ($this->value === null) $res = null;
    	else $res = array('propNames' => $this->propNames, 'substring' => $this->substring);
    	return $res;
    }
	
}
