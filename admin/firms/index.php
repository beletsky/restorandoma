<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003,  Sergey Efremov                                        #
#                                                                              #
#   admin/firms/index.php                                                      #
#   Наши клиенты.                                                              #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Фирмы-клиенты');
define ('PAGE_CODE',     'firms');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_firms.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_files_upload.inc.php');
include(PATH_TO_ADMIN .'FCKeditor/fckeditor.php') ;


print get_header('Фирмы-клиенты:');



// Определим текущее действие
$action = '';
if (isset($_GET['a'])) $action = $_GET['a'];
if ($action == '' && isset($_POST['a'])) $action = $_POST['a'];
if ($action == '') $action = ACT_ADD;

// Получим параметры
$id = 0;
if (isset($_GET['id'])) $id = $_GET['id'];
if (!string_is_id($id) && isset($_POST['id'])) $id = $_POST['id'];
$form = GetData($id);
$form['ID'] = $id;


// Выполним изменения
$msg = '';
$err = '';

switch ($action) {
    case ACT_ADD_PROC: {
        $err = firm_edit($form,true);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = firm_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = GetDbData($id);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = firm_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }
}

if ($id != 0) $action = ACT_EDIT;

// Покажем форму
print get_subheader($action == ACT_ADD ? 'Добавление' : 'Редактирование');
print get_formatted_error($err);

if ($action != ACT_ADD) print get_link('Добавить', $this_page);
print get_formatted_message($msg);
print get_subheader('Список');

print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

// Покажем список
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');



require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm($form, $action) {
    global $this_page;
    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');
    $tpl->set_block('main', 'sites_list',     'sites_list_');

    $oFCKeditor = new FCKeditor( 'form[Content]', '90%', 250 ) ;
    $oFCKeditor->Value = isset($form['Content']) ? $form['Content'] : '';
    $editor_text = $oFCKeditor->CreateHTML() ;

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['ID']) ? $form['ID'] : '');
    $tpl->set_var('TITLE', isset($form['Title']) ? stripslashes(htmlspecialchars($form['Title'])) : '');
    $tpl->set_var('ANNOUNCE', isset($form['Announce']) ? stripslashes(htmlspecialchars($form['Announce'])) : '');
    $tpl->set_var('CONTENT', $editor_text);
    $tpl->set_var('URL', isset($form['URL']) ? stripslashes(htmlspecialchars($form['URL'])) : '');
    $tpl->set_var('IMAGE',      (isset($form['Image'])) ? $form['Image'] : '');    
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? 'Добавить' : 'Изменить');
    $tpl->set_var('ACTION', $action);

    $tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page,$form;

    $db->Query('select * from dwFirms');
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'firms_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'ID', 'title' => 'Title' );

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', 'Заголовок', 'Действия'),
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', ''));

    $q  = 'select * from dwFirms ' . $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('ID'),
                           '<a href="' . $this_page . '?id=' . $db->F('ID') . '&a=' . ACT_EDIT . '">' . stripslashes(htmlspecialchars($db->F('Title'))) . '</a>',
                           '<center><a href="javascript:deleteRecord(' . $db->F('ID') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="Удалить"></a></center>',
                           ));

    return $tbl->GetTable();
}

# Получить параметры как массив из постинга или из базы
function GetData($id) {
    global $_POST;
    $ret = '';
    if (isset($_POST['form'])) {
        $ret = $_POST['form'];
    } else {
        $ret = GetDbData($id);
    }
    return $ret;
}

# Получить параметры как массив из базы
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwFirms where ID = ' . $id);
        if ($row = $db->FetchArray()) {
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>