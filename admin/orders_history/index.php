<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003,  Sergey Efremov                        				   #
#                                                                              #
#   admin/adm/customers/index.php                                              #
#   Клинты.                                                                    #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'История заказов');
define ('PAGE_CODE',     'orders_history');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_orders.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_files_upload.inc.php');


print get_header('Заказы:');

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
$form['IDArticle'] = $id;

// Выполним изменения
$msg = '';
$err = '';

switch ($action) {
    case "upload_menu" : {
        $err = upload_menu();
        if ($err == '') $form = array();
        break;
    }
/*    case ACT_EDIT_PROC: {
        if ($err = article_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
			$form = GetDbData($id);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = article_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }*/
}

// Покажем форму
//print get_subheader($action == ACT_ADD ? 'Добавление' : 'Редактирование');
print get_formatted_error($err);

//if ($action != ACT_ADD) print get_link('Добавить', $this_page);
print get_formatted_message($msg);
print get_subheader('Список');

print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);
// Покажем список
//print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');



require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm($form, $action) {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
	$fdate = isset($form['FDate']) ? get_date_from_select($form['FDate']) : '';
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('FIRST_DATE', create_date_selcet('form[FDate]',10,$fdate,2000));
    $tpl->set_var('LAST_DATE',  create_date_selcet('form[LDate]',10,isset($form['LDate']) ? get_date_from_select($form['LDate']) : '',2000));
	$tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page, $form, $g_options;
	$order_statuses_types_arr = $g_options->GetOptionList('order_statuses');

    $db->Query('select * from dwOrders');
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'quest_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'IDOrder', 'OrderNum'=> 'OrderNum', 'Name' => 'Name', 'ClientName' => 'ClientName', 'Status' => 'Status', 'Summ' => 'Summ' );

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', 'Номер заказа', 'Дата', 'Клиент', 'Физ лицо', 'Статус', 'Сумма'),
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', ''));
    $add_query = '';

	if(isset($form['LDate'])) $add_query .= ' and DO.OrderDate <= "'.date_to_sql(get_date_from_select($form['LDate'])).'" ';
	if(isset($form['FDate'])) $add_query .= ' and DO.OrderDate >=  "'.date_to_sql(get_date_from_select($form['FDate'])).'" ';

	if($add_query != '' ) $add_query = ' where 1 '. $add_query;
	$q = 'select DO.*, DC.Name, DC.IDClientComp, concat(DU.FName, " ", DU.Name, " ", DU.OName) as ClientName from dwOrders DO '.
		' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
		' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
		$add_query.$tbl->GetOrderByClause().$tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('IDOrder'),
                           '<a href="'. PATH_TO_ADMIN.PATH_TO_ADMORDERS . '?&id=' . $db->F('IDOrder') . '&show_date='.sql_to_date($db->F('OrderDate')).'">' .$db->F('OrderNum'). '</a>',
						   sql_to_date($db->F('OrderDate')),
						   $db->F('Name'),
						   $db->F('ClientName'),
						   isset($order_statuses_types_arr[$db->f('Status')]) ? $order_statuses_types_arr[$db->f('Status')] : '',
						   $db->F('Summ')
                           ));
    return $tbl->GetTable();
}

# Получить параметры как массив из постинга или из базы
function GetData($id) {
    global $_POST;
    $ret = '';
	if (isset($_POST['form'])) {
		$ret =  $_POST['form'];
		if(isset($_POST['form']['LDate'])) {
			$_SESSION['LDate'] = $_POST['form']['LDate'];
		}
		if(isset($_POST['form']['FDate'])) {
			$_SESSION['FDate'] = $_POST['form']['FDate'];
		}
	} else {
		$ret =  GetDbData($id);
		if(isset($_SESSION['LDate'])) {
			$ret['LDate'] = $_SESSION['LDate'];
		}
		if(isset($_SESSION['FDate'])) {
			$ret['FDate'] = $_SESSION['FDate'];
		}
	}
	return $ret;
}

# Получить параметры как массив из базы
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwArticles where IDArticle = ' . $id);
        if ($row = $db->FetchArray()) {
			$r[] = $row;
		}
		$r = $r[0];
    }
    return $r;
}

?>