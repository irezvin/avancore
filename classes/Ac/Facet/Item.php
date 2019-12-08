<?php

class Ac_Facet_Item extends Ac_Prototyped {

    protected $debug = null;
    
    protected $debugData = array();
    
    /**
     * @var Ac_Facet_Set
     */
    protected $facetSet = false;

    protected $name = false;

    protected $caption = false;
    
    /**
     * @var Ac_Facet_ItemImpl
     */
    protected $impl = array();
    
    protected $view = false;
    
    protected $value = false;
    
    protected $valueIsNatural = true;

    protected $emptyValue = false;

    protected $emptyCaption = false;
    
    protected $possibleValues = false;
    
    protected $multiple = false;
    
    /**
     * Allows to specify custom filtes' for different values
     * 
     * @var array (value => title)
     */
    protected $titlesForValues = false;
    
    /**
     * Whether possibleValues where supplied by external application using setPossibleValues()
     * or not
     * 
     * @var bool
     */
    protected $possibleValuesSupplied = false;

    function setEmptyCaption($emptyCaption) {
        $this->emptyCaption = $emptyCaption;
    }

    function getEmptyCaption() {
        return $this->emptyCaption;
    }    
    
    function setFacetSet(Ac_Facet_Set $facetSet) {
        $this->facetSet = $facetSet;
    }

    /**
     * @return Ac_Facet_Set
     */
    function getFacetSet() {
        return $this->facetSet;
    }

    function setName($name) {
        $this->name = $name;
    }

    function getName() {
        return $this->name;
    }

    function setCaption($caption) {
        $this->caption = $caption;
    }

    function getCaption() {
        return strlen($this->caption)? $this->caption : $this->name;
    }    

    function setImpl($impl) {
        if (!(is_array($impl) || $impl instanceof Ac_Facet_ItemImpl)) {
            throw new Exception("\$select must be either array or Ac_Facet_ItemImpl");
        }
        $this->impl = $impl;
        if (is_object($this->impl)) $this->impl->setItem($this);
    }

    /**
     * @return Ac_Facet_ItemImpl
     */
    function getImpl($require = false) {
        if (is_array($this->impl)) {
            $this->impl = $this->facetSet->getImpl()->createItemImpl($this, $this->impl);
        }
        if ($require && !$this->impl) throw new Exception("Cannot find Impl object: setImpl() first");
        return $this->impl;
    }
    
    function setValue($value) {
        $this->value = $value;
    }
    
    function notifySetValueChanged() {
        if (!$this->possibleValuesSupplied) 
            $this->possibleValues = false;
    }
    
    function getValue() {
        return $this->value;
    }
    
    function getPossibleValues() {
        if ($this->possibleValues === false) {
            $res = $this->getImpl(true)->getPossibleValues();
            if ($this->emptyCaption !== false) {
                $r = array();
                foreach ($res as $k => $v) {
                    if (!is_array($v)) {
                        if (!strlen($v) && is_numeric($k) || !strlen($k)) {
                            $k = $this->getEmptyValue();
                            $v = $this->getEmptyCaption();
                        }
                        $r[$k] = $v;
                        continue;
                    }
                    if (!strlen($v['title']) || is_null($v['title'])) {
                        $k = $this->getEmptyValue();
                        $v['title'] = $this->getEmptyCaption();
                    }
                    if (isset($this->titlesForValues[$k])) {
                        $v['title'] = $this->titlesForValues[$k];
                    }
                    $r[$k] = $v;
                }
                $res = $r;
            }
            $this->possibleValues = $res;
        }
        return $this->possibleValues;
    }
    
    function setPossibleValues($possibleValues) {
        if (!(is_array($possibleValues) || $possibleValues === false)) throw new Ac_E_InvalidCall("\$possibleValues must be either array or FLASE");
        $this->possibleValues = $possibleValues;
        $this->possibleValuesSupplied = $possibleValues !== false;
    }
    
    function getValueCaption($glue = ", ") {
        $res = false;
        $cap = array();
        $vv = $this->getPossibleValues();
        foreach (Ac_Util::toArray($this->getValue()) as $v) {
            if (isset($vv[$v])) $cap[] = $vv[$v]['title'];
        }
        if ($cap) $res = $glue === false? $cap : implode($glue, $cap);
        return $res;
    }

    function setView($view) {
        if (!(is_array($view) || $view instanceof Ac_Facet_ItemView)) {
            throw new Exception("\$select must be either array or Ac_Facet_ItemView");
        }
        $this->view = $view;
        if (is_object($this->view)) $this->view->setItem($this);
    }

    /**
     * @return Ac_Facet_View
     */
    function getView($require = false) {
        if (is_array($this->view)) {
            $this->view = Ac_Prototyped::factory($this->view, 'Ac_Facet_ItemView');
            $this->view->setItem($this);
        }
        if ($require && !$this->view) {
            $def = $this->facetSet->getDefaultItemView();
            if ($def) {
                if (!is_object($def)) $def = Ac_Prototyped::factory ($def, 'Ac_Facet_ItemView');
                $def->setItem($this);
                $this->view = $def;
            }
        }
        if ($require && !$this->view) throw new Exception("Cannot find View object: setView() first");
        return $this->view;
    }    
    
    function render(Ac_Legacy_Controller_Response_Html $response, $return = false) {
        if ($return) ob_start();
        $this->getView(true)->renderItem($response);
        if ($return) return ob_get_clean();
    }
    
    function setValueIsNatural($valueIsNatural) {
        $this->valueIsNatural = (bool) $valueIsNatural;
    }

    function getValueIsNatural() {
        return $this->valueIsNatural;
    }    
    
    function filterValue($value) {
        return $this->getView(true)->filterValue($value);
    }
     
    function setEmptyValue($emptyValue) {
        $this->emptyValue = $emptyValue;
    }

    function getEmptyValue() {
        if (!strlen($this->emptyValue) && $this->emptyCaption !== false) return ' ';
        return $this->emptyValue;
    }

    function setMultiple($multiple) {
        $this->multiple = $multiple;
    }

    function getMultiple() {
        return $this->multiple;
    }    

    function setDebug($debug) {
        $this->debug = is_null($debug)? $debug : (bool) $debug;
    }

    function getDebug() {
        if (is_null($this->debug)) return $this->getFacetSet()->getDebug();
        return $this->debug;
    }    
    
    function getDebugData() {
        return $this->debugData;
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

    function setTitlesForValues(array $titlesForValues) {
        $this->titlesForValues = $titlesForValues;
    }

    /**
     * @return array
     */
    function getTitlesForValues() {
        return $this->titlesForValues;
    }    
    
    
}