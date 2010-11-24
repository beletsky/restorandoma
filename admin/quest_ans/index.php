<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003,  Sergey Efremov                                        #
#                                                                              #
#   admin/adm/customers/index.php                                              #
#   Клинты.                                                                    #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Список новостей');
define ('PAGE_CODE',     'quest_ans');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_quest.inc.php');
include(PATH_TO_ADMIN .'FCKeditor/fckeditor.php') ;

print get_header('Вопросы ответы:');

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
$form['IDLine'] = $id;


// Выполним изменения
$msg = '';
$err = '';

switch ($action) {
    case ACT_ADD_PROC: {
        $err = quest_edit($form,true);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = quest_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = GetDbData($id);
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = quest_del($id);
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

    $oFCKeditor = new FCKeditor( 'form[Question]', 400, 180 ) ;
    $oFCKeditor->ToolbarSet = 'Basic';
    $oFCKeditor->Value = isset($form['Question']) ? stripslashes($form['Question']) : 'Here you should input text';
    $editor_question = $oFCKeditor->CreateHTML() ;
    unset($oFCKeditor);

    $oFCKeditor = new FCKeditor( 'form[Answer]', 400, 180 ) ;
    $oFCKeditor->ToolbarSet = 'Basic';
    $oFCKeditor->Value = isset($form['Answer']) ? stripslashes($form['Answer']) : 'Here you should input text';
    $editor_answer = $oFCKeditor->CreateHTML() ;
    unset($oFCKeditor);

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['IDLine']) ? $form['IDLine'] : '');
    $tpl->set_var('PAGETITLE', isset($form['PageTitle']) ? stripslashes(htmlspecialchars($form['PageTitle'])) : '');
    $tpl->set_var('QUESTION', $editor_question);
    $tpl->set_var('ANSWER', $editor_answer);
    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? 'Добавить' : 'Изменить');
    $tpl->set_var('ACTION', $action);

    $tpl->set_var('LIST',GetList());

    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page,$form;

    $db->Query('select * from dwQuestAns');
    $cnt = $db->NextRecord() ? $db->NumRows() : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'id';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mShowInPageSel = true;
    $tbl->mSessionPrefix = 'quest_ar';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'IDLine', 'Question' => 'Question');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', 'Вопрос', 'Действия'),
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', ''));

    $q  = 'select *  from dwQuestAns' . $tbl->GetOrderByClause() . $tbl->GetLimitClause();
    $db->Query($q);

    while ($db->NextRecord())
        $tbl->SetRow(array($db->F('IDLine'),
                           '<a href='.$this_page . '?&id=' . $db->F('IDLine') . '>' .stripslashes($db->F('Question')). '</a>',
                           '<center><a href="javascript:deleteRecord(' . $db->F('IDLine') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="Удалить"></a></center>',
                           ));

    return $tbl->GetTable();
}

# Получить параметры как массив из постинга или из базы
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

# Получить параметры как массив из базы
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select *from dwQuestAns where IDLine = ' . $id);
        if ($row = $db->FetchArray()) {
            $r[] = $row;
        }
        $r = $r[0];
    }
    return $r;
}

?>