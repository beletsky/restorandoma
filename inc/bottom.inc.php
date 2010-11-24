<?
################################################################################
#               
#   Project name
#                     
#   Copyright (с) 2003
#                     
#   bottom.inc.php    
#   Подвал страницы.  
#                     
################################################################################


//Получаем список перелинковки для данной страницы.
$links_file = "files/lexch.xml";
$cont_arr = imp_xml_parse( file_get_contents( $links_file ) );
$page_url = "http://" . $_SERVER[ 'HTTP_HOST' ] . $_SERVER[ 'REQUEST_URI' ];

$links_arr = array();
if( isset( $cont_arr[ 'BODY' ][ 0 ][ 'LEXCH' ][ 0 ][ 'ITEM' ] ) )
{
    foreach( $cont_arr[ 'BODY' ][ 0 ][ 'LEXCH' ][ 0 ][ 'ITEM' ] as $val )
    {
        if(    $val[ 'SITEURL' ][ 0 ][ 'VALUE' ] == $page_url
            && !empty( $val[ 'URL' ][ 0 ][ 'VALUE' ] ) )
        {
            $links_arr[] = iconv('UTF-8','CP1251', 
                str_replace( '<a ', '<a target="_blank" ', 
                str_replace( '<A ', '<A target="_blank" ', 
                $val[ 'URL' ][ 0 ][ 'VALUE' ] ) ) );
        }
    }
}

if( count( $links_arr) )
{
    $xlinks_text = '';
    foreach( $links_arr as $l_ar ) $xlinks_text .= $l_ar . "<br/>";

    $tpl = new Template();
    $tpl->set_file('xlinks', PATH_TO_ROOT.'tpl/xlinks.tpl');
    $tpl->set_var('XLINKS',$xlinks_text);
    $xlinks = $tpl->parse('xl', 'xlinks', false);
    unset( $tpl );
}
else $xlinks = '';

//require (PATH_TO_ROOT . "lib/psl_finish.inc.php");
echo $PageFutter;
$tpl = new Template();
$tpl->set_file('futter', PATH_TO_ROOT.'tpl/futter.tpl');
$tpl->set_var('THIS_PAGE',$this_page);
$tpl->set_var('XLINKS',( $PageCode == 'partners' ? '' : $xlinks ));
$tpl->pparse('ft', 'futter', false);
unset($tpl);

?>                          