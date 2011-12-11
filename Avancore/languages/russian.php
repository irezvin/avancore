<?php

if (!defined ('AE_LANG_ENCODING')) define ('AE_LANG_ENCODING', 'windows-1251');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', 'Русский');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', 'Сортировка');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', 'Порядок');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', 'Вы не можете изменить порядок следования элементов списка, так как один или несколько элементов в настоящий момент редактируются');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', 'Сохранить порядок');

if (!defined ('AE_RECORD_NOT_SPECIFIED')) define ('AE_RECORD_NOT_SPECIFIED', 'Не указано название');
if (!defined ('AE_RECORD_ALREADY_EXISTS')) define ('AE_RECORD_ALREADY_EXISTS', 'Запись с именем \'%s\' уже существует');
if (!defined ('AE_FIELD_REQUIRED')) define ('AE_FIELD_REQUIRED', 'Поле \'%s\' обязательно для заполнения');
if (!defined ('AE_SAVE')) define ('AE_SAVE', 'Сохранить');
if (!defined ('AE_APPLY')) define ('AE_APPLY', 'Применить');
if (!defined ('AE_CLOSE')) define ('AE_CLOSE', 'Закрыть');
if (!defined ('AE_EDIT_RECORD')) define ('AE_EDIT_RECORD', 'Редактировать запись');
if (!defined ('AE_TITLE')) define ('AE_TITLE', 'Название');
if (!defined ('AE_TITLE_IN_FORM')) define ('AE_TITLE_IN_FORM', 'Название');
if (!defined ('AE_ORDER_IN_FORM')) define ('AE_ORDER_IN_FORM', 'Порядок');
if (!defined ('AE_PUBLISH_IN_FORM')) define ('AE_PUBLISH_IN_FORM', 'Публикация');
if (!defined ('AE_ID')) define ('AE_ID', 'ID');
if (!defined ('AE_SELECT_ELEMENTS_TO_PUBLISH')) define ('AE_SELECT_ELEMENTS_TO_PUBLISH', 'Пожалуйста, выберите элемент(ы) для публикации');
if (!defined ('AE_PUBLISH')) define ('AE_PUBLISH', 'Публик.');
if (!defined ('AE_SELECT_ELEMENTS_TO_PUBLISH')) define ('AE_SELECT_ELEMENTS_TO_PUBLISH', 'Пожалуйста, выберите элемент(ы) для снятия с публикации');
if (!defined ('AE_HIDE')) define ('AE_HIDE', 'Скрыть');
if (!defined ('AE_SELECT_ELEMENTS_TO_DELETE')) define ('AE_SELECT_ELEMENTS_TO_DELETE', 'Пожалуйста, выберите элемент(ы) для удаления');
if (!defined ('AE_DELETE_CONFIRM')) define ('AE_DELETE_CONFIRM', 'Вы действительно хотите удалить выбранные элемент(ы)?');
if (!defined ('AE_DELETE')) define ('AE_DELETE', 'Удалить');
if (!defined ('AE_SELECT_ELEMENTS_TO_EDIT')) define ('AE_SELECT_ELEMENTS_TO_EDIT', 'Пожалуйста, выберите элемент(ы) для редактирования');
if (!defined ('AE_EDIT')) define ('AE_EDIT', 'Править');
if (!defined ('AE_CREATE')) define ('AE_CREATE', 'Создать');
if (!defined ('AE_REQUIRED_FIELD')) define ('AE_REQUIRED_FIELD', '(Обязательное поле)');
if (!defined ('AE_YES')) define ('AE_YES', 'Да');
if (!defined ('AE_NO')) define ('AE_NO', 'Нет');

if (!defined ('AE_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AE_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a fields');
if (!defined ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a field');
if (!defined ('AE_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AE_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AE_VALIDATOR_MSGS')) define('AE_VALIDATOR_MSGS', 1);
if (!defined('AE_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AE_VALIDATOR_MSG_FIELDWITHCAPTION', 'поле \'[:caption]\'');
if (!defined('AE_VALIDATOR_MSG_FIELD')) define('AE_VALIDATOR_MSG_FIELD', 'это поле');
if (!defined('AE_VALIDATOR_MSG_REQUIRED')) define('AE_VALIDATOR_MSG_REQUIRED', '[:fld] является обязательным для заполнения');
if (!defined('AE_VALIDATOR_MSG_INTTYPE')) define('AE_VALIDATOR_MSG_INTTYPE', '[:fld] должно содержать целое число (пример: 1234)');
if (!defined('AE_VALIDATOR_MSG_FLOATTYPE')) define('AE_VALIDATOR_MSG_FLOATTYPE', '[:fld] должно содержать целое или десятичное число (пример: 1.234)');
if (!defined('AE_VALIDATOR_MSG_DATETYPE')) define('AE_VALIDATOR_MSG_DATETYPE', '[:fld] должно содержать корректную дату (пример: 23.12.1981 или 1981-12-23)');
if (!defined('AE_VALIDATOR_MSG_TIMETYPE')) define('AE_VALIDATOR_MSG_TIMETYPE', '[:fld] должно содержать корректное время (пример: 23:55)');
if (!defined('AE_VALIDATOR_MSG_DATETIMETYPE')) define('AE_VALIDATOR_MSG_DATETIMETYPE', '[:fld] должно содержать корректные дату и время (пример: 23.12.1981 23:55 или 23:55 1981-12-23, и т.д.)');
if (!defined('AE_VALIDATOR_MSG_LE')) define('AE_VALIDATOR_MSG_LE', '[:fld] должно содержать значение, не превышающее [:val]');
if (!defined('AE_VALIDATOR_MSG_GE')) define('AE_VALIDATOR_MSG_GE', '[:fld] должно содержать значение, не меньшее, чем [:val]');
if (!defined('AE_VALIDATOR_MSG_LT')) define('AE_VALIDATOR_MSG_LT', '[:fld] должно содержать значение меньшее, чем [:val]');
if (!defined('AE_VALIDATOR_MSG_GT')) define('AE_VALIDATOR_MSG_GT', '[:fld] должно содержать значение большее, чем [:val]');
if (!defined('AE_VALIDATOR_MSG_NZ')) define('AE_VALIDATOR_MSG_NZ', '[:fld] не может содержать нулевое значение');
if (!defined('AE_VALIDATOR_MSG_RX')) define('AE_VALIDATOR_MSG_RX', '[:fld] содержит некорректное значение');
if (!defined('AE_VALIDATOR_MSG_MAXLENGTH')) define('AE_VALIDATOR_MSG_MAXLENGTH', '[:fld] не должно быть длинее, чем [:val] символов');
if (!defined('AE_VALIDATOR_MSG_VALUELIST')) define('AE_VALIDATOR_MSG_VALUELIST', '[:fld] содержит значение, выходящее за рамки допустимого диапазона');
if (!defined('AE_VALIDATOR_MSG_FUTURE')) define('AE_VALIDATOR_MSG_FUTURE', '[:fld] не должно содержать дату, находящуюся в прошлом');
if (!defined('AE_VALIDATOR_MSG_PAST')) define('AE_VALIDATOR_MSG_PAST', '[:fld] не должно содержать дату, находящуюся в будущем');

if (!defined ('AE_ID_EMPTY_CAPTION')) define ('AE_ID_EMPTY_CAPTION', 'Id будет назначен при сохранении записи');

?>
