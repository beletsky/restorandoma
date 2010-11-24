<?                                    	
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_options.inc.php                                             #
#   ��������� � ������� �������� � �������.                                    #
#                                                                              #
################################################################################
/*

  function options_add()      - ������� ��� ������ � ���������.
  function options_edit()       ���������� ����� ������. 
  function options_del()        ��������� �� ���� ������ � ����������� - ������.
  
*/

function options_add($data) {
    global $db;
    $r = _options_check(_options_prepare($data), true);
    if ($r == '') {
        $q  = 'insert into dwOptions set';
        $q .= ' OptionCode = "' . addslashes($data['OptionCode']) . '", ';
        $q .= ' OptionName = "' . addslashes($data['OptionName']) . '", ';
        $q .= ' OptionSubCode = "' . addslashes(isset($data['values'][0]['OptionSubCode']) ? $data['values'][0]['OptionSubCode'] : '') . '", ';
        $q .= ' OptionValue = "' . addslashes(isset($data['values'][0]['OptionValue']) ? $data['values'][0]['OptionValue'] : '') . '", ';
        $q .= ' OptionOrder = "' . addslashes(isset($data['values'][0]['OptionOrder']) ? $data['values'][0]['OptionOrder'] : '') . '"';
        $db->Lock(array('dwOptions'));
        $db->Query($q);
        $id = $db->GetInsertId();
        if (isset($data['values']) && is_array($data['values']))
            foreach ($data['values'] as $k => $v) 
                if ($k > 0) _option_value_save($id, $v);
        $db->Unlock();
    }
    return $r;
}

function options_edit($data) {
    global $db;
    $r = '';
    $r = _options_check(_options_prepare($data));
    if ($r == '') {
        $q  = 'update dwOptions set';
        $q .= ' OptionCode = "' . addslashes($data['OptionCode']) . '", ';
        $q .= ' OptionName = "' . addslashes($data['OptionName']) . '", ';
        $q .= ' OptionSubCode = "' . addslashes(isset($data['values'][0]['OptionSubCode']) ? $data['values'][0]['OptionSubCode'] : '') . '", ';
        $q .= ' OptionValue = "' . addslashes(isset($data['values'][0]['OptionValue']) ? $data['values'][0]['OptionValue'] : '') . '", ';
        $q .= ' OptionOrder = "' . addslashes(isset($data['values'][0]['OptionOrder']) ? $data['values'][0]['OptionOrder'] : '') . '"';
        $q .= ' where ID_Option = ' . $data['ID_Option'];
        $db->Lock(array('dwOptions'));
        $db->Query($q);
        $id = $data['ID_Option'];
        $db->Query('delete from dwOptions where ID_Parent = ' . $id);
        if (isset($data['values']) && is_array($data['values']))
            foreach ($data['values'] as $k => $v) 
                if ($k > 0) _option_value_save($id, $v);
        $db->Unlock();
    }
    return $r;
}

function options_del($id) {
    global $db;
    $r = '';
    if (!_is_options_exists($id)) $r .= '������ �� �������.<br>';
    if ($r == '') {
        $db->Lock(array('dwOptions'));
        $q = 'delete from dwOptions where ID_Parent = ' . $id;
        $db->Query($q);
        $q = 'delete from dwOptions where ID_Option = ' . $id;
        $db->Query($q);
        $db->Unlock();
    }
    return $r;
}

function _option_value_save($idOption, $optionValue) {
    global $db;
    if (is_array($optionValue) && string_is_id($idOption)) {
    
        // ������� � ����
        $q  = 'insert into dwOptions set';
        $q .= ' ID_Parent = ' . $idOption . ',';
        $q .= ' OptionSubCode = "' . (isset($optionValue['OptionSubCode']) ? $optionValue['OptionSubCode'] : '') . '",';
        $q .= ' OptionValue = "' . (isset($optionValue['OptionValue']) ? $optionValue['OptionValue'] : '""') . '",';
        $q .= ' OptionOrder = ' . (isset($optionValue['OptionOrder']) ? $optionValue['OptionOrder'] : '""');
                         
        $db->Query($q);
    }
}

function _options_check($data, $isAdd = false) {
    global $db;
    $r = '';
    
    // �������� ������� � ������������ �������� �����
    if ($data['OptionCode'] == '') $r .= '������� ���.<br>';
    if ($data['OptionCode'] != '' && !string_is_login($data['OptionCode'])) $r .= '��� �������� ������������ �������.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_options_exists($data['ID_Option'])) $r .= '������ �� �������.<br>';
    // ��������, ���������� �� ������ � ����� �� �����
    $q = 'select count(*) from dwOptions where OptionCode = "' . addslashes($data['OptionCode']) . '"';
    if (!$isAdd) $q .= ' and ID_Option <> ' . $data['ID_Option'];
    $db->Query($q);
    $db->NextRecord();
    if ($db->F(0)) $r .= '����� ��� ��� ���������� � �������.<br>';
    // ��� ������� �������� �������� ������������ ������
    if (is_array($data['values']))
        foreach ($data['values'] as $k => $v) {
            if (is_array($v)) {
            
                // �������� ������������ ������
                if (!string_is_int($v['OptionOrder'])) $r .= "�������� ������� ������ $v[OptionOrder].<br>";
                
            }
        }
        
    return $r;
}

function _options_prepare(&$data) {
    $data['OptionCode'] = isset($data['OptionCode']) ? trim($data['OptionCode']) : '';
    $data['OptionName'] = isset($data['OptionName']) ? trim($data['OptionName']) : '';
    $values = array();
    if (isset($data['values']) && is_array($data['values']))
        foreach ($data['values'] as $v) 
            if ((isset($v['OptionSubCode']) && trim($v['OptionSubCode']) != '')
            	|| (isset($v['OptionValue']) && trim($v['OptionValue']) != '')) 
                $values[] = $v;
    $data['values'] = $values;
    return $data;
}

function _is_options_exists($id) {
    return is_obj_exists($id, 'ID_Option', 'dwOptions');
}

?>                       	