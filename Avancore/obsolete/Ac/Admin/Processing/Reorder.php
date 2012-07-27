<?php

// TODO: translate language strings
class Ac_Admin_Processing_Reorder extends Ac_Admin_Processing {
	
	const directionUp = 'up';
	const directionDown = 'down';
	
	var $fieldName = 'ordering';
	
	// Not implemented yet
	
//	var $orderUpMethod = false;
//	var $orderDownMethod = false;
//	var $orderParamMethod = false;
	
	var $direction = self::directionUp;
	
	function _doProcessRecord($record) {
		
		$value = $record->getField($this->fieldName);
		if (is_numeric($this->direction)) $value += $this->direction;
		elseif ($this->direction === self::directionUp) $value -= 1;
		elseif ($this->direction === self::directionDown) $value += 1;
		
		$record->setField($this->fieldName, $value);
		if ($record->store()) {
			$this->reportRecord($record, 'Запись перемещена', 'message', false, false);
		} else {
			$this->reportRecord($record, 'Не удалось переместить запись', 'error', false, false);
		}
	}
	
}
