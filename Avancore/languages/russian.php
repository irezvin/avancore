<?php

if (!defined ('AE_LANG_ENCODING')) define ('AE_LANG_ENCODING', 'windows-1251');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', '�������');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', '����������');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', '�������');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', '�� �� ������ �������� ������� ���������� ��������� ������, ��� ��� ���� ��� ��������� ��������� � ��������� ������ �������������');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', '��������� �������');

if (!defined ('AE_RECORD_NOT_SPECIFIED')) define ('AE_RECORD_NOT_SPECIFIED', '�� ������� ��������');
if (!defined ('AE_RECORD_ALREADY_EXISTS')) define ('AE_RECORD_ALREADY_EXISTS', '������ � ������ \'%s\' ��� ����������');
if (!defined ('AE_FIELD_REQUIRED')) define ('AE_FIELD_REQUIRED', '���� \'%s\' ����������� ��� ����������');
if (!defined ('AE_SAVE')) define ('AE_SAVE', '���������');
if (!defined ('AE_APPLY')) define ('AE_APPLY', '���������');
if (!defined ('AE_CLOSE')) define ('AE_CLOSE', '�������');
if (!defined ('AE_EDIT_RECORD')) define ('AE_EDIT_RECORD', '������������� ������');
if (!defined ('AE_TITLE')) define ('AE_TITLE', '��������');
if (!defined ('AE_TITLE_IN_FORM')) define ('AE_TITLE_IN_FORM', '��������');
if (!defined ('AE_ORDER_IN_FORM')) define ('AE_ORDER_IN_FORM', '�������');
if (!defined ('AE_PUBLISH_IN_FORM')) define ('AE_PUBLISH_IN_FORM', '����������');
if (!defined ('AE_ID')) define ('AE_ID', 'ID');
if (!defined ('AE_SELECT_ELEMENTS_TO_PUBLISH')) define ('AE_SELECT_ELEMENTS_TO_PUBLISH', '����������, �������� �������(�) ��� ����������');
if (!defined ('AE_PUBLISH')) define ('AE_PUBLISH', '������.');
if (!defined ('AE_SELECT_ELEMENTS_TO_PUBLISH')) define ('AE_SELECT_ELEMENTS_TO_PUBLISH', '����������, �������� �������(�) ��� ������ � ����������');
if (!defined ('AE_HIDE')) define ('AE_HIDE', '������');
if (!defined ('AE_SELECT_ELEMENTS_TO_DELETE')) define ('AE_SELECT_ELEMENTS_TO_DELETE', '����������, �������� �������(�) ��� ��������');
if (!defined ('AE_DELETE_CONFIRM')) define ('AE_DELETE_CONFIRM', '�� ������������� ������ ������� ��������� �������(�)?');
if (!defined ('AE_DELETE')) define ('AE_DELETE', '�������');
if (!defined ('AE_SELECT_ELEMENTS_TO_EDIT')) define ('AE_SELECT_ELEMENTS_TO_EDIT', '����������, �������� �������(�) ��� ��������������');
if (!defined ('AE_EDIT')) define ('AE_EDIT', '�������');
if (!defined ('AE_CREATE')) define ('AE_CREATE', '�������');
if (!defined ('AE_REQUIRED_FIELD')) define ('AE_REQUIRED_FIELD', '(������������ ����)');
if (!defined ('AE_YES')) define ('AE_YES', '��');
if (!defined ('AE_NO')) define ('AE_NO', '���');

if (!defined ('AE_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AE_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a fields');
if (!defined ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AE_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a field');
if (!defined ('AE_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AE_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AE_VALIDATOR_MSGS')) define('AE_VALIDATOR_MSGS', 1);
if (!defined('AE_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AE_VALIDATOR_MSG_FIELDWITHCAPTION', '���� \'[:caption]\'');
if (!defined('AE_VALIDATOR_MSG_FIELD')) define('AE_VALIDATOR_MSG_FIELD', '��� ����');
if (!defined('AE_VALIDATOR_MSG_REQUIRED')) define('AE_VALIDATOR_MSG_REQUIRED', '[:fld] �������� ������������ ��� ����������');
if (!defined('AE_VALIDATOR_MSG_INTTYPE')) define('AE_VALIDATOR_MSG_INTTYPE', '[:fld] ������ ��������� ����� ����� (������: 1234)');
if (!defined('AE_VALIDATOR_MSG_FLOATTYPE')) define('AE_VALIDATOR_MSG_FLOATTYPE', '[:fld] ������ ��������� ����� ��� ���������� ����� (������: 1.234)');
if (!defined('AE_VALIDATOR_MSG_DATETYPE')) define('AE_VALIDATOR_MSG_DATETYPE', '[:fld] ������ ��������� ���������� ���� (������: 23.12.1981 ��� 1981-12-23)');
if (!defined('AE_VALIDATOR_MSG_TIMETYPE')) define('AE_VALIDATOR_MSG_TIMETYPE', '[:fld] ������ ��������� ���������� ����� (������: 23:55)');
if (!defined('AE_VALIDATOR_MSG_DATETIMETYPE')) define('AE_VALIDATOR_MSG_DATETIMETYPE', '[:fld] ������ ��������� ���������� ���� � ����� (������: 23.12.1981 23:55 ��� 23:55 1981-12-23, � �.�.)');
if (!defined('AE_VALIDATOR_MSG_LE')) define('AE_VALIDATOR_MSG_LE', '[:fld] ������ ��������� ��������, �� ����������� [:val]');
if (!defined('AE_VALIDATOR_MSG_GE')) define('AE_VALIDATOR_MSG_GE', '[:fld] ������ ��������� ��������, �� �������, ��� [:val]');
if (!defined('AE_VALIDATOR_MSG_LT')) define('AE_VALIDATOR_MSG_LT', '[:fld] ������ ��������� �������� �������, ��� [:val]');
if (!defined('AE_VALIDATOR_MSG_GT')) define('AE_VALIDATOR_MSG_GT', '[:fld] ������ ��������� �������� �������, ��� [:val]');
if (!defined('AE_VALIDATOR_MSG_NZ')) define('AE_VALIDATOR_MSG_NZ', '[:fld] �� ����� ��������� ������� ��������');
if (!defined('AE_VALIDATOR_MSG_RX')) define('AE_VALIDATOR_MSG_RX', '[:fld] �������� ������������ ��������');
if (!defined('AE_VALIDATOR_MSG_MAXLENGTH')) define('AE_VALIDATOR_MSG_MAXLENGTH', '[:fld] �� ������ ���� ������, ��� [:val] ��������');
if (!defined('AE_VALIDATOR_MSG_VALUELIST')) define('AE_VALIDATOR_MSG_VALUELIST', '[:fld] �������� ��������, ��������� �� ����� ����������� ���������');
if (!defined('AE_VALIDATOR_MSG_FUTURE')) define('AE_VALIDATOR_MSG_FUTURE', '[:fld] �� ������ ��������� ����, ����������� � �������');
if (!defined('AE_VALIDATOR_MSG_PAST')) define('AE_VALIDATOR_MSG_PAST', '[:fld] �� ������ ��������� ����, ����������� � �������');

if (!defined ('AE_ID_EMPTY_CAPTION')) define ('AE_ID_EMPTY_CAPTION', 'Id ����� �������� ��� ���������� ������');

?>
