<?
include("inc/xml_func.inc.php");

$links_file = "files/links.xml";
//$url = "http://arttech.ru/links_db/xml_links.php?code=restorandoma";
$url = "http://www.prodam-ostrov.ru/links/xml_links.php?code=2";

$content = file_get_contents($url);

$fw = fopen($links_file,"w");
fwrite($fw,$content);
fclose($fw);

?>
