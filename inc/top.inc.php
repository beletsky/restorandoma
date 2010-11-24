<?
################################################################################
# 
# 
#                     
#   Copyright (с) 2003
#                     
#   top.inc.php       
#   Вывод шапки страницы и левой колонки                                       
#                                                                              
################################################################################

    include_once(PATH_TO_ROOT."lib/template.inc.php");

    $tpl = new Template();
# загрузим начало страницы
    $tpl->set_file('main', PATH_TO_ROOT.'tpl/header.tpl');
    $tpl->set_var('TITLE',$PageTitle);
    $tpl->set_var('DESCRIPTION',$PageDescription);
    $tpl->pparse('C', 'main', false);
    unset($tpl);


#Load top menu
    $tpl = new Template();
    $tpl->set_file('top_menu', PATH_TO_ROOT.'tpl/top_menu.tpl');
    $tpl->set_block('top_menu', 'top_menu_row', 'top_menu_row_');

    $TopMenuArr = get_menu_pages('top');
//    $ImgArrPas = array("contacts"=>"icn03a.gif","pay_deliv"=>"icn03b.gif","quest_ans"=>"icn03c.gif","dieta"=>"icn03d.gif");
//    $ImgArrAct = array("contacts"=>"icn03a-over.gif","pay_deliv"=>"icn03b-over.gif","quest_ans"=>"icn03c-over.gif","dieta"=>"icn03d-over.gif");
//    $JavaAdd= 'onmouseover="sc(this, 1, 1);" onmouseout="sc(this, 0, 1);"';

    foreach($TopMenuArr as $val) {
        $tpl->set_var('URL','/'.$val['PageCode'].'.html');
        $tpl->set_var('ALT',isset($val['CatName']) ? $val['CatName'] : '');
//        $tpl->set_var('IMG_NAME', $PageCode == $val['PageCode'] ? $ImgArrAct[$val['PageCode']] : $ImgArrPas[$val['PageCode']]);
//        $tpl->set_var('JAVA_ADD', $PageCode != $val['PageCode'] ? $JavaAdd : '');
        $tpl->parse('top_menu_row_','top_menu_row',true);
    }

//  $tpl->set_block('right_menu', 'right_menu_row', 'right_menu_row_');
    $TopMenu = $tpl->parse('TM', 'top_menu', false);
    unset($tpl); 
    
#Load bottom menu
    $tpl = new Template();
    $tpl->set_file('bottom_menu', PATH_TO_ROOT.'tpl/bottom_menu.tpl');
    $tpl->set_block('bottom_menu', 'bottom_menu_row', 'bottom_menu_row_');

    foreach($TopMenuArr as $val) {
        $tpl->set_var('URL','/'.$val['PageCode'].'.html');
        $tpl->set_var('ALT',isset($val['CatName']) ? $val['CatName'] : '');
        $tpl->parse('bottom_menu_row_','bottom_menu_row',true);
    }

    $BottomMenu = $tpl->parse('BM', 'bottom_menu', false);
    unset($tpl); 

# Load right menu
// 20101113 - Правое меню убираем полностью
//    $tpl = new Template();
//    $RightMenuArr = get_menu_pages('right');
//    $tpl->set_file('right_menu', PATH_TO_ROOT.'tpl/right_menu.tpl');
//    $tpl->set_block('right_menu', 'right_menu_row', 'right_menu_row_');
//    foreach($RightMenuArr as $val) {
//        $tpl->set_var('URL','/'.$val['PageCode'].'.html');
//        $tpl->set_var('ACT_CLASS',$PageCode == $val['PageCode'] ?'class="a01d"' : '');
//        $tpl->set_var('NAME', isset($val['CatName']) ? $val['CatName'] : '');
//        $tpl->parse('right_menu_row_', 'right_menu_row', true);
//    }
//    $PageRightMenu = $tpl->parse('RM', 'right_menu', false);
//    unset($tpl);  
  
#Загрузим календарь
    $tpl = new Template();
    $tpl->set_file('calendar', PATH_TO_ROOT.'tpl/calendar.tpl');
    $tpl->set_block('calendar', 'line', 'line_');
    $tpl->set_block('line', 'one_day', 'one_day_');
    $tpl->set_block('line', 'br', 'br_');
    $tpl->set_block('one_day', 'old_day', 'old_day_');
    $tpl->set_block('one_day', 'active_day', 'active_day_');
    $tpl->set_block('one_day', 'new_day', 'new_day_');

    $s_date_arr = split("\.",$show_date);
    //Номер месяца от 1 до 12 двты showDate
    $current_ts = mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]);
    $dayt = date('t',$current_ts);
    //Имя месяца по английски
    $dayN = date('n',$current_ts);
    $day_cur_w = date('w',mktime(0,0,0,date('m'),date('d'),date('Y')));

    // Обработка названия месяца и стрелок перехода на соседние месяцы.    
    $tpl->set_var('MONTH_NAME',strtoupper($months_array[$dayN]));
    $tpl->set_block('calendar','prev_month','prev_month_');
    $tpl->set_block('calendar','next_month','next_month_');
    if ((date('Y')==$s_date_arr[2] && (date('m')==$s_date_arr[1]-1))
        ||(date('Y')==$s_date_arr[2]-1 && $s_date_arr[1]==1)) {
        $prev_month_days = date( 't', mktime(0,0,0,$s_date_arr[1]-1,1,$s_date_arr[2]) );
        // Показать стрелку предыдущего месяца.
        $tpl->set_var('PREV_MONTH_URL','/'.$PageCode.'/show_date/'.
            date('d.m.Y',mktime(0,0,0,$s_date_arr[1]-1,
            $s_date_arr[0] > $prev_month_days ? $prev_month_days : $s_date_arr[0],
            $s_date_arr[2])));
        $tpl->parse('prev_month_','prev_month');
    } else {
        $tpl->set_var('prev_month_','');
    }
    if ((date('Y')==$s_date_arr[2] && (date('m')==$s_date_arr[1] || date('m')==$s_date_arr[1]+1))
        ||(date('Y')==$s_date_arr[2]+1 && $s_date_arr[1]==12)) {
        $next_month_days = date( 't', mktime(0,0,0,$s_date_arr[1]+1,1,$s_date_arr[2]) );
        // Показать стрелку следующего месяца.
        $tpl->set_var('NEXT_MONTH_URL','/'.$PageCode.'/show_date/'.
            date('d.m.Y',mktime(0,0,0,$s_date_arr[1]+1,
            $s_date_arr[0] > $next_month_days ? $next_month_days : $s_date_arr[0],
            $s_date_arr[2])));
        $tpl->parse('next_month_','next_month');
    } else {
        $tpl->set_var('next_month_','');
    }
    
    $file_name_cur  = 'menu_'.date("d.m.Y",mktime(0,0,0,date('m'),date('d')-$day_cur_w+1,date('Y'))).'.csv';
    $file_name_next = 'menu_'.date("d.m.Y",mktime(0,0,0,date('m'),date('d')-$day_cur_w+8,date('Y'))).'.csv';

    $menu_cont_now  = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name_cur);
    $menu_cont_next = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name_next);

    $dayt_n = ceil($dayt/7);
    for($i=0; $i<$dayt_n;$i++) {
        for($j=1;$j<=7;$j++) {
            $tpl->set_var('new_day_','');
            $tpl->set_var('active_day_','');
            $tpl->set_var('old_day_','');
            $day_n = $i*7+$j;
            if($day_n <= $dayt ) {
                $urldate = date('d.m.Y',mktime(0,0,0,$s_date_arr[1],$day_n,$s_date_arr[2]));
                $tpl->set_var('DAY',$day_n);
                if (mktime(date('H'),1,0,date('m'),date('d')+1,date('Y')) >= mktime(16,1,0,$s_date_arr[1],$day_n,$s_date_arr[2])) {
                    $tpl->parse('old_day_', 'old_day', true);
                } elseif (isset($menu_cont_now[$urldate]) || isset($menu_cont_next[$urldate])) {
                    $tpl->set_var('URL','/menu/show_date/'.$urldate);
                    $tpl->parse('active_day_', 'active_day', true);
                } else {
                    $tpl->parse('new_day_', 'new_day', true);
                }
            }
            $tpl->parse('one_day_', 'one_day', true);
        }
        $tpl->parse('line_', 'line', true);
        $tpl->set_var('one_day_','');   
        if($i<$dayt_n-1) { 
            $tpl->parse('br_', 'br', false);
            $tpl->parse('line_', 'line', true);
            $tpl->set_var('br_','');
        }

    }
    $Calendar = $tpl->parse('CL', 'calendar', false);
    unset($tpl);    

    $tpl = new Template();
    $tpl->set_file('date_block', PATH_TO_ROOT.'tpl/date_block.tpl');
    $tpl->set_var('DATE_WRITE',strtoupper($s_date_arr[0].' '.$months_array_to[$s_date_arr[1]+0].' - '.$week_days_arr[date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]))]));
    $DateWrite = $tpl->parse('DB', 'date_block', false);
    unset($tpl);

#Загрузим хидер (красивый заголовок на странице)
    $tpl = new Template();
//    if ($PageCode == 'index') {
//        $tpl->set_file('header_text', PATH_TO_ROOT.'tpl/index_header_table.tpl');
//        $tpl->set_var('HEADER_BLOCK',isset($PageArray['Name']) ? $PageArray['Name'] : '');
//        $tpl->set_var('PAGE_TEXT',isset($PageArray['TxtEditor']) ? $PageArray['TxtEditor'] : '');
//        $HeaderText = $tpl->parse('ht', 'header_text', false);
//    } else 
    {
        if ($PageCode== 'menu' && !$g_user->IsAuthorized()) {
            $IndexPageArray = get_page_cont_by_id('our_menu');
            $tpl->set_var('PAGE_TITLE',isset($IndexPageArray['Name'])? $IndexPageArray['Name'] : '');
            unset($IndexPageArray);
        } else {
            $tpl->set_var('PAGE_TITLE',isset($PageArray['Name'])? $PageArray['Name'] : '');
        }
        $tpl->set_file('header_text', PATH_TO_ROOT.'tpl/simple_header.tpl');
        $tpl->set_var('DATE_BLOCK',$PageCode != 'menu' ? '' : $DateWrite);
        $HeaderText = $tpl->parse('ht', 'header_text', false);
    }
    unset($tpl);

#Загрузим блок авторизации блок
    $tpl = new Template();
    if ($g_user->IsAuthorized()) {
        $UserMenuArr = get_menu_pages('user_menu');
        $tpl->set_file('user_menu', PATH_TO_ROOT.'tpl/user_menu.tpl');
        $tpl->set_block('user_menu', 'row', 'row_');
        $tpl->set_block('row', 'active', 'active_');
        $tpl->set_block('row', 'inactive', 'inactive_');
        foreach($UserMenuArr as $val) {
            if($g_page->IsResourceAccessible($val['PageCode'])) {
                $tpl->set_var('active_','');
                $tpl->set_var('inactive_','');
                $tpl->set_var('NAME', isset($val['CatName']) ? $val['CatName'] : '');
                if ($PageCode == $val['PageCode'] && $action == '') {
                    $tpl->parse('active_', 'active', true);
                } else {
                    $tpl->set_var('URL','/'.$val['PageCode'].'.html');
                    $tpl->parse('inactive_', 'inactive', true);
                }
                $tpl->parse('row_', 'row', true);
            }
        }
        $AuthBlock = $tpl->parse('UM', 'user_menu', false);
    } else {
        $tpl->set_file('auth', PATH_TO_ROOT.'tpl/auth_login_form.tpl');
        $tpl->set_var('THIS_PAGE',$this_page);
        $tpl->set_var('ERROR', $auth_error);
        $AuthBlock = $tpl->parse('au', 'auth', false);
    }
    unset($tpl);    

#Загрузим блок статуса пользователя блок
    $user_arr = array();
    if ($g_user->IsAuthorized()) {
        $tpl = new Template();
        $tpl->set_file('user_status', PATH_TO_ROOT.'tpl/user_status.tpl');
        $user_arr = get_clientuser_info_by_id($g_user->GetId());
        //print_r($user_arr);
        $tpl->set_var('USER_AMOUNT',isset($user_arr['Amount']) && $user_arr['Amount'] != '' ? $user_arr['Amount'] : '0');
        if(isset($user_arr['FName']) && isset($user_arr['UName'])) {
            if($user_arr['UName'] == '' && $user_arr['FName'] == '' && $user_arr['UserLogin']) {
                $ClientName = $user_arr['UserLogin']; 
            } else {
                $ClientName = $user_arr['FName'].' '.$user_arr['UName']; 
            }
        } elseif (isset($user_arr['UserLogin'])) {
            $ClientName = $user_arr['UserLogin'];
        } else {
            $ClientName = "No Name found";
        } 
        $tpl->set_var('USER_NAME',$ClientName);
        $tpl->set_var('THIS_PAGE',$this_page);
        $tpl->set_var('USER_MENU',$AuthBlock);
        $UserStatus = $tpl->parse('US', 'user_status', false);
        unset($tpl);    
    } else {
        $UserStatus = $AuthBlock;
    }

#Загрузим верхний блок
    $tpl = new Template();
    $tpl->set_file('fird', PATH_TO_ROOT.'tpl/top.tpl');
    $tpl->set_var('THIS_PAGE',$this_page);
    $tpl->set_var('MAIN_MENU',$TopMenu);
    $tpl->pparse('fm', 'fird', false);
    unset($tpl);

#Загрузим верх собранный
    $tpl = new Template();
    $tpl->set_file('topb', PATH_TO_ROOT.'tpl/top_block.tpl');
    $tpl->set_var('THIS_PAGE',$this_page);
    $tpl->set_var('AUTH_BLOCK',$UserStatus);
    $tpl->set_var('ADD_MENU',$AddonMenu);
// 20101113 - Правое меню убираем полностью
//    $tpl->set_var('RIGHT_MENU',$PageRightMenu);
    $tpl->set_var('HEADER_BLOCK',$HeaderText);
    $tpl->pparse('tb', 'topb', false);
    unset($tpl);


#Загрузим футтер в переменную
    $tpl = new Template();
    $tpl->set_file('bottom', PATH_TO_ROOT.'tpl/bottom.tpl');
    $tpl->set_var('CALENDAR',$Calendar);
    $tpl->set_var('BOTTOM_MENU',$BottomMenu);
    $PageFutter = $tpl->parse('bo', 'bottom', false);
    unset($tpl);

?>