<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003,  Sergey Efremov                                        #
#                                                                              #
#   admin/adm/customers/index.php                                              #
#   Клинты.                                                                    #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../../');
define ('PATH_TO_ADMIN', '../../');
define ('PAGE_TITLE',    'Карточки пользователей');
define ('PAGE_CODE',     'userscards');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_users.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientusers.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientcomp.inc.php');

print get_header('Карточки пользователей:');

// Определим текущее действие
$action = '';
if (isset($_GET['a'])) $action = $_GET['a'];
if ($action == '' && isset($_POST['a'])) $action = $_POST['a'];
if ($action == '') $action = ACT_ADD;

// Получим параметры
$id = 0;
if (isset($_GET['id'])) $id = $_GET['id'];
if (!string_is_id($id) && isset($_POST['id'])) $id = $_POST['id'];
$form = GetData($id);
$form['IDClientUser'] = $id;

if (isset($_POST['Birthdate'])) $form['Birthdate'] = date_to_sql(get_date_from_select($_POST['Birthdate']));

// Выполним изменения
$msg = '';
$err = '';

switch ($action) {
    case ACT_ADD_PROC: {
        $err = clientuser_edit($form,true);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = clientuser_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = GetDbData($id);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = clientuser_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }
}

if ($id != 0) $action = ACT_EDIT;

// Покажем форму
print get_subheader($action == ACT_ADD ? 'Добавление' : 'Редактирование');
print get_formatted_error($err);

if ($action != ACT_ADD) print get_link('Добавить', $this_page);
print get_formatted_message($msg);
print get_subheader('Список');

print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

// Покажем список
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');



require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm($form, $action) {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_block('main', 'sites_list',     'sites_list_');
    $comp_arr = get_comp_list_arr();
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['IDClientUser']) ? $form['IDClientUser'] : '');
    $tpl->set_var('LOGIN',   isset($form['UserLogin']) ? $form['UserLogin'] : '');
    $tpl->set_var('PWD',     isset($form['UserPwd']) ? $form['UserPwd'] : '');
    $tpl->set_var('COMP_OPTIONS',get_select_options(isset($form['IDClientComp']) && string_is_id($form['IDClientComp']) ? $form['IDClientComp'] : 0, $comp_arr, false));
    $tpl->set_var('BDATE',create_date_selcet('Birthdate',80,isset($form['Birthdate']) ? sql_to_date($form['Birthdate']):'',1930));

    $tpl->set_var('NAME',    isset($form['Name']) ? $form['Name'] : '');
    $tpl->set_var('FNAME',    isset($form['FName']) ? $form['FName'] : '');
    $tpl->set_var('ONAME',    isset($form['OName']) ? $form['OName'] : '');
    $tpl->set_var('POSITION',    isset($form['Position']) ? $form['Position'] : '');
    $tpl->set_var('MAIL',    isset($form['Mail']) ? $form['Mail'] : '');
    $tpl->set_var('STATUS',  (isset($form['Status']) &&  $form['Status'] != '' ) ? 'checked' : '');
    $tpl->set_var('COMMENTS',isset($form['Comments']) ? $form['Comments'] : '');
    $tpl->set_var('AMOUNT',    isset($form['Amount']) ? $form['Amount'] : '');
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? 'Добавить' : 'Изменить');
    $tpl->set_var('ACTION', $action);

    $tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page,$form;

    $db->Query('select * from dwClientUsers');
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'quest_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'IDClientUser', 'CompName' =>'CompName', 'ClientName' => 'ClientName', 'UserLogin' => 'UserLogin');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', 'Организация', 'ФИО', 'Логин', 'Действия'),
                  array('Кликните для сортировки по колонке','Кликните для сортировки по колонке', '', 'Кликните для сортировки по колонке', ''));
    $tbl->mSortDefault   = 'CompName';
    $tbl->mSortTypeDefault = 'asc';

    $q  = 'select DC.*,DU.*,DCC.Name as CompName, concat(DC.Name, " ", DC.FName, " ", DC.OName) as ClientName from dwClientUsers DC '.
        'left join dwUsers DU on DC.IDUser = DU.ID_User ' . 
        'left join dwClientComp DCC on DC.IDClientComp = DCC.IDClientComp group by (IDClientUser) ' . 
        $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);
    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('IDClientUser'),
                           $db->F('CompName'),
                           $db->F('ClientName'),
                           '<a href='.$this_page . '?&id=' . $db->F('IDClientUser') . '>' .$db->F('UserLogin'). '</a>',
                           '<center><a href="javascript:deleteRecord(' . $db->F('IDClientUser') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="Удалить"></a></center>',
                           ));
    return $tbl->GetTable();
}

# Получить параметры как массив из постинга или из базы
function GetData($id) {
    global $_POST;
    $ret = '';
    if (isset($_POST['form'])) {
        $ret =  $_POST['form'];
    } else {
        $ret =  GetDbData($id);
    }
    return $ret;
}

# Получить параметры как массив из базы
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
    $q  = 'select DC.*,DU.*,DCC.Name as CompName from dwClientUsers DC '.
        'left join dwUsers DU on DC.IDUser = DU.ID_User ' . 
        'left join dwClientUsers DCC on DC.IDClientComp = DCC.IDClientComp ' . 
        'where DC.IDClientUser = ' . $id;

        $db->query($q);
        if ($row = $db->FetchArray()) {
            if (isset($row['Status']) && $row['Status'] == '0') $row['Status'] = '';
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>