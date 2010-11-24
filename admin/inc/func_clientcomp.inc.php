<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2006, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_clientcomp.inc.php                                          #
#   Изменения в таблице клиентов(Компании).                                    #
#                                                                              #
################################################################################
/*

  function catalog_edit()       Возвращают текст ошибки.
  function catalog_del()        Принимают на вход массив с параметрами - полями.

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
    if (!_is_clientcomp_exists($id)) $r .= 'Ресурс не найден.<br>';
    if ($r == '') {
        $q1 = 'delete from dwClientComp where IDClientComp = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _clientcomp_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // Проверка наличия полей
    if ($data['Name'] == '') $r .= 'Введите наименование организации.<br/>';
    if ($data['Phone'] == '') $r .= 'Введите телефон.<br/>';
    else if(!check_phone($data['Phone'],10)) $r .= 'Введите правильный телефон (10 цифр).<br/>';
    if ($data['Metro'] == '') $r .= 'Введите ближайшую станцию метро.<br/>';
    if ($data['Adress'] == '') $r .= 'Введите точный адрес доставки.<br/>';
//    if ($data['Phone'] == '' && $data['Mail'] == '') $r .= 'Введите телефон для связи.<br/>';
//    if (!string_is_int($data['EmployersNum'])) $r .= 'Количество сотрудников должно быть числовым значением<br/>';
    // Если не isAdd, проверим, существует ли запись с таким ID
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