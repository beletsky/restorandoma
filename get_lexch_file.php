<?
include( "inc/xml_func.inc.php" );

$links_file = "files/lexch.xml";
$url = "http://www.arttech.ru/links_db/xml_lexch.php?code=restorandoma";

$content = file_get_contents( $url );

$fw = fopen( $links_file, "w" );
fwrite( $fw, $content );
fclose( $fw );

?>
