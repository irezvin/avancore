<?php

class Ac_Result_Placeholder_Template_Tag extends Ac_Result_Placeholder_Template {
   
    protected $tag = false;
    
    protected $content = false;

    protected $attribs = array();

    protected $targetAttrib = false;

    protected $keyAttrib = false;

    protected $tagContent = false;

    protected $isAttribs = false;

    protected $isContent = false;
    
    protected $iterate = false;
    
    protected $asIsKeys = array();
    
    protected function getStrings(Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        $strings = parent::getStrings($placeholder, $writer);
        if ($this->iterate) {
            $res = array();
            foreach ($strings as $key => $value) {
                if (in_array($key, $this->asIsKeys, true)) $res[$key] = $value;
                else $res[$key] = $this->mkTag($key, $value, $placeholder, $writer);
            }
        } else {
            if (!$this->isAttribs) $strings = implode($this->glue, $strings);
            $res = array($this->mkTag(false, $strings, $placeholder, $writer));
        }
        return $res;
    }
    
    protected function mkTag($key, $value, Ac_Result_Placeholder $placeholder, Ac_Result_Writer $writer) {
        if (!strlen($this->tag)) throw new Ac_E_InvalidUsage("\setTag() first");
        $tagName = $this->tag;
        $tagContent = $this->content;
        $tagAttribs = $this->attribs;
        if (is_array($value) && $this->isAttribs) Ac_Util::ms($tagAttribs, $value);
        if ($this->keyAttrib !== false) $tagAttribs[$this->keyAttrib] = $key;
        if ($this->isContent !== false) $tagContent = $this->isContent == 'key'? $key : $value;
        if ($this->targetAttrib !== false) $tagAttribs[$this->targetAttrib] = $value;
        return Ac_Util::mkElement($tagName, $tagContent, $tagAttribs);
    }
    
    function setIsContent($isContent) {
        if (!in_array($isContent, $a = array(true, false, 'key'))) {
            throw new Ac_E_InvalidCall("Invalid value '{$isContent}'; \$isContent can be TRUE, FALSE or 'key'");
        }
        $this->isContent = $isContent;
    }

    function getIsContent() {
        return $this->isContent;
    }    
    
    function setTag($tag) {
        $this->tag = $tag;
    }

    function getTag() {
        return $this->tag;
    }

    function setAttribs(array $attribs) {
        $this->attribs = $attribs;
    }

    function getAttribs() {
        return $this->attribs;
    }

    function setTargetAttrib($targetAttrib) {
        $this->targetAttrib = $targetAttrib;
    }

    function getTargetAttrib() {
        return $this->targetAttrib;
    }

    function setIsAttribs($isAttribs) {
        $this->isAttribs = $isAttribs;
    }

    function getIsAttribs() {
        return $this->isAttribs;
    }    

    function setTagContent($tagContent) {
        $this->tagContent = $tagContent;
    }

    function getTagContent() {
        return $this->tagContent;
    }
    
    function setIsPerItem($isPerItem) {
        $this->isPerItem = $isPerItem;
    }

    function getIsPerItem() {
        return $this->isPerItem;
    }

    function setIterate($iterate) {
        $this->iterate = $iterate;
    }

    function getIterate() {
        return $this->iterate;
    }    

    function setKeyAttrib($keyAttrib) {
        $this->keyAttrib = $keyAttrib;
    }

    function getKeyAttrib() {
        return $this->keyAttrib;
    }    
    
    function setAsIsKeys(array $asIsKeys) {
        $this->asIsKeys = $asIsKeys;
    }

    /**
     * @return array
     */
    function getAsIsKeys() {
        return $this->asIsKeys;
    }    
}