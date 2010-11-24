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

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Заказы');
define ('PAGE_CODE',     'orders');

define ('ACT_VIEW',      'view');
define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_VIEW_PROC', 'act_view_proc');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_orders.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientcomp.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientusers.inc.php');

// Определим текущее действие
$action = '';
if (isset($_GET['a'])) $action = $_GET['a'];
if ($action == '' && isset($_POST['a'])) $action = $_POST['a'];
if ($action == '') $action = ACT_VIEW;

$show_date = '';
if (isset($_GET['show_date'])) $show_date = $_GET['show_date'];
if (isset($_POST['show_date'])) $show_date = $_POST['show_date'];
if ($show_date == '') {
    $day = date("d")+1;
    $show_date = date("d.m.Y", mktime(0, 0, 0, date("m"), $day, date("Y")));
}

// Получим параметры
$id = 0;
if (isset($_GET['id'])) $id = $_GET['id'];
if (!string_is_id($id) && isset($_POST['id'])) $id = $_POST['id'];
$form = GetData($id);
$form['IDOrder'] = $id;


// Выполним изменения
$msg = '';
$err = '';

switch ($action) {
    case ACT_ADD_PROC: {
        $err = order_edit($form,true);
        $show_date = $form['OrderDate'];
        if($err != '') {
            $action = ACT_ADD;
        } else {
            $action = ACT_VIEW;
        }
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = order_edit($form,false)) {
            $show_date = $form['OrderDate'];
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = GetDbData($id);
            $show_date = sql_to_date($form['OrderDate']);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = order_del($id);
        $action = ACT_VIEW;
        $form['IDOrder']=0;
        break;
    }
    case 'declay_order': {
        isset($_GET['IDClietnComp']) ? $form['IDClietnComp'] = $_GET['IDClietnComp'] : 0;
        $CompOrdArr = get_all_orders_by(0,0,$show_date,$form['IDClietnComp']);
        foreach($CompOrdArr as $v) $id_arr[] = $v['IDOrder'];
        change_order_status($id_arr,5);
        $action = ACT_VIEW;
        break;
    }
    case 'accept_order': {
        isset($_GET['IDClietnComp']) ? $form['IDClietnComp'] = $_GET['IDClietnComp'] : 0;
        $CompOrdArr = get_all_orders_by(0,0,$show_date,$form['IDClietnComp']);
        foreach($CompOrdArr as $v) $id_arr[] = $v['IDOrder'];
        change_order_status($id_arr,3);
        $action = ACT_VIEW;
        break;
    }
}

if ($id != 0) {
//  $show_date = sql_to_date($form['OrderDate']);
    $action = ACT_EDIT;
}

// Покажем форму
$sub1= '';
if($action == ACT_ADD)  $sub1 = 'Добавление';
if($action == ACT_EDIT) $sub1 = 'Редактирование';
//if($action == ACT_VIEW) $sub1 = 'Просмотр';
print get_subheader($sub1);

//Обновим статусы необработанных заказов 
automate_update_orders_status();


print get_formatted_error($err);

print get_formatted_message($msg);
//print get_subheader('Список');

if($action == ACT_ADD) {
    $form_action = ACT_ADD_PROC;
//  $form['OrderList'] = array();
}
if($action == ACT_EDIT) $form_action = ACT_EDIT_PROC;
if($action == ACT_VIEW) $form_action = ACT_VIEW_PROC;
print GetForm($form, $form_action, $show_date);

// Покажем список
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');


require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm($form, $action, $show_date) {
    global $this_page, $g_options, $week_days_arr;
    // Определим вдящие параметры дял формы.
    $s_date_arr = split("\.",$show_date);

    $dayw = date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]));
    $file_name = 'menu_'.date("d.m.Y",mktime(0,0,0, $s_date_arr[1], $s_date_arr[0]-$dayw+1, $s_date_arr[2])).'.csv';
    $menu_cont = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name);
    $comp_arr = get_comp_list_arr();
    if($form['IDOrder'] != 0 || $action == ACT_ADD_PROC) {
        $IDClientComp = get_comp_id_by_user($form['IDClientUser']);
        foreach ($comp_arr as $k => $v) 
            if ($k != $IDClientComp) unset($comp_arr[$k]);
    }
    
    $orders = ($action != ACT_ADD_PROC) ? get_all_oders_by_date($show_date,$form['IDOrder'] != 0 ? $form['IDOrder'] : 0, $form['IDOrder'] != 0 ? array(0,1,2,3,4,5) : array(2,3,4,5)) : array();
    if (isset($form['OrderListL']) && ($action == ACT_ADD_PROC)) {
        $orders[$IDClientComp]['OrderListL'] = $form['OrderListL'];
    }
    
    $menu_types_arr = $g_options->GetOptionList('menu_types');

    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    
    $tpl->set_block('main',       'date_item'           , 'date_item_');
    
    $tpl->set_block('main',       'client_name_list'    , 'client_name_list_');
    $tpl->set_block('main',       'client_actions_list' , 'client_actions_list_');
    $tpl->set_block('main',       'client_empty_actions', 'client_empty_actions_');
    $tpl->set_block('main',       'order_status_list'   , 'order_status_list_');
    $tpl->set_block('main',       'comments_list'       , 'comments_list_');
    
    $tpl->set_block('main',       'button',        'button_');
    $tpl->set_block('main',       'oder_details',  'oder_details_'); 
    $tpl->set_block('main',       'add_block',     'add_block_'); 

    $tpl->set_block('main',       'main_row',      'main_row_');
    $tpl->set_block('main_row',   'header_row',    'header_row_');
    $tpl->set_block('main_row',   'simple_row',    'simple_row_');
    $tpl->set_block('simple_row', 'client_number_edit', 'client_number_edit_');
    $tpl->set_block('simple_row', 'client_number_view', 'client_number_view_');

    //Выведем блок детализации заказа в случае вводанового или редактировании существующего
    if($form['IDOrder'] != 0 || $action == ACT_VIEW_PROC || $action == ACT_ADD_PROC) {
        $tpl->set_var('ORDERNUM',  isset($form['OrderNum']) ? $form['OrderNum'] : '');
        
        if (isset($form['OrderDate']))
            if (sql_to_date($form['OrderDate']) != '')  
                $sdate = sql_to_date($form['OrderDate']);
            else
                $sdate = $form['OrderDate'];
        $tpl->set_var('ORDERDATE', isset($form['OrderDate']) ? $sdate : $show_date);
        $tpl->set_var('STATUS_OPTIONS', get_select_options(isset($form['Status']) ? $form['Status'] : '', $g_options->GetOptionList('order_statuses'), false)); 
        $tpl->set_var('CLIENT_NAME_OPTIONS', get_select_options(isset($form['IDClientUser']) ? $form['IDClientUser'] : '', select_all_client_users(false), false)); 

        $tpl->set_var('SUMM', isset($form['Summ']) ? $form['Summ'] : '');
        if ($action != ACT_VIEW_PROC) 
            $tpl->parse('oder_details_','oder_details',false);
        if ($action == ACT_VIEW_PROC) 
            $tpl->parse('add_block_','add_block',false);
    }

    //Выведем навигацию оп неделям (текущая - следующая)
    for($i=1;$i<=14;$i++) {
        $day = date("d")-date("w")+$i;
        $week_date = date("d.m.Y",mktime(0,0,0, date("m"), $day, date("Y")));
        $tpl->set_var('WEEK_D', $week_date);
        $j = ($i > 7) ? $i-7 : $i;
        $tpl->set_var('WEEK_D_NAME', $week_days_arr[$j]);
        $tpl->set_var('ORDATE', $week_date);
        if($show_date != $week_date) {
            $tpl->set_var('DATE_SELECTED', '');
        } else {
            $tpl->set_var('DATE_SELECTED', 'selected="selected"');
            $tpl->set_var('PRINT_DATE_STRING', $week_date.' - '.$week_days_arr[$j]);
        }
        $tpl->parse('date_item_','date_item',true);
    }

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('CLIENT_NUM',count($comp_arr));
    $tpl->set_var('FULL_TABLE_COLS',count($comp_arr) + 3);
    $tpl->set_var('PRINT_URL',PRINT_ORD_PAGE.'?show_date='.$show_date);

    //Выведем список клиентов (организаций в форме заказов)
    $oder_statuses_arr = $g_options->GetOptionList('order_statuses');
    $order_colors_arr = array( 2 => 'blue', 3 => 'green', 4 => '#A08000', 5 => 'red');
    $order_bgcolors_arr = array( 2 => '#A0A0FF', 3 => '#C0FFC0', 4 => '#FFFF80', 5 => '#FFA0A0');

    foreach($comp_arr as $k => $val) {
        if(!isset($orders[$k])) continue;
    
        $tpl->set_var('CLIENTNAME', $val);
        $tpl->set_var('URL', PATH_TO_ADMIN.CLIENT_ADM_PAGE."?id=".$k);
        $tpl->set_var('N_PRINT_URL',PRINT_ORD_PAGE.'?print_nak=1&show_date='.$show_date.'&comp_id='.$k);
        $tpl->set_var('IDCLIENTCOMP',$k);
        $tpl->set_var('SHOWDATE',$show_date);
        $tpl->set_var('COMMENTS',isset($orders[$k]['Comments']) ? $orders[$k]['Comments'] : '');
        $tpl->set_var('client_empty_actions_','');
        if (isset($orders[$k]['Status'])) {
            $kkk = $orders[$k]['Status'];
            $tpl->set_var('STATUS', 'Заказ '.$oder_statuses_arr[$kkk]);
            $tpl->set_var('STATUS_COLOR', isset($order_colors_arr[$kkk]) ? $order_colors_arr[$kkk] : 'black');
            $tpl->parse('client_actions_list_','client_actions_list',true);
        } else {
            $tpl->set_var('STATUS', 'Нет заказов');
            $tpl->set_var('STATUS_COLOR', 'black');
            $tpl->parse('client_actions_list_','client_empty_actions',true);
        }

        $tpl->parse('client_name_list_','client_name_list',true);
        $tpl->parse('order_status_list_','order_status_list',true);
        $tpl->parse('comments_list_','comments_list',true);
    } 

    // Выведем меню и сами заказы в случае определения даты.
    if(isset($menu_cont[$show_date])) {
        $old_menu_type = "old";
        $view_only = ($form['IDOrder'] == 0 && $action != ACT_ADD_PROC);
        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
            $new_menu_type = $menu_val[2];
            $tpl->set_var('header_row_','');
            if($new_menu_type != $old_menu_type) {
                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                $tpl->parse('header_row_','header_row',true);
            }

            $tpl->set_var('client_number_edit_','');
            $tpl->set_var('client_number_view_','');
            $tpl->set_var('simple_row_','');

            $tpl->set_var('WEIGHT',$menu_val[4]);
            $tpl->set_var('TITLE',$menu_val[3]);
            $summ = 0;
            foreach($comp_arr as $comp_k => $comp_val) {
                if(!isset($orders[$comp_k])) continue;
        
                if (isset($orders[$comp_k]['OrderListL'][$menu_val[0]]['main'])) {
                    $tpl->set_var('NUM',$orders[$comp_k]['OrderListL'][$menu_val[0]]['main']);
                    $summ += $orders[$comp_k]['OrderListL'][$menu_val[0]]['main'];
                } else {
                    $tpl->set_var('NUM','');
                }
                $tpl->set_var('ID_MEAL',$menu_val[0]);
                $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                
                if (isset($orders[$comp_k]['Status']) && isset($order_bgcolors_arr[$orders[$comp_k]['Status']])) {
                    $tpl->set_var('CLIENT_NUMBER_BGCOLOR', $order_bgcolors_arr[$orders[$comp_k]['Status']]);
                } else {
                    $tpl->set_var('CLIENT_NUMBER_BGCOLOR','#C0C0C0');
                }
                
                if ($view_only) {
                    $tpl->parse('client_number_view_','client_number_view',true);
                    $tpl->set_var('client_number_edit_','');
                } else {
                    $tpl->parse('client_number_edit_','client_number_edit',true);
                    $tpl->set_var('client_number_view_','');
                }
            }
            $tpl->set_var('SUMM',$summ);
            $tpl->parse('simple_row_','simple_row',true);
            $tpl->parse('main_row_','main_row',true);
            $old_menu_type = $new_menu_type;
        }
    }
//    $tpl->set_var('COMMENTS',isset($form['Comments']) ? $form['Comments'] : '');
    $tpl->set_var('ID', isset($form['IDOrder']) ? $form['IDOrder'] : '');

    if ($action != ACT_VIEW_PROC) {
        $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? 'Добавить' : 'Изменить');
        $tpl->parse('button_','button',false);
    } else {
        $tpl->set_var('button_', '');
    }
    $tpl->set_var('ACTION', $action);
    $tpl->set_var('LIST',GetList($show_date));
    return $tpl->parse('C', 'main', false);
}

# Список
function GetList($show_date) {
    global $db, $this_page, $form, $g_options;
    $order_statuses_types_arr = $g_options->GetOptionList('order_statuses');

    $db->Query('select * from dwOrders where OrderDate = '.date_to_sql($show_date));
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
    $tbl->SetHead($this_page, array('ID', 'Номер заказа', 'Клиент', 'Физ лицо', 'Статус', 'Сумма', 'Действия'),
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', ''));

    $q = 'select DO.*, DC.Name, DC.IDClientComp, DU.IDClientUser, concat(DU.Name, " ", DU.FName, " ", DU.OName) as ClientName from dwOrders DO '.
        ' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
        ' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
        ' where OrderDate = "'.date_to_sql($show_date).'" ' .$tbl->GetOrderByClause().$tbl->GetLimitClause();

    $db->Query($q);

    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('IDOrder'),
                           '<a href="'.$this_page . '?&id=' . $db->F('IDOrder') . '&show_date='.$show_date.'">' .$db->F('OrderNum'). '</a>',
                           '<a href="'.PATH_TO_ADMIN.CLIENT_ADM_PAGE."?id=".$db->f('IDClientComp').'" >'.$db->F('Name').'</a>',
                           '<a href="'.PATH_TO_ADMIN.CLIENT_USER_ADM_PAGE."?id=".$db->f('IDClientUser').'" >'.$db->F('ClientName').'</a>',
                           isset($order_statuses_types_arr[$db->F('Status')]) ? $order_statuses_types_arr[$db->F('Status')] : '',
                           $db->F('Summ'),
                           '<center><a href="javascript:deleteRecord(' . $db->F('IDOrder') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="Удалить"></a></center>',
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
        $q = 'select DO.*, DU.IDClientUser, DU.IDClientComp, DU.Name, DU.FName, DU.OName from dwOrders DO left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser where IDOrder = ' . $id;
        $db->query($q);
        if ($row = $db->FetchArray()) {
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>