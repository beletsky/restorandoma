<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (�) 2003,  Sergey Efremov                                        #
#                                                                              #
#   admin/adm/customers/index.php                                              #
#   ������.                                                                    #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../../');
define ('PATH_TO_ADMIN', '../../');
define ('PAGE_TITLE',    '�������');
define ('PAGE_CODE',     'clients');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientcomp.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_clientusers.inc.php');

print get_header('�������:');

// ��������� ������� ��������
$action = '';
if (isset($_GET['a'])) $action = $_GET['a'];
if ($action == '' && isset($_POST['a'])) $action = $_POST['a'];
if ($action == '') $action = ACT_ADD;

// ������� ���������
$id = 0;
if (isset($_GET['id'])) $id = $_GET['id'];
if (!string_is_id($id) && isset($_POST['id'])) $id = $_POST['id'];
$form = GetData($id);
$form['IDClientComp'] = $id;


// �������� ���������
$msg = '';
$err = '';

switch ($action) {
    case ACT_ADD_PROC: {
        $err = clientcomp_edit($form,true);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = clientcomp_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = GetDbData($id);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = clientcomp_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }
}

if ($id != 0) $action = ACT_EDIT;

// ������� �����
print get_subheader($action == ACT_ADD ? '����������' : '��������������');
print get_formatted_error($err);

if ($action != ACT_ADD) print get_link('��������', $this_page);
print get_formatted_message($msg);
print get_subheader('������');

print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

// ������� ������
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');



require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# �����
function GetForm($form, $action) {
    global $this_page, $g_options;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_block('main', 'sites_list',     'sites_list_');

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['IDClientComp']) ? $form['IDClientComp'] : '');
    $tpl->set_var('NAME',    isset($form['Name']) ? $form['Name'] : '');
    $tpl->set_var('MAIL',    isset($form['Mail']) ? $form['Mail'] : '');
    $tpl->set_var('PHONE',   isset($form['Phone']) ? $form['Phone'] : '');
    $tpl->set_var('ADRESS',  isset($form['Adress']) ? $form['Adress'] : '');

    $tpl->set_var('ORG_TYPES_OPTIONS',get_select_options(isset($form['OrgType']) && string_is_id($form['OrgType']) ? $form['OrgType'] : 0, $g_options->GetOptionList('org_type'), false)); 
    $tpl->set_var('PAY_TYPE_OPTIONS',get_select_options(isset($form['PaymentType']) && string_is_id($form['PaymentType']) ? $form['PaymentType'] : 0, $g_options->GetOptionList('payment_type'), false));  
    $tpl->set_var('METRO',isset($form['Metro']) ? $form['Metro'] : '');
    $tpl->set_var('URL',isset($form['WebUrl']) ? $form['WebUrl'] : ''); 
    $tpl->set_var('EMLOY_NUM',isset($form['EmployersNum']) ? $form['EmployersNum'] : '');  
    $tpl->set_var('DELIV_TIME_OPTIONS',get_select_options(isset($form['DeliveryTime']) && string_is_id($form['DeliveryTime']) ? $form['DeliveryTime'] : 0, $g_options->GetOptionList('deliv_time'), false)); 
  
    $tpl->set_var('COMMENTS',isset($form['Comments']) ? $form['Comments'] : '');
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? '��������' : '��������');
    $tpl->set_var('ACTION', $action);

    $tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# ������
function GetList() {
    global $db, $this_page,$form;

    $db->Query('select * from dwClientComp');
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'quest_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'IDClientComp', 'Name' => 'Name', 'Phone' => 'Phone');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', '������������', '�������', '��������'),
                  array('�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', '�������� ��� ���������� �� �������', ''));

    $q  = 'select * from dwClientComp ' . $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('IDClientComp'),
                           '<a href='.$this_page . '?&id=' . $db->F('IDClientComp') . '>' .$db->F('Name'). '</a>',
                           $db->F('Phone'),
                           '<center><a href="javascript:deleteRecord(' . $db->F('IDClientComp') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="�������"></a></center>',
                           ));
    return $tbl->GetTable();
}

# �������� ��������� ��� ������ �� �������� ��� �� ����
function GetData($id) {
    global $_POST;
    $ret = '';
    if (isset($_POST['form'])) {
        $ret =  $_POST['form'];
    } else {
        $ret =  GetDbData($id);
    }
    return $ret;
}

# �������� ��������� ��� ������ �� ����
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwClientComp where IDClientComp = ' . $id);
        if ($row = $db->FetchArray()) {
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>