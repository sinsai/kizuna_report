<?php

/* ==================================================
 * locations.php 1.4.0
 * Location info output API for Lightweight Google Maps
   ================================================== */

$wpload_error = 'Could not read locations because custom WP_PLUGIN_DIR is set.';
require dirname(__FILE__) . '/wp-load.php';
require_once dirname(__FILE__) . '/Lat_Long.php';
define('INFOWINDOW_DATE_FORMAT', 'Y-m-d');
LWGM_Locations::init();

/* ==================================================
 *   LWGM_Locations Class
   ================================================== */

class LWGM_Locations {
	var $markers = array();
	var $name;

// ==================================================
// static 
function init() {
	if (! defined('LWGM_API_FILENAME')) {
		// The plugin is disabled.
		header('HTTP/1.0 403 Forbidden');
		exit();
	}
	if (! get_option('lw_fixed_map_page_id')) {
		LWGM_Locations::output_error('The static map page does not exists.');
		exit();
	}
	if (! isset($_GET['format'])) {
		LWGM_Locations::output_error('Data format does not specified.');
		exit();
	}
	
	$locations = new LWGM_Locations();
	switch ($_GET['format']) {
	case 'xml':
		$locations->output_xml();
		break;
	case 'kml':
		$locations->output_kml();
		break;
	default:
		LWGM_Locations::output_error('Data format does not specified.');
	}
	exit();
}

// ==================================================
// public 
function LWGM_Locations() {
	$this->__construct();
}

// static 
function __construct() {
	$names = array();
	if (isset($_GET['bounds'])) {
		$bounds = explode(',', $_GET['bounds']);
		$posts_w_loc = Lat_Long::posts_in_bounds($bounds);
		$center = Lat_Long::center($bounds);
		$names[] = sprintf(__('Around %s', 'lw_googlemaps'), "{$center['lat']},{$center['lon']}");
	} else {
		$posts_w_loc = NULL;
	}
	$query = '';
	if (isset($_GET['category']) && ($cat_id = intval($_GET['category']))) {
		$query .= '&post_type=any&cat=' . $cat_id;
		$names[] = __('Category:', 'lw_googlemaps') . get_cat_name($cat_id);
		add_filter('posts_where', array($this, 'remove_post_type_query'));
	}
	if (isset($_GET['tag'])) {
		$query .= '&post_type=any&tag=' . urlencode($_GET['tag']);
		$names[] = __('Tag:', 'lw_googlemaps') . wp_specialchars($_GET['tag']);
		add_filter('posts_where', array($this, 'remove_post_type_query'));
	}
	if (isset($_GET['date'])) {
		list($year, $month, $date) = array_map('intval', explode('-', $_GET['date']));
		$query .= sprintf('&year=%d&monthnum=%d&day=%d', $year, $month, $date);
		$names[] = __('Date:', 'lw_googlemaps') . $_GET['date']; 
	}
	$num = isset($_GET['recent']) ? intval($_GET['recent']) : 0;
	if ($num) {
		$names[] .= sprintf(__('Recent %d points', 'lw_googlemaps'), $num);
	}

	$loc = array();
	if ($query || $num) {
		for ($page = 1 ; $page <= 100000 ; $page++) {
			$posts = new WP_Query("paged=$page$query");
			if (! $posts->have_posts() || $page > 100000) {
				break;
			}
			while ($posts->have_posts()) : $posts->the_post();
				if ($posts_w_loc) {
					global $post;
					$latlongs = @$posts_w_loc[$post->ID];
				} else {
					$latlongs = Lat_Long::get_LatLon($post->ID);
				}
				if ($latlongs) {
					foreach ($latlongs as $index => $l) {
					if (is_numeric($index) && isset($l['lat'])) {
							$loc[] = $this->post_info($l['lat'], $l['lon']);
						}
					}
					if ($num > 0 && count($loc) >= $num) {
						break 2;
					}
				}
			endwhile;
		}
	} elseif ($posts_w_loc) {
		global $post;
		foreach ($posts_w_loc as $id => $latlongs) {
			if (@$latlongs['type'] == 'page' || @$latlongs['status'] == 'static') {
				$posts = new WP_Query("page_id=$id");
			} else {
				$posts = new WP_Query("p=$id");
			}
			if ($posts->have_posts()) {
				$posts->the_post();
				foreach ($latlongs as $index => $l) {
					if (is_numeric($index) && isset($l['lat'])) {
						$loc[] = $this->post_info($l['lat'], $l['lon']);
					}
				}
			}
		}
	}
	$this->markers = $loc;
	$this->name = $names ? implode('/', $names) : NULL;
	return;
}

// ==================================================
// public 
function remove_post_type_query($where) {
	if (Lightweight_Google_Maps::check_wp_version('2.1', '<')) {
		$where = preg_replace("/\(post_status = '[a-z]+'/", "(post_status != 'attachment'", $where); // WP 2.0
	} elseif (Lightweight_Google_Maps::check_wp_version('2.3', '<')) {
		$where = preg_replace("/\(post_type = 'any' AND /", '(', $where); // WP 2.1-2.2
	} else {
		$where = preg_replace("/ AND post_type = 'any'/", '', $where); // WP 2.3
	}
	return $where;
}

// ==================================================
// private 
function post_info($lat, $lon) {
	$title = get_the_title();
	$link = get_permalink();
	$datetime = get_the_time('U');
	$excerpt = preg_replace('/^\s+/', '', get_the_excerpt());
	$excerpt = preg_replace('/\n.*$/m', '', $excerpt);
	return compact('lat', 'lon', 'title', 'link', 'datetime', 'excerpt');
}

// ==================================================
// public 
function output_xml() {
	$encoding = get_option('blog_charset');
	header("Content-Type: application/xml; charset=$encoding");
	echo <<< E__O__T
<?xml version="1.0" encoding="$encoding"?>
<markers>

E__O__T;
	while ($m = array_shift($this->markers)) {
		$m = array_map('wp_specialchars', $m);
		extract($m);
		$date = date(INFOWINDOW_DATE_FORMAT, $datetime);
		echo <<<E__O__T
<marker>
<lat>$lat</lat>
<lon>$lon</lon>
<title>$title</title>
<link>$link</link>
<date>$date</date>
<excerpt>$excerpt</excerpt>
</marker>

E__O__T;
	}
	echo <<< E__O__T
</markers>

E__O__T;
	return;
}

// ==================================================
// public 
function output_kml() {
	$encoding = get_option('blog_charset');
	$name = wp_specialchars(mb_convert_encoding($this->name, 'UTF-8', $encoding));
	header("Content-Type: application/vnd.google-earth.kml+xml; charset=utf-8");
	echo <<< E__O__T
<?xml version="1.0" encoding="UTF-8"?>
<kml xmlns="http://earth.google.com/kml/2.1">
<Folder>
	<name>$name</name>

E__O__T;
	while ($m = array_pop($this->markers)) {
		if ($endocing != 'UTF-8') {
			$m_temp = array();
			foreach($m as $k => $v) {
				$m_temp[$k] = mb_convert_encoding($v, 'UTF-8', $encoding);
			}
			$m = array_map('wp_specialchars', $m_temp);
		} else {
			$m = array_map('wp_specialchars', $m);
		}
		extract($m);
		$timestamp = date('Y-m-d', $datetime) . 'T' . date('H:i:s', $datetime);
		echo <<<E__O__T
	<Placemark>
		<name>$title</name>
		<description>$excerpt</description>
		<Point>
			<coordinates>$lon,$lat,0</coordinates>
		</Point>
		<TimeStamp>
			<when>$timestamp</when>
		</TimeStamp>
	</Placemark>

E__O__T;
	}
	echo <<< E__O__T
</Folder>
</kml>

E__O__T;
	return;
}

// ==================================================
// static 
function output_error($message) {
	header('Content-Type: application/xml; charset=utf-8');
	echo '<error>' . wp_specialchars($message) . '</error>';
	return;
}

// ===== End of class ==============================
}

?>