<?php

class Ac_Admin_Column_Reorder extends Ac_Table_Column_Reorder {

    /**
     * @var Ac_Admin_Manager
     */
    var $manager = false;
    
    var $orderUpIcon = false;
    
    var $orderDownIcon = false;
    
    /**
     * Renders (echo's) column cell
     */
    function showCell($record, $rowNo) {
        
        $upJavascript = false;
        $downJavascript = false;

        $spk = $this->manager->getIdentifierOf($record);
        
        if ($canUp = $this->canOrderUp($record, $rowNo)) {
            $upJavascript = 'return '.(new Ac_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
                $this->getOrderUpTask(),
                array($spk)
            ))).';';
        }
        
        if ($canDown = $this->canOrderDown($record, $rowNo)) {
            $downJavascript = 'return '.(new Ac_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
                $this->getOrderDownTask(),
                array($spk)
            ))).';';
        }
        
        echo "<td ".Ac_Util::mkAttribs($this->getCellAttribs()).">".$this->orderUpIcon($canUp, $upJavascript)."</td>";
        echo "<td ".Ac_Util::mkAttribs($this->getCellAttribs()).">".$this->orderDownIcon($canDown, $downJavascript)."</td>";
    }
    
    function orderUpIcon($condition=true, $javascript, $alt='Move up' ) {
        $orderUpIcon = $this->orderUpIcon === false? $this->manager->getConfigService()->getImagePrefix().'/uparrow.png' : $this->orderUpIcon;
        if ($condition) {
            return '<a href="#reorder" onClick="'.$javascript.'" title="'.$alt.'">
                <img src="'.$orderUpIcon.'" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }
    
    function orderDownIcon($condition=true, $javascript, $alt='Move down' ) {
        $orderDownIcon = $this->orderDownIcon === false? $this->manager->getConfigService()->getImagePrefix().'/downarrow.png' : $this->orderDownIcon;
        if ($condition) {
            return '<a href="#reorder" onClick="'.$javascript.'" title="'.$alt.'">
                <img src="'.$orderDownIcon.'" width="12" height="12" border="0" alt="'.$alt.'" />
            </a>';
        } else {
            return '&nbsp;';
        }
    }
    
}