<?php

class Ac_Form_Control extends Ac_Controller {
    
    static $strictParams = Ac_Prototyped::STRICT_PARAMS_WARNING;
    
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
    
    var $jsConstructor = false;
    
    var $jsObjectId = false;
    
    var $jsPropMap = array();
    
    var $assetLibs = array();
    
    var $presentationDecorator = false;
    
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
    
    var $getterParams = array();
    
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
    
    var $templatePartParam = false;
    
    /**
     * Template class to render the control's wrapper 
     * @access public
     * @var string|bool
     */
    var $wrapperTemplateClass = false;
    
    var $wrapperTemplateParam = false;
    
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
     * @var Ac_Controller_Response_Html
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
     * @var Ac_Model_Data
     */
    var $_model = false;
    
    var $_hasOwnModel = false;
    
    /**
     * Meta-info about the property that is edited by this control. Default value, caption, description, errors and other info will be taken from it, if it is provided.  
     * @access protected
     * @var Ac_Model_Property
     */
    var $_property = false;
    
    /**
     * Composite control that owns this one (if any) 
     * @var Ac_Form_Control_Composite
     */
    var $_parent = false;

    /**
     * Control that *shows* this one
     * @var Ac_Form_Control
     */
    var $_displayParent = false;
    
    var $_displayChildren = array();
    
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
    
    var $debug = false;
    
    /**
     * @return Ac_Controller_Context
     */
    protected function guessContext() {
        $url = Ac_Url::guess(true);
        $context = new Ac_Form_Context();
        if ($this->name) $context->setDataPath($this->name);
        $context->setBaseUrl($url);
        $context->populate(array('get', 'post'), $context->getDataPath());
        return $context;
    }

    function setParent(Ac_Form_Control_Composite $parent = null) {
        $this->_parent = $parent;
    }

    /**
     * @return Ac_Form_Control_Composite
     */
    function getParent() {
        return $this->parent;
    }    
    
    protected function doInitProperties(array & $options = array()) {
        $this->initOptionsFirst(['default', 'parent', 'model', 'modelProperty'], $options);
        if (isset($options['creationOrder'])) {
            $this->_creationOrder = $options['creationOrder'];
            unset($options['creationOrder']);
        }
        if ($this->_context && $this->_context instanceof Ac_Form_Context) {
            $this->_context->valueFirst = $this->valueFirstInContext;
        }
        $this->init = true;
    }
    
/**
     * Returns context of the controller 
     * @return Ac_Controller_Context_Http
     */
    function getContext($asIs = false) {
        if ($this->_context === false && !$asIs) {
            $this->_context = $this->guessContext();
        }
        return $this->_context;
    }
    
    // ------------------------------ dataflow methods -----------------------------
    
    function isSubmitted() {
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
            if (!$this->isReadOnly()) {
                $this->_value = $this->_doGetValue();
            } else {
                $this->_value = $this->getDefault();
            }
        }
        return $this->_value;
    }
    
    function setValue($value) {
        $this->_rqData['value'] = $value;
    }
    
    function deleteValue() {
        unset($this->_rqData['value']);
    }
    
    function setData($value) {
        $this->setValue($value);
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
        }
        return $res;
    }
    
    /**
     * @return bool If the control is read-only (according to the own $readOnly property and the model metadata)
     */
    function isReadOnly() {
        if ($this->readOnly === '?') {
            $p = $this->getModelProperty();
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
     * @param Ac_Form_Control|string $parent Display parent control or the path to it
     */
    function setDisplayParent($parent) {
        if ($parent !== false) {
            $p = $this->searchControlByPathRef($parent);
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
     * @param Ac_Form_Control $control
     */
    function addDisplayChild($control) {
        if (($index = $this->searchDisplayChild($control)) === false) {
            $index = count($this->_displayChildren);
            $this->_displayChildren[$index] = $control;
            $control->_displayParent = $this;
        }
        return $index;
    }
    
    /**
     * Returns index of the control in the displayChildren collection
     *
     * @param Ac_Form_Control $control
     * @return int|false
     */
    function searchDisplayChild($control) {
        $res = false;
        foreach (array_keys($this->_displayChildren) as $c) {
            if (Ac_Util::sameObject($this->_displayChildren[$c], $control)) {
                $res = $c;
            }
        }
        return $res;
    }
    
    function removeDisplayChild($control) {
        $idx = $this->searchDisplayChild($control);
        if ($idx !== false) {
            unset($this->_displayChildren[$control]);
            $res = true;
        }
        else $res = false; 
    }
    
    function getDebugCaption() {
        return $this->name.' - '.$this->displayOrder.' / '.$this->_creationOrder;
    }
    
    function dumpControlNames($arr) {
        var_dump(Ac_Accessor::getObjectProperty($arr, 'debugCaption'));
    }
    
    function getOrderedDisplayChildren() {
        $this->_doInitDisplayChildren();
        $res = array();
        foreach (array_keys($this->_displayChildren) as $i) {
            $child = $this->_displayChildren[$i];
            if ($child->isVisible()) $res[$i] = $child;
        }
        uasort($res, array(& $this, '_displayOrderCallback'));
        return $res;
    }
    
    /**
     * Checks whether given control is display child of current one
     * @param Ac_Form_Control $control
     */
    function isDisplayChild($control) {
        $res = false;
        foreach (array_keys($this->_displayChildren) as $k) {
            if (Ac_Util::sameObject($control, $this->_displayChildren[$k])) {
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
     * @return Ac_Form_Control 
     */
    function getDisplayChildByPath($path, $dontTriggerError = false) {
        $control = $this->searchControlByPath($path);
        $res = false;
        if ($control && $this->isDisplayChild($control)) $res = $control;
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
     * @return Ac_Form_Control
     */
    function searchControlByPath ($path) {
        if (is_a($path, 'Ac_Form_Control')) 
            trigger_error ("\$path must be a string or an array; use Ac_Form_Control::searchControlByPathRef() to supply Ac_Form_Control references instead", E_USER_ERROR);
        $curr = $this;
        if (!is_array($path)) $path = explode('/', $path);
        if (!strlen($path[0])) {
            $curr = $curr->_getRootControl();
            $path = array_slice($path, 1);
        }
        while ($curr && count($path)) {
            $segment = $path[0];
            $path = array_slice($path, 1);
            if ($segment == '..') $curr = $curr->_parent;
            elseif (is_a($curr, 'Ac_Form_Control_Composite') && in_array($segment, $curr->listControls())) {
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
     * Same as Ac_Form_Control::searchControlByPath, but returns $path if $path is a control
     * @see Ac_Form_Control::searchControlByPath
     * @param string|array|Ac_Form_Control $path
     * @return Ac_Form_Control or false
     */
    function searchControlByPathRef ($path) {
        if (is_a($path, 'Ac_Form_Control')) $res = $path;
            else $res = $this->searchControlByPath($path);
        return $res;
    }
    
    // ----------------------------- presentation-related methods --------------------
    
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
                    if ($model = $this->getModel()) Ac_Decorator::pushModel ($model);
                    if (!strlen($this->templatePart)) trigger_error ("Cannot retrieve template to render the control '".$this->_getPath()."' - templatePart property is not set", E_USER_ERROR);
                    $template = $this->getTemplate();
                    $template->setVars($this->tplExtras);
                    $this->_presentation = $template->fetch($this->templatePart, $this->_doGetTemplatePartParams());
                    if ($l = $this->getAssetLibs()) {
                        Ac_Controller_Response_Global::r()->addAssetLibs($l);
                    }
                    if ($js = $this->createJsObject()) { 
                        $script = new Ac_Js_Script($js->init());
                        $this->_presentation .= $script;
                    }
                    $this->postProcessPresentation($this->_presentation);
                    if ($model) Ac_Decorator::popModel();
                }
                $res = $this->_presentation;
            }
            return $res;
        } else {
            return false;
        }
    }
    
    function postProcessChildPresentation(& $html, Ac_Form_Control $child) {
    }
    
    protected function postProcessPresentation(& $html) {
        if ($this->presentationDecorator) $html = Ac_Decorator::decorate($this->presentationDecorator, $html, $this->presentationDecorator, $this->getModel());
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
    
    function renderWrapper($wrappedHtml) {
        return $this->_renderWrapper($wrappedHtml);
    }
    
    function _renderWrapper($wrappedHtml) {
        $wcp = array($this->wrapperTemplateClass, $this->wrapperTemplatePart);
        if (!strlen($this->wrapperTemplateClass) || !strlen($this->wrapperTemplatePart)) {
            if (is_a($this->_displayParent, 'Ac_Form_Control') && $this->getWrapperFromDisplayParent) {
                $wp = $this->_displayParent->getWrapperForTheChild($this);
                if (!strlen($wcp[0])) $wcp[0] = $wp[0];
                if (!strlen($wcp[1])) $wcp[1] = $wp[1]; 
            }
        }
        if (strlen($wcp[1]) && !$wcp[0]) $wcp[0] = $this->getTemplate();
        if ((is_object($wcp[0]) || strlen($wcp[0])) && strlen($wcp[1])) {
            if (is_a($wcp[0], 'Ac_Template')) $tpl = $wcp[0];
                else $tpl = $this->getSpecificTemplate($wcp[0]);
            $res = $tpl->fetch($wcp[1], $this->_doGetWrapperTemplatePartParams($wrappedHtml));
        } else {
            $res = $wrappedHtml;
        }
        return $res;
    }
    
    function getWrapperForTheChild($displayChild) {
        if (strlen($this->childWrapperTemplateClass) && $this->provideChildrenWithTemplateInstance) {
            $tpl = $this->getSpecificTemplate($this->childWrapperTemplateClass);
        } else {
            $tpl = $this->childWrapperTemplateClass;
        }
        return array($tpl, $this->childWrapperTemplatePart);
    }
    
    /**
     * @return Ac_Form_Control_Template Template to render this control
     */
    function getTemplate($templateClass = false) {
        if ($templateClass === false) $templateClass = $this->templateClass;
        if (!strlen($templateClass)) trigger_error ("Cannot retrieve template to render the control '".$this->_getPath()."' - templateClass property is not set", E_USER_ERROR);
        $res = $this->getSpecificTemplate($templateClass);
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
            if (!$this->hasOwnTemplates && $this->_displayParent) $res = $this->_displayParent->getSpecificTemplate($templateClass);
            if (!is_object($res)) {
                $this->_templates[$templateClass] = new $templateClass (array('control' => & $this));
                $this->_doInitializeTemplate($this->_templates[$templateClass]);
                $res = $this->_templates[$templateClass];
            }
        } else {
            $res = $this->_template[$templateClass];
        }
        return $res;
    }
    
    // ------------------------- methods to call from the template --------------------
    
    function getId($forceAuto = false) {
        if ($this->id === false) {
            $this->id = '';
            $htmlAttribs = $this->getHtmlAttribs();
            if (isset($htmlAttribs['id']) && strlen($this->htmlAttribs['id'])) $this->id = $htmlAttribs['id'];
            if ($forceAuto) $this->autoId = true;
            if (!strlen($this->id) && $this->autoId) {
                $this->id = $this->_context->mapIdentifier('');
            }
        }
        return $this->id;
    }
    
    function getCaption() {
        if ($this->caption === false) {
            $res = ucfirst($this->name);
            if ($p = $this->getModelProperty()) {
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
            if ($p = $this->getModelProperty()) {
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
            if (($p = $this->getModelProperty()) && isset($p->description) && strlen($p->description)) $res = str_replace("\n", "<br />", htmlspecialchars($p->description));
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
            $p = $this->getModelProperty();
            if ($p) $res = $p->required;
                else $res = false;
        } else {
            $res = $this->required;
        }
        return $res;
    }
    
    function getErrors() {
        if ($this->errors === false) {
            $res = false;
            if (($this->getErrorsFromModel) && ($m = $this->getModel()) && ($p = $this->getPropertyName())) {
                if ($m->isChecked() || $this->forceModelCheck) {
                    $res = $m->getErrors($p, false);
                }
            }
            if ($res === false && $this->getErrorsFromParent && $this->_parent) 
                $res = $this->_parent->getErrorsForTheChild($this, true);
            return $res;
        } else {
            return $this->errors;
        }
    }
    
    function getHtmlAttribs() {
        $p = $this->getModelProperty();
        if ($p && isset($p->attribs) && is_array($p->attribs)) $res = $p->attribs;
            else $res = array();
        if (!isset($res['id']) && strlen($id = $this->getId())) $res['id'] = $id;
        Ac_Util::ms($res, $this->htmlAttribs);
        return $res;
    }
    
    // ----------------------------- model support methods ----------------------------
    
    /**
     * @return Ac_Model_Data Model (if any) that is used as the data source by this control 
     */
    function getModel() {
        if ($this->_model === false) {
            $this->_model = null;
            if ($this->getModelFromParent && $this->_parent) $this->_model = $this->_parent->getModelForTheChild($this);
        }
        return $this->_model;
    }

    /**
     * @param Ac_Model_Data $model Null or false can also be provided.
     */
    function setModel($model) {
        if (!is_null($model) && ($model !== false) && !is_a($model, 'Ac_Model_Data')) 
            trigger_error ("\$model should be null, false or the instance of Ac_Model_Data", E_USER_ERROR);
        $this->_model = $model;
        if ($this->modelPropertyName === '' && $this->autoModelProperty) 
            $this->modelPropertyName = false;        
        if ($this->_model) $this->_hasOwnModel = true;
            else $this->_hasOwnModel = false;
    }
    
    protected function notifyParentModelChanged($model) {
        if (!$this->_hasOwnModel) {
            $this->_model = false;
            if ($this->modelPropertyName === '' && $this->autoModelProperty) $this->modelPropertyName = false;
        }
    }
    
    function getPropertyName() {
        if ($this->modelPropertyName === false) {
            $this->modelPropertyName = '';
            if ($this->autoModelProperty) {
                if ($this->_parent) $prefix = $this->_parent->getPropertyPrefixForTheChild($this);
                    else $prefix = '';
                $this->modelPropertyName = Ac_Util::concatPaths($prefix, $this->name);
                if (strlen($this->modelPropertyName)) {
                    $m = $this->getModel();
                    if (!$m || !$m->hasProperty($this->modelPropertyName)) $this->modelPropertyName = '';
                } 
            }
        }
        return $this->modelPropertyName;
    }
    
    function getResultPath() {
        if ($this->resultPath !== false) $res = $this->resultPath;
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
                    if (!$m || !Ac_Accessor::methodExists($m, $this->modelGetterName)) $this->modelGetterName = '';
                } 
            }
        }
        return $this->modelGetterName;
    }
    
    
    /**
     * @return Ac_Model_Property Metadata for this control
     */
    function getModelProperty() {
        if ($this->_property === false) {
            $this->_property = null;
            if (strlen($p = $this->getPropertyName()) && ($m = $this->getModel())) {
                if ($m->hasProperty($p)) {
                    $onlyStatic = !($this->forceDynamicPropInfo || $m->isBound());
                    $this->_property = $m->getPropertyInfo($p, $onlyStatic);
                }
                else
                    $this->_property = null;
            }
        }
        return $this->_property;
    }
    
    /**
     * Sets model property that will be used to retrieve various metadata. If $property is valid Ac_Model_Property object,
     * model and modelPropertyName properties of the control will be set to $property->srcObject and $property->propName 
     * respectively.
     *      
     * @param Ac_Model_Property $property Null or false can also be provided.
     */
    function setModelProperty($property) {
        if (!is_null($property) && ($property !== false) && !is_a($property, 'Ac_Model_Property')) 
            trigger_error ("\$property should be null, false or the instance of Ac_Model_Property", E_USER_ERROR);
        $this->_property = $property;
        if (is_a($property, 'Ac_Model_Property') && is_a($property->srcObject, 'Ac_Model_Data')) {
            $this->setModel($property->srcObject);
            $this->modelPropertyName = $property->propName;
        }
    }
    
    // --------------------------------- template methods ---------------------------- 

    function doBindFromRequest() {
        if ($this->decodeHtmlEntitiesOnInput !== false) {
            $c = $this->decodeHtmlEntitiesOnInput === true? null : $this->decodeHtmlEntitiesOnInput;
            $this->_rqData = Ac_Util::htmlEntityDecode($this->_rqData, ENT_QUOTES, $c);
        }
    }
    
    function _doInitDisplayChildren() {
    }
    
    /**
     * Should return default value
     */
    function _doGetDefault() {
        if (($m = $this->getModel()) && !$this->dontGetDefaultFromModel) {
            if (strlen($p = $this->getPropertyName())) {
                $res = $m->getField($p);
            }
            elseif ($this->useGetterIfPossible && $g = $this->getGetterName()) {
                if (is_array($this->getterParams) && $this->getterParams) {
                    $res = call_user_func_array(array($m, $g), $this->getterParams);
                } else {
                    $res = $m->$g();
                }
            }
            else $res = null;
        }
        else $res = null;
        return $res;
    }
    
    /**
     * @access protected
     */
    function _doGetValue() {
        if (($this->readOnly !== true) && isset($this->_rqData['value'])) {
            $res = $this->_rqData['value'];
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
        $res = array (& $this);
        if ($this->templatePartParam !== false) $res[] = $this->templatePartParam;
        return $res;
    }
    
    /**
     * Should return array of parameters that will be passed to the template's method when current control is rendered.
     * @access protected
     */
    function _doGetWrapperTemplatePartParams($representation) {
        $res = array (& $this, $representation);
        if (isset($this->wrapperTemplateParam) && $this->wrapperTemplateParam) {
            $res[] = $this->wrapperTemplateParam;
        }
        return $res;
    }
    
    /**
     * Template method to initialize template properties upon it's instantiation
     * @access protected
     * @param Ac_Form_Control_Template $template
     */
    function _doInitializeTemplate ($template) {
        if ($this->htmlResponse) $template->htmlResponse = $this->htmlResponse;
    }
    
    /**
     * @access protected
     */
    function _doIsSubmitted() {
        return false;
    }
    
    // ----------------------------- supplementary methods ------------------------- 

    /**
     * @param Ac_Form_Control $control1
     * @param Ac_Form_Control $control2
     */
    function _displayOrderCallback($control1, $control2) {
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
     * @return Ac_Form_Control
     * @param string $class Find parent control of specified class
     */
    function _getRootControl($class = false) {
        $curr = $this;
        while ($curr->_parent && ($class === false || !($curr instanceof $class)))
            $curr = $curr->_parent;
        if ($class !== false && !($curr instanceof $class)) $curr = null;
        return $curr;
    }

    function updateFromRequest() {
        $this->bindFromRequest();
        if ($this->name === 'mc_gross') Ac_Debug::dd(get_class($this->_context), $this->_context, $this->_rqData, $this->_context->_data, $this->_context->getData());
    }
    
    function updateFromModel() {
        if ($this->getModel()) {
            $this->deleteValue();
        }
    }
    
    function updateModel() {
        if (($m = $this->getModel()) && !$this->isReadOnly() 
            && strlen($p = $this->getPropertyName()) 
            && $m->hasProperty($p)
        ) {
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
        Ac_Util::setArrayByPath($ctx->_baseUrl->query, Ac_Util::pathToArray($f->getContext()->mapParam($f->_methodParamName)), 'xhr');
        Ac_Util::setArrayByPath($ctx->_baseUrl->query, Ac_Util::pathToArray($f->getContext()->mapParam('xhrTarget')), $this->getXhrId());
        $res = $ctx->getUrl(array(), false);
        return $res;
    }
    
    function invokeCallback($callback) {
        $res = null;
        if ($callback) {
            $args = func_get_args();
            $args = array_slice($args, 1);
            if (is_array($callback) && isset($callback[0])) {
                if (is_object($callback[0]) && ($callback[0] instanceof Ac_Form_Control_Path)) {
                    $callback[0] = $callback[0]->getControl($this, true);
                }
            }
            $res = call_user_func_array($callback, $args);
        }
        return $res;
    }
    
    /**
     * @return array jsProperty => phpProperty
     */
    function getJsPropMap() {
        if ($this->jsPropMap === false) return array();
            else return $this->jsPropMap;
    }
    
    protected function getJsInitializerData() {
        $res = array();
        foreach ($this->getJsPropMap() as $k => $v) {
            if (is_numeric($k)) $k = $v;
            if (is_string($v)) {
                if (Ac_Accessor::methodExists('', $m = 'getJs'.$v)) {
                    $val = $this->$m();
                } else {
                    $val = Ac_Accessor::getObjectProperty($this, $k);
                }
            } elseif (is_callable($v)) {
                $val = call_user_func($v, $k, $this);
            } else {
                throw new Exception("getJsInitializerData('{$k}'): don't know what to do with php property name/getter ".gettype($v));
            }
            $res[$k] = $val;
        }
        if (is_array($this->jsExtras) && $this->jsExtras) {
            Ac_Util::ms($res, $this->jsExtras);
        }
        return $res;
    }
    
    function getJsObjectId() {
        if ($this->jsConstructor !== false) {
            if ($this->jsObjectId !== false) {
                $res = $this->jsObjectId;
            } else {
                $res = 'js_'.$this->_context->mapIdentifier('');
            }
        } else {
            $res = false;
        }
        return $res;
    }
    
    /**
     * @return Ac_Js_Object
     */
    function createJsObject() {
        $res = false;
        if ($this->jsConstructor !== false) {
            $data = $this->getJsInitializerData();
            $args = array();
            if ($data = $this->getJsInitializerData()) $args[0] = $data;
            $res = new Ac_Js_Object($this->getJsObjectId(), $this->jsConstructor, $args);
        }
        return $res;
    }
    
    function getAssetLibs() {
        $res = Ac_Util::toArray($this->assetLibs);
        return $res;
    }
    
    function getApplication() {
        if ($this->application) $res = $this->application;
        elseif ($this->_parent) $res = $this->_parent->getApplication();
        else $res = Ac_Application::getDefaultInstance();
        return $res;
    }

    function getDisplayValue() {
        $res = $this->getValue();
        if ($this->decorator) {
            $res = Ac_Decorator::decorate ($this->decorator, $res, $this->decorator, $this->getModel());
        }
        return $res;
    }
    
    function setId($id) {
        $this->id = $id;
    }
    
}

