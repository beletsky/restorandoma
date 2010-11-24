<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_customers.inc.php                                           #
#   Изменения в таблице ресурсов.                                              #
#                                                                              #
################################################################################
/*

  function catalog_add()      - Функции для работы с ресурсами.
  function catalog_edit()       Возвращают текст ошибки.
  function catalog_del()        Принимают на вход массив с параметрами - полями.

*/

function article_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _article_prepare($data);
    $db->Lock(array('dwArticles'));
    $r = _article_check($data,$is_new);
    if ($r == '') {
        $q  = $is_new ? 'insert into dwArticles set ':'update dwArticles set ';
        $q .= isset($data['PageTitle']) ? ' PageTitle = "' . addslashes($data['PageTitle']) . '",' : '' ;
        $q .= isset($data['Title']) ? ' Title = "' . addslashes($data['Title']) . '",' : '' ;
        $q .= isset($data['Announce']) ? ' Announce = "' .  addslashes($data['Announce']) . '",' : '' ;
        $q .= isset($data['PageCode']) ? ' PageCode = "' .  $data['PageCode'] . '",' : '' ;
        $q .= isset($data['Content']) ? ' Content = "' .  addslashes($data['Content']) . '" ' : '' ;
        $q .= $is_new ? '':' where IDArticle = ' . $data['IDArticle'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function article_del($id) {
    global $db;
    $r = '';
    if (!_is_article_exists($id)) $r .= 'Ресурс не найден.<br>';
    if ($r == '') {
        $q1 = 'delete from dwArticles where IDArticle = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _article_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // Проверка наличия полей
    if ($data['Title'] == '') $r .= 'Введите имя.<br>';
    if ($data['Content'] == '') $r .= 'Введите статьи новости.<br>';
    // Если не isAdd, проверим, существует ли запись с таким ID
    if ($r == '' && !$isAdd && !_is_article_exists($data['IDArticle'])) $r .= 'Новость не найдена.<br>';

    return $r;
}

function _article_prepare(&$data) {
    $data['Title'] = isset($data['Title']) ? trim($data['Title']) : '';
    $data['Content'] = isset($data['Content']) ? trim($data['Content']) : '';
    return $data;
}

function _is_article_exists($id) {
    return is_obj_exists($id, 'IDArticle', 'dwArticles');
}
?>