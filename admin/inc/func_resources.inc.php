<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_resources.inc.php                                           #
#   ��������� � ������� ��������.                                              #
#                                                                              #
################################################################################
/*

  function resource_add()      - ������� ��� ������ � ���������.
  function resource_edit()       ���������� ����� ������. 
  function resource_del()        ��������� �� ���� ������ � ����������� - ������.
  
*/

function resource_add($data) {
    global $db;
    $data = _resource_prepare($data);
    $db->Lock(array('dwResources'));
    $r = _resource_check($data, true);
    if ($r == '') {
        $q  = 'insert into dwResources set';
        $q .= ' ResourceName = "' . addslashes($data['ResourceName']) . '",';
        $q .= ' ResourceCode = "' . addslashes($data['ResourceCode']) . '"';
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function resource_edit($data) {
    global $db;
    $r = '';
    $data = _resource_prepare($data);
    $db->Lock(array('dwResources'));
    $r = _resource_check($data);
    if ($r == '') {
        $q  = 'update dwResources set';
        $q .= ' ResourceName = "' . addslashes($data['ResourceName']) . '",';
        $q .= ' ResourceCode = "' . addslashes($data['ResourceCode']) . '"';
        $q .= ' where ID_Resource = ' . $data['ID_Resource'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function resource_del($id) {
    global $db;
    $r = '';
    if (!_is_resource_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $db->Lock(array('dwPageResources', 'dwGroupResources', 'dwResources'));
        $q = 'delete from dwPageResources where ID_Resource = ' . $id;
        $db->Query($q);
        $q = 'delete from dwGroupResources where ID_Resource = ' . $id;
        $db->Query($q);
        $q = 'delete from dwResources where ID_Resource = ' . $id;
        $db->Query($q);
        $db->Unlock();
    }
    return $r;
}

function _resource_check($data, $isAdd = false) {
    global $db;
    $r = '';
    
    // �������� ������� �����
    if ($data['ResourceName'] == '') $r .= '������� ���.<br>';
    if ($data['ResourceCode'] == '') $r .= '������� ���.<br>';
    if ($data['ResourceCode'] != '' && !string_is_login($data['ResourceCode'])) $r .= '�������� ���.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_resource_exists($data['ID_Resource'])) $r .= '������ �� ������.<br>';
    
    return $r;
}

function _resource_prepare(&$data) {
    $data['ResourceName'] = isset($data['ResourceName']) ? trim($data['ResourceName']) : '';
    $data['ResourceCode'] = isset($data['ResourceCode']) ? trim($data['ResourceCode']) : '';
    return $data;
}

function _is_resource_exists($id) {
    return is_obj_exists($id, 'ID_Resource', 'dwResources');
}

?>