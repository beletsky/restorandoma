<?
################################################################################
#   
#   
#   
#   Copyright (с) 2003
#                     
#   inc/func.inc.php  
#   Общие функции.    
#                     
################################################################################
/*

  string get_formatted_error(string err)      - Отдает форматированную ошибку как строку.
  string get_formatted_message(string msg)    - Отдает форматированное сообщение как строку.
  
     int get_group_id(string groupCode)       - Возвращает ID_Group под заданному GroupCode
  
*/

function get_formatted_error($err) {
    return $err != '' ? '<p class=err>' . $err . '</p>' : '';
}

function get_formatted_message($msg) {
    return $msg != '' ? '<p>' . $msg . '</p>' : '';
}

function get_group_id($groupCode) {
    global $db;
    $r = '';
    if (string_is_login($groupCode)) {
        $db->Query('select ID_Group from Groups where GroupCode = "' . addslashes($groupCode) . '"');
        if ($db->NextRecord()) $r = $db->F(0);
    }
    return $r;
}

function get_catalog_menu($level = 0, $childs_only = false, $leftt = 0, $rightt = 0) {
    global $db;
    $ret = array();
    $lev = ($level != 0) ? 'and DC.level = "'.$level.'" ' : '';
    $addon = $childs_only ? ' and DC.leftt > '.$leftt.' and DC.rightt < '. $rightt.' ' : '';
    $q = 'select DC.*, DSP.IDPage, DSP.PageCode, DSP.HasArticles from dwCategories DC '.
         'left join dwCatPages DCP on DC.ID_Cat = DCP.IDCat left join dwStPages DSP '.
         'on DCP.IDPage = DSP.IDPage where DC.level > 0 '. $lev . $addon .' order by DC.leftt ';
    $db->Query($q);
    while($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_menu_pages($menu_code, $IncludeChilds = false) {
    global $db;
    $ret = array();
    $addon = $IncludeChilds ? '' : ' and DC.level = "1" ';
    
    $q = 'select DC.*, DSP.IDPage, DSP.PageCode, DSP.HasArticles from dwCategories DC '.
         'left join dwCatPages DCP on DC.ID_Cat = DCP.IDCat left join dwStPages DSP '.
         'on DCP.IDPage = DSP.IDPage where DC.level > 0 and Navigation = "'.$menu_code.'" '. $addon .' order by DC.leftt ';
    $db->Query($q);
    while($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_all_questios() {
    global $db;
    $ret = array();
    $db->query('select * from dwQuestAns');
    while ($row = $db->FetchArray()) $ret[] = $row;
    return $ret;
}

function get_one_questios($id) {
    global $db;
    $ret = array();
    if (string_is_id($id)) {
        $db->query('select *from dwQuestAns where IDLine = '. $id);
        if ($row = $db->FetchArray()) $ret = $row;
    }
    return $ret;
}


/*
*   News.
*/

function get_news_list($page_num,$on_page=10) {
    global $db;
    $ret = array();
    $l_st = 0+$on_page*$page_num;
    $q = "select * from dwNews ORDER BY DateNews DESC limit $l_st,$on_page";
    $db->Query($q);
    while ($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_one_news($id) {
    global $db;
    $ret = array();
    $q = "select * from dwNews where IDNews = $id";
    if(string_is_id($id)) {
        $db->query($q);
        if($ar = $db->FetchArray()) $ret = $ar;
    } else {
        $ret = "Неверный ID новости";
    }
    return $ret;
}

function get_news_count() {
    global $db;
    $ret = 0;
    $db->Query('select count(*) from dwNews');
    if($db->NextRecord()) $ret = $db->F(0);
    return $ret;
}


/*
*   Firms.
*/

function get_firms_list($page_num,$on_page=10) {
    global $db;
    $ret = array();
    $l_st = 0+$on_page*$page_num;
    $q = "select * from dwFirms limit $l_st,$on_page";
    $db->Query($q);
    while ($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_firms_count() {
    global $db;
    $ret = 0;
    $db->Query('select count(*) from dwFirms');
    if($db->NextRecord()) $ret = $db->F(0);
    return $ret;
}


/*
*   Articles
*/

function get_articles_list($PageCode,$page_num,$on_page=10) {
    global $db;
    $ret = array();
    $l_st = 0+$on_page*$page_num;
    $q = "select * from dwArticles where PageCode = '$PageCode' ORDER BY IDArticle DESC limit $l_st,$on_page";
    $db->Query($q);
    while ($arr = $db->FetchArray()) {
        $ret[] = $arr;
    }
    return $ret;
}

function get_one_article($id) {
    global $db;
    $ret = array();
    $q = "select * from dwArticles where IDArticle = $id";
    if(string_is_id($id)) {
        $db->query($q);
        if($ar = $db->FetchArray()) $ret = $ar;
    } else {
        $ret = "Неверный ID статьи";
    }
    return $ret;
}

function get_articles_count() {
    global $db;
    $ret = 0;
    $db->Query('select count(*) from dwArticles');
    if($db->NextRecord()) $ret = $db->F(0);
    return $ret;
}


function load_menu() {
    global $s_date_arr;
    $menu_cont = array();
    $menu_table = '';
    $dayw = date('w',mktime(0,0,0,$s_date_arr[1],$s_date_arr[0],$s_date_arr[2]));
    $file_name = 'menu_'.date("d.m.Y",mktime(0,0,0, $s_date_arr[1], $s_date_arr[0]-$dayw+1, $s_date_arr[2])).'.csv';
    $menu_cont = GetMenuArrayFromFile(PATH_TO_ROOT.PATH_TO_MENU.$file_name);
    return $menu_cont;
}
function mail2admin($from,$subject,$message,$admin_mail) {
    $to=convert_cyr_string($admin_mail,'w','k');
    $from1=convert_cyr_string($from,'w','k');
    $from="Content-Type: text/plain; charset=\"koi8-r\"\r\nContent-Transfer-Encoding: 8bit\nFrom: $from1\n";
    $message=convert_cyr_string($message,'w','k');
    $subject=convert_cyr_string($subject,'w','k');
    @mail($to, $subject, $message, $from);
//  $show="From: $from <br>\n To:$to <br>\n Subject: $subject <br>\n Message: $message  <br>\n======================<br>\n";
}

function mail2user($to,$subject,$message) {
    $to=convert_cyr_string($to,'w','k');
    $from1=convert_cyr_string('"РесторанДома" <restorandoma.ru>','w','k');
    $from="Content-Type: text/plain; charset=\"koi8-r\"\r\nContent-Transfer-Encoding: 8bit\nFrom: $from1\n";
    $message=convert_cyr_string($message,'w','k');
    $subject=convert_cyr_string($subject,'w','k');
    @mail($to, $subject, $message, $from);
//  $show="From: $from <br>\n To:$to <br>\n Subject: $subject <br>\n Message: $message  <br>\n======================<br>\n";
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

function check_email( $email )
{
    $email_regexp = '/^[a-zA-Z0-9][\w0-9\.-]*[a-zA-Z0-9]@[a-zA-Z0-9][\w0-9\.-]*[a-zA-Z0-9]\.[a-zA-Z][a-zA-Z\.]*[a-zA-Z]$/';
    return ( preg_match( $email_regexp, $email ) == 1 );
}

function check_phone( $phone, $digits = 11 )
{
    // Check present of disallowed chars.
    $disallowed_chars_regexp = '/[^\d\s\(\)\-]/isU';
    if( preg_match( $disallowed_chars_regexp, $phone ) > 0 ) return false;
    
    // Check count of digits.
    $matches = array();
    if( preg_match_all( '/\d/', $phone, $matches ) != $digits ) return false;
    
    return true;
}

?>