<?php

class Ac_Facet_Item extends Ac_Prototyped {
    
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
                    if (!strlen($v['title']) || is_null($v['title'])) {
                        $k = $this->getEmptyValue();
                        $v['title'] = $this->getEmptyCaption();
                    }
                    $r[$k] = $v;
                }
                $res = $r;
            }
            $this->possibleValues = $res;
        }
        return $this->possibleValues;
    }
    
    function getValueCaption() {
        $res = false;
        if (($v = $this->getValue()) !== false) {
            $vv = $this->getPossibleValues();
            if (isset($vv[$v])) $res = $vv[$v]['title'];
        }
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
   
    
}