<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$page_mode                  = 'create';
$current_user               = FALSE;
$event                      = FALSE;
$event_logo                 = FALSE;
$event_featured_image       = FALSE;
$event_cta_background_image = FALSE;
$event_photos               = array();
$event_creator              = FALSE;

$no_error                   = TRUE;
$success_message            = '';
$error_message              = '';

$minimum_activity_photos_for_event_vt = 8;

unset($_SESSION['current_user']);


/**
 * This function resets all necessary super global variables so the form is ready to create a new event
 */
function reset_super_global_variables() {
	unset($_SESSION['event_featured_image']);
	unset($_SESSION['event_cta_background_image']);
	unset($_SESSION['event_photos']);
	unset($_GET['event_id']);
	unset($_POST['event_id']);
	unset($_POST['event_name']);
	unset($_POST['event_description']);
	unset($_POST['event_start_date']);
	unset($_POST['event_end_date']);
	unset($_POST['event_location']);
	unset($_POST['event_narrative_sentence_01']);
	unset($_POST['event_narrative_sentence_02']);
	unset($_POST['event_narrative_sentence_03']);
	unset($_POST['event_narrative_sentence_04']);
	unset($_POST['event_logo_id']);
	unset($_POST['event_cta_background_image']);
	unset($_POST['event_cta_url']);
	unset($_POST['event_featured_image_id']);
} // function prepare_variables_to_create_new_event


if (isset($_POST['save_event'])) {
	if (isset($_POST['organization_name']) && $_POST['organization_name'] && isset($_POST['user_email']) && $_POST['user_email'] &&
		isset($_POST['event_name']) && $_POST['event_name'] &&
		isset($_POST['event_start_date']) && $_POST['event_start_date'] && isset($_POST['event_location']) && $_POST['event_location'] &&
		isset($_POST['event_narrative_sentence_01']) && $_POST['event_narrative_sentence_01'] &&
		isset($_POST['event_logo_id']) && $_POST['event_logo_id'] &&
		isset($_POST['event_cta_url']) && $_POST['event_cta_url']) {

		if (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes')) {
			$page_mode = 'upload_only';
		} // if (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes'))

		// if there are new user images, put them in $_SESSION['event_photos'] because we want to make sure the continue to appear on the page even there are validation errors
		if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '') {
			foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id) {
				$new_user_image = get_asset($new_user_image_id);

				if ($new_user_image) {
					$_SESSION['event_photos'][] = $new_user_image;
				} // if ($new_user_image)
			} // foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id)
		} // if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '')

		// get user record of event creator
		$event_creator = get_user('', '', '', '', $_POST['user_email']);

		if ($event_creator) {
			if ($event_creator['organization_name'] != $_POST['organization_name']) {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error e-mail address already exist but do not match organization name.';
			} else {
				$current_user = $event_creator;
			}
		} else { // user record for event creator does not exist, create a new user
			$event_creator = create_user($_POST['organization_name'], '', '', $_POST['user_email']);

			if ($event_creator) {
				$current_user = $event_creator;
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error creating user.';
			} // if ($event_creator) else
		} // if ($event_creator)

		// get event logo
		if (isset($_POST['event_logo_id'])) {
			$event_logo = get_asset($_POST['event_logo_id']);

			if ($event_logo) {
				// if the event logo's created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
				if ($current_user && array_key_exists('id', $current_user) && $current_user['id'] && $event_logo['created_by'] == 1) {
					$event_logo = edit_asset($event_logo['id'], '', '', '', $current_user['id']);

					if ($event_logo) {
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . "Error updating event's logo.";
					} // if ($event_logo) else
				} else {
				} // if ($event_logo['created_by'] == 1) else
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . "Error finding event's logo.";
			} // if ($event_logo) else
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'No event logo specified.';
		} // if (isset($_POST['event_logo_id'])) else

		// get event featured image
		if (isset($_POST['event_featured_image_id']) && $_POST['event_featured_image_id']) {
			$event_featured_image = get_asset($_POST['event_featured_image_id']);

			if ($event_featured_image) {
				// if the event featured image's created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
				if ($current_user && array_key_exists('id', $current_user) && $current_user['id'] && $event_featured_image['created_by'] == 1) {
					$event_featured_image = edit_asset($event_featured_image['id'], '', '', '', $current_user['id']);

					if ($event_featured_image) {
						$_SESSION['event_featured_image'] = $event_featured_image;
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . "Error updating event's album cover image.";

						unset($_SESSION['event_featured_image']);
					} // if (!$event_featured_image) else
				} else {
					$_SESSION['event_featured_image'] = $event_featured_image;
				} // if ($event_featured_image['created_by'] == 1) else
			} else {
				$no_error = FALSE;
				$error_message .= ($error_message ? '<br />' : '') . "Error finding event's albumn cover image.";

				unset($_SESSION['event_featured_image']);
			} // ($event_featured_image) else
		} // if (isset($_POST["event_featured_image_id']) && $_POST['event_featured_image_id'])

		// get event call-to-action background image
		if (isset($_POST['event_cta_background_image_id']) && $_POST['event_cta_background_image_id']) {
			$event_cta_background_image = get_asset($_POST['event_cta_background_image_id']);

			if ($event_cta_background_image) {
				// if the event call-to-action background image's created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
				if ($current_user && array_key_exists('id', $current_user) && $current_user['id'] && $event_cta_background_image['created_by'] == 1) {
					$event_cta_background_image = edit_asset($event_cta_background_image['id'], '', '', '', $current_user['id']);

					if ($event_cta_background_image) {
						$_SESSION['event_cta_background_image'] = $event_cta_background_image;
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . "Error updating event's call-to-action background image.";

						unset($_SESSION['event_cta_background_image']);
					} // if ($event_cta_background_image) else
				} else {
					$_SESSION['event_cta_background_image'] = $event_cta_background_image;
				} // if ($event_cta_background_image['created_by'] == 1) else
			} else {
				$no_error = FALSE;
				$error_message .= ($error_message ? '<br />' : '') . "Error finding event's call-to-action background image.";

				unset($_SESSION['event_cta_background_image']);
			} // ($event_cta_background_image) else
		} // if (isset($_POST["event_cta_background_image_id']) && $_POST['event_cta_background_image_id'])

		if ($no_error) {
			if ((isset($_POST['event_id']) && ($_POST['event_id'] == '')) || !isset($_POST['event_id'])) { // is this a new event or an existing event
				$event = create_event($_POST['event_name'], $_POST['event_description'], $_POST['event_start_date'], $_POST['event_end_date'], $_POST['event_location'], $_POST['event_logo_id'], (isset($_POST['event_featured_image_id']) ? $_POST['event_featured_image_id'] : ''), $_POST['event_narrative_sentence_01'], (isset($_POST['event_narrative_sentence_02']) ? $_POST['event_narrative_sentence_02'] : ''), (isset($_POST['event_narrative_sentence_03']) ? $_POST['event_narrative_sentence_03'] : ''), (isset($_POST['event_narrative_sentence_04']) ? $_POST['event_narrative_sentence_04'] : ''), (isset($_POST['event_cta_background_image_id']) ? $_POST['event_cta_background_image_id'] : ''), $_POST['event_cta_url'], $event_creator['id']);

				if ($event) {
					$_POST['event_id'] = $event['id'];
					$page_mode       = (isset($_POST['upload_shots_only']) && ($_POST['upload_shots_only'] == 'yes') ? 'upload_only' : 'edit');
					$success_message = 'Event created. Be sure to check your email for important links to your event\'s page and CrowdShot web app. To see you event\'s page now, click <a href="event.php?event_id=' . $event['id'] . '">here</a>.';
					$email_message   = '<p>Your event "' . $event['event_name'] . '" has been created.</p>' .
									   '<p>Click <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-edit-event.php?event_id=' . $event['id'] . '">here</a> to edit it.</p>' .
									   '<p>Click <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-edit-event-vt.php?event_id=' . $event['id'] . '">here</a> to make a CrowdShot movie from your event photos.</p>' .
									   '<p>Your colleagues can upload photos <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/upload-photos-to-event.php?event_id=' . $event['id'] . '">here</a>.</p>' .
									   '<p>Your participants can visit your event\'s page <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a>.</p>' .
									   '<p>Your participants can create activities off your event <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-edit-activity.php?event_id=' . $event['id'] . '">here</a>.</p>' .
									   '<p>Your participants can create CrowdShot movie of your event <a href="http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/create-event-vt.php?event_id=' . $event['id'] . '">here</a>.</p>';

					if (!send_notification($event['event_name'], $event_creator['user_email'], $email_message)) {
						$error_message = 'Error notifying you on your new event. Please contact our Technical Support.';
					} // if (!send_notification($event['event_name'], $event_creator['user_email'], $email_message))
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error creating event.';
				} // if (!$event) else
			} else {
				$event = edit_event($_POST['event_id'], $_POST['event_name'], $_POST['event_description'], $_POST['event_start_date'], $_POST['event_end_date'], $_POST['event_location'], $_POST['event_logo_id'], (isset($_POST['event_featured_image_id']) ? $_POST['event_featured_image_id'] : ''), $_POST['event_narrative_sentence_01'], (isset($_POST['event_narrative_sentence_02']) ? $_POST['event_narrative_sentence_02'] : ''), (isset($_POST['event_narrative_sentence_03']) ? $_POST['event_narrative_sentence_03'] : ''), (isset($_POST['event_narrative_sentence_04']) ? $_POST['event_narrative_sentence_04'] : ''), (isset($_POST['event_cta_background_image_id']) ? $_POST['event_cta_background_image_id'] : ''), $_POST['event_cta_url'], $current_user['id']);

				if ($event) {
					$success_message = 'Event updated.';
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error updating event.';
				} // if ($event) else
			} // if ((isset($_POST['event_id']) && ($_POST['event_id'] == '')) || !isset($_POST['event_id'])) else

			if ($event) { // if the event was successfully created / updated, create asset relationship records for new event photos
				if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '') {
					$asset_relationship_rows = array();

					foreach (explode(',', $_POST['new_user_image_ids']) as $new_user_image_id) {
						$asset_relationship_rows[] = array('asset_id' => $new_user_image_id, 'related_object_type' => 'event', 'related_object_id' => $event['id'], 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);
					} // foreach (explode(',', $_POST['event_photo_ids']) as $new_user_image_id)

					if (!empty($asset_relationship_rows)) {
						$create_asset_relationship_success = create_asset_relationships($asset_relationship_rows);
					} // if (!empty($asset_relationship_rows))

					if ($create_asset_relationship_success) {
						unset($_POST['new_user_image_ids']);
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . 'Error attaching photos to event.';
					} // if ($create_asset_relationship_success) else
				} // if (isset($_POST['new_user_image_ids']) && $_POST['new_user_image_ids'] != '')
					
				$event_photos = get_assets('', 'user_image', 'published', 'event', $event['id']);

				if (empty($event_photos)) {
					unset($_SESSION['event_photos']);
				} else {
					// go through each event photos and see if the created by is equal to 1 (default user when there isn't one during the upload process); if so, update it to the current user
					foreach ($event_photos as $event_photo_key => $event_photo) {
						if ($event_photo['created_by'] == 1) {
							$revised_event_photo = edit_asset($event_photo['id'], '', '', '', $current_user['id']);

							if ($revised_event_photo) {
								$event_photos[$event_photo_key] = $revised_event_photo;
							} else {
								$no_error      =  FALSE;
								$error_message .= ($error_message ? '<br />' : '') . 'Error updating event photo (' . $event_photo['id'] . ').';
							} // if ($revised_event_photo) else
						} // if ($event_photo['created_by'] == 1) else
					} // foreach ($event_photos as $event_photo_key => $event_photo)

					$_SESSION['event_photos'] = $event_photos;
				} // if (empty($event_photos)) else
			} // if ($event)
		} // if ($no_error)
	} else {
		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Required fields are not filled.';
	} // check required input fields
} else {
	if (isset($_GET['event_id']) && $_GET['event_id']) {
		$page_mode = (isset($_POST['upload_shots_only']) && $_POST['upload_shots_only'] == 'yes' ? 'upload_only' : 'edit');
		$event     = get_event_details($_GET['event_id']);

		if ($event) {
			$event_creator = get_user($event['created_by'], '', '', '');
	
			unset($_POST['organization_name']);
			unset($_POST['user_email']);

			$_POST['event_id']                      = $event['id'];
			$_POST['event_name']                    = $event['event_name'];
			$_POST['event_description']             = $event['event_description'];
			$_POST['event_start_date']              = $event['event_start_date'];
			$_POST['event_end_date']                = $event['event_end_date'];
			$_POST['event_location']                = $event['event_location'];
			$_POST['event_narrative_sentence_01']   = $event['event_narrative_sentence_01'];
			$_POST['event_narrative_sentence_02']   = $event['event_narrative_sentence_02'];
			$_POST['event_narrative_sentence_03']   = $event['event_narrative_sentence_03'];
			$_POST['event_narrative_sentence_04']   = $event['event_narrative_sentence_04'];
			$_POST['event_logo_id']                 = $event['event_logo_id'];
			$_POST['event_cta_background_image_id'] = $event['event_cta_background_image_id'];
			$_POST['event_cta_url']                 = $event['event_cta_url'];
			$_POST['event_featured_image_id']       = $event['event_featured_image_id'];

			$event_logo = ($event['event_logo_id'] ? get_asset($event['event_logo_id']) : FALSE);

			$event_featured_image = ($event['event_featured_image_id'] ? get_asset($event['event_featured_image_id']) : FALSE);

			if ($event_featured_image) {
				$_SESSION['event_featured_image'] = $event_featured_image;
			} else {
				unset($_SESSION['event_featured_image']);
			} // if ($event_featured_image)

			$event_cta_background_image = ($event['event_cta_background_image_id'] ? get_asset($event['event_cta_background_image_id']) : FALSE);

			if ($event_cta_background_image) {
				$_SESSION['event_cta_background_image'] = $event_cta_background_image;
			} else {
				unset($_SESSION['event_cta_background_image']);
			} // if ($event_cta_background_image)

			$event_photos = get_assets('', 'user_image', 'published', 'event', $event['id']);

			if (empty($event_photos)) {
				unset($_SESSION['event_photos']);
			} else {
				$_SESSION['event_photos'] = $event_photos;
			} // if (empty($event_photos)) else
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Cannot find event (' . $_GET['event_id'] . ').';

			$page_mode = 'create';

			reset_super_global_variables();
		} // if ($event) else
	} else {
		reset_super_global_variables();
	} // if (isset($_GET['event_id'])) else
} // if (isset($_POST['save_event'])) else
?>
		<?php output_header('create-edit-event-page', (($page_mode == 'edit') || (isset($_POST['event_id']) && $_POST['event_id'] != '' && $page_mode != 'upload_only') ? 'Edit' : ($page_mode == 'upload_only' ? 'Upload Photos to' : 'Create')) . ' Event | CrowdShot', TRUE, TRUE, TRUE, TRUE, FALSE, TRUE, FALSE); ?>

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

		<!-- Main create event page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>THANK YOU for signing up for <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span>. Please help us create the best mobile app experience for your event participants by completing the form below. As the event organizer, you'll be providing your logo, photography and key messages to promote your event while helping participants create shareable <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movies.</p>
					</div>
				</div>
			</div><!-- .container -->
		</section><!-- #create-edit-introduction -->

		<!-- Main create / edit event form -->
		<section id="create-edit-form">
			<div class="container">
				<form class="form-horizontal" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Organization</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<label for="inputOrganizationName" class="col-md-3 control-label required-field">Organization Name</label>
								<div class="col-md-9">
									<input type="text" name="organization_name" class="form-control" id="inputOrganizationName" placeholder="e.g. Organization Name" maxlength="100" required<?php echo (isset($_POST['organization_name'])? ' value="' . $_POST['organization_name'] . '"' : ''); ?> <?php // echo (($page_mode == 'edit') || (isset($_POST['event_id']) && ($_POST['event_id'] != '')) ? 'readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEmail" class="col-md-3 control-label required-field">Organization Email</label>
								<div class="col-md-9">
									<input type="email" name="user_email" class="form-control" id="inputEmail" placeholder="e.g. email@organization.com" maxlength="100" required<?php echo (isset($_POST['user_email'])? ' value="' . $_POST['user_email'] . '"' : ''); ?> <?php // echo (($page_mode == 'edit') || (isset($_POST['event_id']) && ($_POST['event_id'] != '')) ? 'readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Event</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<label for="inputEventName" class="col-md-3 control-label required-field">Event Name</label>
								<div class="col-md-9">
									<input type="text" name="event_name" class="form-control track-changes" id="inputEventName" placeholder="e.g. <?php echo date('Y'); ?> Event Name" maxlength="50" required<?php echo (isset($_POST['event_name']) ? ' value="' . $_POST['event_name'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEventDescription" class="col-md-3 control-label">Event Description</label>
								<div class="col-md-9">
									<textarea name="event_description" class="form-control track-changes" id="inputEventDescription" placeholder="e.g. Support of x-charity at y-event." rows="3" maxlength="1000" <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?>><?php echo (isset($_POST['event_description']) ? $_POST['event_description'] : ''); ?></textarea>
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEventFromDate" class="col-md-3 control-label required-field">Event Date</label>
								<div class="col-md-2">
									<input type="date" name="event_start_date" class="form-control track-changes start-date-picker-field" id="inputEventStartDate" placeholder="e.g. <?php echo date('Y-m-d'); ?>" maxlength="10" required<?php echo (isset($_POST['event_start_date']) ? ' value="' . $_POST['event_start_date'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
								<label for="inputEventToDate" class="col-md-1 control-label required-field">to</label>
								<div class="col-md-2">
									<input type="date" name="event_end_date" class="form-control track-changes end-date-picker-field" id="inputEventEndDate" placeholder="e.g. <?php echo date('Y-m-d'); ?>" maxlength="10" required<?php echo (isset($_POST['event_end_date']) ? ' value="' . $_POST['event_end_date'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
								<script type="text/javascript">
									$(document).ready(function() {
										/**
										 * Function to set and ensure maxDate of the event start date datepicker is not greater than the event end date
										 */
										function beforeShow_StartDatePicker() {
											var eventStartDate = $(".form-control.start-date-picker-field");
											var eventEndDate   = $(".form-control.end-date-picker-field");

											if (eventEndDate.datepicker("getDate") !== null) {
												if (eventStartDate.datepicker("getDate") === null) {
													eventStartDate.datepicker("option", {maxDate : new Date(eventEndDate.datepicker("getDate")), defaultDate : new Date(eventEndDate.datepicker("getDate"))});
													eventStartDate.val(eventEndDate.val());
												} else {
													eventStartDate.datepicker("option", {maxDate : new Date(eventEndDate.datepicker("getDate"))});
												} // if (eventStartDate.datepicker("getDate") === null) else
											} else {
												eventStartDate.datepicker("option", {maxDate : null});
											} // if (eventEndDate.datepicker("getDate") !== null) else
										} // function beforeShow_StartDatePicker()

										/**
										 * Function to set and ensure minDate of the event end date datepicker is not less than the event start date
										 */
										function beforeShow_EndDatePicker() {
											var eventStartDate = $(".form-control.start-date-picker-field");
											var eventEndDate   = $(".form-control.end-date-picker-field");

											if (eventStartDate.datepicker("getDate") !== null) {
												if (eventEndDate.datepicker("getDate") === null) {
													eventEndDate.datepicker("option", {minDate : new Date(eventStartDate.datepicker("getDate")), defaultDate : new Date(eventStartDate.datepicker("getDate"))});
													eventEndDate.val(eventStartDate.val());
												} else {
													eventEndDate.datepicker("option", {minDate : new Date(eventStartDate.datepicker("getDate"))});
												} // if (eventEndDate.datepicker("getDate") === null) else
											} else {
												eventEndDate.datepicker("option", {minDate : null});
											} // if (eventStartDate.datepicker("getDate") !== null) else
										} // function beforeShow_EndDatePicker()

										// bound jQuery UI Datepicker to input fields (event start date and event end date)
										if (!Modernizr.touch && !Modernizr.inputtypes.date) {
											$(".form-control.start-date-picker-field").datepicker({dateFormat : "yy-mm-dd", beforeShow : beforeShow_StartDatePicker});
											$(".form-control.end-date-picker-field").datepicker({dateFormat : "yy-mm-dd", beforeShow : beforeShow_EndDatePicker});
										} // if (!Modernizr.touch && !Modernizr.inputtypes.date)
									});
								</script>
							</div>
							<div class="row form-group">
								<label for="inputEventLocation" class="col-md-3 control-label required-field">Event Location</label>
								<div class="col-md-9">
									<input type="text" name="event_location" class="form-control track-changes" id="inputEventLocation" placeholder="e.g. City, Province" maxlength="50" required<?php echo (isset($_POST['event_location']) ? ' value="' . $_POST['event_location'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Event's Key Messages</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<?php if ($page_mode != 'upload_only') : ?>
							<div class="row form-group">
								<div class="col-md-12">
									<p>Please complete the following narrative captions about your event. The captions allow up to 75 characters each and will appear on some of the photos shown in the <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> videos of your event. Key messages can be often found on your website.</p>
								</div>
							</div>
							<?php endif; // if ($page_mode != 'upload_only') ?>
							<div class="row form-group">
								<label for="inputEventNarrativeSentence01" class="col-md-3 control-label required-field">Narrative Caption 1</label>
								<div class="col-md-9">
									<input type="text" name="event_narrative_sentence_01" class="form-control track-changes" id="inputEventNarrativeSentence01" placeholder="e.g. Join us as we raise funds and awareness to tackle x-issue." maxlength="75" required<?php echo (isset($_POST['event_narrative_sentence_01']) ? ' value="' . $_POST['event_narrative_sentence_01'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEventNarrativeSentence02" class="col-md-3 control-label">Narrative Caption 2</label>
								<div class="col-md-9">
									<input type="text" name="event_narrative_sentence_02" class="form-control track-changes" id="inputEventNarrativeSentence02" placeholder="e.g. X-event brings x-number of people together to do some sort of activity." maxlength="75"<?php echo (isset($_POST['event_narrative_sentence_02']) ? ' value="' . $_POST['event_narrative_sentence_02'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEventNarrativeSentence03" class="col-md-3 control-label">Narrative Caption 3</label>
								<div class="col-md-9">
									<input type="text" name="event_narrative_sentence_03" class="form-control track-changes" id="inputEventNarrativeSentence03" placeholder="e.g. Participation is growing annually, with the event now held in X-number of cities across the country." maxlength="75"<?php echo (isset($_POST['event_narrative_sentence_03']) ? ' value="' . $_POST['event_narrative_sentence_03'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
							<div class="row form-group">
								<label for="inputEventNarrativeSentence04" class="col-md-3 control-label">Narrative Caption 4</label>
								<div class="col-md-9">
									<input type="text" name="event_narrative_sentence_04" class="form-control track-changes" id="inputEventNarrativeSentence04" placeholder="e.g. Your donation will make it easier for people with X live better lives." maxlength="75"<?php echo (isset($_POST['event_narrative_sentence_04']) ? ' value="' . $_POST['event_narrative_sentence_04'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

				    <div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Event's Website</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<?php if ($page_mode != 'upload_only') : ?>
							<div class="row form-group">
								<div class="col-md-12">
									<p>Please provide your event’s website address. You can choose a specific donation page link in place of the home page.</p>
								</div>
							</div>
							<?php endif; // if ($page_mode != 'upload_only') ?>
							<div class="row form-group">
								<label for="inputEventCallToActionURL" class="col-md-3 control-label required-field">Event Website URL</label>
								<div class="col-md-9">
									<input type="url" name="event_cta_url" class="form-control track-changes" id="inputEventCallToActionURL" placeholder="http://event.org/" maxlength="255" required<?php echo (isset($_POST['event_cta_url']) ? ' value="' . $_POST['event_cta_url'] . '"' : ''); ?> <?php echo ($page_mode == 'upload_only' ? ' readonly' : ''); ?> />
								</div>
							</div>
						</div>
					</div>

				    <div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Event's Logo</h2>
						</div>
					</div>
					<div class="row form-section-content form-section-content-upload">
						<div class="col-md-12">
							 <?php if ($page_mode != 'upload_only') : ?>
							<div class="row form-group">
								<div class="col-md-12">
									<p class="control-label required-field" style="text-align: left;">Please upload a logo with a transparent background (PNG or GIF).</p>
								</div>
							</div>
							<?php endif; // if ($page_mode != 'upload_only') ?>
							<div class="row">
								<?php if ($event_logo) : ?>
									<div id="original-event-logo" class="col-md-3">
										<img src="<?php echo $event_logo['asset_url']; ?>" alt="original event logo" class="img-responsive img-thumbnail">
									</div>
								<?php endif; ?>
								<div id="newEventLogo" class="col-md-3 hidden"></div>

								<div id="upload-user-logo" class="col-md-3">
									<?php if ($page_mode != 'upload_only') : ?>
									<btn class="btn btn-<?php echo ($event_logo ? 'default' : 'primary'); ?> col-md-12" id="upload-logo-btn"><?php echo ($event_logo ? 'Replace Logo' : 'Upload Logo'); ?></btn>
									<input type="file" name="user_logo[]" class="col-md-12 fileinput-button" id="inputEventLogoFullPathFileName" accept="image/png, image/gif" />
									<?php endif; // if ($page_mode != 'upload_only') ?>
									<input type="hidden" name="event_logo_id" id="inputEventLogoId" class="track-changes" value="<?php echo (isset($_POST['event_logo_id']) ? $_POST['event_logo_id'] : ''); ?>" required />
								</div>
							</div><!-- .row -->
						</div>
					</div>
					<script type="text/javascript">
						$(document).ready(function() {
							//***** jQuery File Update for Event Logo - Start
							// Define the url to send the image data to
							var url = 'UploadHandler-user-logo.php';

							// Call the fileupload widget and set some parameters
							$('#inputEventLogoFullPathFileName').fileupload({
								url:      url,
								dataType: 'json',

								done: function (e, data) {
								
									// Hide original logo
									$('#original-event-logo').addClass('hidden');
									
									// Show uploaded event logo file
									$.each(data.result.user_logo, function (index, file) {
										if (file.url) {
											$('#newEventLogo').html('<img src="' + file.url + '" alt="new event logo" class="img-responsive img-thumbnail"/>');
											$('#inputEventLogoId').val(file.asset_id);
										} else {
											$('#newEventLogo').html('<div class="alert alert-danger"><p>Invalid image file.</p></div>');
										} // if (file.url)
										$('#newEventLogo').removeClass('hidden');
										$('#upload-logo-btn').text('Replace Logo').addClass('btn-default').removeClass('btn-primary');
									}); // $.each(data.result.user_logo, function (index, file)
								}
							});
							//***** jQuery File Update for Event Logo - End
						});
					</script>

				    <div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your Event's Photos</h2>
						</div>
					</div>
					<div class="row form-section-content form-section-content-upload">
						<div class="col-md-12">
							<div class="row form-group">
								<div class="col-md-12">
									<p>Please choose photos that best show your past event(s) to help participants tell their stories. Six to ten photos will be helpful. Be sure to select an album cover photo<i class="glyphicon glyphicon-heart" style="color: #1facdf; font-size: .75em; vertical-align: top;"></i> and a call-to-action photo<i class="glyphicon glyphicon-star" style="color: #1facdf; font-size: .75em; vertical-align: top;"></i>.</p>
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

									<input type="file" name="user_images[]" class="fileinput-button" id="inputEventPhotos" multiple accept="image/jpeg, image/png, image/gif" />
									<input type="hidden" name="event_featured_image_id" id="inputEventFeaturedImageId" class="track-changes" value="<?php echo (isset($_POST['event_featured_image_id']) ? $_POST['event_featured_image_id'] : '') ?>" />
									<input type="hidden" name="event_cta_background_image_id" id="inputEventCTABackgroundImageId" class="track-changes" value="<?php echo (isset($_POST['event_cta_background_image_id']) ? $_POST['event_cta_background_image_id'] : ''); ?>" />
									<input type="hidden" name="new_user_image_ids" id="inputEventPhotoIds" class="track-changes" value="<?php echo (isset($_POST['new_user_image_ids']) ? $_POST['new_user_image_ids'] : ''); ?>" />
								</div>

								<div id="event-shots" class="uploaded-shots"><!-- handlebar container --></div>
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

								$('#event-shots').html(shotContainHtml);
							}

							var eventShotContainData;

							// show $event_photos via $_SESSION['event_photos']
							$.getJSON('make-event-shots-json.php', function(data) {
								eventShotContainData = data;
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
								activeObj = eventShotContainData['shots'][activeIndex];
								activeObj.captionTitle = $(this).parents('.row').find('input.form-control').val();

								updateShotContain(eventShotContainData); // refresh list on screen

								$.fancybox.close(); // close the lightbox
							});

							// trap Fancybox set as featured shot button click
							$('body').on('click', '.btn-set-feature-shot', function() {
								//remove old feature shot flag
							  	$.each(eventShotContainData['shots'], function() {
									if (this.featureShot) {
										this.featureShot = undefined;
									}
								});

								//set new feature shot flag
								activeIndex = $(this).data('index');
								activeObj = eventShotContainData['shots'][activeIndex];
								activeObj.featureShot = 'yes';

								//replace existing button with feature shot indicator
								$(this).parent().html('<small><i class="glyphicon glyphicon-heart"></i> Album Cover</small>');

								// disable preview video timeline button if this is a different call to caction background image
								if (activeObj.assetId !== $('#inputEventFeaturedImageId').val()) {
									$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
								}

								//update feature shot url for submission
								$('#inputEventFeaturedImageId').val(activeObj.assetId);

								updateShotContain(eventShotContainData); // refresh list on screen
							});

							// trap Fancybox set as call-to-action background image button click
							$('body').on('click', '.btn-set-cta-background-image', function() {
								//remove old call-to-action background image flag
							  	$.each(eventShotContainData['shots'], function() {
									if (this.ctaBackgroundImage) {
										this.ctaBackgroundImage = undefined;
									}
								});

								//set new call-to-action background image flag
								activeIndex = $(this).data('index');
								activeObj = eventShotContainData['shots'][activeIndex];
								activeObj.ctaBackgroundImage = 'yes';

								//replace existing button with call-to-action background image indicator
								$(this).parent().html('<small><i class="glyphicon glyphicon-star"></i> Call-to-Action Image</small>');

								// disable preview video timeline button if this is a different call to caction background image
								if (activeObj.assetId !== $('#inputEventCTABackgroundImageId').val()) {
									$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
								}

								//update call-to-action background image id for submission
								$('#inputEventCTABackgroundImageId').val(activeObj.assetId);

								updateShotContain(eventShotContainData); // refresh list on screen
							});
							//***** Fancybox - End


							//***** jQuery File Update for Event Photos - Start
							// Define the url to send the image data to
							var url = 'UploadHandler-user-images.php';

							// bound jQuery File Upload to upload event photos input button
							$('#inputEventPhotos').fileupload({
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
									var event_photo_ids = $('#inputEventPhotoIds').val();

									// Show uploaded event photo files
									$.each(data.result.user_images, function (index, file) {
										if (file.url) {
											event_photo_ids += (event_photo_ids === '' ? file.asset_id : ',' + file.asset_id);

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
															 type: "event-shot",
															 assetId: file.asset_id
															};

											eventShotContainData['shots'].unshift(newShotObject);
										} // if (file.url)
									}); // $.each(data.result.user_images, function (index, file)

									// set a default featured image / album cover if there isn't one already
									if (eventShotContainData['shots'].length === 1 && $('#inputEventFeaturedImageId').val() === '') {
										eventShotContainData['shots'][0].featureShot = 'yes';

										$('#inputEventFeaturedImageId').val(eventShotContainData['shots'][0].assetId);
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									// set a default call-to-action background image if there isn't one already
									if (eventShotContainData['shots'].length > 1 && $('#inputEventCTABackgroundImageId').val() === '') {
										eventShotContainData['shots'][1].ctaBackgroundImage = 'yes';

										$('#inputEventCTABackgroundImageId').val(eventShotContainData['shots'][1].assetId);
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									updateShotContain(eventShotContainData); // refresh list on screen

									// disable preview video timeline button if this is a different list of new photo ids
									if (event_photo_ids !== $('#inputEventPhotoIds').val()) {
										$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
									}

									// update the new photo ids into the hidden input field
									$('#inputEventPhotoIds').val(event_photo_ids);

									// hides progress bar after it's done
									$('#upload-progress-all, #upload-progress').addClass('hidden');
								}
							});
							//***** jQuery File Update for Event Photos - End
						});
					</script>

					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-2 col-md-offset-10">
								<input type="hidden" name="event_id" id="inputEventId"<?php echo (isset($_POST['event_id']) ? ' value="' . $_POST['event_id'] . '"' : ''); ?> />
								<input type="hidden" name="upload_shots_only" id="inputUploadShotsOnly"<?php echo (isset($_GET['upload_shots_only']) ? ' value="' . $_GET['upload_shots_only'] . '"' : ''); ?> />
								<button type="submit" class="lead btn btn-primary pull-right" name="save_event" id="btn-save"><?php echo ($page_mode == 'edit' || (isset($_POST['event_id']) && ($_POST['event_id'] != '')) ? 'Save Changes' : 'Create Event'); ?></button>
							</div>
						</div>
					</nav>

					<?php if (($page_mode == 'edit' || (isset($_POST['event_id']) && ($_POST['event_id'] != ''))) && count($event_photos) >= $minimum_activity_photos_for_event_vt) : ?>
					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-3 col-md-offset-9">
								<a  class="lead btn btn-default pull-right" id="btn-preview-activity-tv" href="create-edit-event-vt.php?event_id=<?php echo $_POST['event_id'] ?>">Preview my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie</a>
							</div>
						</div>
					</nav>
					<?php endif; ?>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->

		<?php output_footer(); ?>