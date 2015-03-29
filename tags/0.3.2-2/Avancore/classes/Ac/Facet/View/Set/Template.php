<?php

class Ac_Facet_View_Set_Template extends Ac_Facet_SetView {
    
    protected $template = false;
    
    protected $currentItem = false;
    
    protected $currentResponse = null;
    
    protected $itemTemplate = '{{ofItem}}';
    
    function setItemTemplate($itemTemplate) {
        $this->itemTemplate = $itemTemplate;
    }

    function getItemTemplate() {
        return $this->itemTemplate;
    }    

    function setTemplate($template) {
        $this->template = $template;
    }

    function getTemplate() {
        return $this->template;
    }
    
    function renderTemplate($template) {
        return preg_replace_callback('/\{\{([^\}]+)\}\}/', array($this, 'replacePlaceholder'), $template);
    }
    
    protected function replacePlaceholder($matches) {
        $args = explode(".", $matches[1]);
        $method = $args[0];
        return call_user_func_array(array($this, 'tpl'.ucfirst($method)), array_slice($args, 1));
    }
    
    function renderSet(Ac_Legacy_Controller_Response_Html $response) {
        $this->currentResponse = $response;
        echo $this->renderTemplate($this->template);
    }
    
    function tplItem($item = false, $prop = false) {
        $this->currentItem = $this->facetSet->getItem($item);
        if (strlen($prop)) return htmlspecialchars(Ac_Accessor::getObjectProperty($this->currentItem, $prop));
            else return $this->renderTemplate ($this->itemTemplate);
    }
    
    function tplOfItem($prop = false) {
        if (!strlen($prop)) {
            ob_start(); 
            $this->currentItem->render($this->currentResponse);
            $res = ob_get_clean();
        } else {
            $res = Ac_Accessor::getObjectProperty($this->currentItem, $prop);
        }
        return $res;
    }
    
    
    function tplSubmitButtonId() {
        return '-dummy-';
    }
    
    function tplValueDump() {
        ob_start();
        var_dump($this->facetSet->getValue());
        return ob_get_clean();
    }
    
    
}