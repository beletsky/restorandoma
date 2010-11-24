<?php
//if (($_SERVER['REQUEST_URI'] == "/index.php" || $_SERVER['REQUEST_URI'] == "/index.html") && ($_SERVER['REQUEST_METHOD'] != "POST")) {
//        header('HTTP/1.1 301 Moved Permanently');
//        header("Location: http://{$_SERVER['HTTP_HOST']}/");
//        die();
//}

define ('PATH_TO_ROOT', './');
define ('PATH_TO_ADMIN', './admin/');

require (PATH_TO_ROOT . 'inc/init.inc.php');
include_once(PATH_TO_ROOT . 'inc/stat_page.inc.php');
require (PATH_TO_ROOT . 'inc/xml_func.inc.php');
require (PATH_TO_ADMIN. 'inc/func_orders.inc.php');
require (PATH_TO_ADMIN. 'inc/func_users.inc.php');
require (PATH_TO_ADMIN. 'inc/func_clientcomp.inc.php');
require (PATH_TO_ADMIN. 'inc/func_clientusers.inc.php');

date_default_timezone_set('Europe/Moscow');

$user_message = '';
global $g_options;

#Параметры текущей страницы
$PageTitle = '';
$PageDescription = '';
$PageMainTitle = '';
$MainPageText = '';
$MainPageText_bottom = '';
$SubMenu = '';
$reg_err = '';
$Erros = '';
$EditOrderErrors = '';
$ComUpdateErrors = '';
$UserUpdateErrors = '';
$AddonMenu = '';
$news_on_page = 10;
$SendMessageErrors = '';

$show_date = '';
if (isset($_GET['show_date'])) $show_date = $_GET['show_date'];
if (isset($_POST['show_date'])) $show_date = $_POST['show_date'];
if ($show_date == '' || !string_is_date($show_date)) {
    if (mktime(date('H'), date('i'), 0, date("m"), date("d"), date("Y")) > mktime(16, 1, 0, date("m"), date("d"), date("Y"))) {
        $show_date = date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+2, date("Y")));
    } else {
        $show_date = date("d.m.Y", mktime(0, 0, 0, date("m"), date("d")+1, date("Y")));
    }
}

// Получить рабочую дату.
$s_date_arr = split("\.",$show_date);

//print_r($PageArray);
//Посмотрим чтоже содержит текущая страничка
//print_r($PageArray);

switch ($action) {
    case 'print_menu' : {
        // Выпечатка голого меню.
        $tpl = new Template();
        $tpl->set_file('date_block', PATH_TO_ROOT.'tpl/date_block.tpl');
        $tpl->set_var('DATE_WRITE',strtoupper($s_date_arr[0].' '.$months_array_to[$s_date_arr[1]+0].' - '.$week_days_arr[date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]))]));
        $DateWrite = $tpl->parse('DB', 'date_block', false);
        unset($tpl);
        
        $menu_cont = load_menu();
        if(!$menu_cont) { $Erros = 'Меню отсутствует. Распечатать меню невозможно.'; break; }
        
        $tpl = new Template();
        $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/print_menu.tpl');
        $tpl->set_block('menu_table', 'main_row',      'main_row_');
        $tpl->set_block('main_row',   'header_row',    'header_row_');
        $tpl->set_block('main_row',   'simple_row',    'simple_row_');
        $tpl->set_block('main_row',   'sep_row',       'sep_row_');
        $tpl->set_block('menu_table', 'registr_button','registr_button_');
        $tpl->set_var('DATE',$DateWrite);

        $old_menu_type = "old";
        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
            $new_menu_type = $menu_val[2];
            $tpl->set_var('header_row_','');
            $tpl->set_var('sep_row_','');
            if($new_menu_type != $old_menu_type) {
                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                $tpl->parse('header_row_','header_row',true);
            } 
            $tpl->parse('sep_row_','sep_row',false);
                    
            $tpl->set_var('client_number_','');
            $tpl->set_var('simple_row_','');

            $tpl->set_var('WEIGHT',$menu_val[4]);
            $tpl->set_var('TITLE',$menu_val[3]);
            $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
            $tpl->parse('simple_row_','simple_row',true);
            $tpl->parse('main_row_','main_row',true);
            $old_menu_type = $new_menu_type;
        }
        $MainPageText = $tpl->parse('MT','menu_table',false);
        unset($tpl);

        $tpl = new Template();
        $tpl->set_file('print_page', PATH_TO_ROOT.'tpl/print_page.tpl');
        $tpl->set_var('BODY',$MainPageText);
        $tpl->pparse('PP', 'print_page', false);
                
        exit();
    }
    case 'print_order_form' : {
        // Выпечатка формы заказа.
        
        // Если текущий пользователь не может получить список сотрудников,
        // он не может и печатать форму заказа.
        if (!$g_page->IsResourceAccessible('user_employlist')) break;
        
        $s_date_arr = split("\.",$show_date);
        $user_arr = get_clientuser_info_by_id($g_user->GetId());
        
        $tpl = new Template();
        $tpl->set_file('date_block', PATH_TO_ROOT.'tpl/date_block.tpl');
        $tpl->set_var('DATE_WRITE',strtoupper($s_date_arr[0].' '.$months_array_to[$s_date_arr[1]+0].' - '.$week_days_arr[date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]))]));
        $DateWrite = $tpl->parse('DB', 'date_block', false);
        unset($tpl);
        
        $menu_cont = load_menu();
        if(!$menu_cont) { $Erros = 'Меню для заказа отсутствует. Распечатать бланк заказа невозможно.'; break; }
        $EmployListArr = select_all_company_users($user_arr['IDClientComp']);
        
        $tpl = new Template();
        $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/print_order_form.tpl');
        $tpl->set_block('menu_table', 'client',        'client_');
        $tpl->set_block('menu_table', 'main_row',      'main_row_');
        $tpl->set_block('main_row',   'header_row',    'header_row_');
        $tpl->set_block('main_row',   'simple_row',    'simple_row_');
        $tpl->set_block('simple_row', 'simple_row_cell','simple_row_cell_');
        $tpl->set_block('main_row',   'sep_row',       'sep_row_');
        $tpl->set_block('menu_table', 'registr_button','registr_button_');
        $tpl->set_var('DATE',$DateWrite);

        foreach($EmployListArr as $val) {
            $tpl->set_var('CLIENT_NAME',$val['ClientName']);
            $tpl->parse('client_','client',true);
        }
        $sep_row_colspan = 6 + count($EmployListArr) * 2;
        $tpl->set_var('SEP_ROW_COLSPAN',$sep_row_colspan);
        
        $old_menu_type = "old";
        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
            $new_menu_type = $menu_val[2];
            $tpl->set_var('header_row_','');
            $tpl->set_var('sep_row_','');
            if($new_menu_type != $old_menu_type) {
                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                $tpl->parse('header_row_','header_row',true);
            } 
            $tpl->parse('sep_row_','sep_row',false);
                    
            $tpl->set_var('client_number_','');
            $tpl->set_var('simple_row_','');
            $tpl->set_var('simple_row_cell_','');

            $tpl->set_var('WEIGHT',$menu_val[4]);
            $tpl->set_var('TITLE',$menu_val[3]);
            $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
            for ($i = 0; $i < count($EmployListArr); ++$i) $tpl->parse('simple_row_cell_','simple_row_cell', true);
            $tpl->parse('simple_row_','simple_row',true);
            $tpl->parse('main_row_','main_row',true);
            $old_menu_type = $new_menu_type;
        }
        $MainPageText = $tpl->parse('MT','menu_table',false);
        unset($tpl);

        $tpl = new Template();
        $tpl->set_file('print_page', PATH_TO_ROOT.'tpl/print_page.tpl');
        $tpl->set_var('BODY',$MainPageText);
        $tpl->pparse('PP', 'print_page', false);
                
        exit();
    }
    case 'register_comp': {
        $form['GroupCode'] = 'ClientAdmins';
        $form['UserLogin'] = $form['Mail'];
        $form['UserPwd']   = $g_passowrd->create();
        if (isset($_POST['Birthdate'])) $form['Birthdate'] = date_to_sql(get_date_from_select($_POST['Birthdate']));
        $form_u = $form;    
        $form_c = $form;
        $form_u['Name'] = $form_u['NName'];
        $reg_err = reg_new_comp($form_u,$form_c);
        if($reg_err == '') {
            $g_user->Login($form['UserLogin'], $form['UserPwd']);
            $g_page->mUser = $g_user;
            setcookie('user_login', $form['UserLogin'], time() + $cookie_life_time, '/');
            setcookie('user_pwd', $form['UserPwd'], time() + $cookie_life_time, '/');
            setcookie('user_last_time',time(), time() + $cookie_life_time, '/');
            $g_page->mUser = $g_user;
            $PageCode = 'user_index';
            log_user_login($form['UserLogin'],time());
        } else {
            $PageCode = 'registration';
        }
    break;
    }
    case 'add_client_user' : {
        $form['UserLogin'] = $form['Mail'];
        $form['UserPwd']   = $g_passowrd->create();
        if (isset($_POST['Birthdate'])) $form['Birthdate'] = date_to_sql(get_date_from_select($_POST['Birthdate']));
        $UserUpdateErrors = reg_new_clientuser($form);
        // Прикинуться, как будто данные были прочитаны из базы данных.
        $form['Status'] = isset($form['Status']) && $form['Status'] != '' ? 1 : 0;
    break;
    }
    case 'update_client_user' : {
        if (isset($_POST['Birthdate'])) $form['Birthdate'] = date_to_sql(get_date_from_select($_POST['Birthdate']));
        $UserUpdateErrors = update_clientuser($form);
        // Прикинуться, как будто данные были прочитаны из базы данных.
        $form['Status'] = isset($form['Status']) && $form['Status'] != '' ? 1 : 0;
    break;
    }
    case 'add_user_amount' : {
        $AddUserAmountErrors = clientuser_add_amount($form['IDClientUser'],$form['Amount']);
        if($AddUserAmountErrors) $action = 'add_amount_proc';
    break;
    }
    case 'send_user_question' : {
        if( !isset( $_POST['Person'] ) || !isset( $_POST['Email'] ) || !isset( $_POST['Message'] ) ) break;
        mail2admin('"'.$_POST['Person'].'" <'.$_POST['Email'].'>','Вопрос пользователя с сайта RestoranDoma.ru', 
        'Имя - '.$_POST['Person'].".\nКонтакты - ".$_POST['Email'].".\n\n".$_POST['Message'], 
        'info@restorandoma.ru, restorandoma@mail.ru');
    break;
    }
    case 'send_message' :
    case 'send_message_all' : {
        if (!isset($form['Subject']) || $form['Subject'] == '') {
            $SendMessageErrors = 'Необходимо ввести тему сообщения!<br>';
            $action .= '_proc';
        } elseif (!isset($form['Message']) || $form['Message'] == '') {
            $SendMessageErrors = 'Необходимо ввести текст сообщения!<br>';
            $action .= '_proc';
        } else {
            $message = isset($form['Message']) ? $form['Message'] : '';
            $message = ereg_replace("\(Дата\)",date("d.m.Y"),$message);
            $message = ereg_replace("\(ФИО\)", isset($form['FIO']) ? $form['FIO'] : '',$message);
            $message = ereg_replace("\(ФИ\)", isset($form['FI']) ? $form['FI'] : '',$message);
            $message = ereg_replace("\(И\)", isset($form['I']) ? $form['I'] : '',$message);
            mail2user($form['MailsList'],$form['Subject'],$message,$form);
        }
    break;
    }
    case 'edit_order': {
        $total_summ = 0;
        for($i=0; $i<count($_POST['ItemID']); $i++) {
            $form['OrderListL'][$_POST['ItemID'][$i]]['price'] = $_POST['ItemPrice'][$i];
            $form['OrderListL'][$_POST['ItemID'][$i]]['main'] = $_POST['ItemNum'][$i];
            $form['OrderListL'][$_POST['ItemID'][$i]]['status'] = isset($_POST['ItemStatus'][$i]) ? $_POST['ItemStatus'][$i] : '';
            if (string_is_int($_POST['ItemNum'][$i])) {
                $total_summ += $_POST['ItemNum'][$i]*$_POST['ItemPrice'][$i];
            }
        }
        $form['Summ'] = $total_summ;
//        $db->Lock(array('dwClientUsers','dwOrders'));
        $is_new = (isset($form['IDOrder']) && $form['IDOrder'] != 0) ? false : true;
        $EditOrderErrors .= order_edit($form,$is_new);
//        $db->UnLock();
        header("Location: /confirm_order/a/after_edit_order/show_date/".$form['OrderDate']."/form[IDOrder]/".$form['IDOrder']);
    break;
    }
    case 'confirm_order' : {
        change_order_status($form['IDOrder'],1);
        $PageCode = 'user_myorders';
        $Message = '<H1>Ваш заказ успешно отправлен.</H1>';
    break;
    }
    case 'update_company' : {
        $ComUpdateErrors = clientcomp_edit($form,false);
        if($ComUpdateErrors) $action = 'edit_proc';
    break;
    }
    case 'confirm_comp_order' : {
        $CompOrdArr = get_all_orders_by(0,0,$show_date,$form['IDClietnComp']);
        foreach($CompOrdArr as $v) $id_arr[] = $v['IDOrder'];
        change_order_status($id_arr,2);
        change_order_comments($form['Comments'],$id_arr[count($id_arr)-1]);
    break;  
    }
    case 'send_advert_question' : {
        if( !isset( $form['email'] ) || $form['email'] == '' )
        {
            $SendMessageErrors = 'Укажите Ваш электронный адрес!<br>';
            $action = '';
            break;
        }
        
        mail2admin('"'.$form['person'].'" <'.$form['email'].'>','Запрос о рекламе на сайте RestoranDoma.ru', 
        'Имя - '.$form['person'].".\nКонтакты - ".$form['email'].".\n\n".$form['message'], 
        'info@restorandoma.ru, restorandoma@mail.ru');
    break;
    }
    case 'send_feedback' : {
        if( !isset( $form['email'] ) || $form['email'] == '' )
        {
            $SendMessageErrors = 'Укажите Ваш электронный адрес!<br>';
            $action = '';
            break;
        }
        
        mail2admin('"'.$form['person'].'" <'.$form['email'].'>','Отзыв о сайте RestoranDoma.ru', 
        'Имя - '.$form['person'].".\nКонтакты - ".$form['email'].".\n\n".$form['message'], 
        'info@restorandoma.ru, restorandoma@mail.ru');
    break;
    }
    case 'send_password' : {
        if (isset($form['email']) && $form['email'] != '') {
            $SendPasswordErrors = send_client_password($form['email']);
        } else $SendPasswordErrors = 'Необходимо указать электронный адрес!';
    break;
    }
    case 'search_archive' : {
        if (isset($_POST['StartDate']) && isset($_POST['EndDate'])) {
            $form['StartDate'] = get_date_from_select($_POST['StartDate']);
            $form['EndDate'] = get_date_from_select($_POST['EndDate']);
            $d = split("\.",$form['StartDate']);
            if(!checkdate($d[1],$d[0],$d[2])) $start_date = mktime(0,0,0,$d[1]+1,1,$d[2]);
            else $start_date = mktime(0,0,0,$d[1],$d[0],$d[2]);
            $d = split("\.",$form['EndDate']);
            if(!checkdate($d[1],$d[0],$d[2])) $end_date = mktime(0,0,0,$d[1]+1,1,$d[2]);
            else $end_date = mktime(0,0,0,$d[1],$d[0],$d[2]);
            if ($start_date > $end_date) {
                $ArchiveErrors = 'Начальная дата должна быть меньше конечной!';
                $PageCode = $PageCode.'_archive';
            }
            $form['StartDate'] = date('d.m.Y',$start_date);
            $form['EndDate'] = date('d.m.Y',$end_date);
        } else $PageCode = $PageCode.'_archive';
    break;
    }
}

$user_arr = array();
if ($g_user->IsAuthorized()) {
    $user_arr = get_clientuser_info_by_id($g_user->GetId());
}

$PageArray = array();
$PageArray = get_page_cont_by_id($PageCode);    

$MainPageText = isset($PageArray['TxtEditor']) ? $PageArray['TxtEditor'] : '';
$PageTitle = isset($PageArray['Title']) ? $PageArray['Title'] : 'РесторанДома';
$PageDescription = isset($PageArray['Description']) ? $PageArray['Description'] : 'РесторанДома';

if (isset($PageArray['PageType']) && $PageArray['PageType'] == 'din') {
    switch ($PageCode) {
        case 'index' : {
//            $tpl = new Template();
//            $tpl->set_file('main_index', PATH_TO_ROOT.'tpl/index.tpl');
//            $tpl->set_var('THIS_PAGE', $this_page);

//            {
//                $temp_page = get_page_cont_by_id('clients');
//                $tpl->set_var('CLIENTS_ANNOUNCE', isset($temp_page['Coment']) ? $temp_page['Coment'] : '');
//                unset($temp_page);
//            }
//            {
//                $temp_page = get_page_cont_by_id('delivery');
//                $tpl->set_var('DELIVERY_ANNOUNCE', isset($temp_page['Coment']) ? $temp_page['Coment'] : '');
//                unset($temp_page);
//            }
//            {
//                $temp_page = get_page_cont_by_id('coorp');
//                $tpl->set_var('COORP_ANNOUNCE', isset($temp_page['Coment']) ? $temp_page['Coment'] : '');
//                unset($temp_page);
//            }
//            {
//                $temp_page = get_page_cont_by_id('responses');
//                $tpl->set_var('RESPONSES_ANNOUNCE', isset($temp_page['Coment']) ? $temp_page['Coment'] : '');
//                unset($temp_page);
//            }
//            
//            $MainPageText = $tpl->parse('MI','main_index',false);
//            unset($tpl);
        break;
        }
        case 'user_index' : {
            if ($g_user->IsAuthorized()) {
                $tpl = new Template();
                $tpl->set_file('user_question', PATH_TO_ROOT.'tpl/user_question.tpl');

                $OneUserArr = get_clientuser_info_by_client_id($user_arr['IDClientUser']);
                if ($OneUserArr['GroupCode'] == 'ClientAdmins') {
                    $temp_page = get_page_cont_by_id($PageCode.'_manager');
                    if (isset($temp_page['TxtEditor'])) $MainPageText = $temp_page['TxtEditor'];
                    unset($temp_page);
                }

                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('HEADER', 'Здравствуйте, '.$OneUserArr['FName'].'!');
                $tpl->set_var('TEXT', $MainPageText);
                $tpl->set_var('PERSON', $OneUserArr['ClientName']);
                $tpl->set_var('EMAIL', $OneUserArr['UserLogin']);
                $tpl->set_var('MESSAGE', '');
                
                $MainPageText = $tpl->parse('UQ','user_question',false);
                unset($OneUserArr);
                unset($tpl);
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;
        }
        case 'registration' : {
            $tpl = new Template();
            $tpl->set_file('reg', PATH_TO_ROOT.'tpl/company_registration.tpl');
            $tpl->set_block('reg','pay_type','pay_type_');
            $tpl->set_block('reg','errors','errors_');
            $pay_arr = $g_options->GetOptionList('payment_type');
            $i = 0;
            $tpl->set_var('THIS_PAGE', $this_page);
            $tpl->set_var('PAGE_TEXT', $MainPageText);
            if ($reg_err != '') {
                $tpl->set_var('REG_ERRORS', 'Были обнаружены следующие ошибки:<br/><br/>'.$reg_err);
                $tpl->parse('errors_','errors');
            } else $tpl->set_var('errors_','');
            foreach ($pay_arr as $k => $val) {
                $tpl->set_var('PAY_TITLE', $val);
                $tpl->set_var('PAY_VAL', $k);
                $tpl->set_var('CHECKED', $i == 0 ? 'checked' : '');
                $i++;
                $tpl->parse('pay_type_','pay_type',true);
            }
            $tpl->set_var('ORG_TYPES_OPTIONS',get_select_options(isset($form['OrgType']) && string_is_id($form['OrgType']) ? $form['OrgType'] : 0, $g_options->GetOptionList('org_type'), false)); 
            $tpl->set_var('NAME',isset($form['Name']) ? $form['Name'] : '');
            $tpl->set_var('NNAME',isset($form['NName']) ? $form['NName'] : '');
            $tpl->set_var('FNAME',isset($form['FName']) ? $form['FName'] : '');
            $tpl->set_var('ONAME',isset($form['OName']) ? $form['OName'] : '');
            $tpl->set_var('BDATE_SELECT', create_date_selcet('Birthdate',80,isset($form['Birthdate']) ? sql_to_date($form['Birthdate']) : '',1930,'s03'));
            $tpl->set_var('POSITION',isset($form['Position']) ? $form['Position'] : '');
            $tpl->set_var('MAIL',isset($form['Mail']) ? $form['Mail'] : '');
            $tpl->set_var('WEBURL',isset($form['WebUrl']) ? $form['WebUrl'] : '');
            $tpl->set_var('PHONE',isset($form['Phone']) ? $form['Phone'] : '');
            $tpl->set_var('METRO',isset($form['Metro']) ? $form['Metro'] : '');
            $tpl->set_var('ADRESS',isset($form['Adress']) ? $form['Adress'] : '');
            $tpl->set_var('EMPLOYERSNUM',isset($form['EmployersNum']) ? $form['EmployersNum'] : '');
            $tpl->set_var('DELIV_TIME_OPTIONS',get_select_options(isset($form['DeliveryTime']) && string_is_id($form['DeliveryTime']) ? $form['DeliveryTime'] : 0, $g_options->GetOptionList('deliv_time'), false)); 

            $MainPageText = $tpl->parse('R', 'reg', false);
            unset($tpl);
        break;
        }
        case 'menu' : {
            if ($action=='') {
                //Номер дня недели 
                $menu_cont = load_menu();
                $tpl = new Template();
                if ($g_user->IsAuthorized()) {
                    // Попытаться получить заказ пользователя с указанным номером на указанную дату.
                    $oder = get_all_oders_by_date($show_date,isset($form['IDOrder']) ? $form['IDOrder'] : 0, array(), $user_arr['IDClientUser']);
                    if (isset($oder[$user_arr['IDClientComp']]['OrderListL']) && count($oder[$user_arr['IDClientComp']]['OrderListL']) > 0) {
                        $form['OrderListL'] = $oder[$user_arr['IDClientComp']]['OrderListL'];
                        $show_date = sql_to_date($oder[$user_arr['IDClientComp']]['OrderDate']);
                        $total_summ = $oder[$user_arr['IDClientComp']]['Summ'];
                        if (!isset($form['IDOrder'])) $form['IDOrder'] = $oder[$user_arr['IDClientComp']]['IDOrder'];
                    }

                    // Если заказ обнаружен, но уже не в состоянии "формируется",
                    // отказать в его правке и направить пользователя на страницу просмотра.
                    if (count($oder) > 0 && ($oder[$user_arr['IDClientComp']]['Status'] != 0 && $oder[$user_arr['IDClientComp']]['Status'] != 1)) {
                        $MainPageText = show_message('Ваш заказ на '.show_date_as_string().' уже '.
                            $oder_statuses_arr[$oder[$user_arr['IDClientComp']]['Status']].
                            ' и не может быть исправлен!',
                            'Просмотреть содержимое этого заказа вы можете <a href=/'.
                            'confirm_order/form[IDOrder]/'.$oder[$user_arr['IDClientComp']]['IDOrder'].'/show_date/'.$show_date.
                            '>здесь</a>.');
                        break;
                    }
                    
                    // Если создается новый заказ, то проверить, можно ли его создавать на указанную дату.
                    if (count($oder) == 0 && mktime(date('H'),1,0,date('m'),date('j')+1,date('Y')) >= mktime(16,1,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2])) {
                        $MainPageText = show_message('Прием заказов на '.show_date_as_string().' уже закрыт!',
                            'Вы можете оформить заказ на завтра <a href=/menu.html>здесь</a>.');
                        break;
                    }
                                        
                    $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/menu_order_table.tpl');
                    $tpl->set_block('menu_table',       'main_row',      'main_row_');
                    $tpl->set_block('main_row',   'simple_row',    'simple_row_');

                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('PAGE_TEXT', $MainPageText);
                    $tpl->set_var('IDCLIENTUSER',$user_arr['IDClientUser']);
                    $tpl->set_var('SHOW_DATE',$show_date);
                    $tpl->set_var('IDORDER',isset($form['IDOrder']) ?  $form['IDOrder'] : 0);
                    $tpl->set_var('BTN_CAPTION',(isset($form['IDOrder']) &&  string_is_id($form['IDOrder'])) ? 'Сохранить данные' : 'Сделать заказ');   
                    $old_menu_type = 'old';
                    if(isset($menu_cont[$show_date])) {
                        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
                            $new_menu_type = $menu_val[2];
//                            $tpl->set_var('main_row_','');
                            if($new_menu_type != $old_menu_type && $old_menu_type != 'old') {
                                $tpl->set_var('HEADER',$menu_types_arr[$old_menu_type]);
                                $tpl->parse('main_row_','main_row',true);
                                $tpl->set_var('simple_row_','');
                            } 
                            $tpl->set_var('WEIGHT',$menu_val[4]);
                            $tpl->set_var('TITLE',$menu_val[3]);
                            $tpl->set_var('CODE',$menu_val[0]);
                            $tpl->set_var('NUM', isset($form['OrderListL'][$menu_val[0]]['main']) ? $form['OrderListL'][$menu_val[0]]['main'] : '');
                            $tpl->set_var('INGREDIENTS',isset($menu_val[6]) ? $menu_val[6] : '');
                            $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                            $tpl->parse('simple_row_','simple_row',true);
                            $old_menu_type = $new_menu_type;
                        }
                        $tpl->set_var('HEADER',$menu_types_arr[$old_menu_type]);
                    } else {
                        $tpl->set_var('HEADER',ERR_MENU_CONT);
                    }
                    $tpl->parse('main_row_','main_row',true);
                    $MainPageText = $tpl->parse('MT','menu_table',false);
                } else {
                    $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/menu_simple_table.tpl');
                    $tpl->set_block('menu_table', 'main_row',      'main_row_');
                    $tpl->set_block('main_row',   'header_row',    'header_row_');
                    $tpl->set_block('main_row',   'simple_row',    'simple_row_');
                    $tpl->set_block('main_row',   'sep_row',       'sep_row_');
                    $tpl->set_block('menu_table', 'registr_button','registr_button_');

                    $our_menu_page = get_page_cont_by_id('our_menu');
                    
                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('PAGE_TEXT', isset($our_menu_page['TxtEditor']) ? $our_menu_page['TxtEditor'] : '');
                    if (!$g_user->IsAuthorized()) $tpl->parse('registr_button_','registr_button',false);

                    $old_menu_type = "old";
                    if(isset($menu_cont[$show_date])) {
                        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
                            $new_menu_type = $menu_val[2];
                            $tpl->set_var('header_row_','');
                            $tpl->set_var('sep_row_','');
                            if($new_menu_type != $old_menu_type) {
                                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                                $tpl->parse('header_row_','header_row',true);
                            } 
                            $tpl->parse('sep_row_','sep_row',false);
                                    
                            $tpl->set_var('client_number_','');
                            $tpl->set_var('simple_row_','');
                
                            $tpl->set_var('WEIGHT',$menu_val[4]);
                            $tpl->set_var('TITLE',$menu_val[3]);
                            $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                            $tpl->parse('simple_row_','simple_row',true);
                            $tpl->parse('main_row_','main_row',true);
                            $old_menu_type = $new_menu_type;
                        }
                    }
                    $MainPageText = $tpl->parse('MT','menu_table',false);
                }
                unset($tpl);
                    
                $arr['url'] = '/menu/a/print_menu/show_date/'.$show_date;
                $arr['title'] = 'Распечатать меню';
                $arr['active'] = 0;
                $AddonMenuArr[] = $arr;
                
                if ($g_page->IsResourceAccessible('user_employlist')) {
                    $arr['url'] = '/menu/a/print_order_form/show_date/'.$show_date;
                    $arr['title'] = 'Распечатать форму заказа';
                    $arr['active'] = 0;
                    $AddonMenuArr[] = $arr;
                }
                
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,($g_user->IsAuthorized()) ? 'Заказать обед' : 'Меню на сегодня',$PageCode);
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
        break;
        }
        case 'menu_full' : {
            if ($action=='') {
                //Номер дня недели 
                $menu_cont = load_menu();
                $tpl = new Template();
                
                $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/menu_week_table.tpl');
                $tpl->set_block('menu_table', 'day',           'day_');
                $tpl->set_block('day',        'main_row',      'main_row_');
                $tpl->set_block('main_row',   'header_row',    'header_row_');
                $tpl->set_block('main_row',   'simple_row',    'simple_row_');
                $tpl->set_block('main_row',   'sep_row',       'sep_row_');
                $tpl->set_block('menu_table', 'registr_button','registr_button_');

                $our_menu_page = get_page_cont_by_id('menu_full');
                
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('PAGE_TEXT', isset($our_menu_page['TxtEditor']) ? $our_menu_page['TxtEditor'] : '');
                if (!$g_user->IsAuthorized()) $tpl->parse('registr_button_','registr_button',false);

                $date_tpl = new Template();
                $date_tpl->set_file('date_block', PATH_TO_ROOT.'tpl/date_block.tpl');
                foreach( $menu_cont as $k => $v )
                {
                    $s_date_arr = split("\.",$k);
                    $date_tpl->set_var('DATE_WRITE',strtoupper($s_date_arr[0].' '.$months_array_to[$s_date_arr[1]+0].' - '.$week_days_arr[date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]))]));
                    $tpl->set_var( 'DATE_BLOCK', $date_tpl->parse('DB', 'date_block', false) );
    
                    $old_menu_type = "old";
                    if($v) {
                        foreach($v as $menu_k => $menu_val) {
                            $new_menu_type = $menu_val[2];
                            $tpl->set_var('header_row_','');
                            $tpl->set_var('sep_row_','');
                            if($new_menu_type != $old_menu_type) {
                                $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                                $tpl->parse('header_row_','header_row',true);
                            } 
                            $tpl->parse('sep_row_','sep_row',false);
                                    
                            $tpl->set_var('client_number_','');
                            $tpl->set_var('simple_row_','');
                
                            $tpl->set_var('WEIGHT',$menu_val[4]);
                            $tpl->set_var('TITLE',$menu_val[3]);
                            $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                            $tpl->parse('simple_row_','simple_row',true);
                            $tpl->parse('main_row_','main_row',true);
                            $old_menu_type = $new_menu_type;
                        }
                    
                        $tpl->parse('day_','day',true);
                        $tpl->set_var( 'main_row_', '' );
                    }
                }
                $MainPageText = $tpl->parse('MT','menu_table',false);
                
                unset($tpl);
                    
                $AddonMenuArr = array();
                
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,($g_user->IsAuthorized()) ? 'Заказать обед' : 'Меню на сегодня','menu');
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
        break;
        }
        case 'confirm_order' : {
            if ($g_user->IsAuthorized()) {
                $menu_cont = load_menu();
                $tpl = new Template();

                $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/menu_order_confirm_table.tpl');
                $tpl->set_block('menu_table', 'update_block', 'update_block_');
                $tpl->set_block('update_block', 'send_order_button', 'send_order_button_');
                $tpl->set_block('menu_table', 'main_row',     'main_row_');
                $tpl->set_block('menu_table', 'error_block',  'error_block_');
                $tpl->set_block('menu_table', 'date_status',  'date_status_');
                $tpl->set_block('menu_table', 'return_button','return_button_');
                $tpl->set_block('menu_table', 'return_employ_list_button','return_employ_list_button_');
                $tpl->set_block('menu_table', 'return_user_account_button','return_user_account_button_');
                $tpl->set_block('main_row',   'header_row',   'header_row_');
                $tpl->set_block('main_row',   'simple_row',   'simple_row_');
                $tpl->set_block('main_row',   'sep_row',      'sep_row_');

                $tpl->set_var('IDCLIENTUSER',$user_arr['IDClientUser']);
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('PAGE_TEXT', $MainPageText);
                $tpl->set_var('SHOW_DATE',$show_date);
                $tpl->set_var('DATE_STRING',show_date_as_string());
                $tpl->set_var('IDORDER',isset($form['IDOrder']) ? $form['IDOrder'] : 0);
                if (isset($EditOrderErrors) && $EditOrderErrors != '') {
                    $tpl->set_var('ERRORS', $EditOrderErrors);
                    $tpl->parse('error_block_','error_block',false);
                } else $tpl->set_var('error_block_','');

                $oder = isset($form['IDOrder']) ? get_all_oders_by_date($show_date,$form['IDOrder']) : array();
                if (count($oder)) {
                    $form['OrderListL'] = $oder[$user_arr['IDClientComp']]['OrderListL'];
                    $show_date = sql_to_date($oder[$user_arr['IDClientComp']]['OrderDate']);
                    $total_summ = $oder[$user_arr['IDClientComp']]['Summ'];
                    
                    $tpl->set_var('SUMM',$total_summ);
                    $tpl->set_var('STATUS', isset($oder_statuses_arr[$oder[$user_arr['IDClientComp']]['Status']]) ? $oder_statuses_arr[$oder[$user_arr['IDClientComp']]['Status']] : '');
                    
                    $old_menu_type = "old";
                    if(isset($menu_cont[$show_date])) {
                        foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
                            $id = $menu_val[0];
                            if (isset($form['OrderListL'][$id]) && string_is_int($form['OrderListL'][$id]['main']) ) {
                                $new_menu_type = $menu_val[2];
                                $tpl->set_var('header_row_','');
                                $tpl->set_var('sep_row_','');
                                if($new_menu_type != $old_menu_type) {
                                    $AddElement = false;
                                    $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                                    $tpl->parse('header_row_','header_row',true);
                                } 
                                $tpl->set_var('client_number_','');
                                $tpl->set_var('simple_row_','');
                
                                $tpl->set_var('WEIGHT',$menu_val[4]);
                                $tpl->set_var('TITLE',$menu_val[3]);
                                $tpl->set_var('NUM',$form['OrderListL'][$menu_val[0]]['main']);
                                $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                                $tpl->parse('simple_row_','simple_row',true);

                                $tpl->parse('sep_row_','sep_row',false);
                                $tpl->parse('main_row_','main_row',true);
                                $old_menu_type = $new_menu_type;
                            }       
                        }
                        
                        $OVal = $oder[$user_arr['IDClientComp']];
                        if((isset($OVal['Status']) && ($OVal['Status'] == 0 || $OVal['Status'] == 1))
                             && $action != 'order_employ_view') {
                            if ($action != 'after_edit_order') {
                                $tpl->set_var('PAGE_TEXT','');
                                $tpl->set_var('send_order_button_','');
                            } else {
                                $tpl->parse('send_order_button_','send_order_button',false);
                                $tpl->set_var('PAGE_TEXT', $MainPageText);
                            }
                            $tpl->parse('update_block_','update_block',false);
                        } else {
                            $tpl->set_var('PAGE_TEXT','');
                            $tpl->set_var('update_block_','');
                        }
                    } else {
                        $tpl->set_var('PAGE_TEXT', $MainPageText);
                        $tpl->set_var('HEADER',ERR_MENU_CONT);
                        $tpl->parse('header_row_','header_row',true);
                        $tpl->parse('main_row_','main_row',true);
                        $tpl->set_var('update_block_','');
                    }
                        
                    $tpl->parse('date_status_','date_status',false);
                    
                    if ($action == 'after_edit_order') {
                        $tpl->set_var('return_button_','');
                        $tpl->set_var('return_employ_list_button_','');
                    } elseif ($action == 'order_employ_view') {
                        $tpl->parse('return_employ_list_button_','return_employ_list_button',false);
                        $tpl->set_var('return_user_account_button_','');
                        $tpl->set_var('return_button_','');
                    } elseif ($action == 'user_account_view') {
                        $tpl->set_var('return_employ_list_button_','');
                        $tpl->parse('return_user_account_button_','return_user_account_button',false);
                        $tpl->set_var('return_button_','');
                    } else {
                        $tpl->set_var('return_employ_list_button_','');
                        $tpl->set_var('return_user_account_button_','');
                        $tpl->parse('return_button_','return_button',false);
                    }
                } else {
                    $tpl->set_var('PAGE_TEXT','<H1>'.ERR_NO_ORDER.'</H1>');
                    $tpl->set_var('HEADER','');
                    $tpl->parse('header_row_','header_row',true);
                    $tpl->parse('main_row_','main_row',true);
                    $tpl->set_var('update_block_','');
                    $tpl->set_var('date_status_','');
                    if ($action == 'order_employ_view') {
                        $tpl->parse('return_employ_list_button_','return_employ_list_button',false);
                        $tpl->set_var('return_user_account_button_','');
                        $tpl->set_var('return_button_','');
                    } elseif ($action == 'user_account_view') {
                        $tpl->set_var('return_employ_list_button_','');
                        $tpl->parse('return_user_account_button_','return_user_account_button',false);
                        $tpl->set_var('return_button_','');
                    } else {
                        $tpl->set_var('return_employ_list_button_','');
                        $tpl->set_var('return_user_account_button_','');
                        $tpl->parse('return_button_','return_button',false);
                    }
                }
                
                $MainPageText = $tpl->parse('MT','menu_table',false);
                unset($tpl);
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;
        }
        case 'user_myorders' : {
            if ($g_user->IsAuthorized()) {
                if ($action == 'search_archive' && isset($form['StartDate']) && isset($form['EndDate'])) {
                    $show_archive = true;
                } else {
                    $show_archive = false;
                    $form['StartDate'] = date("d.m.Y");
                    // Очень далеко вперед.
                    $form['EndDate'] = date("d.m.Y",mktime(0,0,0,12,31,2037));
                }
                $form['StartDate'] = date_to_sql($form['StartDate']);
                $form['EndDate'] = date_to_sql($form['EndDate']);
                
                $OrdersListArr = get_all_orders_between_dates($user_arr['IDClientUser'],$form['StartDate'],$form['EndDate']);

                $tpl = new Template();
                $tpl->set_file('orders_list', PATH_TO_ROOT.'tpl/user_orders_list.tpl');
                $tpl->set_block('orders_list', 'one_order', 'one_order_');
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGE_CODE','confirm_order');
                if ($show_archive) {
                    if (count($OrdersListArr)) {
                        $tpl->set_var('PAGE_TEXT','<H1>Результаты поиска по архиву заказов с '.
                            sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                    } else {
                        $tpl->set_var('PAGE_TEXT','<H1>Извините, у Вас нет заказов за период с '.
                            sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                    }
                } elseif ($action == 'confirm_order' && isset($Message) && $Message != '') $tpl->set_var('PAGE_TEXT',$Message);
                else $tpl->set_var('PAGE_TEXT',$MainPageText);
                foreach($OrdersListArr as $k => $val) {
                    $status = $val['Status'];
                    $tpl->set_var('IDORDER',$val['IDOrder']);
                    $tpl->set_var('STATUS', isset($oder_statuses_arr[$status]) ? $oder_statuses_arr[$status] : '');
                    $tpl->set_var('DATE',sql_to_date($val['OrderDate']));
                    $tpl->set_var('ORDER_DATE',sql_to_date($val['OrderDate']));
                    $tpl->set_var('STAT_CLASS', ($status != 4 && $status != 5) ? 'u05y_1' : 'u05y');
                    $tpl->set_var('CIR_PIC',($status != 4 && $status != 5) ? 'pic01k_1.gif' : 'pic01k.gif');
                    $tpl->parse('one_order_','one_order',true);
                }
                $MainPageText = $tpl->parse('OL','orders_list',false);
                unset($tpl);

                $AddonMenuArr = array();
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,'Архив моих заказов','user_myorders_archive');
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
        break;
        }
        case 'user_account': {
            if ($g_user->IsAuthorized()) {
                if ($action == 'search_archive' && isset($form['StartDate']) && isset($form['EndDate'])) {
                    $show_archive = true;
                } else {
                    $show_archive = false;
                    $form['StartDate'] = date("d.m.Y",mktime(0,0,0,date('n')-1,date('j'),date('Y')));
                    // Очень далеко вперед.
                    $form['EndDate'] = date("d.m.Y",mktime(0,0,0,12,31,2037));
                }
                $form['StartDate'] = date_to_sql($form['StartDate']);
                $form['EndDate'] = date_to_sql($form['EndDate']);
                
                $HistoryAccArr = get_account_history($form['StartDate'],$form['EndDate'],$user_arr['IDClientUser']);

                $tpl = new Template();
                $tpl->set_file('account_list', PATH_TO_ROOT.'tpl/account_list.tpl');
                $tpl->set_block('account_list', 'account_op_item', 'account_op_item_');
                if ($show_archive) {
                    if (count($HistoryAccArr)) {
                        $tpl->set_var('PAGE_TEXT','<H1>Результаты поиска по архиву операций по личному счету с '.
                            sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                    } else {
                        $tpl->set_var('PAGE_TEXT','<H1>Извините, у Вас нет операций по личному счету за период с '.
                            sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                    }
                } else $tpl->set_var('PAGE_TEXT',$MainPageText);
                $TotalAdd = 0;
                $TotalMinus = 0;
                foreach($HistoryAccArr as $k => $val) {
                    if ($val['OperationType'] == 'add') {
                        $OpType = 'начислено';
                        $TotalAdd += abs($val['Summ']);
                    } else {
                        $OpType = 'списано<br><a href="'.
                            '/confirm_order/form[IDOrder]/'.$val['IDOrder'].
                            '/show_date/'.sql_to_date($val['OrderDate']).'/a/user_account_view">'.
                            'Заказ №'.$val['OrderNum'].'<br>от '.sql_to_date($val['OrderDate']).'</a>';
                        $TotalMinus += abs($val['Summ']);
                    }
                    $tpl->set_var('OP_DATE',sql_to_date($val['OperationDate']));
                    $tpl->set_var('OP_SUMM', isset($val['Summ']) ? abs($val['Summ']) : '');
                    $tpl->set_var('OP_TYPE',$OpType);
                    $tpl->set_var('AC_LEFT',$val['LeftSum']);
                    $tpl->parse('account_op_item_','account_op_item',true);
                }
                $tpl->set_var('CUR_LEFT',isset($user_arr['Amount']) && $user_arr['Amount'] != '' ? $user_arr['Amount'] : '0');
                $tpl->set_var('TOTAL_MINUS',$TotalMinus);
                $tpl->set_var('TOTAL_ADD',$TotalAdd);

                $MainPageText = $tpl->parse('AL','account_list',false);
                unset($tpl);

                $AddonMenuArr = array();
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,'Архив личного счета','user_account_archive');
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;
        }
        case 'user_question' : {
            if ($g_user->IsAuthorized()) {
                if ($action!='send_user_question') {
                    $tpl = new Template();
                    $tpl->set_file('user_question', PATH_TO_ROOT.'tpl/user_question.tpl');

                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('HEADER', '');
                    $tpl->set_var('TEXT', isset($PageArray['TxtEditor']) ? $PageArray['TxtEditor'] : '');
                    $tpl->set_var('PERSON', $user_arr['ClientName']);
                    $tpl->set_var('EMAIL', $user_arr['UserLogin']);
                    $tpl->set_var('MESSAGE', '');
                    $MainPageText = $tpl->parse('UQ','user_question',false);
                    unset($tpl);
                } else {
                    $MainPageText = show_message(ERR_SEND_USER_QUESTION);
                }
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;  
        }
        case 'user_companyorders' : {
            if($g_page->IsResourceAccessible($PageCode)) {
                $arr['url'] = '/user_companyorders/a/comp_ord_detail';
                $arr['title'] = 'Просмотреть общий заказ на завтра';
                $arr['active'] = 0;
                $AddonMenuArr[] = $arr;
            
                if($action == 'comp_ord_detail') {
                    $tpl = new Template();
                    $menu_cont = load_menu();
                    $tpl = new Template();
                    $tpl->set_file('menu_table', PATH_TO_ROOT.'tpl/company_order_view_confirm.tpl');
                    $tpl->set_block('menu_table', 'update_block', 'update_block_');
                    $tpl->set_block('menu_table', 'no_change_block',   'no_change_block_');
                    $tpl->set_block('menu_table', 'main_row',     'main_row_');
                    $tpl->set_block('menu_table', 'date_status',  'date_status_');
                    $tpl->set_block('menu_table', 'error_block',  'error_block_');
                    $tpl->set_block('main_row',   'header_row',   'header_row_');
                    $tpl->set_block('main_row',   'simple_row',   'simple_row_');
                    $tpl->set_block('main_row',   'sep_row',      'sep_row_');

                    $tpl->set_var('IDCLIENTCOMP',$user_arr['IDClientComp']);
                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('DATE_STRING',show_date_as_string());
                    $tpl->set_var('SHOW_DATE',$show_date);
                    $tpl->set_var('IDORDER',isset($form['IDOrder']) ? $form['IDOrder'] : 0);
                    if (isset($EditOrderErrors) && $EditOrderErrors != '') {
                        $tpl->set_var('ERRORS', $EditOrderErrors);
                        $tpl->parse('error_block_','error_block',false);
                    } else $tpl->set_var('error_block_','');
                    
                    $oder = get_all_oders_by_date($show_date);
                    if (count($oder)) {
                        $form['OrderListL'] = $oder[$user_arr['IDClientComp']]['OrderListL'];
                        $form['IDOrder'] = $oder[$user_arr['IDClientComp']]['IDOrder'];
                        $form['Comments'] = $oder[$user_arr['IDClientComp']]['Comments'];
                        $show_date = sql_to_date($oder[$user_arr['IDClientComp']]['OrderDate']);
                        $total_summ = $oder[$user_arr['IDClientComp']]['Summ'];

                        $tpl->set_var('SUMM',$total_summ);
                        $tpl->set_var('COMMENTS',$form['Comments']);
                        $tpl->set_var('STATUS',isset($oder_statuses_arr[$oder[$user_arr['IDClientComp']]['Status']]) 
                            ? $oder_statuses_arr[$oder[$user_arr['IDClientComp']]['Status']] : '');

                        $old_menu_type = "old";
                        if(isset($menu_cont[$show_date] )) {
                            foreach($menu_cont[$show_date] as $menu_k => $menu_val) {
                                $id = $menu_val[0];
                                if (isset($form['OrderListL'][$id]) && string_is_int($form['OrderListL'][$id]['main']) ) {
                                    $new_menu_type = $menu_val[2];
                                    $tpl->set_var('header_row_','');
                                    $tpl->set_var('sep_row_','');
                                    if($new_menu_type != $old_menu_type) {
                                        $AddElement = false;
                                        $tpl->set_var('HEADER',$menu_types_arr[$menu_val[2]]);
                                        $tpl->parse('header_row_','header_row',true);
                                    } 
                                    $tpl->set_var('client_number_','');
                                    $tpl->set_var('simple_row_','');

                                    $tpl->set_var('WEIGHT',$menu_val[4]);
                                    $tpl->set_var('TITLE',$menu_val[3]);
                                    $tpl->set_var('NUM',$form['OrderListL'][$menu_val[0]]['main']);
                                    $tpl->set_var('PRICE',isset($menu_val[5]) ? $menu_val[5] : 0);
                                    $tpl->parse('simple_row_','simple_row',true);
                                
                                    $tpl->parse('sep_row_','sep_row',false);
                                    $tpl->parse('main_row_','main_row',true);
                                    $old_menu_type = $new_menu_type;
                                }       
                            }
                        } else {
                            $tpl->set_var('HEADER',ERR_MENU_CONT);
                            $tpl->parse('header_row_','header_row',true);
                            $tpl->parse('main_row_','main_row',true);
                        }
                        
                        $OVal = $oder[$user_arr['IDClientComp']];
                        if(isset($OVal['Status']) && ($OVal['Status'] == 0 || $OVal['Status'] == 1)) { 
                            $tpl->set_var('PAGE_TEXT', $MainPageText);
                            $tpl->parse('update_block_','update_block',false);
                            $tpl->set_var('no_change_block_','');
                        } else {
                            $tpl->set_var('PAGE_TEXT','');
                            $tpl->set_var('update_block_','');
                            $tpl->parse('no_change_block_','no_change_block',false);
                        }
                        
                        $tpl->parse('date_status_','date_status',false);
                    } else {
                        $tpl->set_var('PAGE_TEXT','<H1>'.ERR_NO_ORDER.'</H1>');
                        $tpl->set_var('HEADER','');
                        $tpl->parse('header_row_','header_row',true);
                        $tpl->parse('main_row_','main_row',true);
                        $tpl->set_var('update_block_','');
                        $tpl->set_var('coment_row_','');
                        $tpl->set_var('date_status_','');
                    }

                    $MainPageText = $tpl->parse('MT','menu_table',false);

                    unset($tpl);
                    
                    $arr['url'] = '/user_companyorders/a/comp_ord_employ/show_date/'.$show_date;
                    $arr['title'] = 'Просмотреть заказы сотрудников';
                    $arr['active'] = 0;
                    $AddonMenuArr[] = $arr;
                } elseif($action == 'comp_ord_employ') {
                    $tpl = new Template();
                    $tpl->set_file('employ_table', PATH_TO_ROOT.'tpl/company_order_employ_view.tpl');
                    $tpl->set_block('employ_table', 'employ_item', 'employ_item_');

                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('DATE_STRING',show_date_as_string());
                    $tpl->set_var('SHOW_DATE',$show_date);
                    $tmp_page = get_page_cont_by_id('user_companyorders_employ');
                    $tpl->set_var('PAGE_TEXT',isset($tmp_page['TxtEditor']) ? $tmp_page['TxtEditor'] : 'Просмотр заказов сотрудников');
                    unset($tmp_page);
                    
                    $order = get_all_orders_by(0,0,$show_date,$user_arr['IDClientComp']);
                    foreach($order as $val) {
                        // Криво --- будет показан статус последнего заказа.
                        $tpl->set_var('STATUS',isset($oder_statuses_arr[$val['Status']]) 
                            ? $oder_statuses_arr[$val['Status']] : '');
                        $tpl->set_var('EMPLOY_NAME',$val['ClientName']);
                        $tpl->set_var('IDORDER',$val['IDOrder']);
                        $tpl->parse('employ_item_','employ_item',true);
                    }
                    $MainPageText = $tpl->parse('ET','employ_table',false);

                    unset($tpl);
                } else {
                    if ($action == 'search_archive' && isset($form['StartDate']) && isset($form['EndDate'])) {
                        $show_archive = true;
                    } else {
                        $show_archive = false;
                        $form['StartDate'] = date("d.m.Y");
                        // Очень далеко вперед.
                        $form['EndDate'] = date("d.m.Y",mktime(0,0,0,12,31,2037));
                    }
                    $form['StartDate'] = date_to_sql($form['StartDate']);
                    $form['EndDate'] = date_to_sql($form['EndDate']);
                    
                    $CompOrdersListArr = get_all_company_orders($user_arr['IDClientComp'],$form['StartDate'],$form['EndDate']);

                    $tpl = new Template();
                    $tpl->set_file('orders_list', PATH_TO_ROOT.'tpl/company_orders_list.tpl');
                    $tpl->set_block('orders_list', 'one_order', 'one_order_');
                    $tpl->set_var('THIS_PAGE',$this_page);
                    if ($show_archive) {
                        if (count($CompOrdersListArr)) {
                            $tpl->set_var('PAGE_TEXT','<H1>Результаты поиска по архиву заказов компании с '.
                                sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                        } else {
                            $tpl->set_var('PAGE_TEXT','<H1>Извините, у Вашей компании нет заказов за период с '.
                                sql_to_datestr($form['StartDate']).' по '.sql_to_datestr($form['EndDate']).'.</H1>');
                        }
                    } else $tpl->set_var('PAGE_TEXT',$MainPageText);
                    
                    $i=0;
                    $DelivPrice = $g_options->GetOption('deliv_price');
                    $DelivLimit = $g_options->GetOption('deliv_order_limit');
                    foreach($CompOrdersListArr as $k => $val) {
                        $i++;
                        $tpl->set_var('ORDER_NUM',$i);
                        $tpl->set_var('DATE',sql_to_date($val['OrderDate']));
                        $OrderSumm = ($val['CompOrdSumm'] != '') ? $val['CompOrdSumm'] : 0;
                        $OrderDelivPrice = ($DelivLimit > $OrderSumm) ? $DelivPrice : 0;
                        $TotalSumm = $OrderSumm+$OrderDelivPrice;
                        $tpl->set_var('PRICE',$OrderSumm);
                        $tpl->set_var('DELIV_PRICE',$OrderDelivPrice);
                        $tpl->set_var('TOTAL_PRICE',$TotalSumm);
                        $tpl->set_var('URL','/user_companyorders/show_date/'.sql_to_date($val['OrderDate']).'/a/comp_ord_detail');
                        $tpl->set_var('STATUS', isset($oder_statuses_arr[$val['Status']]) ? $oder_statuses_arr[$val['Status']] : '');
                        $tpl->parse('one_order_','one_order',true);
                    }
                    $MainPageText = $tpl->parse('OL','orders_list',true);
                    unset($tpl);
                }
                    
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,'Архив заказов компании','user_companyorders_archive');
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;
        }
        case 'user_companyorders_archive' :
        case 'user_account_archive' :
        case 'user_myorders_archive' : {
            $tpl = new Template();
            $tpl->set_file('archive_form', PATH_TO_ROOT.'tpl/archive.tpl');
            $tpl->set_block('archive_form','errors','errors_');
            if (isset($ArchiveErrors) && $ArchiveErrors != '') {
                $tpl->set_var('ARCHIVE_ERRORS',$ArchiveErrors);
                $tpl->parse('errors_','errors',false);
            } else $tpl->set_var('errors_','');
            $tpl->set_var('THIS_PAGE',$this_page);
            $tpl->set_var('PAGE_CODE',str_replace('_archive','',$PageCode));
            $tpl->set_var('PAGE_TEXT',$MainPageText);
            $tpl->set_var('ACTION','search_archive');
            $tpl->set_var('START_DATE_SELECT', create_date_selcet('StartDate',50,
                isset($form['StartDate']) ? $form['StartDate'] : date("d.m.Y"),2000,'s03'));
            $tpl->set_var('END_DATE_SELECT', create_date_selcet('EndDate',50,
                isset($form['EndDate']) ? $form['EndDate'] : date("d.m.Y"),2000,'s03'));
            $MainPageText = $tpl->parse('AF','archive_form',false);
            unset($tpl);
        break;
        }
        case 'user_employlist' : {
            if($g_page->IsResourceAccessible($PageCode)) {
                $tpl = new Template();
                if($action == 'add_user_proc' || $action == 'view_user_proc' || $action == 'edit_user_proc'
                    || (($action == 'add_client_user' || $action == 'update_client_user') && $UserUpdateErrors !='')) {
                    if ($action == 'view_user_proc' || $action == 'edit_user_proc') {
                        $OneUserArr = get_clientuser_info_by_client_id($form['IDClientUser']);
                    } else $OneUserArr = $form;
                    
                    //print_r($OneUserArr);
                    if($action == 'view_user_proc') {
                        $tpl->set_file('user_detail', PATH_TO_ROOT.'tpl/company_employer_view.tpl');
                    } else {
                        $tpl->set_file('user_detail', PATH_TO_ROOT.'tpl/company_employer_edit.tpl');
                    }
                    
                    $tpl->set_block('user_detail','errors','errors_');
                    if ($UserUpdateErrors != '') {
                        $tpl->set_var('REG_ERRORS', 'Были обнаружены следующие ошибки:<br/><br/>'.$UserUpdateErrors);
                        $tpl->parse('errors_','errors');
                    } else $tpl->set_var('errors_','');
                    
                    $GrpNamesArr = array('ClientAdmins' => 'Администратор','ClientUsers' => 'Пользователь');
                    $tpl->set_var('THIS_PAGE',$this_page);
                    if ($action == 'add_user_proc' || $action == 'add_client_user') {
                        $tmp_page = get_page_cont_by_id($PageCode.'_add');
                        $tpl->set_var('PAGE_TEXT',isset($tmp_page['TxtEditor']) ? $tmp_page['TxtEditor'] : 'Добавление нового сотрудника');
                    } elseif($action == 'view_user_proc') {
                        $tmp_page = get_page_cont_by_id($PageCode.'_view');
                        $tpl->set_var('PAGE_TEXT',isset($tmp_page['TxtEditor']) ? $tmp_page['TxtEditor'] : 'Просмотр данных сотрудника');
                    } else {
                        $tmp_page = get_page_cont_by_id($PageCode.'_edit');
                        $tpl->set_var('PAGE_TEXT',isset($tmp_page['TxtEditor']) ? $tmp_page['TxtEditor'] : 'Редактирование данных сотрудника');
                    }
                    $tpl->set_var('FNAME',isset($OneUserArr['FName']) ? $OneUserArr['FName'] : '');
                    $tpl->set_var('NAME',isset($OneUserArr['Name']) ? $OneUserArr['Name'] : '');
                    $tpl->set_var('ONAME',isset($OneUserArr['OName']) ? $OneUserArr['OName'] : '');
                    $tpl->set_var('BDATE',isset($OneUserArr['Birthdate']) ? sql_to_date($OneUserArr['Birthdate']) : '');
                    $tpl->set_var('POSITION',isset($OneUserArr['Position']) ? $OneUserArr['Position'] : '');
                    $tpl->set_var('MAIL',isset($OneUserArr['Mail']) ? $OneUserArr['Mail'] : '');
                    $tpl->set_var('GRP_NAME',isset($OneUserArr['GroupCode']) ? $GrpNamesArr[$OneUserArr['GroupCode']] : '');
                    $tpl->set_var('STATUS',(isset($OneUserArr['Status']) && $OneUserArr['Status'] == 0 ) ? '' : 'checked');
                    $tpl->set_var('COMMENTS',isset($OneUserArr['Comments']) ? $OneUserArr['Comments'] : '');
                    if ($action == 'add_user_proc' || $action == 'add_client_user') {
                        $tpl->set_var('A_TYPE','add_client_user');
                    } else if ($action == 'edit_user_proc' || $action == 'update_client_user') {
                        $tpl->set_var('A_TYPE','update_client_user');
                    }
                    $tpl->set_var('IDCLIENTCOPM',isset($OneUserArr['IDClientComp']) ? $OneUserArr['IDClientComp'] : $user_arr['IDClientComp']);
                    $tpl->set_var('IDCLIENTUSER',isset($OneUserArr['IDClientUser']) ? $OneUserArr['IDClientUser'] : 0);
                    $tpl->set_var('BDATE_SELECT', create_date_selcet('Birthdate',80,isset($OneUserArr['Birthdate']) ? sql_to_date($OneUserArr['Birthdate']):'',1930,'s03'));
                    $tpl->set_var('GROUP_OPTIONS', get_select_options(isset($OneUserArr['GroupCode']) ? $OneUserArr['GroupCode'] : 'ClientUsers', $GrpNamesArr, false));
                    $tpl->set_var('LOGIN',isset($OneUserArr['UserLogin']) ? $OneUserArr['UserLogin'] : '');
                    $tpl->set_var('PWD',isset($OneUserArr['UserPwd']) ? $OneUserArr['UserPwd'] : '');
                    $MainPageText = $tpl->parse('UD','user_detail',false);
                } elseif ($action == 'add_amount_proc') { 
                    $OneUserArr = get_clientuser_info_by_client_id($form['IDClientUser']);
                    $tpl->set_file('amount_add', PATH_TO_ROOT.'tpl/company_employer_add_amount.tpl');
                    $tpl->set_block('amount_add','errors','errors_');
                    
                    if (isset($AddUserAmountErrors) && $AddUserAmountErrors != '') {
                        $tpl->set_var('ADD_ERRORS',$AddUserAmountErrors);
                        $tpl->parse('errors_','errors',false);
                    } else $tpl->set_var('errors_','');
                    
                    $tpl->set_var('CLIENT_NAME',isset($OneUserArr['ClientName']) ? $OneUserArr['ClientName'] : '');
                    $tpl->set_var('SUMM',isset($OneUserArr['Amount']) ? $OneUserArr['Amount'] : '0');
                    $tpl->set_var('IDCLIENTUSER',$OneUserArr['IDClientUser']);
                    $tpl->set_var('THIS_PAGE',$this_page);
                    $MainPageText = $tpl->parse('AD','amount_add',false);
                } elseif($action == 'send_message_proc') {
                    $OneUserArr = get_clientuser_info_by_client_id(isset($form['IDClientUser']) ? $form['IDClientUser'] : $g_user->GetId());
                    $tpl->set_file('send_message', PATH_TO_ROOT.'tpl/company_employer_message_form.tpl');
                    $tpl->set_block('send_message','error_block','error_block_');
                    $tpl->set_block('send_message','one_user_text','one_user_text_');
                    $tpl->set_block('send_message','many_users_text','many_users_text_');
                    
                    if (isset($SendMessageErrors) && $SendMessageErrors != '') {
                        $tpl->set_var('SEND_ERRORS',$SendMessageErrors);
                        $tpl->parse('error_block_','error_block',false);
                    } else $tpl->set_var('error_block_','');
                    
                    $tpl->parse('one_user_text_','one_user_text',false);
                    $tpl->set_var('many_users_text_','');
                    $tpl->set_var('CLIENT_USER_ID',isset($form['IDClientUser']) ? $form['IDClientUser'] : $g_user->GetId());
                    $tpl->set_var('SEND_MESSAGE_ACTION',str_replace('_proc','',$action));
                    $tpl->set_var('BUTTON_TEXT','Отправить ');
                    $tpl->set_var('MAILS_LIST',htmlspecialchars('"'.$OneUserArr['ClientName'].'" <'.$OneUserArr['Mail'].'>'));
                    $tpl->set_var('FIO',$OneUserArr['ClientName']);
                    $tpl->set_var('FI',$OneUserArr['Name'].' '.$OneUserArr['FName']);
                    $tpl->set_var('I',$OneUserArr['FName']);
                    $tpl->set_var('DATA',date("d.m.Y"));
                    $tpl->set_var('SUBJECT',isset($form['Subject']) ? $form['Subject'] : '');
                    $tpl->set_var('MESSAGE',isset($form['Message']) ? $form['Message'] : '');
                    $MainPageText = $tpl->parse('SM','send_message',false);
                } elseif($action == 'send_message_all_proc') {
                    $EmployListArr = select_all_company_users($user_arr['IDClientComp']);
                    $mail_list = '';
                    foreach($EmployListArr as $val) {
                        $mail_list .= ', "'.$val['ClientName'].'" <'.$val['Mail'].'>';
                    }
                    $mail_list = substr($mail_list,2);
                    
                    $tpl->set_file('send_message', PATH_TO_ROOT.'tpl/company_employer_message_form.tpl');
                    $tpl->set_block('send_message','error_block','error_block_');
                    $tpl->set_block('send_message','one_user_text','one_user_text_');
                    $tpl->set_block('send_message','many_users_text','many_users_text_');
                    
                    if (isset($SendMessageErrors) && $SendMessageErrors != '') {
                        $tpl->set_var('SEND_ERRORS',$SendMessageErrors);
                        $tpl->parse('error_block_','error_block',false);
                    } else $tpl->set_var('error_block_','');
                    
                    $tpl->set_var('one_user_text_','');
                    $tpl->parse('many_users_text_','many_users_text',false);
                    $tpl->set_var('CLIENT_USER_ID',isset($form['IDClientUser']) ? $form['IDClientUser'] : $g_user->GetId());
                    $tpl->set_var('SEND_MESSAGE_ACTION',str_replace('_proc','',$action));
                    $tpl->set_var('BUTTON_TEXT','Разослать ');
                    $tpl->set_var('MAILS_LIST',htmlspecialchars($mail_list));
                    $tpl->set_var('FIO','');
                    $tpl->set_var('FI','');
                    $tpl->set_var('I','');
                    $tpl->set_var('DATA',date("d.m.Y"));
                    $tpl->set_var('SUBJECT',isset($form['Subject']) ? $form['Subject'] : '');
                    $tpl->set_var('MESSAGE',isset($form['Message']) ? $form['Message'] : '');
                    $MainPageText = $tpl->parse('SM','send_message',false);
                } else {
                    $EmployListArr = select_all_company_users($user_arr['IDClientComp']);
                    $tpl->set_file('employ_list', PATH_TO_ROOT.'tpl/company_employers_list.tpl');
                    $tpl->set_block('employ_list','one_employ','one_employ_');
                    $tpl->set_var('PAGE_TEXT',$MainPageText);
                    foreach($EmployListArr as $val) {
                        $tpl->set_var('EMPLOY_NAME',$val['ClientName']);
                        $tpl->set_var('SUMM',isset($val['Amount']) ? $val['Amount'] : '0'); 
                        $tpl->set_var('STATUS', $val['Status'] != 0 ? 'Активен' : 'Неактивен');
                        $tpl->set_var('SEND_MESSAGE_URL','/user_employlist/a/send_message_proc/form[IDClientUser]/'.$val['IDClientUser']); 
                        $tpl->set_var('ADD_AMOUNT_URL',  '/user_employlist/a/add_amount_proc/form[IDClientUser]/'.$val['IDClientUser']);
                        $tpl->set_var('UPDATE_USER_URL', '/user_employlist/a/view_user_proc/form[IDClientUser]/'.$val['IDClientUser']);
                        $tpl->set_var('NUMBER',$val['IDClientUser']);
                        $tpl->parse('one_employ_','one_employ',true);
                    }
                    $MainPageText = $tpl->parse('EL','employ_list',false);
                }
                unset($tpl);
                
                $arr['url'] = '/user_employlist/a/add_user_proc';
                $arr['title'] = 'Добавить сотрудника';
                $arr['active'] = ($action == 'add_user_proc') ? 1 : 0;
                $AddonMenuArr[] = $arr;
                $arr['url'] = '/user_employlist/a/send_message_all_proc';
                $arr['title'] = 'Разослать сообщение всем сотрудникам';
                $arr['active'] = ($action == 'send_message_all_proc') ? 1 : 0;
                $AddonMenuArr[] = $arr;
                
                $AddonMenu = load_add_menu_to_var($AddonMenuArr,'Список сотрудников',$PageCode);
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;
        }
        case 'user_yourcompany' : {
            if($g_page->IsResourceAccessible($PageCode)) {
                $pay_arr = $g_options->GetOptionList('payment_type');

                $tpl = new Template();
                if ($action != 'edit_proc') {
                    $tpl->set_file('copm_info', PATH_TO_ROOT.'tpl/company_detail_view.tpl');
                    $form = get_comp_info($user_arr['IDClientComp']);
                } else {
                    $tpl->set_file('copm_info', PATH_TO_ROOT.'tpl/company_detail_edit.tpl');
                    $tpl->set_block('copm_info','error_block','error_block_');
                    
                    if (isset($ComUpdateErrors) && $ComUpdateErrors != '') {
                        $tpl->set_var('UPDATE_ERRORS',$ComUpdateErrors);
                        $tpl->parse('error_block_','error_block',false);
                    } else {
                        $tpl->set_var('error_block_','');
                        $form = get_comp_info($user_arr['IDClientComp']);
                    }
                }
                $tpl->set_block('copm_info','pay_type','pay_type_');

                foreach ($pay_arr as $k => $val) {
                    $tpl->set_var('PAY_TITLE', $val);
                    $tpl->set_var('PAY_VAL', $k);
                    $tpl->set_var('CHECKED', (isset($form['PaymentType']) && $k == $form['PaymentType']) ? 'checked' : '');
                    $tpl->parse('pay_type_','pay_type',true);
                }
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('IDCLIENTCOMP', isset($form['IDClientComp']) ? $form['IDClientComp'] : '');
                $tpl->set_var('NAME',    isset($form['Name']) ? $form['Name'] : '');
                $tpl->set_var('MAIL',    isset($form['Mail']) ? $form['Mail'] : '');
                $tpl->set_var('PHONE',   isset($form['Phone']) ? $form['Phone'] : '');
                $tpl->set_var('ADRESS',  isset($form['Adress']) ? $form['Adress'] : '');
                $tpl->set_var('ORG_TYPES_OPTIONS',get_select_options(isset($form['OrgType']) && string_is_id($form['OrgType']) ? $form['OrgType'] : 0, $g_options->GetOptionList('org_type'), false)); 
                $tpl->set_var('DELIV_TIME_OPTIONS',get_select_options(isset($form['DeliveryTime']) && string_is_id($form['DeliveryTime']) ? $form['DeliveryTime'] : 0, $g_options->GetOptionList('deliv_time'), false)); 

                $tpl->set_var('METRO',isset($form['Metro']) ? $form['Metro'] : '');
                $tpl->set_var('URL',isset($form['WebUrl']) ? $form['WebUrl'] : ''); 
                $tpl->set_var('EMLOY_NUM',isset($form['EmployersNum']) ? $form['EmployersNum'] : '');  
                $MainPageText = $tpl->parse('CI','copm_info',false);
                unset($tpl);
            } else {
                $MainPageText = show_message(ERR_PAGE_NOT_ALLOWED,ERR_PAGE_NOT_ALLOWED2);
            }
            
        break;  
        }
        case 'quest_ans' : {
            if (isset($form['quest_id'])) {
                $one_quest_array = get_one_questios($form['quest_id']);
                $tpl = new Template();
                $tpl->set_file('question', PATH_TO_ROOT.'tpl/faq_detail.tpl');
                $tpl->set_block('question','odd','odd_');
                $tpl->set_block('question','even','even_');
                $tpl->set_var('odd_','');
                $tpl->set_var('even_','');
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('NUMBER',$one_quest_array['IDLine']);
                $tpl->set_var('QUESTION',stripslashes($one_quest_array['Question']));
                $tpl->set_var('ANSWER',stripslashes($one_quest_array['Answer']));
                if ($one_quest_array['IDLine']%2==0) $tpl->parse('even_','even');
                else $tpl->parse('odd_','odd');
                $MainPageText = $tpl->parse('q','question',false);
                
                if (isset($one_quest_array['PageTitle']) && !empty($one_quest_array['PageTitle'])) {
                    $PageTitle = stripslashes($one_quest_array['PageTitle']);
                }
            } else {
                $QuestionsArray = get_all_questios();
                $tpl = new Template();
                $tpl->set_file('questions', PATH_TO_ROOT.'tpl/faq.tpl');
                $tpl->set_block('questions','question','question_');
                $tpl->set_block('question','odd','odd_');
                $tpl->set_block('question','even','even_');
                
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGE_TEXT',$MainPageText);
                
                foreach($QuestionsArray as $q_val) {
                    $tpl->set_var('odd_','');
                    $tpl->set_var('even_','');
                    $tpl->set_var('NUMBER',$q_val['IDLine']);
                    $tpl->set_var('QUESTION',stripslashes($q_val['Question']));
                    $tpl->set_var('ID',$q_val['IDLine']);
                    if ($q_val['IDLine']%2==0) $tpl->parse('even_','even');
                    else $tpl->parse('odd_','odd');
                    $tpl->parse('question_','question',true);
                }

                $MainPageText = $tpl->parse('q','questions',false);
                unset($tpl);
            }
        break;
        }
        case 'news' : {
            if (isset($form['id'])) {
                $val = get_one_news($form['id']);
                
                $tpl = new Template();
                $tpl->set_file('news_one', PATH_TO_ROOT.'tpl/news_one.tpl');
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('DATE',sql_to_datestr($val['DateNews']).' г.');
                $tpl->set_var('TITLE',$val['Title']);
                $tpl->set_var('ANNOUNCE',$val['Announce']);
                $tpl->set_var('TEXT',$val['Content']);
                $MainPageText = $tpl->parse('NO', 'news_one', false);
                
                if (isset($val['PageTitle']) && !empty($val['PageTitle'])) {
                    $PageTitle = stripslashes($val['PageTitle']);
                }
            } else {    
                $page = isset($form['page']) ? $form['page'] : 0;
                
                $news_arr = get_news_list($page,$news_on_page);
                $tpl = new Template();
                $tpl->set_file('news_list', PATH_TO_ROOT.'tpl/news_list.tpl');
                $tpl->set_block('news_list','item', 'item_');
                if (count($news_arr) > 0) foreach ($news_arr as $val) {
                    $tpl->set_var('THIS_PAGE', $this_page);
                    $tpl->set_var('DATE',sql_to_datestr($val['DateNews']).' г.');
                    $tpl->set_var('TITLE',$val['Title']);
                    $tpl->set_var('ANNOUNCE',$val['Announce']);
                    $tpl->set_var('URL','/'.$PageCode.'/form[id]/'.$val['IDNews']);
                    $tpl->parse('item_', 'item', true);
                }
                $MainPageText = $tpl->parse('NL', 'news_list', false);
                unset($tpl);
                
                $total_news = get_news_count();
                if ($total_news > $news_on_page) {
                    $pages_num = ceil($total_news/$news_on_page);
                    $tpl = new Template();
                    $tpl->set_file('pager', PATH_TO_ROOT.'tpl/pager.tpl');
                    $tpl->set_block('pager','pages', 'pages_');
                    $tpl->set_block('pages','active', 'active_');
                    $tpl->set_block('pages','inactive', 'inactive_');
                    $tpl->set_var('THIS_PAGE',$this_page);
                    $tpl->set_var('PAGE_CODE', $PageCode);
                    $tpl->set_var('FIRST','0');
                    $tpl->set_var('PREV',($page-1 < 0) ? 0 : $page-1);
                    $tpl->set_var('NEXT',($page+1 >= $pages_num) ? $pages_num-1 : $page+1);
                    $tpl->set_var('LAST',$pages_num-1);
                    for($i=0;$i<$pages_num;$i++) {
                        $tpl->set_var('inactive_','');
                        $tpl->set_var('active_','');
                        $tpl->set_var('PAGE_NUM',$i+1);
                        $tpl->set_var('PAGE_NUM_A',$i);
                        if ($i == $page) 
                            $tpl->parse('active_','active',true);
                        else
                            $tpl->parse('inactive_','inactive',true);
                        $tpl->parse('pages_','pages',true);
                    }
                    $pager = $tpl->parse('P', 'pager', false);
                    $MainPageText .= $pager;
                    unset($tpl);
                }
            }
            
        break;
        }
        case 'clients' : {
            $page = isset($form['page']) ? $form['page'] : 0;
            
            $firms_arr = get_firms_list($page,$news_on_page);
            $tpl = new Template();
            $tpl->set_file('firms_list', PATH_TO_ROOT.'tpl/clients.tpl');
            $tpl->set_block('firms_list','item', 'item_');
            $tpl->set_var('THIS_PAGE', $this_page);
            $tpl->set_var('PAGE_TEXT', $MainPageText);
            if (count($firms_arr) > 0) foreach ($firms_arr as $val) {
                $tpl->set_var('TITLE',$val['Title']);
                $tpl->set_var('ANNOUNCE',$val['Announce']);
                $tpl->set_var('CONTENT',$val['Content']);
                $tpl->set_var('URL',$val['URL']);
                $tpl->set_var('IMAGE',PATH_TO_ROOT.PATH_TO_PIC.$val['Image']);
                $tpl->parse('item_', 'item', true);
            }
            $MainPageText = $tpl->parse('FL', 'firms_list', false);
            unset($tpl);
            
            $total_firms = get_firms_count();
            if ($total_firms > $news_on_page) {
                $pages_num = ceil($total_firms/$news_on_page);
                $tpl = new Template();
                $tpl->set_file('pager', PATH_TO_ROOT.'tpl/pager.tpl');
                $tpl->set_block('pager','pages', 'pages_');
                $tpl->set_block('pages','active', 'active_');
                $tpl->set_block('pages','inactive', 'inactive_');
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGE_CODE', $PageCode);
                $tpl->set_var('FIRST','0');
                $tpl->set_var('PREV',($page-1 < 0) ? 0 : $page-1);
                $tpl->set_var('NEXT',($page+1 >= $pages_num) ? $pages_num-1 : $page+1);
                $tpl->set_var('LAST',$pages_num-1);
                for($i=0;$i<$pages_num;$i++) {
                    $tpl->set_var('inactive_','');
                    $tpl->set_var('active_','');
                    $tpl->set_var('PAGE_NUM',$i+1);
                    $tpl->set_var('PAGE_NUM_A',$i);
                    if ($i == $page) 
                        $tpl->parse('active_','active',true);
                    else
                        $tpl->parse('inactive_','inactive',true);
                    $tpl->parse('pages_','pages',true);
                }
                $pager = $tpl->parse('P', 'pager', false);
                $MainPageText .= $pager;
                unset($tpl);
            }
            
        break;
        }
        case 'responses' : {
            if ($action!='send_feedback') {
                $tpl = new Template();
                $tpl->set_file('feedback_form', PATH_TO_ROOT.'tpl/feedback_form.tpl');
                $tpl->set_block('feedback_form','error_block','error_block_');
                
                if (isset($SendMessageErrors) && $SendMessageErrors != '') {
                    $tpl->set_var('SEND_ERRORS',$SendMessageErrors);
                    $tpl->parse('error_block_','error_block',false);
                } else $tpl->set_var('error_block_','');
                    
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('TEXT',isset($PageArray['TxtEditor']) ? $PageArray['TxtEditor'] : '');
                                
                $tpl->set_var('PERSON',isset($form['person']) ? $form['person'] : '');
                $tpl->set_var('EMAIL',isset($form['email']) ? $form['email'] : '');
                $tpl->set_var('MESSAGE',isset($form['message']) ? $form['message'] : '');
                
                $MainPageText = $tpl->parse('FF','feedback_form',false);
                unset($tpl);
            } else {
                $MainPageText = show_message(ERR_SEND_FEEDBACK);
            }
            
        break;
        }
        case 'send_password' : {
            if ($action!='send_password') {
                $tpl = new Template();
                $tpl->set_file('send_password_form', PATH_TO_ROOT.'tpl/send_password.tpl');
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('PAGE_TEXT',$MainPageText);
                $MainPageText = $tpl->parse('SPF','send_password_form',false);
                unset($tpl);
            } else {
                $MainPageText = show_message($SendPasswordErrors);
            }
            
        break;
        }
        case 'advert' : {
            if ($action!='send_advert_question') {
                $tpl = new Template();
                $tpl->set_file('advert_form', PATH_TO_ROOT.'tpl/advert_form.tpl');
                $tpl->set_block('advert_form','error_block','error_block_');
                
                if (isset($SendMessageErrors) && $SendMessageErrors != '') {
                    $tpl->set_var('SEND_ERRORS',$SendMessageErrors);
                    $tpl->parse('error_block_','error_block',false);
                } else $tpl->set_var('error_block_','');
                
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('TEXT',isset($PageArray['TxtEditor']) ? $PageArray['TxtEditor'] : '');
                                
                $tpl->set_var('PERSON',isset($form['person']) ? $form['person'] : '');
                $tpl->set_var('EMAIL',isset($form['email']) ? $form['email'] : '');
                $tpl->set_var('MESSAGE',isset($form['message']) ? $form['message'] : '');
                
                $MainPageText = $tpl->parse('AF','advert_form',false);
                unset($tpl);
            } else {
                $MainPageText = show_message(ERR_SEND_ADVERT_QUESTION);
            }
            
        break;
        }
        case 'map' : {
            $tpl = new Template();
            $tpl->set_file('site_map', PATH_TO_ROOT.'tpl/map.tpl');
            $tpl->set_block('site_map','group','group_');
            $tpl->set_block('group','item','item_');
            $tpl->set_var('THIS_PAGE', $this_page);
            $tpl->set_var('PAGE_TEXT',$MainPageText);

            {
                $tpl->set_var('item_','');
                $menu = get_menu_pages('right');
                foreach($menu as $val) {
                    $tpl->set_var('URL','/'.$val['PageCode'].'.html');
                    $tpl->set_var('NAME',$val['CatName']);
                    $tpl->parse('item_','item',true);
                }            
                unset($menu);
                $tpl->parse('group_','group',true);
            }
            {
                $tpl->set_var('item_','');
                $menu = get_menu_pages('top');
                foreach($menu as $val) {
                    $tpl->set_var('URL','/'.$val['PageCode'].'.html');
                    $tpl->set_var('NAME',$val['CatName']);
                    $tpl->parse('item_','item',true);
                }            
                unset($menu);
                $tpl->parse('group_','group',true);
            }
            {
                $tpl->set_var('item_','');
                $menu = get_menu_pages('user_menu');
                foreach($menu as $val) {
                    if (!$g_page->IsResourceAccessible($val['PageCode'])) continue;
                    $tpl->set_var('URL','/'.$val['PageCode'].'.html');
                    $tpl->set_var('NAME',$val['CatName']);
                    $tpl->parse('item_','item',true);
                }            
                unset($menu);
                $tpl->parse('group_','group',true);
            }
            
            $MainPageText = $tpl->parse('SM','site_map',false);
            unset($tpl);
        break;
        }
        
        case 'links' : {
            $links_file = PATH_TO_ROOT."files/links.xml";
            $cont_arr = imp_xml_parse(file_get_contents($links_file));
                    
            if (isset($form['id'])) {
                $page = isset($form['page']) 
                    ? ( is_numeric( $form['page'] ) ? $form['page'] - 1 : 0 )
                    : 0;
                
                $tpl = new Template();
                $tpl->set_file('links_list',PATH_TO_ROOT.'tpl/links_list.tpl');
                $tpl->set_block('links_list','item','item_');
                                
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGECODE',$PageCode);

                $links_count = 0;
                foreach ($cont_arr['BODY'][0]['LINKS'][0]['ITEM'] as $val) {
                    if ($val['IDCAT'][0]['VALUE'] == $form['id']) {
                        if (($links_count >= $page*$news_on_page) && ($links_count < ($page+1)*$news_on_page)) {
                            $tpl->set_var("URL",iconv('UTF-8','CP1251',$val['MAINURL'][0]['VALUE']));
                            $tpl->set_var("NAME",iconv('UTF-8','CP1251',$val['LINKNAME'][0]['VALUE']));
                            $tpl->set_var("LIST_URL",iconv('UTF-8','CP1251',$val['HREF_LIST'][0]['VALUE']));
                            $tpl->set_var("BUTTON",iconv('UTF-8','CP1251',$val['BUTTON_TEXT'][0]['VALUE']));
                            $tpl->set_var("EMAIL",iconv('UTF-8','CP1251',$val['EMAIL'][0]['VALUE']));
                            
                            $tpl->parse('item_','item',true);
                        }
                    
                        ++$links_count;
                    }
                }
                
                // Найти в списке категорий текущую и получить её название.
                if (isset($cont_arr['BODY'][0]['CATEGORIES'][0]['ITEM'])) {
                    foreach ($cont_arr['BODY'][0]['CATEGORIES'][0]['ITEM'] as $val) {
                        if ($val['ID'][0]['VALUE'] == $form['id']) {
                            $tpl->set_var('PAGE_CODE_NAME',iconv('UTF-8','CP1251',$val['NAME'][0]['VALUE']));
                            break;
                        }
                    }
                }
                
                $MainPageText = $tpl->parse('LL','links_list');
                unset($tpl);

                // Добавить к списку навигатор.
                $total_links = $links_count;
                if ($total_links > $news_on_page) {
                    $pages_num = ceil($total_links/$news_on_page);
                    $tpl = new Template();
                    $tpl->set_file('pager', PATH_TO_ROOT.'tpl/pager2.tpl');
                    $tpl->set_block('pager','pages', 'pages_');
                    $tpl->set_block('pages','active', 'active_');
                    $tpl->set_block('pages','inactive', 'inactive_');
                    $tpl->set_var('THIS_PAGE',$this_page);
                    $tpl->set_var('PAGE_CODE', $PageCode.'_'.$form['id']);
                    $tpl->set_var('FIRST','1');
                    $tpl->set_var('PREV',($page-1 <= 0) ? 1 : $page);
                    $tpl->set_var('NEXT',($page+1 >= $pages_num) ? $pages_num : $page+2);
                    $tpl->set_var('LAST',$pages_num);
                    for($i=0;$i<$pages_num;$i++) {
                        $tpl->set_var('inactive_','');
                        $tpl->set_var('active_','');
                        $tpl->set_var('PAGE_NUM',$i+1);
                        $tpl->set_var('PAGE_NUM_A',$i+1);
                        if ($i == $page) 
                            $tpl->parse('active_','active',true);
                        else
                            $tpl->parse('inactive_','inactive',true);
                        $tpl->parse('pages_','pages',true);
                    }
                    $pager = $tpl->parse('P', 'pager', false);
                    $MainPageText .= $pager;
                    unset($tpl);
                }
            } 
            else {
                $tpl = new Template();
                $tpl->set_file('links_categories',PATH_TO_ROOT.'tpl/links_categories.tpl' );
                $tpl->set_block('links_categories','item','item_');
                
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGECODE',$PageCode);
            
                if (isset($cont_arr['BODY'][0]['CATEGORIES'][0]['ITEM'])) {
                    foreach ($cont_arr['BODY'][0]['CATEGORIES'][0]['ITEM'] as $val) {
                        $tpl->set_var('NAME',iconv('UTF-8','CP1251',$val['NAME'][0]['VALUE']));
                        $tpl->set_var('ID',iconv('UTF-8','CP1251',$val['ID'][0]['VALUE']));
                        
                        $tpl->parse('item_','item',true);
                    }
                }
                
                $MainPageText = $tpl->parse('LC','links_categories');
                unset($tpl);
            }
        break;
        }
        
        case 'partners' : {
        
            $links_file = "files/lexch.xml";
            $cont_arr = imp_xml_parse(file_get_contents($links_file));
            $page_url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
            
            $tpl = new Template();
            $tpl->set_file('list', PATH_TO_ROOT.'tpl/partners.tpl');
            $tpl->set_block('list', 'block', 'block_');
            $tpl->set_block('block', 'item', 'item_');

            $items = '';
            $old_title = '';
            if (isset($cont_arr['BODY'][0]['LEXCH'][0]['ITEM']))
            {
                foreach($cont_arr['BODY'][0]['LEXCH'][0]['ITEM'] as $val)
                {
                    if($val['SITEURL'][0]['VALUE'] == $page_url)
                    {
                        if( $old_title != $val['TITLE'][0]['VALUE'] )
                        {
                            if( $old_title ) $tpl->parse( 'block_', 'block', true );
                            $tpl->set_var( 'item_', '' );
                            $old_title = $val['TITLE'][0]['VALUE'];
                        }
                        
                        $text = 
                            str_replace( '<a ', '<a target="_blank" ', 
                            str_replace( '<A ', '<A target="_blank" ', 
                            $val['URL'][0]['VALUE'] ) );
                                            
                        $tpl->set_var("TEXT",iconv('UTF-8','CP1251',$text));
                        $tpl->parse("item_","item",true);
                    }
                }
            }
            
            $tpl->parse( 'block_', 'block', true );
            $body = $tpl->parse('L','list',false);
            $MainPageText = $body;
        break;
        }
    }

require (PATH_TO_ROOT . 'inc/top.inc.php');

    //Обработка ошибок и сообщений сайта и вывод их
    if ($Erros != '') $MainPageText = show_message('Ошибка',$Erros);

    $tpl = new Template();
    $tpl->set_file('main', PATH_TO_ROOT.'tpl/body_din.tpl');
    $tpl->set_block('main','index','index_');
    if ($PageCode=='index') $tpl->parse('index_','index');
    else $tpl->set_var('index_','');
    $tpl->set_var('BODY',$MainPageText);
//    $tpl->set_var('ADD_MENU',$AddonMenu);
    $tpl->pparse('m', 'main', false);
    unset($tpl);
}

if (isset($PageArray['PageType']) && $PageArray['PageType'] == 'stat') {
    if (isset($PageArray['HasArticles']) && $PageArray['HasArticles'] != 0) {
        if (isset($form['id'])) {
            $val = get_one_article($form['id']);
            
            $tpl = new Template();
            $tpl->set_file('article_one', PATH_TO_ROOT.'tpl/article_one.tpl');
            $tpl->set_var('THIS_PAGE', $this_page);
            $tpl->set_var('TITLE',$val['Title']);
            $tpl->set_var('ANNOUNCE',$val['Announce']);
            $tpl->set_var('TEXT',$val['Content']);
            $MainPageText = $tpl->parse('AO', 'article_one', false);
            
            if (isset($val['PageTitle']) && !empty($val['PageTitle'])) {
                $PageTitle = stripslashes($val['PageTitle']);
            }
        } else {    
            $page = isset($form['page']) ? $form['page'] : 0;
            
            $articles_arr = get_articles_list($PageCode,$page,$news_on_page);
            $tpl = new Template();
            $tpl->set_file('article_list', PATH_TO_ROOT.'tpl/article_list.tpl');
            $tpl->set_block('article_list','item', 'item_');
            if (count($articles_arr) > 0) foreach ($articles_arr as $val) {
                $tpl->set_var('THIS_PAGE', $this_page);
                $tpl->set_var('TITLE',$val['Title']);
                $tpl->set_var('ANNOUNCE',$val['Announce']);
                $tpl->set_var('URL',INDEX_PAGE.'.php?PageCode='.$PageCode.'&form[id]='.$val['IDArticle']);
                $tpl->parse('item_', 'item', true);
            }
            $MainPageText = $tpl->parse('AL', 'article_list', false);
            unset($tpl);

            $total_articles = get_articles_count($PageCode);
            if ($total_articles > $news_on_page) {
                $pages_num = ceil($total_articles/$news_on_page);
                $tpl = new Template();
                $tpl->set_file('pager', PATH_TO_ROOT.'tpl/pager.tpl');
                $tpl->set_block('pager','pages', 'pages_');
                $tpl->set_block('pages','active', 'active_');
                $tpl->set_block('pages','inactive', 'inactive_');
                $tpl->set_var('THIS_PAGE',$this_page);
                $tpl->set_var('PAGE_CODE', $PageCode);
                $tpl->set_var('FIRST','0');
                $tpl->set_var('PREV',($page-1 < 0) ? 0 : $page-1);
                $tpl->set_var('NEXT',($page+1 >= $pages_num) ? $pages_num-1 : $page+1);
                $tpl->set_var('LAST',$pages_num-1);
                for($i=0;$i<$pages_num;$i++) {
                    $tpl->set_var('inactive_','');
                    $tpl->set_var('active_','');
                    $tpl->set_var('PAGE_NUM',$i+1);
                    $tpl->set_var('PAGE_NUM_A',$i);
                    if ($i == $page) 
                        $tpl->parse('active_','active',true);
                    else
                        $tpl->parse('inactive_','inactive',true);
                    $tpl->parse('pages_','pages',true);
                }
                $pager = $tpl->parse('P', 'pager', false);
                $MainPageText .= $pager;
                unset($tpl);
            }
        }
    } else {
        $tpl = new Template();
        $tpl->set_file('stat_page', PATH_TO_ROOT.'tpl/stat_page.tpl');
        $tpl->set_var('BODY',$MainPageText);
        $MainPageText = $tpl->parse('SP', 'stat_page', false);
        unset($tpl);
    }

require (PATH_TO_ROOT . 'inc/top.inc.php');
    
    $tpl = new Template();
    $tpl->set_file('main', PATH_TO_ROOT.'tpl/body_simple.tpl');
    $tpl->set_var('BODY',$MainPageText);
    $tpl->pparse('m', 'main', false);
}

require (PATH_TO_ROOT . 'inc/bottom.inc.php');

function load_add_menu_to_var($MenuArr,$AddButton,$PageCode) {
    global $this_page;
    $ret = '';
    $tpl = new Template();
    $tpl->set_file('add_menu', PATH_TO_ROOT.'tpl/add_menu.tpl');
    $tpl->set_block('add_menu', 'add_menu_item', 'add_menu_item_');
    $tpl->set_block('add_menu_item', 'add_menu_item_active', 'add_menu_item_active_');
    $tpl->set_block('add_menu_item', 'add_menu_item_inactive', 'add_menu_item_inactive_');

    $tpl->set_var('BUTTON_TEXT',$AddButton);
    $tpl->set_var('BUTTON_URL','/'.$PageCode.'.html');
    foreach($MenuArr as $k => $val) {
        $tpl->set_var('add_menu_item_inactive_','');
        $tpl->set_var('add_menu_item_active_','');
        $tpl->set_var('TITLE',isset($val['title']) ? $val['title'] : '');
        if (isset($val['active']) && $val['active'] == '1') {
            $tpl->parse('add_menu_item_','add_menu_item',true);
            $tpl->parse('add_menu_item_active_','add_menu_item_active',true);
        } else {
            $tpl->set_var('URL',isset($val['url']) ? $val['url'] : '');
            $tpl->parse('add_menu_item_inactive_','add_menu_item_inactive',true);
        }
        $tpl->parse('add_menu_item_','add_menu_item',true);
    }
    $ret = $tpl->parse('AM','add_menu',false);
    unset($tpl);
    return $ret;
}

function show_message($str,$text='') {
    global $g_options;
    $tpl = new Template();
    $tpl->set_file('show_message', PATH_TO_ROOT.'tpl/message.tpl');
    $tpl->set_var('MESSAGE',$str);
    $tpl->set_var('BODY_TEXT',$text);
    return $tpl->parse('SM','show_message',false);
    unset($tpl);
}

function show_date_as_string()
{
    global $show_date, $months_array_to;
    $s_date_arr = split("\.",$show_date);
    return $s_date_arr[0].' '.$months_array_to[$s_date_arr[1]+0].' '.$s_date_arr[2].' г.';
}

?> 