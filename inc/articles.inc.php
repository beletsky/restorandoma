<?
################################################################################
#   
#   
#                     
#   Copyright (�) 2003
#                     
#   news.inc.php      
#  ����� ������� �� ������ � ��������� �������  ��� ����������������� ����������
#                                                                      
#                                                                              
################################################################################

function get_articles($id=0) {
    global $db;
    $r = array();
    $q = (string_is_id($id)) ? 'select * from dwArticles where IDArticle = ' . $id : 'select * from dwArticles';
	$db->query($q);
	while ($row = $db->FetchArray()) {
		$r[] = $row;
	}
    return $r;
}
?>
