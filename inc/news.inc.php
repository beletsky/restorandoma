<?
################################################################################
#   
#   
#                     
#   Copyright (с) 2003
#                     
#   news.inc.php      
#  Набор функций по работе с новостыми лентами  для пользовательского интерфейса
#                                                                      
#                                                                              
################################################################################

function get_one_news($id) {
    global $db;
    $r = array();
    if (string_is_id($id)) {
        $db->query('select * from dwNews where IDNews = ' . $id);
        if ($row = $db->FetchArray()) {
			$r[] = $row;
		}
		$r = $r[0];
    }
    return $r;
}

function get_news_list() {
    global $db;
    $r = array();
    $db->query('select * from dwNews order by DateNews DESC ');
    while($row = $db->FetchArray()) {
		$r[] = $row;
    }
    return $r;
}

?>