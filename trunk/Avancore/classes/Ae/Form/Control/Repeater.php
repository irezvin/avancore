<?php

Ae_Dispatcher::loadClass('Ae_Form_Control');

class Ae_Form_Control_Repeater extends Ae_Form_Control_Composite {
	
	var $childPrototype = array();
    
    var $addControlPrototype = array();
    
    var $childNameTemplate = '{index}';
    
    var $indexPlaceholder = '{index}';
    
    var $parentPlaceholder = '{parent}';
    
    var $prototypePathsToReplace = array();
    
    var $minChildren = 1;
    
    var $maxChildren = false;
    
    var $templateClass = 'Ae_Form_Control_Template_Basic';
    
    var $templatePart = 'simpleList';
    
    var $reloadFormJs = '';
    
    var $canRemoveBelowMinChildren = true;
    
    /**
     * Callback to function($repeater) that returns Javascript code to reload form (probably ithout submitting)
     */
    var $reloadFormCallback = false;
    
    /**
     * Callback to function($repeater, $child) that should return TRUE if child is empty
     */
    var $checkChildEmptyCallback = false;
    
    var $removeControlText = '&nbsp;';
    
    var $removeControlAttribs = array();
    
    var $defaultNumChildren = 0;
    
    protected $controlsInit = false;
    
    function getNumChildren() {
        $res = max($this->minChildren, (int) $this->_context->getData('numChildren', $this->defaultNumChildren));
        if ($this->maxChildren !== false) $res = min($res, $this->maxChildren);
        Ae_Util::setArrayByPath($this->_context->_baseUrl->query, Ae_Util::pathToArray($this->_context->mapParam('numChildren')), $res);
        return $res;
    }
    
    function processContextForRemoval() {
        
    }
    
    function listControls() {
        if (!$this->controlsInit) $this->initControls();
        return parent::listControls();
    }
    
    /**
     * @param string $name
     * @return Ae_Form_Control
     */
    function getControl($name) {
        if (!$this->controlsInit) $this->initControls();
        return parent::getControl($name);
    }
    
    protected function replacePlaceholders($string, $index) {
        $res = strtr($string, array(
            $this->indexPlaceholder => $index,
            $this->parentPlaceholder => $this->name,
        ));
        return $res;
    }
    
    protected function fixPrototype(array $prototype, $index) {
        foreach($this->prototypePathsToReplace as $p) {
            if (!is_null($val = Ae_Util::getArrayByPath($prototype, $p = Ae_Util::pathToArray($p))) && is_string($val)) {
                Ae_Util::setArrayByPath($prototype, $p, $this->replacePlaceholders($val, $index));
            }
        }
        return $prototype;
    }
    
    protected function getReloadCode() {
                
        $submit = $this->reloadFormJs;
        if (!strlen($submit)) {
            if ($this->reloadFormCallback) {
                $submit = $this->invokeCallback($this->reloadFormCallback, $this);
            } else {
                $form = $this->_getRootControl();
                if ($form instanceof Ae_Form) {
                    $submit = "document.".($form->getContext()->mapParam('')).".submit();";
                }
            }
        }
        
        return $submit;

    }
    
    protected function processChildrenRemoval() {
        $n = $this->getNumChildren();
        $d = $this->_context->getData();
        for ($i = 0; $i < $n; $i++) {
            if ((int) Ae_Util::getArrayByPath($d, array('remove', $i))) {
                unset($d[$i]);
                for ($j = $i + 1; $j < $n; $j++) {
                    $d[$j - 1] = $d[$j];
                    $d['remove'][$j - 1] = isset($d['remove'][$j])? $d['remove'][$j] : '';
                }
                $d['numChildren'] = --$n;
                $i--;
            }
        }
        $this->_context->setData($d);
    }
    
    function initControls() {
        $this->controlsInit = true;
        $this->processChildrenRemoval();
        $nc = $this->getNumChildren();
        for ($i = 0; $i < $nc; $i++) {
            $name = $this->replacePlaceholders($this->childNameTemplate, $i);
            $prototype = $this->fixPrototype($this->childPrototype, $i);
            $prototype['index'] = $i;
            $this->addControl($name, $prototype);
        }
        
        $submit = $this->getReloadCode();
        
        if (strlen($submit)) {
            $onclick = "var c = document.getElementById(".new Ae_Js_Val($this->_context->mapIdentifier('numChildren'))."); c.value = (parseInt(c.value) || 0) + 1; ".$submit."; return false;";
            
            if ($this->maxChildren === false || ($this->maxChildren > $this->getNumChildren())) {

                $addControl = array(
                    'class' => 'Ae_Form_Control_Button',
                    'buttonType' => 'button',
                    'htmlAttribs' => array('onclick' => $onclick),
                    'caption' => '+',
                    'dontGetDefaultFromModel' => true,
                );
                if ($this->addControlPrototype) Ae_Util::ms($addControl, $this->addControlPrototype);
                $this->addControl('addControl', $addControl);
                
            }
        }
        
    }
    
    protected function canRemove($child) {
        $res = ($child->index >= $this->minChildren) || $this->canRemoveBelowMinChildren;
        return $res;
    }
    
    protected function getRemoveControlLink($child, $content, $attribs) {
        return Ae_Util::mkElement('a', $content, $attribs);
    }
    
    function postProcessChildPresentation(& $html, Ae_Form_Control $child) {
        if ($child->name !== 'addControl' && $this->canRemove($child)) {
            $id = $this->_context->mapIdentifier('remove_'.$child->index);
            $h = Ae_Util::mkElement('input', false, array('type' => 'hidden', 'id' => $id, 'name' => $this->_context->mapParam(array('remove', $child->index)), 'value' => ''));
            $script = 'document.getElementById('.new Pm_Js_Val($id).').value = 1; '.$this->getReloadCode().' return false; ';
            $content = $this->removeControlText;
            $attribs = Ae_Util::m(array('href' => '#', 'class' => 'removeControl', 'onclick' => $script), $this->removeControlAttribs);
            $a = $this->getRemoveControlLink($child, $content, $attribs);
            $html = $h.$a.$html;
        }
    }
    
    protected function postProcessPresentation(& $html) {
        $html = Ae_Util::mkElement('input', false, array(
            'type' => 'hidden', 
            'name' => $this->_context->mapParam('numChildren'), 
            'id' => $this->_context->mapIdentifier('numChildren'), 
            'value' => $this->getNumChildren(),
            
        )).$html;
    }
    
    function _doGetDefault() {
        return null;
    }

    
}