<?
################################################################################
#                                                                              #
#   Project name - Universal Shop                                              #
#                                                                              #
#   Copyright (б) 2004, Sergey Efremov  				                       #
#                                                                              #
#   admin/home/index.php                                                       #
#   Страница каталога - оснеовные категории товаров и параметры                #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Каталог');
define ('PAGE_CODE',     'catalog');

require_once (PATH_TO_ADMIN . 'inc/top.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func_catalog.inc.php');

$cat  = new catalog();
// Получим параметры
$id = 0;
if (isset($_GET['id'])) $id = $_GET['id'];
if (!string_is_id($id) && isset($_POST['id'])) $id = $_POST['id'];
if ($id == '') $id = 0;
$form = GetFormData($id);
$form['ID_Catalog'] = $id;

if ($form['ID_Catalog'] == 0) $form['ID_Parent'] = 0;


$action = '';
if (isset($_GET['a'])) $action = $_GET['a'];
if ($action == '' && isset($_POST['a'])) $action = $_POST['a'];

switch ($action) {
	case 'add_val': 
		if ($form['CatValue'] != '') 
		$cat->add_tree_value($form['CatValue'],$id,$form['IDPage']);
		break;
	case 'change_val': 
		if ($form['CatValue'] != '') 
		$cat->update_tree_value($form['CatValue'],$id,$form['IDPage']);
		break;
	case 'parent_change': 
		$cat->change_parent($id,$form['ID_ParentSel']);
		$id = $form['ID_ParentSel'];
		if ($id == '') $id = 0;
		$form['ID_Parent'] = $cat->get_parent_id($id);
		break;
	case 'position_change':
		$cat->change_parent($id,$form['ID_FirstPosSel'],true);
		$id = $form['ID_FirstPosSel'];
		if ($id == '') $id = 0;
//		$form['ID_Parent'] = $cat->get_parent_id($id);
		break;
	case 'del_proc':
		$cat->del_tree_value($id);
		$id=0;
		break;
}

$curr_child_arr = $cat->get_all_childrens();
foreach ($curr_child_arr as $val) {
	$nbsp = '&nbsp;';
	for($i=0;$i<$val['level']-1;$i++) $nbsp .= '&nbsp;&nbsp;&nbsp;'; 
	$current_tree_array[$val['ID_Cat']] = $nbsp.$val['CatName'];
}
$same_level_arr_ = $cat->get_all_childrens($id,true);
foreach ($same_level_arr_ as $val)
$same_level_arr[$val['ID_Cat']] = $val['CatName'];

$cat_value = '';

if ($id != 0) {
	$tree_params = $cat->get_tree_values_byid($id);
	$cat_value = $tree_params['CatName'];
}

$tpl = new Template();

$tpl->set_file('main', 'iform.html');

$tpl->set_var('LIBRARY_TREE', GetList($id));
$tpl->set_var('THIS_PAGE', $this_page);
$tpl->set_var('ADD_WRITE', "<a href = ".$this_page."?action=add".">Добавить корневую ветвь</a>");
$tpl->set_var('CAT_VALUE', $cat_value);
$tpl->set_var('ID_Tree', $id);

$tpl->set_var('PARENT_OPTIONS', get_select_options(isset($form['ID_Parent']) && string_is_id($form['ID_Parent']) ? $form['ID_Parent'] : 0, $current_tree_array, false));
$tpl->set_var('SAME_LEVEL_OPTIONS', get_select_options(isset($form['ID_Cat']) && string_is_id($form['ID_Cat']) ? $form['ID_Cat'] : 0, $same_level_arr, false));
$tpl->set_var('PAGE_OPTIONS', get_select_options(isset($form['IDPage']) && string_is_id($form['IDPage']) ? $form['IDPage'] : 0, $cat->get_all_stpages(), false));

if($cat->errors != '') echo $cat->errors;

$param_arr = array();

$tpl->pparse('C', 'main', false);

print get_delete_script($this_page . '?a=del_proc&id=');

require (PATH_TO_ADMIN . 'inc/bottom.inc.php');           

function GetList() {
    global $db, $this_page;
    $q = 'select count(*) from dwCategories where ID_Parent is not null';
    $db->query($q);
    $cnt = $db->NextRecord() ? $db->F(0) <> 0 : false;

    $tbl = new PslAdmTbl;
    $tbl->mSortDefault   = 'id';
    $tbl->mSortMain = 'leftt';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mSessionPrefix = 'c_c_n';
    $tbl->mDownImg       =  PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         =  PATH_TO_ADMIN . 'img/up.gif';

    $tbl->mSortFields    = array('id' => 'ID_Cat', 'CatName' => 'CatName');

    $tbl->SetInPageOptions(array(10 => 10 ,20 => 20,30 => 30));

    $tbl->SetHead($this_page, array('ID', 'Наименование', 'Страница', 'Код', 'Действия'), array());

    $q  = 'select DC.*, DSP.Name,DSP.PageCode,DSP.IDPage from dwCategories DC left join dwCatPages DCP on '.
		' DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage where ID_Parent is not null' . 
          $tbl->GetOrderByClause() . $tbl->GetLimitClause();

    $db->Query($q);

	while($db->NextRecord()) {
		$nbsp = '&nbsp;';
		for($i=0;$i<$db->F('level')-1;$i++) $nbsp .= '&nbsp;&nbsp;&nbsp;'; 
        $tbl->SetRow(array($db->F('ID_Cat'), 
					$nbsp.'<a href='.$this_page . '?&id=' . $db->F('ID_Cat') . '>' . $db->F('CatName') . '</a>',
					$nbsp.'<a href="'.PATH_TO_ADMIN.'st_pages/?&id=' . $db->F('IDPage') . '&a=edit">' . $db->F('Name') . '</a>',
					$db->F('PageCode'),
                    '<center><a href="javascript:deleteRecord(' . $db->F('ID_Cat') . ')"><img src="'. PATH_TO_ADMIN  .'img/del.gif" border=0 alt="Удалить"></a></center>',
                     ));   
	}
    return $tbl->GetTable();
}


function GetFormData($id) {
	global $db,$cat;
	$data = array();
	if (!isset($_POST['form'])) {
		$q = 'select * from dwCategories DC left join dwCatPages DCP on DC.ID_Cat = DCP.IDCat where ID_Cat = ' . $id;
		$db->Query($q);
		while ($db->NextRecord()) {
			$data['Catname'] = htmlspecialchars($db->F('CatName'));
			$data['ID_Parent'] = $db->F('ID_Parent');
			$data['IDPage'] = $db->F('IDPage');
		}
	} else {
		$data = $_POST['form'];
	}
	$data['ID_Parent'] = $cat->get_parent_id($id);
	return $data;
}
?>                                   