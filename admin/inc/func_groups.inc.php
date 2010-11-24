<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_groups.inc.php                                              #
#   ��������� � ������� ����� �������������.                                   #
#                                                                              #
################################################################################
/*

  function group_add()      - ������� ��� ������ � �������� �������������.
  function group_edit()       ���������� ����� ������. 
  function group_del()        ��������� �� ���� ������ � ����������� - ������.
  
*/

function group_add($data) {
    global $db;
    $data = _group_prepare($data);
    $db->Lock(array('dwGroups', 'dwGroupResources', 'dwResources'));
    $r = _group_check($data, true);
    if ($r == '') {
        $q  = 'insert into dwGroups set';
        $q .= ' GroupName = "' . addslashes($data['GroupName']) . '",';
        $q .= ' GroupCode = "' . addslashes($data['GroupCode']) . '",';
        $q .= ' GroupAccessBackEnd = ' . (isset($data['GroupAccessBackEnd']) && $data['GroupAccessBackEnd'] ? 1 : 0) . ',';
        $q .= ' GroupAccessFrontEnd = ' . (isset($data['GroupAccessFrontEnd']) && $data['GroupAccessFrontEnd'] ? 1 : 0);
        $db->Query($q);
        _save_group_resources($db->GetInsertId(), isset($data['resources']) ? $data['resources'] : array());
    }
    $db->Unlock();
    return $r;
}

function group_edit($data) {
    global $db;
    $r = '';
    $data = _group_prepare($data);
    $db->Lock(array('dwGroups', 'dwGroupResources', 'dwResources'));
    $r = _group_check($data);
    if ($r == '') {
        $q  = 'update dwGroups set';
        $q .= ' GroupName = "' . addslashes($data['GroupName']) . '",';
        $q .= ' GroupCode = "' . addslashes($data['GroupCode']) . '",';
        $q .= ' GroupAccessBackEnd = ' . (isset($data['GroupAccessBackEnd']) && $data['GroupAccessBackEnd'] ? 1 : 0) . ',';
        $q .= ' GroupAccessFrontEnd = ' . (isset($data['GroupAccessFrontEnd']) && $data['GroupAccessFrontEnd'] ? 1 : 0);
        $q .= ' where ID_Group = ' . $data['ID_Group'];
        $db->Query($q);
        _save_group_resources($data['ID_Group'], isset($data['resources']) ? $data['resources'] : array());
    }
    $db->Unlock();
    return $r;
}

function group_del($id) {
    global $db;
    $r = '';
    if (!_is_group_exists($id)) $r .= '������ �� �������.<br>';
    if ($r == '') {
        $db->Lock(array('dwGroupResources', 'dwGroups', 'dwUsers'));
        $db->Query('delete from dwGroupResources where ID_Group = ' . $id);
        $db->Query('delete from dwGroups where ID_Group = ' . $id);
        $db->Query('delete from dwUsers where ID_Group = ' . $id);
        $db->Unlock();
    }
    return $r;
}

function _group_check($data, $isAdd = false) {
    global $db;
    $r = '';
    
    // �������� ������� �����
    if ($data['GroupName'] == '') $r .= '������� ���.<br>';
    if ($data['GroupCode'] == '') $r .= '������� ���.<br>';
    if ($data['GroupCode'] != '' && !string_is_login($data['GroupCode'])) $r .= '�������� ���.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_group_exists($data['ID_Group'])) $r .= '������ �� �������.<br>';
    // ��������, ���������� �� ������ � ����� �� �����
    if ($r == '') {
        $q = 'select count(*) from dwGroups where GroupCode = "' . addslashes($data['GroupCode']) . '"';
        if (!$isAdd && string_is_id($data['ID_Group'])) $q .= ' and ID_Group <> ' . $data['ID_Group'];
        $db->Query($q);
        $db->NextRecord();
        if ($db->F(0)) $r .= '������ � ����� ����� ��� ����������.<br>';
    }
    // ������� ������ �������� � ��������, ���������� �� ��� ID_Resource, ���������� � $data['resources']
    if (isset($data['resources']) && is_array($data['resources']) && count($data['resources'])) {
        $resources = array();
        $db->Query('select ID_Resource from dwResources');
        while ($db->NextRecord()) $resources[] = $db->F(0);
        
        foreach ($data['resources'] as $id => $v)
            if (!string_is_id($id)) 
                $r .= '������ �������� ���������: �������� ID �������.<br>';
            elseif (!in_array($id, $resources))
                $q .= '������ �������� ���������: ������ � ID ' . $id . ' �� ������.<br>';
    }
    
    return $r;
}

function _group_prepare(&$data) {
    $data['GroupName'] = isset($data['GroupName']) ? trim($data['GroupName']) : '';
    $data['GroupCode'] = isset($data['GroupCode']) ? trim($data['GroupCode']) : '';
    return $data;
}

# ��������� ������� ������
function _save_group_resources($id, $resources) {
    global $db;
    if (is_array($resources) && string_is_id($id)) {
        $db->Query('delete from dwGroupResources where ID_Group = ' . $id);
        foreach ($resources as $k => $v) 
            if (string_is_id($k) && $v) 
                $db->Query('insert into dwGroupResources set ID_Group = ' . $id . ', ID_Resource = ' . $k);
    }
}

function _is_group_exists($id) {
    return is_obj_exists($id, 'ID_Group', 'dwGroups');
}

?>