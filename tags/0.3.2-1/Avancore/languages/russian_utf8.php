<?php

if (!defined ('AC_LANG_ENCODING')) define ('AC_LANG_ENCODING', 'utf-8');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', 'Русский');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', 'Сортировка');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', 'Порядок');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', 'Вы не можете изменить порядок следования элементов списка, так как один или несколько элементов в настоящий момент редактируются');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', 'Сохранить порядок');

if (!defined ('AC_RECORD_NOT_SPECIFIED')) define ('AC_RECORD_NOT_SPECIFIED', 'Не указано название');
if (!defined ('AC_RECORD_ALREADY_EXISTS')) define ('AC_RECORD_ALREADY_EXISTS', 'Запись с именем \'%s\' уже существует');
if (!defined ('AC_FIELD_REQUIRED')) define ('AC_FIELD_REQUIRED', 'Поле \'%s\' обязательно для заполнения');
if (!defined ('AC_SAVE')) define ('AC_SAVE', 'Сохранить');
if (!defined ('AC_APPLY')) define ('AC_APPLY', 'Применить');
if (!defined ('AC_CLOSE')) define ('AC_CLOSE', 'Закрыть');
if (!defined ('AC_EDIT_RECORD')) define ('AC_EDIT_RECORD', 'Редактировать запись');
if (!defined ('AC_TITLE')) define ('AC_TITLE', 'Название');
if (!defined ('AC_TITLE_IN_FORM')) define ('AC_TITLE_IN_FORM', 'Название');
if (!defined ('AC_ORDER_IN_FORM')) define ('AC_ORDER_IN_FORM', 'Порядок');
if (!defined ('AC_PUBLISH_IN_FORM')) define ('AC_PUBLISH_IN_FORM', 'Публикация');
if (!defined ('AC_ID')) define ('AC_ID', 'ID');
if (!defined ('AC_SELECT_ELEMENTS_TO_PUBLISH')) define ('AC_SELECT_ELEMENTS_TO_PUBLISH', 'Пожалуйста, выберите элемент(ы) для публикации');
if (!defined ('AC_PUBLISH')) define ('AC_PUBLISH', 'Публик.');
if (!defined ('AC_SELECT_ELEMENTS_TO_UNPUBLISH')) define ('AC_SELECT_ELEMENTS_TO_UNPUBLISH', 'Пожалуйста, выберите элемент(ы) для снятия с публикации');
if (!defined ('AC_HIDE')) define ('AC_HIDE', 'Скрыть');
if (!defined ('AC_SELECT_ELEMENTS_TO_DELETE')) define ('AC_SELECT_ELEMENTS_TO_DELETE', 'Пожалуйста, выберите элемент(ы) для удаления');
if (!defined ('AC_DELETE_CONFIRM')) define ('AC_DELETE_CONFIRM', 'Вы действительно хотите удалить выбранные элемент(ы)?');
if (!defined ('AC_DELETE')) define ('AC_DELETE', 'Удалить');
if (!defined ('AC_SELECT_ELEMENTS_TO_EDIT')) define ('AC_SELECT_ELEMENTS_TO_EDIT', 'Пожалуйста, выберите элемент(ы) для редактирования');
if (!defined ('AC_EDIT')) define ('AC_EDIT', 'Править');
if (!defined ('AC_CREATE')) define ('AC_CREATE', 'Создать');
if (!defined ('AC_REQUIRED_FIELD')) define ('AC_REQUIRED_FIELD', '(Обязательное поле)');
if (!defined ('AC_YES')) define ('AC_YES', 'Да');
if (!defined ('AC_NO')) define ('AC_NO', 'Нет');

if (!defined ('AC_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AC_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a fields');
if (!defined ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a field');
if (!defined ('AC_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AC_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AC_VALIDATOR_MSGS')) define('AC_VALIDATOR_MSGS', 1);
if (!defined('AC_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AC_VALIDATOR_MSG_FIELDWITHCAPTION', 'поле \'[:caption]\'');
if (!defined('AC_VALIDATOR_MSG_FIELD')) define('AC_VALIDATOR_MSG_FIELD', 'это поле');
if (!defined('AC_VALIDATOR_MSG_REQUIRED')) define('AC_VALIDATOR_MSG_REQUIRED', '[:fld] является обязательным для заполнения');
if (!defined('AC_VALIDATOR_MSG_INTTYPE')) define('AC_VALIDATOR_MSG_INTTYPE', '[:fld] должно содержать целое число (пример: 1234)');
if (!defined('AC_VALIDATOR_MSG_FLOATTYPE')) define('AC_VALIDATOR_MSG_FLOATTYPE', '[:fld] должно содержать целое или десятичное число (пример: 1.234)');
if (!defined('AC_VALIDATOR_MSG_DATETYPE')) define('AC_VALIDATOR_MSG_DATETYPE', '[:fld] должно содержать корректную дату (пример: 23.12.1981 или 1981-12-23)');
if (!defined('AC_VALIDATOR_MSG_TIMETYPE')) define('AC_VALIDATOR_MSG_TIMETYPE', '[:fld] должно содержать корректное время (пример: 23:55)');
if (!defined('AC_VALIDATOR_MSG_DATETIMETYPE')) define('AC_VALIDATOR_MSG_DATETIMETYPE', '[:fld] должно содержать корректные дату и время (пример: 23.12.1981 23:55 или 23:55 1981-12-23, и т.д.)');
if (!defined('AC_VALIDATOR_MSG_LE')) define('AC_VALIDATOR_MSG_LE', '[:fld] должно содержать значение, не превышающее [:val]');
if (!defined('AC_VALIDATOR_MSG_GE')) define('AC_VALIDATOR_MSG_GE', '[:fld] должно содержать значение, не меньшее, чем [:val]');
if (!defined('AC_VALIDATOR_MSG_LT')) define('AC_VALIDATOR_MSG_LT', '[:fld] должно содержать значение меньшее, чем [:val]');
if (!defined('AC_VALIDATOR_MSG_GT')) define('AC_VALIDATOR_MSG_GT', '[:fld] должно содержать значение большее, чем [:val]');
if (!defined('AC_VALIDATOR_MSG_NZ')) define('AC_VALIDATOR_MSG_NZ', '[:fld] не может содержать нулевое значение');
if (!defined('AC_VALIDATOR_MSG_RX')) define('AC_VALIDATOR_MSG_RX', '[:fld] содержит некорректное значение');
if (!defined('AC_VALIDATOR_MSG_MAXLENGTH')) define('AC_VALIDATOR_MSG_MAXLENGTH', '[:fld] не должно быть длинее, чем [:val] символов');
if (!defined('AC_VALIDATOR_MSG_VALUELIST')) define('AC_VALIDATOR_MSG_VALUELIST', '[:fld] содержит значение, выходящее за рамки допустимого диапазона');
if (!defined('AC_VALIDATOR_MSG_FUTURE')) define('AC_VALIDATOR_MSG_FUTURE', '[:fld] не должно содержать дату, находящуюся в прошлом');
if (!defined('AC_VALIDATOR_MSG_PAST')) define('AC_VALIDATOR_MSG_PAST', '[:fld] не должно содержать дату, находящуюся в будущем');

if (!defined('AC_ADMIN_PAGINATION_PREV_N_PAGES')) define ('AC_ADMIN_PAGINATION_PREV_N_PAGES', '&lt;&lt; Пред. %d стр.');
if (!defined('AC_ADMIN_PAGINATION_NEXT_N_PAGES')) define ('AC_ADMIN_PAGINATION_NEXT_N_PAGES', 'След. %d стр. &gt;&gt;');
if (!defined('AC_ADMIN_PAGINATION_PREV_PAGE')) define ('AC_ADMIN_PAGINATION_PREV_PAGE', '&lt; Пред. стр. ');
if (!defined('AC_ADMIN_PAGINATION_NEXT_PAGE')) define ('AC_ADMIN_PAGINATION_NEXT_PAGE', 'След. стр. &gt;');
if (!defined('AC_ADMIN_PAGINATION_SHOWING')) define ('AC_ADMIN_PAGINATION_SHOWING', 'Отображено');
if (!defined('AC_ADMIN_PAGINATION_OF')) define ('AC_ADMIN_PAGINATION_OF', 'из');
if (!defined('AC_ADMIN_PAGINATION_SHOW_QTY')) define ('AC_ADMIN_PAGINATION_SHOW_QTY', 'Показать ');
if (!defined('AC_ADMIN_PAGINATION_RECORDS')) define ('AC_ADMIN_PAGINATION_RECORDS', 'записей');

if (!defined('AC_ID_EMPTY_CAPTION')) define('AC_ID_EMPTY_CAPTION', 'Id будет назначен при сохранении записи');

if (!defined('AC_ADMIN_CREATE_NEW_RECORD_CAPT')) define('AC_ADMIN_CREATE_NEW_RECORD_CAPT', 'Создать');
if (!defined('AC_ADMIN_CREATE_NEW_RECORD_DESCR')) define('AC_ADMIN_CREATE_NEW_RECORD_DESCR', 'Создать новую запись');
if (!defined('AC_ADMIN_EDIT_RECORD_CAPT')) define('AC_ADMIN_EDIT_RECORD_CAPT', 'Изменить');
if (!defined('AC_ADMIN_EDIT_RECORD_DESCR')) define('AC_ADMIN_EDIT_RECORD_DESCR', 'Изменить выбранную запись');
if (!defined('AC_ADMIN_DELETE_RECORD_CAPT')) define('AC_ADMIN_DELETE_RECORD_CAPT', 'Удалить');
if (!defined('AC_ADMIN_DELETE_RECORD_DESCR')) define('AC_ADMIN_DELETE_RECORD_DESCR', 'Удалить одну или несколько выбранных записей');
if (!defined('AC_ADMIN_DELETE_RECORD_CONFIRM')) define('AC_ADMIN_DELETE_RECORD_CONFIRM', 'Удалить выбранные записи?');
if (!defined('AC_ADMIN_APPLY_CAPT')) define('AC_ADMIN_APPLY_CAPT', 'Применить');
if (!defined('AC_ADMIN_APPLY_DESCR')) define('AC_ADMIN_APPLY_DESCR', 'Применить изменения');
if (!defined('AC_ADMIN_SAVE_CAPT')) define('AC_ADMIN_SAVE_CAPT', 'Сохранить');
if (!defined('AC_ADMIN_SAVE_DESCR')) define('AC_ADMIN_SAVE_DESCR', 'Сохранить изменения и вернуться из режима формы');
if (!defined('AC_ADMIN_SAVE_ADD_CAPT')) define('AC_ADMIN_SAVE_ADD_CAPT', 'Сохр+Созд');
if (!defined('AC_ADMIN_SAVE_ADD_DESCR')) define('AC_ADMIN_SAVE_ADD_DESCR', 'Сохранить изменения и создать новую запись');
if (!defined('AC_ADMIN_CANCEL_CAPT')) define('AC_ADMIN_CANCEL_CAPT', 'Отменить');
if (!defined('AC_ADMIN_CANCEL_DESCR')) define('AC_ADMIN_CANCEL_DESCR', 'Отменить изменения и вернуться из режима формы');

?>