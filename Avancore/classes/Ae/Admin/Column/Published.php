<?php

class Ae_Admin_Column_Published extends Ae_Table_Column_Published {

    /**
     * @var Ae_Admin_Manager
     */
    var $manager = false;
    
    function getData(& $record, $rowNo) {
        $data = intval(Ae_Table_Column::getData($record, $rowNo, $this->fieldName));
        $img = $data? $this->getPublishedImg() : $this->getUnpublishedImg();
        $alt = $data? $this->getPublishedAlt() : $this->getUnpublishedAlt();
        $task = $data? $this->getUnpublishTask() : $this->getPublishTask();
        
        $jsCall = new Ae_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
            $task,
            array($this->manager->getStrPk($record))
        ));
        
        $res =  "<a ".
                    Ae_Util::mkAttribs(array(
                        'href' => "javascript: void(0);", 
                        'onclick' => "return {$jsCall};"
                    )).
                "> ".
                    "<img ".
                        Ae_Util::mkAttribs(array(
                            'src' => "$img", 
                            'width' => 12,
                            'height' => 12,
                            'border' => 0,
                            'alt' => $alt,
                        )).
                    "/> ".
                "</a>";
        return $res; 
    }
    
}