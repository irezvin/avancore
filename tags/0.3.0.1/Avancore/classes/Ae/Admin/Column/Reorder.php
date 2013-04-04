<?php

class Ae_Admin_Column_Reorder extends Ae_Table_Column_Reorder {

    /**
     * @var Ae_Admin_Manager
     */
    var $manager = false;
    
    /**
     * Renders (echo's) column cell
     */
    function showCell(& $record, $rowNo) {
        
        $upJavascript = false;
        $downJavascript = false;

        $spk = $this->manager->getStrPk($record);
        
        if ($canUp = $this->canOrderUp($record, $rowNo)) {
            $upJavascript = 'return '.(new Pm_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
                $this->getOrderUpTask(),
                array($spk)
            ))).';';
        }
        
        if ($canDown = $this->canOrderDown($record, $rowNo)) {
            $downJavascript = 'return '.(new Pm_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
                $this->getOrderDownTask(),
                array($spk)
            ))).';';
        }
        
        echo "<td ".Ae_Util::mkAttribs($this->getCellAttribs()).">".$this->orderUpIcon($canUp, $upJavascript)."</td>";
        echo "<td ".Ae_Util::mkAttribs($this->getCellAttribs()).">".$this->orderDownIcon($canDown, $downJavascript)."</td>";
    }
    
    function orderUpIcon($condition=true, $javascript, $alt='Move up' ) {
        if ($condition) {
            return '<a href="#reorder" onClick="'.$javascript.'" title="'.$alt.'">
                <img src="images/uparrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }
    
    function orderDownIcon($condition=true, $javascript, $alt='Move down' ) {
        if ($condition) {
            return '<a href="#reorder" onClick="'.$javascript.'" title="'.$alt.'">
                <img src="images/downarrow.png" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }
    
}