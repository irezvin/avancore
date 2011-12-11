<?php

class Ae_Form_Helper {
    
    /**
     * Prefix to add before field names in controls
     * @var string 
     */
    
    var $fieldNamePrefix = false;
    
    /**
     * Captions for controls (only rendered if $showCaptions is on)
     * array('conrolName' => 'caption')
     * @var array
     */
    var $captions = array();
    
    /**
     * @var bool
     */
    var $showCaptions = true;
    var $srcReturnsCaptions = false;
    
    var $requiredFields = array();
    var $showRequiredFields = true;
    var $srcReturnsRequiredFields = false;
    
    var $showErrors = true;
    var $errors = array();
    var $srcReturnsErrors = false;
    
    var $maxLength = array();
    var $srcReturnsMaxLength = false;
    
    var $extraData = array();
    var $srcReturnsExtraData = false;
    
    var $allowHtml = array();
    var $srcReturnsAllowHtml = false;
    
    var $values = array();
    var $srcReturnsValues = false;
    
    var $templates = array();
    var $srcReturnsTemplates = false;
    
    var $wrapperTemplates = array();
    var $srcReturnsWrapperTemplates = false;
    
    var $valueLists = array();
    var $srcReturnsValueLists = false;
    
    var $dummyCaptions = array();
    var $srcReturnsDummyCaptions = false;
    
    var $attribs = array();
    var $srcReturnsAttribs = false;
    
    var $controlTypes = array();
    var $srcReturnsControlTypes = false;
    
    var $templateWithCaption = false;
    
    var $tplTextInput = false;
    var $tplHidden = false;
    var $tplPassword = false;
    var $tplTextArea = false;
    var $tplRte = false;
    var $tplDateInput = false;
    var $tplSelectList = false;
    var $tplCheckbox = false;
    var $tplRadio = false;
    var $tplWrapper = false;

    var $attribsTextInput = array();
    var $attribsHidden = false;
    var $attribsPassword = array();
    var $attribsTextArea = array();
    var $attribsRte = array();
    var $attribsDateInput = array();
    var $attribsSelectList = array();
    var $attribsCheckbox = array();
    var $attribsRadio = array();
    
    var $defaultCheckedValue = false;
        
    var $srcReturnsOptions = false;
    
    var $rteWidth = 500;
    var $rteHeight = 300;
    
    var $textAreaCols = 40;
    var $textAreaRows = 5; 
    
    var $alwaysWrap = false;
    
    var $_allOptionsFromSrc = false;
    var $_src = false;
    var $_buffer = false;
    
    var $cacheOptions = true;
    var $_cachedOptions = array();    
    
    /**
     * This can be useful to give parameters to controls' templates
     */
    var $etc = array();
    
    // ---------- Constructor ---------
    
    function Ae_Form_Helper (& $src, $searchSrcCaps = false) {
        $this->setSrc ($src, $searchSrcCaps);
    }
    
    // ---------- Configuration methods ---------
    
    function setSrc(& $src, $searchSrcCaps = false) {
        $this->_src = & $src;
        $this->_allOptionsFromSrc = false;
        if ($this->_src && $searchSrcCaps) $this->searchSrcCaps();
    }
    
    // ---------- Reflection methods ---------
    
    function _listCaps() {
        return array(
            'error' => 'errors', 
            'maxLength' => 'maxLength', 
            'caption' => 'captions', 
            'required' => 'requiredFields', 
            'allowHtml' => 'allowHtml',
            'template' => 'templates',
            'wrapperTemplate' => 'wrapperTemplates',
            'valueList' => 'valueLists',
            'value' => 'values',
            'attribs' => 'attribs',
            'dummyCaption' => 'dummyCaptions',
            'controlType' => 'controlTypes',
            'extraData' => 'extraData',
        );
    }
    
    function listControls() { return $this->_listControls(); }
    
    function _listControls() {
        return array(
            'textInput',
            'hidden',
            'password',
            'textArea',
            'rte',
            'dateInput',
            'selectList',
            'checkbox',
            'radio',
            'wrapper',
        );
    }
    
    // ---------- Source Introspection methods ---------
    // TODO: Consider moving all introspection functions into separate class
    
    function searchSrcCaps() {
        $this->srcReturnsOptions = $this->_src && is_callable(array(& $this->_src, 'getFormOptions'));
        foreach ($this->_listCaps() as $opt => $opts) {
            $Opts = ucfirst($opts);
            $optName = 'srcReturns'.$Opts;
            $this->{$optName} = $this->_src && is_callable(array (& $this->_src, 'getForm'.$Opts));
        }
    }
    
    function setOptions($name, $options = array()) {
        if (is_array($name)) {
            foreach ($name as $subName => $options) $this->setOptions($name, $options);
        } else {
            foreach ($this->_listCaps() as $opt => $opts) {
                 if (isset($options[$opt])) $this->{$opts}[$name] = $options[$opt];
                 else unset($this->{$opts}[$name]);
            }
        }
    }
    
    function getOptions($name, $includeFromSrc = false) {
        if ($name === true) {
            $name = array();
            foreach ($this->_listCaps() as $opts) {
                $name = array_merge($name, array_keys($this->{$opts}));
            }
            return $this->getOptions($name, $includeFromSrc);
        }
        
        if (is_array($name)) {
            $res = array();
            foreach ($name as $subName) $res[$subName] = $this->getOptions($subName, $includeFromSrc);
            return $res;
        }
        
        $found = false;
        if ($this->cacheOptions) {
            $ifs = $includeFromSrc? 'y' : 'n'; 
            if (isset($this->_cachedOptions[$name]) && isset($this->_cachedOptions[$name][$ifs])) {
                $res = $this->_cachedOptions[$name][$ifs];
                $found = true;
            }
        }
        
        if (!$found) {
            $res = array();
            foreach ($this->_listCaps() as $opt => $opts) {
                if (isset($this->{$opts}[$name])) $res[$opt] = $this->{$opts}[$name]; 
            }
            if ($includeFromSrc) $res = array_merge($this->getOptionsFromSrc($name),$res);
            if ($this->cacheOptions) $this->_cachedOptions[$name][$ifs] = $res;
        } 
        
        return $res;
    }
    
    function getOptionsFromSrc($name) {
        if ($this->_src) { 
            if ($this->srcReturnsOptions) {
                $res = call_user_func(array(& $this->_src, 'getFormOptions'), $name); 
            } else {
                $res = $this->_getAllOptionsFromSrc($name);
            }
        } else $res = array();
        
        return $res;
    }
    
    function getOption($propName, $optionName, $defaultValue = false) {
        $options = $this->getOptions($propName, true);
        if (isset($options[$optionName])) $res = $options[$optionName]; else $res = $defaultValue;
        return $res;
    }
    
    function _getAllOptionsFromSrc($key = false) {
        if ($this->_allOptionsFromSrc === false) {
            $a = array();
            $res = array();
            foreach ($this->_listCaps() as $opt => $opts) {
                $enabledProp = 'srcReturns'.ucfirst($opts);
                if ($this->$enabledProp) {
                    $getter = array (& $this->_src, 'getForm'.ucfirst($opts));
                    foreach ($srcProp = call_user_method ($getter) as $propName => $propValue) {
                        $a[$propName][$opt] = $propValue; 
                    }
                } 
            }
            $this->_allOptionsFromSrc = $a;
        }
        if ($key !== false) $res = isset($this->_allOptionsFromSrc[$key])? $this->_allOptionsFromSrc[$key] : array();
            else $res = $this->_allOptionsFromSrc;
        return $res;
    }
    
    function getCaption($name) {
        $options = $this->getOptions($name, true);
        if (isset($options['caption'])) $res = $options['caption'];
            else $res = false;
        return $res;
    }
    
    // ---------- Rendering-related methods ---------
    
    function _renderTemplate($templateName, $vars = array()) {
        $disp = Ae_Dispatcher::getInstance();
        $templateName = $disp->getDir(false)."/templates/{$templateName}.tpl.php";
        extract($vars, EXTR_SKIP);
        include($templateName);
    }
    
    function _getTemplate($type, $options) {
        if ($type <> 'wrapper' && isset($options['template'])) $res = $options['template'];
            else {
                if ($type == 'wrapper' && isset($options['wrapperTemplate'])) {
                    $res = $options['wrapperTemplate'];
                } else {
                    $tplProp = 'tpl'.ucfirst($type);
                    $res = $this->$tplProp;
                }
            }
        return $res;
    }
    
    function _renderWrapper($name, $options, $content) {
        $showCaption = $this->showCaptions;
        $caption = isset($options['caption']) ? $options['caption'] : $name;
        $showRequired = $this->showRequiredFields;
        $required =  isset($options['required']) && $options['required'];
//        var_dump($name, $options);
        $showErrors = $this->showErrors; 
        $error = isset($options['error'])? $options['error'] : false;
        if ($templateName = $this->_getTemplate($controlType = 'wrapper', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowWrapper($name, $options, $content, $showCaption, $caption, $showRequired, $required, $showErrors, $error);
    }
    
    function _processAttribs(& $attribs, $type, $options) {
        $defPropName = 'attribs'.ucfirst($type);
        if (!is_array($attribs)) $attribs = array();
        if (isset($options['attribs']) && is_array($options['attribs'])) $attribs = array_merge($options['attribs'], $attribs);
        if (isset($this->$defPropName) && is_array($this->$defPropName)) $attribs = array_merge($this->$defPropName, $attribs);
        if (is_array($this->attribs)) $attribs = array_merge($this->attribs, $attribs);
    }
    
    // ---------- Public buffered rendering methods ---------
    
    function getTextInput($name, $value = null, $attribs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
    
    function getPassword($name, $value = null, $attribs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
    
    
    function getTextArea($name, $value = null, $attribs = array(), $cols = false, $rows = false, $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
    
    function getRte($name, $value = null, $width = false, $height = false, $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
            
    function getDateInput($name, $value = null, $attribs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
            
    function getSelectList($name, $value = null, $valueList = null, $multiple = false, $dummyCaption = false, $useInputs = false, $attribs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
            
    function getCheckbox($name, $value = null, $checkedValue = false, $attibs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
            
    function getRadio($name, $value = null, $checkedValue = false, $attribs = array(), $noWrap = false) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
    
    function getWrapper($name, $options, $content) {
        ob_start(); $args = func_get_args(); call_user_func_array(array(& $this, str_replace('get', 'show', __FUNCTION__)), $args); return ob_get_clean(); 
    }
    
    
    // ---------- Public direct rendering methods ---------
    
    function showAuto($name) {
        $options = $this->getOptions($name, true);
        $args = func_get_args();
        if (isset($options['controlType'])) {
            $type = $options['controlType'];
        } else $type = 'textInput';
        call_user_func_array(array(& $this, 'show'.ucfirst($type)), $args); 
    }

    
    function showTextInput($name, $value = null, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'textInput', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $attribs, true); 
            return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($templateName = $this->_getTemplate($controlType = 'textInput', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowTextInput($name, $value, $attribs, $options);
    }
    
    function showHidden($name, $value = null, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'hidden', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $attribs, true); 
            return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($templateName = $this->_getTemplate($controlType = 'hidden', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowHidden($name, $value, $attribs, $options);
    }
    
    function showPassword($name, $value = null, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'password', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $attribs, true); 
            return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($templateName = $this->_getTemplate($controlType = 'textInput', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowPassword($name, $value, $attribs, $options);
    }
    
    function showTextArea($name, $value = null, $attribs = array(), $cols = false, $rows = false, $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'textArea', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $attribs, $cols, $rows, true); return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($cols === false) $cols = $this->textAreaCols;
        if ($rows === false) $rows = $this->textAreaRows;
        if ($templateName = $this->_getTemplate($controlType = 'textArea', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowTextArea($name, $value, $attribs, $cols, $rows, $options);
    }
    
    function showRte($name, $value = null, $width = false, $height = false, $noWrap = false) {
        $options = $this->getOptions($name, true);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $width, $height, true); return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($width === false) $width = $this->rteWidth;
        if ($height === false) $height = $this->rteHeight;
        if ($templateName = $this->_getTemplate($controlType = 'rte', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowRte($name, $value, $width, $height, $options);
        if ($this->_buffer) return ob_get_clean();
    }
    
    function showDateInput($name, $value = null, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'dateInput', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $attribs, true); return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($templateName = $this->_getTemplate($controlType = 'dateInput', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowDateInput($name, $value, $attribs, $options);
    }
    
    function showSelectList($name, $value = null, $valueList = null, $multiple = false, $dummyCaption = false, $useInputs = false, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'selectList', $options);
        if ($dummyCaption === false && isset($options['dummyCaption'])) $dummyCaption = $options['dummyCaption'];
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $valueList, $multiple, $dummyCaption, $useInputs, $attribs, true);
            return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if (is_null($valueList) && isset($options['valueList'])) $valueList = $options['valueList'];
        if (!is_array($valueList) || !count($valueList)) {
            // Check if we can use Ae_Data_List here...
            if (isset($options['values']) && is_array($options['values'])) {
                Ae_Dispatcher::loadClass('Ae_Model_Values');
                $vls = & Ae_Model_Values::factoryWithFormOptions($this->_src, $name, $options);
                $valueList = array('0' => & $vls); 
            }
        }
        if (!is_array($valueList) || !count($valueList)) trigger_error ("valueList not specified or is empty", E_USER_WARNING);
            
        if ($templateName = $this->_getTemplate($controlType = 'selectList', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowSelectList($name, $value, $valueList, $multiple, $dummyCaption, $useInputs, $attribs, $options);
    }
    
    function showCheckbox($name, $value = null, $checkedValue = false, $attibs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'checkbox', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $checkedValue, $attribs, true); return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($checkedValue === false) $checkedValue = $this->defaultCheckedValue;
        if ($templateName = $this->_getTemplate($controlType = 'checkbox', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowCheckbox($name, $value, $checkedValue, $attribs, $options);
    }
    
    function showRadio($name, $value = null, $checkedValue = false, $attribs = array(), $noWrap = false) {
        $options = $this->getOptions($name, true);
        $this->_processAttribs($attribs, 'radio', $options);
        if (!$noWrap && ($this->alwaysWrap || $this->showCaptions || $this->showErrors && isset($options['error']) && $options['error'] || $this->showRequiredFields && isset($options['required']) && $options['required'])) { 
            $args = array($name, $value, $checkedValue, $attribs, true); return $this->_renderWrapper($name, $options, call_user_func_array(array(& $this, str_replace('show', 'get', __FUNCTION__)), $args)); 
        }
        if ($checkedValue === false) $checkedValue = $this->defaultCheckedValue;
        if (is_null($value) && isset($options['value'])) $value = $options['value'];
        if ($templateName = $this->_getTemplate($controlType = 'radio', $options)) 
            $this->_renderTemplate($templateName, get_defined_vars());
        else $this->doShowCheckbox($name, $value, $checkedValue, $attribs, $options);
    }
    
    // ---------- Default templates ---------
    
    function doShowTextInput($name, $value, $attribs, $options) {
        if (!is_null($value)) $attribs['value'] = $value;
        if (isset($options['maxLength'])) $attribs['maxLength'] = $options['maxLength'];
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'text';
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
    }
    
    function doShowHidden($name, $value, $attribs, $options) {
        if (!is_null($value)) $attribs['value'] = $value;
        if (isset($options['maxLength'])) $attribs['maxLength'] = $options['maxLength'];
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'hidden';
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
    }
    
    function doShowPassword($name, $value, $attribs, $options) {
        if (!is_null($value)) $attribs['value'] = $value;
        if (isset($options['maxLength'])) $attribs['maxLength'] = $options['maxLength'];
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'password';
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
    }
    
    function doShowTextArea($name, $value, $attribs, $cols, $rows, $options) {
        if (!is_null($value) || !isset($options['allowHtml']) || !$options['allowHtml']) $value = htmlspecialchars($value);
            else $value = '';

        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        if ($cols) $attribs['cols'] = $cols;
        if ($rows) $attribs['rows'] = $rows;
        
        echo '<textarea '.Ae_Util::mkAttribs($attribs).' >'.$value.'</textarea>';
    }
        
    function doShowRte($name, $value, $width, $height, $options) {
        if (is_null($value)) $value = '';
        
        $cols = $this->textAreaCols;
        $rows = $this->textAreaRows;

//        var_dump("$width $height $cols $rows ");
        
        editorArea( $name,  $value, $name, $width, $height, $cols, $rows ) ; 
    }
    
    function doShowDateInput($name, $value, $attribs, $options) {
        if (!is_null($value)) $attribs['value'] = $this->formatDateTime($value, $options);
        if (isset($options['maxLength'])) $attribs['maxLength'] = $options['maxLength'];
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'text';
        if (!isset($attribs['id'])) $attribs['id'] = $name;
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
        if (isset($options['imageAttribs'])) {
            $imageAttribs = $options['imageAttribs'];
            $imageAttribs['onclick'] = 'return showCalendar(\''.addslashes($attribs['id']).'\', \'y-mm-dd\');';
            echo '<input type="image" '.Ae_Util::mkAttribs($imageAttribs).' />';
        } else {
            echo '<input type="button" class="date_button" onclick="return showCalendar(\''.addslashes($attribs['id']).'\', \'y-mm-dd\');" value="..." />';
        }
    }
    
    function doShowSelectList($name, $value, $valueList, $multiple, $dummyCaption, $useInputs, $attribs, $options) {
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $mv = false; 
        if (isset($valueList[0]) && is_a($valueList[0], 'Ae_Model_Values')) {
            $mv = & $valueList[0];
            $valueList = $mv->getValueList();
        }
        if ($useInputs) {
            $attribs['type'] = $multiple? 'checkbox' : 'radio';
            if ($dummyCaption !== false) {
                $itemAttribs = $attribs;
                $itemAttribs['value'] = '';
                if (!strlen($value)) $itemAttribs['checked'] = true;
                echo '<div><input '.Ae_Util::mkAttribs($itemAttribs).'/>&nbsp;'.$dummyCaption.'</div>';
            }
            foreach ($valueList as $v => $t) {
                $itemAttribs = $attribs;
                $itemAttribs['value'] = $v;
                $itemAttribs['checked'] = (((string)$v == (string)$value));
                echo '<div><input '.Ae_Util::mkAttribs($itemAttribs).'/>&nbsp;'.$t.'</div>';
            }
        } else {
            $attribs['multiple'] = $multiple;
            echo '<select '.Ae_Util::mkAttribs($attribs).'>';
            if ($dummyCaption !== false) {
                $itemAttribs = $attribs;
                $itemAttribs['value'] = '';
                if (!strlen($value)) $itemAttribs['checked'] = true;
                echo '<option '.Ae_Util::mkAttribs($itemAttribs).'>'.$dummyCaption.'</option>';                
            }
            foreach ($valueList as $v => $t) {
                $itemAttribs['value'] = $v;
                $itemAttribs['selected'] = (((string)$v == (string)$value));
                echo '<option '.Ae_Util::mkAttribs($itemAttribs).'>'.$t.'</option>';                
            }
            echo '</select>';
        }
    }
    
    function doShowCheckbox($name, $value, $checkedValue, $attribs, $options) {
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'checkbox';
        $attribs['value'] = $checkedValue;
        if ($value) $attribs['checked'] = true;
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
    }
    
    function doShowRadio($name, $value, $checkedValue, $attribs, $options) {
        $attribs['name'] = Ae_Util::concatPaths($this->fieldNamePrefix, $name);
        $attribs['type'] = 'radio';
        $attribs['value'] = $checkedValue;
        if ($value) $attribs['checked'] = true;
        echo '<input '.Ae_Util::mkAttribs($attribs).' />';
    }
    
    function doShowWrapper($name, $options, $content, $showCaption, $caption, $showRequired, $required, $showErrors, $error) {
        if ($showCaption && $caption) echo "<span class='caption'>$caption</span>&nbsp;";
        if ($showRequired && $required) echo "<span class='required'>*</span>&nbsp;";
        echo $content;
        if (is_array($error)) $error = Ae_Util::implode_r("\n", $error);
        if ($showErrors && $error) echo "<div class='error'>".nl2br(htmlspecialchars($error))."</div>";        
    }
    
    function formatDateTime($value, $options) {
        $res = $value;
        if (strlen($res)) {
            if (isset($options['outputDateFormat'])) $res = Ae_Util::date($res, $options['outputDateFormat']);
        }
        return $res;
    }
    
}

?>
