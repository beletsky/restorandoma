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

function project_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _project_prepare($data);
    $db->Lock(array('dwPortfolio'));
    $r = _project_check($data,$is_new);
    if ($r == '') {
	    $main  = isset($data['ShowOnMain']) ? 1 : 0;
        $q  = $is_new ? 'insert into dwPortfolio set ':'update dwPortfolio set ';
        $q .= isset($data['ProjTitle']) ? 		  ' ProjTitle = "' . addslashes($data['ProjTitle']) . '",' : '' ;
        $q .= isset($data['ProjMainDesc']) ? 	  ' ProjMainDesc = "' . addslashes($data['ProjMainDesc']) . '",' : '' ;
        $q .= isset($data['ProjDesc']) ? 		  ' ProjDesc = "' . addslashes($data['ProjDesc']) . '",' : '' ;
        $q .= isset($data['ProjAboutCostumer']) ? ' ProjAboutCostumer = "' . ($data['ProjAboutCostumer']) . '",' : '' ;
        $q .= isset($data['ProjUrl']) ? 		  ' ProjUrl = "' . ($data['ProjUrl']) . '",' : '' ;
        $q .= isset($data['ProjReclamDesc']) ? 	  ' ProjReclamDesc = "' . ($data['ProjReclamDesc']) . '", ' : '' ;
        $q .= isset($data['IDProjCat']) ? 	  	  ' IDProjCat = "' . ($data['IDProjCat']) . '", ' : '' ;
		$q .= 'ShowOnMain = '. $main . ' ';
        $q .= $is_new ? '':' where IDProj = ' . $data['IDProj'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function update_pic($data,$isnew = true) {
	global $db;
    $main  = isset($data['is_main_pic']) ? 1 : 0;
	if ($main == 1) {
		$q1 = "update dwPortfolioPics set IsMain = 0 where IDProj = ".$data['IDProj'];
		$db->Query($q1);
	}
	if($isnew) $res_up = upLoadFile(PATH_TO_ROOT.PATH_TO_PIC);
	$q = $isnew ? "insert into dwPortfolioPics set " : "update dwPortfolioPics set ";
    $q .= isset($_FILES['files']['name']['image']) ?     ' BigName  = "'.$res_up['image'].'", ' :' '; 
    $q .= isset($_FILES['files']['name']['image_sm']) ?  ' SmallName = "'.$res_up['image_sm'].'", ':' ';
	$q .= 'IsMain  = '.$main. ', ';
	$q .= 'IDProj = '. $data['IDProj'];
	$q .= $isnew ? '' : 'where IDPic = '.$data['IDPic'];
	$db->Query($q);
}

function get_pics_list($id_proj) {
	global $db;
	$ret = array();
	if(string_is_id($id_proj)) {
	$q = 'select * from dwPortfolioPics where IDProj = '. $id_proj;
		$db->Query($q);
		while($arr = $db->FetchArray()) {
			$ret[] = $arr;
		}
	}
	return $ret;
}

function del_pic($id) {
	global $db;
	$ret = '';
	if(string_is_id($id)) {
		$q = 'select * from dwPortfolioPics where IDPic = '.$id;
		$db->Query($q);
		if($arr = $db->FetchArray()) {
			$n1 = $arr['SmallName'];
			$n2 = $arr['BigName'];
			if ($n1 != '') unlink(PATH_TO_ROOT . PATH_TO_PIC .$n1);
			if ($n2 != '') unlink(PATH_TO_ROOT . PATH_TO_PIC .$n2);
		}
		$db->Query("delete from dwPortfolioPics where IDPic = ".$id);
	} else {
		$ret = "�������� ID ��������";
	}
	return $ret;
}

function project_del($id) {
    global $db;
    $r = '';
    if (!_is_project_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwPortfolio where IDProj = ' . $id;
        $db->Query($q1);
    }
	$q = 'select IDPic from dwPortfolioPics where IDProj = ' . $id;
	$db->Query($q);
	while($db->NextRecord()) {
		$arr[] = $db->F('IDPic');
	}
	foreach ($arr as $val) del_pic($val);
    return $r;
}

function _project_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['ProjTitle'] == '') $r .= '������� ���.<br>';
    if ($data['ProjMainDesc'] == '') $r .= '������� ����� �������.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_project_exists($data['IDProj'])) $r .= '������ �� �������.<br>';

    return $r;
}

function _project_prepare(&$data) {
    $data['ProjTitle'] = isset($data['ProjTitle']) ? trim($data['ProjTitle']) : '';
    $data['ProjMainDesc'] = isset($data['ProjMainDesc']) ? trim($data['ProjMainDesc']) : '';
    $data['ProjReclamDesc'] = isset($data['ProjReclamDesc']) ? trim($data['ProjReclamDesc']) : '';
    $data['ProjDesc'] = isset($data['ProjDesc']) ? trim($data['ProjDesc']) : '';
    return $data;
}

function _is_project_exists($id) {
    return is_obj_exists($id, 'IDProj', 'dwPortfolio');
}
?>