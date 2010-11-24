<?php

ini_set( 'display_errors', 'On' );

define ('PATH_TO_ROOT', './');
define ('PATH_TO_ADMIN', './admin/');

require (PATH_TO_ROOT . 'inc/init.inc.php');

date_default_timezone_set('Europe/Moscow');

$page = 'news';
$news_on_page = 10;

$news_arr = get_news_list($page,$news_on_page);
$tpl = new Template();
$tpl->set_file('news_list', PATH_TO_ROOT.'tpl/rss.tpl');
$tpl->set_block('news_list','item', 'item_');
if (count($news_arr) > 0) foreach ($news_arr as $val) {
    $tpl->set_var('THIS_PAGE', $this_page);
    $tpl->set_var('DATE_RFC',date( 'r', sql_to_ts($val['DateNews'])+10*60*60));
    $tpl->set_var('TITLE',$val['Title']);
    $tpl->set_var('ANNOUNCE',$val['Announce']);
    $tpl->set_var('URL','/news/form[id]/'.$val['IDNews']);
    $tpl->parse('item_', 'item', true);
}

//header( 'Content-Type: application/rss+xml; charset=UTF-8' );
echo $tpl->parse('NL', 'news_list', false);

exit();


?>


