<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_pages.inc.php                                               #
#   ��������� � ������� �������.                                               #
#                                                                              #
################################################################################
/*

  function page_add()      - ������� ��� ������ �� ����������.
  function page_edit()       ���������� ����� ������. 
  function page_del()        ��������� �� ���� ������ � ����������� - ������.
  
*/

function page_add($data) {
    global $db;
    $data = _page_prepare($data);
    $db->Lock(array('dwPages', 'dwPageResources', 'dwResources'));
    $r = _page_check($data, true);
    if ($r == '') {
        $q  = 'insert into dwPages set';
        $q .= ' PageName = "' . addslashes($data['PageName']) . '",';
        $q .= ' PagePath = "' . addslashes($data['PagePath']) . '",';
        $q .= ' PageCode = "' . addslashes($data['PageCode']) . '"';
        $db->Query($q);
        _save_page_resources($db->GetInsertId(), isset($data['resources']) ? $data['resources'] : array());
    }
    $db->Unlock();
    return $r;
}

function page_edit($data) {
    global $db;
    $r = '';
    $data = _page_prepare($data);
    $db->Lock(array('dwPages', 'dwPageResources', 'dwResources'));
    $r = _page_check($data);
    if ($r == '') {
        $q  = 'update dwPages set';
        $q .= ' PageName = "' . addslashes($data['PageName']) . '",';
        $q .= ' PagePath = "' . addslashes($data['PagePath']) . '",';
        $q .= ' PageCode = "' . addslashes($data['PageCode']) . '"';
        $q .= ' where ID_Page = ' . $data['ID_Page'];
        $db->Query($q);
        _save_page_resources($data['ID_Page'], isset($data['resources']) ? $data['resources'] : array());
    }
    $db->Unlock();
    return $r;
}

function page_del($id) {
    global $db;
    $r = '';
    if (!_is_page_exists($id)) $r .= '�������� �� �������.<br>';
    if ($r == '') {
        $db->Lock(array('dwPageResources', 'dwMenu', 'dwPages'));
        $db->Query('delete from dwPageResources where ID_Page = ' . $id);
        $db->Query('delete from dwMenu where ID_Page = ' . $id);
        $db->Query('delete from dwPages where ID_Page = ' . $id);
        $db->Unlock();
    }
    return $r;
}

function _page_check($data, $isAdd = false) {
    global $db;
    $r = '';
    
    // �������� ������� �����
    if ($data['PageName'] == '') $r .= '������� ���.<br>';
    if ($data['PageCode'] == '') $r .= '������� ���.<br>';
    if ($data['PageCode'] != '' && !string_is_login($data['PageCode'])) $r .= '�������� ���.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_page_exists($data['ID_Page'])) $r .= '�������� �� �������.<br>';
    // ��������, ���������� �� ������ � ����� �� �����
    if ($r == '') {
        $q = 'select count(*) from dwPages where PageCode = "' . addslashes($data['PageCode']) . '"';
        if (!$isAdd && string_is_id($data['ID_Page'])) $q .= ' and ID_Page <> ' . $data['ID_Page'];
        $db->Query($q);
        $db->NextRecord();
        if ($db->F(0)) $r .= '�������� � ����� ����� ��� ����������.<br>';
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

function _page_prepare(&$data) {
    $data['PageName'] = isset($data['PageName']) ? trim($data['PageName']) : '';
    $data['PageCode'] = isset($data['PageCode']) ? trim($data['PageCode']) : '';
    $data['PagePath'] = isset($data['PagePath']) ? trim($data['PagePath']) : '';
    return $data;
}

# ��������� ������� ��������
function _save_page_resources($id, $resources) {
    global $db;
    if (is_array($resources) && string_is_id($id)) {
        $db->Query('delete from dwPageResources where ID_Page = ' . $id);
        foreach ($resources as $k => $v) 
            if (string_is_id($k) && $v) 
                $db->Query('insert into dwPageResources set ID_Page = ' . $id . ', ID_Resource = ' . $k);
    }
}

function _is_page_exists($id) {
    return is_obj_exists($id, 'ID_Page', 'dwPages');
}

?>