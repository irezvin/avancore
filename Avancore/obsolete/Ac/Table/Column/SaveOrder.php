<?php

class Ac_Table_Column_SaveOrder extends Ac_Table_Column {

    
    function getHeaderAttribs($rowCount, $rowNo = 1) {
        if (isset($this->settings['headerAttribs'])) $res = $this->settings['headerAttribs'];
            else $res = array('align' => 'center', 'width' => '1%');
        $res['rowspan'] = $this->getHeaderRowspan($rowCount, $rowNo);
        return $res;
    }
    
    function getCellAttribs() {
        if (isset($this->settings['cellAttribs'])) $res = $this->settings['cellAttribs'];
            else $res = array('colspan' => '2', 'align' => 'center', 'width' => '2%');
        return $res;
    }
    
    function getTitle() {
        if (isset($this->settings['title'])) $res = $this->settings['title'];
            else $res = ACLT_ORDER;
        return $res;
    }    
    
    function getTaskName() {
        if (isset($this->settings['taskName'])) $res = $this->settings['taskName'];
            else $res = 'saveorder';
        return $res;
    }    
    
    function showHeader($rowCount, $rowNo) {
        ?>
        <th <?php echo Ac_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)); ?> > 
            <?php echo $this->getTitle(); ?> 
            <script type="text/javascript" >
            /* <![CDATA[ */
                function saveOrderWithTask( n, taskName ) {
                    if (!taskName) taskName = "saveorder";
                    for ( var j = 0; j <= n; j++ ) {
                        box = eval( "document.adminForm.cb" + j );
                        if ( box ) {
                            if ( box.checked == false ) {
                                box.checked = true;
                            }
                        } else {
                            alert(ACLT_ORDERING_PROHIBITED);
                            return;
                        }
                    }
                    submitform(taskName);
                }
            /* ]]> */
            </script>
        </th>
        <th <?php echo Ac_Util::mkAttribs($this->getHeaderAttribs($rowCount, $rowNo)); ?> > <a href="javascript: saveOrderWithTask( <?php echo count( $this->_table->listRecords() )-1; ?>, '<?php echo addslashes($this->getTaskName()); ?>' )"><img src="images/filesave.png" border="0" width="16" height="16" alt=ACLT_SAVE_ORDER /></a></th>
        <?php
    }
    
    function showCell(& $record, $rowNo) {
        $data = $this->getData($record, $rowNo, $this->fieldName);
        if (is_null($data)) $data = 0;
        ?>
        <td <?php echo Ac_Util::mkAttribs($this->getCellAttribs()); ?> >
            <input type="text" name="order[]" size="5" value="<?php echo $data; ?>" class="text_area" style="text-align: center" />
        </td>
        <?php
    }

    function getFieldName() {
        if (isset($this->settings['fieldName'])) $res = $this->settings['fieldName'];
            else $res = 'ordering';
        return $res;
    }
}
?>