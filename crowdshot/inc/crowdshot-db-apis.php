<?php
require_once('setup-db.php');




/**
 * This function creates a user record
 * 
 * @param string $organization_name
 * @param string $first_name
 * @param string $last_name
 * @param string $email
 * @return userObject $user returns the user record if it is successful; otherwise, FALSE
 */
function create_user($organization_name = '', $first_name = '', $last_name = '', $email = '') {
	//***** Temporary Code - Start
	global $TABLE_USER;

	if (($organization_name || ($first_name && $last_name)) && $email) {
		$user = get_user('', '', '', '', $email);

		if (!$user) {
			DB::insert($TABLE_USER, array('organization_name' => $organization_name, 'last_name' => $last_name, 'first_name' => $first_name, 'user_email' => strtolower($email), 'created_datetime' => date("Y-m-d H:i:s")));

			$user = get_user(DB::insertId());
		} // if (!$user)

		return $user;
	} else {
		return FALSE;
	} // if (($organization_name || ($first_name && $last_name)) && $email) else
	//***** Temporary Code - End
} // function create_user


/**
 * This function retrieves a user record
 * 
 * @param bigint $user_id
 * @param string $organization_name
 * @param string $first_name
 * @param string $last_name
 * @param string $email
 * @return userObject $user returns the user record if it is successful; otherwise, FALSE
 */
function get_user($user_id = '', $organization_name = '', $first_name = '', $last_name = '', $email = '') {
	//***** Temporary Code - Start
	global $TABLE_USER;

	if ($user_id || $organization_name || $first_name || $last_name || $email) {
		$select_sql = sprintf("select id, organization_name, last_name, first_name, user_email, created_datetime from %s %s %s %s %s %s %s",
							  $TABLE_USER,
							  ($user_id || $organization_name || $first_name || $last_name || $email ? "where" : ""),
							  ($user_id ? "id = " . $user_id : ""),
							  ($organization_name ? ($user_id ? "and " : "") . "organization_name = '" . $organization_name . "'" : ""),
							  ($first_name ? ($user_id || $organization_name ? "and " : "") . "first_name = '" . $first_name . "'" : ""),
							  ($last_name ? ($user_id || $organization_name || $first_name ? "and " : "") . "last_name = '" . $last_name . "'" : ""),
							  ($email ? ($user_id || $organization_name || $first_name || $last_name ? "and " : "") . "user_email = '" . $email . "'" : "")
							 );

		$user = DB::queryFirstRow($select_sql);

		return $user;
	} else {
		return FALSE;
	} // if ($user_id || $organization_name || $first_name || $last_name || $email) else
	//***** Temporary Code - End
} // function get_user


/**
 * This function formats the name of a user based on what is available (organizsation name or first name plus last name)
 * 
 * @param string $organization_name
 * @param string $first_name
 * @param string $last_name
 * @return string $formated_user_name 
 */
function format_user_name($organization_name, $first_name, $last_name) {
	return ($organization_name ? $organization_name : ($first_name ? $first_name . ' ' : '') . $last_name);
} // function format_user_name




/**
 * This function edits an asset record
 * 
 * @param bigint $asset_id
 * @param string $asset_type
 * @param string $asset_properties in JSON format
 * @param string $asset_status 'draft', 'generating', 'published', or 'unpublished'
 * @param bigint $user_id
 * @return assetObject $asset returns the asset record if it is successful; otherwise, FALSE
 */
function edit_asset($asset_id = '', $asset_type = '', $asset_properties = '', $asset_status = '', $user_id = '') {
	//***** Temporary Code - Start
	//*****		asset_url is never updated in this termporary code
	global $TABLE_ASSET;

	if ($asset_id && $user_id) {
		$update_fields = array();

		if ($asset_type)       {$update_fields['asset_type']       = $asset_type;}
		if ($asset_properties) {$update_fields['asset_properties'] = $asset_properties;}
		if ($asset_status)     {$update_fields['asset_status']     = $asset_status;}

		$update_fields['created_by'] = $user_id;

		DB::update($TABLE_ASSET, $update_fields, "id = %s", $asset_id);

		$asset = get_asset($asset_id);

		return $asset;
	} else {
		return FALSE;
	} // if ($asset_id && $user_id)
	//***** Temporary Code - End
} // function edit_asset


/**
 * This function get an asset record
 * 
 * @param bigint $asset_id
 * @return assetObject $asset returns the asset record if it is successful; otherwise, FALSE
 */
function get_asset($asset_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ASSET;

	if ($asset_id) {
		$select_sql = sprintf("select id, asset_type, asset_url, asset_properties, asset_status, created_datetime, created_by from %s where id = %s", $TABLE_ASSET, $asset_id);

		$asset = DB::queryFirstRow($select_sql);

		return $asset;
	} else {
		return FALSE;
	} // if ($asset_id) else
	//***** Temporary Code - End
} // function get_asset


/**
 * This function get all asset record satisfying the query criteria
 * 
 * @param bigint $asset_id
 * @param string $asset_type
 * @param string $asset_status 'draft', 'generating', 'published', or 'unpublished'
 * @param string $parent_type
 * @param bigint $parent_id
 * @param bigint $user_id
 * @param string $order_by 'created_datetime' or 'owner_id'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $get_how_many
 * @return assetObjects $assets returns asset records if it is successful; otherwise, FALSE
 */
function get_assets($asset_id = '', $asset_type = '', $asset_status = '', $parent_type = '', $parent_id = '', $user_id = '', $order_by = 'created_datetime', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_ASSET, $TABLE_ASSET_RELATIONSHIP;

	switch ($order_by) {
		case 'created_datetime' :
			$order_by = $TABLE_ASSET . '.created_datetime';

			break;
		case 'owner_id' :
			$order_by = $TABLE_ASSET . '.created_by';

			break;
		default :
			$order_by = $TABLE_ASSET . '.created_datetime';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select %s.id, asset_type, asset_url, asset_properties, asset_status, %s.created_datetime, %s.created_by from %s %s %s %s %s %s %s %s %s %s %s",
						  $TABLE_ASSET, $TABLE_ASSET, $TABLE_ASSET, $TABLE_ASSET,
						  ($parent_type && $parent_id ? ', ' . $TABLE_ASSET_RELATIONSHIP : ''),
						  ($asset_id || $asset_type || ($parent_type && $parent_id) || $user_id ? "where" : ""),
						  ($asset_id ? $TABLE_ASSET . ".id = " . $asset_id : ""),
						  ($asset_type ? ($asset_id ? "and " : "") . "asset_type = '" . $asset_type . "'" : ""),
						  ($asset_status ? ($asset_id || $asset_type ? "and " : "") . "asset_status = '" . $asset_status . "'" : ""),
						  ($parent_type && $parent_id ? ($asset_id || $asset_type || $asset_status ? "and " : "") . $TABLE_ASSET_RELATIONSHIP . ".asset_id = " . $TABLE_ASSET . ".id and related_object_type = '" . $parent_type . "' and related_object_id = " . $parent_id : ""),
						  ($user_id ? ($asset_id || $asset_type || $asset_status || ($parent_type && $parent_id) ? "and " : "") . $TABLE_ASSET . ".created_by = " . $user_id : ""),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$assets = DB::query($select_sql);

	return $assets;
	//***** Temporary Code - End
} // function get_assets


/**
 * This function creates a 'generated_video' asset record
 * 
 * @param string $asset_properties in JSON format
 * @param bigint $activity_id
 * @param bigint $event_id
 * @param bigint $user_id
 * @return boolean
 */
function create_generated_video($asset_properties = '', $activity_id = '', $event_id = '', $user_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ASSET, $TABLE_ASSET_RELATIONSHIP;

	if ($asset_properties && $user_id) {
		DB::insert($TABLE_ASSET, array('asset_type' => 'generated_video', 'asset_url' => '', 'asset_properties' => $asset_properties, 'asset_status' => 'draft', 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $user_id));

		$generated_video_id = DB::insertId();

		if ($activity_id) {DB::insert($TABLE_ASSET_RELATIONSHIP, array('asset_id' => $generated_video_id, 'related_object_type' => 'activity', 'related_object_id' => $activity_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $user_id));}
		if ($event_id)    {DB::insert($TABLE_ASSET_RELATIONSHIP, array('asset_id' => $generated_video_id, 'related_object_type' => 'event',    'related_object_id' => $event_id,    'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $user_id));}

		$generated_video = get_generated_videos($generated_video_id);
		$generated_video = (empty($generated_video) ? FALSE : $generated_video[0]); // because get_generated_videos returns an array of generated videos and because an unique asset id ($_GET['vt_id']) was passed in, there should only be one item in the array

		return $generated_video;
	} else {
		return FALSE;
	} // 
	//***** Temporary Code - End
} // function create_generated_video


/**
 * This function updates a 'generated_video' asset record
 * 
 * @param bigint $generated_video_id
 * @param string $asset_properties in JSON format
 * @param bigint $user_id
 * @return boolean
 */
function edit_generated_video($generated_video_id = '', $asset_properties = '', $user_id = '') {
	//***** Temporary Code - Start
	//*****		asset_url is never updated in this termporary code
	global $TABLE_ASSET;

	if ($generated_video_id && $user_id) {
		$update_fields = array();

		if ($asset_properties) {$update_fields['asset_properties'] = $asset_properties;}

		$update_fields['asset_status'] = 'draft';
		$update_fields['created_by']   = $user_id;

		DB::update($TABLE_ASSET, $update_fields, "id = %s", $generated_video_id);

		$generated_video = get_generated_videos($generated_video_id);
		$generated_video = (empty($generated_video) ? FALSE : $generated_video[0]); // because get_generated_videos returns an array of generated videos and because an unique asset id ($_GET['vt_id']) was passed in, there should only be one item in the array

		return $generated_video;
	} else {
		return FALSE;
	} // 
	//***** Temporary Code - End
} // function edit_generated_video


/**
 * This function gets published generated videos from the asset table
 *		It also gets related activity and event objects if they exist
 * 
 * @param bigint $asset_id
 * @param string $asset_status 'draft', 'generating', 'published', or 'unpublished'
 * @param string $parent_type
 * @param bigint $parent_id
 * @param bigint $user_id
 * @param string $order_by 'created_datetime' or 'owner_id'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $get_how_many
 * @return assetObjects $assets returns asset records if it is successful; otherwise, FALSE
 */
function get_generated_videos($asset_id = '', $asset_status = '', $parent_type = '', $parent_id = '', $user_id = '', $order_by = 'created_datetime', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_ASSET, $TABLE_ASSET_RELATIONSHIP;

	switch ($order_by) {
		case 'created_datetime' :
			$order_by = $TABLE_ASSET . '.created_datetime';

			break;
		case 'owner_id' :
			$order_by = $TABLE_ASSET . '.created_by';

			break;
		default :
			$order_by = $TABLE_ASSET . '.created_datetime';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select %s.id, asset_type, asset_url, asset_properties, asset_status, %s.created_datetime, %s.created_by from %s %s where asset_type = 'generated_video' %s %s %s %s %s %s %s  %s",
						  $TABLE_ASSET, $TABLE_ASSET, $TABLE_ASSET, $TABLE_ASSET,
						  ($parent_type && $parent_id ? ', ' . $TABLE_ASSET_RELATIONSHIP : ''),
						  ($asset_id || $asset_status || ($parent_type && $parent_id) || $user_id ? "and" : ""),
						  ($asset_id ? $TABLE_ASSET . ".id = " . $asset_id : ""),
						  ($asset_status ? ($asset_id ? "and " : "") . "asset_status = '" . $asset_status . "'" : ""),
						  ($parent_type && $parent_id ? ($asset_id || $asset_status ? "and " : "") . $TABLE_ASSET_RELATIONSHIP . ".asset_id = " .$TABLE_ASSET . ".id and related_object_type = '" . $parent_type . "' and related_object_id = " . $parent_id : ""),
						  ($user_id ? ($asset_id || $asset_status || ($parent_type && $parent_id) ? "and " : "") . $TABLE_ASSET . ".created_by = " . $user_id : ""),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$generated_videos = DB::query($select_sql);

	// get related objects (only related object types 'activity' and 'event') for each generated videos
	foreach ($generated_videos as $generated_video_key => $generated_video) {
		$asset_activity_relationships = get_asset_relationships('', $generated_video['id'], 'activity', '', 'related_object', 'ASC', -1);
		$asset_event_relationships    = get_asset_relationships('', $generated_video['id'], 'event', '', 'related_object', 'ASC', -1);

		$generated_videos[$generated_video_key]['activity'] = ($asset_activity_relationships && !empty($asset_activity_relationships) && array_key_exists('related_object_id', $asset_activity_relationships[0]) && $asset_activity_relationships[0]['related_object_id'] ? get_activity_details($asset_activity_relationships[0]['related_object_id']) : FALSE);
		$generated_videos[$generated_video_key]['event']    = ($asset_event_relationships && !empty($asset_event_relationships) && array_key_exists('related_object_id', $asset_event_relationships[0]) && $asset_event_relationships[0]['related_object_id'] ? get_event_details($asset_event_relationships[0]['related_object_id']) : FALSE);
	} // foreach ($generated_videos as $generated_video_key => $generated_video)

	return $generated_videos;
	//***** Temporary Code - End
} // function get_generated_videos


/**
 * This function returns the URL of a specified thumbnail size of an asset
 * 
 * @param type $asset_properties
 * @param type $desire_thumbnail_size
 * @return string $assetURLofSpecificSize
 */
function get_image_thumbnail_url($asset_properties, $desire_thumbnail_size) {
	if (is_array($asset_properties) && array_key_exists('thumbnails', $asset_properties)) {
		foreach ($asset_properties['thumbnails'] as $thumbnail) {
			if (array_key_exists('size', $thumbnail) && $thumbnail['size'] == $desire_thumbnail_size && array_key_exists('url', $thumbnail)) {
				return $thumbnail['url'];
			} // if (array_key_exists('size', $thumbnail) && $thumbnail['size'] == $desire_thumbnail_size && array_key_exists('url', $thumbnail))
		} // foreach ($asset_properties['thumbnails'] as $thumbnail)
	} // if (array_key_exists('thumbnails', $asset_properties))
	
	return ''; // asset does not have thumbnails or didn't find the size
} // function get_image_thumbnail_url($asset_properties)




/**
 * This function creates relationship between an asset record and its related object record
 * 
 * @param bigint $asset_id
 * @param string $related_object_type 'event' or 'activity'
 * @param bigint $related_object_id
 * @param bigint $user_id
 * @return boolean
 */
function create_asset_relationship($asset_id = '', $related_object_type = '', $related_object_id = '', $user_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ASSET_RELATIONSHIP;

	if ($asset_id && $related_object_type && $related_object_id && $user_id) {
		DB::insert($TABLE_ASSET_RELATIONSHIP, array('asset_id' => $asset_id, 'related_object_type' => $related_object_type, 'related_object_id' => $related_object_id, 'created_datetime' => date("Y-m-d H:i:s"), 'created_by' => $user_id));

		return TRUE;
	} else {
		return FALSE;
	}
	//***** Temporary Code - End
} // function create_asset_relationship


/**
 * This function creates relationships between assets and their related object records
 * 
 * @param array $asset_relationships array of relationships
 * @return boolean
 */
function create_asset_relationships($asset_relationships = array()) {
	//***** Temporary Code - Start
	global $TABLE_ASSET_RELATIONSHIP;

	if (!empty($asset_relationships)) {
		DB::insert($TABLE_ASSET_RELATIONSHIP, $asset_relationships);

		return TRUE;
	} else {
		return FALSE;
	}
	//***** Temporary Code - End
} // function create_asset_relationships


/**
 * This function gets the relationships of an asset
 * 
 * @param type $relationship_id
 * @param type $asset_id
 * @param type $related_object_type
 * @param type $related_object_id
 * @param type $order_by
 * @param type $order
 * @param type $get_how_many
 * @return type
 */
function get_asset_relationships($relationship_id, $asset_id, $related_object_type, $related_object_id, $order_by = 'created_datetime', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_ASSET_RELATIONSHIP;

	switch ($order_by) {
		case 'created_datetime' :
			$order_by = 'created_datetime';

			break;
		case 'related_object' :
			$order_by = 'related_object_id';

			break;
		default :
			$order_by = 'created_datetime';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select id, asset_id, related_object_type, related_object_id, created_datetime, created_by from %s %s %s %s %s %s %s %s",
						  $TABLE_ASSET_RELATIONSHIP,
						  ($relationship_id || $asset_id || $related_object_type || $related_object_id ? "where" : ""),
						  ($relationship_id ? "id = " . $relationship_id : ""),
						  ($asset_id ? ($relationship_id ? "and " : "") . "asset_id = " . $asset_id : ""),
						  ($related_object_type ? ($relationship_id || $asset_id ? "and " : "") . "related_object_type = '" . $related_object_type . "'" : ""),
						  ($related_object_id ? ($relationship_id || $asset_id || $related_object_type ? "and " : "") . "related_object_id = " . $related_object_id : ""),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$asset_relationships = DB::query($select_sql);

	return $asset_relationships;
	//***** Temporary Code - End
} // function get_asset_relationships




/**
 * This function create an event record
 * 
 * @param string $event_name
 * @param string $event_description
 * @param date $event_start_date
 * @param date $event_end_date
 * @param string $event_location
 * @param bigint $event_logo_id
 * @param bigint $event_featured_image_id
 * @param string $event_narrative_sentence_01
 * @param string $event_narrative_sentence_02
 * @param string $event_narrative_sentence_03
 * @param string $event_narrative_sentence_04
 * @param bigint $event_cta_background_image_id
 * @param string $event_cta_url
 * @param bigint $user_id
 * @return eventObject $event returns the event record if it is successful; otherwise, FALSE
 */
function create_event($event_name = '', $event_description = '', $event_start_date = '', $event_end_date = '', $event_location = '', $event_logo_id = '', $event_featured_image_id = '', $event_narrative_sentence_01 = '', $event_narrative_sentence_02 = '', $event_narrative_sentence_03 = '', $event_narrative_sentence_04 = '', $event_cta_background_image_id = '', $event_cta_url = '', $user_id = '') {
	//***** Temporary Code - Start
	global $TABLE_EVENT;

	if ($event_name && $event_start_date && $event_end_date && $event_location && $event_logo_id && $user_id) {
		DB::insert($TABLE_EVENT, array('event_name'                    => $event_name,
									   'event_description'             => $event_description,
									   'event_start_date'              => $event_start_date,
									   'event_end_date'                => $event_end_date,
									   'event_location'                => $event_location,
									   'event_logo_id'                 => $event_logo_id,
									   'event_featured_image_id'       => $event_featured_image_id,
									   'event_narrative_sentence_01'   => $event_narrative_sentence_01,
									   'event_narrative_sentence_02'   => $event_narrative_sentence_02,
									   'event_narrative_sentence_03'   => $event_narrative_sentence_03,
									   'event_narrative_sentence_04'   => $event_narrative_sentence_04,
									   'event_cta_background_image_id' => $event_cta_background_image_id,
									   'event_cta_url'                 => $event_cta_url,
									   'event_url'                     => '',
									   'created_datetime'              => date("Y-m-d H:i:s"),
									   'created_by'                    => $user_id
									  ));

		$event = get_event_details(DB::insertId());

		return $event;
	} else {
		return FALSE;
	} // if ($name && $start_date && $end_date && $location && $logo_id && $user_id) else
	//***** Temporary Code - End
} // function create_event


/**
 * This function update an event record
 * 
 * @param bigint $event_id
 * @param string $event_name
 * @param string $event_description
 * @param date $event_start_date
 * @param date $event_end_date
 * @param string $event_location
 * @param bigint $event_logo_id
 * @param bigint $event_featured_image_id
 * @param string $event_narrative_sentence_01
 * @param string $event_narrative_sentence_02
 * @param string $event_narrative_sentence_03
 * @param string $event_narrative_sentence_04
 * @param bigint $event_cta_background_image_id
 * @param string $event_cta_url
 * @param bigint $user_id
 * @return eventObject $event returns the event record if it is successful; otherwise, FALSE
 */
function edit_event($event_id = '', $event_name = '', $event_description = '', $event_start_date = '', $event_end_date = '', $event_location = '', $event_logo_id = '', $event_featured_image_id = '', $event_narrative_sentence_01 = '', $event_narrative_sentence_02 = '', $event_narrative_sentence_03 = '', $event_narrative_sentence_04 = '', $event_cta_background_image_id = '', $event_cta_url = '', $user_id = '') {
	//***** Temporary Code - Start
	//*****		event_url is never updated in this termporary code
	global $TABLE_EVENT;

	if ($event_id && $user_id) {
		$update_fields = array();

		if ($event_name)                    {$update_fields['event_name']                    = $event_name;}
		if ($event_description)             {$update_fields['event_description']             = $event_description;}
		if ($event_start_date)              {$update_fields['event_start_date']              = $event_start_date;}
		if ($event_end_date)                {$update_fields['event_end_date']                = $event_end_date;}
		if ($event_location)                {$update_fields['event_location']                = $event_location;}
		if ($event_logo_id)                 {$update_fields['event_logo_id']                 = $event_logo_id;}
		if ($event_featured_image_id)       {$update_fields['event_featured_image_id']       = $event_featured_image_id;}
		if ($event_narrative_sentence_01)   {$update_fields['event_narrative_sentence_01']   = $event_narrative_sentence_01;}
		if ($event_narrative_sentence_02)   {$update_fields['event_narrative_sentence_02']   = $event_narrative_sentence_02;}
		if ($event_narrative_sentence_03)   {$update_fields['event_narrative_sentence_03']   = $event_narrative_sentence_03;}
		if ($event_narrative_sentence_04)   {$update_fields['event_narrative_sentence_04']   = $event_narrative_sentence_04;}
		if ($event_cta_background_image_id) {$update_fields['event_cta_background_image_id'] = $event_cta_background_image_id;}
		if ($event_cta_url)                 {$update_fields['event_cta_url']                 = $event_cta_url;}

		$update_fields['last_updated_datetime'] = date("Y-m-d H:i:s");
		$update_fields['last_updated_by']       = $user_id;

		DB::update($TABLE_EVENT, $update_fields, "id = %s", $event_id);

		$event = get_event_details($event_id);

		return $event;
	} else {
		return FALSE;
	} // if ($event_id && $user_id) else
	//***** Temporary Code - End
} // function edit_event


/**
 * This function get an event record
 * 
 * @param bigint $event_id
 * @return eventObject $event returns the event record if it is successful; otherwise, FALSE
 */
function get_event_details($event_id = '') {
	//***** Temporary Code - Start
	global $TABLE_EVENT;

	if ($event_id) {
		$select_sql = sprintf("select id, event_name, event_description, event_start_date, event_end_date, event_location, event_logo_id, event_featured_image_id, event_narrative_sentence_01, event_narrative_sentence_02, event_narrative_sentence_03, event_narrative_sentence_04, event_cta_background_image_id, event_cta_url, event_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s where id = %s", $TABLE_EVENT, $event_id);

		$event = DB::queryFirstRow($select_sql);

		return $event;
	} else {
		return FALSE;
	} // if ($event_id) else
	//***** Temporary Code - End
} // function get_event_details


/**
 * This function gets event records
 * 
 * @param bigint $event_id
 * @param bigint $user_id
 * @param string $order_by 'start_date', 'created_datetime', or 'name'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $get_how_many
 * @return array $events an array of event records if it is successful; otherwise, FALSE
 */
function get_events($event_id = '', $user_id = '', $order_by = 'created_datetime', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_EVENT;

	switch ($order_by) {
		case 'start_date' :
			$order_by = 'event_start_date';

			break;
		case 'created_datetime' :
			$order_by = 'created_datetime';

			break;
		case 'name' :
			$order_by = 'event_name';

			break;
		default :
			$order_by = 'created_datetime';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select id, event_name, event_description, event_start_date, event_end_date, event_location, event_logo_id, event_featured_image_id, event_narrative_sentence_01, event_narrative_sentence_02, event_narrative_sentence_03, event_narrative_sentence_04, event_cta_background_image_id, event_cta_url, event_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s %s %s %s %s %s %s",
						  $TABLE_EVENT,
						  ($event_id || $user_id ? "where" : ""),
						  ($event_id ? "id = " . $event_id : ""),
						  ($user_id ? ($event_id ? "and " : "") . "created_by = " . $user_id : ""),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$events = DB::query($select_sql);

	return $events;
	//***** Temporary Code - End
} // function get_events


/**
 * This function gets the latest event records
 * 
 * @param bigint $user_id
 * @param string $from_date_field 'start_date' or 'created_datetime'
 * @param string $order_by 'start_date', 'created_datetime', or 'name'
 * @param string $order 'ASC', 'DESC', or 'RAND'
 * @param int $get_how_many
 * @return array $events an array of event records if it is successful; otherwise, FALSE
 */
function get_latest_events($user_id = '', $from_date_field = '', $order_by = '', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_EVENT;

	switch ($from_date_field) {
		case 'start_date' :
			$latest_from_field = 'event_start_date';

			break;
		case 'created_datetime' :
			$latest_from_field = 'created_datetime';

			break;
	} // switch ($from_date_field)

	switch ($order_by) {
		case 'start_date' :
			$order_by = 'event_start_date';

			break;
		case 'created_datetime' :
			$order_by = 'created_datetime';

			break;
		case 'name' :
			$order_by = 'event_name';

			break;
		default :
			$order_by = 'event_start_date';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select id, event_name, event_description, event_start_date, event_end_date, event_location, event_logo_id, event_featured_image_id, event_narrative_sentence_01, event_narrative_sentence_02, event_narrative_sentence_03, event_narrative_sentence_04, event_cta_background_image_id, event_cta_url, event_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s %s %s %s %s %s %s",
						  $TABLE_EVENT,
						  ($user_id || $from_date_field ? 'where' : ''),
						  ($user_id ? "created_by = " . $user_id : ""),
						  ($from_date_field ? ($user_id ? "and " : "") . $latest_from_field . ' >= curdate()' : ''),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$events = DB::query($select_sql);

	return $events;
	//***** Temporary Code - End
} // function get_latest_events




/**
 * This function creates an activity
 * 
 * @param bigint $event_id
 * @param string $activity_name
 * @param date $activity_start_date
 * @param date $activity_end_date
 * @param string $activity_location
 * @param bigint $activity_featured_image_id
 * @param bigint $activity_cta_background_image_id
 * @param string $activity_cta_url
 * @param bigint $user_id
 * @return activityObject $activity activity record (plus the associated activity and or event record) if it is successful; otherwise, FALSE
 */
function create_activity($event_id = '', $activity_name = '', $activity_start_date = '', $activity_end_date = '', $activity_location = '', $activity_featured_image_id = '', $activity_cta_background_image_id = '', $activity_cta_url = '', $user_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ACTIVITY;

	if ($event_id && $activity_name && $activity_start_date && $activity_end_date && $activity_location && $user_id) {
		DB::insert($TABLE_ACTIVITY, array('event_id' => $event_id,
										  'activity_name' => $activity_name,
										  'activity_start_date' => $activity_start_date,
										  'activity_end_date' => $activity_end_date,
										  'activity_location' => $activity_location,
										  'activity_featured_image_id' => $activity_featured_image_id,
										  'activity_cta_background_image_id' => $activity_cta_background_image_id,
										  'activity_cta_url' => $activity_cta_url,
										  'activity_url' => '',
										  'created_datetime' => date("Y-m-d H:i:s"),
										  'created_by' => $user_id
										 ));

		$activity = get_activity_details(DB::insertId());

		return $activity;
	} else {
		return FALSE;
	} // if ($event_id && $activity_name && $activity_start_date && $activity_end_date && $activity_location && $user_id) else
	//***** Temporary Code - End
} // function create_activity


/**
 * This function updates an activity
 * 
 * @param bigint $activity_id
 * @param bigint $event_id
 * @param string $activity_name
 * @param date $activity_start_date
 * @param date $activity_end_date
 * @param string $activity_location
 * @param bigint $activity_featured_image_id
 * @param bigint $activity_cta_background_image_id
 * @param string $activity_cta_url
 * @param bigint $user_id
 * @return activityObject $activity activity record (plus the associated activity and or event record) if it is successful; otherwise, FALSE
 */
function edit_activity($activity_id = '', $event_id = '', $activity_name = '', $activity_start_date = '', $activity_end_date = '', $activity_location = '', $activity_featured_image_id = '', $activity_cta_background_image_id = '', $activity_cta_url = '', $user_id = '') {
	//***** Temporary Code - Start
	//*****		activity_url is never updated in this termporary code
	global $TABLE_ACTIVITY;

	if ($activity_id && $user_id) {
		$update_fields = array();

		if ($event_id)                         {$update_fields['event_id']                         = $event_id;}
		if ($activity_name)                    {$update_fields['activity_name']                    = $activity_name;}
		if ($activity_start_date)              {$update_fields['activity_start_date']              = $activity_start_date;}
		if ($activity_end_date)                {$update_fields['activity_end_date']                = $activity_end_date;}
		if ($activity_location)                {$update_fields['activity_location']                = $activity_location;}
		if ($activity_featured_image_id)       {$update_fields['activity_featured_image_id']       = $activity_featured_image_id;}
		if ($activity_cta_background_image_id) {$update_fields['activity_cta_background_image_id'] = $activity_cta_background_image_id;}
		if ($activity_cta_url)                 {$update_fields['activity_cta_url']                 = $activity_cta_url;}

		$update_fields['last_updated_datetime'] = date("Y-m-d H:i:s");
		$update_fields['last_updated_by']       = $user_id;

		DB::update($TABLE_ACTIVITY, $update_fields, "id = %s", $activity_id);

		$activity = get_activity_details($activity_id);

		return $activity;
	} else {
		return FALSE;
	} // if ($activity_id && $user_id) else
	//***** Temporary Code - End
} // function edit_activity


/**
 * This function get an activity
 * 
 * @param bigint $activity_id
 * @return activityObject $activity activity record (plus the associated activity and or event record) if it is successful; otherwise, FALSE
 */
function get_activity_details($activity_id = '') {
	//***** Temporary Code - Start
	global $TABLE_ACTIVITY;

	if ($activity_id) {
		$select_sql = sprintf("select id, event_id, activity_name, activity_start_date, activity_end_date, activity_location, activity_featured_image_id, activity_cta_background_image_id, activity_cta_url, activity_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s where id = %s", $TABLE_ACTIVITY, $activity_id);

		$activity = DB::queryFirstRow($select_sql);

		return $activity;
	} else {
		return FALSE;
	} // if ($activity_id) else
	//***** Temporary Code - End
} // function get_activity_details


/**
 * This function get activities records
 * 
 * @param bigint $activity_id
 * @param bigint $event_id
 * @param bigint $user_id
 * @param string $order_by 'start_date', 'created_datetime', or 'name'
 * @param string $order 'ASC', DESC', or 'RAND'
 * @param int $get_how_many
 * @return activityObject $activity activity record (plus the associated activity and or event record) if it is successful; otherwise, FALSE
 */
function get_activities($activity_id = '', $event_id = '', $user_id = '', $order_by = 'created_datetime', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_ACTIVITY;

	switch ($order_by) {
		case 'start_date' :
			$order_by = 'activity_start_date';

			break;
		case 'created_datetime' :
			$order_by = 'created_datetime';

			break;
		case 'name' :
			$order_by = 'activity_name';

			break;
		default :
			$order_by = 'created_datetime';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select id, event_id, activity_name, activity_start_date, activity_end_date, activity_location, activity_featured_image_id, activity_cta_background_image_id, activity_cta_url, activity_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s %s %s %s %s %s %s %s",
						  $TABLE_ACTIVITY,
						  ($activity_id || $event_id || $user_id ? "where" : ""),
						  ($activity_id ? "id = " . $activity_id : ""),
						  ($event_id ? ($activity_id ? "and " : "") . "event_id = " . $event_id : ""),
						  ($user_id ? ($activity_id || $event_id ? "and " : "") . "created_by = " . $user_id : ""),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$activities = DB::query($select_sql);

	return $activities;
	//***** Temporary Code - End
} // function get_activities


/**
 * This function get the latest activities records based on the specified date field
 * 
 * @param bigint $event_id
 * @param bigint $user_id
 * @param string $from_date_field 'start_date' or 'created_datetime'
 * @param string $order_by 'start_date', 'created_datetime', or 'name'
 * @param string $order 'ASC', DESC', or 'RAND'
 * @param int $get_how_many
 * @return activityObject $activity activity record (plus the associated activity and or event record) if it is successful; otherwise, FALSE
 */
function get_latest_activities($event_id = '', $user_id = '', $from_date_field = '', $order_by = '', $order = 'DESC', $get_how_many = -1) {
	//***** Temporary Code - Start
	global $TABLE_ACTIVITY;

	switch ($from_date_field) {
		case 'start_date' :
			$latest_from_field = 'activity_start_date';

			break;
		case 'created_datetime' :
			$latest_from_field = 'created_datetime';

			break;
	} // switch ($from_date_field)

	switch ($order_by) {
		case 'start_date' :
			$order_by = 'activity_start_date';

			break;
		case 'created_datetime' :
			$order_by = 'created_datetime';

			break;
		case 'name' :
			$order_by = 'event_name';

			break;
		default :
			$order_by = 'activity_start_date';

			break;
	} // switch ($order_by)

	$select_sql = sprintf("select id, event_id, activity_name, activity_start_date, activity_end_date, activity_location, activity_featured_image_id, activity_cta_background_image_id, activity_cta_url, activity_url, created_datetime, created_by, last_updated_datetime, last_updated_by from %s where %s %s %s %s %s %s",
						  $TABLE_ACTIVITY,
						  ($event_id ? "event_id = " . $event_id : ""),
						  ($user_id ? ($event_id ? "and " : "") . "created_by = " . $user_id : ""),
						  ($from_date_field ? ($event_id || $user_id ? "and " : "") . $latest_from_field . ' >= curdate()' : ''),
						  ($order != 'RAND' && $order_by ? "order by " . $order_by . " " : ""),
						  ($order == 'RAND' ? "order by RAND()" : ($order_by ? $order : "")),
						  ($get_how_many > 0 ? "limit " . $get_how_many : "")
						 );

	$events = DB::query($select_sql);

	return $events;
	//***** Temporary Code - End
} // function get_latest_activities
?>