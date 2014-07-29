<?php
session_start();
require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');
$foldername=$_GET['foldername'];
?>
		<?php output_header('generating-video-page', 'Generating Video | CrowdShot', FALSE, FALSE, FALSE, FALSE, TRUE, FALSE, TRUE); ?>
<div style="text-align:center">
 <iframe width="1285" height="740" frameborder="0" marginheight="0" marginwidth="0" src="Content/<?php echo $foldername; ?>/preview.php"></iframe></div>
		<?php output_footer(); ?>