<?
################################################################################
#   
#   
#                     
#   Copyright (�) 2006
#                     
#   init.inc.php      
#   ������������� �������� � ����������� ����������� �� ������ ��������        
#   �������                                                                    
#                                                                              
################################################################################

require (PATH_TO_ROOT . "lib/psl_start.inc.php");
require (PATH_TO_ROOT . "inc/const.inc.php");
require (PATH_TO_ROOT . "inc/func.inc.php");
include_once (PATH_TO_ROOT . "inc/stat_page.inc.php");
require (PATH_TO_ROOT . "admin/inc/func_login_stat.inc.php");

$g_user->mAutoAcceptGroups = true;
$g_user->mAccessBackEnd = false;
$g_user->mAccessFrontEnd = true;

$g_user->mCheckAccessBackEnd = false;   // ��������� ������� ������� � ������
$g_user->mCheckAccessFrontEnd = true;  // ��������� ������� ������� � �����

//$g_user->Start();
$g_page->mUser = $g_user;

$this_page = $_SERVER['PHP_SELF'];

// �������� ��������
$action = '';
if (isset($_GET['a']))  $action = $_GET['a'];
if (isset($_POST['a'])) $action = $_POST['a'];

$form = array();
if (isset($_GET['form'])) $form = $_GET['form'];
if (isset($_POST['form'])) $form = $_POST['form'];

// ������� ����� �������� (������� �������� ��� ���� ���������� ret � ������� ��������, �� ���)
$ret = $_SERVER['REQUEST_URI'];
if (isset($_GET['ret']))  $ret = $_GET['ret'];
if (isset($_POST['ret'])) $ret = $_POST['ret'];

#��������� ��� ����������� ������� �� ���������
$PageCode = 'index';

if(isset($_GET['PageCode']) && $_GET['PageCode'] != '') $PageCode = $_GET['PageCode'];
if(isset($_POST['PageCode']) && $_POST['PageCode'] != '') $PageCode = $_POST['PageCode'];

define ('LOGIN_ERROR_ENTER_LOGIN',      '���������� ������� �����');
define ('LOGIN_ERROR_LOGIN_NOT_VALID',  '����� �������� ������������ �������');
define ('LOGIN_ERROR_BAD_LOGIN_OR_PWD', '������ ��������');
  
$login = '';
$pwd = '';
$auth_error = '';
if (isset($_POST['form']['login'])) $login = $_POST['form']['login'];
if (isset($_POST['form']['pwd']))   $pwd   = $_POST['form']['pwd'];
if (isset($_GET['form']['login'])) $login = $_GET['form']['login'];
if (isset($_GET['form']['pwd']))   $pwd   = $_GET['form']['pwd'];

$cookie_life_time = 60 * 60 * 24 * 365 * 5;
$log_user_timeout = 60 * 30;

if ($HTTP_SERVER_VARS['REQUEST_METHOD'] == 'POST' && isset($_POST['form']['login'])) { 
    if ($login == '') {
        $auth_error = LOGIN_ERROR_ENTER_LOGIN;
    } elseif (!string_is_login($login)) {
        $auth_error = LOGIN_ERROR_LOGIN_NOT_VALID;
    } elseif (!$g_user->Login($login, $pwd)) {
        $auth_error = LOGIN_ERROR_BAD_LOGIN_OR_PWD;
    } else {
        $g_page->mUser = $g_user;
        setcookie('user_login', $login, time() + $cookie_life_time, '/');
        setcookie('user_pwd', $pwd, time() + $cookie_life_time, '/');
        setcookie('user_last_time',time(), time() + $cookie_life_time, '/');
        log_user_login($login,time());
        $PageCode = 'user_index';
    }
} elseif (!$g_user->IsAuthorized() && isset($HTTP_COOKIE_VARS['user_login'])) {
    if ($g_user->Login($HTTP_COOKIE_VARS['user_login'], isset($HTTP_COOKIE_VARS['user_pwd']) ? $HTTP_COOKIE_VARS['user_pwd'] : '')) {
        $g_page->mUser = $g_user;
        // ���� � ����� ���� ����� ���������� ������ ������������ � ����� ������������ �������,
        // ������������� ����� ������������ �� ����.
        if (isset($HTTP_COOKIE_VARS['user_last_time']) && $HTTP_COOKIE_VARS['user_last_time'] + $log_user_timeout < time()) {
            log_user_login($HTTP_COOKIE_VARS['user_login'],time());
        }
        setcookie('user_login', $HTTP_COOKIE_VARS['user_login'], time() + $cookie_life_time, '/');
        setcookie('user_pwd', $HTTP_COOKIE_VARS['user_pwd'], time() + $cookie_life_time, '/');
        setcookie('user_last_time',time(), time() + $cookie_life_time, '/');
        if ($PageCode=='index') $PageCode = 'user_index';
    }
}


if($g_user->IsAuthorized() && $PageCode=='Logout') {
    $g_user->Logout();
    setcookie('user_login', '', 0);
    setcookie('user_pwd', '', 0);
    setcookie('user_last_time','',0);
}

// �������� Logout ������ ����� �� �������.
if($PageCode=='Logout') $PageCode = 'index';

// ���������, ���������� �� ����������� ��������.
// ���� ������� ���, ������ � �������.
if (!count(get_page_cont_by_id($PageCode))) {
    $PageCode = 'index';
    header("HTTP/1.1 404 Not Found");
}

$menu_types_arr = $g_options->GetOptionList('menu_types');
$oder_statuses_arr = $g_options->GetOptionList('order_statuses');

?>