<?php
session_start();

if (isset($_SESSION['activity_photos']) && is_array($_SESSION['activity_photos']) && !empty($_SESSION['activity_photos'])) {
	$activity_photos            = $_SESSION['activity_photos'];
	$activity_photos_JSON_items = array();

	foreach ($activity_photos as $activity_photo) {
		$activity_photo_properties = json_decode($activity_photo['asset_properties'], TRUE);

		if (array_key_exists('thumbnails', $activity_photo_properties)) {
			$activity_photo_thumbnails = array();

			foreach ($activity_photo_properties['thumbnails'] as $activity_photo_thumbnail) {
				if (array_key_exists('size', $activity_photo_thumbnail) && array_key_exists('url', $activity_photo_thumbnail)) {
					switch ($activity_photo_thumbnail['size']) {
						case 'desktop_myshots' :
							$activity_photo_thumbnail_size = 'desktopThumbnailUrl';

							break;
						case 'mobile_myshots' :
							$activity_photo_thumbnail_size = 'mobileThumbnailUrl';

							break;
						case 'desktop_timeline' :
							$activity_photo_thumbnail_size = 'desktopTimelineUrl';

							break;
						case 'mobile_timeline' :
							$activity_photo_thumbnail_size = 'mobileTimelineUrl';

							break;
						case 'desktop_album_cover' :
							$activity_photo_thumbnail_size = 'desktopAlbumCoverUrl';

							break;
						case 'mobile_album_cover' :
							$activity_photo_thumbnail_size = 'mobileAlbumCoverUrl';

							break;
						case 'desktop_featured_image' :
							$activity_photo_thumbnail_size = 'desktopFeaturedImageUrl';

							break;
						case 'mobile_featured_image' :
							$activity_photo_thumbnail_size = 'mobileFeaturedImageUrl';

							break;
					} // switch ($activity_photo_thumbnail['size'])

					$activity_photo_thumbnails[] = '"' . $activity_photo_thumbnail_size . '":"' . $activity_photo_thumbnail['url'] . '"';
				} // if (array_key_exists('size', $activity_photo_thumbnail) && array_key_exists('url', $activity_photo_thumbnail))
			} // foreach ($activity_photo_properties['thumbnails'] as $activity_photo_thumbnail)

			$activity_photo_thumbnails_JSON = implode(',', $activity_photo_thumbnails);
		} else {
			$activity_photo_thumbnails_JSON = '';
		} // if (array_key_exists('thumbnails', $activity_photo_properties)) else

		$activity_photos_JSON_items[] = '{"shotUrl":"' . $activity_photo['asset_url'] . '",' . $activity_photo_thumbnails_JSON . ',"type":"activity-shot","assetId":"' . $activity_photo['id'] . '"' . (isset($_SESSION['activity_featured_image']) && $_SESSION['activity_featured_image']['id'] == $activity_photo['id'] ? ',"featureShot":"yes"' : '') . (isset($_SESSION['activity_cta_background_image']) && $_SESSION['activity_cta_background_image']['id'] == $activity_photo['id'] ? ',"ctaBackgroundImage":"yes"' : '') . '}';
	} // foreach ($activity_photos as $activity_photo)

	$activity_photos_JSON = '{"shots":[' . implode(',', $activity_photos_JSON_items) . ']}';
} else {
	$activity_photos_JSON = '{"shots":[]}';
} // if (isset($_SESSION['activity_photos']) && is_array($_SESSION['activity_photos']) && !empty($_SESSION['activity_photos'])) else

echo $activity_photos_JSON;
?>