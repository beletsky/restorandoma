<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   admin/login_stat/index.php                                                 #
#   Статистика по времени захода на сайт.                                      #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Статистика времени захода менеджеров на сайт');
define ('PAGE_CODE',     'login_stat');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_login_stat.inc.php');


print get_header('Заходы менеджеров клиентов на сайт за последние 24 часа:');

print get_subheader('Список');

print GetForm();

require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm() {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page, $g_options;

    $db->Query('select CC.Name as CompName,CC.OrgType,CU.*,LT.LoginTime'.
        ' from dwLoginStat LT inner join dwClientUsers CU on LT.IDClientUser = CU.IDClientUser'.
        ' inner join dwClientComp CC on CU.IDClientComp = CC.IDClientComp'.
        ' inner join dwUsers U on CU.IDUser = U.ID_User inner join dwGroups G on U.ID_Group = G.ID_Group'.
        ' where G.GroupCode = "ClientAdmins" and LT.LoginTime >='.(time() - 60*60*24));
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'name';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'login_stat_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('comp_name' => 'CompName', 'name' => 'Name' );

    $tbl->SetInPageOptions(-1);
    $tbl->SetHead($this_page, array('Название компании','Имя менеджера', 'Время захода на сайт'),
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', ''));

    $q  = 'select CC.Name as CompName,CC.OrgType,CU.*,LT.LoginTime'.
        ' from dwLoginStat LT inner join dwClientUsers CU on LT.IDClientUser = CU.IDClientUser'.
        ' inner join dwClientComp CC on CU.IDClientComp = CC.IDClientComp'.
        ' inner join dwUsers U on CU.IDUser = U.ID_User inner join dwGroups G on U.ID_Group = G.ID_Group'.
        ' where G.GroupCode = "ClientAdmins" and LT.LoginTime >='.(time() - 60*60*24). 
        $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);
    $arr = array();
    while ($tmp = $db->FetchArray()) {
        $arr[] = $tmp;
    }

    if (!count($arr)) return'';

    $org_types_arr = $g_options->GetOptionList('org_type');
    $times = '';    
    $i = 0;
    $val = $arr[$i];
    $last_client_id = $val['IDClientUser'];
    while($i < count($arr)) {
        $val = $arr[$i];
        if( $last_client_id != $val['IDClientUser'] ) {
            $tbl->SetRow(array($comp_name,$name, $times));
            $times = '';
        }
        $comp_name = $org_types_arr[$val['OrgType']].' "'.$val['CompName'].'"';
        $name = $val['Name'].' '.$val['FName'].' '.$val['OName'];
        $times .= date('H:i d.m.Y',$val['LoginTime']).'<br>';
        $last_client_id = $val['IDClientUser'];
        ++$i;
    }
    $tbl->SetRow(array($comp_name,$name, $times));

    return $tbl->GetTable();
}

?>