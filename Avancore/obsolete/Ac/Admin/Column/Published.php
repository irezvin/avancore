<?php

class Ac_Admin_Column_Published extends Ac_Table_Column_Published {

    /**
     * @var Ac_Admin_Manager
     */
    var $manager = false;
    
    function getData($record, $rowNo, $fieldName) {
        $data = intval(Ac_Table_Column::getData($record, $rowNo, $this->fieldName));
        $img = $data? $this->getPublishedImg() : $this->getUnpublishedImg();
        $alt = $data? $this->getPublishedAlt() : $this->getUnpublishedAlt();
        
        $task = $data? $this->getUnpublishTask() : $this->getPublishTask();
        
        if ($data? !$this->canUnpublish($record) : !$this->canPublish($record)) $task = false;
        
        $jsCall = new Ac_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
            $task,
            array($this->manager->getStrPk($record))
        ));
        
        $inner = 
                    "<img ".
                        Ac_Util::mkAttribs(array(
                            'src' => "$img", 
                            'width' => 12,
                            'height' => 12,
                            'border' => 0,
                            'alt' => $alt,
                        )).
                    "/> ";
        
        if (strlen($task))
            $res =  "<a ".
                        Ac_Util::mkAttribs(array(
                            'href' => "javascript: void(0);", 
                            'onclick' => "return {$jsCall};"
                        )).
                    "> ".$inner."</a>";
            else $res = $inner;
            
        return $res; 
    }
    
    function canPublish($record) {
        if (strlen($p = $this->getCanPublishProperty())) 
            $res = (bool) Ac_Accessor::getObjectProperty ($record, $p);
        else $res = true;
        return $res;
    }
    
    function canUnpublish($record) {
        if (strlen($p = $this->getCanUnpublishProperty())) 
            $res = (bool) Ac_Accessor::getObjectProperty ($record, $p);
        else $res = true;
        return $res;
    }
    
    function getCanPublishProperty() {
        return isset($this->settings['canPublishProperty'])? $this->settings['canPublishProperty'] : false;
    }
    
    function getCanUnpublishProperty() {
        return isset($this->settings['canUnpublishProperty'])? $this->settings['canUnpublishProperty'] : false;
    }
    
}