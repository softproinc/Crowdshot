<?php 
if (isset($GLOBALS["HTTP_RAW_POST_DATA"]))
{
 	$fname=explode("/",$_GET["name"]);	
	$app_path="/var/www/html/crowdshota02/Content/".$fname[1]."/Snapshots/";
	file_put_contents($app_path.$fname[2], $GLOBALS["HTTP_RAW_POST_DATA"]);
}
?>