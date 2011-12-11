<?php

if (!defined ('AE_LANG_ENCODING')) define ('AE_LANG_ENCODING', 'utf-8');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', 'English');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', 'Ordering');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', 'Reorder');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', 'You cannot reorder items since one or more of them are being currently edited');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', 'Save Order');

if (!defined ('AE_RECORD_NOT_SPECIFIED')) define ('AE_RECORD_NOT_SPECIFIED', 'Title must be provided');
if (!defined ('AE_RECORD_ALREADY_EXISTS')) define ('AE_RECORD_ALREADY_EXISTS', 'Record with title \'%s\' already exists');
if (!defined ('AE_FIELD_REQUIRED')) define ('AE_FIELD_REQUIRED', 'Field \'%s\' is required');
if (!defined ('AE_SAVE')) define ('AE_SAVE', 'Save');
if (!defined ('AE_APPLY')) define ('AE_APPLY', 'Apply');
if (!defined ('AE_CLOSE')) define ('AE_CLOSE', 'Close');
if (!defined ('AE_EDIT_RECORD')) define ('AE_EDIT_RECORD', 'Edit record');
if (!defined ('AE_TITLE')) define ('AE_TITLE', 'Title');
if (!defined ('AE_TITLE_IN_FORM')) define ('AE_TITLE_IN_FORM', 'Title');
if (!defined ('AE_ORDER_IN_FORM')) define ('AE_ORDER_IN_FORM', 'Ordering');
if (!defined ('AE_PUBLISH_IN_FORM')) define ('AE_PUBLISH_IN_FORM', 'Publishing');
if (!defined ('AE_ID')) define ('AE_ID', 'ID');
if (!defined ('AE_SELECT_ELEMENTS_TO_PUBLISH')) define ('AE_SELECT_ELEMENTS_TO_PUBLISH', 'Please, select element(s) to publish');
if (!defined ('AE_PUBLISH')) define ('AE_PUBLISH', 'Publish');
if (!defined ('AE_SELECT_ELEMENTS_TO_UNPUBLISH')) define ('AE_SELECT_ELEMENTS_TO_UNPUBLISH', 'Please, select element(s) to unpublish');
if (!defined ('AE_HIDE')) define ('AE_HIDE', 'Unpublish');
if (!defined ('AE_SELECT_ELEMENTS_TO_DELETE')) define ('AE_SELECT_ELEMENTS_TO_DELETE', 'Please, select element(s) to delete');
if (!defined ('AE_DELETE_CONFIRM')) define ('AE_DELETE_CONFIRM', 'Do you really want to delete selected element(s)?');
if (!defined ('AE_DELETE')) define ('AE_DELETE', 'Delete');
if (!defined ('AE_SELECT_ELEMENTS_TO_EDIT')) define ('AE_SELECT_ELEMENTS_TO_EDIT', 'Please, select element to edit');
if (!defined ('AE_EDIT')) define ('AE_EDIT', 'Edit');
if (!defined ('AE_CREATE')) define ('AE_CREATE', 'Create');
if (!defined ('AE_REQUIRED_FIELD')) define ('AE_REQUIRED_FIELD', '(Required Field)');
if (!defined ('AE_YES')) define ('AE_YES', 'Yes');
if (!defined ('AE_NO')) define ('AE_NO', 'No');

if (!defined ('AE_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AE_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a fields');
if (!defined ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a field');
if (!defined ('AE_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AE_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AE_VALIDATOR_MSGS')) define('AE_VALIDATOR_MSGS', 1);
if (!defined('AE_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AE_VALIDATOR_MSG_FIELDWITHCAPTION', 'field \'[:caption]\'');
if (!defined('AE_VALIDATOR_MSG_FIELD')) define('AE_VALIDATOR_MSG_FIELD', 'this field');
if (!defined('AE_VALIDATOR_MSG_REQUIRED')) define('AE_VALIDATOR_MSG_REQUIRED', '[:fld] is required to fill-in');
if (!defined('AE_VALIDATOR_MSG_INTTYPE')) define('AE_VALIDATOR_MSG_INTTYPE', '[:fld] should contain integer number (example: 1234)');
if (!defined('AE_VALIDATOR_MSG_FLOATTYPE')) define('AE_VALIDATOR_MSG_FLOATTYPE', '[:fld] should contain decimal number (example: 1.234)');
if (!defined('AE_VALIDATOR_MSG_DATETYPE')) define('AE_VALIDATOR_MSG_DATETYPE', '[:fld] should contain correct date (example: 23.12.1981 or 1981-12-23)');
if (!defined('AE_VALIDATOR_MSG_TIMETYPE')) define('AE_VALIDATOR_MSG_TIMETYPE', '[:fld] should contain correct time (example: 23:55)');
if (!defined('AE_VALIDATOR_MSG_DATETIMETYPE')) define('AE_VALIDATOR_MSG_DATETIMETYPE', '[:fld] should contain correct data and time (examples: 23.12.1981 23:55 or 23:55 1981-12-23 and so on)');
if (!defined('AE_VALIDATOR_MSG_LE')) define('AE_VALIDATOR_MSG_LE', '[:fld] should contain value not greater than [:val]');
if (!defined('AE_VALIDATOR_MSG_GE')) define('AE_VALIDATOR_MSG_GE', '[:fld] should not contain value less than [:val]');
if (!defined('AE_VALIDATOR_MSG_LT')) define('AE_VALIDATOR_MSG_LT', '[:fld] should contain value less than [:val]');
if (!defined('AE_VALIDATOR_MSG_GT')) define('AE_VALIDATOR_MSG_GT', '[:fld] should contain value greater than [:val]');
if (!defined('AE_VALIDATOR_MSG_NZ')) define('AE_VALIDATOR_MSG_NZ', '[:fld] should contain zero');
if (!defined('AE_VALIDATOR_MSG_RX')) define('AE_VALIDATOR_MSG_RX', '[:fld] contains incorrect value');
if (!defined('AE_VALIDATOR_MSG_VALUELIST')) define('AE_VALIDATOR_MSG_VALUELIST', '[:fld] contains value that isn\'t in allowed range');
if (!defined('AE_VALIDATOR_MSG_FUTURE')) define('AE_VALIDATOR_MSG_FUTURE', '[:fld] cannot contain date that is in the past');
if (!defined('AE_VALIDATOR_MSG_PAST')) define('AE_VALIDATOR_MSG_PAST', '[:fld] cannot contain date that is in the future');
if (!defined('AE_VALIDATOR_MSG_MAXLENGTH')) define('AE_VALIDATOR_MSG_MAXLENGTH', '[:fld] should not be longer than [:val] characters');

if (!defined ('AE_ID_EMPTY_CAPTION')) define ('AE_ID_EMPTY_CAPTION', 'Id will be assigned after the record will have been stored');

if (!defined('AE_ADMIN_CREATE_NEW_RECORD_CAPT')) define('AE_ADMIN_CREATE_NEW_RECORD_CAPT', 'New');
if (!defined('AE_ADMIN_CREATE_NEW_RECORD_DESCR')) define('AE_ADMIN_CREATE_NEW_RECORD_DESCR', 'Create new record');
if (!defined('AE_ADMIN_EDIT_RECORD_CAPT')) define('AE_ADMIN_EDIT_RECORD_CAPT', 'Edit');
if (!defined('AE_ADMIN_EDIT_RECORD_DESCR')) define('AE_ADMIN_EDIT_RECORD_DESCR', 'Edit selected record');
if (!defined('AE_ADMIN_DELETE_RECORD_CAPT')) define('AE_ADMIN_DELETE_RECORD_CAPT', 'Delete');
if (!defined('AE_ADMIN_DELETE_RECORD_DESCR')) define('AE_ADMIN_DELETE_RECORD_DESCR', 'Deletes selected record(s)');
if (!defined('AE_ADMIN_DELETE_RECORD_CONFIRM')) define('AE_ADMIN_DELETE_RECORD_CONFIRM', 'Delete selected record(s)?');
if (!defined('AE_ADMIN_APPLY_CAPT')) define('AE_ADMIN_APPLY_CAPT', 'Apply');
if (!defined('AE_ADMIN_APPLY_DESCR')) define('AE_ADMIN_APPLY_DESCR', 'Applies changes');
if (!defined('AE_ADMIN_SAVE_CAPT')) define('AE_ADMIN_SAVE_CAPT', 'Save');
if (!defined('AE_ADMIN_SAVE_DESCR')) define('AE_ADMIN_SAVE_DESCR', 'Saves changes and returns from the form');
if (!defined('AE_ADMIN_SAVE_ADD_CAPT')) define('AE_ADMIN_SAVE_ADD_CAPT', 'Save+Add');
if (!defined('AE_ADMIN_SAVE_ADD_DESCR')) define('AE_ADMIN_SAVE_ADD_DESCR', 'Saves changes and adds one more record');
if (!defined('AE_ADMIN_CANCEL_CAPT')) define('AE_ADMIN_CANCEL_CAPT', 'Cancel');
if (!defined('AE_ADMIN_CANCEL_DESCR')) define('AE_ADMIN_CANCEL_DESCR', 'Cancels changes and returns from the form');

