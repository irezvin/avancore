<?php

class Ac_Model_Validator {

    /**
     * Error messages and their parts
     */
    var $msgs = array(
        'fieldWithCaption' => 'field \'[:caption]\'',
        'field' => 'this field',
        'required' => '[:fld] is required to fill-in',
        'intType' => '[:fld] should contain integer number (example: 1234)',
        'floatType' => '[:fld] should contain decimal number (example: 1.234)',
        'stringType' => '[:fld] should be a string',
        'dateType' => '[:fld] should contain correct date (example: 23.12.1981 or 1981-12-23)',
        'timeType' => '[:fld] should contain correct time (example: 23:55)',
        'dateTimeType' => '[:fld] should contain correct data and time (examples: 23.12.1981 23:55 or 23:55 1981-12-23 and so on)',
        'le' => '[:fld] should contain value not greater than [:val]',
        'ge' => '[:fld] should not contain value less than [:val]',
        'lt' => '[:fld] should contain value less than [:val]',
        'gt' => '[:fld] should contain value greater than [:val]',
        'nz' => '[:fld] should not contain zero',
        'rx' => '[:fld] contains incorrect value',
        'maxLength' => '[:fld] should not be longer than [:val] characters',
        'valueList' => '[:fld] contains value that isn\'t in allowed range',
        'future' => '[:fld] cannot contain date that is in the past',
        'past' => '[:fld] cannot contain date that is in the future',
    );
    
    var $zeroDate = '0000-00-00 00:00:00';

    var $defaultZeroDateValue = array(
        'date' => '0000-00-00',
        'time' => '00:00:00',
        'dateTime' => '0000-00-00 00:00:00',
    );
    
    var $defaultInternalDateFormat = array(
        'date' => 'U',
        'time' => 'U',
        'dateTime' => 'U',
    );
    
    var $defaultOutputDateFormat = array(
        'date' => 'Y-m-d',
        'time' => 'H:i:s',
        'dateTime' => 'Y-m-d H:i:s',
    );
    
    /**
     * Valid decimal separators for decimal values
     * @var array 
     */
    var $decimalSeparators = array('.', ',');
    
    /**
     * Fields metadata or FALSE.
     *
     * Currently following metadata keys are used:
     * 'dataType', 'required', 'le', 'lt', 'ge', 'gt', 'nz', 'valueList', 
     * 'internalDateFormat', 'outputDateFormat' (for date and time values)
     *  
     * @var array|false
     */
    var $fieldsInfo = false;
    
    /**
     * @var object|array
     */
    var $model = false;
    
    /**
     * @var array
     */
    var $errors = array();
    
    /**
     * If BOTH $this->fieldsInfo[someField] is set AND $model->getFormOptions($fieldName) returns data, 
     * merge the results ($this->fieldsInfo will override model formOptions)  
     * @var bool
     */
    var $mergeFieldsInfo = false;
    
    var $_fieldsInfo = array();
    
    /**
     * @var array
     */
    var $fieldList = false;
    
    var $dontCheckReadOnly = false;
    var $_dontCheck = false;
    
    /**
     * @param object|array $model Model that we work with (can be object or associative array)
     * @param bool|array $fieldsInfo Array with fields metadata (array('fieldName' => array(...))) or FALSE (metadata will be taken from $model)
     * @return Ac_Model_Validator
     */
    function factory ($model, $fieldsInfo = false) {
        $res = new Ac_Model_Validator($model, $fieldsInfo);
        return $res;
    }
    
    /**
     * @param object|array $model Model that we work with (can be object or associative array)
     * @param bool|array $fieldsInfo Array with fields metadata (array('fieldName' => array(...))) or FALSE (metadata will be taken from $model)
     */
    function __construct($model, $fieldsInfo = false) {
        $this->model = $model;
        $this->fieldsInfo = $fieldsInfo;
        if (!is_object($model) && !$fieldsInfo) trigger_error ('Model is not an object and won\'t be able to return any metadata; $fieldsInfo is also empty.', E_USER_WARNING);
        
        if (isset($GLOBALS['Ac_Validator_Msgs']) && is_object($GLOBALS['Ac_Validator_Msgs'])) {
            $this->msgs = get_object_vars($GLOBALS['Ac_Validator_Msgs']);
        }
        
        if (defined('AC_VALIDATOR_MSGS')) {
            foreach (array_keys($this->msgs) as $msgId) {
                $defName = 'AC_VALIDATOR_MSG_'.strtoupper($msgId);
                if (defined($defName)) $this->msgs[$msgId] = constant($defName);
            }
        }
    }
    
    function resetErrors() {
        $this->errors = array();
        $this->_fieldsInfo = array();    
    }
    
    function getErrors($fieldName = false) {
        if ($fieldName !== false) {
            if (isset($this->errors[$fieldName])) {
                if (is_array($this->errors[$fieldName])) $res = Ac_Util::implode_r("; ", $this->errors[$fieldName]);
                    else $res = $this->errors[$fieldName];
                if (strlen($res)) $res = ucfirst($res);
            } else $res = false;
        } else {
            $res = array();
            foreach (array_keys($this->errors) as $fieldName) $res[$fieldName] = $this->getErrors($fieldName);
        }
        return $res;
    }
    
    function substitute($src, $replacements = array()) {
        foreach ($replacements as $rep => $str) if(is_scalar($str)) $src = str_replace('[:'.$rep.']', $str, $src);
        return $src; 
    }
    
    function makeErrorMsg($msgId, $fieldOptions = array(), $substitutions = array()) {
        if (isset($fieldOptions['msgs']) && is_array($fieldOptions['msgs']) && isset($fieldOptions['msgs'][$msgId])) {
            $message = $fieldOptions['msgs'][$msgId];
        } else {
            $message = $this->msgs[$msgId];
        }
        if ($msgId !== 'fieldWithCaption' && $msgId !== 'field') {
            if (isset($fieldOptions['caption'])) {
                $fld = $this->makeErrorMsg('fieldWithCaption', $fieldOptions, array('caption' => strip_tags($fieldOptions['caption'])));
            } else {
                $fld = $this->makeErrorMsg('field', $fieldOptions);
            }
            $substitutions['fld'] = $fld;
        }
        $substitutions = array_merge($fieldOptions, $substitutions);
        $res = $this->substitute($message, $substitutions);
        return $res;
    }
    
    function _getMethod($target, $prefix, $suffix = '') {
        $methodName = $prefix.$suffix;
        if (method_exists($target, $methodName)) $res = $methodName;
            else $res = false;
        return $res;
    }
    
    function getFieldValue($fieldName, $default = null, $fieldInfo = false) {
        $res = $default;
        
        if (isset($fieldInfo['assocClass']) && $fieldInfo['assocClass']) return $res;
        if (isset($fieldInfo['value'])) {
            $res = $fieldInfo['value'];
            return $res; 
        }
        
        list($head, $tail) = Ac_Util::pathHeadTail($fieldName);        
        if (is_a($this->model, 'Ac_Model_Data')) {
            $res = $this->model->getField($fieldName);
        }
        elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'get', $head)) {
            if (strlen($tail)) $res = $this->model->$method($tail); 
                else $res = $this->model->$method(); 
        }
        elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'getProperty')) {
            $res = $this->model->$method($fieldName);
        }
        elseif (is_array($this->model)) {
            $tail = $fieldName;
            $val = $this->model;
            do {
                if (is_array($val) && isset($val[$head])) {
                    $val = $val[$head];
                    $found = true;
                } else {
                    $found = false;
                }
            } while ($found && strlen($tail));
            if ($found) $res = $val;                
        }
        return $res;
    }
    
    function setFieldValue($fieldName, $value) {
        list($head, $tail) = Ac_Util::pathHeadTail($fieldName);
        
        if (is_a($this->model, 'Ac_Model_Data')) $this->model->setField($fieldName, $value);
        elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'set', $head)) {
            if (strlen($tail)) $res = $this->model->$method($value, $tail); 
                else $res = $this->model->$method($value); 
        } 
        elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'setProperty')) {
            $res = $this->model->$method($fieldName, $value);
        }
        elseif (is_array($this->model)) {
            $tail = $fieldName;
            $val = $this->model;
            do {
                if (is_array($val) && isset($val[$head])) {
                    $val = $val[$head];
                } else {
                    $val[$head] = array();
                }
            } while ($found && strlen($tail));
            $val = $value;                
        }
    }
    
    function valueIsEmpty($value) {
        if (is_object($value)) $res = false;
        elseif (is_array($value)) $res = !count($value);
        else $res = is_null($value) || !strlen($value);
        return $res;
    }
    
    function listFields() {
        $res = false;
        if (is_array($this->fieldList)) {
            $res = $this->fieldList;
        } elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'listFields', '')) {
            $res = $this->model->$method();
        } elseif (is_object($this->model) && $method = $this->_getMethod($this->model, 'listPublicProperties', '')) {
            $res = $this->model->$method();
        } elseif (is_array($this->fieldsInfo)) {
            $res = array_keys($this->fieldsInfo);
        } elseif (is_array($this->model)) {
            $res = array_keys($this->model); 
        } else {
            $res = array();
        }
        
        if (!is_array($this->_dontCheck)) {
            $this->_dontCheck = array();
            foreach ($res as $fieldName) {
                $fi = $this->getFieldInfo($fieldName);
                if ($this->dontCheckReadOnly && isset($fi['readOnly']) && $fi['readOnly']) $this->_dontCheck[] = $fieldName;
                elseif (isset($fi['autoValidate']) && !$fi['autoValidate']) $this->_dontCheck[] = $fieldName;
            }
        }
        $res = array_diff ($res, $this->_dontCheck);
        return $res;
    }
    
    function getFieldInfo($fieldName) {
        if (isset($this->_fieldsInfo[$fieldName])) return $this->_fieldsInfo[$fieldName];
        if (is_object($this->model) && $method = $this->_getMethod($this->model, 'getFormOptions', '')) {
            $res = $this->model->$method($fieldName);
            if (!is_array($res)) $res = array();
            if (is_array($this->fieldsInfo) && $this->mergeFieldsInfo && isset($this->fieldsInfo[$fieldName]) && is_array($this->fieldsInfo[$fieldName])) {
                $res = array_merge($res, $this->fieldsInfo[$fieldName]);
            }
        } elseif (is_array($this->fieldsInfo) && $this->mergeFieldsInfo && isset($this->fieldsInfo[$fieldName]) && is_array($this->fieldsInfo[$fieldName])) {
            $res = $this->fieldsInfo[$fieldName];
        } else {
            $res = array();
        }
        if(!isset($res['valueList']) && isset($res['values']) && (
            is_array($res['values']) 
            || is_object($res['values']) && ($res['values'] instanceof Ac_Model_Values))
        ) {
            if (!is_object($res['values'])) {
                if (is_object($this->model)) {
                    $vals = Ac_Model_Values::factoryWithProperty ($this->model->getPropertyInfo($fieldName));
                } else $vals = Ac_Model_Values::factoryIndependent($res['values']);
            }
            else $vals = $res['values'];
            if (is_object($this->model)) $vals->setData($this->model);
            $res['values'] = $vals;
        }
        $this->_fieldsInfo[$fieldName] = $res;
        return $res;
    }
    
    function checkForRequiredFields() {
        $res = true;
        foreach ($this->listFields() as $fieldName) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            $res = $this->_checkForRequiredField($fieldName, $fieldInfo) && $res;
        }
        return $res;
    }
    
    function _checkForRequiredField($fieldName, $fieldInfo) {
        $res = true;
        if (!(isset($fieldInfo['skipValidation']) && $fieldInfo['skipValidation'])) {
            if (isset($fieldInfo['required']) && $fieldInfo['required']) {
                $value = $this->convertValue($this->getFieldValue($fieldName, null, $fieldInfo), false, $fieldInfo);
                if ($this->valueIsEmpty($value)) {
                    $res = false;
                    $this->errors[$fieldName]['required'] = $this->makeErrorMsg('required', $fieldInfo);
                } else {
                }
            }
        }
        return $res;
    }
    
    function myGmDate($format, $date) {
        
        if ((intval(AC_PHP_VERSION_MAJOR) >= 5) && (intval(AC_PHP_VERSION_MINOR) >= 1)) {
            $isWrongDate = $date === false;     
        } else {
            $isWrongDate = $date == -1;
        }
        if ($isWrongDate) return false;
        $res = gmdate($format, $date);
        if ($format == 'U') $res = intval($res);
        return $res;
    }
    
    /**
     * Understands following data types ('dataType'): int, float, date, time, dateTime
     */
    function checkForDataTypes($modifyModel = false) {
        $res = true;
        foreach ($this->listFields() as $fieldName) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            $res = $this->_checkForDataType($fieldName, $fieldInfo, $modifyModel) && $res; 
        }
        return $res;
    }
    
    function _checkForDataType($fieldName, $fieldInfo, $modifyModel) {
        $res = true;
        if (!(isset($fieldInfo['skipValidation']) && $fieldInfo['skipValidation'])) {
            $fieldValue = $this->getFieldValue($fieldName, null, $fieldInfo);
            if (!$this->valueIsEmpty($fieldValue)) {
                $errValue = null;
                if (isset($fieldInfo['isNullable']) && $fieldInfo['isNullable']) $errValue = md5(microtime().rand());
                    else $errValue = null;
                $cValue = $this->convertValue($fieldValue, false, $fieldInfo, $errValue);
                if ($cValue === $errValue) {
                    $type = isset($fieldInfo['dataType'])? $fieldInfo['dataType'] : 'string';
                    $this->errors[$fieldName]['type'] = $this->makeErrorMsg($type.'Type', $fieldInfo);
                    $res = false; 
                } 
                elseif ($modifyModel && $fieldValue !== $cValue) {
                    $this->setFieldValue($fieldName, $cValue);
                }
            }
        }
        return $res;
    }
    
    function getInternalDateFormat($fieldInfo, $type = 'date') {
        if (isset($fieldInfo['internalDateFormat'])) $res = $fieldInfo['internalDateFormat'];
            else $res = $this->defaultInternalDateFormat[$type];
        return $res;
    }
    
    function getZeroDateValue($fieldInfo, $type = 'date') {
        if (isset($fieldInfo['zeroDateValue'])) $res = $fieldInfo['zeroDateValue'];
            else $res = $this->defaultZeroDateValue[$type];
        return $res;
    }
    
    function getOutputDateFormat($fieldInfo, $type = 'date') {
        if (isset($fieldInfo['outputDateFormat'])) $res = $fieldInfo['outputDateFormat'];
            else $res = $this->defaultOutputDateFormat[$type];
        return $res;
    }
    
    /**
     * @param mixed $value
     * @param string|mixed $type string/int/float/date/time/dateTime or FALSE to get it from $fieldInfo (default type is string)
     * @param array $fieldInfo Field metadata. Can contain keys 'noTrim' (otherwise $value is trimmed), 'allowHtml' (otherwise tags will be stripped out of $value)  
     * @param mixed $errValue value to return when conversion cannot be done
     * @return mixed Typed value or $errValue if it can't convert value 
     */
    function convertValue($value, $type = false, $fieldInfo = array(), $errValue = null) {
        if (isset($fieldInfo['skipValidation']) && $fieldInfo['skipValidation']) return $value;
        if (is_array($value)) {
            if (isset($fieldInfo['plural']) || isset($fieldInfo['arrayValue'])) {
                $res = array();
                $intFi = $fieldInfo;
                unset($intFi['plural']);
                unset($intFi['arrayValue']);
                if (isset($fieldInfo['restrictToListOnConvert']) && $fieldInfo['restrictToListOnConvert']) { 
                    if (isset($fieldInfo['valueList']) && is_array($fieldInfo['valueList'])) {
                        foreach ($value as $key => $val) {
                            if (!is_scalar($val) || !isset($fieldInfo['valueList'][$val])) unset($value[$key]);
                        }
                    } elseif (isset($fieldInfo['values']) && is_object($fieldInfo['values']) 
                        && $fieldInfo['values'] instanceof Ac_Model_Values) {
                        $value = $fieldInfo['values']->filterValuesArray($value);
                    }
                }
                foreach ($value as $key => $val) {
                    if (($item = $this->convertValue($val, $type, $intFi, $errValue)) !== $errValue)
                        $res[$key] = $item;    
                }
            } else {
                $res = $errValue;
            }
        } else {
            $noTrim = isset($fieldInfo['noTrim']) && $fieldInfo['noTrim'];
            $allowHtml = isset($fieldInfo['allowHtml']) && $fieldInfo['allowHtml'];
            if (!is_object($value)) {
            	if (!$noTrim) $value = trim($value);
            	if (!$allowHtml) $value = strip_tags($value);
            }
            $res = $errValue;
            if ($type === false && isset($fieldInfo['dataType'])) $type = $fieldInfo['dataType'];
            
            $canBeNull = isset($fieldInfo['isNullable']) && $fieldInfo['isNullable'] ;
            
            // we still allow nulls for value-restricted fields
            // TODO: allow them only if they have respecitve satisfied to-one associations
            $canBeNull = $canBeNull || isset($fieldInfo['values']) || isset($fieldInfo['valueList']);
            
            if (is_null($value) && $canBeNull) $value = null;
            else {
                switch ($type) {
                    case 'int':
                        if (is_numeric($value) && intval($value) == $value) $res = $value; 
                        break;
                        
                    case 'float':
                        foreach($this->decimalSeparators as $decSep) $value = str_replace($decSep, '.', $value);
                        if (is_numeric($value)) $res = floatval($value);
                        break;
                        
                    case 'date':
                        $arrDate = Ac_Model_DateTime::arrayFromString($value);
                        if (isset($arrDate['year']) && isset($arrDate['month']) && isset($arrDate['mday'])) {
                            if (Ac_Model_DateTime::isZeroDate($arrDate)) {
                                $res = $this->getZeroDateValue($fieldInfo, $type);
                            } else {
                                $ts = gmmktime(0, 0, 0, $arrDate['month'], $arrDate['mday'], $arrDate['year']);
                                $res = $this->myGmDate($this->getInternalDateFormat($fieldInfo, $type), $ts);
                            }
                        }
                        break;
                        
                    case 'time':
                        $arrDate = Ac_Model_DateTime::arrayFromString($value);
                        if (isset($arrDate['hours']) && isset($arrDate['minutes'])) {
                            if (!isset($arrDate['seconds'])) $arrDate['seconds'] = 0;
                            $ts = gmmktime($arrDate['hours'], $arrDate['minutes'], $arrDate['seconds']);
                            $res = $this->myGmDate($this->getInternalDateFormat($fieldInfo, $type), $ts);
                        }
                        break;
                        
                    case 'dateTime':
                        $arrDate = Ac_Model_DateTime::arrayFromString($value);
                        if (Ac_Model_DateTime::isZeroDate($arrDate)) {
                            $res = $this->getZeroDateValue($fieldInfo, $type);
                        } else {
                            if (($this->getInternalDateFormat($fieldInfo, $type) === 'Y-m-d H:i:s') && ($value === '0000-00-00 00:00:00')) $res = $value; else {                    
                                $arrDate = Ac_Model_DateTime::arrayFromString($value);
                                if (isset($arrDate['year']) && isset($arrDate['month']) && isset($arrDate['mday']) && isset($arrDate['hours']) && isset($arrDate['minutes'])) {
                                    if (!isset($arrDate['seconds'])) $arrDate['seconds'] = 0;
                                    $ts = gmmktime($arrDate['hours'], $arrDate['minutes'], $arrDate['seconds'], $arrDate['month'], $arrDate['mday'], $arrDate['year']);
                                    $res = $this->myGmDate($this->getInternalDateFormat($fieldInfo, $type), $ts);
                                }
                            }
                        }
                        break;
                                    
                    default:
                    case 'string':
                        $res = $value;
                }
            }
        }
        return $res;
    }
    
    /**
     * Understands following bounds: 'le', 'lt', 'ge', 'gt', 'rx'
     */
    function checkForBounds() {
        $res = true;
        foreach ($this->listFields() as $fieldName) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            $res = $this->_checkForBounds($fieldName, $fieldInfo) && $res;
        }
        return $res;
    }
    
    function _checkForBounds($fieldName, $fieldInfo) {
        $res = true;
        $fieldInfo['internalDateFormat'] = 'U';
        $fieldValue = $this->convertValue($this->getFieldValue($fieldName, null, $fieldInfo), false, $fieldInfo);
        if (!$this->valueIsEmpty($fieldValue)) {
            foreach (array('le', 'lt', 'ge', 'gt', 'nz', 'rx', 'maxLength', 'future', 'past') as $c) {
                $boundValue = false;
                if (isset($fieldInfo[$c]) && ($c == 'future' || $c == 'past' || strlen($boundValue = $this->convertValue($fieldInfo[$c], false, $fieldInfo)))) {
                    switch ($c) {
                        case 'le': $ok = $fieldValue <= $boundValue; break;
                        case 'lt': $ok = $fieldValue <  $boundValue; break;
                        case 'ge': $ok = $fieldValue >= $boundValue; break;
                        case 'gt': $ok = $fieldValue >  $boundValue; break;
                        case 'rx': 
                            $ok = preg_match($boundValue, $fieldValue); break;
                        case 'nz': $ok = (string) $fieldValue != '0'; break;
                        case 'maxLength': 
                                if (defined('AC_VALIDATOR_CHARSET')) {
                                    $len = mb_strlen($fieldValue, AC_VALIDATOR_CHARSET);
                                } else {
                                    $len = strlen($fieldValue);
                                }
                                $ok = ($len <= $boundValue);
                                break;
                        case 'future': $ok = $fieldValue > time(); break;
                        case 'past': $ok = $fieldValue < time(); break;
                    }
                    if (isset($fieldInfo['dataType']) && in_array($fieldInfo['dataType'], array('date', 'time', 'dateTime'))) {
                        $val = $this->myGmDate($this->getOutputDateFormat($fieldInfo, $fieldInfo['dataType']), $boundValue); 
                    } else $val = $boundValue;
                    if (!$ok) {
                        $msg = isset($fieldInfo['msgs']) && is_array($fieldInfo['msgs']) && isset($fieldInfo['msgs'][$c])?
                            $fieldInfo['msgs'][$c] : $this->msgs[$c];
                        $this->errors[$fieldName][$c] = $this->makeErrorMsg($c, $fieldInfo, array('val' => $val));
                        $res = false;
                    }
                }
            }
        }
        return $res;
    }
    
    /**
     * Checks value to be in 'valueList' 
     */
    function checkForLists() {
        $res = true;
        foreach ($this->listFields() as $fieldName) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            $res = $this->_checkForList($fieldName, $fieldInfo);
        }
        return $res;
    }
    
    function _checkForList($fieldName, $fieldInfo) {
        if (isset($fieldInfo['skipValidation']) && $fieldInfo['skipValidation']) return;
        $res = true;
        $valueList = null;
        if (isset($fieldInfo['valueList']) && is_array($fieldInfo['valueList'])) $valueList = $fieldInfo['valueList'];
        elseif (isset($fieldInfo['values']) && is_object($fieldInfo['values']) && $fieldInfo['values'] instanceof Ac_Model_Values) {
            $valueList = $fieldInfo['values'];
        }
        if (is_array($valueList) || is_object($valueList)) {
            $fieldValue = $this->convertValue($this->getFieldValue($fieldName, false, $fieldInfo), false, $fieldInfo);
            if (!$fieldValue) $fieldValue = null;
            if (!$this->valueIsEmpty($fieldValue)) {
                if (is_array($valueList)) {
                    if (!is_array($fieldValue)) $fieldValue = array($fieldValue);
                    foreach ($fieldValue as $fv) {
                        if (!strlen($fv) || !isset($valueList[$fv])) {
                            $res = false;
                            break;
                        }
                    } 
                } elseif ($valueList instanceof Ac_Model_Values) { // it's an Ac_Model_Values
                    if (is_array($fieldValue)) {
                        $res = count($fieldValue) == count($valueList->filterValuesArray($fieldValue));
                    } else {
                        $res = $valueList->check($fieldValue);
                    }
                }
                if (!$res) {
                    $this->errors[$fieldName]['valueList'] = $this->makeErrorMsg('valueList', $fieldInfo);
                }
            }
        } 
        return $res;
    }
    
    /**
     * @param bool $modifyModel Whether to update model with converted or modified values
     */
    function check($modifyModel = false) {
        $this->resetErrors();
        
        foreach ($this->listFields() as $fieldName) {
            $fieldInfo = $this->getFieldInfo($fieldName);
            if (!isset($fieldInfo['value'])) {
                $fieldInfo['value'] = $this->getFieldValue($fieldName, null, $fieldInfo);
            }
            $fieldValue = $fieldInfo['value'];
            
            if ($this->_checkForRequiredField($fieldName, $fieldInfo) && !is_null($fieldValue)) {   
                $this->_checkForDataType($fieldName, $fieldInfo, $modifyModel);
                $this->_checkForBounds($fieldName, $fieldInfo);
                if (!isset($fieldInfo['allowValuesOutOfList']) || !$fieldInfo['allowValuesOutOfList'])
                    $this->_checkForList($fieldName, $fieldInfo);
            }
        }
        
        return !$this->errors;
    }
    
}

