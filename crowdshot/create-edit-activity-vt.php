<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$page_mode               = 'create';
$vt                      = FALSE;
$vt_properties           = '';
$vt_album_cover          = FALSE;
$vt_title_01             = '';
$vt_title_02             = '';
$vt_cta_background_image = FALSE;
$vt_cta_name             = '';
$vt_cta_date_time        = '';
$vt_cta_location         = '';
$vt_cta_url              = '';
$vt_cta_url_label        = '';
$vt_cta_logo_text_before = '';
$vt_cta_logo_text_after  = '';
$vt_music                = FALSE;
$vt_creator              = FALSE;
$activity                = FALSE;
$activity_photos         = array();
$event                   = FALSE;
$event_logo              = FALSE;
$event_photos            = array();
$potential_music         = array();

$number_of_photos_required_for_vt   = 8;
$number_of_random_activity_photos   = 4;
$number_of_random_event_photos      = 4;
$number_of_potential_music          = 3;

$no_error        = TRUE;
$success_message = '';
$warning_message = '';
$error_message   = '';


/**
 * This function gets more activity photos due to the lack of event photos
 *		It will also fill missing activity video timeline's albumn cover and missing activity video timeline's call-to-action's background photo
 * 
 * @global assetObject $vt_album_cover
 * @global assetObject $vt_cta_background_image
 * @global activityObject $activity
 * @global arrayOfAssetObjects $activity_photos
 * @global eventObject $event
 * @global arrayOfAssetObjects $event_photos
 * @global int $number_of_photos_required_for_vt
 * @global int $number_of_random_activity_photos
 * @global boolean $no_error
 * @global string $warning_message
 * @global string $error_message
 */
function get_additional_activity_photos_for_event_photos() {
	global $vt_album_cover, $vt_cta_background_image;
	global $activity, $activity_photos;
	global $event, $event_photos;
	global $number_of_photos_required_for_vt, $number_of_random_activity_photos;
	global $no_error, $warning_message, $error_message;
	
	$additional_activity_photos_for_event_photos = array();

	$additional_activity_photos_for_event_photos = get_assets('', 'user_image', 'published', 'activity', $activity['id'], '', '', 'RAND', $number_of_photos_required_for_vt - count($activity_photos) - count($event_photos) + ($vt_album_cover ? 0 : 1) + ($vt_cta_background_image ? 0 : 1));

	if (empty($additional_activity_photos_for_event_photos)) {
		// not able to get any activity photos and because there isn't enough event photos, it is not possible to create an activity video timeline
		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'The is not enough photos between you and the organizer to create the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos.';
	} else {
		if (count($additional_activity_photos_for_event_photos) == ($number_of_photos_required_for_vt - count($activity_photos) - count($event_photos) + ($vt_album_cover ? 0 : 1) + ($vt_cta_background_image ? 0 : 1))) {
			// if there isn't an activity video timeline album cover already, take it from the random additional activity photo
			if (!$vt_album_cover) {
				$warning_message .= ($warning_message ? '<br />' : '') . 'You have not set an album cover. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to set an album cover or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
				$vt_album_cover  =  array_shift($additional_activity_photos_for_event_photos);
			} // if (!$vt_album_cover)

			// if there isn't an activity's call-to-action's background photo, take it from the random activity photos
			if (!$vt_cta_background_image) {
				$warning_message         .= ($warning_message ? '<br />' : '') . 'You have not selected call-to-action photo. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to select a call-to-action photo or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
				$vt_cta_background_image =  array_shift($additional_activity_photos_for_event_photos);
			} // if (!$vt_cta_background_image)

			if (count($activity_photos) < $number_of_random_activity_photos) {
				$i = ($number_of_random_activity_photos - count($activity_photos));

				for ($j = 1; $j <= $i; $j++) {
					$activity_photos[] = array_shift($additional_activity_photos_for_event_photos);
				} // for ($j = 1; $j <= $i; $j++)
			} // if (count($activity_photos) < $number_of_random_activity_photos)

			$warning_message .= ($warning_message ? '<br />' : '') . 'The organizer has not uploaded enough photos. More of your photos have been used instead. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
			$event_photos    =  array_merge($event_photos, $additional_activity_photos_for_event_photos);
		} else { // if it falls into here, there isn't just enough photos to create an activity video timeline
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'The is not enough photos between you and the organizer to create the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos.';
		} // if (count($additional_activity_photos_for_event_photos) == ($number_of_photos_required_for_vt - count($activity_photos) - count($event_photos) + ($vt_album_cover ? 0 : 1) + ($vt_cta_background_image ? 0 : 1))) else
	} // if (empty($additional_activity_photos_for_event_photos)) else
} // function get_additional_activity_photos_for_event_photos


/**
 * This function retrieves information on the activity and its associated event
 *		It also builds the list of required photos from the event and from the activity
 * 
 * @global boolean $vt
 * @global type $vt_album_cover
 * @global type $vt_title_01
 * @global type $vt_title_02
 * @global type $vt_cta_background_image
 * @global type $vt_cta_name
 * @global type $vt_cta_date_time
 * @global type $vt_cta_location
 * @global type $vt_cta_url
 * @global string $vt_cta_url_label
 * @global type $vt_cta_logo_text_before
 * @global type $vt_cta_logo_text_after
 * @global type $activity
 * @global type $activity_photos
 * @global type $event
 * @global type $event_logo
 * @global type $event_photos
 * @global int $number_of_photos_required_for_vt
 * @global type $number_of_random_activity_photos
 * @global type $number_of_random_event_photos
 * @global type $no_error
 * @global type $warning_message
 * @global type $error_message
 */
function get_activity_and_event_info_for_new_vt_based_on_GET_activity_id() {
	global $vt, $vt_album_cover, $vt_title_01, $vt_title_02, $vt_cta_background_image, $vt_cta_name, $vt_cta_date_time, $vt_cta_location, $vt_cta_url, $vt_cta_url_label, $vt_cta_logo_text_before, $vt_cta_logo_text_after;
	global $activity, $activity_photos;
	global $event, $event_logo, $event_photos;
	global $number_of_photos_required_for_vt, $number_of_random_activity_photos, $number_of_random_event_photos;
	global $no_error, $warning_message, $error_message;

	$vt = FALSE;

	$additional_activity_photos_for_event_photos = array();

	unset($_GET['vt_id']);
	unset($_POST['vt_id']);

	// get the activity
	$activity = get_activity_details($_GET['activity_id']);

	if ($activity) { // the activity id is invalid; therefore, an activity video timeline should not be created - just go back to the home page
		// get the event assocated with the activity
		$event = get_event_details($activity['event_id']);

		if ($event) { // the event associated with activity is invalid; therefore, an activity video timeline should not be created - just go back to the home page
			// get activity featured image - this will be used for the activity video timeline album cover
			$vt_album_cover = get_asset($activity['activity_featured_image_id']);

			if ($vt_album_cover) {
			} else {
				// because there isn't an activity featured image, let's get the event featured image for the activity video timeline album cover
				$vt_album_cover = get_asset($event['event_featured_image_id']);

				if ($vt_album_cover) {
				} else {
					// because there isn't an activity featured image and there isn't an event featured image, let's get one more random activity photo for the activity video timeline album cover
					++$number_of_random_activity_photos;
				} // if ($vt_album_cover) else
			} // if ($vt_album_cover) else

			// get activity's call-to-action's background photo
			$vt_cta_background_image = get_asset($activity['activity_cta_background_image_id']);

			if ($vt_cta_background_image) {
			} else {
				$vt_cta_background_image = get_asset($event['event_cta_background_image_id']);

				if ($vt_cta_background_image) {
				} else {
					// because there isn't an activity's call-to-action's background photo, let's get one more random activity photo for the activity's call-to-action's background photo
					++$number_of_random_activity_photos;
				} // if ($vt_cta_background_image) else
			} // if ($vt_cta_background_image) else

			// get activity photos to fill in activity video timeline
			$activity_photos = get_assets('', 'user_image', 'published', 'activity', $activity['id'], '', '', 'RAND', $number_of_random_activity_photos);

			if (empty($activity_photos)) {
				// because there isn't any activity photos, let's set the number of random event photos to the number of photos required by the activity video timeline - have to consider if there is one for the activity video timeline album cover and call-to-action background image
		/***** there is a problem with this - is this really necessary *****/ //		$number_of_random_activity_photos = $number_of_random_activity_photos - ($vt_album_cover ? 0 : 1) - ($vt_cta_background_image ? 0 : 1);;
				$number_of_random_activity_photos = $number_of_photos_required_for_vt - $number_of_random_event_photos;
				$number_of_random_event_photos    = $number_of_photos_required_for_vt + ($vt_album_cover ? 0 : 1) + ($vt_cta_background_image ? 0 : 1);
			} else {
				if (count($activity_photos) == $number_of_random_activity_photos) {
					// if there isn't an activity video timeline album cover already, take it from the random activity photos
					if (!$vt_album_cover) {
						$warning_message .= ($warning_message ? '<br />' : '') . 'You have not set an album cover. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to set an album cover or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
						$vt_album_cover  =  array_shift($activity_photos);

						--$number_of_random_activity_photos;
					} // if (!$vt_album_cover)

					// if there isn't an activity's call-to-action's background photo, take it from the random activity photos
					if (!$vt_cta_background_image) {
						$warning_message         .= ($warning_message ? '<br />' : '') . 'You have not selected call-to-action photo. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to select a call-to-action photo or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
						$vt_cta_background_image =  array_shift($activity_photos);

						--$number_of_random_activity_photos;
					} // if (!$vt_cta_background_image)
				} else {
					// since there isn't enough activity photos, let's increase the number of random event photos to include the shortfall - have to consider if there is one for the activity video timeline album cover and call-to-action background image
		/***** there is a problem with this - is this really necessary *****/ //			$number_of_random_activity_photos = $number_of_random_activity_photos - ($vt_album_cover ? 0 : 1) - ($vt_cta_background_image ? 0 : 1);
					$number_of_random_activity_photos = $number_of_photos_required_for_vt - $number_of_random_event_photos;
					$number_of_random_event_photos    = $number_of_photos_required_for_vt - count($activity_photos) + ($vt_album_cover ? 0 : 1) + ($vt_cta_background_image ? 0 : 1);
				} // if (count($activity_photos) == $number_of_random_activity_photos)
			} // if (empty($activity_photos)) else

			// get event photos to fill in activity video timeline
			$event_photos = get_assets('', 'user_image', 'published', 'event', $event['id'], '', '', 'RAND', $number_of_random_event_photos);

			if (empty($event_photos)) {
				// if it falls into there, there isn't any event photos; therefore, will attempt to get remaining required photos from the activity - potential duplicates
				if (empty($activity_photos)) {
					// if it falls into here, it wasn't successful in getting event photos and it wasn't successfull previously in getting activity photos; therefore, not going to try to get more photos from activity
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'The organizer has not uploaded any photos and you do not have enough photos. Please ask the organizer to upload his/her photos or click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos.';
				} else {
					// meaning it was successfull in getting activity photos earlier
					get_additional_activity_photos_for_event_photos();
				} // if (empty($activity_photos)) else
			} else {
				if (count($event_photos) == $number_of_random_event_photos) {
					// if there isn't an activity video timeline album cover already, take it from the random event photos
					if (!$vt_album_cover) {
						$warning_message .= ($warning_message ? '<br />' : '') . 'You have not set an album cover. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to set an album cover or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
						$vt_album_cover  =  array_shift($event_photos);

						--$number_of_random_event_photos;
					} // if (!$vt_album_cover)

					// if there isn't an activity's call-to-action's background photo, take it from the random activity photos
					if (!$vt_cta_background_image) {
						$warning_message         .= ($warning_message ? '<br />' : '') . 'You have not selected call-to-action photo. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to select a call-to-action photo or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
						$vt_cta_background_image =  array_shift($event_photos);

						--$number_of_random_event_photos;
					} // if (!$vt_cta_background_image)

					// if there isn't enough activity photos, take it from the random event photos
					if (count($activity_photos) < $number_of_random_activity_photos) {
						$warning_message .= ($warning_message ? '<br />' : '') . 'You have not uploaded enough photos. More organizer\'s photos were used instead. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos to the activity or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';

						$i = ($number_of_random_activity_photos - count($activity_photos));

						for ($j = 1; $j <= $i; $j++) {
							$activity_photos[] = array_shift($event_photos);

							--$number_of_random_event_photos;
						} // for ($j = 1; $j <= $i; $j++)
					} // if (count($activity_photos) < $number_of_random_activity_photos)
				} else { // if it falls into here, there isn't enough event photos; therefore, will attempt to get more photos from the activity
					if (empty($activity_photos)) { // meaning it was successfull in getting activity photos earlier
						// if it falls into here, there isn't enough event photos and there are not activity photos; therefore it is not possible to create an activity video timeline
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . 'The is not enough photos between you and the organizer to create the movie. Click <a href="create-edit-activity.php?activity_id=' . $activity['id'] . '">here</a> to upload more photos.';
					} else {
						get_additional_activity_photos_for_event_photos();
					} // if (empty($activity_photos)) else
				} // if (count($activity_photos) == $number_of_random_event_photos) else
			} // if (empty($event_photos))

			unset($additional_activity_photos_for_event_photos);

			// get event's logo
			$event_logo = get_asset($event['event_logo_id']);

			if ($event_logo) {
			} else {
				// *** future decision *** is it okay to create an activity video timeline without an event logo?
			} // if ($event_logo) else

			// get titles
			$vt_title_01 = $event['event_name'];
			$vt_title_02 = $activity['activity_name'];

			// get call-to-action information
			$vt_cta_name             = ($activity['activity_name'] ? $activity['activity_name'] : $event['event_name']);
			$vt_cta_date_time        = ($activity['activity_start_date'] ? $activity['activity_start_date'] . ($activity['activity_start_date'] != $activity['activity_end_date'] ? ' to ' . $activity['activity_end_date'] : '') : $event['event_start_date'] . ($event['event_start_date'] != $event['event_end_date'] ? ' to ' . $event['event_end_date'] : ''));
			$vt_cta_location         = ($activity['activity_location'] ? $activity['activity_location'] : $event['event_location']);
			$vt_cta_url              = ($activity['activity_cta_url'] ? $activity['activity_cta_url'] : $event['event_cta_url']);
			$vt_cta_url_label        = '';
			$vt_cta_logo_text_before =  'Or Pledge Here.';
			$vt_cta_logo_text_after  =  'Thank You!';
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Cannot create a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because the event of the selected activity is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		} // if ($event)
	} else {
		unset($_GET['activity_id']);
		unset($_POST['activity_id']);

		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Cannot create a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because the selected activity is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
	} // if ($activity)
} //function get_activity_and_event_info_for_new_vt_based_on_GET_activity_id



// *** Start building the page

// see if current user is in a cookie - if so, get it; otherwise, get user info
$current_user = get_SESSION_current_user(sprintf('http%1$s://%2$s%3$s%4$s/create-edit-activity-vt.php?%5$s_id=%6$u', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER["SERVER_NAME"], ($_SERVER["SERVER_PORT"] != '80' ? ':' . $_SERVER["SERVER_PORT"] : ''), dirname($_SERVER['PHP_SELF']), (isset($_GET['vt_id']) || isset($_POST['vt_id']) ? 'vt' : (isset($_GET['activity_id']) || isset($_POST['activity_id']) ? 'activity' : '')), (isset($_GET['vt_id']) ? $_GET['vt_id'] : (isset($_POST['vt_id']) ? $_POST['vt_id'] : (isset($_GET['activity_id']) ? $_GET['activity_id'] : (isset($_POST['activity_id']) ? $_POST['activity_id'] : ''))))));

// check if the create / save button was clicked - if so, create / save the data
if (isset($_POST['save_vt'])) {
	if (isset($_POST['vt_id']) && isset($_POST['vt_status']) && $_POST['vt_status'] &&
		isset($_POST['vt_cover_image_id']) && $_POST['vt_cover_image_id'] &&
		isset($_POST['vt-block-02-content-01-text']) && $_POST['vt-block-02-content-01-text'] && isset($_POST['vt-block-11-content-01-text']) && $_POST['vt-block-11-content-01-text'] &&
		isset($_POST['vt-block-12-content-02-cta-name']) && $_POST['vt-block-12-content-02-cta-name'] && isset($_POST['vt-block-12-content-03-cta-date-time']) && $_POST['vt-block-12-content-03-cta-date-time'] && isset($_POST['vt-block-12-content-04-cta-location']) && $_POST['vt-block-12-content-04-cta-location'] && isset($_POST['vt-block-12-content-05-cta-url']) && $_POST['vt-block-12-content-05-cta-url'] && $_POST['vt-block-12-content-06-cta-url-label'] && isset($_POST['vt-block-12-content-07-cta-logo-text-before']) && isset($_POST['vt-block-12-content-08-cta-logo-text-after']) &&
		isset($_POST['vt-music']) && $_POST['vt-music'] &&
		isset($_POST['vt_properties']) && $_POST['vt_properties'] &&
		isset($_POST['vt_creator_id']) && $_POST['vt_creator_id'] &&
		isset($_POST['activity_id']) && $_POST['activity_id'] &&
		isset($_POST['event_id']) && $_POST['event_id'] &&
		isset($_POST['event_logo_id']) && $_POST['event_logo_id'] &&
		isset($_POST['event_narrative_sentence_01']) && $_POST['event_narrative_sentence_01'] && isset($_POST['event_narrative_sentence_02']) && isset($_POST['event_narrative_sentence_03']) && isset($_POST['event_narrative_sentence_04'])) {
		if ($_POST['vt-block-12-content-06-cta-url-label'] || $_POST['vt-block-12-content-07-cta-logo-text-before'] || $_POST['vt-block-12-content-08-cta-logo-text-after']) {
			// decode activity video timeline properties into associative array
			$vt_properties_associative_array = json_decode($_POST['vt_properties'], TRUE);

			if (array_key_exists('album_cover', $vt_properties_associative_array) &&
				array_key_exists('timeline', $vt_properties_associative_array) &&
				array_key_exists('music', $vt_properties_associative_array)) {
				// update album cover title
				$vt_properties_associative_array['album_cover']['title'] = $_POST['vt-block-12-content-02-cta-name'];

				// update activity video timeline properties with $_POST fields
				$vt_properties_associative_array['timeline'][2]['properties']['content'][0]['text']  = $_POST['vt-block-02-content-01-text'];
				$vt_properties_associative_array['timeline'][20]['properties']['content'][0]['text'] = $_POST['vt-block-11-content-01-text'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][1]['text'] = $_POST['vt-block-12-content-02-cta-name'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][2]['text'] = $_POST['vt-block-12-content-03-cta-date-time'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][3]['text'] = $_POST['vt-block-12-content-04-cta-location'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][4]['text'] = $_POST['vt-block-12-content-05-cta-url'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][5]['text'] = $_POST['vt-block-12-content-06-cta-url-label'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][6]['text'] = $_POST['vt-block-12-content-07-cta-logo-text-before'];
				$vt_properties_associative_array['timeline'][22]['properties']['content'][7]['text'] = $_POST['vt-block-12-content-08-cta-logo-text-after'];

				// update music selection
				$vt_properties_associative_array['music']['asset_id'] = $_POST['vt-music'];
			} else {
				$vt_properties =  '';
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Ops! Something is wrong with your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-edit-activity-vt.php?' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? 'vt' : 'activity') . '_id=' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? $_POST['vt_id'] : $_POST['activity_id']) . '">here</a> to try again.';
			} // if (array_key_exists('album_cover', $vt_properties_associative_array) && array_key_exists('timeline', $vt_properties_associative_array) && array_key_exists('music', $vt_properties_associative_array)) else
		} else {
			$no_error      = FALSE;
			$error_message = 'You must provide either call-to-action link label, text before logo button, or text after logo button. Click <a href="create-edit-activity-vt.php?' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? 'vt' : 'activity') . '_id=' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? $_POST['vt_id'] : $_POST['activity_id']) . '">here</a> to try again.';
		} // if ($_POST['vt-block-12-content-06-cta-url-label'] || $_POST['vt-block-12-content-07-cta-logo-text-before'] || $_POST['vt-block-12-content-08-cta-logo-text-after']) else
	} else {
		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Required information is missing. Please review your movie timeline.';
	} // end of ensuring required $_POST variables are set

	if ($no_error) { // if no error, create / update activity video timeline
		if ($_POST['vt_id'] == '') { // there isn't an activity video timeline id passed - let's create a new one
			$vt = create_generated_video(json_encode($vt_properties_associative_array), $_POST['activity_id'], $_POST['event_id'], $_POST['vt_creator_id']);

			if ($vt) {
				$_POST['vt_id'] = $vt['id'];
				$page_mode               = 'edit';
				$success_message         = 'Your movie timeline has been created and your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie is being generated.';
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error creating your movie timeline.';
			} // if (!$vt) else
		} else { // there is an activity video timeline id passed, let's update it
			if ($_POST['vt_status'] == 'draft') {
				$vt = edit_generated_video($_POST['vt_id'], json_encode($vt_properties_associative_array), $_POST['vt_creator_id']);

				if ($vt) {
					$_POST['vt_id']  = $vt['id'];
					$page_mode       = 'edit';
					$success_message = 'Your movie timeline has been updated and your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie is being generated.';
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error updating your movie timeline.';
				} // if (!$vt) else
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the movie timeline that has already been used to generate a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-edit-activity-vt.php?activity_id=' . $_POST['activity_id'] . '">here</a> to create a new movie timeline.';
			} // if ($_POST['vt_status'] == 'draft' ) else
		} // if ($_POST['vt_id'] == '') else
	} // if ($no_error)

	if ($no_error) { // if there no error, send activity video timeline to video engine
		//	frist, add event's narratives to the activity video timeline
		$video_engine_ready_vt_properties = insert_event_narratives_to_vt_properties($vt['asset_properties'], $_POST['event_logo_id'], $_POST['event_narrative_sentence_01'], $_POST['event_narrative_sentence_02'], $_POST['event_narrative_sentence_03'], $_POST['event_narrative_sentence_04']);

		// call video engine - need to create the necessary $_POST variables adn then calling the video engine form - ideally, after calling the video engine the user is returned here with a message or to a new page with a message the video is being generated
		call_video_engine($vt['id'], $video_engine_ready_vt_properties, $current_user['id'], $current_user['user_email'], $_POST['vt-block-12-content-02-cta-name']);
	} // if ($no_error)

	unset($vt_properties_associative_array);
} else {
	if (isset($_GET['vt_id'])) {
		// get activity video timeline
		$vt = get_generated_videos($_GET['vt_id']);

		if (!empty($vt)) {
			$page_mode = 'edit';

			$vt = $vt[0]; // because get_generated_videos returns an array of generated videos and because an unique asset id ($_GET['vt_id']) was passed in, there should only be one item in the array

			if ($vt['asset_status'] == 'draft') {
				$activity = $vt['activity'];

				if ($activity) { // the activity id is invalid; therefore, an activity video timeline should not be created - just go back to the home page
					// get the event assocated with the activity
					$event = $vt['event'];

					if ($event) { // the event associated with activity is invalid; therefore, an activity video timeline should not be created - just go back to the home page
						// get event logo
						$event_logo = get_asset($event['event_logo_id']);

						// set activity video timeline properties
						$vt_properties = $vt['asset_properties'];

						// decode activity video timeline properties into associative array
						$vt_properties_associative_array = json_decode($vt_properties, TRUE);

						// get activity video timeline's alumn cover from activity video timeline properties
						$vt_album_cover = get_asset($vt_properties_associative_array['album_cover']['asset_id']);

						// get first title
						$vt_title_01 = $vt_properties_associative_array['timeline'][2]['properties']['content'][0]['text'];

						// get event photos from activity video timeline properties
						$event_photos   = array();
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][4]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][6]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][12]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][14]['properties']['content'][0]['asset_id']);

						// get activity photos from activity video timeline properties
						$activity_photos   = array();
						$activity_photos[] = get_asset($vt_properties_associative_array['timeline'][8]['properties']['content'][0]['asset_id']);
						$activity_photos[] = get_asset($vt_properties_associative_array['timeline'][10]['properties']['content'][0]['asset_id']);
						$activity_photos[] = get_asset($vt_properties_associative_array['timeline'][16]['properties']['content'][0]['asset_id']);
						$activity_photos[] = get_asset($vt_properties_associative_array['timeline'][18]['properties']['content'][0]['asset_id']);

						// get second title
						$vt_title_02 = $vt_properties_associative_array['timeline'][20]['properties']['content'][0]['text'];

						// get activity video timeline's call-to-action background photo from activity video timeline properties
						$vt_cta_background_image = get_asset($vt_properties_associative_array['timeline'][22]['properties']['background']['asset_id']);
						$vt_cta_name             = $vt_properties_associative_array['timeline'][22]['properties']['content'][1]['text'];
						$vt_cta_date_time        = $vt_properties_associative_array['timeline'][22]['properties']['content'][2]['text'];
						$vt_cta_location         = $vt_properties_associative_array['timeline'][22]['properties']['content'][3]['text'];
						$vt_cta_url              = $vt_properties_associative_array['timeline'][22]['properties']['content'][4]['text'];
						$vt_cta_url_label        = (array_key_exists(5, $vt_properties_associative_array['timeline'][22]['properties']['content']) ? $vt_properties_associative_array['timeline'][22]['properties']['content'][5]['text'] : '');
						$vt_cta_logo_text_before = (array_key_exists(6, $vt_properties_associative_array['timeline'][22]['properties']['content']) ? $vt_properties_associative_array['timeline'][22]['properties']['content'][6]['text'] : 'Or Pledge Here.');
						$vt_cta_logo_text_after  = (array_key_exists(7, $vt_properties_associative_array['timeline'][22]['properties']['content']) ? $vt_properties_associative_array['timeline'][22]['properties']['content'][7]['text'] : 'Thank You!');

						// get music from activity video timeline properties
						$vt_music = get_asset($vt_properties_associative_array['music']['asset_id']);

						unset($vt_properties_associative_array);

						$_POST['vt_id'] = $_GET['vt_id'];
					} else {
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because the activity it is connected to has an invalid event. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
					} // if ($event)
				} else {
					unset($_GET['activity_id']);
					unset($_POST['activity_id']);

					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because it is connected to an invalid activity. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
				} // if ($activity)
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the timeline that has already been used to generate a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if ($vt['asset_status'] == 'draft' ) else
		} else { // if it falls here, the passed activity video timeline id is not valid
			// see if there is an activity id passed into the page
			if (isset($_GET['activity_id']) && $_GET['activity_id']) {
				$GET_vt_id = $_GET['vt_id']; // save it for warning messgae

				// try to create an activity video timeline based on the input activity id
				get_activity_and_event_info_for_new_vt_based_on_GET_activity_id();

				$warning_message .= ($warning_message ? '<br />' : '') . 'The selected activity video timeline (' . $GET_vt_id . ') does not exist. A new activity video timeline will be created based on the supplied activity (' . $_GET['activity_id'] . ').';

				unset($GET_vt_id);
			} else { // if it falls into here, no activity vt id and no activity id were passed; therefore, it has to go back to the home page (can't create activity vedio timeline because no activity id can be associated
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because it is not valid and cannot create a new <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because no activity was selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if (isset($_GET['activity_id'])) else
		} // if ($vt) else
	} else { // if it falls into here, no activity vt id was passed
		if (isset($_GET['activity_id']) && $_GET['activity_id']) {
			// try to create an activity video timeline based on the input activity id
			get_activity_and_event_info_for_new_vt_based_on_GET_activity_id();
		} else { // if it falls into here, no activity vt id and no activity id were passed; therefore, it has to go back to the home page (can't create activity vedio timeline because no activity id can be associated
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Cannot create a new <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because no activity was selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		} // if (isset($_GET['activity_id'])) else
	} // if (isset($_GET['vt_id'])) else

	// if it reaches here, there are enough photos to create an activity video timeline - let's get more movie's elements

	// get potential music
	$potential_music = get_assets('', 'vt_music', 'published', '', '', '', '', 'RAND', $number_of_potential_music);

	if (empty($potential_music)) {
		if ($vt_music) {
			// if there is a music attached to the activity video timeline already, prepend the attached music to the potential music list
			$potential_music[] = $vt_music;
		} else {
			// *** future decision *** is it okay to create an activity video timeline wihtout a song?
		} // if ($vt_music) else
	} else {
		if ($vt_music) {
			// if there is a music attached to the activity video timeline already, remove one from the potential music list and prepend the attached music to the potential music list
			$vt_music_in_potential_music = FALSE;
			$temporary_potential_music   = array();

			foreach ($potential_music as $potential_song) {
				if ($potential_song['id'] == $vt_music['id']) {
					$vt_music_in_potential_music = TRUE;
				} else {
					$temporary_potential_music[] = $potential_song;
				} // if ($potential_song['id'] == $vt_music['id'])
			} // foreach ($potential_music as $potential_song)
			
			if (!$vt_music_in_potential_music) {
				$throw_away = array_shift($temporary_potential_music);
			} // if (!$vt_music_in_potential_music)

			array_unshift($temporary_potential_music, $vt_music);

			$potential_music = $temporary_potential_music;

			unset($temporary_potential_music);
		} else {
		} // if ($vt_music) else
	} // if (empty($potential_music)) else

	// set actvity video timeline creator if it is not already set
	if (!$vt_creator) {
		if ($current_user) {
			$vt_creator = $current_user;
		} else { // if it falls into here, current user is not set - which mean the user got here directly and without providing user information via create / edit event page or create / edit activity page; therefore, let's use the activity creator's id as current user and activity video timeline creator
			$vt_creator = get_user($activity['created_by']);

			if ($vt_creator) {
				$_SESSION['current_user'] = $current_user = $vt_creator;
			} else { // if it falls into here, the user associated with the activity is not valid, let's use event's creator's id
				$vt_creator = get_user($event['created_by']);

				if ($vt_creator) {
					$_SESSION['current_user'] = $current_user = $vt_creator;
				} else { // if it falls into here, the user assoicated with the event is not valid and since the activity's creator's id is also invalid, let's use the default system id 1
					$vt_creator   = get_user(1);
					$current_user = false; // make sure current user is not set because the default system id 1 should not be used

					unset($_SESSION['current_user']); // make sure current user is not set because the default system id 1 should not be used
				} // if ($vt_creator) else
			} // if ($vt_creator) else
		} // if ($current_user) else
	} // if (!$vt_creator)

	// build activity video timeline properties
	if ($no_error && $page_mode == 'create') {
		if ($vt_album_cover && $activity && $event && $event_logo && (count($activity_photos) + count($event_photos) == $number_of_photos_required_for_vt) && $vt_cta_background_image && !empty($potential_music)) {
			$vt_properties_associative_array = array();

			$vt_properties_associative_array['album_cover'] = array('title' => $vt_cta_name, 'asset_id' => $vt_album_cover['id']);

			$vt_properties_associative_array['timeline'][] = array('sequence' => 1,  'type' => 'block',      'is_locked' => TRUE,  'properties' => array('type' => 'album_cover',                                                                                                                          'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $vt_album_cover['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 2,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'star_light'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 3,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'title',                   'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                'content' => array(array('type' => 'text',  'is_locked' => FALSE, 'text'     => $vt_title_01, 'color' => 'rgba(255, 255, 255, 1)'))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 4,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'light_burst_fade'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 5,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[0]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 6,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'down_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 7,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[1]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 8,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 9,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $activity_photos[0]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 10, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 11, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $activity_photos[1]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 12, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 13, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[2]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 14, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 15, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[3]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 16, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 17, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $activity_photos[2]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 18, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 19, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $activity_photos[3]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 20, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 21, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'title',                   'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                'content' => array(array('type' => 'text',  'is_locked' => FALSE, 'text'     => $vt_title_02, 'color' => 'rgba(255, 255, 255, 1)'))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 22, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'page_flip'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 23, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'branded_call_to_action',  'background' => array('type' => 'image', 'is_locked' => TRUE, 'asset_id' => $vt_cta_background_image['id']), 'content' => array(array('type' => 'logo',  'is_locked' => TRUE,  'asset_id' => $event_logo['id']),                                    array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_name, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_date_time, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_location, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_url, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_url_label, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_logo_text_before, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_logo_text_after, 'color' => 'rgba(255, 255, 255, 1)'))));

			$vt_properties_associative_array['music'] = array('asset_id' => $potential_music[0]['id']);

			$vt_properties = json_encode($vt_properties_associative_array, JSON_NUMERIC_CHECK);

			unset($vt_properties_associative_array);
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'There was an error building your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="activity.php?activity_id=' . $activity['id'] . '">here</a> to return to the activity\'s (' . $activity['activity_name'] . ') page.';
		} // if ($vt_album_cover && $activity && !empty($activity_photos) && $event && $event_logo && !empty($event_photos) && (count($activity_photos) + count($event_photos) == $number_of_photos_required_for_vt) && $vt_cta_background_image && !empty($potential_music))
	} // if ($no_error && $page_mode == 'create')
} // if (isset($_POST['save_vt'])) else
?>
		<?php output_header('create-edit-activity-vt-page', (($page_mode == 'edit') || (isset($_POST['vt_id']) && ($_POST['vt_id'] != '')) ? 'Edit' : 'Create') . ' Activity Video Timeline | CrowdShot', FALSE, TRUE, FALSE, FALSE, FALSE, FALSE, FALSE); ?>

		<!-- Display messages section -->
		<section id="create-edit-messages">
			<div class="container">
				<div class="row">
					<div class="col-md-12" id="messages">
						<?php echo ($error_message ? '<div class="alert alert-danger"><p>' . $error_message . '</p></div>' : ''); ?>
						<?php echo ($warning_message ? '<div class="alert alert-warning"><p>' . $warning_message . '</p></div>' : ''); ?>
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

		<?php if ($vt_properties) : ?>
		<!-- Main create activity video timeline page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>Let's preview what you've done so far on your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. You will be able to adjust the titles, call-to-action information, and your background music. Click <a href="upload-photos-to-activity.php?activity_id=<?php echo $activity['id']; ?>">here</a> to upload more photos.</p>
					</div>
				</div>
			</div><!-- .container -->
		</section><!-- #create-edit-introduction -->

		<!-- Main create / edit activity vt form -->
		<section id="create-edit-form">
			<div class="container">
				<form class="form-horizontal" role="form" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> Movie Timeline</h2>
						</div>
					</div>
					<div class="row form-section-content" id="vt-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<ul class="list-unstyled" id="vt-wrapper">
									<li id="vt-block-01" class="text-center vt-block-container vt-block-type-albumn-cover locked-vt-block-container">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-01-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $vt_album_cover['asset_url']; ?>">
												<img id="vt-block-01-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($vt_album_cover['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $vt_album_cover['id']; ?>" />
											</div><!-- #vt-block-01-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-01 -->
									<li id="vt-block-02" class="text-center vt-block-container vt-block-type-title locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-02-content-02" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-02-content-01-text" class="form-control vt-block-text vt-block-text-white-on-black unlocked-content" id="vt-block-02-content-01-text" placeholder="Introduce your fundraising story and why it's important" maxlength="75" required value="<?php echo $vt_title_01; ?>" />
											</div><!-- #vt-block-02-content-02 -->
											<span class="vt-block-type-title-instructions">Click / tap text to edit.</span>
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-02 -->
									<li id="vt-block-03" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-03-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[0]['asset_url']; ?>">
												<img id="vt-block-03-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[0]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[0]['id']; ?>" />
											</div><!-- #vt-block-03-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-03 -->
									<li id="vt-block-04" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-04-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[1]['asset_url']; ?>">
												<img id="vt-block-04-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[1]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[1]['id']; ?>" />
											</div><!-- #vt-block-04-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-04 -->
									<li id="vt-block-05" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-05-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $activity_photos[0]['asset_url']; ?>">
												<img id="vt-block-05-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($activity_photos[0]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $activity_photos[0]['id']; ?>" />
											</div><!-- #vt-block-05-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-05 -->
									<li id="vt-block-06" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-06-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $activity_photos[1]['asset_url']; ?>">
												<img id="vt-block-06-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($activity_photos[1]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $activity_photos[1]['id']; ?>" />
											</div><!-- #vt-block-06-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-06 -->
									<li id="vt-block-07" class="ctext-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-07-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[2]['asset_url']; ?>">
												<img id="vt-block-07-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[2]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[2]['id']; ?>" />
											</div><!-- #vt-block-07-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-07 -->
									<li id="vt-block-08" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-08-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[3]['asset_url']; ?>">
												<img id="vt-block-08-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[3]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[3]['id']; ?>" />
											</div><!-- #vt-block-08-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-08 -->
									<li id="vt-block-09" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-09-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $activity_photos[2]['asset_url']; ?>">
												<img id="vt-block-09-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($activity_photos[2]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $activity_photos[2]['id']; ?>" />
											</div><!-- #vt-block-09-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-09 -->
									<li id="vt-block-10" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-10-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $activity_photos[3]['asset_url']; ?>">
												<img id="vt-block-10-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($activity_photos[3]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $activity_photos[3]['id']; ?>" />
											</div><!-- #vt-block-10-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-10 -->
									<li id="vt-block-11" class="text-center vt-block-container vt-block-type-title locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-11-content-01" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-11-content-01-text" class="form-control vt-block-text unlocked-content" id="vt-block-11-content-01-text" placeholder="Introduce your fundraising activity" maxlength="75" required value="<?php echo $vt_title_02; ?>" />
											</div><!-- #vt-block-11-content-01 -->
											<span class="vt-block-type-title-instructions">Click / tap text to edit.</span>
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-11 -->
									<li id="vt-block-12" class="text-center vt-block-container vt-block-type-branded-call-to-action locked-block">
										<div class="block-background-wrapper form-section-content-upload fancybox" href="<?php echo $vt_cta_background_image['asset_url']; ?>">
											<img id="vt-block-12-background-content-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($vt_cta_background_image['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $vt_cta_background_image['id']; ?>" />
										</div><!-- .block-background-content-wrapper -->
											<span class="vt-block-type-title-instructions">Click / tap text to edit.</span>
										<div class="vt-block-content-wrapper">
											<div id="vt-block-12-content-02" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-02-cta-name"      class="form-control vt-block-text unlocked-content" id="vt-block-12-content-02-cta-name"      placeholder="Activity name"          maxlength="50" required value="<?php echo $vt_cta_name; ?>" />
											</div><!-- #vt-block-12-content-02 -->
											<div id="vt-block-12-content-03" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-03-cta-date-time" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-03-cta-date-time" placeholder="Activity date and time" maxlength="50" required value="<?php echo $vt_cta_date_time; ?>" />
											</div><!-- #vt-block-12-content-03 -->
											<div id="vt-block-12-content-04" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-04-cta-location"  class="form-control vt-block-text unlocked-content" id="vt-block-12-content-04-cta-location"  placeholder="Activity location"      maxlength="50" required value="<?php echo $vt_cta_location; ?>" />
											</div><!-- #vt-block-12-content-04 -->
											<span class="vt-block-type-title-instructions">Call-to-action link information</span>
											<div id="vt-block-12-content-05" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="url" name="vt-block-12-content-05-cta-url" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-05-cta-url" placeholder="http://event.org/" maxlength="255" required value="<?php echo $vt_cta_url; ?>" />
											</div><!-- #vt-block-12-content-05 -->
											<div id="vt-block-12-content-06" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-12-content-06-cta-url-label" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-06-cta-url-label" placeholder="Or Pledge Here. Thank You!" maxlength="50" value="<?php echo $vt_cta_url_label; ?>" />
											</div><!-- #vt-block-12-content-06 -->
											<input type="hidden" name="vt-block-12-content-07-cta-logo-text-before" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-07-cta-logo-text-before" placeholder="Or Pledge Here." maxlength="50" value="<?php echo $vt_cta_logo_text_before; ?>" />
											<input type="hidden" name="vt-block-12-content-08-cta-logo-text-after" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-08-cta-logo-text-after" placeholder="Thank You!" maxlength="50" value="<?php echo $vt_cta_logo_text_after; ?>" />
<?php /*
											<span class="vt-block-type-title-instructions">Text before logo button</span>
											<div id="vt-block-12-content-07" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-12-content-07-cta-logo-text-before" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-07-cta-logo-text-before" placeholder="Or Pledge Here." maxlength="50" value="<?php echo $vt_cta_logo_text_before; ?>" />
											</div><!-- #vt-block-12-content-07 -->
											<span class="vt-block-type-title-instructions">Text after logo button</span>
											<div id="vt-block-12-content-08" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-12-content-08-cta-logo-text-after" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-08-cta-logo-text-after" placeholder="Thank You!" maxlength="50" value="<?php echo $vt_cta_logo_text_after; ?>" />
											</div><!-- #vt-block-12-content-08 -->
*/ ?>
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-12 -->
								</ul><!-- #timeline-sortable -->
								<script type="text/javascript">
									$(document).ready(function() {
										//***** Fancybox - Start
										// bond Fancyboc to elements
										$('.fancybox').fancybox({
											openEffect	: 'elastic',
											closeEffect	: 'elastic',
											padding :     0,

											helpers : {
												title : {
													type : 'inside'
												}
											}
										});
										//***** Fancybox - End
									})
								</script>
							</div><!-- .row -->
						</div>
					</div><!-- .row -->

					<?php if (($potential_music && !empty($potential_music)) || $vt_music) : ?>
					<div class="row form-section-header">
						<div class="col-md-12">
							<h2 class="create-edit-form-section-header">Your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> Movie Music</h2>
						</div>
					</div>
					<div class="row form-section-content">
						<div class="col-md-12">
							<div class="row form-group">
								<?php foreach ($potential_music as $potential_music_key => $potential_song) : ?>
								<div class="vt-music-option-wrapper">
									<input type="radio" name="vt-music" class="vt-music-radio-button" value="<?php echo $potential_song['id'] ?>"<?php echo (($vt_music && ($potential_song['id'] == $vt_music['id'])) || (!$vt_music && $potential_music_key == 0) ? ' checked' : ''); ?> />
									<span class="vt-music-title-and-artis">
										<?php
										$potential_song_properties = json_decode($potential_song['asset_properties'], TRUE);

										printf('%1$s by %2$s', ($potential_song_properties && is_array($potential_song_properties) && array_key_exists('title', $potential_song_properties) ? $potential_song_properties['title'] : 'No Title'), ($potential_song_properties && is_array($potential_song_properties) && array_key_exists('artist', $potential_song_properties) ? $potential_song_properties['artist'] : 'Unknown'));
										?>
									</span>
									<audio controls class="vt-music-player">
										<source src="<?php echo $potential_song['asset_url']; ?>" type="audio/mpeg" />
										<embed height="25" width="220" src="<?php echo $potential_song['asset_url']; ?>" />
									</audio> 
									<div class="clearfix"></div>
								</div><!-- . vt-music-option-wrapper -->
								<?php endforeach; // foreach ($potential_music as $potential_song) ?>
							</div><!-- .row -->
						</div>
					</div><!-- .row -->
					<?php endif; // if (($potential_music && !empty($potential_music)) || $vt_music) ?>

					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-2 col-md-offset-10">
								<input type="hidden" name="vt_id" id="inputVTId" value="<?php echo (isset($_POST['vt_id']) ? $_POST['vt_id'] : ''); ?>" />
								<input type="hidden" name="vt_status" id="inputVTStatus" value="<?php echo ($vt ? $vt['asset_status'] : 'draft'); ?>" />
								<input type="hidden" name="vt_cover_image_id" id="inputVTCoverImageId" value="<?php echo ($vt_album_cover ? $vt_album_cover['id'] : ''); ?>" />
								<input type="hidden" name="vt_properties" id="inputVTProperties" value='<?php echo ($vt_properties ? str_replace("'", "&apos", $vt_properties) : ''); ?>' />
								<input type="hidden" name="vt_creator_id" id="inputVTCreatorId" value="<?php echo ($vt_creator ? $vt_creator['id'] : ''); ?>" />
								<input type="hidden" name="activity_id" id="inputActivityId" value="<?php echo ($activity ? $activity['id'] : ''); ?>" />
								<input type="hidden" name="event_id" id="inputEventId" value="<?php echo ($event ? $event['id'] : ''); ?>" />
								<input type="hidden" name="event_logo_id" id="inputEventLogoId" value="<?php echo ($event_logo ? $event_logo['id'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_01" id="inputEventNarrativeSentence01" value="<?php echo ($event ? $event['event_narrative_sentence_01'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_02" id="inputEventNarrativeSentence02" value="<?php echo ($event ? $event['event_narrative_sentence_02'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_03" id="inputEventNarrativeSentence03" value="<?php echo ($event ? $event['event_narrative_sentence_03'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_04" id="inputEventNarrativeSentence04" value="<?php echo ($event ? $event['event_narrative_sentence_04'] : ''); ?>" />
								<button type="submit" class="lead btn btn-primary pull-right" name="save_vt" id="btn-save"><?php echo ($page_mode == 'edit' || (isset($_POST['vt_id']) && ($_POST['vt_id'] != '')) ? 'Save' : 'Create'); ?> my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie<?php echo ($page_mode == 'edit' || (isset($_POST['vt_id']) && ($_POST['vt_id'] != '')) ? '' : ' now!'); ?></button>
							</div>
						</div>
					</nav>

					<?php if ($page_mode == 'create') : ?>
					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-3 col-md-offset-9">
								<a  class="lead btn btn-default pull-right" id="btn-reload-vt" href="<?php echo $_SERVER['PHP_SELF'] . '?activity_id=' . $activity['id']; ?>">Shuffle my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie</a>
							</div>
						</div>
					</nav>
					<?php endif; ?>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->
		<?php endif; // if ($no_error) ?>

		<?php output_footer(); ?>
