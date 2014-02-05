<?php

// TODO: translate language strings
class Ac_Admin_Processing_Switch extends Ac_Admin_Processing {
	
    var $canSwitchProperty = false;
    var $valueList = array();
	var $fieldName = false;
	
	function _doProcessRecord($record) {
        if (strlen($this->fieldName)) {
            $currValue = $record->getField($this->fieldName);
            if (strlen($this->canSwitchProperty) && !Ac_Accessor::getObjectProperty($record, $this->fieldName)) return;
            $pl = array_values($this->valueList);
            if (($idx = array_search($currValue, $pl)) !== false) {
                if ($idx >= (count($pl) - 1)) $idx = 0;
                else $idx++;
                $newValue = $pl[$idx];
            }
		}
		if ($newValue !== $currValue) {
			$record->setField($this->fieldName, $newValue);
			if ($record->store()) {
				$this->reportRecord($record, 'Запись '.($newValue? 'опубликована' : 'снята с публикации'), 'message', false, false);
			} else {
				$this->reportRecord($record, 'Не удалось '.($newValue? 'опубликовать' : 'снять с публикации').' запись', 'error', false, false);
			}
		}
	}
	
}
