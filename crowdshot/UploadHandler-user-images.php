<?php
/*
 * jQuery File Upload Plugin PHP Example 5.14
 * https://github.com/blueimp/jQuery-File-Upload
 *
 * Copyright 2010, Sebastian Tschan
 * https://blueimp.net
 *
 * Licensed under the MIT license:
 * http://www.opensource.org/licenses/MIT
 */

error_reporting(E_ALL | E_STRICT);
require('UploadHandler.php');
if (isset($_GET['related_object_type']) && isset($_GET['related_object_id']) && ($_GET['related_object_type'] == 'event' || $_GET['related_object_type'] == 'activity') && $_GET['related_object_id']) {
	$upload_user_image_handler = new UploadHandler(array('param_name' => 'user_images',
														 'asset_type' => 'user_image',
														 'related_object_type' => $_GET['related_object_type'],
														 'related_object_id' => $_GET['related_object_id'],
														 'image_versions' => array('thumbnail_desktop_myshots' => array('crop' => true, 'max_width' => 300, 'max_height' => 300),
																				   'thumbnail_mobile_myshots' => array('crop' => true, 'max_width' => 480, 'max_height' => 480),
																				   'thumbnail_desktop_timeline' => array('max_width' => 160, 'max_height' => 90),
																				   'thumbnail_mobile_timeline' => array('max_width' => 480, 'max_height' => 270),
																				   'thumbnail_desktop_album_cover' => array('crop' => true, 'max_width' => 360, 'max_height' => 203),
																				   'thumbnail_mobile_album_cover' => array('crop' => true, 'max_width' => 480, 'max_height' => 270),
																				   'thumbnail_desktop_featured_image' => array('max_width' => 600, 'max_height' => 99999),
																				   'thumbnail_mobile_featured_image' => array('max_width' => 480, 'max_height' => 99999))
														));
} else {
	$upload_user_image_handler = new UploadHandler(array('param_name' => 'user_images',
														 'asset_type' => 'user_image',
														 'image_versions' => array('thumbnail_desktop_myshots' => array('crop' => true, 'max_width' => 300, 'max_height' => 300),
																				   'thumbnail_mobile_myshots' => array('crop' => true, 'max_width' => 480, 'max_height' => 480),
																				   'thumbnail_desktop_timeline' => array('max_width' => 160, 'max_height' => 90),
																				   'thumbnail_mobile_timeline' => array('max_width' => 480, 'max_height' => 270),
																				   'thumbnail_desktop_album_cover' => array('crop' => true, 'max_width' => 360, 'max_height' => 203),
																				   'thumbnail_mobile_album_cover' => array('crop' => true, 'max_width' => 480, 'max_height' => 270),
																				   'thumbnail_desktop_featured_image' => array('max_width' => 600, 'max_height' => 99999),
																				   'thumbnail_mobile_featured_image' => array('max_width' => 480, 'max_height' => 99999))
														));
}
