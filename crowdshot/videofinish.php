<?php  
$folder=$_GET['name'];
$fname=explode("/",$_GET["name"]);
chdir('/var/www/html/crowdshota02/Content/'.$fname[1].'/Snapshots/');
$response=exec('ffmpeg -r 24 -i frame_%d.png -i bgaudio.mp3 -b 10000k -s sxga -t 00:01:06 -vcodec mpeg4 video.mp4');
?>
