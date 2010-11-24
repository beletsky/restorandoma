<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/adm/resources/index.php                                              #
#   �������.                                                                   #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../../');
define ('PATH_TO_ADMIN', '../../');
define ('PAGE_TITLE',    '�����������������. �������');
define ('PAGE_CODE',     'adm_resources');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_resources.inc.php');

print get_header('�������');

// ��������� ������� ��������
$action = '';
if (isset($HTTP_GET_VARS['a'])) $action = $HTTP_GET_VARS['a'];
if ($action == '' && isset($HTTP_POST_VARS['a'])) $action = $HTTP_POST_VARS['a'];
if ($action == '') $action = ACT_ADD;

// ������� ���������
$id = 0;
if (isset($HTTP_GET_VARS['id'])) $id = $HTTP_GET_VARS['id'];
if (!string_is_id($id) && isset($HTTP_POST_VARS['id'])) $id = $HTTP_POST_VARS['id'];
$form = GetData($id);
$form['ID_Resource'] = $id;

// �������� ���������
$msg = '';
$err = '';
switch ($action) {
    case ACT_ADD_PROC: {
        $err = resource_add($form);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = resource_edit($form)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = array();
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = resource_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }
}

// ������� �����
print get_subheader($action == ACT_ADD ? '����������' : '��������������');
print get_formatted_error($err);
print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

// ������� ������
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');
print get_subheader('������');
if ($action != ACT_ADD) print get_link('��������', $this_page);
print get_formatted_message($msg);
print GetList();

require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# �����
function GetForm($form, $action) {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['ID_Resource']) ? $form['ID_Resource'] : '');
    $tpl->set_var('NAME', isset($form['ResourceName']) ? htmlspecialchars($form['ResourceName']) : '');
    $tpl->set_var('CODE', isset($form['ResourceCode']) ? htmlspecialchars($form['ResourceCode']) : '');
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? '��������' : '��������');
    $tpl->set_var('ACTION', $action);
    return $tpl->parse('C', 'main', false);
}

# ������
function GetList() {
    global $db, $this_page;
    $db->Query('select count(*) from dwResources');
    $cnt = $db->NextRecord() ? $db->F(0) : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'name';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mSessionPrefix = 'a_a_r';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'ID_Resource', 'name' => 'ResourceName', 'code' => 'ResourceCode');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', '��������', '���', '��������'), 
                  array('�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', ''));
    $q  = 'select * from dwResources' . $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord()) 
        $tbl->SetRow(array($db->F('ID_Resource'), 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Resource') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('ResourceName')) . '</a>', 
                           '<a href="' . $this_page . '?id=' . $db->F('ID_Resource') . '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('ResourceCode')) . '</a>', 
                           '<center><a href="javascript:deleteRecord(' . $db->F('ID_Resource') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="�������"></a></center>',
                           ));
     
    return $tbl->GetTable();
}

# �������� ��������� ��� ������ �� �������� ��� �� ����
function GetData($id) {
    global $HTTP_POST_VARS;
    return isset($HTTP_POST_VARS['form']) ? $HTTP_POST_VARS['form'] : GetDbData($id);
}

# �������� ��������� ��� ������ �� ����
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwResources where ID_Resource = ' . $id);
        if ($db->NextRecord()) $r = $db->mRecord;
    } 
    return $r;
}

?>