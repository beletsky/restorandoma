<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/init.inc.php                                                     #
#   ������������� �������� � ����������� ����������� �� ������ ��������        #
#   �������                                                                    #
#                                                                              #
################################################################################

require (PATH_TO_ROOT . "lib/psl_start.inc.php");
require (PATH_TO_ROOT . "inc/const.inc.php");
require (PATH_TO_ROOT . "lib/psl_admtbl.inc.php");

//date_default_timezone_set('Europe/Moscow');

$param_types = array('Text' => '�����', 'Varchar' => '������', 'Select' => '������', 'Int' => '�����');

$g_user->mAutoAcceptGroups = true;
$g_user->mAccessBackEnd = true;
//$g_user->mAccessFrontEnd = true;


$g_user->mCheckAccessBackEnd = true;   // ��������� ������� ������� � ������
$g_user->mCheckAccessFrontEnd = false;  // ��������� ������� ������� � �����

$g_user->Start();
$g_page->mUser = $g_user;

date_default_timezone_set('Europe/Moscow');

?>