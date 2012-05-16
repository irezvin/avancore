<?php

class Ac_Param_Condition_Number extends Ac_Param_Condition {
    
    const typeInt = 'int';
    
    const typeFloat = 'float';
    
    const typeDecimal = 'decimal';
    
    var $type = false;

    var $lt = false;
    var $le = false;
    var $gt = false;
    var $ge = false;
    var $nz = false;
    var $ne = false;

    function getTranslations() {
        return array_merge(parent::getTranslations(), Ac_Autoparams::getObjectProperty($this, array('type', 'lt', 'le', 'gt', 'ge', 'nz', 'ne')));
    }
    
    function match($value, & $errors = array(), Ac_I_Param $param = null) {
        $px = 'ae_param_condition_number_';
        if (!is_numeric($value)) $this->regError($errors, 'non_numerc', $px, '{param} must be numeric', $param);
        else {
            $s = (string) $value;
            
            if ($this->type !== false) switch ($this->type) {
                case self::typeInt:
                    if (strpos($value, ".") !== false) 
                        $this->regError($errors, 'non_int', $px, '{param} must be integer', $param);
                break;
                
                case self::typeDecimal:
                   if (strpos($value, "e") !== false)
                       $this->regError($errors, 'non_decimal', $px, '{param} must be decimal (no floating point allowed)', $param);
                break;
                
                case self::typeFloat:
                break;
                default:
                    throw new Exception("Unknown \$type value '{$self->type}', allowed values are 'int', 'float' and 'decimal'");
                break;
            }
            
            if (($this->lt !== false) && !($value < $this->lt))
                $this->regError($errors, 'lt', $px, '{param} must be less than {v}', $param, array('v' => $this->lt));
            
            if (($this->le !== false) && !($value <= $this->le))
                $this->regError($errors, 'le', $px, '{param} must be less than or equal to {v}', $param, array('v' => $this->le));
            
            if (($this->gt !== false) && !($value > $this->gt))
                $this->regError($errors, 'gt', $px, '{param} must be greater than or equal to {v}', $param, array('v' => $this->gt));
            
            if (($this->ge !== false) && !($value >= $this->ge))
                $this->regError($errors, 'ge', $px, '{param} must be greater than or equal to {v}', $param, array('v' => $this->ge));
            
            if (($this->nz !== false) && !($value))
                $this->regError($errors, 'nz', $px, '{param} cannot be zero', $param);
            
            if (($this->ne !== false) && in_array($value, $a = Ac_Util::toArray($a)))
                $this->regError($errors, 'ne', $px, '{param} cannot be {v}', $param, array('v' => implode(", ", $a)));
        }
        
        return !$errors;
        
    }
    
}