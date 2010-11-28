<?
################################################################################
#                                                                              #
#   Project name                                                               #
#                                                                              #
#   Copyright (с) 2003, Ilya Blagorodov, Sergey Efremov                        #
#                                                                              #
#   admin/adm/users/index.php                                                  #
#   Пользователи.                                                              #
#                                                                              #
################################################################################

define ('PATH_TO_ROOT',  '../../');
define ('PATH_TO_ADMIN', '../');
define ('PAGE_TITLE',    'Статичные страницы');
define ('PAGE_CODE',     'st_pages');

define ('ACT_ADD',       'add');
define ('ACT_EDIT',      'edit');
define ('ACT_ADD_PROC',  'add_proc');
define ('ACT_EDIT_PROC', 'edit_proc');
define ('ACT_DEL_PROC',  'del_proc');

require (PATH_TO_ADMIN . 'inc/top.inc.php');
include_once (PATH_TO_ADMIN . 'inc/func_site_pages.inc.php');
require_once (PATH_TO_ADMIN . 'inc/func_catalog.inc.php');

include(PATH_TO_ADMIN."FCKeditor/fckeditor.php") ;

print get_header('Статичные страницы');

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
$form['id'] = $id;

// Выполним изменения
$msg = '';
$err = '';
switch ($action) {
    case ACT_ADD_PROC: {
        $err = spage_edit($form,true);
        if ($err == '') $form = array();
        $action = ACT_ADD;
        break;
    }
    case ACT_EDIT_PROC: {
        if ($err = spage_edit($form,false)) {
            $action = ACT_EDIT;
        } else {
            $action = ACT_ADD;
            $form = array();
        }
        break;
    }
    case ACT_DEL_PROC: {
        $err = spage_del($id);
        $action = ACT_ADD;
        $form = array();
        break;
    }
    case 'save_table': {
        foreach($_POST['form']['st'] as $k => $val) {
            unset($sdata);
            $sdata['id'] = $k;
            $sdata['priority'] = $val['prior']  ;
            if (isset($val['arhiv'])) $sdata['arhiv'] = '1';
//print_r($sdata);
            spage_edit($sdata,false,true);
        }
    }

}


// Покажем список
print get_delete_script($this_page . '?a=' . ACT_DEL_PROC . '&id=');
print get_subheader('Список');
if ($action != ACT_ADD) print get_link('Добавить', $this_page);
print get_formatted_message($msg);
print GetList();
//print '<input type=submit value="Сохранить изменения" onClick="javascript:document.forms.a2.submit();">';

// Покажем форму
print get_subheader($action == ACT_ADD ? 'Добавление' : 'Редактирование');
print get_formatted_error($err);
print GetForm($form, $action == ACT_ADD ? ACT_ADD_PROC : ACT_EDIT_PROC);

require (PATH_TO_ADMIN . 'inc/bottom.inc.php');


# Форма
function GetForm($form, $action) {
    global $this_page, $g_options;

    $cat  = new catalog();

    $curr_child_arr = $cat->get_all_childrens();
    foreach ($curr_child_arr as $val) {
        $nbsp = '&nbsp;';
        for($i=0;$i<$val['level']-1;$i++) $nbsp .= '&nbsp;&nbsp;&nbsp;'; 
        $current_tree_array[$val['ID_Cat']] = $nbsp.$val['CatName'];
    }

//convert_cyr_string(
    $text_c = isset($form['TxtEditor']) ?  $form['TxtEditor'] : 'Here you should input text';
//  $text_c = $form['TxtEditor'];
    $oFCKeditor = new FCKeditor('form[TxtEditor]', '100%', 450 ) ;
    $oFCKeditor->Value = $text_c;
    $editor = $oFCKeditor->CreateHTML() ;

    $tpl = new Template();
    $tpl->set_file('main', 'form.ihtml');

    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('ID', isset($form['id']) ? $form['id'] : '');
    $tpl->set_var('NAME', isset($form['Name']) ? htmlspecialchars($form['Name']) : '');
    $tpl->set_var('COMENT', isset($form['Coment']) ? htmlspecialchars($form['Coment']) : '');
    $tpl->set_var('TITLE', isset($form['Title']) ? htmlspecialchars($form['Title']) : '');
    $tpl->set_var('DESCRIPTION', isset($form['Description']) ? htmlspecialchars($form['Description']) : '');
    $tpl->set_var('KEYWORDS', isset($form['Keywords']) ? htmlspecialchars($form['Keywords']) : '');
    $tpl->set_var('PAGECODE', isset($form['PageCode']) ? htmlspecialchars($form['PageCode']) : '');
    $tpl->set_var('ARCH_CHECKED', (isset($form['arhiv']) && $form['arhiv'] == 1)? ' checked' : '');
    $tpl->set_var('HASAR_CHECKED', (isset($form['HasArticles']) && $form['HasArticles'] == 1)? ' checked' : '');
    $tpl->set_var('PAGE_TYPES_OPTIONS', get_select_options(isset($form['PageType']) ? trim($form['PageType']) : '', $g_options->GetOptionList('page_types'), false));
    $tpl->set_var('NAVIGATION_OPTIONS', get_select_options(isset($form['Navigation']) ? trim($form['Navigation']) : '', $g_options->GetOptionList('navigation'), false));

    $tpl->set_var('EDITOR',$editor);

    $tpl->set_var('BTN_CAPTION', $action == ACT_ADD_PROC ? 'Добавить' : 'Изменить');
    $tpl->set_var('ACTION', $action);
    return $tpl->parse('C', 'main', false);
}

# Список
function GetList() {
    global $db, $this_page;
    $db->Query('select count(*) from dwStPages');
    $cnt = $db->NextRecord() ? $db->F(0) : 0;

    $tbl = new PslAdmTbl;

    $tbl->mSortDefault   = 'name';
    $tbl->mRecordsCnt    = $cnt;
    $tbl->mSessionPrefix = 'a_a_u';
    $tbl->mTblForm = '<form name=a2 action="index.php" method="post"><input type=hidden name=a value="save_table">';
    $tbl->mDownImg       = PATH_TO_ADMIN . 'img/down.gif';
    $tbl->mUpImg         = PATH_TO_ADMIN . 'img/up.gif';
    $tbl->mSortFields    = array('id' => 'IDPage', 'name' => 'name', 'PageCode'=>'PageCode','archiv' => 'archiv');

    $tbl->SetInPageOptions(get_inpage_array());
    $tbl->SetHead($this_page, array('ID', 'Наименование', 'Код ', 'Неактивен', 'Действия'), 
                  array('Кликните для сортировки по колонке', 'Кликните для сортировки по колонке', 
                        'Кликните для сортировки по колонке', ''));
    $q  = 'select * from dwStPages';
    $q .= $tbl->GetOrderByClause() . $tbl->GetLimitClause();             
    $db->Query($q);

    while ($db->NextRecord()) {
        $ch_ar = ($db->F('arhiv') == 1) ? 'checked':'';
        $tbl->SetRow(array($db->F('IDPage'), 
                           '<a href="' . $this_page . '?id=' . $db->F('IDPage') .  '&a=' . ACT_EDIT . '">' . htmlspecialchars($db->F('Name')) . '</a>',
                            $db->F('PageCode'),
                           '<center><input type="checkbox" name="form[st]['.$db->F('id').'][arhiv]" '. $ch_ar .'></center>', 
                           '<center><a href="javascript:deleteRecord(' . $db->F('IDPage') . ')"><img src="' . PATH_TO_ADMIN . 'img/del.gif" border=0 alt="Удалить"></a></center>'
                           ));
    }
     
    return $tbl->GetTable();
}

# Получить параметры как массив из постинга или из базы
function GetData($id) {
    global $_POST;
    return isset($_POST['form']) ? $_POST['form'] : GetDbData($id);
}

# Получить параметры как массив из базы
function GetDbData($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwStPages where IDPage = ' . $id);
        if ($db->NextRecord()) $r = $db->mRecord;
    } 
    return $r;
}

?>
