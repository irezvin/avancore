<?php

Ae_Dispatcher::loadClass('Ae_Form_Control_Composite');

class Ae_Form extends Ae_Form_Control_Composite {

    /**
     * Path to the control that should be used to check if form is submitted (or the submission control itself)
     * Several controls and/or paths can be specified too (for exmaple, to have a form with multiple submit buttons) 
     * 
     * @var string|array|Ae_Form_Control
     */
    var $submissionControl = false;
    
    /**
     * If it is set to true, form will check it's request for the presence of the special parameter with special value (independently of $submissionControl paraeter value).
     * If $submissionControl is set, it will be necessary to mach both conditions to consider that form is submitted.
     *   
     * @see Ae_Form::getOwnSubmissionParamName()
     * @see Ae_Form::getOwnSubmissionParamValue()
     * @var bool|string
     */
    var $performOwnSubmissionCheck = false;
    
    /**
     * Value or values that $submissionControl should match to consider that form is submitted. If it is set to FALSE, any non-false value of submission control 
     * would make the form report that it is submitted.
     *  
     * @see Ae_Form::performOwnSubmissionCheck
     * @var bool|string|array of strings
     */
    var $allowedSubmittedValues = false;
    
    var $autoModelProperty = false;
    
    var $formTemplateClass = 'Ae_Form_Control_Template_Basic';
    
    var $formTemplatePart = 'form';
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'table';
    
    var $_presentationWithForm = false;
    
    var $cssLibs = false;
    
    var $jsLibs = false;

    var $inlineStyles = false;
    
    /**
     * Always put base url arguments to 'action' attribute of the <form>
     * @var bool
     */
    var $baseUrlToAction = false;
    
    function fetchPresentation($refresh = false, $withWrapper = null) {
        if ($this->visible) {
            if (is_null($withWrapper)) $withWrapper = $this->showWrapper;
            if ($withWrapper) $res = $this->fetchWithWrapper($refresh);
            else {
                if ($this->_presentationWithForm === false || $refresh) {
                    $formTemplate = & $this->getTemplate($this->formTemplateClass);
                    $html = parent::fetchPresentation($refresh, false);
                    $this->_presentationWithForm = $formTemplate->fetch($this->formTemplatePart, array(& $this, $html));
                }
                $res = $this->_presentationWithForm;
            }
        } else {
            $res = false;
        }
        return $res;
    }
        
    function _doIsSubmitted() {
        
        $res = '?';
        
        if ($this->submissionControl) {
            
            $this->submissionControl = Ae_Util::toArray($this->submissionControl);

            $res = false;
            
            foreach ($this->submissionControl as $p) {
                
                $c = $this->searchControlByPathRef($p);
                
                if (!$c) trigger_error ("Form '".$this->_getPath()."': submissionControl property "
                    ."points to non-existent control ('{$p}')", E_USER_ERROR);
                $subValue = $c->getValue();
                if ($subValue !== false) {
                    if ($this->allowedSubmittedValues !== false) {
                        if (!is_array($this->allowedSubmittedValues)) $this->allowedSubmittedValues = array($this->allowedSubmittedValues);
                        if (in_array($subValue, $this->allowedSubmittedValues)) $res = true;
                    } else {
                        $res = true;
                    }
                } else {
                    $res = false;
                }
                    
                if ($res !== false && $this->performOwnSubmissionCheck) {
                    if ($res === '?') $res = true;
                    $sv = $this->_context->getData($this->getOwnSubmissionParamName(), false);
                    if ($sv !== $this->getOwnSubmissionParamValue()) $res = false;  
                }
            
                if ($res) break;
            
            }
                
        }
        
        return $res;
    }
    
    function getOwnSubmissionParamName() {
        return '_submitted';   
    }
    
    function getOwnSubmissionParamValue() {
        if (is_string($this->performOwnSubmissionCheck)) $res = $this->performOwnSubmissionCheck;
            else $res = 'submitted';
        return $res; 
    }
    
    
}

?>