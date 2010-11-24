<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/adm/options/index.php                                                #
#   ��������� � ������.                                                        #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../../');
define ('PATH_TO_ADMIN', '../../');
define ('PAGE_TITLE',    '�����������������. ��������� � ������');
define ('PAGE_CODE',     'adm_options');

define ('ACT_LIST',      'list');
define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');
define ('ACT_DEL_PIC',   'del_pic');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func_options.inc.php');

print get_header('��������� � ������');

// ��������� ������� ��������
$action = '';
if (isset($HTTP_GET_VARS['a'])) $action = $HTTP_GET_VARS['a'];
if ($action == '' && isset($HTTP_POST_VARS['a'])) $action = $HTTP_POST_VARS['a'];
if ($action == '') $action = ACT_LIST;

// ������� ���������
if (!in_array($action, array(ACT_LIST, ACT_DEL_PIC))) {
    $id = 0;
    if (isset($HTTP_GET_VARS['id'])) $id = $HTTP_GET_VARS['id'];
    if (!string_is_id($id) && isset($HTTP_POST_VARS['id'])) $id = $HTTP_POST_VARS['id'];
    $form = GetData($id);
    $form['ID_Option'] = $id;
}

// �������� ���������
$msg = '';
$err = '';
switch ($action) {
    case ACT_ADD_PROC: {
        if ($err = options_add($form)) {
            $action = ACT_ADD;
        } else {
            $form = array();
            $action = ACT_LIST;
        }
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = options_edit($form)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_LIST;
            $form = array();
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = options_del($id);
        $action = ACT_LIST;
        $form = array();
        break;
    }
}

// ������� �����
print get_subheader($action == ACT_LIST ? '������' : ($action == ACT_ADD ? '����������' : '��������������'));
print get_formatted_error($err);
if ($action != ACT_LIST) {

    print get_link('��������� � ������', $this_page);
    print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

} else {    

    // ������� ������
    print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');
    print get_link('��������', $this_page . '?a=' . ACT_ADD);
    print get_formatted_message($msg);
    print GetList();
 
}

require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# �����
function GetForm($form, $action) {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_block('main', 'value', 'values');
    $tpl->set_var('values', '');
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['ID_Option']) ? $form['ID_Option'] : '');
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? '��������' : '��������');
    $tpl->set_var('ACTION', $action);
    // ������������ ����� -- ������ ������ ����� �� ���������� ��������
    $maxNum = 0;
    // ����� ��������� ������ � �������
    $lastRow = 0;
    // ������� ��������
    $i = 0;
    if (isset($form['values']) && is_array($form['values']))
        foreach ($form['values'] as $v)
            if (is_array($v)) {
                if (!$v['OptionOrder']) $v['OptionOrder'] = 1;
                $tpl->set_var('I', $i);
                $tpl->set_var('OPTION_CODE', isset($v['OptionSubCode']) ? htmlspecialchars($v['OptionSubCode']) : '');
                $tpl->set_var('OPTION_VALUE', isset($v['OptionValue']) ? htmlspecialchars($v['OptionValue']) : '');
                $tpl->set_var('OPTION_ORDER', isset($v['OptionOrder']) ? htmlspecialchars($v['OptionOrder']) : '');
                if (isset($v['OptionOrder']) && string_is_int($v['OptionOrder']) && $v['OptionOrder'] > $maxNum) 
                    $maxNum = $v['OptionOrder'];
                $tpl->parse('values', 'value', true);
                $i++;
                if ((isset($v['OptionSubCode']) && $v['OptionSubCode'] != '') 
                	|| (isset($v['OptionValue']) && $v['OptionValue'] != '')) $lastRow = $i;
            }
    // ������� ������ ������
    $rowCnt = 20 - $i + $lastRow;
    for ($j = 0; $j < $rowCnt; $j++) {
        $maxNum++;
        $tpl->set_var('I', $i);
        $tpl->set_var('OPTION_CODE', '');
        $tpl->set_var('OPTION_VALUE', '');
        $tpl->set_var('OPTION_ORDER', $maxNum);
        $tpl->parse('values', 'value', true);
        $i++;
    }
	$tpl->set_var('OPTION_CODE', isset($form['OptionCode']) ? htmlspecialchars($form['OptionCode']) : '');
    $tpl->set_var('OPTION_NAME', isset($form['OptionName']) ? htmlspecialchars($form['OptionName']) : '');
    return $tpl->parse('C', 'main', false);
}

# ������
function GetList() {
    global $db, $this_page;
    $db->Query('select count(*) from dwOptions where ID_Parent is null');
    $cnt = $db->NextRecord() ? $db->F(0) : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mSessionPrefix = 'a_a_o';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'o.ID_Option', 'code' => 'OptionCode', 'name' => 'OptionName', 
                                 'value' => 'OptionValue', 'childcnt' => 'ChildCnt');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', '���', '��������', '��������', '���-��&nbsp;��-�&nbsp;������', '��������'), 
                  array('�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', 
                        '�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', ''));
    $q  = 'select o.*, count(l.ID_Option) as ChildCnt from dwOptions o left join dwOptions l on l.ID_Parent = o.ID_Option' .
          ' where o.ID_Parent is null group by o.ID_Option' . 
          $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord()) 
        $tbl->SetRow(array($db->F('ID_Option'), 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Option') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('OptionCode')) . '</a>', 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Option') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('OptionName')) . '</a>',
                           $db->F('ChildCnt') ? '-������-' : htmlspecialchars($db->F('OptionValue')),
                           '<center>' . ($db->F('ChildCnt') ? $db->F('ChildCnt') + 1 : '-') . '</center>',
                           '<center><a href="javascript:deleteRecord(' . $db->F('ID_Option') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="�������"></a></center>',
                           ));
     
    return $tbl->GetTable();
}

# �������� ��������� ��� ������ �� �������� ��� �� ����
function GetData($id) {
    global $HTTP_POST_VARS;
    $form = isset($HTTP_POST_VARS['form']) ? $HTTP_POST_VARS['form'] : GetDbData($id);
    return $form;
}

# �������� ��������� ��� ������ �� ����
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
    
        // ������� ���������
        $db->Query('select * from dwOptions where ID_Option = ' . $id);
        if ($db->NextRecord()) $r = $db->mRecord;
        $r['values'][0]['OptionSubCode'] = $db->F('OptionSubCode');
        $r['values'][0]['OptionValue'] = $db->F('OptionValue');
        $r['values'][0]['OptionOrder'] = $db->F('OptionOrder');
        
        // ������� ������ ����������� ���������
        $db->Query('select * from dwOptions where ID_Parent = ' . $id . ' order by OptionOrder');
        $i = 1;
        while ($db->NextRecord()) {
            $r['values'][$i]['OptionSubCode'] = $db->F('OptionSubCode');
            $r['values'][$i]['OptionValue'] = $db->F('OptionValue');
            $r['values'][$i]['OptionOrder'] = $db->F('OptionOrder');
            $i++;
        }
    }
    return $r;
}

?>