<?php

class Ac_Etl_Detector_Data_String extends Ac_Etl_Detector_Data {
    
    protected $minLength = false;

    protected $maxLength = false;
    
    /**
     * @var bool
     */
    protected $detectSimpleEnums = true;

    /**
     * @var bool
     */
    protected $enumSizeLimit = 10;
    
    protected $considerEmptyAsPass = true;
    
    protected $enum = array();
    
    function reset() {
        parent::reset();
        $this->minLength = false;
        $this->maxLength = false;
        $this->enum = array();
    }
    
    function getEnum() {
        return $this->enum;
    }
    
    function accept($value, $problems = false) {
        parent::accept($value);
        $len = mb_strlen($value, 'utf-8');
        if (!$problems && $len > 0) {
            if ($this->detectSimpleEnums && $len == 1 && !is_numeric($value)) {
                $this->enum[$value] = $value;
            }
            if ($this->minLength === false || $this->minLength > $len) $this->minLength = $len;
            if ($this->maxLength === false || $this->maxLength < $len) $this->maxLength = $len;
        }
    }
    
    function getMinValue() {
        return $this->minValue;
    }

    function getMaxValue() {
        return $this->maxValue;
    }
    
    function getColumnDefinition(Ac_Sql_Db $db) {
        $res[] = $db->n($this->fieldName);
        $enum = false;
        if ($this->detectSimpleEnums) {
            if ($this->minLength == 1 && $this->maxLength == 1 && count($this->enum) <= $this->enumSizeLimit && count($this->enum) > 1) {
                $im = implode('', $this->enum);
                if (strtolower($im) == $im || strtoupper($im) == $im) {
                    $enum = true;
                    $res[] = "ENUM(".$db->q($this->enum).")";
                    if ($this->numNulls || $this->numEmpty) $res[] = "NULL";
                        else $res[] = "NOT NULL";
                }
            }
        }
        if (!$enum) {
            if ($this->maxLength < 255) {
                if (!$this->maxLength) {
                    $res[] = "TINYTEXT";
                } else {
                    if ($this->maxLength === 1) {
                        $res[] = "VARCHAR(1)";
                    } else {
                        if ($this->maxLength < 10) $l = 50;
                        elseif ($this->maxLength < 50) $l = 100;
                        elseif ($this->maxLength < 100) $l = 255;
                        else $l = 255;
                        $res[] = "VARCHAR({$l})";
                    }
                    if ($this->numNulls) $res[] = "NULL";
                        else $res[] = "NOT NULL DEFAULT ''";
                }
            }
            elseif ($this->maxLength < 65536) $res[] = "MEDIUMTEXT";
            else $res[] = "LONGTEXT";
        }
        return implode(" ", $res);
    }

    /**
     * @param bool $detectSimpleEnums
     */
    function setDetectSimpleEnums($detectSimpleEnums) {
        $this->detectSimpleEnums = $detectSimpleEnums;
    }

    /**
     * @return bool
     */
    function getDetectSimpleEnums() {
        return $this->detectSimpleEnums;
    }

    /**
     * @param bool $enumSizeLimit
     */
    function setEnumSizeLimit($enumSizeLimit) {
        $this->enumSizeLimit= $enumSizeLimit;
    }

    /**
     * @return bool
     */
    function getEnumSizeLimit() {
        return $this->enumSizeLimit;
    }
    
}