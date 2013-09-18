<?php

class Ae_Admin_Column_SaveOrder extends Ae_Table_Column_SaveOrder {

    /**
     * @var Ae_Admin_Manager
     */
    var $manager = false;
    var $paramName = 'ordering';
    
    function getTaskName() {
        if (isset($this->settings['taskName'])) $res = $this->settings['taskName'];
            else $res = 'saveOrder';
        return $res;
    }    
    
    function showHeader($rowCount, $rowNo) {
        
        $saveOrderCall = new Pm_Js_Call($this->manager->getJsManagerControllerRef().'.executeProcessing', array(
            $this->getTaskName(), true
        ));
        
        ?>
        <th <?php echo Ae_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)); ?> > 
            <?php echo $this->getTitle(); ?> 
        </th>
        <th <?php echo Ae_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)); ?> > <a href="#" onclick="<?php echo $saveOrderCall; ?>"><img src="images/filesave.png" border="0" width="16" height="16" alt=ACLT_SAVE_ORDER /></a></th>
        <?php
    }
    
    function showCell(& $record, $rowNo) {
        $fieldName = $this->manager->getContext()->mapParam(array('processingParams', $this->paramName, $this->manager->getStrPk($record)));
        $data = $this->getData($record, $rowNo, $this->fieldName);
        if (is_null($data)) $data = 0;
        ?>
        <td <?php echo Ae_Util::mkAttribs($this->getCellAttribs()); ?> >
            <input type="text" name="<?php echo $fieldName; ?>" size="5" value="<?php echo $data; ?>" class="text_area" style="text-align: center" />
        </td>
        <?php
    }
        
}