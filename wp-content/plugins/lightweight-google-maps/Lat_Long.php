<?php
/* ==================================================
 *   Lat_Long Class
     version 1.4.0
   ================================================== */

define('GEO_META_FIELD_NAME', 'Lat_Long');
define('GEO_SAME_LOC_THRESHOLD', 0.0001);
define('GEO_MAX_BOUND_DIFFERENCE', 1.0);

class Lat_Long {

// ==================================================
// static 
function get_LatLon($post_id = 0) {
	if (! $post_id) {
		global $post;
		$post_id = $post->ID;
	}
	$locs = array();
	$meta_values = get_post_meta($post_id, GEO_META_FIELD_NAME);
	if (! $meta_values) {
		return NULL;
	}
	foreach ($meta_values as $value) {
		$lat_long = array_map('floatval', split(',', $value));
		$loc = array('lat' => $lat_long[0], 'lon' => $lat_long[1]);
		if (isset($lat_long[2])) {
			$loc['alt'] = $lat_long[2];
		}
		$locs[] = $loc;
	}
	return $locs;
}

// ==================================================
// static 
function is_same($point1, $point2) {
	return (abs($point1['lat'] - $point2['lat']) < GEO_SAME_LOC_THRESHOLD && abs($point1['lon'] - $point2['lon']) < GEO_SAME_LOC_THRESHOLD);
}

// ==================================================
// static 
function center($bounds) {
	return array('lat' => ($bounds[0] + $bounds[2]) / 2, 'lon' => ($bounds[1] + $bounds[3]) / 2);
}

// ==================================================
// static 
function posts_in_bounds($bounds) {
	if (count($bounds) != 4 || abs($bounds[0] - $bounds[2]) > GEO_MAX_BOUND_DIFFERENCE || abs($bounds[1] - $bounds[3]) > GEO_MAX_BOUND_DIFFERENCE ) {
		return NULL;
	}
	$south_lat = floatval(min($bounds[0], $bounds[2]));
	$north_lat = floatval(max($bounds[0], $bounds[2]));
	$west_lon  = floatval(min($bounds[1], $bounds[3]));
	$east_lon  = floatval(max($bounds[1], $bounds[3]));
	global $wpdb;
	$locations = $wpdb->get_results("SELECT p.ID AS id, p.post_status AS status, p.post_type AS type, m.meta_value AS value FROM {$wpdb->posts} AS p, {$wpdb->postmeta} AS m WHERE p.ID = m.post_id AND m.meta_key = '" . GEO_META_FIELD_NAME . "' AND SUBSTRING_INDEX(m.meta_value,',',1) >= $south_lat AND SUBSTRING_INDEX(m.meta_value,',',1) <= $north_lat AND SUBSTRING_INDEX(SUBSTRING_INDEX(m.meta_value,',',2),',',-1) >= $west_lon AND SUBSTRING_INDEX(SUBSTRING_INDEX(m.meta_value,',',2),',',-1) <= $east_lon ORDER BY p.post_date DESC, m.meta_id ASC");
	$posts_w_loc = array();
	foreach ($locations as $l) {
		$posts_w_loc[$l->id]['status'] = $l->status;
		$posts_w_loc[$l->id]['type'] = $l->type;
		$lat_long = array_map('floatval', split(',', $l->value));
		$loc = array('lat' => $lat_long[0], 'lon' => $lat_long[1]);
		if (isset($lat_long[2])) {
			$loc['alt'] = $lat_long[2];
		}
		$posts_w_loc[$l->id][] = $loc;
	}
	return $posts_w_loc;
}

// ===== End of class ==========
}
?>