<?php

if (!defined ('AC_LANG_ENCODING')) define ('AC_LANG_ENCODING', 'windows-1251');
if (!defined ('ACLT_LANG')) define ('ACLT_LANG', '�������');
if (!defined ('ACLT_ORDERING')) define ('ACLT_ORDERING', '����������');
if (!defined ('ACLT_ORDER')) define ('ACLT_ORDER', '�������');
if (!defined ('ACLT_ORDERING_PROHIBITED')) define ('ACLT_ORDERING_PROHIBITED', '�� �� ������ �������� ������� ���������� ��������� ������, ��� ��� ���� ��� ��������� ��������� � ��������� ������ �������������');
if (!defined ('ACLT_SAVE_ORDER')) define ('ACLT_SAVE_ORDER', '��������� �������');

if (!defined ('AC_RECORD_NOT_SPECIFIED')) define ('AC_RECORD_NOT_SPECIFIED', '�� ������� ��������');
if (!defined ('AC_RECORD_ALREADY_EXISTS')) define ('AC_RECORD_ALREADY_EXISTS', '������ � ������ \'%s\' ��� ����������');
if (!defined ('AC_FIELD_REQUIRED')) define ('AC_FIELD_REQUIRED', '���� \'%s\' ����������� ��� ����������');
if (!defined ('AC_SAVE')) define ('AC_SAVE', '���������');
if (!defined ('AC_APPLY')) define ('AC_APPLY', '���������');
if (!defined ('AC_CLOSE')) define ('AC_CLOSE', '�������');
if (!defined ('AC_EDIT_RECORD')) define ('AC_EDIT_RECORD', '������������� ������');
if (!defined ('AC_TITLE')) define ('AC_TITLE', '��������');
if (!defined ('AC_TITLE_IN_FORM')) define ('AC_TITLE_IN_FORM', '��������');
if (!defined ('AC_ORDER_IN_FORM')) define ('AC_ORDER_IN_FORM', '�������');
if (!defined ('AC_PUBLISH_IN_FORM')) define ('AC_PUBLISH_IN_FORM', '����������');
if (!defined ('AC_ID')) define ('AC_ID', 'ID');
if (!defined ('AC_SELECT_ELEMENTS_TO_PUBLISH')) define ('AC_SELECT_ELEMENTS_TO_PUBLISH', '����������, �������� �������(�) ��� ����������');
if (!defined ('AC_PUBLISH')) define ('AC_PUBLISH', '������.');
if (!defined ('AC_SELECT_ELEMENTS_TO_PUBLISH')) define ('AC_SELECT_ELEMENTS_TO_PUBLISH', '����������, �������� �������(�) ��� ������ � ����������');
if (!defined ('AC_HIDE')) define ('AC_HIDE', '������');
if (!defined ('AC_SELECT_ELEMENTS_TO_DELETE')) define ('AC_SELECT_ELEMENTS_TO_DELETE', '����������, �������� �������(�) ��� ��������');
if (!defined ('AC_DELETE_CONFIRM')) define ('AC_DELETE_CONFIRM', '�� ������������� ������ ������� ��������� �������(�)?');
if (!defined ('AC_DELETE')) define ('AC_DELETE', '�������');
if (!defined ('AC_SELECT_ELEMENTS_TO_EDIT')) define ('AC_SELECT_ELEMENTS_TO_EDIT', '����������, �������� �������(�) ��� ��������������');
if (!defined ('AC_EDIT')) define ('AC_EDIT', '�������');
if (!defined ('AC_CREATE')) define ('AC_CREATE', '�������');
if (!defined ('AC_REQUIRED_FIELD')) define ('AC_REQUIRED_FIELD', '(������������ ����)');
if (!defined ('AC_YES')) define ('AC_YES', '��');
if (!defined ('AC_NO')) define ('AC_NO', '���');

if (!defined ('AC_SUCH_VALUES_OF_FIELD_SINGLE')) define ('AC_SUCH_VALUES_OF_FIELD_SINGLE', 'such values of a fields');
if (!defined ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE')) define ('AC_SUCH_VALUES_OF_FIELD_MULTIPLE', 'such value of a field');
if (!defined ('AC_RECORD_BY_INDEX_ALREADY_EXISTS')) define ('AC_RECORD_BY_INDEX_ALREADY_EXISTS', 'Record with %s %s already exists in the database');

if (!defined('AC_VALIDATOR_MSGS')) define('AC_VALIDATOR_MSGS', 1);
if (!defined('AC_VALIDATOR_MSG_FIELDWITHCAPTION')) define('AC_VALIDATOR_MSG_FIELDWITHCAPTION', '���� \'[:caption]\'');
if (!defined('AC_VALIDATOR_MSG_FIELD')) define('AC_VALIDATOR_MSG_FIELD', '��� ����');
if (!defined('AC_VALIDATOR_MSG_REQUIRED')) define('AC_VALIDATOR_MSG_REQUIRED', '[:fld] �������� ������������ ��� ����������');
if (!defined('AC_VALIDATOR_MSG_INTTYPE')) define('AC_VALIDATOR_MSG_INTTYPE', '[:fld] ������ ��������� ����� ����� (������: 1234)');
if (!defined('AC_VALIDATOR_MSG_FLOATTYPE')) define('AC_VALIDATOR_MSG_FLOATTYPE', '[:fld] ������ ��������� ����� ��� ���������� ����� (������: 1.234)');
if (!defined('AC_VALIDATOR_MSG_DATETYPE')) define('AC_VALIDATOR_MSG_DATETYPE', '[:fld] ������ ��������� ���������� ���� (������: 23.12.1981 ��� 1981-12-23)');
if (!defined('AC_VALIDATOR_MSG_TIMETYPE')) define('AC_VALIDATOR_MSG_TIMETYPE', '[:fld] ������ ��������� ���������� ����� (������: 23:55)');
if (!defined('AC_VALIDATOR_MSG_DATETIMETYPE')) define('AC_VALIDATOR_MSG_DATETIMETYPE', '[:fld] ������ ��������� ���������� ���� � ����� (������: 23.12.1981 23:55 ��� 23:55 1981-12-23, � �.�.)');
if (!defined('AC_VALIDATOR_MSG_LE')) define('AC_VALIDATOR_MSG_LE', '[:fld] ������ ��������� ��������, �� ����������� [:val]');
if (!defined('AC_VALIDATOR_MSG_GE')) define('AC_VALIDATOR_MSG_GE', '[:fld] ������ ��������� ��������, �� �������, ��� [:val]');
if (!defined('AC_VALIDATOR_MSG_LT')) define('AC_VALIDATOR_MSG_LT', '[:fld] ������ ��������� �������� �������, ��� [:val]');
if (!defined('AC_VALIDATOR_MSG_GT')) define('AC_VALIDATOR_MSG_GT', '[:fld] ������ ��������� �������� �������, ��� [:val]');
if (!defined('AC_VALIDATOR_MSG_NZ')) define('AC_VALIDATOR_MSG_NZ', '[:fld] �� ����� ��������� ������� ��������');
if (!defined('AC_VALIDATOR_MSG_RX')) define('AC_VALIDATOR_MSG_RX', '[:fld] �������� ������������ ��������');
if (!defined('AC_VALIDATOR_MSG_MAXLENGTH')) define('AC_VALIDATOR_MSG_MAXLENGTH', '[:fld] �� ������ ���� ������, ��� [:val] ��������');
if (!defined('AC_VALIDATOR_MSG_VALUELIST')) define('AC_VALIDATOR_MSG_VALUELIST', '[:fld] �������� ��������, ��������� �� ����� ����������� ���������');
if (!defined('AC_VALIDATOR_MSG_FUTURE')) define('AC_VALIDATOR_MSG_FUTURE', '[:fld] �� ������ ��������� ����, ����������� � �������');
if (!defined('AC_VALIDATOR_MSG_PAST')) define('AC_VALIDATOR_MSG_PAST', '[:fld] �� ������ ��������� ����, ����������� � �������');

if (!defined ('AC_ID_EMPTY_CAPTION')) define ('AC_ID_EMPTY_CAPTION', 'Id ����� �������� ��� ���������� ������');

?>
