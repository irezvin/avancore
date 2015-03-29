<?php

// TODO: translate language strings
class Ac_Admin_Processing_Publish extends Ac_Admin_Processing {
	
	const modePublish = 'publish';
	const modeUnpublish = 'unpublish';
	const modeToggle = 'toggle';
	
	var $mode = self::modePublish;
	var $header = 'Смена режима публикации';

	var $fieldName = 'published';
	
	function _doProcessRecord($record) {
		$currValue = $record->getField($this->fieldName);
		switch ($this->mode) {
			case self::modePublish: $newValue = 1; break;
			case self::modeUnpublish: $newValue = 0; break;
			case self::modeToggle: $newValue = !$currValue; break;
		}
		if ((bool) $newValue != (bool) $currValue) {
			$record->setField($this->fieldName, $newValue);
			if ($record->store()) {
				$this->reportRecord($record, 'Запись '.($newValue? 'опубликована' : 'снята с публикации'), 'message', false, false);
			} else {
				$this->reportRecord($record, 'Не удалось '.($newValue? 'опубликовать' : 'снять с публикации').' запись', 'error', false, false);
			}
		}
	}
	
}
