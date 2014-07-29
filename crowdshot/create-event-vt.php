<?php
session_start();

require_once('inc/crowdshot-db-apis.php');
require_once('inc/crowdshot-functions.php');

$event                      = FALSE;
$event_logo                 = FALSE;
$event_featured_image       = FALSE;
$event_cta_background_image = FALSE;
$event_photos               = array();

$potential_music = array();

$vt                      = FALSE;
$vt_properties           = '';
$vt_album_cover          = FALSE;
$vt_cta_background_image = FALSE;
$vt_cta_date_time        = '';
$vt_cta_url_label        = '';
$vt_cta_logo_text_before = '';
$vt_cta_logo_text_after  = '';
$vt_music                = FALSE;
$vt_creator              = FALSE;

$number_of_random_event_photos            = 6;
$number_of_additional_random_event_photos = 0;
$number_of_potential_music                = 3;

$no_error        = TRUE;
$success_message = '';
$warning_message = '';
$error_message   = '';


/**
 * This function updates specific blocks
 *		It also creates the asset relationship records for the newly uploaded photos
 * 
 * @param bigint $event_id
 * @param string $vt_properties in JSON format
 * @param string $album_cover_title
 * @param bigint $album_cover_image_id
 * @param bigint $block_01_user_image_id
 * @param string $block_02_text
 * @param bigint $block_09_user_image_id
 * @param bigint $block_10_user_image_id
 * @param string $block_11_text1
 * @param bigint $block_12_background_image_id
 * @param string $block_12_text_01
 * @param string $block_12_text_02
 * @param string $block_12_text_03
 * @param string $block_12_text_04
 * @param string $block_12_text_05
 * @param string $block_12_text_06
 * @param string $block_12_text_07
 * @param bigint $music_id
 * @global boolean $no_error
 * @global string $error_message
 * @global string $warning_message
 * @return string in JSON format
 */
function update_generated_video_properties($event_id, $vt_properties, $album_cover_title, $album_cover_image_id, $block_01_user_image_id, $block_02_text, $block_09_user_image_id, $block_10_user_image_id, $block_11_text1, $block_12_background_image_id, $block_12_text_01, $block_12_text_02, $block_12_text_03, $block_12_text_04, $block_12_text_05, $block_12_text_06, $block_12_text_07, $music_id) {
	global $no_error, $error_message, $warning_message;
	// decode event video timeline properties into associative array
	$vt_properties_associative_array = json_decode($vt_properties, TRUE);

	// validate generated video timeline asset properties
	if (array_key_exists('album_cover', $vt_properties_associative_array) &&
		array_key_exists('timeline', $vt_properties_associative_array) &&
		array_key_exists('music', $vt_properties_associative_array)) {
		// update album cover title
									$vt_properties_associative_array['album_cover']['title']    = $album_cover_title;
		if ($album_cover_image_id) {$vt_properties_associative_array['album_cover']['asset_id'] = $album_cover_image_id;} else {$warning_message .= ($warning_message ? '<br />' : '') . 'Albmun cover photo was not found. A photo from the event has been selected for you.';}

		// update event video timeline properties with $_POST fields
		$vt_properties_associative_array['timeline'][2]['properties']['content'][0]['text']  = $block_02_text;
		$vt_properties_associative_array['timeline'][20]['properties']['content'][0]['text'] = $block_11_text1;
		$vt_properties_associative_array['timeline'][22]['properties']['content'][1]['text'] = $block_12_text_01; // call-to-action name/title
		$vt_properties_associative_array['timeline'][22]['properties']['content'][2]['text'] = $block_12_text_02; // call-to-action date/time
		$vt_properties_associative_array['timeline'][22]['properties']['content'][3]['text'] = $block_12_text_03; // call-to-action location
		$vt_properties_associative_array['timeline'][22]['properties']['content'][4]['text'] = $block_12_text_04; // call-to-action URL
		$vt_properties_associative_array['timeline'][22]['properties']['content'][5]['text'] = $block_12_text_05; // call-to-action URL label
		$vt_properties_associative_array['timeline'][22]['properties']['content'][6]['text'] = $block_12_text_06; // call-to-action text before logo
		$vt_properties_associative_array['timeline'][22]['properties']['content'][7]['text'] = $block_12_text_07; // call-to-action text after logo

		// only update image id if the user has uploaded on. otherwise, use the default set in the asset_properties
		if ($block_01_user_image_id)       {$vt_properties_associative_array['timeline'][0]['properties']['content'][0]['asset_id']  = $block_01_user_image_id;}       else {$warning_message .= ($warning_message ? '<br />' : '') . 'You did not upload a photo for block 1. A photo from the event has been selected for you.';}
		if ($block_09_user_image_id)       {$vt_properties_associative_array['timeline'][16]['properties']['content'][0]['asset_id'] = $block_09_user_image_id;}       else {$warning_message .= ($warning_message ? '<br />' : '') . 'You did not upload a photo for block 9. A photo from the event has been selected for you.';}
		if ($block_10_user_image_id)       {$vt_properties_associative_array['timeline'][18]['properties']['content'][0]['asset_id'] = $block_10_user_image_id;}       else {$warning_message .= ($warning_message ? '<br />' : '') . 'You did not upload a photo for block 10. A photo from the event has been selected for you.';}
		if ($block_12_background_image_id) {$vt_properties_associative_array['timeline'][22]['properties']['background']['asset_id'] = $block_12_background_image_id;} else {$warning_message .= ($warning_message ? '<br />' : '') . 'You did not upload a photo for block 12. A photo from the event has been selected for you.';}

		// update music selection
		$vt_properties_associative_array['music']['asset_id'] = $music_id;

		return json_encode($vt_properties_associative_array);
	} else {
		$no_error      = FALSE;
		$error_message = 'Ops! Something is wrong with your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="create-event-vt.php?event_id=' . $event_id . '">here</a> to try again.';
	} // if (array_key_exists('album_cover', $vt_properties_associative_array) && array_key_exists('timeline', $vt_properties_associative_array) && array_key_exists('music', $vt_properties_associative_array)) else
} // function update_generated_video_properties



/**
 * This function links the input photo ids to the input event id
 * 
 * @global userDBObject $current_user
 * @global string $warning_message
 * @param bigint $event_id
 * @param bigint $block_01_user_image_id
 * @param bigint $block_09_user_image_id
 * @param bigint $block_10_user_image_id
 * @param bigint $block_12_background_image_id
 */
function link_new_photos_to_event($event_id, $block_01_user_image_id, $block_09_user_image_id, $block_10_user_image_id, $block_12_background_image_id) {
	global $current_user;
	global $warning_message;

	$asset_relationship_rows = array();

	if ($block_01_user_image_id)       {$asset_relationship_rows[] = array('asset_id' => $block_01_user_image_id,       'related_object_type' => 'event', 'related_object_id' => $event_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);}
	if ($block_09_user_image_id)       {$asset_relationship_rows[] = array('asset_id' => $block_09_user_image_id,       'related_object_type' => 'event', 'related_object_id' => $event_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);}
	if ($block_10_user_image_id)       {$asset_relationship_rows[] = array('asset_id' => $block_10_user_image_id,       'related_object_type' => 'event', 'related_object_id' => $event_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);}
	if ($block_12_background_image_id) {$asset_relationship_rows[] = array('asset_id' => $block_12_background_image_id, 'related_object_type' => 'event', 'related_object_id' => $event_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $current_user['id']);}

	if (!empty($asset_relationship_rows)) {
		$create_asset_relationship_success = create_asset_relationships($asset_relationship_rows);

		if ($create_asset_relationship_success) {
		} else {
			$warning_message .= ($warning_message ? '<br />' : '') . 'Error attaching photos to event.';
		} // if ($create_asset_relationship_success) else
	} // if (!empty($asset_relationship_rows))
} // function link_new_photos_to_event


/**
 * This function gets the event's logo object
 * 
 * @global boolean $event
 * @global assetDBObject $event_logo
 * @global boolean $no_error
 * @global string $error_message
 */
function get_event_logo() {
	global $event, $event_logo;
	global $no_error, $error_message;
	
	if ($no_error) {
		if (array_key_exists('event_logo_id', $event)) {
			$event_logo = get_asset($event['event_logo_id']);

			if ($event_logo) {
			} else {
				$no_error      = FALSE;
				$error_message = 'Event\'s logo not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
			} // if ($event_logo) else
		} else {
			$no_error      = FALSE;
			$error_message = 'Event\'s logo information not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
		} // if (array_key_exists('event_logo_id', $event)) else
	} // if ($no_error)
} // function get_event_logo


/**
 * This function gets the event's featured image object
 * 
 * @global boolean $event
 * @global assetDBObject $event_featured_image
 * @global int $number_of_additional_random_event_photos
 * @global boolean $no_error
 * @global string $error_message
 */
function get_event_featured_image() {
	global $event, $event_featured_image;
	global $number_of_additional_random_event_photos;
	global $no_error, $error_message;
	
	if ($no_error) {
		if (array_key_exists('event_featured_image_id', $event)) {
			$event_featured_image = get_asset($event['event_featured_image_id']);

			if ($event_featured_image) {
			} else {
				++$number_of_additional_random_event_photos;
			} // if ($event_logo) else
		} else {
			$no_error      = FALSE;
			$error_message = 'Event\'s featured image information not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
		} // if ( array_key_exists('event_featured_image_id', $event)) else
	} // if ($no_error)
} // function get_event_featured_image


/**
 * This function gets the event's call-to-action background image object
 * 
 * @global boolean $event
 * @global assetDBObject $event_cta_background_image
 * @global int $number_of_additional_random_event_photos
 * @global boolean $no_error
 * @global string $error_message
 */
function get_event_cta_background_image() {
	global $event, $event_cta_background_image;
	global $number_of_additional_random_event_photos;
	global $no_error, $error_message;
	
	if ($no_error) {
		if (array_key_exists('event_cta_background_image_id', $event)) {
			$event_cta_background_image = get_asset($event['event_cta_background_image_id']);

			if ($event_cta_background_image) {
			} else {
				++$number_of_additional_random_event_photos;
			} // if ($event_logo) else
		} else {
			$no_error      = FALSE;
			$error_message = 'Event\'s call-to-action background image information not valid. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
		} // if ( array_key_exists('event_featured_image_id', $event)) else
	} // if ($no_error)
} // function get_event_cta_background_image


/**
 * This function gets random event photos
 * 
 * @global boolean $event
 * @global assetDBObjects $event_photos
 * @global int $number_of_random_event_photos
 * @global int $number_of_additional_random_event_photos
 * @global boolean $no_error
 * @global string $warning_message
 * @global string $error_message
 */
function get_event_photos() {
	global $event, $event_featured_image, $event_cta_background_image, $event_photos;
	global $number_of_random_event_photos, $number_of_additional_random_event_photos;
	global $no_error, $warning_message, $error_message;
	
	if ($no_error) {
		$event_photos = get_assets('', 'user_image', 'published', 'event', $event['id'], '', '', 'RAND', $number_of_random_event_photos + $number_of_additional_random_event_photos);

		if (empty($event_photos)) {
			$no_error      = FALSE;
			$error_message = 'Event does not have any photos. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
		} else {
			if (count($event_photos) == $number_of_random_event_photos + $number_of_additional_random_event_photos) {
				if ($number_of_additional_random_event_photos > 0 && !$event_featured_image) {
					$event_featured_image = array_shift($event_photos);

					--$number_of_additional_random_event_photos;
				} // if ($number_of_additional_random_event_photos > 0 && !$event_featured_image)

				if ($number_of_additional_random_event_photos > 0 && !$event_cta_background_image) {
					$event_cta_background_image = array_shift($event_photos);

					--$number_of_additional_random_event_photos;
				} // if ($number_of_additional_random_event_photos > 0 && !$event_cta_background_image)
				
				if (!$event_featured_image || !$event_cta_background_image) {
					$warning_message .= ($warning_message ? '<br />' : '') . 'There may not be enough event photos to create a movie.';
				} // if (!$event_featured_image || !$event_cta_background_image)
			} else {
				$no_error      = FALSE;
				$error_message = 'Event does not have enough photos. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '/event.php?event_id=' . $event['id'] . '">here</a> to continue.';
			} // if (count($event_photos) == $number_of_random_event_photos + $number_of_additional_random_event_photos) else
		} // if (empty($event_photos)) else
	} // if ($no_error)
} // function get_event_photos


/**
 * This function retrieves the event record (and related information) based on URL parameter event_id
 * 
 * @global eventDBObject $event
 * @global boolean $no_error
 * @global string $error_message
 */
function get_event() {
	global $event;
	global $no_error, $error_message;

	if (isset($_GET['event_id'])) {
		$event = get_event_details($_GET['event_id']);

		if ($event) {
			get_event_logo();
			get_event_featured_image();
			get_event_cta_background_image();
			get_event_photos();
		} else {
			$no_error      = FALSE;
			$error_message = 'Event not found. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to continue.';
		}
	} else {
		$no_error      = FALSE;
		$error_message = 'Event not selected. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to continue.';
	} // if (isset($_GET['event_id']))
} // function get_SESSION_event


/**
 * This function retrieves music candidates for the video timeline
 * 
 * @global type $potential_music
 * @global int $number_of_potential_music
 * @global boolean $no_error
 * @global string $error_message
 */
function get_potential_vt_music() {
	global $potential_music;
	global $number_of_potential_music;
	global $no_error, $error_message;

	$potential_music = get_assets('', 'vt_music', 'published', '', '', '', '', 'RAND', $number_of_potential_music);

	if (empty($potential_music)) {
		$no_error      = FALSE;
		$error_message = 'No available music found. Click <a href="' . dirname($_SERVER['PHP_SELF']) . '">here</a> to continue.';
	} else {
	} // if (empty($potential_music)) else
} // function get_potential_vt_music


/**
 * This function builds the initial video timeline properties
 * 
 * @global eventDBObject $event
 * @global assetDBObject $event_logo
 * @global assetDBObject $event_featured_image
 * @global assetDBObject $event_cta_background_image
 * @global assetDBObjects $event_photos
 * @global string $vt_cta_date_time
 * @global string $vt_cta_url_label
 * @global string $vt_cta_logo_text_before
 * @global string $vt_cta_logo_text_after
 * @global assetDBObject $potential_music
 * @global string $vt_properties in JSON format
 * @global int $number_of_random_event_photos
 * @global int $number_of_potential_music
 * @global boolean $no_error
 * @global string $error_message
 */
function build_generated_video_properties() {
	global $event, $event_logo, $event_featured_image, $event_cta_background_image, $event_photos;
	global $vt_cta_date_time, $vt_cta_url_label, $vt_cta_logo_text_before, $vt_cta_logo_text_after;
	global $potential_music;
	global $vt_properties;
	global $number_of_random_event_photos;
	global $number_of_potential_music;
	global $no_error, $error_message;

	if ($no_error &&
		$event && array_key_exists('id', $event) && array_key_exists('event_name', $event) && array_key_exists('event_location', $event) && array_key_exists('event_cta_url', $event) &&
		$event_logo && array_key_exists('id', $event_logo) &&
		$event_featured_image && array_key_exists('id', $event_featured_image) &&
		$event_cta_background_image && array_key_exists('id', $event_cta_background_image) &&
		!empty($event_photos) && (count($event_photos) == $number_of_random_event_photos) &&
		!empty($potential_music) && (count($potential_music) == $number_of_potential_music)) {
		$vt_properties_associative_array = array();

		$vt_properties_associative_array['album_cover'] = array('title' => $event['event_name'], 'asset_id' => $event_featured_image['id']);

		$vt_properties_associative_array['timeline'][] = array('sequence' => 1,  'type' => 'block',      'is_locked' => TRUE,  'properties' => array('type' => 'album_cover',                                                                                                                             'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_featured_image['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 2,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'star_light'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 3,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'title',                   'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                   'content' => array(array('type' => 'text',  'is_locked' => FALSE, 'text'     => $event['event_name'], 'color' => 'rgba(255, 255, 255, 1)'))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 4,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'light_burst_fade'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 5,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[0]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 6,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'down_venetian_blinds'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 7,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[1]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 8,  'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 9,  'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[2]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 10, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'diagonal_venetian_blinds'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 11, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[3]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 12, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 13, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[4]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 14, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 15, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => TRUE,  'asset_id' => $event_photos[5]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 16, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 17, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[5]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 18, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 19, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'shot',                                                                                                                                    'content' => array(array('type' => 'image', 'is_locked' => FALSE, 'asset_id' => $event_photos[5]['id']))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 20, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'wipe'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 21, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'title',                   'background' => array('type' => 'color', 'is_locked' => TRUE, 'color' => 'rgba(0, 0, 0, 1)'),                   'content' => array(array('type' => 'text',  'is_locked' => FALSE, 'text'     => $event['event_name'], 'color' => 'rgba(255, 255, 255, 1)'))));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 22, 'type' => 'transition', 'is_locked' => TRUE,  'properties' => array('type' => 'page_flip'));
		$vt_properties_associative_array['timeline'][] = array('sequence' => 23, 'type' => 'block',      'is_locked' => FALSE, 'properties' => array('type' => 'branded_call_to_action',  'background' => array('type' => 'image', 'is_locked' => TRUE, 'asset_id' => $event_cta_background_image['id']), 'content' => array(array('type' => 'logo',  'is_locked' => TRUE,  'asset_id' => $event_logo['id']),                                            array('type' => 'text', 'is_locked' => FALSE, 'text' => $event['event_name'], 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_date_time, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $event['event_location'], 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $event['event_cta_url'], 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_url_label, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_logo_text_before, 'color' => 'rgba(255, 255, 255, 1)'), array('type' => 'text', 'is_locked' => FALSE, 'text' => $vt_cta_logo_text_after, 'color' => 'rgba(255, 255, 255, 1)'))));

		$vt_properties_associative_array['music'] = array('asset_id' => $potential_music[0]['id']);

		$vt_properties = json_encode($vt_properties_associative_array, JSON_NUMERIC_CHECK);

		unset($vt_properties_associative_array);
	} else {
		$no_error      = FALSE;
		$error_message = 'There was an error building your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. Click <a href="event.php?event_id=' . $event['id'] . '">here</a> to return to the event\'s (' . $event['event_name'] . ') page.';
	} // end of ensuring required $_POST variables are set
} // function build_generated_video_properties()


// get user information from session cookie
$current_user = get_SESSION_current_user(sprintf('http%1$s://%2$s%3$s%4$s/create-event-vt.php?event_id=%5$u', (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? 's' : ''), $_SERVER["SERVER_NAME"], ($_SERVER["SERVER_PORT"] != '80' ? ':' . $_SERVER["SERVER_PORT"] : ''), dirname($_SERVER['PHP_SELF']), (isset($_GET['event_id']) ? $_GET['event_id'] : (isset($_POST['event_id']) ? $_POST['event_id'] : ''))));

if ($no_error) {
	if (isset($_POST['save_vt'])) { // check if the create / save button was clicked - if so, create / save the data
		if (isset($_POST['vt_status']) && $_POST['vt_status'] &&
			isset($_POST['vt-block-01-content-01-image-id']) &&
			isset($_POST['vt-block-02-content-01-text']) && $_POST['vt-block-02-content-01-text'] &&
			isset($_POST['vt-block-09-content-01-image-id']) &&
			isset($_POST['vt-block-10-content-01-image-id']) &&
			isset($_POST['vt-block-11-content-01-text']) && $_POST['vt-block-11-content-01-text'] &&
			isset($_POST['vt-block-12-background-content-image-id']) && isset($_POST['vt-block-12-content-02-cta-name']) && $_POST['vt-block-12-content-02-cta-name'] && isset($_POST['vt-block-12-content-03-cta-date-time']) && $_POST['vt-block-12-content-03-cta-date-time'] && isset($_POST['vt-block-12-content-04-cta-location']) && $_POST['vt-block-12-content-04-cta-location'] && isset($_POST['vt-block-12-content-05-cta-url']) && $_POST['vt-block-12-content-05-cta-url'] && isset($_POST['vt-block-12-content-06-cta-url-label']) && isset($_POST['vt-block-12-content-07-cta-logo-text-before']) && isset($_POST['vt-block-12-content-08-cta-logo-text-after']) &&
			isset($_POST['vt-music']) && $_POST['vt-music'] &&
			isset($_POST['vt_properties']) && $_POST['vt_properties'] &&
			isset($_POST['vt_creator_id']) &&
			isset($_POST['event_id']) && $_POST['event_id'] &&
			isset($_POST['event_logo_id']) && $_POST['event_logo_id'] &&
			isset($_POST['event_narrative_sentence_01']) && $_POST['event_narrative_sentence_01'] && isset($_POST['event_narrative_sentence_02']) && isset($_POST['event_narrative_sentence_03']) && isset($_POST['event_narrative_sentence_04'])) {
			if ($_POST['vt-block-12-content-06-cta-url-label'] || $_POST['vt-block-12-content-07-cta-logo-text-before'] || $_POST['vt-block-12-content-08-cta-logo-text-after']) {
				$vt_properties = update_generated_video_properties($_POST['event_id'], $_POST['vt_properties'], $_POST['vt-block-12-content-02-cta-name'], $_POST['vt-block-01-content-01-image-id'], $_POST['vt-block-01-content-01-image-id'], $_POST['vt-block-02-content-01-text'], $_POST['vt-block-09-content-01-image-id'], $_POST['vt-block-10-content-01-image-id'], $_POST['vt-block-11-content-01-text'], $_POST['vt-block-12-background-content-image-id'], $_POST['vt-block-12-content-02-cta-name'], $_POST['vt-block-12-content-03-cta-date-time'], $_POST['vt-block-12-content-04-cta-location'], $_POST['vt-block-12-content-05-cta-url'], $_POST['vt-block-12-content-06-cta-url-label'], ($_POST['vt-block-12-content-06-cta-url-label'] ? '' : $_POST['vt-block-12-content-07-cta-logo-text-before']), ($_POST['vt-block-12-content-06-cta-url-label'] ? '' : $_POST['vt-block-12-content-08-cta-logo-text-after']), $_POST['vt-music']);
			} else {
				$no_error      = FALSE;
				$error_message = 'You must provide either call-to-action link label, text before logo button, or text after logo button.';
			} // if ($_POST['vt-block-12-content-06-cta-url-label'] || $_POST['vt-block-12-content-07-cta-logo-text-before'] || $_POST['vt-block-12-content-08-cta-logo-text-after']) else
		} else {
			$no_error      = FALSE;
			$error_message = 'Required information is missing. Please review your movie timeline.';
		} // end of ensuring required $_POST variables are set

		if ($no_error) { // if no error, create video timeline
			$vt = create_generated_video($vt_properties, '', $_POST['event_id'], $_POST['vt_creator_id']);

			if ($vt) {
				$_POST['vt_id']  = $vt['id'];
				$success_message = 'Your movie timeline has been created and your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie is being generated.';
			} else {
				$no_error      =  FALSE;
				$error_message .= ($error_message ? '<br />' : '') . 'Error creating your movie timeline.';
			} // if (!$vt) else
		} // if ($no_error)

		if ($no_error) { // if there no error, send video timeline to video engine
			// frist, link new photos to event
			link_new_photos_to_event($_POST['event_id'], $_POST['vt-block-01-content-01-image-id'], $_POST['vt-block-09-content-01-image-id'], $_POST['vt-block-10-content-01-image-id'], $_POST['vt-block-12-background-content-image-id']);

			// second, add event's narratives to the event video timeline
			$video_engine_ready_vt_properties = insert_event_narratives_to_vt_properties($vt['asset_properties'], $_POST['event_logo_id'], $_POST['event_narrative_sentence_01'], $_POST['event_narrative_sentence_02'], $_POST['event_narrative_sentence_03'], $_POST['event_narrative_sentence_04']);

			// call video engine - need to create the necessary $_POST variables adn then calling the video engine form - ideally, after calling the video engine the user is returned here with a message or to a new page with a message the video is being generated
			call_video_engine($vt['id'], $video_engine_ready_vt_properties, $current_user['id'], $current_user['user_email'], $_POST['vt-block-12-content-02-cta-name']);
		} // if ($no_error)
	} else {
		get_event(); // get additional information on the event

		if ($no_error) {
			$vt_cta_date_time        =  (array_key_exists('event_start_date', $event) && $event['event_start_date'] ? $event['event_start_date'] : '');
			$vt_cta_date_time        .= ($vt_cta_date_time && array_key_exists('event_end_date', $event) && $event['event_end_date'] && $vt_cta_date_time != $event['event_end_date']  ? ' to ' . $event['event_end_date'] : '');
			$vt_cta_url_label        =  '';
			$vt_cta_logo_text_before =  'Or Pledge Here.';
			$vt_cta_logo_text_after  =  'Thank You!';
			$vt_creator              =  $current_user['id'];
		} // if ($no_error)

		get_potential_vt_music(); // get potential video timeline music

		build_generated_video_properties(); // build the properties of the future generated video timeline
	} // if (isset($_POST['save_vt'])) else
} // if ($no_error)
?>
		<?php output_header('create-event-vt-page', 'Create Video Timeline for an Event | CrowdShot', FALSE, TRUE, TRUE, FALSE, FALSE, TRUE, FALSE); ?>

		<!-- Display messages section -->
		<section id="create-edit-messages">
			<div class="container">
				<div class="row">
					<div class="col-md-12" id="messages">
						<?php echo ($error_message ? '<div class="alert alert-danger"><p>' . $error_message . '</p></div>' : ''); ?>
						<?php echo ($warning_message ? '<div class="alert alert-warning"><p>' . $warning_message . '</p></div>' : ''); ?>
						<?php echo ($success_message ? '<div class="alert alert-success"><p>' . $success_message . '</p></div>' : ''); ?>
					</div>
				</div>
			</div>
		</section>

		<?php if ($no_error && !$vt) : ?>
		<!-- Main create event video timeline page heading section -->
		<section id="create-edit-introduction">
			<div class="container">
				<div class="row">
					<div class="col-md-12">
						<p>Let's preview what you've done so far on your <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie. You will be able to adjust the titles, call-to-action information, and your background music.<span id="shuffle-link"> Click <a href="create-event-vt.php?event_id=<?php echo ($event ? $event['id'] : ''); ?>">here</a> to shuffle photos.</span></p>
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
											<img id="vt-block-01-content-01-user-image-placeholder" data-src="holder.js/450x249/auto/text: + Upload Photo" class="img-responsive img-thumbnail vt-block-user-image-placeholder locked-content" />
											<input type="file" name="user_images[]" class="fileinput-button vt-block-user-image-fileinput-button" id="inputVTBlock01Content01SourceImage" accept="image/jpeg, image/png, image/gif" />
											<input type="hidden" name="vt-block-01-content-01-image-id" id="inputVTBlock01Content01SourceImageId" class="track-changes" />
											<div id="vt-block-01-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox hidden" href="">
												<img id="vt-block-01-content-01-user-image" class="vt-block-user-image locked-content hidden" src="" data-asset_id="" />
											</div><!-- #vt-block-01-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-01 -->
									<li id="vt-block-02" class="text-center vt-block-container vt-block-type-title locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-02-content-02" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-02-content-01-text" class="form-control vt-block-text vt-block-text-white-on-black unlocked-content" id="vt-block-02-content-01-text" placeholder="Introduce your fundraising story and why it's important" maxlength="75" required value="<?php echo (array_key_exists('event_name', $event) ? $event['event_name'] : ''); ?>" />
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
											<img id="vt-block-09-content-01-user-image-placeholder" data-src="holder.js/450x249/auto/text: + Upload Photo" class="img-responsive img-thumbnail vt-block-user-image-placeholder locked-content" />
											<input type="file" name="user_images[]" class="fileinput-button vt-block-user-image-fileinput-button" id="inputVTBlock09Content01SourceImage" accept="image/jpeg, image/png, image/gif" />
											<input type="hidden" name="vt-block-09-content-01-image-id" id="inputVTBlock09Content01SourceImageId" class="track-changes" />
											<div id="vt-block-09-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox hidden" href="">
												<img id="vt-block-09-content-01-user-image" class="vt-block-user-image locked-content hidden" src="" data-asset_id="" />
											</div><!-- #vt-block-09-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-09 -->
									<li id="vt-block-10" class="text-center vt-block-container vt-block-type-shot vt-block-subtype-user-shot unlocked-block">
										<div class="vt-block-content-wrapper">
											<img id="vt-block-10-content-01-user-image-placeholder" data-src="holder.js/450x249/auto/text: + Upload Photo" class="img-responsive img-thumbnail vt-block-user-image-placeholder locked-content" />
											<input type="file" name="user_images[]" class="fileinput-button vt-block-user-image-fileinput-button" id="inputVTBlock10Content01SourceImage" accept="image/jpeg, image/png, image/gif" />
											<input type="hidden" name="vt-block-10-content-01-image-id" id="inputVTBlock10Content01SourceImageId" class="track-changes" />
											<div id="vt-block-10-content-01" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter fancybox hidden" href="">
												<img id="vt-block-10-content-01-user-image" class="vt-block-user-image locked-content hidden" src="" data-asset_id="" />
											</div><!-- #vt-block-10-content-01 -->
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-10 -->
									<li id="vt-block-11" class="text-center vt-block-container vt-block-type-title locked-block">
										<div class="vt-block-content-wrapper">
											<div id="vt-block-11-content-01" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="text" name="vt-block-11-content-01-text" class="form-control vt-block-text unlocked-content" id="vt-block-11-content-01-text" placeholder="Introduce your fundraising event" maxlength="75" required value="<?php echo (array_key_exists('event_name', $event) ? $event['event_name'] : ''); ?>" />
											</div><!-- #vt-block-11-content-01 -->
											<span class="vt-block-type-title-instructions">Click / tap text to edit.</span>
										</div><!-- .vt-block-content-wrapper -->
									</li><!-- #vt-block-11 -->
									<li id="vt-block-12" class="text-center vt-block-container vt-block-type-branded-call-to-action locked-block">
										<div class="vt-block-background-content-wrapper">
											<img id="vt-block-12-background-content-image-placeholder" data-src="holder.js/450x249/auto/text: + Upload Photo" class="img-responsive img-thumbnail vt-block-user-image-placeholder locked-content" />
											<input type="file" name="user_images[]" id="inputVTBlock12BackgroundContentImage" class="fileinput-button vt-block-user-image-fileinput-button" accept="image/jpeg, image/png, image/gif" />
											<input type="hidden" name="vt-block-12-background-content-image-id" id="inputVTBlock12BackgroundContentImageId" class="track-changes" />
											<div class="block-background-wrapper form-section-content-upload fancybox hidden" href="">
												<img id="vt-block-12-background-content-image" class="vt-block-user-image locked-content hidden" src="" data-asset_id="" />
											</div><!-- .block-background-content-wrapper -->
										</div><!-- .vt-block-background-content-wrapper -->
										<span class="vt-block-type-title-instructions">Do you have a specific fundraising activity to promote<br />Click / tap text to edit.</span>
										<div class="vt-block-content-wrapper">
											<div id="vt-block-12-content-02" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-02-cta-name"      class="form-control vt-block-text unlocked-content" id="vt-block-12-content-02-cta-name"      placeholder="Event name"          maxlength="50" required value="<?php echo (array_key_exists('event_name', $event) ? $event['event_name'] : ''); ?>" />
											</div><!-- #vt-block-12-content-02 -->
											<div id="vt-block-12-content-03" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-03-cta-date-time" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-03-cta-date-time" placeholder="Event date and time" maxlength="50" required value="<?php echo $vt_cta_date_time; ?>" />
											</div><!-- #vt-block-12-content-03 -->
											<div id="vt-block-12-content-04" class="vt-block-content-container vt-block-content-container-type-image locked-content-contenter">
												<input type="text" name="vt-block-12-content-04-cta-location"  class="form-control vt-block-text unlocked-content" id="vt-block-12-content-04-cta-location"  placeholder="Event location"      maxlength="50" required value="<?php echo (array_key_exists('event_location', $event) ? $event['event_location'] : ''); ?>" />
											</div><!-- #vt-block-12-content-04 -->
											<span class="vt-block-type-title-instructions">Call-to-action link information</span>
											<div id="vt-block-12-content-05" class="vt-block-content-container vt-block-content-container-type-text-without-logo unlocked-content-contenter">
												<input type="url" name="vt-block-12-content-05-cta-url" class="form-control vt-block-text unlocked-content" id="vt-block-12-content-05-cta-url" placeholder="http://event.org/" maxlength="255" required value="<?php echo (array_key_exists('event_cta_url', $event) ? $event['event_cta_url'] : ''); ?>" />
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
										// Disable certain links in docs
										$('[href^=#]').click(function (hrefElement) {
											hrefElement.preventDefault()
										});


										// trap changes to input - if changed, disable preview button
										$('input.track-changes').change(function() {
											$('#btn-preview-activity-tv').addClass('disabled').removeAttr('href'); // need to remove href because of IE
										});


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


										//***** jQuery File Update for Event Photos - Start
										// Define the url to send the image data to
										//***** 20140717 - start - Reg Romero requested not to link photos to event unless the video timeline is saved
										//***** 20140717 - removed
										// var url = 'UploadHandler-user-images.php?related_object_type=event&related_object_id=<?php echo $event['id']; ?>';
										//***** 20140717 - replaced
										var url = 'UploadHandler-user-images.php';
										//***** 20140717 - end

										// bound jQuery File Upload to upload event photos input button
										$('.vt-block-user-image-fileinput-button').fileupload({
											url:      url,
											dataType: 'json',

											done: function (e, data) {
												var currentObject       = this;
												var currentObjectParent = $(currentObject).parent();
												// Show uploaded event photo files
												$.each(data.result.user_images, function (index, file) {
													if (file.url) {
														$('#' + $(currentObject).attr('id') + 'Id').val(file.asset_id);
														$(currentObjectParent).find('.vt-block-user-image-placeholder').addClass('hidden');
														$(currentObjectParent).find('.vt-block-user-image').attr('src', file.thumbnail_mobile_timelineUrl).data('asset_id', file.asset_id).removeClass('hidden');
														$(currentObjectParent).find('.fancybox').attr('href', file.url).removeClass('hidden');
														$(currentObject).addClass('hidden');
														$('#shuffle-link').addClass('hidden');
													} // if (file.url)
												}); // $.each(data.result.user_images, function (index, file)
											}
										});
										//***** jQuery File Update for Event Photos - End
									})
								</script>
							</div><!-- .row -->
						</div>
					</div><!-- .row -->

					<?php if ($potential_music && !empty($potential_music)) : ?>
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
									<input type="radio" name="vt-music" class="vt-music-radio-button" value="<?php echo $potential_song['id'] ?>"<?php echo ($potential_music_key == 0 ? ' checked' : ''); ?> />
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
								<?php endforeach; // foreach ($potential_music as $potential_music_key => $potential_song) ?>
							</div><!-- .row -->
						</div>
					</div><!-- .row -->
					<?php endif; // if ($potential_music && !empty($potential_music)) ?>

					<nav class="navbar" role="navigation">
						<div class="row row-progress">
							<div class="col-md-2 col-md-offset-10">
								<input type="hidden" name="vt_id" id="inputVTId" value="<?php echo (isset($_POST['vt_id']) ? $_POST['vt_id'] : ''); ?>" />
								<input type="hidden" name="vt_status" id="inputVTStatus" value="<?php echo ($vt ? $vt['asset_status'] : 'draft'); ?>" />
								<input type="hidden" name="vt_properties" id="inputVTProperties" value='<?php echo ($vt_properties ? str_replace("'", "&apos", $vt_properties) : ''); ?>' />
								<input type="hidden" name="vt_creator_id" id="inputVTCreatorId" value="<?php echo ($vt_creator ? $vt_creator['id'] : ''); ?>" />
								<input type="hidden" name="event_id" id="inputEventId" value="<?php echo ($event ? $event['id'] : ''); ?>" />
								<input type="hidden" name="event_logo_id" id="inputEventLogoId" value="<?php echo ($event_logo ? $event_logo['id'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_01" id="inputEventNarrativeSentence01" value="<?php echo ($event && array_key_exists('event_narrative_sentence_01', $event) ? $event['event_narrative_sentence_01'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_02" id="inputEventNarrativeSentence02" value="<?php echo ($event && array_key_exists('event_narrative_sentence_02', $event) ? $event['event_narrative_sentence_02'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_03" id="inputEventNarrativeSentence03" value="<?php echo ($event && array_key_exists('event_narrative_sentence_03', $event) ? $event['event_narrative_sentence_03'] : ''); ?>" />
								<input type="hidden" name="event_narrative_sentence_04" id="inputEventNarrativeSentence04" value="<?php echo ($event && array_key_exists('event_narrative_sentence_04', $event) ? $event['event_narrative_sentence_04'] : ''); ?>" />
								<button type="submit" class="lead btn btn-primary pull-right" name="save_vt" id="btn-save">Create my <span class="logo-text-crowd">Crowd</span><span class="logo-text-shot">Shot</span> movie now!</button>
							</div>
						</div>
					</nav>
				</form>
			</div><!-- .container -->
		</section><!-- #create-edit-form -->
		<?php endif; // if ($no_error && !$vt) ?>

		<?php output_footer(); ?>