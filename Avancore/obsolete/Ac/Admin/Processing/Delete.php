<?php

class Ac_Admin_Processing_Delete extends Ac_Admin_Processing {
    
    var $header = 'Deleting records';
    
    /**
     * @access protected
     * @param Ac_Model_Object $record
     */
    function _doProcessRecord(& $record) {
        if ($record->delete()) {
            $this->reportRecord($record, 'record deleted', 'message', false, false);
        } else {
            $this->reportRecord($record, 'can\'t delete record', 'error', false, false);
        }
    }
}

?>