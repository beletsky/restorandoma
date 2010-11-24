<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003,                  Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_params.inc.php                                              #
#   Функции для работы со списком параметров.                                  #
#                                                                              #
################################################################################
/*
add_sel_val($val, $id_par) - добавляет значение в списковою переменную
*/
class params {
	//Constructor
	var $erros = '';
    function params(){
	}

	function add_sel_val($val, $id_par) {
		global $db;
		$q = 'insert into dwSelects (Value, ID_Param) values ("' . $val .'", ' . $id_par . ')';
		$db->Query($q);
	}

	function del_sel_val($id) {
		global $db;
		$ret = false;
		$q = 'select * from dwSelects where ID_Sel = ' . $id;
		$db->Query($q);
		if ($db->NextRecord()) $ret = $db->F('ID_Param');
		$q = 'delete from dwSelects where ID_Sel = ' . $id;
		if ($this->is_sel_param_exist($id)) $db->Query($q);
		return $ret;
	}

	function del_param($id) {
		global $db, $param_types;
		$q = 'delete from dwParams where ID_Param = ' . $id;
		$db->Query($q);
	
		foreach($param_types as $k => $v) {
    		if ($k != 'Select') {
				$q = 'delete from dwValues_'.$k.' where ID_Param = '.$id;
				$db->Query($q);
			} else {
				$q = 'select ID_Sel from dwSelects where ID_Param = '. $id;
				$db->Query($q);	
				while($db->NextRecord()) {
					$q = 'delete from dwGoodSelVal  where ID_Sel = '. $db->F('ID_Sel');
					$db->Query($q);
	   			}
				$q = 'delete from dwSelects where ID_Param = '. $id;
				$db->Query($q);
			}
		}
	}
	
	function add_param_value($data) {
    	global $db;
		if ($data['ParamNum'] == '') $data['ParamNum'] = 0;
		if (isset($data['IsHref'])) { $this->clear_href(); $data['IsHref'] = 1; } else { $data['IsHref'] = 0; }
		if (isset($data['IsOrder'])) { $this->clear_order(); $data['IsOrder'] = 1; } else { $data['IsOrder'] = 0; }
		if ($data['ParType'] == 'Select' && $data['IsOrder'] = 1) {$this->erros .= "Список не может быть сортировочным полем</br>"; $isorder = 0;}
		isset($data['IsMain']) ? $data['IsMain'] = 0 : $data['IsMain'] = 1;
		isset($data['IsSupport']) ? $data['IsSupport'] = 0 : $data['IsSupport'] = 1;
		$q = 'insert into dwParams (ParamName, ParamType, ParamNum, IsMain, IsOrder, IsHref,IsSupport,ParamCode) values ("' . $data['ParamName'] .'", "' . $data['ParType']. '", "'.$data['ParamNum'].'","' . $data['ismain'] . '","' . $data['isorder'] . '","' . $data['ishref'] . '","' . $data['isSupport'] . '","' . $data['ParamCode'] . '")';
		$db->Query($q);
	}
	
	function update_param($data) {
		global $db;
		$ismain = 0;
		$param_num = $data['ParamNum'];
		if ($param_num == '') $param_num = 0;
		$data['ParType'] = $this->get_param_type($data['ID_Param']);
		if (isset($data['IsOrder']))  { $this->clear_order(); $isorder = 1; } else { $isorder = 0; }
		if (isset($data['IsHref'])) { $this->clear_href(); $ishref = 1; } else { $ishref = 0; }
		$supp = isset($data['IsSupport']) ? 1 : 0;
		if ($data['ParType'] == 'Select' && $data['IsOrder'] = 1) { $this->erros .= "Список не может быть сортировочным полем</br>"; $isorder = 0; }
		$ismain = isset($data['IsMain']) ? 1 : 0;
		isset($data['IsSupport']) ? $data['IsSupport'] = 0 : $data['IsSupport'] = 1;
		$q = 'update dwParams set ParamName = "' . $data['ParamName'] . '", ParamNum = "'. $data['ParamNum'] 
			.'", IsMain = ' . $ismain . ', IsOrder = ' . $isorder . ', IsHref = ' . $ishref 
			.', IsSupport = ' . $supp . ', ParamCode = "' . $data['ParamCode'] 
			. '" where ID_Param = ' . $data['ID_Param'];
		$db->Query($q);
	}
	
	
	function clear_order() {
		global $db;
		$q = "update dwParams set IsOrder = 0 where IsOrder = 1";	
		$db->Query($q);
	}
	
	function clear_href() {
		global $db;
		$q = "update dwParams set IsHref = 0 where IsHref = 1";	
		$db->Query($q);
    }
	
	function is_sel_param_exist($id) {
		global $db;
		$ret = false;
		$q = 'select * from dwSelects where ID_Sel = ' . $id;
		$db->Query($q);
		if ($db->NextRecord()) $ret = true;
		return $ret;
	}
	
	function get_param_type($id) {
		global $db;
		$ret = false;
		$q = 'select * from dwParams where ID_Param = ' . $id;
		$db->Query($q);
		if ($db->NextRecord()) $ret = $db->F('ParamType');
    	return $ret;
	}

	function get_param_array($id) {
		global $db;
		$data = array();
		$q = 'select * from dwParams where ID_Param = ' . $id;
		$db->Query($q);
		while ($db->NextRecord()) {
			$data['ParamName'] = htmlspecialchars($db->F('ParamName'));
			if ($db->F('IsMain') != 0) $data['IsMain'] = $db->F('IsMain');
			if ($db->F('IsOrder') != 0)$data['IsOrder'] = $db->F('IsOrder');
			if ($db->F('IsHref') != 0) $data['IsHref'] = $db->F('IsHref');
			if ($db->F('IsSupport') != 0) $data['IsSupport'] = $db->F('IsSupport');
			$data['ParamType'] = htmlspecialchars($db->F('ParamType'));
			$data['ParamNum'] = htmlspecialchars($db->F('ParamNum'));
			$data['ParamCode'] = htmlspecialchars($db->F('ParamCode'));
		}
		return $data;
	}

}
?>
	
	
	
	
	
	
	
	