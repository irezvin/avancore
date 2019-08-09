<?php

class Ac_Etl_Detector_Data_Number extends Ac_Etl_Detector_Data {
    
    protected $minValue = false;

    protected $maxValue = false;
    
    protected $maxLength = 0;
    
    protected $maxPrecision = 0;

    protected $minLength = false;
    
    protected $minPrecision = false;

    function reset() {
        parent::reset();
        $this->minValue = false;
        $this->maxValue = false;
        $this->maxPrecision = 0;
        $this->maxLength = 0;
        $this->minPrecision = 0;
        $this->minLength = 0;
    }
    
    function accept($value, $problems = false) {
        parent::accept($value, $problems);
        if (!strlen($problems)) {
            if (is_numeric($value)) {
                $foobar = explode('.', ltrim($value, '-'));
                $foobar[] = '';
                $length = strlen($foobar[0].$foobar[1]);
                $precision = strlen($foobar[1]);
                if ($this->maxLength < $length) $this->maxLength = $length;
                if ($this->maxPrecision < $precision) $this->maxPrecision = $precision;
                if ($this->minLength === false || $this->minLength > $length) $this->minLength = $length;
                if ($this->minPrecision === false || $this->minPrecision > $precision) $this->minPrecision = $precision;
                if ($this->minValue === false || $this->minValue > $value) $this->minValue = $value;
                if ($this->maxValue === false || $this->maxValue < $value) $this->maxValue = $value;
            }
        }
    }
    
    function getMinValue() {
        return $this->minValue;
    }

    function getMaxValue() {
        return $this->maxValue;
    }    
    
    function getMaxLength() {
        return $this->maxLength;
    }
    
    function getMaxPrecision() {
        return $this->maxPrecision;
    }

    function getColumnDefinition(Ac_Sql_Db $db) {
        $res[] = $db->n($this->fieldName);
        if ($this->minLength == 1 && $this->maxLength == 1) $length = 1;
        else $length = $this->maxLength < 10? 10 : $this->maxLength + 2;
        if ($this->minPrecision == $this->maxPrecision) $precision = $this->minPrecision;
            else {
                $precision = $this->maxPrecision + 2;
                $length += 2;
            }
        if ($this->maxPrecision) $res[] = "DECIMAL(".($length).','.($precision + 1).")";
            else $res[] = "INT(".($length).")";
        if ($this->minValue >= 0) $res[] = "UNSIGNED";
        if ($this->numNulls) $res[] = "NULL";
            else $res[] = "NOT NULL DEFAULT 0";
        return implode(" ", $res);
    }

    
}