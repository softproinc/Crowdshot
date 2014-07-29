<?php
session_start();

if (isset($_SESSION['event_photos']) && is_array($_SESSION['event_photos']) && !empty($_SESSION['event_photos'])) {
	$event_photos            = $_SESSION['event_photos'];
	$event_photos_JSON_items = array();

	foreach ($event_photos as $event_photo) {
		$event_photo_properties = json_decode($event_photo['asset_properties'], TRUE);

		if (array_key_exists('thumbnails', $event_photo_properties)) {
			$event_photo_thumbnails = array();

			foreach ($event_photo_properties['thumbnails'] as $event_photo_thumbnail) {
				if (array_key_exists('size', $event_photo_thumbnail) && array_key_exists('url', $event_photo_thumbnail)) {
					switch ($event_photo_thumbnail['size']) {
						case 'desktop_myshots' :
							$event_photo_thumbnail_size = 'desktopThumbnailUrl';

							break;
						case 'mobile_myshots' :
							$event_photo_thumbnail_size = 'mobileThumbnailUrl';

							break;
						case 'desktop_timeline' :
							$event_photo_thumbnail_size = 'desktopTimelineUrl';

							break;
						case 'mobile_timeline' :
							$event_photo_thumbnail_size = 'mobileTimelineUrl';

							break;
						case 'desktop_album_cover' :
							$event_photo_thumbnail_size = 'desktopAlbumCoverUrl';

							break;
						case 'mobile_album_cover' :
							$event_photo_thumbnail_size = 'mobileAlbumCoverUrl';

							break;
						case 'desktop_featured_image' :
							$event_photo_thumbnail_size = 'desktopFeaturedImageUrl';

							break;
						case 'mobile_featured_image' :
							$event_photo_thumbnail_size = 'mobileFeaturedImageUrl';

							break;
					} // switch ($event_photo_thumbnail['size'])

					$event_photo_thumbnails[] = '"' . $event_photo_thumbnail_size . '":"' . $event_photo_thumbnail['url'] . '"';
				} // if (array_key_exists('size', $event_photo_thumbnail) && array_key_exists('url', $event_photo_thumbnail))
			} // foreach ($event_photo_properties['thumbnails'] as $event_photo_thumbnail)

			$event_photo_thumbnails_JSON = implode(',', $event_photo_thumbnails);
		} else {
			$event_photo_thumbnails_JSON = '';
		} // if (array_key_exists('thumbnails', $event_photo_properties)) else

		$event_photos_JSON_items[] = '{"shotUrl":"' . $event_photo['asset_url'] . '",' . $event_photo_thumbnails_JSON . ',"type":"event-shot","assetId":"' . $event_photo['id'] . '"' . (isset($_SESSION['event_featured_image']) && $_SESSION['event_featured_image']['id'] == $event_photo['id'] ? ',"featureShot":"yes"' : '') . (isset($_SESSION['event_cta_background_image']) && $_SESSION['event_cta_background_image']['id'] == $event_photo['id'] ? ',"ctaBackgroundImage":"yes"' : '') . '}';
	} // foreach ($event_photos as $event_photo)

	$event_photos_JSON = '{"shots":[' . implode(',', $event_photos_JSON_items) . ']}';
} else {
	$event_photos_JSON = '{"shots":[]}';
} // if (isset($_SESSION['event_photos']) && is_array($_SESSION['event_photos']) && !empty($_SESSION['event_photos']))) else

echo $event_photos_JSON;
?>