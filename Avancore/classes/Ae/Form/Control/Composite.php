<?php

Ae_Dispatcher::loadClass('Ae_Form_Control');

class Ae_Form_Control_Composite extends Ae_Form_Control {
    
    /**
     * Base part of the property paths of child controls with $autoModelProperty = true.
     *
     * For example, if this control's $modelPropertyBase is 'userDetails' and child control's name is 'name',
     * and that child control has $autoModelProperty = true, it's effective model property name will be 'userDetails[name]' 
     * @var string
     */
    var $modelPropertyBase = '';

    var $moveErrorsToTheChildren = true;
    
    var $_controls = array();
    
    var $_creationCount = 0;
    
    var $_gotDefault = false;
    
    var $getDefaultFromModel = false;
    
    var $valueFirstInContext = false;
    
    protected $modelUpdated = false;
    
    function doInitProperties($options) {
        $this->_iid = round(rand()*100);
        parent::doInitProperties($options);
        if (!isset($options['controls'])) $options['controls'] = array();
        $this->addInitialControls($options['controls']);
    }
    
    protected function addInitialControls(array $controls) {
        $this->addControls($controls);
    }
    
    function addControl($name, $settings = array()) {
        if (isset($this->_controls[$name])) trigger_error ("Control with name '{$name}' already exists - delete it first", E_USER_ERROR);
            else {
                if (!isset($settings['creationOrder'])) {
                    $settings['creationOrder'] = $this->_creationCount++;
                }
                $this->_controls[$name] = $settings;
            }
        $this->_orderedDisplayChildren = false;
    }
    
    protected function sortControlPrototypesByCreationOrder(array $prototypes) {
        
        //uasort($prototypes, array('Ae_Form_Control_Composite', '_coCompare'));
        return $prototypes;
    }
    
    static function _coCompare($prot1, $prot2) {
        if (is_object($prot1)) {
            $co1 = isset($prot1->_creationOrder)? $prot1->_creationOrder : 0;
        } elseif (is_array($prot1)) {
            $co1 = isset($prot1['creationOrder'])?  $prot1['creationOrder'] : 0;
        } else {
            $co1 = 0;
        }
        if (is_object($prot2)) {
            $co2 = isset($prot2->_creationOrder)? $prot2->_creationOrder : 0;
        } elseif (is_array($prot2)) {
            $co2 = isset($prot2['creationOrder'])?  $prot2['creationOrder'] : 0;
        } else {
            $co2 = 0;
        }
        return $co1 - $co2;
    }
    
    function addControls($controls) {
        $keys = array_keys($this->sortControlPrototypesByCreationOrder($controls));
        foreach ($keys as $name) {
            $this->addControl($name, $controls[$name]);
        }
        
    }
    
    function removeControl($name) {
        if (!in_array($name, $this->listControls())) trigger_error ("No such control: '{$name}'", E_USER_ERROR);
        unset($this->_controls[$name]);
    }
    
    /**
     * @param string $name
     * @return Ae_Form_Control
     */
    function getControl($name) {
        if (!in_array($name, $this->listControls())) trigger_error ("No such control: '{$name}'", E_USER_ERROR);
        if (is_array($this->_controls[$name])) {
            $controlSettings = $this->_controls[$name];
            $this->_controls[$name] = & $this->_createControl($name, $controlSettings);
        }
        $res = & $this->_controls[$name];
        return $res;
    }
    
    function listControls() {
        $res = array_keys($this->_controls);
        return $res;
    }
    
    function getControlsValues() {
        $res = array();
        foreach ($this->listControls() as $k) {
            $control = & $this->getControl($k);
            if ($control->hasValue && $control->isEnabled()) {
                $resultPath = $control->getResultPath();
                Ae_Util::setArrayByPath($res, Ae_Util::pathToArray($resultPath), $control->getValue(), true);
                //$res[$k] = $control->getValue();
            }
        }
        return $res;
    }
    
    /**
     * @param string $name
     * @return mixed|null Default value (null if it isn't found)
     */
    function getDefaultValueForTheChild($name) {
        $def = false;
        if ($this->_hasDefault && is_array($this->_default)) $def = $this->_default; 
        else {
            if ($this->getDefaultFromModel && !$this->_gotDefault) {
                $this->_gotDefault = true;
                $def = $this->getDefault();
                if (is_array($def)) {
                    $this->_hasDefault = true;
                    $this->_default = $def;
                }
            }
        }
        if (is_array($def) && isset($def[$name])) $res = $def[$name];
            else $res = null;
        return $res;
    }
    
    /**
     * Returns model prefix that will be added to the child names to automatically form child property names
     *
     * @param Ae_Form_Control $child
     * @return string
     */
    function getPropertyPrefixForTheChild(& $child) {
        if ($this->_hasOwnModel) {
            $res = $this->modelPropertyBase;
        } else {
            $res = $this->getPropertyName();
        }
        return $res;
    }

    /**
     * Returns element from errors array that has same name as the child.  
     *
     * @param Ae_Form_Control $child Child control to get errors for
     * @param bool $move Whether to remove found errors from own array if $this->moveErrorsToTheChilren is true
     * @return array|bool
     */
    function getErrorsForTheChild(& $child, $move = false) {
        $this->getErrors();
        $res = false;
        if (is_array($this->errors) && isset($this->errors[$child->name])) {
            $res = $this->errors[$child->name];
            if ($move && $this->moveErrorsToTheChildren) unset($this->errors[$child->name]); 
        }
        return $res;
    }
    
    /**
     * Returns model for the child control
     *
     * @param Ae_Form_Control $child
     * @return Ae_Model_Data
     */
    function getModelForTheChild(& $child) {
        $res = & $this->getModel();
        return $res;
    }
    
    function getValue() {
        return parent::getValue();
    }
    
    function _doGetValue() {
        if ($this->getDefaultFromModel) {
            if (!($this->readOnly === true) && isset($this->_rqData) && $this->_rqData) {
                $res = & $this->getControlsValues();
            } else {
                $res = $this->getDefault();
            }
            return $res;
        } else {
            return $this->getControlsValues();
        }
    }
    
    /**
     * @return Ae_Legacy_Controller_Context_Http
     */
    function & _createSubContext($name) {
        Ae_Dispatcher::loadClass('Ae_Form_Context');
        $res = & Ae_Form_Context::spawnFrom($this->_context, $name);
        return $res;
    }
    
    /**
     * Creates new control within the context of current one and with parent as current one
     *
     * @param string $name Name of the controls
     * @param array $settings
     * @return Ae_Form_Control
     */
    function & _createControl($name, $settings = array()) {
        if (isset($settings['class']) && strlen($settings['class'])) {
            $class = $settings['class'];
            Ae_Dispatcher::loadClass($class);
        } else {
            $class = 'Ae_Form_Control'; 
        }
        $context = & $this->_createSubContext($name);
        $settings['parent'] = & $this;
        if (isset($settings['name']) && ($settings['name'] !== $name)) 
            trigger_error ("Name in the settings of the sub control ('{$settings['name']}') does not match key in the array ('{$name}')", E_USER_WARNING);
        $settings['name'] = $name;
        $instanceId = $name;
        $co = isset($settings['creationOrder'])? $settings['creationOrder'] : '-';
        $res = new $class ($context, $settings, $instanceId);
        if (isset($settings['displayParent'])) $res->setDisplayParent($settings['displayParent']);
            else $res->setDisplayParent($this);
        //if (isset($settings['_creationOrder'])) $res->_creationOrder = $settings['_creationOrder']; 
        return $res;
    }
    
    function _doInitDisplayChildren() {
        foreach ($this->listControls() as $c) {
            $con = & $this->getControl($c);
        }
    }
    
    function _doGetDefault() {
        if (($m = & $this->getModel()) && !$this->dontGetDefaultFromModel) {
            if (strlen($p = $this->getPropertyName())) $res = $m->getField($p);
            elseif ($this->useGetterIfPossible && $g = $this->getGetterName()) $res = $m->$g();
            else $res = null;
        } else {
            $res = null;
        }
        return $res;
    }
    
    function updateFromModel() {
        foreach ($this->listControls() as $c) {
            $this->getControl($c)->updateFromModel();
        }
    }
    
    function updateModel() {
        $this->modelUpdated = true;
        foreach ($this->listControls() as $c) {
            $this->getControl($c)->updateModel();
        }
    }
    
    function executeXhr() {
        $xhrTarget = $this->_context->getData('xhrTarget', '');
        if (strlen($xhrTarget) && ($c = $this->searchControlByPath($xhrTarget))) $c->executeXhr();
            else $this->executeXhrCore();
    }
    
    function isXhr() {
        $res = $this->_context->getData($this->_methodParamName) === 'xhr';
        return $res;
    }
    
}

?>