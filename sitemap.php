<?php

ini_set( 'display_errors', 'On' );
date_default_timezone_set('Europe/Moscow');

define( 'PATH_TO_ROOT', './' );
define( 'PATH_TO_ADMIN', './admin/' );

define( 'PATH_TO_TPL', PATH_TO_ROOT . 'tpl/' );

require (PATH_TO_ROOT . 'inc/init.inc.php');
include_once(PATH_TO_ROOT . 'inc/stat_page.inc.php');
require (PATH_TO_ROOT . 'inc/xml_func.inc.php');

$news_on_page = 10;

// Подготовить шаблон Sitemap.
$tpl = new Template();
$tpl->set_file( 'sitemap', PATH_TO_TPL . 'sitemap.tpl' );
$tpl->set_block( 'sitemap', 'page_', 'page__' );
$tpl->set_block( 'sitemap', 'article_', 'article__' );

$tpl->set_var( 'HOST', 'www.restorandoma.ru' );

add_page( $tpl, 'index.html', '1.0' );
add_page( $tpl, 'advert.html', '0.6' );
add_page( $tpl, 'registration.html', '0.6' );
add_page( $tpl, 'map.html', '0.6' );

// Верхнее меню.
$TopMenuArr = get_menu_pages('top');
foreach($TopMenuArr as $val)
{
    add_page( $tpl, $val['PageCode'] . '.html', '0.6' );
    
    if( isset($val['HasArticles']) && $val['HasArticles'] != 0)
    {
        $articles_arr = get_articles_list($val['PageCode'],0,999999999);
        foreach ($articles_arr as $val2) add_page( $tpl, 'index.php?PageCode='.$val['PageCode'].'&form[id]='.$val2['IDArticle'], '0.6' );
        $total_articles = get_articles_count($PageCode);
        $pages_num = ceil($total_articles/$news_on_page);
        for($i=0;$i<$pages_num;$i++) add_page( $tpl, 'index.php?PageCode='.$val['PageCode'].'/form[page]/'.$i, '0.6' );
    }
}

// Правое меню.
$RightMenuArr = get_menu_pages('right');
foreach($RightMenuArr as $val)
{
    add_page( $tpl, $val['PageCode'] . '.html', '0.6' );
    
    if( isset($val['HasArticles']) && $val['HasArticles'] != 0)
    {
        $articles_arr = get_articles_list($val['PageCode'],0,999999999);
        foreach ($articles_arr as $val2) add_page( $tpl, 'index.php?PageCode='.$val['PageCode'].'&form[id]='.$val2['IDArticle'], '0.6' );
        $total_articles = get_articles_count($PageCode);
        $pages_num = ceil($total_articles/$news_on_page);
        for($i=0;$i<$pages_num;$i++) add_page( $tpl, 'index.php?PageCode='.$val['PageCode'].'/form[page]/'.$i, '0.6' );
    }
}

// Вопросы-ответы.
$QuestionsArray = get_all_questios();
foreach($QuestionsArray as $q_val) add_page( $tpl, 'quest_ans/form[quest_id]/' . $q_val['IDLine'], '0.6' );

// Новости.
$news_arr = get_news_list(0,999999999);
foreach ($news_arr as $val) add_page( $tpl, 'news/form[id]/' . $val['IDNews'], '0.6', $val['DateNews'] );
$total_news = get_news_count();
if ($total_news > $news_on_page)
{
    $pages_num = ceil($total_news/$news_on_page);
    for($i=0;$i<$pages_num;$i++) add_page( $tpl, 'news/form[page]/' . $i, '0.6' );
}


header( 'Content-Type: application/rss+xml; charset=windows-1251' );
$tpl->pparse( 'c', 'sitemap' );

exit();


function add_page( $tpl, $code, $priority, $date = NULL )
{
    if( !isset( $date ) ) $date = date( 'Y-m-d' );

    $tpl->set_var( 'PAGE_PAGECODE', $code );
    $tpl->set_var( 'PAGE_PRIORITY', $priority );
    $tpl->set_var( 'PAGE_TIME', $date );
    $tpl->parse( 'page__', 'page_', true );
}


?>
