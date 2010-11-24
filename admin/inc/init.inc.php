<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/init.inc.php                                                     #
#   Инициализация объектов и подключение необходимых на каждой странице        #
#   модулей                                                                    #
#                                                                              #
################################################################################

require (PATH_TO_ROOT . "lib/psl_start.inc.php");
require (PATH_TO_ROOT . "inc/const.inc.php");
require (PATH_TO_ROOT . "lib/psl_admtbl.inc.php");

//date_default_timezone_set('Europe/Moscow');

$param_types = array('Text' => 'Текст', 'Varchar' => 'Строка', 'Select' => 'Список', 'Int' => 'Число');

$g_user->mAutoAcceptGroups = true;
$g_user->mAccessBackEnd = true;
//$g_user->mAccessFrontEnd = true;


$g_user->mCheckAccessBackEnd = true;   // Проверять наличие доступа к админу
$g_user->mCheckAccessFrontEnd = false;  // Проверять наличие доступа к сайту

$g_user->Start();
$g_page->mUser = $g_user;

date_default_timezone_set('Europe/Moscow');

?>