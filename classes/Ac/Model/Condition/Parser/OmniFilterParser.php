<?php

class Ac_Model_Condition_Parser_OmniFilterParser extends Ac_Prototyped {
    
    function hasPublicVars() {
        return true;
    }
    
    /**
     * @param array $jsonNotation
     */
    function createWithJsonNotation(array $jsonNotation) {
    }
    
    function parseJsonNotation(array $jsonNotation) {
        return $this->parseJsonExpression($jsonNotation);
    }
    
    function parseFieldsNotation(array $fieldsNotation) {
        if (count($fieldsNotation) == 1 && isset($fieldsNotation[' json '])) {
            if (is_string($fieldsNotation[' json '])) {
                $json = json_decode($fieldsNotation[' json '], true);
            }
            return $this->parseJsonNotation ($json);
        }
        $jsonNotation = [];
        foreach ($fieldsNotation as $field => $fieldCriterion) {
            $jsonNotation[$field] = $this->parseFieldCriterion($fieldCriterion);
        }
        return $this->parseJsonExpression($jsonNotation);
    }
    
    protected function parseFieldCriterion($fieldCriterion) {
        if (is_array($fieldCriterion)) return $fieldCriterion; // unchanged
        $s = (string) $fieldCriterion;
        $shouldParse = false;
        $len = strlen($s);
        if ($len > 1) {
            $first = $s[0];
            $last = $s[-1];
            if ($first === '!') return ['not' => $this->parseFieldCriterion(substr($s, 1))];
            if ($first === '/' && preg_match("#^/.*/\\w+$#", $s)) return ['rx' => $s];
            if ($first === '"' && $last === '"') $shouldParse = true;
            elseif ($first === "'" && $last === "'") $shouldParse = true;
            elseif ($first === "[" && $last === "]") $shouldParse = true;
            elseif ($first === "{" && $last === "}") $shouldParse = true;
            if ($shouldParse) return json_decode($fieldCriterion, true);
        }
        if (strpos($s, ",") !== false) {
            return array_map([$this, "parseFieldCriterion"], explode(",", $s));
        }
        if ($len > 2 && strpos($s, "..") !== false) {
            $minMax = explode('..', $s, 2);
            $minMaxProto = [];
            if (strlen($minMax[0])) $minMaxProto['min'] = $minMax[0];
            if (strlen($minMax[1])) $minMaxProto['max'] = $minMax[1];
            return $minMaxProto;
        }
        return $s;
    }
    
    protected function parseJsonExpression(array $json) {
        $isNumeric = self::isNumericArray($json);
        if (!$isNumeric) {
            return $this->parseJsonCriterion($json);
        }
        return [
            'class' => Ac_Model_Condition_PropertyCondition::class,
            'matchAll' => true,
            'conditions' => array_map([$this, 'parseJsonExpression'], $json)
        ];
    }
    
    protected function parseJsonCriterion($json) {
        
        if (!is_array($json) || !count($json)) {
            throw new Exception("Expected: array with at least one element");
        }
        
        $stmt = null;
        
        if (isset($json[$k = '$not']) || isset($json[$k = '$or']) || isset($json[$k = '$and'])) {
            $stmt = $k;
            if (count($json) > 1 || !is_array($json[$k])) {
                throw new Exception("'{$k}' expression must contain only one, array element");
            }
            return [
                'class' => Ac_Model_Condition_PropertyCondition::class,
                'not' => ($k == '$not'),
                'matchAll' => ($k == '$and'),
                'conditions' => [$this->parseJsonExpression($json)]
            ];
        }
        
        $crit = [];
        
        foreach ($json as $property => $valueCriterion) {
            $crit[] = [
                'class' => Ac_Model_Condition_PropertyCondition::class,
                'property' => $property,
                'matchAll' => false,
                'conditions' => [$this->parseValueCriterion($valueCriterion)]
            ];
        }
        
        if (count($crit) == 1) return $crit[0];
        
        return [
            'class' => Ac_Model_Condition_PropertyCondition::class,
            'matchAll' => true,
            'conditions' => $crit
        ];
        
    }
    
    protected function parseValueCriterion($json) {
        
        if (is_array($json) && !count($json)) {
            throw new Exception("Value criterion cannot be an empty array");
        }
        
        $scalarOrScalarArray = 
            is_scalar($json) 
            || self::isNumericArray($json) && count($json) == count(array_filter($json, 'is_scalar'));
        
        if ($scalarOrScalarArray) {
            return [
                'class' => Ac_Model_Condition_EqualsCondition::class,
                'value' => $json
            ];
        }
        
        if (self::isNumericArray($json)) {
            return [
                'class' => Ac_Model_Condition_MultiCondition::class,
                'conditions' => array_map([$this, 'parseValueCriterion'], $json)
            ];
        }
        
        if (isset($json['and'])) {
            if (count($json) != 1) throw Exception("'and' must be the only key in 'and' value criterion");
            if (!count($json['and']) || !self::isNumericArray($json)) {
                throw Exception("'and' must be non-empty numeric array in value criterion");
            }
            return [
                'class' => Ac_Model_Condition_MultiCondition::class,
                'matchAll' => true,
                'conditions' => array_map($json['and'], [$this, 'parseValueCriterion'])
            ];
        }
        
        if (isset($json['not'])) {
            if (count($json) != 1) throw Exception("'not' must be the only key in 'not' value criterion");
            return [
                'class' => Ac_Model_Condition_MultiCondition::class,
                'not' => true,
                'conditions' => [$this->parseValueCriterion($json['not'])]
            ];
        }
        
        if (isset($json['rx'])) {
            if (count($json) != 1) throw Exception("'rx' must be the only key in regexp value criterion");
            return [
                'class' => Ac_Model_Condition_RegexpCondition::class,
                'regexp' => $json['rx']
            ];
        }
        
        if (count($json) == 1 && isset($json['empty'])) {
            return [
                'class' => Ac_Model_Condition_EmptyCondition::class,
            ];
        }
        
        if (isset($json['min']) || isset($json['max'])) {
            if ((array_diff(array_keys($json), ['min', 'max']))) {
                throw new Exception("min..max value criterion shouldn't have other keys");
            }
            return [
                'class' => Ac_Model_Condition_RangeCondition::class,
                'min' => isset($json['min'])? $json['min'] : false,
                'max' => isset($json['max'])? $json['max'] : false,
            ];
        }
        
    }
    
    static function isNumericArray($var) {
        return is_array($var) && count(array_filter(array_keys($var), 'is_numeric')) === count($var);
    }
    
}