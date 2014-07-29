<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$page_mode                     = 'create';
$activity                      = FALSE;
$event                         = FALSE;
$activity_featured_image       = FALSE;
$activity_cta_background_image = FALSE;
$activity_photos               = array();
$activity_creator              = FALSE;

$no_error                      = TRUE;
$display_form                  = TRUE;
$success_message               = '';
$error_message                 = '';

$minimum_activity_photos_for_actiivty_vt = 6;


// see if current user is in a cookie - if so, get it; otherwise, get user info
$current_user = get_SESSION_current_user(sprintf('http%1$s://%2$s%3$s%4$s/create-edit-activity.php?%5$s_id=%6$u', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER["SERVER_NAME"], ($_SERVER["SERVER_PORT"] != '80' ? ':' . $_SERVER["SERVER_PORT"] : ''), dirname($_SERVER['PHP_SELF']), (isset($_GET['activity_id']) || isset($_POST['activity_id']) ? 'activity' : (isset($_GET['event_id']) || isset($_POST['event_id']) ? 'event' : '')), (isset($_GET['activity_id']) ? $_GET['activity_id'] : (isset($_POST['activity_id']) ? $_POST['activity_id'] : (isset($_GET['event_id']) ? $_GET['event_id'] : (isset($_POST['event_id']) ? $_POST['event_id'] : ''))))));


if (isset($_POST['save_activity'])) {
	if (isset($_POST['event_id']) &&
		isset($_POST['first_name']) && $_POST['first_name'] &&
		isset($_POST['last_name']) && $_POST['last_name'] &&
		isset($_POST['user_email']) && $_POST['user_email'] &&
		isset($_POST['activity_name']) && $_POST['activity_name'] &&
		isset($_POST['activity_start_date']) && $_POST['activity_start_date'] &&
		isset($_POST['activity_location']) && $_POST['activity_location']) {

		if (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes')) {
			$page_mode = 'upload_only';
		} // if (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes'))

		// if there are new user images, put them in $_SESSION['activity_photos'] because we want to make sure the continue to appear on the page even there are validation errors
		if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '') {
			foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id) {
				$new_user_image = get_asset($new_user_image_id);

				if ($new_user_image) {
					$_SESSION['activity_photos'][] = $new_user_image;
				} // if ($new_user_image)
			} // foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id)
		} // if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '')

		// get user record of activity creator
		$activity_creator = get_user('', '', '', '', $_POST['user_email']);

		if ($activity_creator) {
			if ($activity_creator['first_name'] != $_POST['first_name'] || $activity_creator['last_name'] != $_POST['last_name']) {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error e-mail address already exist but do not match first name and or last name.';
			} else {
				$current_user = $_SESSION['current_user'] = $activity_creator;
			} // if ($activity_creator['first_name'] != $_POST['first_name'] || $activity_creator['last_name'] != $_POST['last_name'])
		} else { // user record does not exist, create a new user
			$activity_creator = create_user('', $_POST['first_name'], $_POST['last_name'], $_POST['user_email']);

			if ($activity_creator) {
				$current_user = $_SESSION['current_user'] = $activity_creator;
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error creating user.';
			} // if ($activity_creator) else
		} // if ($activity_creator) else

		// get event record
		$event = get_event_details($_POST['event_id']);

		if ($event) {
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Error finding event (' . $_POST['event_id'] . ').';
		} // if ($event) else

		// get activity featured image
		if (isset($_POST['activity_featured_image_id']) && $_POST['activity_featured_image_id']) {
			$activity_featured_image = get_asset($_POST['activity_featured_image_id']);

			if ($activity_featured_image) {
				// if the activity featured image's created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
				if ($current_user && array_key_exists('id', $current_user) && $current_user['id'] && $activity_featured_image['created_by'] == 1) {
					$activity_featured_image = edit_asset($activity_featured_image['id'], '', '', '', $current_user['id']);

					if ($activity_featured_image) {
						$_SESSION['activity_featured_image'] = $activity_featured_image;
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . "Error updating activity's album cover image.";

						unset($_SESSION['activity_featured_image']);
					} // if (!$activity_featured_image) else
				} else {
					$_SESSION['activity_featured_image'] = $activity_featured_image;
				} // if ($activity_featured_image['created_by'] == 1) else
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . "Error finding activity's albumn cover image.";

				unset($_SESSION['activity_featured_image']);
			} // ($activity_featured_image) else
		} // if (isset($_POST["activity_featured_image_id']) && $_POST['activity_featured_image_id'])

		// get activity call-to-action background image
		if (isset($_POST['activity_cta_background_image_id']) && $_POST['activity_cta_background_image_id']) {
			$activity_cta_background_image = get_asset($_POST['activity_cta_background_image_id']);

			if ($activity_cta_background_image) {
				// if the activity call-to-action background image's created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
				if ($current_user && array_key_exists('id', $current_user) && $current_user['id'] && $activity_cta_background_image['created_by'] == 1) {
					$activity_cta_background_image = edit_asset($activity_cta_background_image['id'], '', '', '', $current_user['id']);

					if ($activity_cta_background_image) {
						$_SESSION['activity_cta_background_image'] = $activity_cta_background_image;
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . "Error updating activity's call-to-action background image.";

						unset($_SESSION['activity_cta_background_image']);
					} // if ($activity_cta_background_image) else
				} else {
					$_SESSION['activity_cta_background_image'] = $activity_cta_background_image;
				} // if ($activity_cta_background_image['created_by'] == 1) else
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . "Error finding activity's call-to-action background image.";

				unset($_SESSION['activity_cta_background_image']);
			} // ($activity_cta_background_image) else
		} // if (isset($_POST["activity_cta_background_image_id']) && $_POST['activity_cta_background_image_id'])

		if ($no_error) {
			if ((isset($_POST['activity_id']) && ($_POST['activity_id'] == '')) || !isset($_POST['activity_id'])) { // is this a new activity or an existing activity
				$activity = create_activity($event['id'], $_POST['activity_name'], $_POST['activity_start_date'], $_POST['activity_end_date'], $_POST['activity_location'], $_POST['activity_featured_image_id'], $_POST['activity_cta_background_image_id'], $_POST['activity_cta_url'], $activity_creator['id']);

				if ($activity) {
					$_POST['activity_id'] = $activity['id'];
					$page_mode       = (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes') ? 'upload_only' : 'edit');
					$success_message = 'Activity created. Be sure to check your email for important links to your activity\'s page and CrowdShot web app. To see you activity\'s page now, click <a href="activity.php?activity_id=' . $activity['id'] . '">here</a>.';
					$email_message   = '<p>Your activity "' . $activity['activity_name'] . '" has been created.</p>' .
									   '<p>Click <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to edit it.</p>' .
									   '<p>Click <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-edit-activity-vt.php?activity_id=' . $activity['id'] . '">here</a> to make a CrowdShot movie with photos from the organizer and your activity.</p>' .
									   '<p>Your fellow participants can upload photos at <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/upload-photos-to-activity.php?activity_id=' . $activity['id'] . '">http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/upload-photos-to-activity.php?activity_id=' . $activity['id'] . '</a>.</p>' .
									   '<p>Your participants can visit your activity\'s page at <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/activity.php?activity_id=' . $activity['id'] . '">http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/activity.php?activity_id=' . $activity['id'] . '</a>.</p>';

					if (!send_notification($activity['activity_name'], $activity_creator['user_email'], $email_message)) {
						$error_message = 'Error notifying you on your new activity. Please contact our Technical Support.';
					} // if (!send_notification($activity['activity_name'], $activity_creator['user_email'], $email_message))
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error creating activity.';
				} // if ($activity) else
			} else {
				$activity = edit_activity($_POST['activity_id'], $event['id'], $_POST['activity_name'], $_POST['activity_start_date'], $_POST['activity_end_date'], $_POST['activity_location'], $_POST['activity_featured_image_id'], $_POST['activity_cta_background_image_id'], $_POST['activity_cta_url'], $current_user['id']);

				if ($activity) {
					$success_message = 'Activity updated.';
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error updating activity.';
				} // if ($activity) else
			} // if ((isset($_POST['activity_id']) && ($_POST['activity_id'] == '')) || !isset($_POST['activity_id'])) else

			if ($activity) { // if the activity was successfully created / updated, create asset relationship records for new activity photos
				if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '') {
					$asset_relationship_rows = array();

					foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id) {
						$asset_relationship_rows[] = array('asset_id' => $new_user_image_id, 'related_object_type' => 'activity', 'related_object_id' => $activity['id'], 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);
					} // foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id)

					if (!empty($asset_relationship_rows)) {
						$create_asset_relationship_success = create_asset_relationships($asset_relationship_rows);
					} // if (!empty($asset_relationship_rows))

					if ($create_asset_relationship_success) {
						unset($_POST['new_user_image_ids']);
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . 'Error attaching photo to event.';
					} // if ($create_asset_relationship_success) else
				} // if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '')
					
				$activity_photos = get_assets('', 'user_image', 'published', 'activity', $activity['id']);

				if (empty($activity_photos)) {
					unset($_SESSION['activity_photos']);
				} else {
					// go through each activity photos and see if the created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
					foreach ($activity_photos as $activity_photo_key => $activity_photo) {
						if ($activity_photo['created_by'] == 1) {
							$revised_activity_photo = edit_asset($activity_photo['id'], '', '', '', $current_user['id']);

							if ($revised_activity_photo) {
								$activity_photos[$activity_photo_key] = $revised_activity_photo;
							} else {
								$no_error      =  FALSE;
								$error_message .= ($error_message ? '<br />' : '') . 'Error updating activity photo (' . $revised_activity_photo['id'] . ').';
							} // if ($revised_activity_photo) else
						} // if ($activity_photo['created_by'] == 1) else
					} // foreach ($activity_photos as $activity_photo_key => $activity_photo)

					$_SESSION['activity_photos'] = $activity_photos;

					// ***** Let's see if going directly to the activity video timeline creation page is a good idea or not ***** Staet
					if (count($activity_photos) >= $minimum_activity_photos_for_actiivty_vt) {
						// ***** so, for now, let's delete the activity photos from the session variable
						unset($_SESSION['activity_photos']);

						header(sprintf('Location: %1$s%2$s', dirname($_SERVER['PHP_SELF']), '/create-edit-activity-vt.php?activity_id=' . $activity['id']));

						exit;
					} // if (count($activity_photos) >= 5)
					// ***** Let's see if going directly to the activity video timeline creation page is a good idea or not ***** End
				} // if (empty($activity_photos)) else
			} // if ($activity)
		} // if ($no_error)
	} else {
		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Required fields are not filled.';
	} // if (isset($_POST['event_id']) && (isset($_POST['organization_name']) || (isset($_POST['first_name']) && isset($_POST['last_name']))) && isset($_POST['user_email']) && isset($_POST['activity_name']) && isset($_POST['activity_start_date']) && isset($_POST['activity_location'])) else
} else {
	$activity_creator    = $current_user;
	$_POST['first_name'] = (array_key_exists('first_name', $current_user) ? $current_user['first_name'] : '');
	$_POST['last_name']  = (array_key_exists('last_name',  $current_user) ? $current_user['last_name']  : '');
	$_POST['user_email'] = (array_key_exists('user_email', $current_user) ? $current_user['user_email'] : '');

	if (isset($_GET['activity_id']) && $_GET['activity_id']) {
		$page_mode = (isset($_POST['upload_shots_only']) && $_POST['upload_shots_only'] == 'yes' ? 'upload_only' : 'edit');
		$activity  = get_activity_details($_GET['activity_id']);

		if ($activity) {
			$event = get_event_details($activity['event_id']);

			if ($event) {
				$_POST['event_id'] = $event['id'];

				$_POST['activity_id']                      = $activity['id'];
				$_POST['activity_name']                    = $activity['activity_name'];
				$_POST['activity_start_date']              = $activity['activity_start_date'];
				$_POST['activity_end_date']                = $activity['activity_end_date'];
				$_POST['activity_location']                = $activity['activity_location'];
				$_POST['activity_featured_image_id']       = $activity['activity_featured_image_id'];
				$_POST['activity_cta_background_image_id'] = $activity['activity_cta_background_image_id'];
				$_POST['activity_cta_url']                 = $activity['activity_cta_url'];

				$activity_featured_image = ($activity['activity_featured_image_id'] ? get_asset($activity['activity_featured_image_id']) : FALSE);

				if ($activity_featured_image) {
					$_SESSION['activity_featured_image'] = $activity_featured_image;
				} else {
					unset($_SESSION['activity_featured_image']);
				} // if ($activity_featured_image)

				$activity_cta_background_image = ($activity['activity_cta_background_image_id'] ? get_asset($activity['activity_cta_background_image_id']) : FALSE);

				if ($activity_cta_background_image) {
					$_SESSION['activity_cta_background_image'] = $activity_cta_background_image;
				} else {
					unset($_SESSION['activity_cta_background_image']);
				} // if ($activity_cta_background_image)

				$activity_photos = get_assets('', 'user_image', 'published', 'activity', $activity['id']);

				if (empty($activity_photos)) {
					unset($_SESSION['activity_photos']);
				} else {
					$_SESSION['activity_photos'] = $activity_photos;
				} // if (empty($activity_photos)) else
			} else { // if it falls into here, the activity is not associated with a validate event; therefore, it should not be edited - just go back to the home page
				$no_error      =  FALSE;
				$display_form  =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected activity because it is not connected to a valid event. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if ($event) else
		} else { // if it falls into here, the activity id is invalidate; therefore, we could allow the user to create a new activity if there is an event id
			$page_mode = 'create';
			$activity  = FALSE;

			unset($_GET['activity_id']);
			unset($_POST['activity_id']);

			if (isset($_GET['event_id']) && $_GET['event_id']) {
				$event = get_event_details($_GET['event_id']);

				if ($event) {
					$_POST['event_id'] = $event['id'];
				} else { // if it falls into here, the activity id is invalid and there isn't an event id passed into here; therefore, it is not possible to create a new activity
					unset($_GET['event_id']);
					unset($_POST['event_id']);

					$no_error      =  FALSE;
					$display_form  =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected activity because it is not valid and the selected event is invalid to create a new activity. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
				} // if ($event) else
			} else { // if it falls into here, event id was not provided
				$no_error      =  FALSE;
				$display_form  =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected activity becaues it is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if (isset($_GET['event_id']) && $_GET['event_id']) else
		} // if ($activity) else
	} else { // if it falls into here, no activity id was passed
		$activity                = FALSE;
		$activity_featured_image = FALSE;
		$activity_photos         = array();

		unset($_GET['activity_id']);
		unset($_POST['activity_id']);
		unset($_SESSION['activity_featured_image']);
		unset($_SESSION['activity_cta_background_image']);
		unset($_SESSION['activity_photos']);
		
		if (isset($_GET['event_id']) && $_GET['event_id']) {
			$event = get_event_details($_GET['event_id']);

			if ($event) {
				$_POST['event_id']            = $event['id'];
				$_POST['activity_name']       = $event['event_name'];
				$_POST['activity_start_date'] = $event['event_start_date'];
				$_POST['activity_end_date']   = $event['event_end_date'];
				$_POST['activity_location']   = $event['event_location'];
				$_POST['activity_cta_url']    = $event['event_cta_url'];
			} else { // if it falls into here, the event id is invalidate and there isn't an activity id passed into here; therefore, it is not possible to create a new activity
				unset($_GET['event_id']);
				unset($_POST['event_id']);

				$no_error      =  FALSE;
				$display_form  =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot create an activity because the selected event is invalid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if ($event) else
		} else { // if it falls into here, no activity id and no event id were passed; therefore, it has to go back to the home page (can't create activity because no event id can be associated
			$no_error      =  FALSE;
			$display_form  =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Cannot create an activity because no event was selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		} // if ($event) else
	} // if (isset($_GET['activity_id']) && $_GET['activity_id'] else
} // if (isset($_POST['save_activity'])) else
?>
		<?php output_header('create-edit-activity-page', (($page_mode == 'edit') || (isset($_POST['activity_id']) && $_POST['activity_id'] != '' && $page_mode != 'upload_only') ? 'Edit' : ($page_mode == 'upload_only' ? 'Upload Photos to' : 'Create')) . ' Activity | CrowdShot', TRUE, TRUE, TRUE, TRUE, FALSE, TRUE, FALSE); ?>

		<!-- Display messages section -->
		<section id="create-edit-messages">
			<div class="container">
				<div class="row">
					<div class="col-md-12" id="messages">
						<?php echo ($error_message ? '<div class="alert alert-danger"><p>' . $error_message . '</p></div>' : ''); ?>
						<?php echo ($success_message ? '<div class="alert alert-success"><p>' . $success_message . '</p></div>' : ''); ?>
					</div>
					<script type="text/javascript">
						$(document).ready(function() {
//							$('input').focus(function() {
//								$('#messages').html('');
//							})
//							$('input[type="file"]').click(function() {
//								$('#messages').html('');
//							})
						});
					</script>
				</div>
			</div>
		</section>

		<?php if ($display_form) : ?>
		<!-- Main create activity page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>THANK YOU for signing up for <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span>. You can promote your individual or team fundraising activity by creating a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Just complete the steps below and we'll have your movie ready for you to share with your donors.</p>
					</div>
				</div>
			</div><!-- .container -->
		</section><!-- #create-edit-introduction -->

		<!-- Main create / edit activity form -->
		<section id="create-edit-form">
			<div class="container">
				<form class="form-horizontal" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<input type="hidden" name="first_name" value="<?php echo (isset($_POST['first_name'])? $_POST['first_name'] : ''); ?>" />
					<input type="hidden" name="last_name" value="<?php echo (isset($_POST['last_name'])? $_POST['last_name'] : ''); ?>" />
					<input type="hidden" name="user_email" value="<?php echo (isset($_POST['user_email'])? $_POST['user_email'] : ''); ?>" />

					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Activity</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<label for="inputActivityName" class="col-md-3 control-label required-field">Activity Name</label>
								<div class="col-md-9">
									<input type="text" name="activity_name" class="form-control track-changes" id="inputActivityName" placeholder="e.g. <?php echo date('Y'); ?> Activity Name" maxlength="50" required<?php echo (isset($_POST['activity_name']) ? ' value="' . $_POST['activity_name'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputActivityFromDate" class="col-md-3 control-label required-field">Activity Date</label>
								<div class="col-md-2">
									<input type="date" name="activity_start_date" class="form-control track-changes start-date-picker-field" id="inputActivityStartDate" placeholder="e.g. <?php echo date('Y-m-d'); ?>" maxlength="10" required<?php echo (isset($_POST['activity_start_date']) ? ' value="' . $_POST['activity_start_date'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
								<label for="inputActivityToDate" class="col-md-1 control-label required-field">to</label>
								<div class="col-md-2">
									<input type="date" name="activity_end_date" class="form-control track-changes end-date-picker-field" id="inputActivityEndDate" placeholder="e.g. <?php echo date('Y-m-d'); ?>" maxlength="10" required<?php echo (isset($_POST['activity_end_date']) ? ' value="' . $_POST['activity_end_date'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
								<script type="text/javascript">
									$(document).ready(function() {
										/**
										 * Function to set and ensure maxDate of the activity start date datepicker is not greater than the activity end date
										 */
										function beforeShow_StartDatePicker() {
											var activityStartDate = $(".form-control.start-date-picker-field");
											var activityEndDate   = $(".form-control.end-date-picker-field");

											if (activityEndDate.datepicker("getDate") !== null) {
												if (activityStartDate.datepicker("getDate") === null) {
													activityStartDate.datepicker("option", {maxDate : new Date(activityEndDate.datepicker("getDate")), defaultDate : new Date(activityEndDate.datepicker("getDate"))});
													activityStartDate.val(activityEndDate.val());
												} else {
													activityStartDate.datepicker("option", {maxDate : new Date(activityEndDate.datepicker("getDate"))});
												} // if (activityStartDate.datepicker("getDate") === null) else
											} else {
												activityStartDate.datepicker("option", {maxDate : null});
											} // if (activityEndDate.datepicker("getDate") !== null) else
										} // function beforeShow_StartDatePicker()

										/**
										 * Function to set and ensure minDate of the activity end date datepicker is not less than the activity start date
										 */
										function beforeShow_EndDatePicker() {
											var activityStartDate = $(".form-control.start-date-picker-field");
											var activityEndDate   = $(".form-control.end-date-picker-field");

											if (activityStartDate.datepicker("getDate") !== null) {
												if (activityEndDate.datepicker("getDate") === null) {
													activityEndDate.datepicker("option", {minDate : new Date(activityStartDate.datepicker("getDate")), defaultDate : new Date(activityStartDate.datepicker("getDate"))});
													activityEndDate.val(activityStartDate.val());
												} else {
													activityEndDate.datepicker("option", {minDate : new Date(activityStartDate.datepicker("getDate"))});
												} // if (activityEndDate.datepicker("getDate") === null) else
											} else {
												activityEndDate.datepicker("option", {minDate : null});
											} // if (activityStartDate.datepicker("getDate") !== null) else
										} // function beforeShow_EndDatePicker()

										// bound jQuery UI Datepicker to input fields (activity start date and activity end date)
										if (!Modernizr.touch && !Modernizr.inputtypes.date) {
											$(".form-control.start-date-picker-field").datepicker({dateFormat : "yy-mm-dd", beforeShow : beforeShow_StartDatePicker});
											$(".form-control.end-date-picker-field").datepicker({dateFormat : "yy-mm-dd", beforeShow : beforeShow_EndDatePicker});
										} // if (!Modernizr.touch && !Modernizr.inputtypes.date)
									});
								</script>
							</div>
							<div class="row form-group">
								<label for="inputActivityLocation" class="col-md-3 control-label required-field">Activity Location</label>
								<div class="col-md-9">
									<input type="text" name="activity_location" class="form-control track-changes" id="inputActivityLocation" placeholder="e.g. Address, City, Province" maxlength="50" required<?php echo (isset($_POST['activity_location']) ? ' value="' . $_POST['activity_location'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

				    <div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Activity's Call-to-Action</h2>
						</div>
					</div>
					<div class="row form-section-content form-section-content-upload">
						<div class="col-md-12">
							<?php if ($page_mode != 'upload_only') : ?>
							<div class="row form-group">
								<div class="col-md-12">
									<p>Provide your pledge page's URL to promote your fundraising activity. If you don't have your own pledge page, we'll direct donors to event's donation page.</p>
								</div>
							</div>
							<?php endif; // if ($page_mode != 'upload_only') ?>
							<div class="row form-group">
								<label for="inputActivityCallToActionURL" class="col-md-3 control-label">Pledge Page's URL</label>
								<div class="col-md-9">
									<input type="url" name="activity_cta_url" class="form-control track-changes" id="inputActivityCallToActionURL" placeholder="http://event.org/yourname/" maxlength="255"<?php echo (isset($_POST['activity_cta_url']) ? ' value="' . $_POST['activity_cta_url'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

				    <div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Activity's Photos</h2>
						</div>
					</div>
					<div class="row form-section-content form-section-content-upload">
						<div class="col-md-12">
							<div class="row form-group">
								<div class="col-md-12">
									<p>Your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie will randomly combine photos from the organizer, other event participants, and you. Please choose a minimum of <?php echo $minimum_activity_photos_for_actiivty_vt; ?> photos that best tell your story and that you'd like to share with the event. Be sure to select an album cover photo<i class="glyphicon glyphicon-heart" style="color: #1facdf; font-size: .75em; vertical-align: top;"></i> and a call-to-action photo<i class="glyphicon glyphicon-star" style="color: #1facdf; font-size: .75em; vertical-align: top;"></i>.</p>
								</div>
							</div>
							<!-- Handlebar templates -->
							<script type="text/x-handlebars-template" id="list-shot-contain-template">
								{{#each shots}}
									<div class="{{type}}-contain uploaded-shot-contain col-md-3 fancybox{{#if featureShot}} featured{{/if}}{{#if ctaBackgroundImage}} cta-background-image{{/if}}" data-action-id="{{type}}-action-{{@index}}" href="{{shotUrl}}">
										<img src="{{desktopThumbnailUrl}}" class="img-responsive img-thumbnail">

										<h5 class="hidden">
											{{#if captionTitle}}
												{{captionTitle}}
											{{else}}
												<i class="glyphicon glyphicon-plus"></i> Add Caption
											{{/if}}
										</h5>
									</div>
									<div id="{{type}}-action-{{@index}}" class="hidden">
									<div class="container-fluid">
										<div class="row">
											<!--<div class="col-md-7"><input type="text" class="form-control input-lg" placeholder="Enter a caption..." value="{{captionTitle}}"></div>-->

											<div class="col-md-9">
												<div class="fancybox-set-button">
													{{#if featureShot}}
														<p><i class="glyphicon glyphicon-heart"></i> Album Cover</p>
													{{else}}
														<button class="btn btn-set-feature-shot" data-index="{{@index}}">Set as Album Cover</button>
													{{/if}}
												</div><!-- .fancybox-set-button -->
												<div class="fancybox-set-button">
													{{#if ctaBackgroundImage}}
														<p><i class="glyphicon glyphicon-star"></i> Call-to-Action Image</p>
													{{else}}
														<button class="btn btn-set-cta-background-image" data-index="{{@index}}">Set as Call-to-Action Image</button>
													{{/if}}
												</div><!-- .fancybox-set-button -->
											</div>

											<div class="col-md-2 col-md-offset-1"><button class="btn btn-primary btn-save-caption pull-right" data-shot-url="{{shotUrl}}" data-desktop-thumbnail-url="{{desktopThumbnailUrl}}" data-mobile-thumbnail-url="{{mobileThumbnailUrl}}" data-desktop-timeline-url="{{desktopTimelineUrl}}" data-mobile-timeline-url="{{mobileTimelineUrl}}" data-desktop-album-cover-url="{{desktopAlbumCoverUrl}}" data-mobile-album-cover-url="{{mobileAlbumCoverUrl}}" data-desktop-featured-image-url="{{desktopFeaturedImageUrl}}" data-mobile-featured-image-url="{{mobileFeaturedImageUrl}}" data-asset-id="{{assetId}}" data-index="{{@index}}">Save</button></div>
										</div>
									</div>
									</div><!-- #{{type}}-action -->
								{{/each}}
							</script>
							<div class="row">
								<div class="upload-shot col-md-3">
									<img data-src="holder.js/300x300/auto/text: + Upload Photos" class="img-responsive img-thumbnail">

<!--
									<div id="upload-progress" class="progress hidden"><div class="progress-bar progress-bar-yellow"></div></div>
-->
									<div id="upload-progress-all" class="progress hidden"><div class="progress-bar progress-bar-yellow"></div></div>

									<input type="file" name="user_images[]" class="fileinput-button" id="inputActivityPhotos" multiple accept="image/jpeg, image/png, image/gif" />
									<input type="hidden" name="activity_featured_image_id" id="inputActivityFeaturedImageId" class="track-changes" value="<?php echo (isset($_POST['activity_featured_image_id']) ? $_POST['activity_featured_image_id'] : '') ?>" />
									<input type="hidden" name="activity_cta_background_image_id" id="inputActivityCTABackgroundImageId" class="track-changes" value="<?php echo (isset($_POST['activity_cta_background_image_id']) ? $_POST['activity_cta_background_image_id'] : ''); ?>" />
									<input type="hidden" name="new_user_image_ids" id="inputActivityPhotoIds" class="track-changes" value="<?php echo (isset($_POST['new_user_image_ids']) ? $_POST['new_user_image_ids'] : ''); ?>" />
								</div>

								<div id="activity-shots" class="uploaded-shots"><!-- handlebar container --></div>
							</div><!-- .row -->
						</div><!-- .col-md-12 -->
					</div><!-- .row -->
					<script type="text/javascript">
						$(document).ready(function() {
							// Disable certain links in docs
							$('[href^=#]').click(function (hrefElement) {
								hrefElement.preventDefault()
							});


							// trap changes to input - if changed, disable preview button
							$('input.track-changes').change(function() {
								$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
							});


							//***** Handlebars - Start
							// Compile handlebars Templates
							var listShotContainSource   = $('#list-shot-contain-template').html();
							var listShotContainTemplate = Handlebars.compile(listShotContainSource);

							function updateShotContain(shotContainData) {
								var shotContainHtml = listShotContainTemplate(shotContainData);

								$('#activity-shots').html(shotContainHtml);
							}

							var activityShotContainData;

							// show $activity_photos via $_SESSION['activity_photos']
							$.getJSON('make-activity-shots-json.php', function(data) {
								activityShotContainData = data;
								updateShotContain(data);
							});
							//***** Handlebars - End


							//***** Fancybox - Start
							// bond Fancyboc to elements
							$('.fancybox:not(.used)').fancybox({
								openEffect	: 'elastic',
								closeEffect	: 'elastic',
								padding :     0,

								helpers : {
									title : {
										type : 'inside'
									}
								},

								beforeLoad: function() {
									var el, id = $(this.element).data('action-id');
									if (id) {
										el = $('#' + id);

										if (el.length) {
											this.title = el.html();
										}
									}
								}
							});

							// trap Fancybox close button click
							$('body').on('click', '.btn-close-lightbox', function() {
								$.fancybox.close();
							});

							// trap Fancybox save caption button click
							$('body').on('click', '.btn-save-caption', function() {
								// updates shot caption
								activeIndex = $(this).data('index');
								activeObj = activityShotContainData['shots'][activeIndex];
								activeObj.captionTitle = $(this).parents('.row').find('input.form-control').val();

								updateShotContain(activityShotContainData); // refresh list on screen

								$.fancybox.close(); // close the lightbox
							});

							// trap Fancybox set as featured shot button click
							$('body').on('click', '.btn-set-feature-shot', function() {
								//remove old feature shot flag
							  	$.each(activityShotContainData['shots'], function() {
									if (this.featureShot) {
										this.featureShot = undefined;
									}
								});

								//set new feature shot flag
								activeIndex = $(this).data('index');
								activeObj = activityShotContainData['shots'][activeIndex];
								activeObj.featureShot = 'yes';

								//replace existing button with feature shot indicator
								$(this).parent().html('<small><i class="glyphicon glyphicon-heart"></i> Album Cover</small>');

								// disable preview video timeline button if this is a different feature shot
								if (activeObj.assetId !== $('#inputActivityFeaturedImageId').val()) {
									$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
								}

								//update feature shot id for submission
								$('#inputActivityFeaturedImageId').val(activeObj.assetId);

								updateShotContain(activityShotContainData); // refresh list on screen
							});

							// trap Fancybox set as call-to-action background image button click
							$('body').on('click', '.btn-set-cta-background-image', function() {
								//remove old call-to-action background image flag
							  	$.each(activityShotContainData['shots'], function() {
									if (this.ctaBackgroundImage) {
										this.ctaBackgroundImage = undefined;
									}
								});

								//set new call-to-action background image flag
								activeIndex = $(this).data('index');
								activeObj = activityShotContainData['shots'][activeIndex];
								activeObj.ctaBackgroundImage = 'yes';

								//replace existing button with call-to-action background image indicator
								$(this).parent().html('<small><i class="glyphicon glyphicon-star"></i> Call-to-Action Image</small>');

								// disable preview video timeline button if this is a different call to caction background image
								if (activeObj.assetId !== $('#inputActivityCTABackgroundImageId').val()) {
									$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
								}

								//update call-to-action background image id for submission
								$('#inputActivityCTABackgroundImageId').val(activeObj.assetId);

								updateShotContain(activityShotContainData); // refresh list on screen
							});
							//***** Fancybox - End


							//***** jQuery File Update for Activity Photos - Start
							// Define the url to send the image data to
							var url = 'UploadHandler-user-images.php';

							// bound jQuery File Upload to upload activity photos input button
							$('#inputActivityPhotos').fileupload({
								url:      url,
								dataType: 'json',

								submit: function (e, data) {
									// This is to reset the overall progress bar when the user clicks submit
									$('#upload-progress-all').removeClass('hidden');
									$('#upload-progress-all .progress-bar').css('width', '0%')
								},

								start: function (e) {
									// This is to reset the individual file progress bar at the start of each file being uploaded
									$('#upload-progress').removeClass('hidden');
									$('#upload-progress .progress-bar').css('width', '0%');
								},

								progressall: function (e, data) {
									// Update the overall progress bar while files are being uploaded
									var progressAll = parseInt(data.loaded / data.total * 100, 10);

									$('#upload-progress-all .progress-bar').css('width', progressAll + '%');
								},

//								progress: function (e, data) {
//									// Update the individual file progress bar while the file is being uploaded
//									var progress = parseInt(data.loaded / data.total * 100, 10);
//
//									$('#upload-progress .progress-bar').css('width', progress + '%');
//								},

								done: function (e, data) {
									var activity_photo_ids = $('#inputActivityPhotoIds').val();

									// Show uploaded activity photo files
									$.each(data.result.user_images, function (index, file) {
										if (file.url) {
											activity_photo_ids += (activity_photo_ids === '' ? file.asset_id : ',' + file.asset_id);

											newShotObject = {
															 shotUrl: file.url,
															 desktopThumbnailUrl: file.thumbnail_desktop_myshotsUrl,
															 mobileThumbnailUrl: file.thumbnail_mobile_myshotsUrl,
															 desktopTimelineUrl: file.thumbnail_desktop_timelineUrl,
															 mobileTimelineUrl: file.thumbnail_mobile_timelineUrl,
															 desktopAlbumCoverUrl: file.thumbnail_desktop_album_coverUrl,
															 mobileAlbumCoverUrl: file.thumbnail_mobile_album_coverUrl,
															 desktopFeaturedImageUrl: file.thumbnail_desktop_featured_imageUrl,
															 mobileFeaturedImageUrl: file.thumbnail_mobile_featured_imageUrl,
															 type: "activity-shot",
															 assetId: file.asset_id
															};

											activityShotContainData['shots'].unshift(newShotObject);
										} // if (file.url)
									}); // $.each(data.result.user_images, function (index, file)

									// set a default featured image / album cover if there isn't one already
									if (activityShotContainData['shots'].length === 1 && $('#inputActivityFeaturedImageId').val() === '') {
										activityShotContainData['shots'][0].featureShot = 'yes';

										$('#inputActivityFeaturedImageId').val(activityShotContainData['shots'][0].assetId);
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									// set a default call-to-action background image if there isn't one already
									if (activityShotContainData['shots'].length > 1 && $('#inputActivityCTABackgroundImageId').val() === '') {
										activityShotContainData['shots'][1].ctaBackgroundImage = 'yes';

										$('#inputActivityCTABackgroundImageId').val(activityShotContainData['shots'][1].assetId);
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									updateShotContain(activityShotContainData); // refresh list on screen

									// disable preview video timeline button if this is a different list of new photo ids
									if (activity_photo_ids !== $('#inputActivityPhotoIds').val()) {
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									// update the new photo ids into the hidden input field
									$('#inputActivityPhotoIds').val(activity_photo_ids);

									// hides progress bar after it's done
									$('#upload-progress-all, #upload-progress').addClass('hidden');
								}
							});
							//***** jQuery File Update for Activity Photos - End
						});
					</script>

					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-2 col-md-offset-10">
								<input type="hidden" name="activity_id" id="inputActivityId"<?php echo (isset($_POST['activity_id']) ? ' value="' . $_POST['activity_id'] . '"' : ''); ?> />
								<input type="hidden" name="event_id" id="inputEventId"<?php echo (isset($_POST['event_id']) ? ' value="' . $_POST['event_id'] . '"' : ''); ?> />
								<button type="submit" class="lead btn btn-primary pull-right" name="save_activity" id="btn-save"><?php echo ($page_mode == 'edit' || (isset($_POST['activity_id']) && ($_POST['activity_id'] != '')) ? 'Save Changes' : 'Create Activity'); ?></button>
							</div>
						</div>
					</nav>

					<?php if ($page_mode == 'edit' || (isset($_POST['activity_id']) && ($_POST['activity_id'] != ''))) : ?>
					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-3 col-md-offset-9">
								<a  class="lead btn btn-default pull-right" id="btn-preview-activity-tv" href="create-edit-activity-vt.php?activity_id=<?php echo $_POST['activity_id'] ?>">Preview my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie</a>
							</div>
						</div>
					</nav>
					<?php endif; ?>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->
		<?php endif; // if ($display_form) ?>

		<?php output_footer(); ?>