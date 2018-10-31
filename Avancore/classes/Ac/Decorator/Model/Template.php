<?php

class Ac_Decorator_Model_Template extends Ac_Decorator implements Ac_I_Decorator_Model {

    /**
     * Example of template: string {{placeholder}} {{placeholder}}
     * @var string
     */
    var $template = '{{_value_}}';
    
    var $valuePlaceholder = '_value_';
    
    var $templateIfNoModel = '{{_value_}}';
    
    /**
     * @var array placeholder => propName or placeholder => array (propName, decorator)
     */
    var $placeholders = array();
    
    var $dummyValue = null;
    
    var $valueToModel = true;
    
    var $defaultDecorator = false;
    
    var $treatArraysAsObjects = false;
    
    protected $model = false;
    
    function setModel($model = null) {
        $this->model = $model;
    }

    function getModel() {
        if ($this->model) $res = $this->model;
        else $res = Ac_Decorator::topModel ();
        return $res;
    }
    
    function apply($value) {
        $model = $this->getModel();
        $push = false;
        if ($this->valueToModel && is_array($value) || is_object($value)) {
            $push = true;
            $model = $value;
        }
        if ($push) Ac_Decorator::pushModel($model);
        $res = $model? $this->template : $this->templateIfNoModel;
        if ($model) {
            preg_match_all('#\{\{([^}]+)\}\}#u', $res, $matches);
            if ($matches) {
                $tr = array();
                foreach ($matches[1] as $idx => $placeholder) {
                    $decorator = false;
                    $propName = $placeholder;
                    if (isset($this->placeholders[$placeholder])) {
                        if (is_array($this->placeholders[$placeholder])) {
                            if (isset($this->placeholders[$placeholder][1])) {
                                $propName = $this->placeholders[$placeholder][0];
                                $decorator = & $this->placeholders[$placeholder][1];
                            } else {
                                $decorator = & $this->placeholders[$placeholder][0];
                            }
                        } else {
                            $propName = $this->placeholders[$placeholder];
                        }
                    }
                    if ($placeholder === $this->valuePlaceholder) $val = $value;
                    else $val = Ac_Accessor::getObjectProperty ($model, $propName, $this->dummyValue, $this->treatArraysAsObjects);
                    if ($this->defaultDecorator) $decorator = & $this->defaultDecorator;
                    if ($decorator) $val = Ac_Decorator::decorate($decorator, $val, $decorator);
                    $tr[$matches[0][$idx]] = $val;
                }
                if ($tr) $res = strtr($res, $tr);
            }
        } else {
            if ($this->defaultDecorator) $value = Ac_Decorator::decorate($this->defaultDecorator, $value, $this->defaultDecorator);
            $res = str_replace('{{'.$this->valuePlaceholder.'}}', $value, $res);
        }
        if ($push) Ac_Decorator::popModel($model);
        return $res;
    }
    
}