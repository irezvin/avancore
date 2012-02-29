<?php

// TODO: translate language strings
class Ae_Admin_Processing_SaveOrder extends Ae_Admin_Processing {
	
	var $fieldName = 'ordering';
	var $paramName = 'ordering';
	
	/**
	 * @var Ae_Admin_Manager
	 */
	var $manager = false;
	
	function getOrderingValue($record) {
		$res = $this->getContext()->getData(array($this->paramName, $this->manager->getStrPk($record)), false);
		if (is_numeric($res) && $res >= 1) $res = (int) $res;
			else $res = false;
		return $res;
	}
	
	function _doProcessRecord($record) {
		
		$value = $record->getField($this->fieldName);
		$newValue = $this->getOrderingValue($record);
		if ($newValue !== false && $newValue != $value) {
		$record->setField($this->fieldName, $newValue);
			if ($record->store()) {
				$this->reportRecord($record, 'Запись перемещена', 'message', false, false);
			} else {
				$this->reportRecord($record, 'Не удалось переместить запись', 'error', false, false);
			}
		}
		
	}
	
}
