<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_users.inc.php                                               #
#   ��������� � ������� �������������.                                         #
#                                                                              #
################################################################################
/*

  function user_adm_add()      - ������� ��� ������ � �������������� - ��������.
  function user_adm_edit()       ���������� ����� ������. 
  function user_adm_del()        ��������� �� ���� ������ � ����������� - ������.
  
  function get_group_by_code() - ������� ID_Group �� ���� ������

*/

function user_adm_add($data) {
    global $db;
    $r = _user_adm_check(_user_prepare($data), true);
    if ($r == '') {
        $q  = 'insert into dwUsers set';
        $q .= ' ID_Group = ' . $data['ID_Group'] . ',';
        $q .= ' UserLogin = "' . addslashes($data['UserLogin']) . '",';
        $q .= ' UserPwd = "' . addslashes($data['UserPwd']) . '",';
        $q .= ' UserDeleted = ' . $data['UserDeleted'];
        $db->Query($q);
    }
    return $r;
}

function user_adm_edit($data) {
    global $db;
    $r = '';
    $r = _user_adm_check(_user_prepare($data));
    if ($r == '') {
        $q  = 'update dwUsers set';
        $q .= ' ID_Group = ' . $data['ID_Group'] . ',';
        $q .= ' UserLogin = "' . addslashes($data['UserLogin']) . '",';
        $q .= ' UserPwd = "' . addslashes($data['UserPwd']) . '",';
        $q .= ' UserDeleted = ' . $data['UserDeleted'];
        $q .= ' where ID_User = ' . $data['ID_User'];
        $db->Query($q);
    }
    return $r;
}

function user_adm_del($id) {
    global $db;
    $r = '';
    if (!_is_user_exists($id)) $r .= '������������ �� ������.<br>';
    if ($r == '') {
        $q = 'delete from dwUsers where ID_User = ' . $id;
        $db->Query($q);
    }
    return $r;
}

// ���� isAdd, �� ��������� ������������� �����. � ����� �������, 
// ����� �������� �� ���. ������ � ����� ID � ���. �����. � ����� �������, �� ������ ID.
function _user_adm_check($data, $isAdd = false) {
    global $db, $g_user;
    $r = '';
    
    // �������� ������� �����
    if ($data['UserLogin'] == '') $r .= '������� �����.<br>';
    if ($data['UserPwd'] == '') $r .= '������� ������.<br>';
    // �������� ������������ �������� �����
    if ($r == '') {
        if (!$isAdd && !string_is_id($data['ID_User'])) $r .= '�������� ID.<br>';
        if (!string_is_login($data['UserLogin'])) $r .= '����������� ����� �������� ������������ �������.<br>';
        if (!string_is_login($data['UserPwd'])) $r .= '������ �������� ������������ �������.<br>';
        if (!string_is_id($data['ID_Group'])) $r .= '������ �� �������.<br>';
        if (!$isAdd && $data['ID_User'] == $g_user->Get('ID_User')) {
            if ($data['UserDeleted']) $r .= '�� �� ������ ������� ������ ����.<br>';
            if ($data['ID_Group'] != $g_user->Get('ID_Group')) $r .= '�� �� ������ �������� ���� ������.<br>';
        }
    }
    // ���� isAdd, �������� ���� �� ������������ � ����� �������, ����� �������� ���� �� ������������ � ����� �������, �� ������ ID
    if ($r == '') {
        $q = 'select ID_User from dwUsers where UserLogin = "' . $data['UserLogin'] . '"';
        if (!$isAdd) $q .= ' and ID_User <> ' . $data['ID_User'];
        $db->Query($q);
        if ($db->Nf()) $r .= '������������ � ����� ����������� ������� ��� ���������������.<br>';
    }
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_user_exists($data['ID_User'])) $r .= '������������ �� ������.<br>';
    
    return $r;
}

function _user_prepare(&$data) {
    $data['ID_Group']        = get_group_by_code($data['GroupCode']);
    $data['UserDeleted']     = isset($data['UserDeleted']) && $data['UserDeleted'] != '' ? 1 : 0;
    $data['UserTypeCode']    = isset($data['UserTypeCode']) ? trim($data['UserTypeCode']) : '';
    $data['UserLogin']       = isset($data['UserLogin']) ? trim($data['UserLogin']) : '';
    $data['UserPwd']         = isset($data['UserPwd']) ? trim($data['UserPwd']) : '';
    $data['UserSecretCode']  = isset($data['UserSecretCode']) ? trim($data['UserSecretCode']) : '';
    $data['UserIP']          = isset($data['UserIP']) ? trim($data['UserIP']) : '';
    $data['UserFName']       = isset($data['UserFName']) ? trim($data['UserFName']) : '';
    $data['UserLName']       = isset($data['UserLName']) ? trim($data['UserLName']) : '';
    $data['UserMName']       = isset($data['UserMName']) ? trim($data['UserMName']) : '';
    $data['UserEmail']       = isset($data['UserEmail']) ? trim($data['UserEmail']) : '';
    $data['UserPwdQuestion'] = isset($data['UserPwdQuestion']) ? trim($data['UserPwdQuestion']) : '';
    $data['UserPwdAnswer']   = isset($data['UserPwdAnswer']) ? trim($data['UserPwdAnswer']) : '';
    $data['UserSubscribed']  = isset($data['UserSubscribed']) ? trim($data['UserSubscribed']) : '';
    $data['UserContacts']    = isset($data['UserContacts']) ? trim($data['UserContacts']) : '';
    $data['UserPhoto']       = isset($data['UserPhoto']) ? trim($data['UserPhoto']) : '';
    $data['UserExpiryDate']  = isset($data['UserExpiryDate']) ? trim($data['UserExpiryDate']) : '';
    $data['UserVisible']     = isset($data['UserVisible']) && $data['UserVisible'] != '' ? 1 : 0;
    $data['UserIsVoter']     = isset($data['UserIsVoter']) && $data['UserIsVoter'] != '' ? 1 : 0;
    $data['UserRegDate']     = isset($data['UserRegDate']) ? trim($data['UserRegDate']) : '';
    $data['UserRegDbDate']   = isset($data['UserRegDbDate']) ? trim($data['UserRegDbDate']) : '';
    $data['UserDataChecked'] = isset($data['UserDataChecked']) && $data['UserDataChecked'] != '' ? 1 : 0;
    return $data;
}

function _is_user_exists($id) {
    return is_obj_exists($id, 'ID_User', 'dwUsers');
}

function get_group_by_code($groupCode) {
    global $db;
    $r = 0;
    if (string_is_login($groupCode)) {
        $db->Query('select ID_Group from dwGroups where GroupCode = "' . $groupCode . '"');
        if ($db->NextRecord()) $r = $db->F('ID_Group');
    }
    return $r;
}

?>