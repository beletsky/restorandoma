<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/inc/func.inc.php                                                     #
#   ����� ������� ������ �����������������.                                    #
#                                                                              #
################################################################################
/*

  string get_formatted_error(string err)      - ������ ��������������� ������ ��� ������.
  string get_formatted_message(string msg)    - ������ ��������������� ��������� ��� ������.
  string get_header(string str)               - ������ ��������� ��� ������.
  string get_subheader(string str)            - ������ ������������ ��� ������.
  string get_link(string str, string lnk)     - ������ ������ � ������� str �� �������� lnk.
  string get_inpage_array()                   - ������ ������ ��� ��������� ���������� ������� � �������.
   array get_admin_groups()                   - ������� ������ ��������� ����� �������������.
  string get_delete_script(string lnk, string msg = '')        - ������� <SCRIPT> ��� ������� �������� ������ �� ������� �� lnk � ������� msg.
    bool is_obj_exists(int id, string idField, string tblName) - �������� ������������� ������ � ������� tblName, ��� idField = id

*/

function get_formatted_error($err) {
    return $err != '' ? '<p><font color=red>' . $err . '</font></p>' : '';
}

function get_formatted_message($msg) {
    return $msg != '' ? '<p>' . $msg . '</p>' : '';
}

function get_header($str) {
    return $str != '' ? '<h1>' . $str . '</h1>' : '';
}

function get_subheader($str) {
    return $str != '' ? '<h2>' . $str . '</h2>' : '';
}

function get_link($str, $lnk) {
    return $str != '' ? '<p>' . ($lnk != '' ? '<a href="' . $lnk . '">' : '') . $str . ($lnk != '' ? '</a>' : '') . '</p>' : '';
}

function get_inpage_array() {
    return array('10' => '10', '25' => '25', '100' => '100', '500' => '500', '0' => '���');
}

function get_admin_groups() {
    global $db;
    $r = array();
    $db->Query('select GroupCode, GroupName from dwGroups where GroupAccessBackEnd = 1');
    while ($db->NextRecord()) $r[$db->f('GroupCode')] = $db->f('GroupName');
    return $r;
}

function get_all_admin_groups() {
    global $db;
    $r = array();
    $db->Query('select GroupCode, GroupName from dwGroups ');
    while ($db->NextRecord()) $r[$db->f('GroupCode')] = $db->f('GroupName');
    return $r;
}

function get_delete_script($lnk, $msg = '', $funcName = '') {
    if ($msg == '') $msg = '����������� ��������?';
    if ($funcName == '') $funcName = 'deleteRecord';
    $r = "<script language=javascript><!--\n" .
         "function $funcName(id){if(confirm('$msg'))location.href='$lnk'+id;}\n" .
         "//--></script>";
    return $r;
}

function is_obj_exists($id, $idField, $tblName) {
    global $db;
    $r = false;
    if (string_is_id($id)) {
        $db->Query('select ' . $idField . ' from ' . $tblName . ' where ' . $idField . ' = ' . $id . ' limit 1');
        if ($db->Nf()) $r = true;
    }
    return $r;
}

?>