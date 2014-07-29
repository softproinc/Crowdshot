<?php 
//$dirname = basename(dirname(__FILE__)); 
//$file_url="preview.swf";
?>
<html>
<head> 
 
<script type="text/javascript" src="../../inc/swfobject.js"></script>
<script type="text/javascript">
		swfobject.registerObject("myId", "9.0.0", "../../inc/expressInstall.swf");
</script>
</head> 
<body style="margin:0px; padding:0px; " >
<object id="myId" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"  width="1280" height="720" border="0" hspace="0" vspace="0">
				<param name="movie" value="preview.swf" />
        		<!--[if !IE]>-->
				<object type="application/x-shockwave-flash" data="preview.swf"  width="1280" height="720" 
				 border="0" hspace="0" vspace="0">
				<!--<![endif]-->
				<div>
				<p ><a href="http://www.adobe.com/go/getflashplayer"><img src="http://www.adobe.com/images/shared/download_buttons/get_flash_player.gif" alt="Get Adobe Flash player" /></a></p>
				</div>
				<!--[if !IE]>-->
				</object>
				<!--<![endif]-->
			</object>
</body>
</html>