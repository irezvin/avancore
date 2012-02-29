<?php

Ae_Dispatcher::loadClass('Ae_Legacy_Controller');

class Ae_Form_Control extends Ae_Legacy_Controller {

    protected $init = false;
    
    /**
     * Name of the control
     *
     * @var string
     */
    var $name = false;
    
    /**
     * Control's id attribute
     *
     * @var string
     */
    var $id = false;
    
    /**
     * Control's id should be automatically assigned if it's false
     *
     * @var bool
     */
    var $autoId = false;
    
    /**
     * Caption of the control (may be rendered by the template; defaults to the control name in most cases)
     * @var string|bool
     */
    var $caption = false;
    
    var $emptyCaption = false;
    
    /**
     * Whether control shows caption by itself and it's caption shouldn't be displayed by outer wrapper
     * @var bool
     */
    var $showsOwnCaption = false;
    
    /**
     * Detailed description of the control (may be rendered by the template)
     * @var string|bool
     */
    var $description = false;
    
    /**
     * Extra options that will be provided to javascript controller via JSON 
     * @var array
     */
    var $jsExtras = array();
    
    /**
     * Extra values that are intended to be picked up by the template
     * @var array
     */
    var $tplExtras = array();
    
    var $htmlAttribs = array();
    
    /**
     * Error info that may be rendered by the template
     * @var array|string|bool
     */
    var $errors = false;
    
    var $getErrorsFromModel = true;
    
    var $getErrorsFromParent = true;
    
    var $getModelFromParent = true;
    
    var $required = '?';
    
    var $readOnly = '?';
    
    var $visible = true;
    
    var $enabled = true;
    
    var $hasValue = true;
    
    var $useGetterIfPossible = true;
    
    /**
     * Whether the control should try to retrieve default value from the model if no own default value is supplied
     * @var bool
     */
    var $getDefaultFromModel = true;

    /**
     * Whether the control should try to retrieve default value from it's parent if no default value and no model are supplied
     * @var bool
     */
    var $getDefaultFromParent = true;
    
    /**
     * If we have to retrieve some information form the model and the model isn't bound yet, control will retrieve only static property info from it.
     * This applies only to the cases when control has it's own model (since if control asks it's parent about the metadata, parent will consult it's own $forceDynamicPropInfo).   
     *
     * @var bool
     */
    var $forceDynamicPropInfo = false;
    
    /**
     * Don't show errors if model isChecked() is FALSE. This only applies to the controls with their own models. 
     * @var bool
     */
    var $forceModelCheck = false;
    
    /**
     * Wheteher this control should try to obtain parent control's model if no own model is provided
     * @var bool
     */
    var $useParentsModel = true;
    
    /**
     * Whether the control should try to obtain model property name automatically based on it's control path (if $modelPropertyName is false) 
     * @var bool
     */
    var $autoModelProperty = true;
    
    var $modelPropertyName = false;
    
    var $resultPath = false;
    
    var $modelGetterName = false;
    
    var $displayOrder = -1;
    
    /**
     * Template class to render the control
     * @access public
     * @var string
     */
    var $templateClass = false;
    
    /**
     * Name of template part that will be used to show the control
     * @access public
     * @var string
     */
    var $templatePart = false;
    
    /**
     * Template class to render the control's wrapper 
     * @access public
     * @var string|bool
     */
    var $wrapperTemplateClass = false;
    
    /**
     * Name of template part (of given template class) of the control's wrapper.
     * Wrapper function should accept two parameters: reference to the control and it's rendered HTML representation 
     * @var string|bool
     */
    var $wrapperTemplatePart = false;
    
    var $childWrapperTemplateClass = false; 
    
    var $childWrapperTemplatePart = false;
    
    var $provideChildrenWithTemplateInstance = true;
    
    /**
     * Whether the control itself shows wrapper
     * @var bool
     */
    var $showWrapper = true;
    
    /**
     * Whether the control should get $wrapperTemplateClass and $wrapperTemplatePart from the display parent
     * @var bool
     */
    var $getWrapperFromDisplayParent = true;
    
    /**
     * Whether this control always creates it's own templates instead of trying to use displayParent's ones
     * @var bool
     */
    var $hasOwnTemplates = false;

    /**
     * @var Ae_Legacy_Controller_Response_Html
     */
    var $htmlResponse = false;
    
    var $valueFirstInContext = true;

    /**
     * @var bool|string 
     * - FALSE means don't decode; 
     * - TRUE means decode with default charset; 
     * - other string means decode with given charset.
     */
    var $decodeHtmlEntitiesOnInput = false;
    
    var $_value = false;
    
    var $_gotValue = false;
    
    var $_default = false;
    
    var $_hasDefault = false;
    
    var $_presentation = false;
    
    var $_presentationWithWrapper = false;
    
    var $dontGetDefaultFromModel = false;
    
    /**
     * Model that provides current control with the values and error info (or FALSE if no model is provided)
     * @access protected
     * @var Ae_Model_Data
     */
    var $_model = false;
    
    var $_hasOwnModel = false;
    
    /**
     * Meta-info about the property that is edited by this control. Default value, caption, description, errors and other info will be taken from it, if it is provided.  
     * @access protected
     * @var Ae_Model_Property
     */
    var $_property = false;
    
    /**
     * Composite control that owns this one (if any) 
     * @var Ae_Form_Control_Composite
     */
    var $_parent = false;

    /**
     * Control that *shows* this one
     * @var Ae_Form_Control
     */
    var $_displayParent = false;
    
    var $_displayChildren = array();
    
    var $_orderedDisplayChildren = false;
    
    /**
     * Templates that were instantiated for this control
     * @access protected
     * @var array template class => template
     */
    var $_templates = array();
    
    var $_submitted = '?';
    
    var $_creationOrder = null;
    
    var $index = false;
    
    var $decorator = false;
    
    function doInitProperties($options = array()) {
        if (isset($options['default'])) $this->setDefault($options['default']);
        if (isset($options['parent']) && is_a($options['parent'], 'Ae_Form_Control')) $this->_parent = & $options['parent'];
        //if (isset($options['displayParent'])) $this->setDisplayParent($options['displayParent']);
        //    elseif ($this->_parent) $this->setDisplayParent($this->_parent);
        if (isset($options['model'])) {
            $this->setModel($options['model']);
        }
        if (isset($options['modelProperty'])) $this->setModelProperty($options['modelProperty']);
        if (is_a($this->_context, 'Ae_Form_Context')) {
            $this->_context->valueFirst = $this->valueFirstInContext;
            //if (!$this->valueFirstInContext) var_dump("!!!");
            //var_dump($this->_context->getData());
        }
        $this->init = true;
        $tmp = $options;
        foreach (array('default', 'parent', 'model', 'modelProperty') as $opt) unset($tmp[$opt]);
        if (isset($options['name'])) $this->name = $options['name'];
        if (isset($options['creationOrder'])) {
            $this->_creationOrder = $options['creationOrder'];
        }
        Ae_Util::bindAutoparams($this, $options);
    }
    
/**
     * Returns context of the controller 
     * @return Ae_Legacy_Controller_Context_Http
     */
    function getContext() {
        return $this->_context;
    }
    
    // ------------------------------ dataflow methods -----------------------------
    
    function isSubmitted() {
        $this->bindFromRequest();
        if ($this->_submitted === '?') {
            if ($this->_parent) $this->_submitted = $this->_parent->isSubmitted();
                else $this->_submitted = $this->_doIsSubmitted();
        }
        return $this->_submitted === true;
    }
    
    function setSubmitted($submitted = true) {
        $this->_submitted = $submitted === true;
    }
    
    function getValue() {
        if (!$this->_gotValue) {
            $this->bindFromRequest();   
            $this->_gotValue = true;
            if (!$this->isReadOnly()) {
                $this->_value = $this->_doGetValue();
            } else {
                $this->_value = $this->getDefault();
            }
        }
        return $this->_value;
    }
    
    function setData($value = array()) {
        $this->_gotValue = true;
        $this->_value = $value;
    }
    
    function setDefault($default = array()) {
        $this->_hasDefault = !is_null($default);
        $this->_doSetDefault($default);
    }
    
    function getDefault() {
        if ($this->_hasDefault) $res = $this->_default; else {
            if ($this->getDefaultFromParent && $this->_parent) {
                $res = $this->_parent->getDefaultValueForTheChild($this->name);
                if (is_null($res)) $res = $this->_doGetDefault(); 
            } else {
                $res = $this->_doGetDefault();
            }
            if ($this->decorator)
                $res = Ae_Decorator::decorate ($this->decorator, $res, $this->decorator);            
        }
        return $res;
    }
    
    /**
     * @return bool If the control is read-only (according to the own $readOnly property and the model metadata)
     */
    function isReadOnly() {
        if ($this->readOnly === '?') {
            $p = & $this->getModelProperty();
            if ($p) $res = $p->readOnly;
                else $res = false;
        } else {
            $res = $this->readOnly;
        }
        return $res;
    }
    
    function isVisible() {
        return $this->visible && $this->enabled;
    }
    
    function isEnabled() {
        return $this->enabled;
    }
    
    // ----------------------------- composition support methods ----------------------
    
    /**
     * @param Ae_Form_Control|string $parent Display parent control or the path to it
     */
    function setDisplayParent($parent) {
        if ($parent !== false) {
            $p = & $this->searchControlByPathRef($parent);
            if (!$p) {
                trigger_error ("Control '".($this->_getPath())."{$this->id}': display parent's path '{$parent}' points to non-existent control", E_USER_ERROR);
            }
            $p->addDisplayChild($this);
        } else {
            $this->_displayParent = false;
        }
    }
    
    /**
     * Adds new display child
     * @param Ae_Form_Control $control
     */
    function addDisplayChild(& $control) {
        if (($index = $this->searchDisplayChild($control)) === false) {
            $this->_orderedDisplayChildren = false;
            $index = count($this->_displayChildren);
            $this->_displayChildren[$index] = & $control;
            $control->_displayParent = & $this;
        }
        return $index;
    }
    
    /**
     * Returns index of the control in the displayChildren collection
     *
     * @param Ae_Form_Control $control
     * @return int|false
     */
    function searchDisplayChild(& $control) {
        $res = false;
        foreach (array_keys($this->_displayChildren) as $c) {
            if (Ae_Util::sameObject($this->_displayChildren[$c], $control)) {
                $res = $c;
            }
        }
        return $res;
    }
    
    function removeDisplayChild(& $control) {
        $idx = $this->searchDisplayChild($control);
        if ($idx !== false) {
            unset($this->_displayChildren[$control]);
            $this->_orderedDisplayChildren = false;
            $res = true;
        }
        else $res = false; 
    }
    
    function getDebugCaption() {
        return $this->name.' - '.$this->displayOrder.' / '.$this->_creationOrder;
    }
    
    function dumpControlNames($arr) {
        var_dump(Ae_Autoparams::getObjectProperty($arr, 'debugCaption'));
    }
    
    function getOrderedDisplayChildren() {
        if ($this->_orderedDisplayChildren === false) {
            $this->_doInitDisplayChildren();
            $this->_orderedDisplayChildren = array();
            foreach (array_keys($this->_displayChildren) as $i) {
                $child = & $this->_displayChildren[$i];
                if ($child->isVisible()) $this->_orderedDisplayChildren[$i] = & $child;
            }
            uasort($this->_orderedDisplayChildren, array(& $this, '_displayOrderCallback'));
        }
        return $this->_orderedDisplayChildren;
    }
    
    /**
     * Checks whether given control is display child of current one
     * @param Ae_Form_Control $control
     */
    function isDisplayChild(& $control) {
        $res = false;
        foreach (array_keys($this->_displayChildren) as $k) {
            if (Ae_Util::sameObject($control, $this->_displayChildren[$k])) {
                $res = true;
                break;
            }
        }
        return $res;
    }
    
    /**
     * Returns display child that has given relative path to current control.
     * If found control isn't display child of this one, error will occur or FALSE will be returned (depending on dontTriggerError value).
     *
     * @param string $path
     * @param bool $dontTriggerError Don't trigger error if control is not found or if it isn't display children of current one
     * @return Ae_Form_Control 
     */
    function getDisplayChildByPath($path, $dontTriggerError = false) {
        $control = & $this->searchControlByPath($path);
        $res = false;
        if ($control && $this->isDisplayChild($control)) $res = & $control;
        if (!$res && !$dontTriggerError) trigger_error ("No such display child: '{$path}'", E_USER_ERROR);
        return $res;
    }
    
    /**
     * Searches other control by given path.
     * 
     * Path format that is similar to one of UNIX directory paths, 
     * i.e. ../foo/bar means control 'bar' of control 'foo' of my parent; 
     * adding leading slash allow to specify absolute "paths".
     * 
     * @param string $path 
     * @return Ae_Form_Control
     */
    function searchControlByPath ($path) {
        if (is_a($path, 'Ae_Form_Control')) 
            trigger_error ("\$path must be a string or an array; use Ae_Form_Control::searchControlByPathRef() to supply Ae_Form_Control references instead", E_USER_ERROR);
        $curr = $this;
        if (!is_array($path)) $path = explode('/', $path);
        if (!strlen($path[0])) {
            $curr = $curr->_getRootControl();
            $path = array_slice($path, 1);
        }
        while ($curr && count($path)) {
            $segment = $path[0];
            //var_dump(get_class($curr), get_class($curr->_parent), $segment);
            $path = array_slice($path, 1);
            if ($segment == '..') $curr = $curr->_parent;
            elseif (is_a($curr, 'Ae_Form_Control_Composite') && in_array($segment, $curr->listControls())) {
                    $curr = $curr->getControl($segment);
            } else {
                unset($curr);
                $curr = false;
            }
        }
        if (count($path)) $curr = false;
        return $curr; 
    }
    
    /**
     * Same as Ae_Form_Control::searchControlByPath, but returns $path if $path is a control
     * @see Ae_Form_Control::searchControlByPath
     * @param string|array|Ae_Form_Control $path
     * @return Ae_Form_Control or false
     */
    function searchControlByPathRef ($path) {
        if (is_a($path, 'Ae_Form_Control')) $res = $path;
            else $res = $this->searchControlByPath($path);
        return $res;
    }
    
    // ----------------------------- presentation-related methods --------------------
    
    function doPopulateResponse() {
        
    }
    
    /**
     * @param bool $refresh Whether presentation should be re-rendered even if it already has been calculated before. 
     * @return string Control's HTML presentation
     */
    function fetchPresentation($refresh = false, $withWrapper = null) {
        if ($this->isVisible()) {
            if (is_null($withWrapper)) $withWrapper = $this->showWrapper;
            if ($withWrapper) {
                $res = $this->fetchWithWrapper($refresh);
            } else {
                if ($this->_presentation === false || $refresh) {
                    if (!strlen($this->templatePart)) trigger_error ("Cannot retrieve template to render the control '".$this->_getPath()."' - templatePart property is not set", E_USER_ERROR);
                    $template = & $this->getTemplate();
                    $template->setVars($this->tplExtras);
                    $this->_presentation = $template->fetch($this->templatePart, $this->_doGetTemplatePartParams());
                    $this->postProcessPresentation($this->_presentation);
                }
                $res = $this->_presentation;
            }
            return $res;
        } else {
            return false;
        }
    }
    
    function postProcessChildPresentation(& $html, Ae_Form_Control $child) {
    }
    
    protected function postProcessPresentation(& $html) {
        if ($this->_parent) $this->_parent->postProcessChildPresentation($html, $this);
    }
    
    function fetchWithWrapper($refresh = false) {
        if ($this->isVisible()) {
            if ($this->_presentationWithWrapper === false || $refresh) {
                $pres = $this->fetchPresentation($refresh, false);
                $this->_presentationWithWrapper = $this->_renderWrapper($pres);
            }
            return $this->_presentationWithWrapper;
        } else {
            return false;
        }
    }
    
    function _renderWrapper($wrappedHtml) {
        $wcp = array($this->wrapperTemplateClass, $this->wrapperTemplatePart);
        if (!strlen($this->wrapperTemplateClass) || !strlen($this->wrapperTemplatePart)) {
            if (is_a($this->_displayParent, 'Ae_Form_Control') && $this->getWrapperFromDisplayParent) {
                $wp = $this->_displayParent->getWrapperForTheChild($this);
                if (!strlen($wcp[0])) $wcp[0] = & $wp[0];
                if (!strlen($wcp[1])) $wcp[1] = $wp[1]; 
            }
        }
        if ((is_object($wcp[0]) || strlen($wcp[0])) && strlen($wcp[1])) {
            if (is_a($wcp[0], 'Ae_Template')) $tpl = & $wcp[0];
                else $tpl = & $this->getSpecificTemplate($wcp[0]);
            $res = $tpl->fetch($wcp[1], $this->_doGetWrapperTemplatePartParams($wrappedHtml));
        } else {
            $res = $wrappedHtml;
        }
        return $res;
    }
    
    function getWrapperForTheChild(& $displayChild) {
        if (strlen($this->childWrapperTemplateClass) && $this->provideChildrenWithTemplateInstance) {
            $tpl = & $this->getSpecificTemplate($this->childWrapperTemplateClass);
        } else {
            $tpl = $this->childWrapperTemplateClass;
        }
        return array($tpl, $this->childWrapperTemplatePart);
    }
    
    /**
     * @return Ae_Form_Control_Template Template to render this control
     */
    function getTemplate($templateClass = false) {
        if ($templateClass === false) $templateClass = $this->templateClass;
        if (!strlen($templateClass)) trigger_error ("Cannot retrieve template to render the control '".$this->_getPath()."' - templateClass property is not set", E_USER_ERROR);
        $res = & $this->getSpecificTemplate($templateClass);
        return $res;
    }
    
    /**
     * Returns template of specified class. Instantiates it if needed. 
     * If $this->hasOwnTemplates is true, always instantiates template if it isn't already in $this->_templates; otherwise asks display parent for it first.
     *
     * @param string $templateClass
     */
    function getSpecificTemplate($templateClass) {
        if (!isset($this->_template[$templateClass])) {
            $res = false;
            if (!$this->hasOwnTemplates && $this->_displayParent) $res = & $this->_displayParent->getSpecificTemplate($templateClass);
            if (!is_object($res)) {
                Ae_Dispatcher::loadClass($templateClass);
                $this->_templates[$templateClass] = new $templateClass (array('control' => & $this));
                $this->_doInitializeTemplate($this->_templates[$templateClass]);
                $res = & $this->_templates[$templateClass];
            }
        } else {
            $res = & $this->_template[$templateClass];
        }
        return $res;
    }
    
    // ------------------------- methods to call from the template --------------------
    
    function getId() {
        if ($this->id === false) {
            $this->id = '';
            $htmlAttribs = $this->getHtmlAttribs();
            if (isset($htmlAttribs['id']) && strlen($this->htmlAttribs['id'])) $this->id = $htmlAttribs['id'];
            if (!strlen($this->id) && $this->autoId) $this->id = $this->_context->mapIdentifier('');
        }
        return $this->id;
    }
    
    function getCaption() {
        if ($this->caption === false) {
            $res = ucfirst($this->name);
            if ($p = & $this->getModelProperty()) {
                if (strlen($p->caption)) $res = $p->caption;
            }
        } else {
            $res = $this->caption;
        }
        //$res .= " ".$this->_creationOrder;
        return $res;
    }
    
    function getEmptyCaption() {
        $res = false;
        if ($this->emptyCaption === false) {
            if ($p = & $this->getModelProperty()) {
                if (isset($p->emptyCaption) && $p->emptyCaption !== false) $res = $p->emptyCaption;
            }
        } else {
            $res = $this->emptyCaption;
        }
        return $res;
    }
    
    function getDescription() {
        if ($this->description === false) {
            $res = false;
            if (($p = & $this->getModelProperty()) && isset($p->description) && strlen($p->description)) $res = str_replace("\n", "<br />", htmlspecialchars($p->description));
        } else $res = $this->description;
        return $res;
    }
    
    /**
     * @return bool Whether this control is required to be filled-in
     * Note: in most cases, form controls should not validate data correctness themselves, especially when the submission data is
     * given out to the model. So this function can be effectively renamed to hasToShowNiceAsteriskNearTheCaption() 
     */
    function isRequired() {
        if ($this->required === '?') {
            $p = & $this->getModelProperty();
            if ($p) $res = $p->required;
                else $res = false;
        } else {
            $res = $this->required;
        }
        return $res;
    }
    
    function getErrors() {
        if ($this->errors === false) {
            if (($this->getErrorsFromModel) && ($m = & $this->getModel()) && ($p = $this->getPropertyName())) {
                if ($m->isChecked() || $this->forceModelCheck) {
                    $this->errors = $m->getErrors($p, false);
                }
            }
            if ($this->errors === false && $this->getErrorsFromParent && $this->_parent) 
                $this->errors = $this->_parent->getErrorsForTheChild($this, true);
        }
        return $this->errors;
    }
    
    function getHtmlAttribs() {
        $p = & $this->getModelProperty();
        if ($p && isset($p->attribs) && is_array($p->attribs)) $res = $p->attribs;
            else $res = array();
        if (!isset($res['id']) && strlen($id = $this->getId())) $res['id'] = $id;
        Ae_Util::ms($res, $this->htmlAttribs);
        return $res;
    }
    
    // ----------------------------- model support methods ----------------------------
    
    /**
     * @return Ae_Model_Data Model (if any) that is used as the data source by this control 
     */
    function getModel() {
        if ($this->_model === false) {
            $this->_model = null;
            if ($this->getModelFromParent && $this->_parent) $this->_model = & $this->_parent->getModelForTheChild($this);
        }
        return $this->_model;
    }

    /**
     * @param Ae_Model_Data $model Null or false can also be provided.
     */
    function setModel($model) {
        if (!is_null($model) && ($model !== false) && !is_a($model, 'Ae_Model_Data')) 
            trigger_error ("\$model should be null, false or the instance of Ae_Model_Data", E_USER_ERROR);
        $this->_model = & $model;
        if ($this->_model) $this->_hasOwnModel = true;
            else $this->_hasOwnModel = false;
    }
    
    function getPropertyName() {
        if ($this->modelPropertyName === false) {
            $this->modelPropertyName = '';
            if ($this->autoModelProperty) {
                if ($this->_parent) $prefix = $this->_parent->getPropertyPrefixForTheChild($this);
                    else $prefix = '';
                $this->modelPropertyName = Ae_Util::concatPaths($prefix, $this->name);
                if (strlen($this->modelPropertyName)) {
                    $m = $this->getModel();
                    if (!$m || !$m->hasProperty($this->modelPropertyName)) $this->modelPropertyName = '';
                } 
            }
        }
        return $this->modelPropertyName;
    }
    
    function getResultPath() {
        if (strlen($this->resultPath)) $res = $this->resultPath;
        elseif (strlen($pn = $this->getPropertyName())) $res = $pn;
        else $res = $this->name;
        return $res;
    }
        
    function getGetterName() {
        if ($this->modelGetterName === false) {
            $this->modelGetterName = '';
            if ($this->autoModelProperty) {
                $this->modelGetterName = 'get'.ucFirst($this->name);
                if (strlen($this->modelGetterName)) {
                    $m = $this->getModel();
                    if (!$m || !method_exists($m, $this->modelGetterName)) $this->modelGetterName = '';
                } 
            }
        }
        return $this->modelGetterName;
    }
    
    
    /**
     * @return Ae_Model_Property Metadata for this control
     */
    function getModelProperty() {
        if ($this->_property === false) {
            $this->_property = null;
            if (strlen($p = $this->getPropertyName()) && ($m = & $this->getModel())) {
                if ($m->hasProperty($p))
                    $this->_property = & $m->getPropertyInfo($p, !($this->forceDynamicPropInfo || $m->isBound()));
                else
                    $this->_property = null;
            }
        }
        return $this->_property;
    }
    
    /**
     * Sets model property that will be used to retrieve various metadata. If $property is valid Ae_Model_Property object,
     * model and modelPropertyName properties of the control will be set to $property->srcObject and $property->propName 
     * respectively.
     *      
     * @param Ae_Model_Property $property Null or false can also be provided.
     */
    function setModelProperty(& $property) {
        if (!is_null($property) && ($property !== false) && !is_a($property, 'Ae_Model_Property')) 
            trigger_error ("\$property should be null, false or the instance of Ae_Model_Property", E_USER_ERROR);
        $this->_property = & $property;
        if (is_a($property, 'Ae_Model_Property') && is_a($property->srcObject, 'Ae_Model_Data')) {
            $this->setModel($property->srcObject);
            $this->modelPropertyName = $property->propName;
        }
    }
    
    // --------------------------------- template methods ---------------------------- 

    function doBindFromRequest() {
        if ($this->decodeHtmlEntitiesOnInput !== false) {
            $c = $this->decodeHtmlEntitiesOnInput === true? null : $this->decodeHtmlEntitiesOnInput;
            $this->_rqData = Ae_Util::htmlEntityDecode($this->_rqData, ENT_QUOTES, $c);
            //echo('<p>'.'111'.Ae_Util::implode_r('<br />', $this->_rqData).'</p>');
        }
    }
    
    function _doInitDisplayChildren() {
    }
    
    /**
     * Should return default value
     */
    function _doGetDefault() {
        if (($m = & $this->getModel()) && !$this->dontGetDefaultFromModel) {
            if (strlen($p = $this->getPropertyName())) {
                $res = $m->getField($p);
            }
            elseif ($this->useGetterIfPossible && $g = $this->getGetterName()) $res = $m->$g();
            else $res = null;
        }
        else $res = null;
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetValue() {
        if (!($this->readOnly === true) && isset($this->_rqData['value'])) {
            $res = & $this->_rqData['value'];
        } else {
            $res = $this->getDefault();
        }
        return $res;
    }
    
    /**
     * @access protected
     * @param mixed $default
     */
    function _doSetDefault($default) {
        $this->_default = $default;
    }
    
    /**
     * Should return array of parameters that will be passed to the template's method when current control is rendered.
     * @access protected
     */
    function _doGetTemplatePartParams() {
        return array (& $this);
    }
    
    /**
     * Should return array of parameters that will be passed to the template's method when current control is rendered.
     * @access protected
     */
    function _doGetWrapperTemplatePartParams($representation) {
        return array (& $this, $representation);
    }
    
    /**
     * Template method to initialize template properties upon it's instantiation
     * @access protected
     * @param Ae_Form_Control_Template $template
     */
    function _doInitializeTemplate (& $template) {
        if ($this->htmlResponse) $template->htmlResponse = & $this->htmlResponse;
    }
    
    /**
     * @access protected
     */
    function _doIsSubmitted() {
        return false;
    }
    
    // ----------------------------- supplementary methods ------------------------- 

    /**
     * @param Ae_Form_Control $control1
     * @param Ae_Form_Control $control2
     */
    function _displayOrderCallback(& $control1, & $control2) {
        if ($control1->displayOrder > $control2->displayOrder) return 1;
        elseif ($control1->displayOrder < $control2->displayOrder) return -1;
        else {
            if ($control1->_creationOrder > $control2->_creationOrder) return 1;
            elseif ($control1->_creationOrder < $control2->_creationOrder) return -1;
            else return 0;
        }
    }
    
    /**
     * @access protected
     */
    function _getPath() {
        $res = $this->name;
        if ($this->_parent) $res = $this->_parent->_getPath().'/'.$res;
        return $res;
    }
    
    function getPath() {
        return $this->_getPath();
    }
    
    /**
     * @return Ae_Form_Control
     * @param string $class Find parent control of specified class
     */
    function & _getRootControl($class = false) {
        $curr = & $this;
        while ($curr->_parent && ($class === false || !($curr instanceof $class)))
            $curr = & $curr->_parent;
        if ($class !== false && !($curr instanceof $class)) $curr = null;
        return $curr;
    }
    
    function bindAutoparams() {
        /**
         * After I have added bindAutoparams() call to Ae_Legacy_Controller, doInitProperties() is called after
         * bindAutoparams(), that adds bug to the controls creation
         */
        if (!$this->init) return false;
    }

    function updateFromModel() {
        $this->_value = $this->getDefault();
        $this->_gotValue = true;
    }
    
    function updateModel() {
        if (($m = $this->getModel()) && strlen($p = $this->getPropertyName()) && $m->hasProperty($p)) {
            $m->setField($p, $this->getValue());
        }
    }
    
    function executeXhr() {
        $this->executeXhrCore();
    }
    
    function executeXhrCore() {
    }
    
    function getXhrId() {
        $res = $this->_getPath();
        return $res;
    }
    
    function getXhrUrl() {
        $ctx = $this->_context->cloneObject();
        $f = $this->_getRootControl();
        Ae_Util::setArrayByPath($ctx->_baseUrl->query, Ae_Util::pathToArray($f->getContext()->mapParam($f->_methodParamName)), 'xhr');
        Ae_Util::setArrayByPath($ctx->_baseUrl->query, Ae_Util::pathToArray($f->getContext()->mapParam('xhrTarget')), $this->getXhrId());
        $res = $ctx->getUrl(array(), false);
        return $res;
    }
    
    function invokeCallback($callback) {
        $res = null;
        if ($callback) {
            $args = func_get_args();
            $args = array_slice($args, 1);
            if (is_array($callback) && isset($callback[0])) {
                if (is_object($callback[0]) && ($callback[0] instanceof Ae_Form_Control_Path)) {
                    $callback[0] = $callback[0]->getControl($this, true);
                }
            }
            $res = call_user_func_array($callback, $args);
        }
        return $res;
    }
    
}

?>