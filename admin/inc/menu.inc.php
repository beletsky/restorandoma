<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/menu.inc.php                                                     #
#   ����� ����. ���������� � ���� top.inc.php, ������� �� ��������             #
#   ��������� ����������� �������.                                             #
#                                                                              #
################################################################################

$tpl = new Template();
$tpl->set_file('main', PATH_TO_ADMIN . 'inc/menu.ihtml');
$tpl->set_block('main', 'item', 'item_');
$tpl->set_block('main', 'sub', 'sub_');
$tpl->set_block('sub', 'subitem', 'subitem_');
$tpl->set_block('main', 'subsub', 'subsub_');
$tpl->set_block('subsub', 'subsubitem', 'subsubitem_');
$tpl->set_var('PATH_TO_ROOT', PATH_TO_ADMIN);
$tpl->set_var('item_', '');
$tpl->set_var('sub_', '');
$tpl->set_var('subsub_', '');

unset($submenu);
unset($subsubmenu);

// ������� ���� ������� ������
$menu = $g_page->GetMenuOf('');

foreach ($menu as $m) {
    $tpl->set_var('NAME', $m['MenuName']);
    $tpl->set_var('FOLDER', isset($m['PagePath']) ? $m['PagePath'] : '');
    $tpl->set_var('CLS', in_array(PAGE_CODE, $m['_codes']) ? ' class=selected' : '');
    $tpl->parse('item_', 'item', true);
    // �������� ������ ������ ������� ������
    if (in_array(PAGE_CODE, $m['_codes'])) $submenu = $g_page->GetMenuOf($m['MenuCode']);
}

// �������� ������� ������
if (isset($submenu) && is_array($submenu) && count($submenu)) {
    foreach ($submenu as $m) {
        $tpl->set_var('NAME', $m['MenuName']);
        $tpl->set_var('FOLDER', isset($m['PagePath']) ? $m['PagePath'] : '');
        $tpl->set_var('CLS', in_array(PAGE_CODE, $m['_codes']) ? ' class=selected' : '');
        $tpl->parse('subitem_', 'subitem', true);
        // �������� ������ ������ �������� ������
        if (in_array(PAGE_CODE, $m['_codes'])) $subsubmenu = $g_page->GetMenuOf($m['MenuCode']);
    }
    $tpl->parse('sub_', 'sub', false);
}
//                           print is_array($subsubmenu) ? 'true' : 'false';
// �������� �������� ������
if (isset($subsubmenu) && is_array($subsubmenu) && count($subsubmenu)) {
    foreach ($subsubmenu as $m) {
        $tpl->set_var('NAME', $m['MenuName']);
        $tpl->set_var('FOLDER', isset($m['PagePath']) ? $m['PagePath'] : '');
        $tpl->set_var('CLS', in_array(PAGE_CODE, $m['_codes']) ? ' class=selected' : '');
        $tpl->parse('subsubitem_', 'subsubitem', true);
    }
    $tpl->parse('subsub_', 'subsub', false);
}

$tpl->pparse('C', 'main', false);

?>