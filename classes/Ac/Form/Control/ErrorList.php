<?php

class Ac_Form_Control_ErrorList extends Ac_Form_Control {

    var $templateClass = 'Ac_Form_Control_Template_Basic';
    
    var $templatePart = 'errorList';
    
    var $getErrorsFromModel = true;
  
    var $getErrorsFromParent = true;
    
    var $hideIfNoErrors = true;
    
    var $hideErrorsShownByOtherControls = false;
    
    var $visible = '?';
    
    var $showErrorsInMainArea = true;
    
    var $errorSourcePath = false;
    
    protected $returnErrors = false;
    
    /**
     * @return Ac_Form_Control_Composite
     */
    protected function getErrorsSource() {
        $root = false;
        if ($this->errorSourcePath !== false) $root = $this->searchControlByPath($this->errorSourcePath);
        else {
            if (!($root = $this->_getRootControl('Ac_Form'))) $root = $this->_getRootControl('Ac_Form_Control_Composite');
        }
        return $root;
    }
    
    protected function collectErrorsShownByOtherControls() {
        $res = array();
        if ($src = $this->getErrorsSource()) {
            $allControls = $src->findControlsRecursive(true);
            foreach ($allControls as $c) if ($c !== $this) {
                $e = $c->getErrors();
                if (is_array($e) && $e) Ac_Util::ms($res, $e);
            }
        }
        return $res;
    }
    
    function isVisible() {
        if ($this->visible === '?' && $this->hideIfNoErrors) {
            $tmp = $this->visible;
            $this->visible = (bool) count($this->getAllErrors());
            $res = parent::isVisible();
            $this->visible = $tmp;
        } else {
            $res = parent::isVisible();
        }
        return $res;
    }
    
    function getValue() {
        $res = false;
        if ($this->showErrorsInMainArea) {
            $this->returnErrors = true;
            $res = $this->getAllErrors();
            $this->returnErrors = false;
        }
        return $res;
    }
    
    function getAllErrors() {
        $this->returnErrors = true;
        $res = $this->getErrors();
        $this->returnErrors = false;
        return $res;
    }
    
    function getErrors() {
        if ($this->showErrorsInMainArea && $this->returnErrors === false) return array();
        $res = array();
        $own = parent::getErrors();
        if ($own) {
            Ac_Util::setArrayByPath($res, $this->getPath(), $own);
        }
        if ($this->getErrorsFromParent) {
            $root = $this->getErrorsSource();
            if ($root) {
                $e = $root->getErrors();
                if (is_array($e) && count($e)) {
                    Ac_Util::ms($res, $e);
                }
            }
        }
        if ($this->getErrorsFromModel && ($m = $this->getModel()) && $m->isChecked()) {
            $e = $m->getErrors();
            if (is_array($e) && count($e)) {
                Ac_Util::ms($res, $e);
            }
        }
        if ($this->hideErrorsShownByOtherControls) {
            $res = array_diff(Ac_Util::flattenArray($res), Ac_Util::flattenArray($this->collectErrorsShownByOtherControls()));
        }
        return $res;
    }
    
}