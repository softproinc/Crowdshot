<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$number_of_latest_events     = 3;
$number_of_latest_crowdshots = 10;
?>
		<?php output_header('home-page', 'CrowdShot', FALSE, FALSE, FALSE, FALSE, TRUE, FALSE, TRUE); ?>

		<!-- Carousel -->
		<section id="carousel">
			<div id="myCarousel" class="carousel slide" data-ride="carousel" data-interval="5000">
				<ol class="carousel-indicators">
					<li data-target="#myCarousel" data-slide-to="0" class="active"></li>
					<li data-target="#myCarousel" data-slide-to="1"></li>
					<li data-target="#myCarousel" data-slide-to="2"></li>
					<li data-target="#myCarousel" data-slide-to="3"></li>
				</ol>

				<div class="carousel-inner">
					<div class="item active" style="background-image: url('img/homePage-carousel-img-01-cellphone.jpg');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading">Tell Great Stories</h2>
								<p class="carousel-caption-description">Pool photos from organizers, participants, sponsors, and media to create CrowdShot movies that tell great stories.</p>
								<p><a role="button" href="create-edit-event.php" class="btn btn-lg btn-primary">Sign up for CrowdShot</a></p>
							</div>
						</div>
					</div><!-- . item -->

					<div class="item" style="background-image: url('img/homePage-carousel-img-03-selfy.jpg');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading">Engage Your Fans</h2>
								<p class="carousel-caption-description">Turn shared photos into real-time web movies by using a mobile app unique to your event. Create contests and drive ticket sales.</p>
								<p><a role="button" href="create-edit-event.php" class="btn btn-lg btn-primary">Sign up for CrowdShot</a></p>
							</div>
						</div>
					</div><!-- . item -->

					<div class="item" style="background-image: url('img/homePage-carousel-img-04-girl.jpg');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading">Fundraising Made Easier</h2>
								<p class="carousel-caption-description">A clickable call-to-action ends each movie, delivering donors or customers to your charity fundraising or e-commerce web page.</p>
								<p><a role="button" href="create-edit-event.php" class="btn btn-lg btn-primary">Sign up for CrowdShot</a></p>
							</div>
						</div>
					</div><!-- . item -->

					<div class="item" style="background-image: url('img/homePage-carousel-img-05-manFlying.jpg');">
						<div class="container">
							<div class="carousel-caption">
								<h2 class="carousel-caption-heading">Memories Worth Keeping</h2>
								<p class="carousel-caption-description">Share your branded content and event photography with your guests to include in their CrowdShot movies. Generate photo sales.</p>
								<p><a role="button" href="create-edit-event.php" class="btn btn-lg btn-primary">Sign up for CrowdShot</a></p>
							</div>
						</div>
					</div><!-- . item -->
				</div><!-- .carousel-inner -->

				<a class="left carousel-control" href="#myCarousel" data-slide="prev"><span class="glyphicon glyphicon-chevron-left"></span></a>
		        <a class="right carousel-control" href="#myCarousel" data-slide="next"><span class="glyphicon glyphicon-chevron-right"></span></a>
			</div><!-- #myCarousel -->
		</section><!-- #carousel -->

		<?php output_crowdshot_steps(); ?>

		<?php latest_crowdshots('', '', '', 'event_name', 'album_title', $number_of_latest_crowdshots); ?>

		<?php // latest_events('', 'start_date', 'ASC', $number_of_latest_events); ?>

		<!-- About us -->
		<section id="about-us" class="featurette">
			<div class="container">
				<div class="row">
					<div class="col-md-7">
						<h2>About us</h2>
						<p class="lead"><span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> was created in Vancouver, Canada, because we wanted to make it easier for events to tell their story to raise charitable donations. (Nobody enjoys asking for money.) The <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> team (now six and counting) come from diverse backgrounds and we&rsquo;re not all  technology geeks. Contact us, if you&rsquo;re interested in joining the team, think we&rsquo;re doing a good job or want to learn more about what we&rsquo;re doing. See you on <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span>.</p>
					</div>
				</div>
			</div> <!-- .container -->
		</section><!-- #about-us -->

		<?php output_footer(); ?>