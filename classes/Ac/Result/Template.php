<?php

class Ac_Result_Template extends Ac_Result {

    const CONTENT_MERGE_DROP = 0;
    const CONTENT_MERGE_PREPEND = 1;
    const CONTENT_MERGE_APPEND = 2;
    
    protected $contentMergeMode = self::CONTENT_MERGE_PREPEND;
    
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
    protected $fields = false;
    
    /**
     * @var array
     */
    protected $partArgs = array();
    
    protected $templateProperties = array();
    
    function setTemplate($template) {
        if ($template !== ($oldTemplate = $this->template)) {
            $this->template = $template;
            $this->templateInstance = false;
            $this->replaceWith = null;
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
                $this->setTemplateInstance(Ac_Prototyped::factory($this->template, 'Ac_Template', $this->templateProperties));
            }
        }
        return $this->templateInstance;
    }
    
    function setTemplateInstance(Ac_Template $templateInstance) {
        if ($this->templateInstance === $templateInstance) return;
        $this->replaceWith = null;
        $this->templateInstance = $templateInstance;
        if ($this->component) $this->templateInstance->setComponent($this->component);
        if ($this->fields) $this->templateInstance->setFields($this->fields, true);
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

    function setFields(array $fields) {
        $this->fields = $fields;
        if ($this->templateInstance) $this->templateInstance->setFields($fields, true);
    }

    /**
     * @return array
     */
    function getFields() {
        return $this->fields;
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
    
    function getReplaceWith() {
        if (!$this->replaceWith && $this->getPartName()) {
            $this->replaceWith = $this->render();
        }
        return $this->replaceWith;
    }
    
    /**
     * @return Ac_Result
     */
    function render() {
        $pn = $this->getPartName();
        if (!strlen($pn)) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() without partName set");
        $tpl = $this->getTemplateInstance();
        if (!($tpl)) throw new Ac_E_InvalidUsage("Cannot ".__METHOD__."() without template or templateInstance set");
        $res = $tpl->renderResultWithArgs($pn, $this->partArgs);
        if ($this->contentMergeMode !== self::CONTENT_MERGE_DROP && strlen($ownContent = $this->getContent())) {
            if ($this->contentMergeMode === self::CONTENT_MERGE_APPEND) $res->put($this->getContent());
            if ($this->contentMergeMode === self::CONTENT_MERGE_PREPEND) $res->prepend($this->getContent());
        }
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
    
    function setTemplateProperties(array $templateProperties) {
        $this->templateProperties = $templateProperties;
        if ($this->templateInstance) Ac_Accessor::setObjectProperty ($this->templateInstance, $templateProperties);
    }

    /**
     * @return array
     */
    function getTemplateProperties() {
        return $this->templateProperties;
    }    
    

    function setContentMergeMode($contentMergeMode) {
        if (!in_array($contentMergeMode, [self::CONTENT_MERGE_PREPEND, self::CONTENT_MERGE_APPEND, self::CONTENT_MERGE_DROP]))
            throw Ac_E_InvalidCall::outOfConst ('contentMergeMode', $contentMergeMode, 'CONTENT_MERGE_', 'Ac_Result_Template');
        $this->contentMergeMode = $contentMergeMode;
    }

    function getContentMergeMode() {
        return $this->contentMergeMode;
    }    
    
}
