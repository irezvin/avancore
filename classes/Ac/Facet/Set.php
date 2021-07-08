<?php

class Ac_Facet_Set extends Ac_Prototyped {

    /**
     * @var bool
     */
    protected $debug = false;
    
    /**
     * @var array
     */
    protected $items = false;

    /**
     * @var Ac_Facet_SetImpl
     */
    protected $impl = false;

    protected $paramName = 'facets';
    
    protected $defaultItemView = false;
    
    protected $valueOrder = array();
    
    protected $view = false;

    protected $emptyParamName = false;

    protected $emptyParamValue = null;
    
    protected $debugData = array();
    
    /**
     * @var array
     */
    protected $defaultValue = false;
    
    function setParamName($paramName) {
        $this->paramName = $paramName;
    }

    function getParamName() {
        return $this->paramName;
    }    
    
    function setItems(array $items) {
        $this->items = Ac_Prototyped::factoryCollection($items, 'Ac_Facet_Item', array('facetSet' => $this), 'name', true, true);
    }
    
    /**
     * @return array
     */
    function getItems() {
        return $this->items;
    }
    
    /**
     * @return array
     */
    function listItems() {
        return array_keys($this->items);
    }
    
    /**
     * @return Ac_Facet_Item
     */
    function getItem($id) {
        if (!in_array($id, $this->listItems())) throw new Exception("No such item: '{$id}'");
        return $this->items[$id];
    }
    
    function setImpl($impl) {
        if (!(is_array($impl) || $impl instanceof Ac_Facet_SetImpl)) {
            throw new Exception("\$select must be either array or Ac_Facet_SetImpl");
        }
        $this->impl = $impl;
        if (is_object($this->impl)) $this->impl->setFacetSet($this);
    }

    /**
     * @return Ac_Facet_ItemImpl
     */
    function getImpl($require = false) {
        if (is_array($this->impl)) {
            $this->impl = Ac_Prototyped::factory($this->impl, 'Ac_Facet_SetImpl');
            $this->impl->setFacetSet($this);
        }
        if ($require && !$this->impl) throw new Exception("Cannot find Impl object: setImpl() first");
        return $this->impl;
    }

    function setValue(array $value) {
        foreach ($this->items as $item) $item->notifySetValueChanged();
        if (is_object($this->impl)) $this->impl->notifySetValueChanged();
        if (is_object($this->view)) $this->view->notifySetValueChanged();
        $items = array_keys($this->items);
        $this->valueOrder = array();
        $emptyParamUsed = false;
        if ($this->emptyParamName !== false) {
            if (isset($value[$this->emptyParamName])) {
                if (is_null($this->emptyParamValue )) {
                    $emptyParamUsed = true;
                } else {
                    $emptyParamUsed = $value[$this->emptyParamName] == $this->emptyParamValue;
                }
            }
        }
        if ($emptyParamUsed) $value = array();
        else {
            $noItems = !array_intersect_key($value, $this->items);
            
            if ($noItems && $this->defaultValue)
                $value = $this->defaultValue;
            
            foreach ($value as $name => $val) {
                if (isset($this->items[$name])) {
                    $this->valueOrder[] = $name;
                    $this->items[$name]->setValue($val);
                }
            }
        }
    }

    /**
     * @return array
     */
    function getValue() {
        $res = array();
        foreach ($this->valueOrder as $name) {
            $res[$name] = $this->items[$name]->getValue();
        }
        return $res;
    }    
    
    function setView($view) {
        if (!(is_array($view) || $view instanceof Ac_Facet_SetView)) {
            throw new Exception("\$select must be either array or Ac_Facet_SetView");
        }
        $this->view = $view;
        if (is_object($this->view)) $this->view->setFacetSet($this);
    }

    /**
     * @return Ac_Facet_SetView
     */
    function getView($require = false) {
        if (is_array($this->view)) {
            $this->view = Ac_Prototyped::factory($this->view, 'Ac_Facet_SetView');
            $this->view->setFacetSet($this);
        }
        if ($require && !$this->view) throw new Exception("Cannot find View object: setView() first");
        return $this->view;
    }    

    function setDefaultItemView($defaultItemView) {
        $this->defaultItemView = $defaultItemView;
    }

    function getDefaultItemView() {
        return $this->defaultItemView;
    }
    
    function updateValueFromRequest($source = false) {
        if ($source === false) $source = $_REQUEST;
        $val = $this->getView(true)->getValue($source);
        $this->setValue($val);
    }
    
    function render(Ac_Legacy_Controller_Response_Html $response, $return = false) {
        if ($return) ob_start();
        $this->getView(true)->renderSet($response);
        if ($return) return ob_get_clean();
    }
    
    function getJsResponse() {
        return $this->getView(true)->getJsResponse();
    }    
    
    function getValueOrder() {
        return $this->valueOrder;
    }
    
    function setDebug($debug) {
        $this->debug = (bool) $debug;
    }

    function getDebug() {
        return $this->debug;
    }    
    
    function setDebugData($key, $data = null) {
        if (func_num_args() == 1) {
            $this->debugData = $key;
            if (!$this->debugData) $this->debugData = array();
        }
        else {
            if (!is_null($data))
                $this->debugData[$key] = $data;
            else 
                unset ($this->debugData[$key]);
        }
    }
    
    function getDebugData() {
        $res = $this->debugData;
        $items = array();
        foreach ($this->listItems() as $i) {
            $d = $this->getItem($i)->getDebugData();
            if ($d) $items[$i] = $d;
        }
        if ($items) $res['items'] = $items;
        return $res;
    }

    function setEmptyParamName($emptyParamName) {
        $this->emptyParamName = $emptyParamName;
    }

    function getEmptyParamName() {
        return $this->emptyParamName;
    }

    function setEmptyParamValue($emptyParamValue) {
        $this->emptyParamValue = $emptyParamValue;
    }

    function getEmptyParamValue() {
        return $this->emptyParamValue;
    }    

    function setDefaultValue(array $defaultValue) {
        $this->defaultValue = $defaultValue;
    }

    /**
     * @return array
     */
    function getDefaultValue() {
        return $this->defaultValue;
    }    
    
}