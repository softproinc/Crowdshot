<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$no_error        = TRUE;
$error_message   = '';

if (isset($_GET['event_id'])) {
	$event = get_event_details($_GET['event_id']);

	$number_of_additional_carousel_images = 4;
	$number_of_latest_activities          = -1;
	$number_of_latest_crowdshots          = -1;

	if ($event) {
		$event_featured_image       = get_asset($event['event_featured_image_id']);
		$additional_carousel_images = get_assets('', 'user_image', 'published', 'event', $event['id'], '', '', 'RAND', $number_of_additional_carousel_images);
	} else {
		$no_error      = FALSE;
		$error_message = 'Selected event is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
	} // if ($event) else
} else {
		$no_error      = FALSE;
		$error_message = 'No event select. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
} // if (isset($_GET['event_id'])) else
?>

		<?php output_header('event-page', $event['event_name'] . ' | CrowdShot', FALSE, FALSE, FALSE, FALSE, TRUE, FALSE, TRUE, $event['id']); ?>

		<?php if ($error_message) : ?>
		<!-- Display messages section -->
		<section id="create-edit-messages">
			<div class="container">
				<div class="row">
					<div class="col-md-12" id="messages">
						<?php echo ($error_message ? '<div class="alert alert-danger"><p>' . $error_message . '</p></div>' : ''); ?>
					</div>
				</div>
			</div>
		</section>
		<?php endif; // if ($error_message) ?>

		<?php if ($no_error) : ?>
		<!-- Carousel -->
		<section id="carousel">
			<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
				<ol class="carousel-indicators">
					<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
					<?php foreach ($additional_carousel_images as $additional_carousel_image_key => $additional_carousel_image) : ?>
					<li data-target="#myCarousel" data-slide-to="<?php echo $additional_carousel_image_key + 1; ?>"></li>
					<?php endforeach; // foreach ($additional_carousel_images as $additional_carousel_image_key => $additional_carousel_image) ?>
				</ol>

				<div class="carousel-inner">
					<div class="item active" style="background-image: url('<?php echo $event_featured_image['asset_url']; ?>');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading"><?php echo $event['event_name']; ?></h2>
								<p class="carousel-caption-description"><?php echo $event['event_description']; ?></p>
								<p><a class="btn btn-lg btn-primary" href="create-event-vt.php?event_id=<?php echo $event['id']; ?>" role="button">Get started now</a></p>
							</div>
						</div>
					</div><!-- . item -->

					<?php foreach ($additional_carousel_images as $additional_carousel_image_key => $additional_carousel_image) : ?>
					<div class="item" style="background-image: url('<?php echo $additional_carousel_image['asset_url']; ?>');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading"><?php echo $event['event_name']; ?></h2>
								<p class="carousel-caption-description"><?php echo $event['event_description']; ?></p>
								<p><a class="btn btn-lg btn-primary" href="create-event-vt.php?event_id=<?php echo $event['id']; ?>" role="button">Get started now</a></p>
							</div>
						</div>
					</div><!-- . item -->
					<?php endforeach; // foreach ($additional_carousel_images as $additional_carousel_image) ?>
				</div><!-- .carousel-inner -->

				<a class="left carousel-control" href="#myCarousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
		        <a class="right carousel-control" href="#myCarousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
			</div><!-- #myCarousel -->
		</section><!-- #carousel -->

		<?php output_crowdshot_steps('Join', 'Accept your event\'s invitation to tell your fundraising story on <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span>.', 'Create', 'Upload your photos and fundraising activity details. Find photos from others.', 'Share', 'Your movie is made like magic. Share with your friends and donors.'); ?>

		<?php if($event) {latest_crowdshots($event['id'], '', '', 'activity_name', 'album_title', $number_of_latest_crowdshots);} ?>

		<?php if($event) {latest_activities($event['id'], '', 'start_date', 'DESC', $number_of_latest_activities);} ?>

		<!-- Sounds Interesting -->
		<section id="sounds-interesting" class="featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<h2 class="text-center">Tell Your Story</h2>
						<p class="text-center"><a class="btn btn-primary btn-lg" role="button" href="create-event-vt.php?event_id=<?php echo $event['id']; ?>">Get started now</a></p>
					</div>
				</div>
			</div> <!-- .container -->
		</section><!-- #sounds-interesting -->
		<?php endif; // if ($error_message) ?>

		<?php output_footer(); ?>