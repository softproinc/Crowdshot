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
$vt_music                = FALSE;
$vt_creator              = FALSE;
$event                   = FALSE;
$event_logo              = FALSE;
$event_photos            = array();
$potential_music         = array();

$number_of_photos_required_for_vt = 8;
$number_of_random_event_photos    = 8;
$number_of_potential_music        = 3;

$no_error        = TRUE;
$success_message = '';
$warning_message = '';
$error_message   = '';


/**
 * This function retrieves information on the event
 *		It also builds the list of required photos from the event
 * 
 * @global generatedVideoObject $vt
 * @global assetObject $vt_album_cover
 * @global string $vt_title_01
 * @global string $vt_title_02
 * @global assetObject $vt_cta_background_image
 * @global string $vt_cta_name
 * @global string $vt_cta_date_time
 * @global string $vt_cta_location
 * @global string $vt_cta_url
 * @global eventObject $event
 * @global assetObject $event_logo
 * @global arrayOfAssetObject $event_photos
 * @global int $number_of_random_event_photos
 * @global boolean $no_error
 * @global string $warning_message
 * @global string $error_message
 */
function get_event_info_for_new_vt_based_on_GET_event_id() {
	global $vt, $vt_album_cover, $vt_title_01, $vt_title_02, $vt_cta_background_image, $vt_cta_name, $vt_cta_date_time, $vt_cta_location, $vt_cta_url;
	global $event, $event_logo, $event_photos;
	global $number_of_random_event_photos;
	global $no_error, $warning_message, $error_message;

	$vt = FALSE;

	unset($_GET['vt_id']);
	unset($_POST['vt_id']);

	// get the event
	$event = get_event_details($_GET['event_id']);

	if ($event) {
		// get event's featured image - this will be used for the event video timeline album cover
		$vt_album_cover = get_asset($event['event_featured_image_id']);

		if ($vt_album_cover) {
		} else {
				// because there isn't an event featured image, let's get one more random event photo for the event video timeline album cover
				++$number_of_random_event_photos;
		} // if ($vt_album_cover) else

		// get event's call-to-action's background photo
		$vt_cta_background_image = get_asset($event['event_cta_background_image_id']);

		if ($vt_cta_background_image) {
		} else {
			// because there isn't an event's call-to-action's background photo, let's get one more random event photo for the event video timeline call-to-action's background photo
			++$number_of_random_event_photos;
		} // if ($vt_cta_background_image) else

		// get event photos to fill in event video timeline
		$event_photos = get_assets('', 'user_image', 'published', 'event', $event['id'], '', '', 'RAND', $number_of_random_event_photos);

		if (empty($event_photos)) {
			// if it falls into there, there isn't any event photos; therefore, it is not possible to create an event video timeline
			$no_error      = FALSE;
			$error_message = 'The event does not have enough to create a movie. Click <a href="upload-photos-to-event.php?event_id=' . $event['id'] . '">here</a> to upload more photos.';
		} else {
			if (count($event_photos) == $number_of_random_event_photos) {
				// if there isn't an event video timeline album cover already, take it from the random event photos
				if (!$vt_album_cover) {
					$warning_message .= ($warning_message ? '<br />' : '') . 'You have not set an album cover. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="upload-photos-to-event.php?event_id=' . $event['id'] . '">here</a> to set an album cover or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
					$vt_album_cover  =  array_shift($event_photos);

					--$number_of_random_event_photos;
				} // if (!$vt_album_cover)

				// if there isn't an event's call-to-action's background photo, take it from the random event photos
				if (!$vt_cta_background_image) {
					$warning_message         .= ($warning_message ? '<br />' : '') . 'You have not selected call-to-action photo. A random photo has been selected for you. Duplicate photos may appear in the movie. Click <a href="upload-photos-to-event.php?event_id=' . $event['id'] . '">here</a> to select a call-to-action photo or click <a href="event.php?event_id=' . $event['id'] . '">here</a> if you do not wish to continue.';
					$vt_cta_background_image =  array_shift($event_photos);

					--$number_of_random_event_photos;
				} // if (!$vt_cta_background_image)
			} else { // if it falls into here, there isn't enough event photos; therefore, it is not possible to create an event video timeline
				$no_error      = FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'The event does not have enough photos to create a movie. Click <a href="upload-photos-to-event.php?event_id=' . $event['id'] . '">here</a> to upload more photos.';
			} // if (count($event_photos) == $number_of_random_event_photos) else
		} // if (empty($event_photos))

		// get event's logo
		$event_logo = get_asset($event['event_logo_id']);

		if ($event_logo) {
		} else {
			// *** future decision *** is it okay to create an event video timeline without an event logo?
		} // if ($event_logo) else

		// get titles
		$vt_title_01 = $vt_title_02 = $event['event_name'];

		// get call-to-action information
		$vt_cta_name      = $event['event_name'];
		$vt_cta_date_time = $event['event_start_date'] . ($event['event_start_date'] != $event['event_end_date'] ? ' to ' . $event['event_end_date'] : '');
		$vt_cta_location  = $event['event_location'];
		$vt_cta_url       = $event['event_cta_url'];
	} else { // the event is invalid; therefore, an event video timeline should not be created - just go back to the home page
		unset($_GET['event_id']);
		unset($_POST['event_id']);

		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Cannot create a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because the selected event is not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
	} // if (!$event)
} //function get_event_info_for_new_vt_based_on_GET_event_id

/**
 * This function inserts event narratives into the event video timeline
 * 
 * @param array $vt_properties
 * @param bigint $event_logo_id
 * @param string $event_narrative_sentence_01
 * @param string $event_narrative_sentence_02
 * @param string $event_narrative_sentence_03
 * @param string $event_narrative_sentence_04
 * @return string $vt_properties revised event video timeline properties - JSON format
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
	
	return json_encode($vt_properties_associative_array);
} // function insert_event_narratives_to_vt_properties


// *** Start building the page

// see if current user is in a cookie - if so, get it; otherwise, get user info
$current_user = get_SESSION_current_user(sprintf('http%1$s://%2$s%3$s%4$s/create-edit-event-vt.php?%5$s_id=%6$u', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER["SERVER_NAME"], ($_SERVER["SERVER_PORT"] != '80' ? ':' . $_SERVER["SERVER_PORT"] : ''), dirname($_SERVER['PHP_SELF']), (isset($_GET['vt_id']) || isset($_POST['vt_id']) ? 'vt' : (isset($_GET['event_id']) || isset($_POST['event_id']) ? 'event' : '')), (isset($_GET['vt_id']) ? $_GET['vt_id'] : (isset($_POST['vt_id']) ? $_POST['vt_id'] : (isset($_GET['event_id']) ? $_GET['event_id'] : (isset($_POST['event_id']) ? $_POST['event_id'] : ''))))));

// check if the create / save button was clicked - if so, create / save the data
if (isset($_POST['save_vt'])) {
	if (isset($_POST['vt_id']) && isset($_POST['vt_status']) && $_POST['vt_status'] &&
		isset($_POST['vt_cover_image_id']) && $_POST['vt_cover_image_id'] &&
		isset($_POST['vt-block-02-content-02-text']) && $_POST['vt-block-02-content-02-text'] && isset($_POST['vt-block-11-content-01-text']) && $_POST['vt-block-11-content-01-text'] &&
		isset($_POST['vt-block-12-content-02-cta-name']) && $_POST['vt-block-12-content-02-cta-name'] && isset($_POST['vt-block-12-content-03-cta-date-time']) && $_POST['vt-block-12-content-03-cta-date-time'] && isset($_POST['vt-block-12-content-04-cta-location']) && $_POST['vt-block-12-content-04-cta-location'] && isset($_POST['vt-block-12-content-05-cta-url']) && $_POST['vt-block-12-content-05-cta-url'] &&
		isset($_POST['vt-music']) && $_POST['vt-music'] &&
		isset($_POST['vt_properties']) && $_POST['vt_properties'] &&
		isset($_POST['vt_creator_id']) && $_POST['vt_creator_id'] &&
		isset($_POST['event_id']) && $_POST['event_id'] &&
		isset($_POST['event_logo_id']) && $_POST['event_logo_id'] &&
		isset($_POST['event_narrative_sentence_01']) && $_POST['event_narrative_sentence_01'] && isset($_POST['event_narrative_sentence_02']) && isset($_POST['event_narrative_sentence_03']) && isset($_POST['event_narrative_sentence_04'])) {
		// decode event video timeline properties into associative array
		$vt_properties_associative_array = json_decode($_POST['vt_properties'], TRUE);

		if (array_key_exists('album_cover', $vt_properties_associative_array) &&
			array_key_exists('timeline', $vt_properties_associative_array) &&
			array_key_exists('music', $vt_properties_associative_array)) {
			// update album cover title
			$vt_properties_associative_array['album_cover']['title'] = $_POST['vt-block-12-content-02-cta-name'];

			// update event video timeline properties with $_POST fields
			$vt_properties_associative_array['timeline'][2]['properties']['content'][1]['text']  = $_POST['vt-block-02-content-02-text'];
			$vt_properties_associative_array['timeline'][20]['properties']['content'][0]['text'] = $_POST['vt-block-11-content-01-text'];
			$vt_properties_associative_array['timeline'][22]['properties']['content'][1]['text'] = $_POST['vt-block-12-content-02-cta-name'];
			$vt_properties_associative_array['timeline'][22]['properties']['content'][2]['text'] = $_POST['vt-block-12-content-03-cta-date-time'];
			$vt_properties_associative_array['timeline'][22]['properties']['content'][3]['text'] = $_POST['vt-block-12-content-04-cta-location'];
			$vt_properties_associative_array['timeline'][22]['properties']['content'][4]['text'] = $_POST['vt-block-12-content-05-cta-url'];

			// update music selection
			$vt_properties_associative_array['music']['asset_id'] = $_POST['vt-music'];
		} else {
			$vt_properties =  '';
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Ops! Something is wrong with your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-edit-event-vt.php?' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? 'vt' : 'event') . '_id=' . (isset($_POST['vt_id']) && $_POST['vt_id'] ? $_POST['vt_id'] : $_POST['event_id']) . '">here</a> to try again.';
		} // if (array_key_exists('album_cover', $_POST['vt_properties']) && array_key_exists('timeline', $vt_properties_associative_array) && array_key_exists('music', $vt_properties_associative_array))
	} else {
		$no_error      =  FALSE;
		$error_message .= ($error_message ? '<br />' : '') . 'Required information is missing. Please review your movie timeline.';
	} // end of ensuring required $_POST variables are set

	if ($no_error) { // if no error, create / update event video timeline
		if ($_POST['vt_id'] == '') { // there isn't an event video timeline id passed - let's create a new one
			$vt = create_generated_video(json_encode($vt_properties_associative_array), '', $_POST['event_id'], $_POST['vt_creator_id']);

			if ($vt) {
				$_POST['vt_id'] = $vt['id'];
				$page_mode       = 'edit';
				$success_message = 'Your movie timeline has been created and your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie is being generated.';
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error creating your movie timeline.';
			} // if (!$vt) else
		} else { // there is an event video timeline id passed, let's update it
			if ($_POST['vt_status'] == 'draft') {
				$vt = edit_generated_video($_POST['vt_id'], json_encode($vt_properties_associative_array), $_POST['vt_creator_id']);

				if ($vt) {
					$_POST['vt_id'] = $vt['id'];
					$page_mode       = 'edit';
					$success_message = 'Your movie timeline has been updated and your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie is being generated.';
				} else {
					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Error updating your movie timeline.';
				} // if (!$vt) else
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the timeline that has already been used to generate a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-edit-event-vt.php?event_id=' . $_POST['event_id'] . '">here</a> to create a new movie timeline.';
			} // if ($_POST['vt_status'] == 'draft' ) else
		} // if ($_POST['vt_id'] == '') else
	} // if ($no_error)

	if ($no_error) { // if there no error, send event video timeline to video engine
		//	frist, add event's narratives to the event video timeline
		$video_engine_ready_vt_properties = insert_event_narratives_to_vt_properties($vt['asset_properties'], $_POST['event_logo_id'], $_POST['event_narrative_sentence_01'], $_POST['event_narrative_sentence_02'], $_POST['event_narrative_sentence_03'], $_POST['event_narrative_sentence_04']);
		
		// call video engine - need to create the necessary $_POST variables adn then calling the video engine form - ideally, after calling the video engine the user is returned here with a message or to a new page with a message the video is being generated
	} // if ($no_error)

	unset($vt_properties_associative_array);
} else {
	if (isset($_GET['vt_id']) && $_GET['vt_id']) {
		// get event video timeline
		$vt = get_generated_videos($_GET['vt_id']);

		if (!empty($vt)) {
			$page_mode = 'edit';

			$vt = $vt[0]; // because get_generated_videos returns an array of generated videos and because an unique asset id ($_GET['vt_id']) was passed in, there should only be one item in the array

			if ($vt['asset_status'] == 'draft') {
				$event = $vt['event'];

				if ($event) { // the event id is invalid; therefore, an event video timeline should not be created - just go back to the home page
					// get event logo
					$event_logo = get_asset($event['event_logo_id']);

					// set event video timeline properties
					$vt_properties = $vt['asset_properties'];

					// decode event video timeline properties into associative array
					$vt_properties_associative_array = json_decode($vt_properties, TRUE);

					if (array_key_exists('album_cover', $vt_properties_associative_array) &&
						array_key_exists('timeline', $vt_properties_associative_array) &&
						array_key_exists('music', $vt_properties_associative_array)) {
						// get event video timeline's alumn cover from event video timeline properties
						$vt_album_cover = get_asset($vt_properties_associative_array['album_cover']['asset_id']);

						// get first title
						$vt_title_01 = $vt_properties_associative_array['timeline'][2]['properties']['content'][1]['text'];

						// get event photos from event video timeline properties
						$event_photos   = array();
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][4]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][6]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][8]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][10]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][12]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][14]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][16]['properties']['content'][0]['asset_id']);
						$event_photos[] = get_asset($vt_properties_associative_array['timeline'][18]['properties']['content'][0]['asset_id']);

						// get second title
						$vt_title_02 = $vt_properties_associative_array['timeline'][20]['properties']['content'][0]['text'];

						// get event video timeline's call-to-action background photo from event video timeline properties
						$vt_cta_background_image = get_asset($vt_properties_associative_array['timeline'][22]['properties']['background']['asset_id']);
						$vt_cta_name             = $vt_properties_associative_array['timeline'][22]['properties']['content'][1]['text'];
						$vt_cta_date_time        = $vt_properties_associative_array['timeline'][22]['properties']['content'][2]['text'];
						$vt_cta_location         = $vt_properties_associative_array['timeline'][22]['properties']['content'][3]['text'];
						$vt_cta_url              = $vt_properties_associative_array['timeline'][22]['properties']['content'][4]['text'];

						// get music from event video timeline properties
						$vt_music = get_asset($vt_properties_associative_array['music']['asset_id']);

						unset($vt_properties_associative_array);
					} else {
						$vt_properties =  '';
						$no_error      =  FALSE;
						$error_message .= ($error_message ? '<br />' : '') . 'Ops! Something is wrong with your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-edit-event-vt.php?vt_id=' . $vt['id'] . '">here</a> to try again.';
					}

					$_POST['vt_id'] = $_GET['vt_id'];
				} else {
					unset($_GET['event_id']);
					unset($_POST['event_id']);

					$no_error      =  FALSE;
					$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because it is connected to an invalid event. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
				} // if ($event)
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the timeline that has already been used to generate a <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if ($vt['asset_status'] == 'draft' ) else
		} else { // if it falls here, the passed event video timeline id is not valid
			// see if there is an event id passed into the page
			if (isset($_GET['event_id']) && $_GET['event_id']) {
				$GET_vt_id = $_GET['vt_id']; // save it for warning messgae

				// try to create an event video timeline based on the input event id
				get_event_info_for_new_vt_based_on_GET_event_id();

				$warning_message .= ($warning_message ? '<br />' : '') . 'The selected event video timeline (' . $GET_vt_id . ') does not exist. A new event video timeline will be created based on the supplied event (' . $_GET['event_id'] . ').';

				unset($GET_vt_id);
			} else { // if it falls into here, no event vt id and no event id were passed; therefore, it has to go back to the home page (can't create event vedio timeline because no event id can be associated
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Cannot edit the selected <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because it is not valid and cannot create a new <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because no event was selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
			} // if (isset($_GET['event_id'])) else
		} // if ($vt) else
	} else { // if it falls into here, no event vt id was passed
		if (isset($_GET['event_id']) && $_GET['event_id']) {
			// try to create an event video timeline based on the input event id
			get_event_info_for_new_vt_based_on_GET_event_id();
		} else { // if it falls into here, no event vt id and no event id were passed; therefore, it has to go back to the home page (can't create event vedio timeline because no event id can be associated
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'Cannot create a new <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie because no event was selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to exit.';
		} // if (isset($_GET['event_id'])) else
	} // if (isset($_GET['vt_id'])) else

	// if it reaches here, there are enough photos to create an event video timeline - let's get more movie's elements

	// get potential music
	$potential_music = get_assets('', 'vt_music', 'published', '', '', '', '', 'RAND', $number_of_potential_music);

	if (empty($potential_music)) {
		if ($vt_music) {
			// if there is a music attached to the event video timeline already, prepend the attached music to the potential music list
			$potential_music[] = $vt_music;
		} else {
			// *** future decision *** is it okay to create an event video timeline wihtout a song?
		} // if ($vt_music) else
	} else {
		if ($vt_music) {
			// if there is a music attached to the event video timeline already, remove one from the potential music list and prepend the attached music to the potential music list
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

	// set event video timeline creator if it is not already set
	if (!$vt_creator) {
		if ($current_user) {
			$vt_creator = $current_user;
		} else { // if it falls into here, current user is not set - which mean the user got here directly and without providing user information via create / edit event page; therefore, let's use the event creator's id as current user and event video timeline creator
			$vt_creator = get_user($event['created_by']);

			if ($vt_creator) {
				$_SESSION['current_user'] = $current_user = $vt_creator;
			} else { // if it falls into here, the user assoicated with the event is not valid and since the event's creator's id is also invalid, let's use the default system id 1
				$vt_creator = get_user(1);
				$current_user        = false; // make sure current user is not set because the default system id 1 should not be used

				unset($_SESSION['current_user']); // make sure current user is not set because the default system id 1 should not be used
			} // if ($vt_creator) else
		} // if ($current_user) else
	} // if (!$vt_creator)

	// build event video timeline properties
	if ($no_error && $page_mode == 'create') {
		if ($vt_album_cover && $event && $event_logo && !empty($event_photos) && (count($event_photos) == $number_of_photos_required_for_vt) && $vt_cta_background_image && !empty($potential_music)) {
			$vt_properties_associative_array = array();

			$vt_properties_associative_array['album_cover'] = array('title' => $vt_cta_name, 'asset_id' => $vt_album_cover['id']);

			$vt_properties_associative_array['timeline'][] = array('sequence' => 1,  'type' => 'block',      'is_locked' => TRUE,  'properties' => array('type' => 'album_cover',                                                                                                                          'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $vt_album_cover['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 2,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'star_light'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 3,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'branded_title',           'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                'content' => array(array('type' => 'logo',  'is_locked' => TRUE,  'asset_id' => $event_logo['id']),                                              array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_title_01, 'color' => 'rgba(255, 255, 255, 1)'))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 4,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'light_burst_fade'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 5,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[0]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 6,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'down_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 7,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[1]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 8,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 9,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[2]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 10, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 11, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[3]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 12, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 13, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[4]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 14, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 15, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[5]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 16, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 17, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[6]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 18, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 19, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                 'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[7]['id']))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 20, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 21, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'title',                   'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                'content' => array(array('type' => 'text',  'is_locked' => FALSE, 'text'     => $vt_title_02, 'color' => 'rgba(255, 255, 255, 1)'))));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 22, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'page_flip'));
			$vt_properties_associative_array['timeline'][] = array('sequence' => 23, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'branded_call_to_action',  'background' => array('type' => 'image', 'is_locked' => TRUE, 'asset_id' => $vt_cta_background_image['id']), 'content' => array(array('type' => 'logo',  'is_locked' => TRUE,  'asset_id' => $event_logo['id']),                                              array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_name, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_date_time, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_location, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_url, 'color' => 'rgba(255, 255, 255, 1)'))));

			$vt_properties_associative_array['music'] = array('asset_id' => $potential_music[0]['id']);

			$vt_properties = json_encode($vt_properties_associative_array, JSON_NUMERIC_CHECK);

			unset($vt_properties_associative_array);
		} else {
			$no_error      =  FALSE;
			$error_message .= ($error_message ? '<br />' : '') . 'There was an error building your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="event.php?event_id=' . $event['id'] . '">here</a> to return to the event\'s (' . $event['event_name'] . ') page.';
		} // if ($vt_album_cover && $event && $event_logo && !empty($event_photos) && (count($event_photos) == $number_of_photos_required_for_vt) && $vt_cta_background_image && !empty($potential_music))
	} // if ($no_error && $page_mode == 'create')
} // if (isset($_POST['save_vt'])) else
?>
		<?php output_header('create-edit-event-vt-page', (($page_mode == 'edit') || (isset($_POST['vt_id']) && ($_POST['vt_id'] != '')) ? 'Edit' : 'Create') . ' Event Video Timeline | CrowdShot', FALSE, TRUE, FALSE, FALSE, FALSE, FALSE, FALSE); ?>

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
		<!-- Main create event video timeline page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>Let's preview what you've done so far on your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. You will be able to adjust the titles, call-to-action information, and your background music. Click <a href="upload-photos-to-event.php?event_id=<?php echo $event['id']; ?>">here</a> to upload more photos.</p>
					</div>
				</div>
			</div><!-- .container -->
		</section><!-- #create-edit-introduction -->

		<!-- Main create / edit event vt form -->
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
												<input type="text" name="vt-block-02-content-02-text" class="form-control vt-block-text vt-block-text-white-on-black unlocked-content" id="vt-block-02-content-02-text" placeholder="Introduce your fundraising story and why it's important" maxlength="75" required value="<?php echo $vt_title_01; ?>" />
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
											<div id="vt-block-05-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[2]['asset_url']; ?>">
												<img id="vt-block-05-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[2]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[2]['id']; ?>" />
											</div><!-- #vt-block-05-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-05 -->
									<li id="vt-block-06" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-06-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[3]['asset_url']; ?>">
												<img id="vt-block-06-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[3]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[3]['id']; ?>" />
											</div><!-- #vt-block-06-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-06 -->
									<li id="vt-block-07" class="ctext-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-07-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[4]['asset_url']; ?>">
												<img id="vt-block-07-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[4]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[4]['id']; ?>" />
											</div><!-- #vt-block-07-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-07 -->
									<li id="vt-block-08" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-event-shot locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-08-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[5]['asset_url']; ?>">
												<img id="vt-block-08-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[5]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[5]['id']; ?>" />
											</div><!-- #vt-block-08-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-08 -->
									<li id="vt-block-09" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-09-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[6]['asset_url']; ?>">
												<img id="vt-block-09-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[6]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[6]['id']; ?>" />
											</div><!-- #vt-block-09-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-09 -->
									<li id="vt-block-10" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-10-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox" href="<?php echo $event_photos[7]['asset_url']; ?>">
												<img id="vt-block-10-content-01-user-image" class="vt-block-user-image locked-content" src="<?php echo get_image_thumbnail_url(json_decode($event_photos[7]['asset_properties'], TRUE), 'mobile_timeline'); ?>" data-asset_id="<?php echo $event_photos[7]['id']; ?>" />
											</div><!-- #vt-block-10-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-10 -->
									<li id="vt-block-11" class="text-center vt-block-container vt-block-type-title locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-11-content-01" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-11-content-01-text" class="form-control vt-block-text unlocked-content" id="vt-block-11-content-01-text" placeholder="Introduce your fundraising event" maxlength="75" required value="<?php echo $vt_title_02; ?>" />
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
												<input type="text" name="vt-block-12-content-02-cta-name"      class="form-control vt-block-text unlocked-content" id="vt-block-12-content-02-cta-name"      placeholder="Event name"          maxlength="50" required value="<?php echo $vt_cta_name; ?>" />
											</div><!-- #vt-block-12-content-02 -->
											<div id="vt-block-12-content-03" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-03-cta-date-time" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-03-cta-date-time" placeholder="Event date and time" maxlength="50" required value="<?php echo $vt_cta_date_time; ?>" />
											</div><!-- #vt-block-12-content-03 -->
											<div id="vt-block-12-content-04" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-04-cta-location"  class="form-control vt-block-text unlocked-content" id="vt-block-12-content-04-cta-location"  placeholder="Event location"      maxlength="50" required value="<?php echo $vt_cta_location; ?>" />
											</div><!-- #vt-block-12-content-04 -->
											<div id="vt-block-12-content-05" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="url" name="vt-block-12-content-05-cta-url" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-05-cta-url" placeholder="http://event.org/" maxlength="255" required value="<?php echo $vt_cta_url; ?>" />
											</div><!-- #vt-block-12-content-05 -->
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
								<a  class="lead btn btn-default pull-right" id="btn-reload-vt" href="<?php echo $_SERVER['PHP_SELF'] . '?event_id=' . $event['id']; ?>">Shuffle my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie</a>
							</div>
						</div>
					</nav>
					<?php endif; ?>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->
		<?php endif; // if ($no_error) ?>

		<?php output_footer(); ?>