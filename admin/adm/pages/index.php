<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/adm/pages/index.php                                                  #
#   ��������.                                                                  #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../../');
define ('PATH_TO_ADMIN', '../../');
define ('PAGE_TITLE',    '�����������������. ��������');
define ('PAGE_CODE',     'adm_pages');

define ('ACT_LIST',      'list');
define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');
define ('ACT_DEL_PIC',   'del_pic');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func_pages.inc.php');

print get_header('��������');

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
    $form['ID_Page'] = $id;
}

// �������� ���������
$msg = '';
$err = '';
switch ($action) {
    case ACT_ADD_PROC: {
        if ($err = page_add($form)) {
            $action = ACT_ADD;
        } else {
            $form = array();
            $action = ACT_LIST;
        }
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = page_edit($form)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_LIST;
            $form = array();
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = page_del($id);
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
    global $this_page, $db;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_block('main', 'resource', 'resources');
    $tpl->set_var('resources', '');
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['ID_Page']) ? $form['ID_Page'] : '');
    $tpl->set_var('CODE', isset($form['PageCode']) ? htmlspecialchars($form['PageCode']) : '');
    $tpl->set_var('NAME', isset($form['PageName']) ? htmlspecialchars($form['PageName']) : '');
    $tpl->set_var('PATH', isset($form['PagePath']) ? htmlspecialchars($form['PagePath']) : '');
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? '��������' : '��������');
    $tpl->set_var('ACTION', $action);
    // ������� ������ �������� � ������� ���
    $db->Query('select * from dwResources order by ResourceName');
    while ($db->NextRecord()) {
        $tpl->set_var('ID_RESOURCE', $db->F('ID_Resource'));
        $tpl->set_var('RESOURCE_NAME', $db->F('ResourceName'));
        $tpl->set_var('USED', isset($form['resources'][$db->F('ID_Resource')]) && $form['resources'][$db->F('ID_Resource')] ? ' checked' : '');
        $tpl->parse('resources', 'resource', true);
    }
    return $tpl->parse('C', 'main', false);
}

# ������
function GetList() {
    global $db, $this_page;
    $db->Query('select count(*) from dwPages');
    $cnt = $db->NextRecord() ? $db->F(0) : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'name';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mSessionPrefix = 'a_a_p';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'ID_Page', 'code' => 'PageCode', 'name' => 'PageName', 'path' => 'PagePath');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', '���', '��������', '����', '��������'), 
                  array('�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', 
                        '�������� ��� ���������� �� �������', ''));
    $q = 'select * from dwPages' . $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord()) 
        $tbl->SetRow(array($db->F('ID_Page'), 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Page') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('PageCode')) . '</a>', 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Page') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('PageName')) . '</a>',
                           '<a href="' . PATH_TO_ADMIN . $db->F('PagePath') . '" target=_blank>' . htmlspecialchars($db->F('PagePath')) . '</a>',
                           '<center><a href="javascript:deleteRecord(' . $db->F('ID_Page') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="�������"></a></center>',
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
        $db->Query('select * from dwPages where ID_Page = ' . $id);
        if ($db->NextRecord()) $r = $db->mRecord;
        
        // ������� ������ ��������
        $q = 'select ID_Resource from dwPageResources where ID_Page = ' . $id;
        $db->Query($q);
        while ($db->NextRecord()) $r['resources'][$db->F('ID_Resource')] = true;
        
    }
    return $r;
}

?>