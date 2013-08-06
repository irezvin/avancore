<?php

class Ac_Result_Template extends Ac_Result {
    
    protected $writeOnStore = true;

    protected $template = false;

    /**
     * @var Ac_Template
     */
    protected $templateInstance = false;

    protected $component = false;

    protected $partName = false;
    
    protected $renderedResultWriter = false;

    /**
     * @var array
     */
    protected $values = false;
    
    /**
     * @var array
     */
    protected $partArgs = array();

    function setTemplate($template) {
        if ($template !== ($oldTemplate = $this->template)) {
            $this->template = $template;
            $this->templateInstance = false;
        }
    }

    function getTemplate() {
        return $this->template;
    }

    /**
     * @return Ac_Template
     */
    function getTemplateInstance() {
        if ($this->templateInstance === false) {
            $this->templateInstance = null;
            if ($this->template) {
                $this->templateInstance = Ac_Prototyped::factory($this->template, 'Ac_Template');
                
                if ($this->values) 
                    $this->templateInstance->setValues($this->values, true);
                
                if ($this->component !== false) 
                    $this->templateInstance->setComponent($this->component);
            }
        }
        return $this->templateInstance;
    }

    function setComponent($component) {
        $this->component = $component;
        if ($this->component)
        if ($this->templateInstance) $this->templateInstance->setComponent($component);
    }

    function getComponent() {
        return $this->component;
    }

    function setPartName($partName) {
        $this->partName = $partName;
    }

    function getPartName() {
        if ($this->partName === false) {
            if (is_object($this->component) && $this->component instanceof Ac_Cr_Controller) {
                return $this->component->getAction();
            }
        }
        return $this->partName;
    }

    function setValues(array $values) {
        $this->values = $values;
        if ($this->templateInstance) $this->templateInstance->setValues($values, true);
    }

    /**
     * @return array
     */
    function getValues() {
        return $this->values;
    }    

    function setRenderedResultWriter($renderedResultWriter) {
        $this->renderedResultWriter = $renderedResultWriter;
    }

    function getRenderedResultWriter() {
        return $this->renderedResultWriter;
    }

    function setPartArgs(array $partArgs) {
        $this->partArgs = $partArgs;
    }

    /**
     * @return array
     */
    function getPartArgs() {
        return $this->partArgs;
    }    
    
    /**
     * @return Ac_Result
     */
    function render() {
        $pn = $this->getPartName();
        if (!strlen($pn)) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() without PartName set");
        $res = $this->getTemplateInstance()->renderResultWithArgs($pn, $this->partArgs);
        if ($w = $this->getRenderedResultWriter()) {
            $w = Ac_Prototyped::factory($w, 'Ac_Result_Writer');
            $res->setWriter($w);
        }
        return $res;
    }
    
    function createDefaultWriter() {
        $res = new Ac_Result_Writer_Template(array('source' => $this));
        return $res;
    }
    
}