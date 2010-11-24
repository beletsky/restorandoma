<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_users.inc.php                                               #
#   Изменения в таблице пользователей.                                         #
#                                                                              #
################################################################################
/*

  function user_adm_add()      - Функции для работы с пользователями - админами.
  function user_adm_edit()       Возвращают текст ошибки. 
  function user_adm_del()        Принимают на вход массив с параметрами - полями.
  
  function get_group_by_code() - Вернуть ID_Group по коду группы

*/

function spage_edit($data,$isnew=true,$auto=false) {
    global $db;
    $r = '';
    if(!$auto) $r = _spage_check(_page_prepare($data));
    if ($r == '') {
        $arch = isset($data['arhiv']) ? 1 : 0;
        $hasar = isset($data['HasArticles']) ? 1 : 0;

        $q  = $isnew ? 'insert into dwStPages set ' : 'update dwStPages set';
        $q .= isset($data['name']) ? ' name = "' . addslashes($data['name']) . '", ' : '';
        $q .= isset($data['Coment']) ? 'Coment = "' . $data['Coment'] .'", ' : '';
        $q .= isset($data['TxtEditor']) ? 'txtEditor = "' . $data['TxtEditor'] .'", ' : '';
        $q .= isset($data['Title']) ? 'Title = "' . $data['Title'] .'", ' : '';
        $q .= isset($data['Description']) ? 'Description = "' . $data['Description'] .'", ' : '';
        $q .= isset($data['PageCode']) ? 'PageCode = "' . $data['PageCode'] .'", ' : '';
        $q .= isset($data['PageType']) ? 'PageType = "' . $data['PageType'] .'", ' : '';
        $q .= isset($data['Navigation']) ? 'Navigation = "' . $data['Navigation'] .'", ' : '';
        $q .= ' arhiv = ' . $arch. ', ';
        $q .= ' HasArticles = ' . $hasar;
        $q .= $isnew? '' : ' where IDPage = ' . $data['id'];
        $db->Query($q);
    }
    return $r;
}

function spage_del($id) {
    global $db;
    $r = '';
    if (!_is_page_exists($id)) $r .= 'Страница не найдена.<br>';
    if ($r == '') {
        $q = 'delete from dwStPages where IDPage = ' . $id;
        $db->Query($q);
    }
    return $r;
}

//Функция выборки все существующих статичных страниц возвращает код и наименование.
function  get_all_stpages_IDCode() {
    global $db;
    $ret = array();
    $q = 'select IDPage, Name, Title, Description, PageCode from dwStPages order by Name';
    $db->query($q);
    while($tree_arr = $db->FetchArray()) { 
        $ret[$tree_arr['PageCode']] = $tree_arr['Name'];
    }
    return $ret;
}   

//Функция выборки статичных страниц со статьями возвращает код и наименование.
function  get_stpages_with_articles_IDCode() {
    global $db;
    $ret = array();
    $q = 'select IDPage, Name, Title, Description, PageCode from dwStPages where HasArticles=1 order by Name';
    $db->query($q);
    while($tree_arr = $db->FetchArray()) { 
        $ret[$tree_arr['PageCode']] = $tree_arr['Name'];
    }
    return $ret;
}   

// Если isAdd, то проверяем существование польз. с таким логином, 
// иначе проверка на сущ. записи с таким ID и сущ. польз. с таким логином, но другим ID.
function _spage_check($data, $isAdd = false) {
    global $db, $g_prod;
    $r = '';
    
    // Проверка наличия полей
    if ($data['name'] == '') $r .= 'Введите название.<br>';
    
    return $r;
}

function _page_prepare(&$data) {
    $data['name'] = isset($data['name']) ? trim($data['name']) : '';
    return $data;
}

function _is_page_exists($id) {
    return is_obj_exists($id, 'IDPage', 'dwStPages');
}

?>