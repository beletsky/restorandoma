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

function quest_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _quest_prepare($data);
    $db->Lock(array('dwQuestAns'));
    $r = _quest_check($data,$is_new);
    if ($r == '') {
        $q  = $is_new ? 'insert into dwQuestAns set ':'update dwQuestAns set ';
        $q .= isset($data['PageTitle']) ? ' PageTitle = "' . addslashes($data['PageTitle']) . '",' : '' ;
        $q .= isset($data['Answer']) ? ' Answer = "' . addslashes($data['Answer']) . '",' : '' ;
        $q .= isset($data['Question']) ? ' Question = "' . addslashes($data['Question']) . '" ' : '' ;
        $q .= $is_new ? '':' where IDLine = ' . $data['IDLine'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function quest_del($id) {
    global $db;
    $r = '';
    if (!_is_quest_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwQuestAns where IDLine = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _quest_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['Question'] == '') $r .= '������� ����� �������.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_quest_exists($data['IDLine'])) $r .= '������� �� �������.<br>';
    return $r;
}

function _quest_prepare(&$data) {
    $data['Question'] = isset($data['Question']) ? trim($data['Question']) : '';
    return $data;
}

function _is_quest_exists($id) {
    return is_obj_exists($id, 'IDLine', 'dwQuestAns');
}
?>