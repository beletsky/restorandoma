<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2006, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_clientcomp.inc.php                                          #
#   ��������� � ������� ��������(��������).                                    #
#                                                                              #
################################################################################
/*

  function catalog_edit()       ���������� ����� ������.
  function catalog_del()        ��������� �� ���� ������ � ����������� - ������.

*/

function clientcomp_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _clientcomp_prepare($data);
    $db->Lock(array('dwClientComp'));
    $r = _clientcomp_check($data,$is_new);
    if ($r == '') {
        $q  = $is_new ? 'insert into dwClientComp set ':'update dwClientComp set ';
        $q .= isset($data['Adress']) ? ' Adress = "' . addslashes($data['Adress']) . '", ' : '' ;
        $q .= isset($data['Comments']) ? ' Comments = "' . addslashes($data['Comments']) . '", ' : '' ;
        $q .= isset($data['Mail']) ? ' Mail = "' . addslashes($data['Mail']) . '", ' : '' ;
        $q .= isset($data['Phone']) ? ' Phone = "' . addslashes($data['Phone']) . '", ' : '' ;

        $q .= isset($data['DeliveryTime']) ? ' DeliveryTime = "' . addslashes($data['DeliveryTime']) . '", ' : '' ;
        $q .= isset($data['EmployersNum']) ? ' EmployersNum = "' . addslashes($data['EmployersNum']) . '", ' : '' ;
        $q .= isset($data['WebUrl']) ? ' WebUrl = "' . addslashes($data['WebUrl']) . '", ' : '' ;
        $q .= isset($data['Metro']) ? ' Metro = "' . addslashes($data['Metro']) . '", ' : '' ;
        $q .= isset($data['PaymentType']) ? ' PaymentType = "' . addslashes($data['PaymentType']) . '", ' : '' ;
        $q .= isset($data['OrgType']) ? ' OrgType = "' . addslashes($data['OrgType']) . '", ' : '' ;

        $q .= isset($data['Name']) ? ' Name = "' . addslashes($data['Name']) . '" ' : '' ;
        $q .= $is_new ? '' : ' where IDClientComp = ' . $data['IDClientComp'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function clientcomp_del($id) {
    global $db;
    $r = '';
    if (!_is_clientcomp_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwClientComp where IDClientComp = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _clientcomp_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['Name'] == '') $r .= '������� ������������ �����������.<br/>';
    if ($data['Phone'] == '') $r .= '������� �������.<br/>';
    else if(!check_phone($data['Phone'],10)) $r .= '������� ���������� ������� (10 ����).<br/>';
    if ($data['Metro'] == '') $r .= '������� ��������� ������� �����.<br/>';
    if ($data['Adress'] == '') $r .= '������� ������ ����� ��������.<br/>';
//    if ($data['Phone'] == '' && $data['Mail'] == '') $r .= '������� ������� ��� �����.<br/>';
//    if (!string_is_int($data['EmployersNum'])) $r .= '���������� ����������� ������ ���� �������� ���������<br/>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    return $r;
}

function _clientcomp_prepare(&$data) {
    $data['Name'] = isset($data['Name']) ? trim($data['Name']) : '';
    $data['Adress'] = isset($data['Adress']) ? trim($data['Adress']) : '';
    $data['Comments'] = isset($data['Comments']) ? trim($data['Comments']) : '';
    $data['EmployersNum'] = (isset($data['EmployersNum']) && string_is_int($data['EmployersNum'])) ? $data['EmployersNum'] : 0;
    return $data;
}

function _is_clientcomp_exists($id) {
    return is_obj_exists($id, 'IDClientComp', 'dwClientComp');
}

function get_comp_list_arr() {
    global $db;
    $ret = array();
    $db->query("select * from dwClientComp");
    while($ar = $db->FetchArray()) {
        $ret[$ar['IDClientComp']] = $ar['Name'];
    }
    return $ret;
}

function get_comp_id_by_user($id_clientuser) {
    global $db;
    $ret = 0;
    if(string_is_id($id_clientuser)) {
        $q = 'select * from dwClientUsers DU left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp  where DU.IDClientUser = '.$id_clientuser;
        $db->query($q);
        if($db->NextRecord()) $ret = $db->F('IDClientComp');
    }
    return $ret;
}

function get_comp_info($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwClientComp where IDClientComp = ' . $id);
        if ($row = $db->FetchArray()) {
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>