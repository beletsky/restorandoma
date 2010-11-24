<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2006, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_clientuser.inc.php                                          #
#   Изменения в таблице клиентов(Сотрудники).                                  #
#                                                                              #
################################################################################
/*

  function catalog_edit()       Возвращают текст ошибки.
  function catalog_del()        Принимают на вход массив с параметрами - полями.

*/

function reg_new_comp($data_u,$data_c) {
    global $db;

    $r = '';
    $db->Lock(array('dwClientUsers','dwClientComp','dwUsers','dwGroups'));
    
    $data_a = array();
    if (isset($data_u['UserLogin'])) $data_a['UserLogin'] = $data_u['UserLogin'];
    if (isset($data_u['UserPwd']))   $data_a['UserPwd']   = $data_u['UserPwd'];
    $data_a['GroupCode']  = isset($data_u['GroupCode']) ? $data_u['GroupCode'] : 'ClientUsers';
    
    $r .= _clientcomp_check($data_c,true);
    $r .= _clientuser_check($data_u,true);
    if ($r != '' ) {
        $db->Unlock();
        return $r;
    }
    $r = _user_adm_check(_user_prepare($data_a),true);
    if ($r != '' ) {
        $db->Unlock();
        return $r;
    }
    
    $r = clientcomp_edit($data_c,true);
    $q = 'select last_insert_id()';
    $db->Query($q);
    $data_u['IDClientComp'] = $db->NextRecord() ?  $db->F(0) : 0;
    $r .= clientuser_edit($data_u,true);
    
    if ($r == '') {
        // Отослать сообщение об успешной регистрации.
        mail2admin('"РесторанДома" <webmaster@restorandoma.ru>','Регистрация на сайте "РесторанДома"', 
        'Здравствуйте, '.$data_u['FName'].'!'."\n".
        'Вы успешно зарегистрированы на сайте www.restorandoma.ru.'."\n".
        'Чтобы заказать обед, зайдите на сайт и введите свои регистрационные данные:'."\n\n".
        'Логин : '.$data_u['UserLogin']."\n".
        'Пароль: '.$data_u['UserPwd']."\n\n".
        'Приятного Вам аппетита!',
        '"'.$data_u['Name'].' '.$data_u['FName'].' '.$data_u['OName'].'" <'.$data_u['UserLogin'].'>');
    }
        
    $db->Unlock();
    return $r;
}

function reg_new_clientuser($data)
{
    $r = clientuser_edit($data,true);
        
    if ($r == '') {
        // Отослать сообщение об успешной регистрации.
        mail2admin('"РесторанДома" <webmaster@restorandoma.ru>','Регистрация на сайте "РесторанДома"', 
        'Здравствуйте, '.$data['FName'].'!'."\n".
        'Вы успешно зарегистрированы на сайте www.restorandoma.ru.'."\n".
        'Чтобы заказать обед, зайдите на сайт и введите свои регистрационные данные:'."\n\n".
        'Логин : '.$data['UserLogin']."\n".
        'Пароль: '.$data['UserPwd']."\n\n".
        'Приятного Вам аппетита!',
        '"'.$data['Name'].' '.$data['FName'].' '.$data['OName'].'" <'.$data['UserLogin'].'>');
    }
    
    return $r;
}

function update_clientuser($data)
{
    // Если почтовый адрес пользователя не совпадает с логином, 
    // надо менять логин и высылать повторное письмо.
    $send_mail = false;
    if (isset($data['UserLogin']) && isset($data['Mail']) && $data['UserLogin'] != $data['Mail']) {
        $data['UserLogin'] = $data['Mail'];
        $send_mail = true;
    }

    $r = clientuser_edit($data,false);
        
    if ($r == '' && $send_mail) {
        // Отослать сообщение об успешной регистрации.
        mail2admin('"РесторанДома" <webmaster@restorandoma.ru>','Регистрация на сайте "РесторанДома"', 
        'Здравствуйте, '.$data['FName'].'!'."\n".
        'Вы зарегистрированы на сайте www.restorandoma.ru.'."\n".
        'Ваш электронный адрес был изменен. Новые регистрационные данные:'."\n\n".
        'Логин : '.$data['UserLogin']."\n".
        'Пароль: '.$data['UserPwd']."\n\n".
        'Приятного Вам аппетита!',
        '"'.$data['Name'].' '.$data['FName'].' '.$data['OName'].'" <'.$data['UserLogin'].'>');
    }
    
    return $r;
}

function send_client_password($login)
{
    $data = get_clientuser_info_by_login($login);
    if (count($data))
    {
        mail2admin('"РесторанДома" <webmaster@restorandoma.ru>','Регистрация на сайте "РесторанДома"', 
        'Здравствуйте, '.$data['FName'].'!'."\n".
        'Вы запросили свой пароль для доступа к сайту www.restorandoma.ru.'."\n".
        'Ваши регистрационные данные:'."\n\n".
        'Логин : '.$data['UserLogin']."\n".
        'Пароль: '.$data['UserPwd']."\n\n".
        'Приятного Вам аппетита!',
        '"'.$data['ClientName'].'" <'.$data['UserLogin'].'>');
        
        return $data['FName'].' '.$data['OName']
            .', письмо с Вашими регистрационными данными выслано на адрес '
            .$data['UserLogin'].'.';
    } else {
        return 'Пользователя с электронным адресом '.$login.' на сайте не зарегистрировано!';
    }
}

function clientuser_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _clientuser_prepare($data);
    $db->Lock(array('dwClientUsers','dwUsers','dwGroups'));
    $r = _clientuser_check($data,$is_new);

    if (isset($data['UserLogin'])) $udata['UserLogin'] = $data['UserLogin'];
    if (isset($data['UserPwd']))   $udata['UserPwd']   = $data['UserPwd'];
    $udata['GroupCode']  = isset($data['GroupCode']) ? $data['GroupCode'] : 'ClientUsers';
                                            
    if ($r == '') {
        if (!$is_new) {
            $q = "select * from dwClientUsers where IDClientUser = ". $data['IDClientUser'];
            $db->Query($q);
            $udata['ID_User'] = $db->NextRecord() ? $db->F('IDUser') : 0;
            $r .= user_adm_edit($udata);
        } else {
            $r .= user_adm_add($udata);
            $q = 'select last_insert_id()';
            $db->Query($q);
            $udata['ID_User'] = $db->NextRecord() ?  $db->F(0) : 0;
        }
    }

    if ($r == '') {
        $q  = $is_new ? 'insert into dwClientUsers set ':'update dwClientUsers set ';
        $st = (isset($data['Status']) && $data['Status'] != '') ? '1' : '0';

        $q .= isset($data['IDClientComp']) ? ' IDClientComp = "' . addslashes($data['IDClientComp']) . '", ' : '' ;
        $q .= isset($data['Birthdate']) ?    ' Birthdate = "' . addslashes($data['Birthdate']) . '", ' : '' ;
        $q .= isset($data['Position']) ?     ' Position = "' . addslashes($data['Position']) . '", ' : '' ;
        $q .= isset($data['Amount']) ?       ' Amount = Amount+"' . addslashes($data['Amount']) . '", ' : '' ;
        $q .= isset($data['Comments']) ?     ' Comments = "' . addslashes($data['Comments']) . '", ' : '' ;
        $q .= isset($data['Name']) ?         ' Name = "' . addslashes($data['Name']) . '", ' : '' ;
        $q .= isset($data['FName']) ?        ' FName = "' . addslashes($data['FName']) . '", ' : '' ;
        $q .= isset($data['OName']) ?        ' OName = "' . addslashes($data['OName']) . '", ' : '' ;
        $q .= isset($data['Mail']) ?         ' Mail = "' . addslashes($data['Mail']) . '", ' : '' ;
        $q .= ' IDUser = '. $udata['ID_User'] . ', ';
        $q .= ' Status = '.$st.' ';
        $q .= $is_new ? '' : ' where IDClientUser = ' . $data['IDClientUser'];
        $db->Query($q);
    }

    $db->Unlock();
    return $r;
}

function clientuser_add_amount($id,$summ) {
    global $db;
    $ret = '';
    if(string_is_id($id) && is_numeric($summ)) {
        add_account_operation($id,$summ,true);
        update_user_amount($id);
    } else {
        $ret = ERR_ADD_AMOUNT;
    }
    return $ret;
}

function clientuser_del($id) {
    global $db;
    $r = '';
    $q = "select * from dwClientUsers where IDClientUser = ". $id;
    $db->Query($q);
    $id_us = $db->NextRecord() ? $db->F('IDUser') : '';
    $r .= user_adm_del($id_us);
    if ($r == '') {
        $q1 = 'delete from dwClientUsers where IDClientUser = ' . $id;
        $db->Query($q1);
    }
    $q2 = 'delete from dwOrders where IDClientUser = ' . $id;
    $db->query($q2);
    return $r;
}

function _clientuser_check($data, $isAdd = false) {
    global $db;
    $r = '';
    // Проверка наличия полей
    if ($data['Name'] == '') $r .= 'Введите фамилию.<br>';
    if ($data['FName'] == '') $r .= 'Введите имя.<br/>';
    if ($data['Mail'] == '') $r .= 'Введите адрес электронной почты.<br/>';
    else if(!check_email($data['Mail'])) $r .= 'Введите правильный адрес электронной почты.<br/>';
    if (isset($data['Amount']) && !string_is_float($data['Amount']) && $data['Amount']!= '') $r .= 'Сумма должна быть числовым значением.<br/>';
    // Если не isAdd, проверим, существует ли запись с таким ID
    return $r;
}

function _clientuser_prepare(&$data) {
    $data['Name'] = isset($data['Name']) ? trim($data['Name']) : '';
    $data['Adress'] = isset($data['Adress']) ? trim($data['Adress']) : '';
    $data['Comments'] = isset($data['Comments']) ? trim($data['Comments']) : '';
    return $data;
}

function _is_clientuser_exists($id) {
    return is_obj_exists($id, 'IDClientUser', 'dwClientUsers');
}

function select_all_client_users($ManagersOnly = true) {
    global $db;
    $ret = array();
    $where = $ManagersOnly ? ' where DG.GroupCode = "ClientAdmins" ' : '';
    $q = 'select concat(DC.Name, " ", DC.FName, " ", DC.OName) as ClientName, DC.* from dwClientUsers DC left join dwUsers DU on DC.IDUser = DU.ID_User left join dwGroups DG on DU.ID_Group = DG.ID_Group '. $where .' group by (DC.IDClientUser) ';
    $db->query($q);
    while($arr = $db->FetchArray()) {
        $ret[$arr['IDClientUser']] = $arr['ClientName'];
    }
    return $ret;
}

function select_all_company_users($id_comp) {
    global $db;
    $ret = array();
    $where = string_is_id($id_comp) ? ' where DC.IDClientComp = '.$id_comp.' ' : '';
    $q = 'select concat(DC.Name, " ", DC.FName, " ", DC.OName) as ClientName, DC.* from dwClientUsers DC left join dwUsers DU on DC.IDUser = DU.ID_User left join dwGroups DG on DU.ID_Group = DG.ID_Group '. $where .' group by (DC.IDClientUser) ';
    $db->query($q);
    while($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_clientuser_info_by_id($id) {
    global $db;
    $ret = array();
    if (string_is_id($id)) {
        $q = 'select *, concat(DCU.Name, " ", DCU.FName, " ", DCU.OName) as ClientName,DCU.Name as UName from dwUsers DU left join dwClientUsers DCU on DU.ID_User = DCU.IDUser left join dwClientComp DCC on DCU.IDClientComp = DCC.IDClientComp where DU.ID_User = '.$id;
        $db->Query($q);
        if($arr = $db->FetchArray()) $ret = $arr;
    }
    return $ret;
}

function get_clientuser_info_by_login($login) {
    global $db;
    $ret = array();
    if (isset($login)) {
        $q = 'select *, concat(DCU.Name, " ", DCU.FName, " ", DCU.OName) as ClientName from dwUsers DU left join dwClientUsers DCU on DU.ID_User = DCU.IDUser left join dwClientComp DCC on DCU.IDClientComp = DCC.IDClientComp where DU.UserLogin = "'.$login.'"';
        $db->Query($q);
        if($arr = $db->FetchArray()) $ret = $arr;
    }
    return $ret;
}

function get_clientuser_info_by_client_id($id) {
    global $db;
    $ret = array();
    if (string_is_id($id)) {
        $q = 'select DG.*, DU.*, DCU.*, concat(DCU.Name, " ", DCU.FName, " ", DCU.OName) as ClientName from dwUsers DU left join dwGroups DG on DU.ID_Group = DG.ID_Group left join dwClientUsers DCU on DU.ID_User = DCU.IDUser left join dwClientComp DCC on DCU.IDClientComp = DCC.IDClientComp where DCU.IDClientUser = '.$id;
        $db->Query($q);
        if($arr = $db->FetchArray()) $ret = $arr;
    }
    return $ret;
}
?>