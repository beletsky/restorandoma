<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_orders.inc.php                                              #
#   Изменения и запросы по заказам.                                            #
#                                                                              #
################################################################################
/*

  function order_add()      - Функции для работы с пользователями - админами.
  function order_edit()       Возвращают текст ошибки. 
  function order_del()        Принимают на вход массив с параметрами - полями.
  
*/

function order_edit(&$data,$isnew=true,$auto=false) {
    global $db;
    $r = '';
    $q0 = 'select IDClientComp from dwClientUsers where IDClientUser = '.$data['IDClientUser'];
    $db->Query($q0);
    if($arr = $db->FetchArray()) {
        $IdClientComp = $arr['IDClientComp'];
        $CurOrdresArr = get_all_orders_by(0,0,$data['OrderDate'],$IdClientComp); 
        if(count($CurOrdresArr)>0) {
            $data['OrderNum'] = $CurOrdresArr[0]['OrderNum'];
        }
    }
    if(!isset($data['OrderNum']) || $data['OrderNum'] == '') $data['OrderNum'] = create_new_order_num()."/".date('m');
    if(!$auto) $r = _order_check(_page_prepare($data));
    if ($r == '') {
        $db->Lock(array('dwClientUsers','dwOrders','dwOrders as O','dwAccounts','dwAccounts as A'));
        $arch = isset($data['arhiv']) ? 1 : 0;
        $hasar = isset($data['HasArticles']) ? 1 : 0;
        $OrderList = '';
        $summ = 0;
        if (isset($data['OrderListL'])) {
            foreach($data['OrderListL'] as $k => $v) {
                if (string_is_int($v['main'])) {
                    $OrderList .= $k.'-'.$v['main'].'-'.$v['price'].';';
                    $summ += $v['price']*$v['main'];
                }           
            }
        }
        $q  = $isnew ? 'insert into dwOrders set ' : 'update dwOrders set';
        $q .= isset($data['OrderNum']) ? ' OrderNum = "' . addslashes($data['OrderNum']) . '", ' : '';
        $q .= isset($data['OrderDate']) ? 'OrderDate = "' . date_to_sql($data['OrderDate']) .'", ' : '';
        $q .= isset($data['Status']) ? 'Status = "' . $data['Status'] .'", ' : 'Status = "0", ' ;
        $q .= (isset($data['Summ']) && $data['Summ'] != '') ? 'Summ = "' . $data['Summ'] .'", ' : 'Summ = "' . $summ.'", ';
        $q .= $isnew ? ' CreationDate = "'.date("Y-m-d").'", ' : '';
        $q .= ' OrderList  = "' . $OrderList  .'", ';
        $q .= ' IDClientUser = ' . $data['IDClientUser'];
        $q .= $isnew? '' : ' where IDOrder = ' . $data['IDOrder'];
        $db->Query($q);

        // Новый заказ создает соответствующую запись в таблице движения по личному счету.
        if ($isnew) {
            $q = 'select last_insert_id()';
            $db->Query($q);
            $data['IDOrder'] = $db->NextRecord() ?  $db->F(0) : 0;
            add_account_operation($data['IDClientUser'],0,false,$data['IDOrder']);
        }
        // Пересчитать состояние личного счета.
        update_user_amount($data['IDClientUser']);
        
        $db->UnLock();
    }
    return $r;
}

function update_user_amount($IDClientUser) {
    global $db;
    $ret = '';
    if (string_is_id($IDClientUser)) {
        $q = 'update dwClientUsers set Amount='.
            '(select sum(ifnull(-O.Summ,A.Summ)) from dwAccounts as A left outer join dwOrders as O on A.IDOrder = O.IDOrder'.
            ' where A.IDClientUser='.$IDClientUser.' and (A.OperationType="add" or O.Status in (3,4)))'.
            ' where IDClientUser = '.$IDClientUser;
        $db->Query($q);
    } else {
        $ret = 'Ошибка обновления личного счета клиента.';
    }
    return $ret;
}

function add_account_operation($idclient,$summ,$is_add,$id_order = 0) {
    global $db;
    $OpType = ($is_add) ? 'add' : 'minus';
    $q = 'insert into dwAccounts set IDClientUser = '. $idclient .', '.
         'OperationDate = "'.date("Y-m-d").'", '.
         'Summ = '. (($is_add) ? abs($summ) : -abs($summ)) .', '.
         'OperationType = "'. $OpType .'", '.
         'lefton = 0, '.
         'IDOrder = '.$id_order;
    $db->Query($q);
}

function get_account_history($StDate,$FinDate,$IdClient) {
    global $db;
    $AddQuery = '';
    $AddQueryArr = array();
    $ret = array();
    if(string_is_date(sql_to_date($StDate)))  $AddQueryArr[] = '  OperationDate >= "'.$StDate.'" ';
    if(string_is_date(sql_to_date($FinDate))) $AddQueryArr[] = '  OperationDate <= "'.$FinDate.'" ';
    if(count($AddQueryArr) > 0 ) $AddQuery = ' and '. implode(' and ',$AddQueryArr);
    if(string_is_id($IdClient))  {
        $q = 'select *,ifnull(-O1.Summ,A1.Summ) as Summ,'.
            '(select sum(ifnull(-O0.Summ,A0.Summ)) from dwAccounts A0 left outer join dwOrders O0 on A0.IDOrder = O0.IDOrder'.
            ' where A0.IDClientUser='.$IdClient.' and (A0.OperationType="add" or O0.Status in (3,4))'.
            ' and A0.OperationDate<=A1.OperationDate and A0.IDOperation<=A1.IDOperation)'.
            ' as LeftSum from dwAccounts A1 left outer join dwOrders O1 on A1.IDOrder = O1.IDOrder'.
            ' where A1.IDClientUser = '.$IdClient.' and (A1.OperationType="add" or O1.Status in (3,4))'.
            ' and ifnull(-O1.Summ,A1.Summ)<>0'.$AddQuery.
            ' order by OperationDate,IDOperation';
        $db->query($q);
        while($arr = $db->FetchArray()) {
            $ret[] = $arr;
        }
    }
    return $ret;
}

function change_order_status($id_order,$status) {
    global $db;
    if (is_array($id_order)) {
        $add_q = "in (";
        $add_q .= implode(",", $id_order);
        $add_q .= ")";
    } else {
        $add_q = " = ".$id_order;
    }
    $q = 'update dwOrders set Status = '.$status.' where  IDOrder '. $add_q;
    $db->Query($q);

    if (!is_array($id_order)) $id_order = array($id_order);
    
    if ($status == 3 || $status == 5) {
        foreach($id_order as $val) {
            $q = 'select Summ,IDClientUser from dwOrders where IDOrder = '. $val;
            $db->query($q);
            if($ar = $db->FetchArray()) {
                update_user_amount($ar['IDClientUser']);
            }
        }
    }
}

function order_del($id) {
    global $db;
    $r = '';
    if (!_is_order_exists($id)) $r .= 'Заказ не найден.<br>';
    if ($r == '') {
        $q = 'delete from dwOrders where IDOrder = ' . $id;
        $db->Query($q);
    }
    return $r;
}

   //Функция выборки все существующих статичных страниц возвращает код и наименование.
function  get_all_stpages_IDCode() {
    global $db;
    $ret = array();
    $q = 'select IDOrder, Name, Title, PageCode from dwOrders order by Name';
    $db->query($q);
    while($tree_arr = $db->FetchArray()) { 
        $ret[$tree_arr['PageCode']] = $tree_arr['Name'];
    }
    return $ret;
}   

// Если isAdd, то проверяем существование польз. с таким логином, 
// иначе проверка на сущ. записи с таким ID и сущ. польз. с таким логином, но другим ID.
function _order_check($data, $isAdd = false) {
    global $db, $g_prod;
    $r = '';
    
    // Проверка наличия полей
    if ($data['OrderNum'] == '') $r .= 'Введите номер заказа.<br>';
    if (!string_is_date($data['OrderDate']) || ($data['OrderDate'] == '')) $r .= "Неверно введена дата заказа<br/>";
    $order_some = false;
    if (isset($data['OrderListL'])) {
        foreach($data['OrderListL'] as $vv) {
            if($vv['main'] != '' && string_is_int($vv['main'])) {
                $order_some = true;
            } elseif(!string_is_int($vv['main']) && $vv['main'] != '') {
                $r .= "Количество блюд должно задаваться цифрой<br/>";
            }
        }
    }
    if(!$order_some) $r .= "Вы не заказали ни одного блюда<br/>";
    return $r;
}

function _page_prepare(&$data) {
    $data['OrderNum'] = isset($data['OrderNum']) ? trim($data['OrderNum']) : '';
    return $data;
}

function _is_order_exists($id) {
    return is_obj_exists($id, 'IDOrder', 'dwOrders');
}

function change_order_comments($comments,$id_order) {
    global $db;
    $q = 'update dwOrders set Comments = "'.htmlspecialchars($comments).'" where IDOrder = '.$id_order;
    if(string_is_id($id_order)) {
        $db->Query($q);
    }
}

function get_all_orders_by($id_order=0,$idclient=0,$order_date = '',$id_copm) {
    global $db;
    $ret = array();
    $add_where = '';
    if ($id_order != 0)  
        $add_where .= ' and DO.IDOrder = '.$id_order.' ';
    if($idclient != 0)  
        $add_where .= ' and DU.IDClientUser = '.$idclient.' ';
    if (string_is_date($order_date)) 
        $add_where .= ' and DO.OrderDate = "'.date_to_sql($order_date).'" ';
    if($id_copm != 0)  
        $add_where .= ' and DU.IDClientComp = '.$id_copm.' ';    
    $q = 'select DO.*, DC.Name, DC.IDClientComp, DC.IDClientComp, concat(DU.Name, " ", DU.FName, " ", DU.OName) as ClientName'.
        ' from dwOrders DO '.
        ' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
        ' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
        ' where 1 '.$add_where.' order by DC.IDClientComp';
    $db->query($q);
    $OldCompID = 0;
    while($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_all_orders_between_dates($idclient,$start_date,$end_date) {
    global $db;
    $ret = array();
    $add_where = '';
    if($idclient != 0)  
        $add_where .= ' and DU.IDClientUser = '.$idclient.' ';
    if ($start_date!='') 
        $add_where .= ' and DO.OrderDate >= "'.$start_date.'" ';
    if ($end_date!='') 
        $add_where .= ' and DO.OrderDate <= "'.$end_date.'" ';
    $q = 'select DO.*, DC.Name, DC.IDClientComp from dwOrders DO '.
        ' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
        ' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
        ' where 1 '.$add_where.' order by DO.OrderDate,DO.IDOrder';
    $db->query($q);
    $OldCompID = 0;
    while($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_all_company_orders($id_comp,$StDate='',$FinDate='') {
    global $db;
    $ret = array();
    if(string_is_date(sql_to_date($StDate)))  $AddQueryArr[] = ' DO.OrderDate >= "'.$StDate.'" ';
    if(string_is_date(sql_to_date($FinDate))) $AddQueryArr[] = ' DO.OrderDate <= "'.$FinDate.'" ';
    if(count($AddQueryArr) > 0 ) $AddQuery = ' and '. implode(' and ',$AddQueryArr);

    if(string_is_id($id_comp)) {
    $q = 'select DO.*, SUM(DO.Summ) as CompOrdSumm, DC.Name, DC.IDClientComp from dwOrders DO '.
        ' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
        ' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
        ' where DC.IDClientComp = '.$id_comp.$AddQuery.' group by(DO.OrderDate) order by DC.IDClientComp ';
        $db->query($q);
        while($arr = $db->FetchArray()) {
            $ret[] = $arr;
        }
    }
    return $ret;
}

function get_all_oders_by_date($order_date,$id_order=0,$statuses_arr = array(),$idclient=0) {
    global $db;
    $ret_tmp = array();
    $ret = array();
    $add_where = '';
    if (string_is_date($order_date) || $id_order != 0 || $idclient != 0) {
        if ($id_order != 0)  
            $add_where .= ' and DO.IDOrder = '.$id_order.' ';
        if (string_is_date($order_date)) 
            $add_where .= ' and DO.OrderDate = "'.date_to_sql($order_date).'" ';
        
        if (count($statuses_arr) > 0) $add_where .= ' and DO.Status in ('.implode(",", $statuses_arr).')';
        if($idclient != 0)  $add_where .= ' and DU.IDClientUser = '.$idclient.' ';
        $q = 'select DO.*, DC.Name, DC.IDClientComp, concat(DU.Name, " ", DU.FName, " ", DU.OName) as ClientName'.
            ' from dwOrders DO '.
            ' left join dwClientUsers DU on DO.IDClientUser = DU.IDClientUser'.
            ' left join dwClientComp DC on DU.IDClientComp = DC.IDClientComp'.
            ' where 1 '.$add_where.' order by DC.IDClientComp';
        $db->query($q);
        $OldCompID = -1;
        while($arr = $db->FetchArray()) {
            if ($arr['OrderList'] != '') {
                $l1 = split(";",$arr['OrderList']);
                foreach($l1 as $v) {
                    $l2 = split("-",$v);
                    if(isset($l2[1]))
                        $arr['OrderListL'][$l2[0]]['main'] = $l2[1];
                        $arr['OrderListL'][$l2[0]]['price'] = isset($l2[2]) ? $l2[2] : 0;
                }
            }
            $NewCompID = $arr['IDClientComp'];
        //Блок модификаций во избежани пустых элементов
            if ($NewCompID != $OldCompID) {
                $ret_tmp[] = $arr;
            } else {
                $cur_num = count($ret_tmp)-1;
                foreach ($arr['OrderListL'] as $k1 => $v1) {
                    if (!isset($ret_tmp[$cur_num]['OrderListL'][$k1]['main'])) {
                        $ret_tmp[$cur_num]['OrderListL'][$k1]['main'] =  isset($v1['main']) ? $v1['main'] : 0;
                    } else {
                        $ret_tmp[$cur_num]['OrderListL'][$k1]['main'] += isset($v1['main'])? $v1['main'] : 0;
                    }
                }
                if (!isset($ret_tmp[$cur_num]['Summ'])) $ret_tmp[$cur_num]['Summ'] = 0;
                $ret_tmp[$cur_num]['Summ'] += isset($arr['Summ']) ? $arr['Summ'] : 0;
                if(!isset($ret_tmp[$cur_num]['Comments'])) $ret_tmp[$cur_num]['Comments'] = '';
                $ret_tmp[$cur_num]['Comments'] = isset($arr['Comments']) ? $ret_tmp[$cur_num]['Comments']."\n".$arr['Comments'] : $ret_tmp[$cur_num]['Comments'];
            }                       
            $OldCompID = $NewCompID;
        }
    }
    
    if(isset($ret_tmp) && count($ret_tmp)>0) {
        foreach($ret_tmp as $val) {
            if(isset($val['IDClientComp']))
                $ret[$val['IDClientComp']] = $val;
        }
    }
    return $ret;
}

//загрузка меню из файла
function GetMenuArrayFromFile($path) {
    $ret = array();
    $errors = '';
    if (file_exists($path)) {
        if ($fp = fopen($path, "r")) {
            $main_string = fread($fp,filesize($path));
            if ($main_string != '') {
                $arr1 = split("\n",$main_string);
                foreach($arr1 as $k => $v) {
                    $v=ereg_replace("\n",'',$v);
                    $v=ereg_replace("\r",'',$v);
                    $arr2=split(";",$v);

                    foreach($arr2 as $k2 => $v2) {
                        if (is_string($v2) && $v2 != '' && $v2{0} == '"') {
                            // Строки, в которых первый символ является кавычкой, раскавычить,
                            $v2 = substr($v2,1,strlen($v2)-2);
                            // и заменить в них двойные двойные ;-) кавычки одинарными двойными кавычками.
                            $arr2[$k2] = str_replace('""','"',$v2);
                        }
                    }
                    
                    if (isset($arr2[1]) && $arr2[1] != "") { $arr3[$arr2[1]][] = $arr2; }
                }
                $ret = $arr3;
            } else {
                $errors .= "Empty file</br>";
            }
            fclose($fp);
        }
    } else {
        $errors .= "No file found</br>";
    }
    return $ret;
}

function create_new_order_num() {
    $filename = PATH_TO_ROOT.PATH_TO_MENU.'order_num.txt';
    $ret = '';
    if ($fp = fopen($filename, "r")) {
        $main_string = fread($fp,filesize($filename));
        if ($main_string != '') {
            $arr = split(';',$main_string);
        }
        fclose($fp);
    }
    if (count($arr) > 1) {
        if(date('m') != $arr[0]) {
            $arr[1] = 0;
            $arr[0] = date('m');
        }
        $arr[1]++;
        $ret = $arr[1];
    }
    if ($fp = fopen($filename, "w")) {
        $fwr = '';
        for($i=0;$i<2;$i++)
            $fwr .= $arr[$i].';';
        fwrite($fp,$fwr);
        fclose($fp);
    }

    return $ret;
}

function upload_menu() {
    $errors = '';
    $dayw = date('w');
    $filename = 'menu_'.date("d.m.Y",mktime(0,0,0, date('m'), date('d')+8-$dayw, date('Y'))).'.csv';
    $errors = upload_simple_file(PATH_TO_ROOT.PATH_TO_MENU,$filename);
    return $errors;
}

function automate_update_orders_status() {
    global $db;
    $q = "update dwOrders set Status = 5  where NOW() > (OrderDate - INTERVAL 8 HOUR) and Status in (1,0)";
    $q1 = "update dwOrders set Status = 3  where NOW() > (OrderDate - INTERVAL 8 HOUR) and Status = 2";
    $q2 = "update dwOrders set Status = 4  where NOW() > (OrderDate + INTERVAL 16 HOUR) and Status = 3";
    $db->Query($q);
    $db->Query($q1);
    $db->Query($q2);
}
?>