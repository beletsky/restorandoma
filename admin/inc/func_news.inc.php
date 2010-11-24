<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func_customers.inc.php                                           #
#   ��������� � ������� ��������.                                              #
#                                                                              #
################################################################################
/*

  function catalog_add()      - ������� ��� ������ � ���������.
  function catalog_edit()       ���������� ����� ������.
  function catalog_del()        ��������� �� ���� ������ � ����������� - ������.

*/

function news_edit($data,$isnew = true) {
    global $db;
    $r = '';
    $data = _news_prepare($data);
    $db->Lock(array('dwNews'));
    $r = _news_check($data,$isnew);
    if ($r == '') {
        $q  = $isnew ? 'insert into dwNews set ':'update dwNews set ';
        $q .= isset($data['PageTitle']) ? ' PageTitle = "' . addslashes($data['PageTitle']) . '",' : '' ;
        $q .= isset($data['Title']) ? ' Title = "' . addslashes($data['Title']) . '",' : '' ;
        $q .= isset($data['Announce']) ? ' Announce = "' . ($data['Announce']) . '",' : '' ;
        $q .= isset($data['Content']) ? ' Content = "' . ($data['Content']) . '",' : '' ;
        $q .= isset($data['DateNews']) ? ' DateNews = "' . date_to_sql($data['DateNews']) . '" ' : '' ;
        $q .= $isnew ? '':' where IDNews = ' . $data['IDNews'];
        $db->Query($q);
        if($isnew) {
            $q = 'select last_insert_id()';
            $db->Query($q);
            $db->NextRecord() ? $news_id = $db->F(0) : $news_id = 0;
            $data['IDNews'] = $news_id;
        } else {
            $news_id = $data['IDNews'];
        }

    }
    $db->Unlock();
    return $r;
}

function news_del($id) {
    global $db;
    $r = '';
    if (!_is_news_exists($id)) $r .= '������ �� ������.<br>';
    if ($r == '') {
        $q1 = 'delete from dwNews where IDNews = ' . $id;
        $db->Query($q1);
    }
    return $r;
}

function _news_check($data, $isAdd = false) {
    global $db;
    $r = '';

    // �������� ������� �����
    if ($data['Title'] == '') $r .= '������� ���.<br>';
    if ($data['Content'] == '') $r .= '������� ����� �������.<br>';
    if (!string_is_date($data['DateNews'])) $r .= '���� ������� ������� �����������.<br>';
    // ���� �� isAdd, ��������, ���������� �� ������ � ����� ID
    if ($r == '' && !$isAdd && !_is_news_exists($data['IDNews'])) $r .= '������� �� �������.<br>';

    return $r;
}

function _news_prepare(&$data) {
    $data['Title'] = isset($data['Title']) ? trim($data['Title']) : '';
    $data['Content'] = isset($data['Content']) ? trim($data['Content']) : '';
    return $data;
}

function _is_news_exists($id) {
    return is_obj_exists($id, 'IDNews', 'dwNews');
}
?>