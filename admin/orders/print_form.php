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

//require (PATH_TO_ADMIN . 'inc/top.inc.php');
require_once (PATH_TO_ADMIN . 'inc/init.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func.inc.php');

include_once (PATH_TO_ADMIN . 'inc/func_orders.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientcomp.inc.php');
//include_once (PATH_TO_ADMIN . 'inc/func_clientusers.inc.php');

$show_date = '';
if (isset($_GET['show_date'])) $show_date = $_GET['show_date'];
if (isset($_POST['show_date'])) $show_date = $_POST['show_date'];
if ($show_date == '') {
    $day = (date('w') < 5) ? date("d")+1 : date("d")+3;
    $show_date = date("d.m.Y", mktime(0, 0, 0, date("m"), $day, date("Y")));
}

if(!isset($_GET['print_nak'])) {
    print GetForm($show_date);
} else {
    print GetNakForm($show_date,$_GET['comp_id']);
}
function GetForm($show_date) {
    global $this_page, $g_options, $week_days_arr;

    $s_date_arr = split("\.",$show_date);

    $dayw = date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]));
    $file_name = 'menu_'.date("d.m.Y",mktime(0,0,0, $s_date_arr[1], $s_date_arr[0]-$dayw+1, $s_date_arr[2])).'.csv';
    $menu_cont = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name);
    $comp_arr = get_comp_list_arr();
    $orders =  get_all_oders_by_date($show_date,0,array(3,4));
    $menu_types_arr = $g_options->GetOptionList('menu_types');

    $clients_on_page = 3;
    
    $tpl = new Template();
    $tpl->set_file('main', 'print_ord_form.ihtml');
    $tpl->set_block('main',         'client_block',  'client_block_');
    $tpl->set_block('client_block', 'comp_list',     'comp_list_');
    $tpl->set_block('client_block', 'comment_list',  'comment_list_');
    $tpl->set_block('client_block', 'oder_details',  'oder_details_'); 

    $tpl->set_block('client_block', 'main_row',    'main_row_');
    $tpl->set_block('main_row',   'header_row',    'header_row_');
    $tpl->set_block('main_row',   'simple_row',    'simple_row_');
    $tpl->set_block('simple_row', 'client_number', 'client_number_');

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('CLIENT_NUM',count($comp_arr)+3);
    $tpl->set_var('CUR_DATE',$show_date);

    //Выведем список клиентов (организаций в форме заказов)
    foreach($comp_arr as $k => $val) {
        if(!isset($orders[$k])) continue;
        
        $temp_comp_arr = get_comp_info($k);
        $tpl->set_var('CLIENTNAME', $val);
        $tpl->set_var('COMMENTS',isset($orders[$k]['Comments']) ? $orders[$k]['Comments'] : '');
        $tpl->set_var('CLIENT_ADDRESS',isset($temp_comp_arr['Adress']) ? $temp_comp_arr['Adress'] : '');
        $tpl->parse('comp_list_','comp_list',true);
        $tpl->parse('comment_list_','comment_list',true);
    } 

    //Выведем меню и сами аказы в случае определения даты.
    if(isset($menu_cont[$show_date])) {
        $old_menu_type = "old";
        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
            $new_menu_type = $menu_val[2];
            $tpl->set_var('header_row_','');
            if($new_menu_type != $old_menu_type) {
                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                $tpl->parse('header_row_','header_row',true);
            }

            $tpl->set_var('client_number_','');
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
                $tpl->parse('client_number_','client_number',true);
            }
            $tpl->set_var('SUMM',$summ);
            $tpl->parse('simple_row_','simple_row',true);
            $tpl->parse('main_row_','main_row',true);
            $old_menu_type = $new_menu_type;
        }
    }
    $tpl->parse('client_block_','client_block',true);
    return $tpl->parse('C', 'main', false);
}

function GetNakForm($show_date,$id_comp) {
    global $this_page, $g_options, $week_days_arr;

    $s_date_arr = split("\.",$show_date);

    $dayw = date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]));
    $file_name = 'menu_'.date("d.m.Y",mktime(0,0,0, $s_date_arr[1], $s_date_arr[0]-$dayw+1, $s_date_arr[2])).'.csv';
    $menu_cont = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name);
    $orders =  get_all_oders_by_date($show_date);
    $menu_types_arr = $g_options->GetOptionList('menu_types');
    $OrgTypes = $g_options->GetOptionList('org_type');

    $CompInfoArr = get_comp_info($id_comp);

    $CompInfo = $OrgTypes[$CompInfoArr['OrgType']].' '.$CompInfoArr['Name'].' '.$CompInfoArr['Adress'];
    $tpl = new Template();
    $tpl->set_file('main',  'print_nakl_form.ihtml');
    $tpl->set_block('main', 'simple_row',    'simple_row_');

    $tpl->set_var('CUR_DATE',$show_date);
    $tpl->set_var('NAK_NUM',$orders[$id_comp]['OrderNum']);
    $tpl->set_var('CLIENT_INFO',$CompInfo);
    $tpl->set_var('MAIN_INFO',$g_options->GetOption('company_title'));

    //Выведем меню и сами аказы в случае определения даты.
    if(isset($menu_cont[$show_date])) {
        $old_menu_type = "old";
        $tot_summ = 0;
        $num_summ = 0;
        $nn=1;
        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
            // Определим количество заказанного блюда.
            $summ = 0;
            if (isset($orders[$id_comp]['OrderListL'][$menu_val[0]]['main'])) {
                $summ = $orders[$id_comp]['OrderListL'][$menu_val[0]]['main'];
            }
            
            if ($summ !=0 ) {
                $money_summ = isset($menu_val[5]) ? $menu_val[5]*$summ : 0;
                $num_summ += $summ;
                $tot_summ += $money_summ;
                
                $tpl->set_var('NUM',$summ);
                $tpl->set_var('WEIGHT',$menu_val[4]);
                $tpl->set_var('TITLE',$menu_val[3]);
                $tpl->set_var('NN',$nn);
                $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                $tpl->set_var('SUMM',$money_summ);
                
                $tpl->parse('simple_row_','simple_row',true);
                ++$nn;
            }
        }
        $tpl->set_var('TOT_SUMM',$tot_summ);
        $tpl->set_var('NUM_SUMM',$nn-1);
        $tpl->set_var('SUMM_WRITE',num2str($tot_summ));

    }
    return $tpl->parse('C', 'main', false);
}

?>