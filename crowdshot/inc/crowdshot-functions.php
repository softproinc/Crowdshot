<?php
require_once('inc/crowdshot-db-apis.php');



/**
 * Function that generate / display the head of every page (from the start of the HTML tag to the end of the header section inside the BODY tag
 * 
 * @param string $body_id
 * @param string $title
 * @param boolean $activate_handlebars
 * @param boolean $activate_fancybox
 * @param boolean $activate_holderJS
 * @param boolean $activate_jQueryUI_datePicker
 * @param boolean $activate_videoJS
 * @param boolean $activate_jQueryFileUpload
 * @param boolean $show_menu
 * @param string $menu_parameter Event Id / Activity Id
 */
function output_header($body_id, $title, $activate_handlebars = FALSE, $activate_fancybox = FALSE, $activate_holderJS = FALSE, $activate_jQueryUI_datePicker = FALSE, $activate_videoJS = FALSE, $activate_jQueryFileUpload = FALSE, $show_menu = TRUE, $menu_parameter = '') {
?>
<!DOCTYPE html>
<html lang="en-US">
	<head>
		<title><?php echo $title; ?></title>

		<meta charset="UTF-8">
		<meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link rel="icon" href="favicon.ico" type="image/x-icon" />
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon"> 

		<!-- jQuery: a fast, small, and feature-rich JavaScript library -->
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>

		<!-- Bootstrap: front-end framework for developing responsive, mobile first projects -->
		<script src="js/bootstrap.min.js" type="text/javascript"></script>
		<link href="css/bootstrap-crowdshot.css" rel="stylesheet" />

		<!-- Handlebars: provides the power necessary to let you build semantic templates effectively with no frustration -->
		<?php if ($activate_handlebars) : ?>
		<script src="js/handlebars-v1.1.2.js" type="text/javascript"></script>
		<?php endif; ?>

		<!-- Fancybox: a nice and elegant way to add zooming functionality for images, html content and multi-media -->
		<?php if ($activate_fancybox) : ?>
		<script src="js/fancybox/jquery.fancybox.pack.js?v=2.1.5" type="text/javascript"></script>
		<link href="js/fancybox/jquery.fancybox.css?v=2.1.5" rel="stylesheet" type="text/css" media="screen" />
		<?php endif; ?>

		<!-- Holder.js: renders image placeholders entirely on the client side -->
		<?php if ($activate_holderJS) : ?>
		<script src="js/holder.js" type="text/javascript"></script>
		<?php endif; ?>

		<?php if ($activate_jQueryUI_datePicker) : ?>
		<!-- Mordernizr: JavaScript library that detects HTML5 and CSS3 features in the user’s browser -->
		<script src="js/modernizr.inputTypes-touchEvents.min.js"></script>
		<!-- jQuery UI Datepicker -->
		<script src="js/jquery-ui-1.10.4.datepicker.min.js" type="text/javascript"></script>
		<link href="css/jquery-ui-smoothness/jquery-ui-1.10.4.custom.min.css" rel="stylesheet">
		<script type="text/javascript">
			$(document).ready(function() {
				$(".form-control.date-picker-field").datepicker({dateFormat : "yy-mm-dd"});
			});
		</script>
		<?php endif; ?>

		<!-- VideoJS: a JavaScript and CSS library that makes it easier to work with and build on HTML5 video -->
		<!-- video.js must be in the <head> for older IEs to work -->
		<?php if ($activate_videoJS) : ?>
		<script src="js/video-js/video.js" type="text/javascript"></script>
		<link href="js/video-js/video-js.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript">
			videojs.options.flash.swf = "js/video-js/video-js.swf";
		</script>
		<?php endif; ?>

		<!-- jQuery File Upload: widget with multiple file selection, drag&drop support, progress bars, validation and preview images, audio and video -->
		<?php if ($activate_jQueryFileUpload) : ?>
		<script src="js/jquery.ui.widget.js" type="text/javascript"></script>
		<script src="js/jquery.iframe-transport.js" type="text/javascript"></script>
		<script src="js/jquery.fileupload.js" type="text/javascript"></script>
		<?php endif; ?>

		<!-- Font Awesome: provides scalable vector icons that can instantly be customized -->
		<link href="//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.min.css" rel="stylesheet" />
		
		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
			<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js" type="text/javascript"></script>
			<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js" type="text/javascript"></script>
		<![endif]-->

		<!-- CrowdShot's custom CSS -->
		<link href="css/crowdshot.css" rel="stylesheet" />

		<!-- Setup login popover -->
		<script type="text/javascript">
			$(document).ready(function() {
				$('#login_popover').popover({html: true, content: function() {return $('#login-popover-content').html();}});
			});
		</script>
	</head>

	<body id="<?php echo $body_id; ?>">
		<!-- Main header section -->
		<header>
			<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
				<div class="container">
					<div class="navbar-header">
						<?php if ($show_menu) : ?>
						<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
							<span class="sr-only">Toggle navigation</span>
							<span class="icon-bar"></span>
						</button>
						<?php endif; // if ($show_menu) ?>

						<a class="navbar-brand" href="index.php">CrowdShot</a>
					</div>

					<?php if ($show_menu) : ?>
					<div class="navbar-collapse collapse" id="main-nav">
						<ul class="nav navbar-nav navbar-right">
							<?php
								switch ($body_id) :
									case 'home-page' :
							?>
							<li><a href="create-edit-event.php">Sign Up</a></li>
							<?php
										break; // case 'home-page'
									case 'event-page' :
							?>
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown">Menu <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="upload-photos-to-event.php?event_id=<?php echo $menu_parameter; ?>">Upload Photos (Organizer and Sponsors)</a></li>
									<li><a href="create-edit-event-vt.php?event_id=<?php echo $menu_parameter; ?>">Create Movie (Organizer)</a></li>
									<li><a href="create-edit-activity.php?event_id=<?php echo $menu_parameter; ?>">Create Activity (Participants)</a></li>
									<li><a href="create-event-vt.php?event_id=<?php echo $menu_parameter; ?>">Create Movie (Participants)</a></li>
								</ul><!-- .dropdown-menu -->
							</li><!-- .dropdown -->
							<?php
										break; // case 'event-page'
									case 'activity-page' :
							?>
							<li class="dropdown">
								<a class="dropdown-toggle" data-toggle="dropdown">Menu <span class="caret"></span></a>
								<ul class="dropdown-menu" role="menu">
									<li><a href="upload-photos-to-activity.php?activity_id=<?php echo $menu_parameter; ?>">Upload Photos</a></li>
									<li><a href="create-edit-activity-vt.php?activity_id=<?php echo $menu_parameter; ?>">Create Movie</a></li>
								</ul><!-- .dropdown-menu -->
							</li><!-- .dropdown -->
							<?php
										break; // case 'activity-page'
								endswitch; // switch ($body_id)
							?>
						</ul>
					</div><!-- #main-nav -->
					<?php endif; // if ($show_menu) ?>
				</div><!-- .container -->
			</nav>
		</header>
<?php
} // function output_header



/**
 * Function that generate / display the footer of every page (it includes the end BODY tag and end HTML tag)
 */
function output_footer() {
?>
		<!-- Main footer section -->
		<footer>
			<div class="container">
				<div class="row">
					<div class="col-sm-9">
						<ul id="footer-copyright-terms-privacy" class="list-inline">
							<li>
								<p>&copy; CrowdShot <?php echo date('Y'); ?></p>
							</li>
							<li>
								<p><a href="terms-of-service.php">Terms of service</a></p>
							</li>
							<li>
								<p><a href="privacy-policy.php">Privacy policy</a></p>
							</li>
						</ul>
					</div>

					<div class="col-sm-3">
						<ul class="share-icon-list list-inline pull-right">
							<li>Share</li>
							<li class="share-icon"><a href="http://facebook.com/" target="crowdshotfacebook"><i class="fa fa-facebook-square"></i></a></li>
							<li class="share-icon"><a href="http://twitter.com/" target="crowdshottwitter"><i class="fa fa-twitter-square"></i></a></li>
							<li class="share-icon"><a href="mailto:"><i class="fa fa-envelope"></i></a></li>
						</ul>
						<div class="clearfix"></div>
					</div>
				</div>
			</div> <!-- .container -->
		</footer>
	</body>
</html>
<?php
} // function output_footer



/**
 * Function that will shows the steps in creating CrowdShots
 * 
 * @param string $step_1_title
 * @param string $step_1_message
 * @param string $step_2_title
 * @param string $step_2_message
 * @param string $step_3_title
 * @param string $step_3_message
 */
function output_crowdshot_steps($step_1_title = 'Organize', $step_1_message = 'Add your event to <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> and upload your key messages, logo and recent event photos.', $step_2_title = 'Invite', $step_2_message = 'We\'ll instantly generate a mobile app with a link you can email to your event participants.', $step_3_title = 'Create', $step_3_message = 'They\'ll open the app to combine their event photos with your supplied content to create easy-to-share movies.') {
?>
<!-- Steps Section -->
		<section id="steps" class="featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-4">
						<h3>
							<span class="num-circle">1</span>
							<?php echo $step_1_title; ?>
						</h3>
						<p><?php echo $step_1_message; ?></p>
					</div><!-- .col-md-4 -->

					<div class="col-md-4">
						<h3>
							<span class="num-circle">2</span>
							<?php echo $step_2_title; ?>
						</h3>
						<p><?php echo $step_2_message; ?></p>
					</div><!-- .col-md-4 -->

					<div class="col-md-4">
						<h3>
							<span class="num-circle">3</span>
							<?php echo $step_3_title; ?>
						</h3>
						<p><?php echo $step_3_message; ?></p>
					</div><!-- .col-md-4 -->
				</div><!-- .row -->
			</div><!-- .container -->
		</section><!-- #steps -->
<?php
} // function output_crowdshot_steps



/**
 * Function that will show the x number of latest events based on what is supplied
 * 
 * @param bigint $user_id
 * @param string $from_date_field 'start_date' or 'created_datetime'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $number_of_latest_events
 */
function latest_events($user_id = '', $from_date_field = 'start_date', $order = 'DESC', $number_of_latest_events = 1) {
	$latest_events = get_latest_events('', $from_date_field, $from_date_field, $order, $number_of_latest_events);

	if (empty($latest_events)) {
		return;
	}
?>
		<!-- Latest Events -->
		<section id="latest-events" class="section-invert featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h2>Latest sponsored event<?php echo (count($latest_events) > 1 ? 's' : ''); ?></h2>
					</div>
				</div>

				<div class="latest-events row">
					<?php
					foreach ($latest_events as $latest_event) :
						$latest_event_featured_image               = get_asset($latest_event['event_featured_image_id']);
						$latest_event_featured_image_properties    = json_decode($latest_event_featured_image['asset_properties'], TRUE);
						$latest_event_featured_image_thumbnail_url = get_image_thumbnail_url($latest_event_featured_image_properties, 'desktop_featured_image');
					?>
					<div id="latest-event-<?php echo $latest_event['id']; ?>" class="latest-events-event-wrapper">
						<div class="col-md-6">
							<a href="event.php?event_id=<?php echo $latest_event['id']; ?>"><img class="img-responsive" src="<?php echo $latest_event_featured_image_thumbnail_url; ?>" alt="<?php echo 'event featured image'; // $latest_event_featured_image['asset_caption']; ?>"></a>
						</div><!-- .col-md-7 -->

						<div class="col-md-6 hero">
							<h3 class="lead"><a href="event.php?event_id=<?php echo $latest_event['id']; ?>"><?php echo $latest_event['event_name']; ?></a></h3>

							<?php echo ($latest_event['event_start_date'] ? '<p class="lead">' . ($latest_event['event_end_date'] && $latest_event['event_start_date'] == $latest_event['event_end_date'] ? 'On ' : 'From ') . date('F j, Y', strtotime($latest_event['event_start_date'])) . ($latest_event['event_end_date'] && $latest_event['event_start_date'] != $latest_event['event_end_date'] ? ' to ' . date('F j, Y', strtotime($latest_event['event_end_date'])) : '') . '</p>' : ''); ?>

							<?php echo ($latest_event['event_description'] ? '<p class="lead">' . $latest_event['event_description'] . '</p>' : ''); ?>

							<a href="upload-photos-to-event.php?event_id=<?php echo $latest_event['id']; ?>" class="btn btn-primary">Let's make a movie now</a>
						</div><!-- .col-md-5 -->

						<div class="clearfix"></div>
					</div><!-- .latest-events-event-wrapper -->
					<?php endforeach; // foreach ($latest_events as $latest_event) ?>
				</div><!-- .latest-events -->
			</div><!-- .container -->
		</section><!-- #latest-events -->
<?php
} // function latest_events



/**
 * Function that will show the x number of latest activities based on what is supplied
 * 
 * @param bigint $event_id
 * @param bigint $user_id
 * @param string $from_date_field 'start_date' or 'created_datetime'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $number_of_latest_activities
 */
function latest_activities($event_id = '', $user_id = '', $from_date_field = 'start_date', $order = 'DESC', $number_of_latest_activities = 1) {
	$latest_activities = get_latest_activities($event_id, '', $from_date_field, $from_date_field, $order, $number_of_latest_activities);

	if (empty($latest_activities)) {
		return;
	}
?>
		<!-- Latest Activities -->
		<section id="latest-activities" class="section-invert featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h2>Latest fundraising activit<?php echo (count($latest_activities) > 1 ? 'ies' : 'y'); ?></h2>
					</div>
				</div>

				<div class="latest-activities row">
					<?php
					foreach ($latest_activities as $latest_activity) :
						$latest_activity_featured_image               = get_asset($latest_activity['activity_featured_image_id']);
						$latest_activity_featured_image_properties    = json_decode($latest_activity_featured_image['asset_properties'], TRUE);
						$latest_activity_featured_image_thumbnail_url = get_image_thumbnail_url($latest_activity_featured_image_properties, 'desktop_featured_image');
					?>
					<div id="latest-activity-<?php echo $latest_activity['id']; ?>" class="latest-activities-activity-wrapper">
						<div class="col-md-6">
							<a href="activity.php?activity_id=<?php echo $latest_activity['id']; ?>"><img class="img-responsive" src="<?php echo $latest_activity_featured_image_thumbnail_url; ?>" alt="<?php echo 'activity featured image'; // $latest_activity_featured_image['asset_caption']; ?>"></a>
						</div><!-- .col-md-7 -->

						<div class="col-md-6 hero">
							<h3 class="lead"><a href="activity.php?activity_id=<?php echo $latest_activity['id']; ?>"><?php echo $latest_activity['activity_name']; ?></a></h3>

							<?php echo ($latest_activity['activity_start_date'] || $latest_activity['activity_location'] ? '<p class="lead">' . ($latest_activity['activity_end_date'] && $latest_activity['activity_start_date'] == $latest_activity['activity_end_date'] ? 'On ' : 'From ') . ($latest_activity['activity_start_date'] ? date('F j, Y', strtotime($latest_activity['activity_start_date'])) . ($latest_activity['activity_end_date'] && $latest_activity['activity_start_date'] != $latest_activity['activity_end_date'] ? ' to ' . date('F j, Y', strtotime($latest_activity['activity_end_date'])) : '') : '') . ($latest_activity['activity_location'] ? ' at ' . $latest_activity['activity_location'] : '') . '</p>' : ''); ?>

							<a href="upload-photos-to-activity.php?activity_id=<?php echo $latest_activity['id']; ?>" class="btn btn-primary">Let's make a movie now</a>
						</div><!-- .col-md-5 -->

						<div class="clearfix"></div>
					</div><!-- .latest-activities-activity-wrapper -->
					<?php endforeach; // foreach ($latest_activities as $latest_activity) ?>
				</div><!-- .latest-activities -->
			</div><!-- .container -->
		</section><!-- #latest-activities -->
<?php
} // function latest_activities



/**
 * Function that will show the x number of latest CrowdShots based on what is supplied
 * 
 * @param bigint $event_id
 * @param bigint $activity_id
 * @param bigint $user_id
 * @param string $heading_field_01 'event_name' or 'activity_name' or 'album_title'
 * @param string $heading_field_02 'event_name' or 'activity_name' or 'album_title' or ''
 * @param string $number_of_latest_crowdshots
 */
function latest_crowdshots($event_id = '', $activity_id = '', $user_id = '', $heading_field_01 = 'album_title', $heading_field_02 = '', $number_of_latest_crowdshots = 1) {
	$latest_crowdshots = get_generated_videos('', 'generating', ($event_id ? 'event' : ($activity_id ? 'activity' : '')), ($event_id ? $event_id : ($activity_id ? $activity_id : '')), ($user_id ? $user_id : ''), 'created_datetime', 'DESC', $number_of_latest_crowdshots) + get_generated_videos('', 'published', ($event_id ? 'event' : ($activity_id ? 'activity' : '')), ($event_id ? $event_id : ($activity_id ? $activity_id : '')), ($user_id ? $user_id : ''), 'created_datetime', 'DESC', $number_of_latest_crowdshots);

	if (empty($latest_crowdshots)) {
		return;
	}
?>
		<!-- Latest Crowdshots -->
		<section id="latest-crowdshots" class="section-invert featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h2>Made with CrowdShot</h2>
					</div>
				</div>

				<div class="latest-crowdshots row">
					<?php
					foreach ($latest_crowdshots as $latest_crowdshot) :
						$latest_crowdshot_properties                = json_decode($latest_crowdshot['asset_properties'], TRUE);
						$latest_crowdshot_album_cover               = (array_key_exists('album_cover', $latest_crowdshot_properties) && array_key_exists('asset_id', $latest_crowdshot_properties['album_cover']) ? get_asset($latest_crowdshot_properties['album_cover']['asset_id']) : FALSE);
						$latest_crowdshot_album_cover_thumbnail_url = ($latest_crowdshot_album_cover ? get_image_thumbnail_url(json_decode($latest_crowdshot_album_cover['asset_properties'], TRUE), 'desktop_album_cover') : '');
						$latest_crowdshot_cta_url                   = (array_key_exists('timeline', $latest_crowdshot_properties) &&
																	   array_key_exists(22, $latest_crowdshot_properties['timeline']) &&
																	   array_key_exists('properties', $latest_crowdshot_properties['timeline'][22]) &&
																	   array_key_exists('content', $latest_crowdshot_properties['timeline'][22]['properties']) &&
																	   array_key_exists(4, $latest_crowdshot_properties['timeline'][22]['properties']['content']) &&
																	   array_key_exists('text', $latest_crowdshot_properties['timeline'][22]['properties']['content'][4]) ? $latest_crowdshot_properties['timeline'][22]['properties']['content'][4]['text'] : '');
						$latest_crowdshot_creator                   = get_user($latest_crowdshot['created_by']);
					?>
					<div class="latest-crowdshots-crowdshot-wrapper">
						<div class="latest-crowdshots-crowdshot-content">
							<?php if ($latest_crowdshot['asset_status'] == 'published') : // movie has been converted to MP4 / H.264 ?>
							<video id="crowdshot-video-<?php echo $latest_crowdshot['id']; ?>" class="video-js vjs-default-skin vjs-big-play-centered" controls preload="none" poster="<?php echo $latest_crowdshot_album_cover_thumbnail_url; ?>" data-setup="{}" width="auto" height="auto">
								<source src="<?php echo $latest_crowdshot['asset_url']; ?>" type='video/mp4' />
							</video>
							<?php if ($latest_crowdshot_cta_url) : ?>
							<p id="latest-crowdshots-crowdshot-cta-<?php echo $latest_crowdshot['id']; ?>" class="latest-crowdshots-crowdshot-cta hidden"><a href="<?php echo $latest_crowdshot_cta_url; ?>"></a></p>
							<script type="text/javascript">
								videojs("#crowdshot-video-<?php echo $latest_crowdshot['id']; ?>").ready(function() {
									this.on('ended', function() {
										$("#latest-crowdshots-crowdshot-cta-<?php echo $latest_crowdshot['id']; ?>").removeClass('hidden');
									});
								});
							</script>
							<?php endif; // if ($latest_crowdshot_cta_url) ?>
							<?php else : // movie Flash based - conversion to MP4 / H.265 has not been completed ?>
								<?php
								$latest_crowdshot_flash_folder_name = (array_key_exists('flash_properties', $latest_crowdshot_properties) && array_key_exists('folder_name', $latest_crowdshot_properties['flash_properties']) ? $latest_crowdshot_properties['flash_properties']['folder_name'] : '');
								if ($latest_crowdshot_flash_folder_name) :
								?>
								<!-- embed Flash player here to play the movie -->
								<?php else : ?>
								<img id="crowdshot-video-<?php echo $latest_crowdshot['id']; ?>" src="<?php echo $latest_crowdshot_album_cover_thumbnail_url; ?>" />
								<?php endif; // if ($latest_crowdshot_flash_folder_name) ?>
							<?php endif; // if ($latest_crowdshot['asset_status'] = 'published') ?>
						</div>

						<h3>
							<?php
							switch ($heading_field_01) :
								case 'event_name' :
									echo (array_key_exists('event', $latest_crowdshot) && $latest_crowdshot['event'] && array_key_exists('event_name', $latest_crowdshot['event']) && $latest_crowdshot['event']['event_name'] ? $latest_crowdshot['event']['event_name'] : '');
									break;
								case 'activity_name' :
									echo (array_key_exists('activity', $latest_crowdshot) && $latest_crowdshot['activity'] && array_key_exists('activity_name', $latest_crowdshot['activity']) && $latest_crowdshot['activity']['activity_name'] ? $latest_crowdshot['activity']['activity_name'] : '');
									break;
								case 'album_title' :
									echo (array_key_exists('album_cover', $latest_crowdshot_properties) && array_key_exists('title', $latest_crowdshot_properties['album_cover']) ? $latest_crowdshot_properties['album_cover']['title'] : '');
									break;
							endswitch; // switch ($heading_field_01)
							?>
						</h3>

						<?php if ($heading_field_02 && $heading_field_01 != $heading_field_02) : ?>
						<h4>
							<?php
							switch ($heading_field_02) :
								case 'event_name' :
									echo (array_key_exists('event', $latest_crowdshot) && $latest_crowdshot['event'] && array_key_exists('event_name', $latest_crowdshot['event']) && $latest_crowdshot['event']['event_name'] ? $latest_crowdshot['event']['event_name'] : '');
									break;
								case 'activity_name' :
									echo (array_key_exists('activity', $latest_crowdshot) && $latest_crowdshot['activity'] && array_key_exists('activity_name', $latest_crowdshot['activity']) && $latest_crowdshot['activity']['activity_name'] ? $latest_crowdshot['activity']['activity_name'] : '');
									break;
								case 'album_title' :
									echo (array_key_exists('album_cover', $latest_crowdshot_properties) && array_key_exists('title', $latest_crowdshot_properties['album_cover']) ? $latest_crowdshot_properties['album_cover']['title'] : '');
									break;
							endswitch; // switch ($heading_field_02)
							?>
						</h4>

						<h5>by <?php echo format_user_name($latest_crowdshot_creator['organization_name'], $latest_crowdshot_creator['first_name'], $latest_crowdshot_creator['last_name']); ?></h5>
						<?php else : ?>
						<h4>by <?php echo format_user_name($latest_crowdshot_creator['organization_name'], $latest_crowdshot_creator['first_name'], $latest_crowdshot_creator['last_name']); ?></h4>
						<?php endif; // if ($heading_field_02 && $heading_field_01 != $heading_field_02) ?>
					</div><!-- .latest-crowdshots-crowdshot-wrapper -->
					<?php endforeach; // foreach ($latest_crowdshots as $latest_crowdshot) ?>
				</div><!-- .latest-crowdshots -->
			</div><!-- .container -->
		</section><!-- #latest-crowdshots -->
<?php
} // function latest_crowdshots



/**
 * 
 * This function check to see if current user is in a cookie - if so, get it, validate it, and return it
 * 
 * @param string $redirect_to_URL
 * @return userDBObject
 */
function get_SESSION_current_user($redirect_to_URL) {
	if (isset($_SESSION['current_user'])) {
		if (array_key_exists('id', $_SESSION['current_user'])) {
			return $_SESSION['current_user'];
		} else {
			header(sprintf('Location: %1$s/get-user-info.php?redirect_to=%2$s', dirname($_SERVER['PHP_SELF']), urlencode($redirect_to_URL)));

			exit;
		} // if (array_key_exists('id', $session_current_user))
	} else {
		header(sprintf('Location: %1$s/get-user-info.php?redirect_to=%2$s', dirname($_SERVER['PHP_SELF']), urlencode($redirect_to_URL)));

		exit;
	} // if (isset($_SESSION['current_user'])) else
} // function get_SESSION_current_user



/**
 * This function inserts event narratives into the event video timeline
 * 
 * @param string $vt_properties in JSON format
 * @param bigint $event_logo_id
 * @param string $event_narrative_sentence_01
 * @param string $event_narrative_sentence_02
 * @param string $event_narrative_sentence_03
 * @param string $event_narrative_sentence_04
 * @return string $vt_properties revised video timeline properties - JSON format
 */
function insert_event_narratives_to_vt_properties($vt_properties, $event_logo_id, $event_narrative_sentence_01, $event_narrative_sentence_02, $event_narrative_sentence_03, $event_narrative_sentence_04) {
	// decode event video timeline properties into associative array
	$vt_properties_associative_array = json_decode($vt_properties, TRUE);

	$event_narrative_sentence_index = 1;

	// insert event's narratives
	for ($i = 4; $i <= 18; $i = $i + 2) {
		if (($event_narrative_sentence_index <= 4) && ($vt_properties_associative_array['timeline'][$i]['properties']['type'] == 'shot')) {
			$vt_properties_associative_array['timeline'][$i]['properties']['type'] = 'branded_caption_shot';

			$vt_properties_associative_array['timeline'][$i]['properties']['content'][] = array('type' => 'logo',  'is_locked' => TRUE,  'asset_id' => $event_logo_id);
			$vt_properties_associative_array['timeline'][$i]['properties']['content'][] = array('type' => 'text', 'is_locked' => FALSE, 'text' => ${"event_narrative_sentence_0" . $event_narrative_sentence_index}, 'color' => 'rgba(255, 255, 255, 1)');

			$event_narrative_sentence_index++;
		} // if (($event_narrative_sentence_index <= 4) && ($vt_properties_associative_array['timeline'][$i]['properties']['type'] == 'shot'))
	} // for ($i = 4; $i <= 18; $i = $i + 2)

	unset($event_narrative_sentence_index);

	return json_encode($vt_properties_associative_array);
} // function insert_event_narratives_to_vt_properties



/**
 * This function builds the e-mail headers
 * 
 * @param string $email_from
 * @param string $email_reply_to
 * @param string $email_bcc
 * @return string $emailHeaders
 */
function build_email_header($email_from, $email_reply_to, $email_bcc) {

	$email_headers   = array();
	$email_headers[] = 'MIME-Version: 1.0';
	$email_headers[] = 'Content-Transfer-Encoding: 8bit';
	$email_headers[] = 'Content-type: text/html; charset=UTF-8';
	$email_headers[] = 'From: ' . $email_from;
	$email_headers[] = 'Bcc: ' . $email_bcc;
	$email_headers[] = 'Reply-To: ' . $email_reply_to;

	return implode("\r\n", $email_headers);
} // function build_email_header



/**
 * This function wraps the e-mail message into an HTML
 * 
 * @param string $email_subject e-mail subject - to be used in the <title> tag
 * @param string $email_message e-mail message - can contain HTML code; it will be wrapped inside the <body> tag
 * @return string $emailMessageInHTML
 */
function build_email_message_html($email_subject, $email_message) {
	$email_message_html =<<<CROWDSHOT
<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>$email_subject</title>
	</head>
	<body>$email_message</body>
</html>
CROWDSHOT;

	return $email_message_html;
} // function build_email_message_html



/**
 * This function sends an e-mail notification to the user
 * 
 * @param string $email_subject
 * @param string $email_to e-mail address of the user to send message to
 * @param string $email_message e-mail message - can contain HTML code; it will be wrapped inside the <body> tag
 * @return boolean
 */
function send_notification($email_subject, $email_to, $email_message) {
	$email_from     = 'CrowdShot Administrator <admin@crowdshotmovies.com>';
	$email_reply_to = 'CrowdShot Technical Help <tech_help@crowdshotmovies.com>';
	$email_bcc      = '';
	$email_to       = filter_var($email_to, FILTER_SANITIZE_EMAIL);

	if (filter_var($email_to, FILTER_VALIDATE_EMAIL)) {
		return mail($email_to, $email_subject, build_email_message_html($email_subject, $email_message), build_email_header($email_from, $email_reply_to, $email_bcc));
	} else {
		return FALSE;
	} // if (filter_var($email_to, FILTER_VALIDATE_EMAIL) && filter_var($email_from, FILTER_VALIDATE_EMAIL))
} // function send_email_notification



/**
 * 
 * @param bigint $vt_id
 * @param string $vt_properties in JSON format
 * @param bigin $vt_creator_id
 * @param string $vt_creator_email
 * @param string $email_subject
 */
function call_video_engine($vt_id, $vt_properties, $vt_creator_id, $vt_creator_email, $email_subject) {
	$cURL = curl_init(sprintf('http%1$s://%2$s%3$s%4$s/videoengine/call.php', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER["SERVER_NAME"], ($_SERVER["SERVER_PORT"] != '80' ? ':' . $_SERVER["SERVER_PORT"] : ''), dirname($_SERVER['PHP_SELF'])));

	curl_setopt($cURL, CURLOPT_POST, 1);
	curl_setopt($cURL, CURLOPT_POSTFIELDS, array("method" => "generatevideo", "vdata" => $vt_properties, "video_id" => $vt_id));
	curl_setopt($cURL, CURLOPT_RETURNTRANSFER, true);

	$cURL_result = curl_exec($cURL);		

	curl_close($cURL);

	if ($cURL_result) {
		$vt = edit_asset($vt_id, '', json_encode(array('flash_properties' => array('folder_name' => $cURL_result)) + json_decode($vt_properties, TRUE)), 'generating', $vt_creator_id);

		if ($vt) {
			$email_message   = '<p>Your CrowdShot Movie for "' . $email_subject . '" is being generated.</p>' .
							   '<p>Click <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/generatingvideo.php?foldername=' . $cURL_result . '">here</a> to view your CrowdShot movie.</p>';

			send_notification('Generating CrowdShot Movie - ' . $email_subject, $vt_creator_email, $email_message);

			header(sprintf('Location: %1$s/generatingvideo.php?foldername=%2$s', dirname($_SERVER['PHP_SELF']), $cURL_result));

			exit();
		} else {
			return 'Error updating your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie preview.';
		} // if ($vt) else
	} else {
		return 'Error generating your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie.';
	} // if ($cURL_result) else
} // function call_video_engine
?>