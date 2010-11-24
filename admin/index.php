<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/index.php                                                            #
#   ����������� ������� �����������������.                                     #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../');
define ('PATH_TO_ADMIN', './');
define ('PAGE_TITLE',    '�����������');
define ('PAGE_CODE',     'login');

require_once (PATH_TO_ADMIN . 'inc/init.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func.inc.php');

$auth_error = '';

define ('LOGIN_ERROR_ENTER_LOGIN',      '���������� ������� �����');
define ('LOGIN_ERROR_LOGIN_NOT_VALID',  '����� �������� ������������ �������');
define ('LOGIN_ERROR_BAD_LOGIN_OR_PWD', '������ ��������');

// �������� ���� ��� ������ �� ��������.  
setcookie('admin_login', '', 0);
setcookie('admin_pwd', '', 0);
  
$login = '';
$pwd = '';
if (isset($HTTP_POST_VARS['form']['login'])) $login = $HTTP_POST_VARS['form']['login'];
if (isset($HTTP_POST_VARS['form']['pwd']))   $pwd   = $HTTP_POST_VARS['form']['pwd'];

if ($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST') { 
    if ($login == '') {
        $auth_error = LOGIN_ERROR_ENTER_LOGIN;
    } elseif (!string_is_login($login)) {
        $auth_error = LOGIN_ERROR_LOGIN_NOT_VALID;
    } elseif (!$g_user->Login($login, $pwd)) {
        $auth_error = LOGIN_ERROR_BAD_LOGIN_OR_PWD;
    } else {
        setcookie('admin_login', $login, time() + 31536000);
        setcookie('admin_pwd', $pwd, time() + 31536000);
    }
}
  
require (PATH_TO_ADMIN . 'inc/top.inc.php');

if ((isset($auth_error) && $auth_error != '') || $HTTP_SERVER_VARS['REQUEST_METHOD'] != 'POST') $g_user->Logout();

$ret = '';
if (isset($HTTP_GET_VARS['ret'])) $ret = $HTTP_GET_VARS['ret'];

if ($g_user->IsAuthorized()) {
    $goto = 'Location: ' . ($ret != '' ? 'http://' . $HTTP_SERVER_VARS['HTTP_HOST'] . $ret : PATH_TO_ROOT . "admin/home/\n");
    header($goto);
    exit();
}
  

#  ������� ��������
  
$tpl = new Template;
$tpl->set_file('main', 'index.ihtml');
$tpl->set_var(array('TITLE'         => PAGE_TITLE,
                    'THIS_PAGE'     => $ret != '' ? $this_page."?ret=" . urlencode($ret) : $this_page,
                    'PATH_TO_ADMIN' => PATH_TO_ADMIN,
                    'LOGIN_ERROR'   => get_formatted_error($auth_error),
                    'OLD_LOGIN'     => $login));
$tpl->pparse('OUT', 'main');
  
require (PATH_TO_ADMIN . 'inc/bottom.inc.php');

?>