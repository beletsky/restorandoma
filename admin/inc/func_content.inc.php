<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_content.inc.php                                             #
#   Изменения в таблице контента.                                              #
#                                                                              #
################################################################################
/*

  function content_add()      - Функции для работы с контентом.
  function content_edit()       Возвращают текст ошибки. 
  function content_del()        Принимают на вход массив с параметрами - полями.
  
*/

include_once (PATH_TO_ADMIN . 'inc/func_languages.inc.php');

function content_add($data) {
    global $db;
    $r = _content_check(_content_prepare($data), true);
    if ($r == '') {
        $q  = 'insert into Content set';
        $q .= ' ContentCode = "' . addslashes($data['ContentCode']) . '",';
        $q .= ' ContentName = "' . addslashes($data['ContentName']) . '",';
        $q .= ' ContentURL = "' . addslashes($data['ContentURL']) . '",';
        $q .= ' ContentSearchable = ' . (isset($data['ContentSearchable']) && $data['ContentSearchable'] ? '1' : '0') ;
        $db->Lock(array('Content', 'ContentValues'));
        $db->Query($q);
        $id = $db->GetInsertId();
        foreach ($data as $k => $v)
            if (is_array($v) && string_is_id($k)) 
                _content_values_save($id, $k, $v['ContentValue']);
        $db->Unlock();
    }
    return $r;
}

function content_edit($data) {
    global $db;
    $r = '';
    $r = _content_check(_content_prepare($data));
    if ($r == '') {
        $q  = 'update Content set';
        $q .= ' ContentCode = "' . addslashes($data['ContentCode']) . '",';
        $q .= ' ContentName = "' . addslashes($data['ContentName']) . '",';
        $q .= ' ContentURL = "' . addslashes($data['ContentURL']) . '",';
        $q .= ' ContentSearchable = ' . (isset($data['ContentSearchable']) && $data['ContentSearchable'] ? '1' : '0');
        $q .= ' where ID_Content = ' . $data['ID_Content'];
        $db->Lock(array('Content', 'ContentValues'));
        $db->Query($q);
        foreach ($data as $k => $v)
            if (is_array($v) && string_is_id($k)) 
                _content_values_save($data['ID_Content'], $k, $v['ContentValue']);
        $db->Unlock();
    }
    return $r;
}

function content_del($id) {
    global $db;
    $r = '';
    if (!_is_content_exists($id)) $r .= 'Запись не найдена.<br>';
    if ($r == '') {
        $db->Lock(array('Content', 'ContentValues'));
        $q = 'delete from ContentValues where ID_Content = ' . $id;
        $db->Query($q);
        $q = 'delete from Content where ID_Content = ' . $id;
        $db->Query($q);
        $db->Unlock();
    }
    return $r;
}

function _content_values_save($idContent, $idLanguage, $contentValue) {
    global $db;
    if (string_is_id($idContent) && string_is_id($idLanguage)) {
        $db->Query("select * from ContentValues where ID_Content = $idContent and ID_Language = $idLanguage");
        if ($db->Nf()) {
            $q  = 'update ContentValues set';
            $q .= ' ContentValue = "' . addslashes($contentValue) . '"';
            $q .= ' where ID_Content = ' . $idContent . ' and ID_Language = ' . $idLanguage;
        } else {
            $q  = 'insert into ContentValues set';
            $q .= ' ID_Content = ' . $idContent . ',';
            $q .= ' ID_Language = ' . $idLanguage . ',';
            $q .= ' ContentValue = "' . addslashes($contentValue) . '"';
        }
        $db->Query($q);
    }
}

function _content_check($data, $isAdd = false) {
    global $db;
    $r = '';
    
    // Проверка наличия полей
    if ($data['ContentName'] == '') $r .= 'Введите имя.<br>';
    if ($data['ContentCode'] == '') $r .= 'Введите код.<br>';
    if ($data['ContentCode'] != '' && !string_is_login($data['ContentCode'])) $r .= 'Код содержит недопустимые символы.<br>';
    // Проверим наличие записи с таким же кодом
    if ($r == '') {
        $q = 'select ID_Content from Content where ContentCode = "' . $data['ContentCode'] . '"';
        if (!$isAdd) $q .= ' and ID_Content <> ' . $data['ID_Content'];
        $db->Query($q);
        if ($db->Nf()) $r .= 'Контент с таким кодом уже зарегистрирован.<br>';
    }
    // Если не isAdd, проверим, существует ли запись с таким ID
    if ($r == '' && !$isAdd && !_is_content_exists($data['ID_Content'])) $r .= 'Контент не найден.<br>';
    // Если isAdd 
    // Для каждого языка проверим его существование в базе
    foreach ($data as $k => $v) {
        if (is_array($v)) {
            if (!string_is_id($k)) $r .= "Неверный код языка $k.<br>";
            if (!_is_language_exists($k)) $r .= "Язык с кодом $k не найден.<br>";
        }
    }
    
    return $r;
}

function _content_prepare(&$data) {
    $data['ContentCode'] = isset($data['ContentCode']) ? trim($data['ContentCode']) : '';
    $data['ContentName'] = isset($data['ContentName']) ? trim($data['ContentName']) : '';
    $data['ContentURL'] = isset($data['ContentURL']) ? trim($data['ContentURL']) : '';
    return $data;
}

function _is_content_exists($id) {
    return is_obj_exists($id, 'ID_Content', 'Content');
}

?>