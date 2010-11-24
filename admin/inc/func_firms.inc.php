<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_firms.inc.php                                               #
#                                                                              #
################################################################################


function firm_edit($data,$isnew = true) {
    global $db;
    $r = '';
    $data = _firm_prepare($data);
    $db->Lock(array('dwFirms'));
    $r = _firm_check($data,$isnew);
    if ($r == '') {
        $q  = $isnew ? 'insert into dwFirms set ':'update dwFirms set ';
        $q .= isset($data['Title']) ? ' Title = "' . addslashes($data['Title']) . '",' : '' ;
        $q .= isset($data['Announce']) ? ' Announce = "' . addslashes($data['Announce']) . '",' : '' ;
        $q .= isset($data['Content']) ? ' Content = "' . ($data['Content']) . '",' : '' ;
        if (isset($_FILES['files']['name']['Image']) && ($_FILES['files']['name']['Image'] != '')) {
            if (!$isnew) {
                $r = del_pic($data['ID'],'dwFirms','ID');
            }
            $res_up = upLoadFile(PATH_TO_ROOT.PATH_TO_PIC, array('Image'=>array(88,0)));
            $q .= (isset($_FILES['files']['name']['Image']) &&  $_FILES['files']['name']['Image'] != '') ? ' Image = "'.$res_up['Image'].'", ' :' '; 
        }
        $q .= isset($data['URL']) ? ' URL = "' . addslashes($data['URL']) . '" ' : '' ;
        $q .= $isnew ? '':' where ID = ' . $data['ID'];
        $db->Query($q);
        if($isnew) {
            $q = 'select last_insert_id()';
            $db->Query($q);
            $db->NextRecord() ? $firm_id = $db->F(0) : $firm_id = 0;
            $data['ID'] = $firm_id;
        } else {
            $firm_id = $data['ID'];
        }

    }
    $db->Unlock();
    return $r;
}

function firm_del($id) {
    global $db;
    $r = '';
    if (!_is_firm_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwFirms where ID = ' . $id;
        $r = del_pic($id,'dwFirms','ID');
        $db->Query($q1);
    }
    return $r;
}

function _firm_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['Title'] == '') $r .= '������� �������� �����.<br>';
    if ($data['Announce'] == '') $r .= '������� ����� ������������ �����.<br>';
    if ($data['Content'] == '') $r .= '������� �������� �����.<br>';
    if ($data['URL'] == '') $r .= '������� URL �����.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_firm_exists($data['ID'])) $r .= '������� �� �������.<br>';

    return $r;
}

function _firm_prepare(&$data) {
    $data['Title'] = isset($data['Title']) ? trim($data['Title']) : '';
    $data['Announce'] = isset($data['Announce']) ? trim($data['Announce']) : '';
    $data['Content'] = isset($data['Content']) ? trim($data['Content']) : '';
    $data['URL'] = isset($data['URL']) ? trim($data['URL']) : '';
    return $data;
}

function _is_firm_exists($id) {
    return is_obj_exists($id, 'ID', 'dwFirms');
}


function del_pic($id,$table_name,$index_name) {
    global $db;
    $ret = '';
    if(string_is_id($id)) {
        $q = 'select * from '.$table_name.' where '.$index_name.' = '.$id;
        $db->Query($q);
        if($arr = $db->FetchArray()) {
            $n1 = $arr['Image'];
            if ($n1 != '') unlink(PATH_TO_ROOT . PATH_TO_PIC .$n1);
        }
    } else {
        $ret = "�������� ID �������� ��������";
    }
    return $ret;
}

?>
