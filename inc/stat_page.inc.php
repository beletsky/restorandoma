<?
################################################################################
#   
#   
#                     
#   Copyright (с) 2003
#                     
#   satat_page.inc.php         
#   Функции по работе со статичными страницами
#                        
################################################################################

function get_page_cont_by_id($PageCode) {
	global $db;
	$ret = array();
	if($PageCode != '') {
		$q = "select DSP.*, DC.* from dwStPages DSP left join dwCatPages DCP on DSP.IDPage = DCP.IDPage ".
			"left join dwCategories DC on DCP.IDCat = DC.ID_Cat ".
			"where PageCode = '$PageCode'";
		$db->Query($q);
		if($arr = $db->FetchArray()) $ret = $arr;
	}
	return $ret;
}

function get_all_parents($leftt, $rightt) {
	global $db;
	$ret = array();
	if (string_is_int($leftt) && string_is_int($rightt)) {
		$db->Query("SELECT DC.*, DSP.IDPage, DSP.PageCode  from dwCategories DC ".
			"left join dwCatPages DCP on DC.ID_Cat = DCP.IDCat left join dwStPages DSP on DCP.IDPage = DSP.IDPage ". 
			"where DC.leftt <= $leftt and DC.rightt >= $rightt and DC.level>0 group by DC.ID_Cat ORDER BY DC.leftt");
		while($arr = $db->FetchArray()) $ret[] = $arr;
	}
	return $ret;

}

function get_page_articles($PageCode) {
	global $db;
	$ret = array();
	if($PageCode != '') {
		$q = "select * from dwArticles where PageCode = '$PageCode'";
		$db->Query($q);
		while($arr = $db->FetchArray()) $ret[] = $arr;
	}
	return $ret;
}
?>