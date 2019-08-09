<?php

if (!defined ('AC_LANG_ENCODING')) define ('AC_LANG_ENCODING', 'utf-8');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', 'English');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', 'Ordering');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', 'Reorder');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', 'You cannot reorder items since one or more of them are being currently edited');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', 'Save Order');

if (!defined ('AC_RECORD_NOT_SPECIFIED')) define ('AC_RECORD_NOT_SPECIFIED', 'Title must be provided');
if (!defined ('AC_RECORD_ALREADY_EXISTS')) define ('AC_RECORD_ALREADY_EXISTS', 'Record with title \'%s\' already exists');
if (!defined ('AC_FIELD_REQUIRED')) define ('AC_FIELD_REQUIRED', 'Field \'%s\' is required');
if (!defined ('AC_SAVE')) define ('AC_SAVE', 'Save');
if (!defined ('AC_APPLY')) define ('AC_APPLY', 'Apply');
if (!defined ('AC_CLOSE')) define ('AC_CLOSE', 'Close');
if (!defined ('AC_EDIT_RECORD')) define ('AC_EDIT_RECORD', 'Edit record');
if (!defined ('AC_TITLE')) define ('AC_TITLE', 'Title');
if (!defined ('AC_TITLE_IN_FORM')) define ('AC_TITLE_IN_FORM', 'Title');
if (!defined ('AC_ORDER_IN_FORM')) define ('AC_ORDER_IN_FORM', 'Ordering');
if (!defined ('AC_PUBLISH_IN_FORM')) define ('AC_PUBLISH_IN_FORM', 'Publishing');
if (!defined ('AC_ID')) define ('AC_ID', 'ID');
if (!defined ('AC_SELECT_ELEMENTS_TO_PUBLISH')) define ('AC_SELECT_ELEMENTS_TO_PUBLISH', 'Please, select element(s) to publish');
if (!defined ('AC_PUBLISH')) define ('AC_PUBLISH', 'Publish');
if (!defined ('AC_SELECT_ELEMENTS_TO_UNPUBLISH')) define ('AC_SELECT_ELEMENTS_TO_UNPUBLISH', 'Please, select element(s) to unpublish');
if (!defined ('AC_HIDE')) define ('AC_HIDE', 'Unpublish');
if (!defined ('AC_SELECT_ELEMENTS_TO_DELETE')) define ('AC_SELECT_ELEMENTS_TO_DELETE', 'Please, select element(s) to delete');
if (!defined ('AC_DELETE_CONFIRM')) define ('AC_DELETE_CONFIRM', 'Do you really want to delete selected element(s)?');
if (!defined ('AC_DELETE')) define ('AC_DELETE', 'Delete');
if (!defined ('AC_SELECT_ELEMENTS_TO_EDIT')) define ('AC_SELECT_ELEMENTS_TO_EDIT', 'Please, select element to edit');
if (!defined ('AC_EDIT')) define ('AC_EDIT', 'Edit');
if (!defined ('AC_CREATE')) define ('AC_CREATE', 'Create');
if (!defined ('AC_REQUIRED_FIELD')) define ('AC_REQUIRED_FIELD', '(Required Field)');
if (!defined ('AC_YES')) define ('AC_YES', 'Yes');
if (!defined ('AC_NO')) define ('AC_NO', 'No');

if (!defined ('AC_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AC_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a field');
if (!defined ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a fields');
if (!defined ('AC_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AC_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AC_VALIDATOR_MSGS')) define('AC_VALIDATOR_MSGS', 1);
if (!defined('AC_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AC_VALIDATOR_MSG_FIELDWITHCAPTION', 'field \'[:caption]\'');
if (!defined('AC_VALIDATOR_MSG_FIELD')) define('AC_VALIDATOR_MSG_FIELD', 'this field');
if (!defined('AC_VALIDATOR_MSG_REQUIRED')) define('AC_VALIDATOR_MSG_REQUIRED', '[:fld] is required to fill-in');
if (!defined('AC_VALIDATOR_MSG_INTTYPE')) define('AC_VALIDATOR_MSG_INTTYPE', '[:fld] should contain integer number (example: 1234)');
if (!defined('AC_VALIDATOR_MSG_FLOATTYPE')) define('AC_VALIDATOR_MSG_FLOATTYPE', '[:fld] should contain decimal number (example: 1.234)');
if (!defined('AC_VALIDATOR_MSG_DATETYPE')) define('AC_VALIDATOR_MSG_DATETYPE', '[:fld] should contain correct date (example: 23.12.1981 or 1981-12-23)');
if (!defined('AC_VALIDATOR_MSG_TIMETYPE')) define('AC_VALIDATOR_MSG_TIMETYPE', '[:fld] should contain correct time (example: 23:55)');
if (!defined('AC_VALIDATOR_MSG_DATETIMETYPE')) define('AC_VALIDATOR_MSG_DATETIMETYPE', '[:fld] should contain correct data and time (examples: 23.12.1981 23:55 or 23:55 1981-12-23 and so on)');
if (!defined('AC_VALIDATOR_MSG_LE')) define('AC_VALIDATOR_MSG_LE', '[:fld] should contain value not greater than [:val]');
if (!defined('AC_VALIDATOR_MSG_GE')) define('AC_VALIDATOR_MSG_GE', '[:fld] should not contain value less than [:val]');
if (!defined('AC_VALIDATOR_MSG_LT')) define('AC_VALIDATOR_MSG_LT', '[:fld] should contain value less than [:val]');
if (!defined('AC_VALIDATOR_MSG_GT')) define('AC_VALIDATOR_MSG_GT', '[:fld] should contain value greater than [:val]');
if (!defined('AC_VALIDATOR_MSG_NZ')) define('AC_VALIDATOR_MSG_NZ', '[:fld] should contain zero');
if (!defined('AC_VALIDATOR_MSG_RX')) define('AC_VALIDATOR_MSG_RX', '[:fld] contains incorrect value');
if (!defined('AC_VALIDATOR_MSG_VALUELIST')) define('AC_VALIDATOR_MSG_VALUELIST', '[:fld] contains value that isn\'t in allowed range');
if (!defined('AC_VALIDATOR_MSG_FUTURE')) define('AC_VALIDATOR_MSG_FUTURE', '[:fld] cannot contain date that is in the past');
if (!defined('AC_VALIDATOR_MSG_PAST')) define('AC_VALIDATOR_MSG_PAST', '[:fld] cannot contain date that is in the future');
if (!defined('AC_VALIDATOR_MSG_MAXLENGTH')) define('AC_VALIDATOR_MSG_MAXLENGTH', '[:fld] should not be longer than [:val] characters');

if (!defined ('AC_ID_EMPTY_CAPTION')) define ('AC_ID_EMPTY_CAPTION', 'Id will be assigned after the record will have been stored');

if (!defined('AC_ADMIN_CREATE_NEW_RECORD_CAPT')) define('AC_ADMIN_CREATE_NEW_RECORD_CAPT', 'New');
if (!defined('AC_ADMIN_CREATE_NEW_RECORD_DESCR')) define('AC_ADMIN_CREATE_NEW_RECORD_DESCR', 'Create new record');
if (!defined('AC_ADMIN_EDIT_RECORD_CAPT')) define('AC_ADMIN_EDIT_RECORD_CAPT', 'Edit');
if (!defined('AC_ADMIN_EDIT_RECORD_DESCR')) define('AC_ADMIN_EDIT_RECORD_DESCR', 'Edit selected record');
if (!defined('AC_ADMIN_DELETE_RECORD_CAPT')) define('AC_ADMIN_DELETE_RECORD_CAPT', 'Delete');
if (!defined('AC_ADMIN_DELETE_RECORD_DESCR')) define('AC_ADMIN_DELETE_RECORD_DESCR', 'Deletes selected record(s)');
if (!defined('AC_ADMIN_DELETE_RECORD_CONFIRM')) define('AC_ADMIN_DELETE_RECORD_CONFIRM', 'Delete selected record(s)?');
if (!defined('AC_ADMIN_APPLY_CAPT')) define('AC_ADMIN_APPLY_CAPT', 'Apply');
if (!defined('AC_ADMIN_APPLY_DESCR')) define('AC_ADMIN_APPLY_DESCR', 'Applies changes');
if (!defined('AC_ADMIN_SAVE_CAPT')) define('AC_ADMIN_SAVE_CAPT', 'Save');
if (!defined('AC_ADMIN_SAVE_DESCR')) define('AC_ADMIN_SAVE_DESCR', 'Saves changes and returns from the form');
if (!defined('AC_ADMIN_SAVE_ADD_CAPT')) define('AC_ADMIN_SAVE_ADD_CAPT', 'Save+Add');
if (!defined('AC_ADMIN_SAVE_ADD_DESCR')) define('AC_ADMIN_SAVE_ADD_DESCR', 'Saves changes and adds one more record');
if (!defined('AC_ADMIN_CANCEL_CAPT')) define('AC_ADMIN_CANCEL_CAPT', 'Cancel');
if (!defined('AC_ADMIN_CANCEL_DESCR')) define('AC_ADMIN_CANCEL_DESCR', 'Cancels changes and returns from the form');

