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

function quest_edit($data,$is_new = true) {
    global $db;
    $r = '';
    $data = _quest_prepare($data);
    $db->Lock(array('dwQuestAns'));
    $r = _quest_check($data,$is_new);
    if ($r == '') {
        $q  = $is_new ? 'insert into dwQuestAns set ':'update dwQuestAns set ';
        $q .= isset($data['PageTitle']) ? ' PageTitle = "' . addslashes($data['PageTitle']) . '",' : '' ;
        $q .= isset($data['Answer']) ? ' Answer = "' . addslashes($data['Answer']) . '",' : '' ;
        $q .= isset($data['Question']) ? ' Question = "' . addslashes($data['Question']) . '" ' : '' ;
        $q .= $is_new ? '':' where IDLine = ' . $data['IDLine'];
        $db->Query($q);
    }
    $db->Unlock();
    return $r;
}

function quest_del($id) {
    global $db;
    $r = '';
    if (!_is_quest_exists($id)) $r .= 'Ресурс не найден.<br>';
    if ($r == '') {
        $q1 = 'delete from dwQuestAns where IDLine = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _quest_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // Проверка наличия полей
    if ($data['Question'] == '') $r .= 'Введите текст вопроса.<br>';
    // Если не isAdd, проверим, существует ли запись с таким ID
    if ($r == '' && !$isAdd && !_is_quest_exists($data['IDLine'])) $r .= 'Новость не найдена.<br>';
    return $r;
}

function _quest_prepare(&$data) {
    $data['Question'] = isset($data['Question']) ? trim($data['Question']) : '';
    return $data;
}

function _is_quest_exists($id) {
    return is_obj_exists($id, 'IDLine', 'dwQuestAns');
}
?>