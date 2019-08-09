<?php

class Ac_Facet_View_Item_Text extends Ac_Facet_ItemView {
    
    protected $htmlAttribs = array();

    protected $dummyCaption = false;
    
    function setDummyCaption($dummyCaption) {
        $this->dummyCaption = $dummyCaption;
    }

    function getDummyCaption() {
        return $this->dummyCaption !== false? $this->dummyCaption : $this->item->getCaption().'...';
    }
    
    function renderItem(Ac_Legacy_Controller_Response_Html $response) {
        
        $a = array(
            'name' => $this->getHtmlName(),
            'onchange' => 'if (this.form) this.form.submit();',
            'value' => $this->getItem()->getValue()
        );
        Ac_Util::ms($a, $this->htmlAttribs);
        echo Ac_Util::mkElement('input', false, $a);
    }
    
    function getCaption($item) {
        $res = $item['title'];
        if (isset($item['count'])) $res .= ' ('.$item['count'].')';
        return $res;
    }
   
    function setHtmlAttribs(array $htmlAttribs) {
        $this->htmlAttribs = $htmlAttribs;
    }

    function getHtmlAttribs() {
        return $this->htmlAttribs;
    }    
    
}