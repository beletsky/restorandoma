<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_customers.inc.php                                           #
#   ��������� � ������� ��������.                                              #
#                                                                              #
################################################################################
/*

  function catalog_add()      - ������� ��� ������ � ���������.
  function catalog_edit()       ���������� ����� ������.
  function catalog_del()        ��������� �� ���� ������ � ����������� - ������.

*/

function article_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _article_prepare($data);
    $db->Lock(array('dwArticles'));
    $r = _article_check($data,$is_new);
    if ($r == '') {
        $q  = $is_new ? 'insert into dwArticles set ':'update dwArticles set ';
        $q .= isset($data['PageTitle']) ? ' PageTitle = "' . addslashes($data['PageTitle']) . '",' : '' ;
        $q .= isset($data['Title']) ? ' Title = "' . addslashes($data['Title']) . '",' : '' ;
        $q .= isset($data['Announce']) ? ' Announce = "' .  addslashes($data['Announce']) . '",' : '' ;
        $q .= isset($data['PageCode']) ? ' PageCode = "' .  $data['PageCode'] . '",' : '' ;
        $q .= isset($data['Content']) ? ' Content = "' .  addslashes($data['Content']) . '" ' : '' ;
        $q .= $is_new ? '':' where IDArticle = ' . $data['IDArticle'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function article_del($id) {
    global $db;
    $r = '';
    if (!_is_article_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwArticles where IDArticle = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _article_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['Title'] == '') $r .= '������� ���.<br>';
    if ($data['Content'] == '') $r .= '������� ������ �������.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_article_exists($data['IDArticle'])) $r .= '������� �� �������.<br>';

    return $r;
}

function _article_prepare(&$data) {
    $data['Title'] = isset($data['Title']) ? trim($data['Title']) : '';
    $data['Content'] = isset($data['Content']) ? trim($data['Content']) : '';
    return $data;
}

function _is_article_exists($id) {
    return is_obj_exists($id, 'IDArticle', 'dwArticles');
}
?>