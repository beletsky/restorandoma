<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/top.inc.php                                                      #
#   ������������� �������� � ����������� ����������� �� ������ ��������        #
#   �������, �������� ���� ������� � ���� ��������                             #
#                                                                              #
################################################################################

require_once (PATH_TO_ADMIN . 'inc/init.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func.inc.php');

// ���� �� �� ���������� ��������, �� �������� ����������� �� ������������ � ����� �� �� ����� ���������� �� ���� ��������
$g_page->mThisPage = PAGE_CODE;

if (PAGE_CODE != 'login') {
	// ���� �� �����������, �� ������� ������ � �������, ������� �� �����
    if (!$g_user->IsAuthorized() && isset($HTTP_COOKIE_VARS['admin_login'])) {

    	if ($g_user->Login($HTTP_COOKIE_VARS['admin_login'], isset($HTTP_COOKIE_VARS['admin_pwd']) ? $HTTP_COOKIE_VARS['admin_pwd'] : '')) {

			$g_page->mUser = $g_user;

		}
    	
    }
    
    if (!$g_user->IsAuthorized()) {
    
        header('Location: ' . PATH_TO_ADMIN . '?ret=' . urlencode($HTTP_SERVER_VARS['REQUEST_URI']));
        exit();
    
    } elseif (!$g_page->IsPageAccessible()) {

        header('Location: ' . PATH_TO_ADMIN);
        exit();
        
    } else {
    
        $tpl = new Template;
        $tpl->set_file('main', PATH_TO_ADMIN . 'inc/top_title.ihtml');
        $tpl->set_var(array('TITLE' => PAGE_TITLE, 'PATH_TO_ADMIN' => PATH_TO_ADMIN));
        $tpl->pparse('OUT', 'main', false);
        require (PATH_TO_ADMIN . 'inc/menu.inc.php');
        
    }
}

?>