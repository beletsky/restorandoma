<?
################################################################################
#   
#   
#                     
#   Copyright (�) 2003
#                     
#   const.php         
#   ����������� �������� 
#                        
################################################################################

define ('EMAIL_HEADER',       'From: ......ru'."\n");

define ('CLOGIN_NO',               0);
define ('CLOGIN_ENTER_LOGIN',      1);
define ('CLOGIN_LOGIN_NOT_VALID',  2);
define ('CLOGIN_BAD_LOGIN_OR_PWD', 3);
define ('CLOGIN_OK',               4);
define ('CLOGIN_NEED_KEY',         5);
define ('CLOGIN_BAD_KEY',          6);
define ('CLOGIN_AUTH_REQ',         7);

define ('PATH_TO_PIC', 'pic/');
define ('PATH_TO_MENU', 'menu/');
define ('PATH_TO_ADMORDERS','orders/');
define ('CLIENT_ADM_PAGE', 'clients/clients_comp/');
define ('CLIENT_USER_ADM_PAGE', 'clients/userscards/');
define ('INDEX_PAGE','index');
define ('ADMIN_MAIL','info@arttech.ru');
define ('PRINT_ORD_PAGE','print_form.php');

define ('ERR_SEND_USER_QUESTION','��� ������ ������� ���������! �� �������� ����� � ��������� �����.');
define ('ERR_SEND_ADVERT_QUESTION','��� ������ ������� ���������! �� �������� � ���� � ��������� �����.');
define ('ERR_SEND_FEEDBACK','������� �������! ���� ������ ����� ����� ��� ���.');
define ('ERR_PAGE_NOT_ALLOWED','� ��� ������������ ���� ��� ��������� ������ ��������.');
define ('ERR_PAGE_NOT_ALLOWED2','������� �� ���� � �������������� ������ ������ � ������.');
define ('ERR_ADD_AMOUNT','������� ������� ����� ��� ���������� �����.');
define ('ERR_MENU_CONT','�� ������� ���� �� ��������� ����');
define ('ERR_NO_ORDER','������� �� ��������� ���� �� ����������!');
define ('ERR_ORDER_CONFIRMED','<H1>�������, ��� ����� �������� � ����� ����� ��������.</H1><P>�� ������ ��� �������� �� �������, ���� ����� ����� �� ��������� ����������.</P>');

$week_days_arr = array("","�����������","�������", "�����", "�������", "�������","�������","�����������");
$months_array_to = array("1" => "������","2" => "�������","3" => "�����","4" => "������","5" => "���","6" => "����","7" => "����","8" => "�������","9" => "��������","10" => "�������","11" => "������","12" => "�������");
$months_array = array("1" => "������","2" => "�������","3" => "����","4" => "������","5" => "���","6" => "����","7" => "����","8" => "������","9" => "��������","10" => "�������","11" => "������","12" => "�������");

?>