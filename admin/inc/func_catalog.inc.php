<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2004, Sergey Efremov  				                       #
#                                                                              #
#   admin/inc/func_catalog.inc.php                                             #
#   Изменения в таблице категорий.                                             #
#                                                                              #
################################################################################
/*

  function content_del()        Принимают на вход массив с параметрами - полями.
  
*/
class catalog {

    var $errors = '';

	function catalog() {
	}

	function get_parent_id($id) {
		global $db;
		$q = "select * from dwCategories where ID_Cat = ". $id;
    	$db->query($q);
		($db->NextRecord()) ? $id_parent = $db->F('ID_Parent') : $id_parent = 0; 
		return  $id_parent;
	}


    function get_tree_values_byid($id) {
		global $db;
		if (string_is_id($id)) {
			$q = 'select DC.*, DSP.IDPage, DSP.Name,DSP.PageCode from dwCategories DC left join dwCatPages DCP on '.
			' DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage '.			
			' where DC.ID_Cat = ' . $id . '';
		} else {
			$q = 'select DC.*, DSP.IDPage, DSP.Name,DSP.PageCode from dwCategories DC left join dwCatPages DCP on '.
			' DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage '.			
			' where ID_Parent is null ';		
		}
        $db->query($q);
		if(!($ret = $db->FetchArray())) $ret = array(); 
		return $ret;
	}

	function  get_all_childrens($start_id=0,$same_level = false) {
		global $db;
		$ret = array();
		$add_sql = '';
		$st_arr = $this->get_tree_values_byid($start_id);
		if ($same_level) {
		    $cur_level = $st_arr['level'];
			$st_arr = $this->get_tree_values_byid($st_arr['ID_Parent']);
			$add_sql = ' and DC.level = '. $cur_level. ' ';
		}
		$q =  'select DC.*, DSP.IDPage, DSP.Name,DSP.PageCode from dwCategories DC left join dwCatPages DCP on '.
			' DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage '.
			' where DC.leftt >= ' . $st_arr['leftt'] . ' and DC.rightt <= '.$st_arr['rightt'].$add_sql.' order by leftt';
//		    $q  = 'select DC.*, DSP.IDPage, DSP.Name,DSP.PageCode from dwCategories DC left join dwCatPages DCP on '.
//				' DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage where ID_Parent is not null order by leftt';

		$db->query($q);
		while($tree_arr = $db->FetchArray()) { 
    		$ret[] = $tree_arr;
		}
		return $ret;
	}   
    
	//Функци добавлениея редактирования и удаления ветки дерева.
	
	function add_tree_value($val,$id_cat, $idpage, $same_level = false) {
    	global $db;
    	$id_cat == 0 ? $id_cat = 'null' : $id_cat = $id_cat;
        $arr = $this->get_tree_values_byid($id_cat);
		if($same_level) {
			$arr['rightt'] = $arr['rightt']+1;
			$arr['ID_Cat'] = $arr['ID_Parent'];
			$arr['level'] = $arr['level']-1;
		}
		$q1 = 'update dwCategories set leftt  = leftt+2  where leftt >= '. $arr['rightt'];
		$q2 = 'update dwCategories set rightt = rightt+2 where rightt >= '. $arr['rightt'];
		$q3 = 'insert into dwCategories (CatName,ID_Parent,leftt,rightt,level) values ("' . $val .'",' . $arr['ID_Cat']. ',' . $arr['rightt']. ',' . ($arr['rightt']+1) . ',' . ($arr['level']+1) . ')';
		$db->Query($q1);
		$db->Query($q2);
		$db->Query($q3);
		$qn = 'select last_insert_id()';
		$db->Query($qn);
	    $id_c = $db->NextRecord() ? $db->F(0) :  0;
    	$q4 = 'insert into dwCatPages set IDPage = "' . $idpage .'",  IDCat = ' . $id_c;
		$db->Query($q4);
    }
	
	function update_tree_value($val,$id_cat,$idpage) {
		global $db;
    	$q = 'update dwCategories set CatName = "' . $val .'" where ID_Cat = ' . $id_cat;
		if ($this->_is_catpage_exists($id_cat))
    		$q2 = 'update dwCatPages set IDPage = "' . $idpage .'" where IDCat = ' . $id_cat;
		else 
    		$q2 = 'insert into dwCatPages set IDPage = "' . $idpage .'", IDCat = ' . $id_cat;
		$db->Query($q);
		$db->Query($q2);
	}
    
    //Удалеине ветки
    function del_tree_value($id_cat) {
		global $db;
		$q = "select * from dwCategories where ID_Parent = $id_cat";
		$db->Query($q);
		if($db->NextRecord()) {
			$this->errors.= "Удаление категории имеющей подкатегори невозможно. Удалите или перенесите  все подкатегории.";
		} else {
	        $arr = $this->get_tree_values_byid($id_cat);
			$q  = 'delete from dwCategories where ID_Cat = ' . $id_cat;
			$q1 = 'update dwCategories set leftt  = leftt-2  where leftt > '. $arr['rightt'];
			$q2 = 'update dwCategories set rightt = rightt-2 where rightt > '. $arr['rightt'];
			$q3 = 'delete from dwCatPages  where IDCat = '. $id_cat;
    		$db->Query($q);
    		$db->Query($q1);
    		$db->Query($q2);
    		$db->Query($q3);
	        $q_del = 'delete from dwCatPages where IDCat = ' . $id_cat;
	        $db->Query($q_del);
    	}
	}


    //Функция измения родителя у ветки???????????????????????????????????????????????????????????????
	
	function change_parent($id_cat,$id_new_parent,$same_level = false) {
    	global $db; 
		if (string_is_id($id_cat)) {
       		$arr_cat = $this->get_tree_values_byid($id_cat);
			if (string_is_id($arr_cat['ID_Parent']) && $id_cat != $id_new_parent) { 
				$childs_list = $this->get_all_childrens($id_cat);
				if(count($childs_list) > 1) {
					$this->delete_line($arr_cat['level'],$arr_cat['leftt'],$arr_cat['rightt']);
					$this->add_line($arr_cat['level'],$childs_list,$id_new_parent,$same_level);
				} else {
			  		$this->del_tree_value($id_cat);
					$this->add_tree_value($arr_cat['CatName'],$id_new_parent,$arr_cat['IDPage'],$same_level);
				}
			} else {
				$this->errors .= 'Невозможно переместить элемент в самого себя';
			}
		} else {
			$this->errors .= 'Вы не выбрали ни одного элемнта каталога';
		}
	}

	#Функция удалении ветви дерева. Включая ВСЕ дочернии элементы.
	function delete_line($level,$leftt,$rightt) {
		global $db;
		if(string_is_id($rightt) && string_is_id($leftt) && $rightt > $leftt) {
			$diff = $rightt - $leftt+1;
			$q1 = 'delete from dwCategories where leftt >= '.$leftt. ' and rightt <= '.$rightt;	
			$q2 = 'update dwCategories set leftt  = leftt - '.$diff. ' where leftt > '.$leftt;
			$q3 = 'update dwCategories set rightt = rightt - '.$diff. ' where rightt > '.$leftt;
			$db->Query($q1);
			$db->Query($q2);
			$db->Query($q3);
		} else {
			$this->errors .= 'Неверные параметры удаляемой ветви';
		}
	}


	function add_line($level,$arr,$id_cat,$same_level = false) {
		global $db;
		foreach ($arr as $k => $val) {
		    $level_new = $val['level'];
			if($level_new != $level) {
				$qn = 'select last_insert_id()';
				$db->Query($qn);
			    $id_cat = $db->NextRecord() ? $db->F(0) :  0;
			}
	        $this->add_tree_value($val['CatName'],$id_cat, $val['IDPage'],$same_level);
			$level = $level_new;
			$same_level = false;
		}
	}
    
    //Функция выборки все существующих статичных страниц.
	function  get_all_stpages() {
		global $db;
		$ret = array();
		$q = 'select IDPage, Name, Title from dwStPages order by Name';
		$db->query($q);
		while($tree_arr = $db->FetchArray()) { 
    		$ret[$tree_arr['IDPage']] = $tree_arr['Name'];
		}
		return $ret;
	}   

	function _is_catpage_exists($id) {
    	return is_obj_exists($id, 'IDCat', 'dwCatPages');
	}

}	
?>
    
	
	
	
    
    