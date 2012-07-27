<?php

class Ac_Admin_Column_Published extends Ac_Table_Column_Published {

    /**
     * @var Ac_Admin_Manager
     */
    var $manager = false;
    
    function getData(& $record, $rowNo) {
        $data = intval(Ac_Table_Column::getData($record, $rowNo, $this->fieldName));
        $img = $data? $this->getPublishedImg() : $this->getUnpublishedImg();
        $alt = $data? $this->getPublishedAlt() : $this->getUnpublishedAlt();
        $task = $data? $this->getUnpublishTask() : $this->getPublishTask();
        
        $jsCall = new Ac_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
            $task,
            array($this->manager->getStrPk($record))
        ));
        
        $res =  "<a ".
                    Ac_Util::mkAttribs(array(
                        'href' => "javascript: void(0);", 
                        'onclick' => "return {$jsCall};"
                    )).
                "> ".
                    "<img ".
                        Ac_Util::mkAttribs(array(
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