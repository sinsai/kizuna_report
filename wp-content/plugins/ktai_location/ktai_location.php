<?php 
/* 
Plugin Name: Ktai Location
Plugin URI: http://wppluginsj.sourceforge.jp/ktai_location/
Description: Read latitude-longitude data from a map URL which is provided with location services for mobile phone, or from an EXIF infomation of a posted picture. Or, convert place names and/or addresses to latitude-longitude data (geocoding). And save the location data as "Lat_Long" custom fields.
Author: IKEDA yuriko
Version: 1.0.3
Author URI: http://www.yuriko.net/cat/wordpress/
 */

/*  Copyright (c) 2007-2008 yuriko

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; version 2 of the License.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

/*
 * Special Thanks:
	- "TSJ Blog II": author of wp-eznavi.
		[http://blog2.atsj.net/]
	- Daishin Doi: support for kokomail.
 */

//define('GOOGLEMAPS_API_KEY', 'XXXXXXXXXX');

define('GEO_META_FIELD_NAME', 'Lat_Long');
define('CSS_CLASS_FOR_LOCATION', 'locationurl');
define('LATITUDE_MAX', 90);
define('LONGTITUDE_MAX', 180);
define('EZNAVI_WGS84',  0);
define('EZNAVI_TOKYO',  1);
define('EZNAVI_ITRF',   2);
define('EZNAVI_DMS',    0);
define('EZNAVI_DEGREE', 1);
define('NAVITIME_WGS84',  0);
define('NAVITIME_TOKYO',  1);
define('NAVITIME_ITRF',   2);
define('NAVITIME_DMS',    0);
define('NAVITIME_DEGREE', 1);

define('JSKYCMI_PAT', '/jskycmi/[a-zA-Z0-9+/]+?/');
define('EQUATORIAL_RADIUS_TOKYO', 6377397.155);
define('EQUATORIAL_RADIUS_WGS84', 6378137.000);
define('REV_OBLATENESS_TOKYO', 299.152813);
define('REV_OBLATENESS_WGS84', 298.257223563);
define('LATLONG_SIGNIFICANT_DIGITS', 4);
define('HTTP_OK', 200);
define('GEOCODING_URL', 'http://maps.google.com/maps/geo?q=%s&key=%s&output=%s');
define('GEOCODING_ERROR_NORESPOSE', '(Geocoding no response) ');
define('GEOCODING_ERROR_STATUS', '(Geocoding error: %s) ');

$Ktai_Location = new Ktai_Location;
add_action('wp_head', array($Ktai_Location, 'output_style'), 20);
add_filter('the_content', array($Ktai_Location, 'shrink_content'), 20);
add_action('publish_post', array($Ktai_Location, 'read_location'), 20);
add_action('publish_phone', array($Ktai_Location, 'read_location'), 20);
add_action('edit_post', array($Ktai_Location, 'read_location'), 20);

/* ==================================================
 *   Ktai_Location class
   ================================================== */

class Ktai_Location {
	var $content;
	var $locations;

/* ==================================================
 * @param	none
 * @return	none
 */
//static
function output_style() {
	if (function_exists('is_ktai') && is_ktai() 
	||  function_exists('is_mobile') && is_mobile()) {
		return;
	}
?>
<style type="text/css" media="all">
.<?php echo CSS_CLASS_FOR_LOCATION; ?> {
	display:none;
}
</style>
<?php
}

/* ==================================================
 * @param	string   $content
 * @return	string   $content
 */
//static
function shrink_content($content) {
	if (function_exists('is_ktai') && is_ktai() 
	||  function_exists('is_mobile') && is_mobile()) {
		$content = preg_replace('!\s*<div class="' . CSS_CLASS_FOR_LOCATION . '">.*?</div>!s', '', $content);
	}
	return $content;
}


/* ==================================================
 * @param	int      $postID
 * @return	int      $postID
 */
//static
function replace_location($postID, $replace = true) {
	$this->read_location($postID);
}

/* ==================================================
 * @param	int      $postID
 * @param	boolean  $replace
 * @return	int      $postID
 */
//static
function read_location($postID, $replace = false) {
	if (! is_numeric($postID) || $postID <= 0) {
		return $postID;
	}

	$post = get_post($postID);
	if ($post->ID != $postID) {
		return $postID;
	}
	$this->content   = $post->post_content;
	$this->locations = array_merge(
		$this->read_gps_url(), 
		$this->read_gps_exif(), 
		$this->geocoding()
	);

	if (count($this->locations)) {
		$this->uniq();
		$updated = $this->update_meta($post->ID, $replace);
		$touched = false;
		foreach ($this->locations as $l) {
			if ($updated && isset($l->url)) {
				$touched = $this->format_url($l->url);
			} elseif (isset($l->geotag)) {
				$touched = $this->delete_geotag($l->geotag, $l->place);
			}
		}
		if ($touched) {
			$post->post_content = $this->content;
			global $wpdb;
			$content_sql = $wpdb->escape($post->post_content);
			$id_sql = intval($post->ID);
			$wpdb->query("UPDATE {$wpdb->posts} SET post_content = '$content_sql' WHERE ID = $id_sql");
			$posts = array($post);
			update_post_cache($posts);
		}
	}
	return  $postID;
}

/* ==================================================
 * @param	none
 * @return	array    $locations
 */
//private
function read_gps_url() {
	$locations = array();
	if (preg_match_all('#^(\S+\s*)?https?://\S+\s*(---.*?MBK/iPC)?#ms', $this->content, $m)) {
		foreach ($m[0] as $n) {
			switch (true) {
			case $loc = Ktai_Location_EZ_Navi::factory($n):
				break;
			case $loc = Ktai_Location_Navitime::factory($n):
				break;
			case $loc = Ktai_Location_DoCoMoGPS::factory($n):
				break;
			case $loc = Ktai_Location_YahooMap::factory($n):
				break;
			case $loc = Ktai_Location_itsumoNavi::factory($n):
				break;
			case $loc = Ktai_Location_iZenrin::factory($n):
				break;
			case $loc = Ktai_Location_MapFan::factory($n):
				break;
			case $loc = Ktai_Location_vMapFan::factory($n):
				break;
			case $loc = Ktai_Location_iEkitan::factory($n):
				break;
			case $loc = Ktai_Location_Mapion::factory($n):
				break;
			case $loc = Ktai_Location_iMappuru::factory($n):
				break;
			case $loc = Ktai_Location_sMappuru::factory($n):
				break;
			case $loc = Ktai_Location_iChizumaru::factory($n):
				break;
			}
			if ($loc) {
				$locations[] = $loc;
			}
		}
	}
	return $locations;
}

/* ==================================================
 * @param	none
 * @return	array    $locations
 */
//private
function read_gps_exif() {
	$locations = Ktai_Location_EXIF::factory($this->content);
	return $locations ?  $locations : array();
}

/* ==================================================
 * @param	none
 * @return	array    $locations
 */
//private
function geocoding() {
	$locations = Ktai_Location_Geocoding::factory($this->content);
	return $locations ?  $locations : array();
}

/* ==================================================
 * @param	none
 * @return	int      $num_removed
 */
//private
function uniq() {
	if (count($this->locations) < 1) {
		return false;
	}
	$loc_index = array();
	$num_removed = 0;
	foreach ($this->locations as $i => $l) {
		if (! isset($l->lat) || ! isset($l->lon)) {
			continue;
		}
		$lat = round($l->lat, LATLONG_SIGNIFICANT_DIGITS);
		$lon = round($l->lon, LATLONG_SIGNIFICANT_DIGITS);
		if (isset($loc_index["$lat,$lon"])) {
			unset($this->locations[$i]);
			$num_removed++;
		} else {
			$loc_index["$lat,$lon"] = $i;
		}
	}
	return $num_removed;
}

/* ==================================================
 * @param	int      $postID
 * @param	boolean  $replace
 * @return	boolean  $do_update
 */
//private
function update_meta($postID, $replace = false) {
	$meta_values = get_post_meta($postID, GEO_META_FIELD_NAME);
	$loc_index = array();
	$updated = false;
	if ($meta_values) {
		foreach ($meta_values as $v) {
			$lat_long = explode(',', $v);
			$lat = round($lat_long[0], LATLONG_SIGNIFICANT_DIGITS);
			$lon = round($lat_long[1], LATLONG_SIGNIFICANT_DIGITS);
			$loc_index["$lat,$lon"] = true;
		}
		if (! $replace && count($loc_index)) {
			return $updated;
		}
		delete_post_meta($postID, GEO_META_FIELD_NAME);
	}
	foreach ($this->locations as $l) {
		if (! isset($l->lat) || ! isset($l->lon)) {
			continue;
		}
		$lat = round($l->lat, LATLONG_SIGNIFICANT_DIGITS);
		$lon = round($l->lon, LATLONG_SIGNIFICANT_DIGITS);
		if (! isset($loc_index["$lat,$lon"])) {
			$lat_long = $l->lat . ',' . $l->lon;
			if (isset($l->alt) && is_numeric($l->alt)) {
				$lat_long .= ',' . $l->alt;
			}
			add_post_meta($postID, GEO_META_FIELD_NAME, $lat_long);
			$updated = true;
		}
	}
	return $updated;
}

/* ==================================================
 * @param	string   $url
 * @return	boolean  $touched
 */
//private
function format_url($url) {
	$new_url = str_replace('http://', 'HTTP://', $url);
	$this->content = preg_replace('!([\r\n]*)?' . preg_quote($url, '!') . '(\s*)(</p>)?!', 
		'$3$1<div class="' . CSS_CLASS_FOR_LOCATION . '">' . $new_url . '</div>$2', 
		$this->content);
	return true;
}

/* ==================================================
 * @param	string   $geotag
 * @return	boolean  $touched
 */
//private
function delete_geotag($geotag, $place) {
	$this->content = str_replace($geotag, $place, $this->content);
	return true;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_Info class
   ================================================== */

class Ktai_Location_Info {
	var $lat;
	var $lon;
	var $alt;
	var $url;
	var $geometory;
	var $unit;
	var $accuracy;
	var $fixed_mode;
	var $geotag;
	var $place;

/* ==================================================
 * @param	string   $query
 * @param	array    $keys
 * @return	array    $params
 */
//protected
function get_params($query, $keys = array('lat', 'lon', 'alt')) {
	$query = str_replace('&amp;', '&', $query);
	parse_str(urldecode($query), $params);
	$params += array_combine($keys, array_fill(1, count($keys), NULL));
/*	$params += array_fill_keys($keys, NULL)); // PHP >= 5.2.0 */
	return $params;
}

/* ==================================================
 * @param	string   $dms
 * @param	int      $limit
 * @return	float    $value
 */
//protected
function dms2deg($dms, $limit = LONGTITUDE_MAX) {
	$value = NULL;
	if (preg_match("#([-+]?)(\d+)\.(\d+)\.([.\d]+)#", $dms, $m)) {
		$value = ($m[1] == '-' ? -1 : 1) * 
			(intval($m[2]) + intval($m[3]) / 60 + floatval($m[4]) / 3600);
	} elseif (is_numeric($dms)) {
		$value = floatval($dms);
	}
	if ($value < -1 * $limit || $value > $limit) {
		$value = NULL;
	}
	return $value;
}

/* ==================================================
 * @param	string   $seconds
 * @param	int      $limit
 * @return	float    $value
 */
//protected
function sec2deg($seconds, $limit = LONGTITUDE_MAX) {
	$value = NULL;
	if (is_numeric($seconds)) {
		$value = floatval($seconds) / 3600;
	}
	if ($value < -1 * $limit || $value > $limit) {
		$value = NULL;
	}
	return $value;
}

/* ==================================================
 * @param	string   $mili_sec
 * @param	int      $limit
 * @return	float    $value
 */
//protected
function milisec2deg($mili_sec, $limit = LONGTITUDE_MAX) {
	$value = NULL;
	if (is_numeric($mili_sec)) {
		$value = floatval($mili_sec) / 3600000.0;
	}
	if ($value < -1 * $limit || $value > $limit) {
		$value = NULL;
	}
	return $value;
}

/* ==================================================
 * @param	none
 * @return	none
 */
//protected
function tokyo2wgs84() {
	/*
	 * Thanks to "Mac.GPS.Perl"
	 * <http://homepage3.nifty.com/Nowral/02_DATUM/02_DATUM.html>
	 *
	 * ----- Test script -----
	$loc = new Ktai_Location_Info;
	$loc->lat = 35  + 20/60 + 39.984328/3600;
	$loc->lon = 138 + 35/60 +  8.086122/3600;
	$loc->alt = 697.681000;
	$good = array(35 + 20 / 60 + 51.685555 / 3600,  138 + 34 / 60 + 56.838916 / 3600, 737.895217);
	$loc->tokyo2wgs84();
	print_r($loc);
	if ($loc->lat == $good[0] && $loc->lon == $good[1] && $loc->alt == $good[2]) {
		echo "Good Anser!!\n";
	} else {
		echo "Bad Answer\nExpected: ";
		print_r($good);
	}
	 */
	if (! $this->alt) {
		$this->lat = $this->lat - 0.000106950 * $this->lat + 0.000017464 * $this->lon + 0.0046017;
		$this->lon = $this->lon - 0.000046038 * $this->lat - 0.000083043 * $this->lon + 0.0100400;
	} else {
		list($x, $y, $z) = $this->lla2xyz($this->lat, $this->lon, $this->alt, EQUATORIAL_RADIUS_TOKYO, REV_OBLATENESS_TOKYO);
		list($this->lat, $this->lon, $this->alt) = $this->xyz2lla($x - 148, $y + 507, $z + 681, EQUATORIAL_RADIUS_WGS84, REV_OBLATENESS_WGS84);
	}
	return;
}

//private
function lla2xyz($lat, $lon, $alt, $a, $ro) {
	$e2 = 2 / $ro - 1/($ro * $ro);
	$lat_sin = sin(deg2rad($lat));
	$lat_cos = cos(deg2rad($lat));
	$radius = $a / sqrt(1 - $e2 * $lat_sin * $lat_sin);
	$x = ($radius + $alt) * $lat_cos * cos(deg2rad($lon));
	$y = ($radius + $alt) * $lat_cos * sin(deg2rad($lon));
	$z = ($radius * (1 - $e2) + $alt) * $lat_sin;
	return array($x, $y, $z);
}

//private
function xyz2lla($x, $y, $z, $a, $ro) {
	$e2 = 2 / $ro - 1/($ro * $ro);
	$bda = sqrt(1 - $e2);
	$p = sqrt($x * $x + $y * $y);
	$t = atan2($z, $p * $bda);
	$t_sin = sin($t);
	$t_cos = cos($t);
	$lat = atan2($z + $e2 * $a / $bda * $t_sin * $t_sin * $t_sin, 
	             $p - $e2 * $a * $t_cos * $t_cos * $t_cos);
	$lon = atan2($y, $x);
	$lat_sin = sin($lat);
	$alt = $p /cos($lat) - $a / sqrt(1 - $e2 * $lat_sin * $lat_sin);
	return array(rad2deg($lat), rad2deg($lon), $alt);
}

/* ==================================================
 * @param	none
 * @return	none
 */
//protected
function check_values() {
	if (is_numeric($this->lat)) {
		$this->lat = floatval($this->lat);
	} else {
		$this->lat = NULL;
	}
	if (is_numeric($this->lon)) {
		$this->lon = floatval($this->lon);
	} else {
		$this->lon = NULL;
	}
	return ($this->lat && $this->lon);
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_EZ_Navi class
 * example: http://walk.eznavi.jp/map/?datum=AAA&unit=BBB&lat=XXX&lon=YYY
   ================================================== */

class Ktai_Location_EZ_Navi extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*GPS\S*?)?\s*http://walk\.eznavi\.jp/map/\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_EZ_Navi;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('datum', 'unit', 'lat', 'lon', 'alt', 'fm'));
	$loc->lat        = trim($params['lat']);
	$loc->lon        = trim($params['lon']);
	$loc->alt        = trim($params['alt']);
	$loc->geometory  = $params['datum'];
	$loc->unit       = $params['unit'];
	$loc->fixed_mode = $params['fm'];
	if ($loc->unit != EZNAVI_DEGREE) {
		$loc->lat = $loc->dms2deg($loc->lat, LATITUDE_MAX);
		$loc->lon = $loc->dms2deg($loc->lon, LONGTITUDE_MAX);
	}
	if (! $loc->check_values()) {
		return NULL;
	}
	if (isset($loc->geometory) && $loc->geometory == EZNAVI_TOKYO) {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_Navitime class
 * example: http://map.navitime.jp/?datum=0&unit=1&lat=+34.65856&lon=+135.50640&fm=1
 * example: http://map.navitime.jp/?lat=%2835.51.57.17&lon=%28139.45.05.009&geo=wgs84&x-acc=3
   ================================================== */

class Ktai_Location_Navitime extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S+:\s*)?http://map\.navitime\.jp/?\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_Navitime;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('pos', 'x-acr', 'x-acc', 'lat', 'lon', 'alt', 'unit', 'fm', 'geo', 'datum'));
	if (isset($params['pos']) && preg_match('/^([NS])([.0-9]+)([EW])([.0-9]+)$/', $params['pos'], $n)) {
		$loc->lat = ($n[1] == 'S' ? -1: 1) * $loc->dms2deg($n[2], LATITUDE_MAX);
		$loc->lon = ($n[3] == 'W' ? -1: 1) * $loc->dms2deg($n[4], LONGTITUDE_MAX);
		$loc->accuracy = isset($params['x-acr']) ? $params['x-acr'] : $params['x-acc'];
	} else {
		$loc->lat        = trim($params['lat']);
		$loc->lon        = trim($params['lon']);
		$loc->alt        = trim($params['alt']);
		$loc->unit       = $params['unit'];
		$loc->fixed_mode = $params['fm'];
		if ($loc->unit != NAVITIME_DEGREE) {
			$loc->lat = $loc->dms2deg($loc->lat, LATITUDE_MAX);
			$loc->lon = $loc->dms2deg($loc->lon, LONGTITUDE_MAX);
		}
	}
	$loc->geometory = isset($params['geo']) ? $params['geo'] : $params['datum'];
	if (! $loc->check_values()) {
		return NULL;
	}
	if (isset($loc->geometory) && ($loc->geometory == 'tokyo' || $loc->geometory == EZNAVI_TOKYO)) {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_DoCoMoGPS class
 * Document: http://www.nttdocomo.co.jp/service/imode/make/content/gps/
 * example: http://docomo.ne.jp/cp/map.cgi?lat=%2B34.40.47.178&lon=%2B135.10.40.074&geo=wgs84&x-acc=3
   ================================================== */

class Ktai_Location_DoCoMoGPS extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^\S*http://docomo\.ne\.jp/cp/map\.cgi\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_DoCoMoGPS;
	$loc->url = $m[0];
	$params = $loc->get_params($m[1], array('lat', 'lon', 'alt', 'unit', 'geo', 'x-acc'));
	$loc->lat       = trim($params['lat']);
	$loc->lon       = trim($params['lon']);
	$loc->alt       = trim($params['alt']);
	$loc->geometory = $params['geo'];
	$loc->unit      = $params['unit'];
	$loc->accuracy  = $params['x-acc'];
	$loc->lat       = $loc->dms2deg($loc->lat, LATITUDE_MAX);
	$loc->lon       = $loc->dms2deg($loc->lon, LONGTITUDE_MAX);
	if (! $loc->check_values()) {
		return NULL;
	}
	if ($loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_YahooMap class
 * example: http://maps.mobile.yahoo.co.jp/mpl?lat=35.60538194&lon=139.58113&ac=&k=
 * example: http://map.mobile.yahoo.co.jp/mpl?lat=31.54.6.086&lon=131.25.31.498&sc=4&dc=4&mode=map&mv=2&k=
    ================================================== */

class Ktai_Location_YahooMap extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*URL:\s*)?http://maps?\.mobile\.yahoo\.co\.jp/mpl\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_YahooMap;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('lat', 'lon', 'geo', 'title'));
	$loc->lat       = trim($params['lat']);
	$loc->lon       = trim($params['lon']);
	$loc->lat       = $loc->dms2deg($loc->lat, LATITUDE_MAX);
	$loc->lon       = $loc->dms2deg($loc->lon, LONGTITUDE_MAX);
	$loc->geometory = $params['geo'];
	$loc->place     = mb_convert_encoding($params['title'], get_bloginfo('charset'), 'Shift_JIS');
	if (! $loc->check_values()) {
		return NULL;
	}
	if (empty($loc->geometory) || $loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_itsumoNavi class
 * example: http://mobile.its-mo.com/p1?128219829-502437693-6
 * example: http://mobile.its-mo.com/MapToLink/p2?pos=N35.39.19.183E139.44.55.335&geo=tokyo&x-acr=3
 * example: http://v.its-mo.com/zv/menu/ar5/jskycmi/.../ar5?pos=N35.36.59.74E139.34.27.8&amp;geo=wgs84&amp;x-acr=1
   ================================================== */

class Ktai_Location_itsumoNavi extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*(GPS|URL:)\S*?)?\s*http://(mobile|v)\.its-mo\.com/(p1|MapToLink/p2|zv/menu/ar5' . JSKYCMI_PAT . 'ar5)\?([^\s<]+)#', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_itsumoNavi;
	$loc->url = $m[0];
	$params = $loc->get_params($m[5], array('geo', 'x-acr', 'pos'));
	$loc->geometory = $params['geo'];
	$loc->accuracy  = $params['x-acr'];
	if (isset($params['pos']) && preg_match('/^([NS])([.0-9]+)([EW])([.0-9]+)$/', $params['pos'], $n)) {
		$loc->lat = ($n[1] == 'S' ? -1: 1) * $loc->dms2deg($n[2], LATITUDE_MAX);
		$loc->lon = ($n[3] == 'W' ? -1: 1) * $loc->dms2deg($n[4], LONGTITUDE_MAX);
	} elseif (preg_match('/^(\d+)-(\d+)-/', $m[5], $n)) {
		$loc->lat = $loc->milisec2deg($n[1], LATITUDE_MAX);
		$loc->lon = $loc->milisec2deg($n[2], LONGTITUDE_MAX);
	}
	if (! $loc->check_values()) {
		return NULL;
	}
	if (empty($loc->geometory) || $loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_iZenrin class
 * example: http://i.i.zenrin.co.jp/MapToLink/p1?scd=00300&rl=%2fzi%2fmenu%2far1%3farea%3d07900&x=6&n=35.616297&e=139.565364&uid=NULLGWDOCOMO
 * example: http://i.i.zenrin.co.jp/MapToLink/p1?an=128197368&ae=502458466&sid=00010&x=3&p=2&c=6&uid=NULLGWDOCOMO
   ================================================== */

class Ktai_Location_iZenrin extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^http://i\.i\.zenrin\.co\.jp/MapToLink/p1\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_iZenrin;
	$loc->url = $m[0];
	$params = $loc->get_params($m[1], array('n', 'e', 'an', 'ae'));
	if (isset($params['an'])) {
		$loc->lat = $loc->milisec2deg($params['an'], LATITUDE_MAX);
		$loc->lon = $loc->milisec2deg($params['ae'], LONGTITUDE_MAX);
	} else {
		$loc->lat = floatval($params['n']);
		$loc->lon = floatval($params['e']);
	}
	if (! $loc->check_values()) {
		return NULL;
	}
	$loc->tokyo2wgs84();
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_MapFan class
 * example: http://i.mapfan.com/m.cgi?uid=NULLGWDOCOMO&F=AP&M=E139.34.4.3N35.36.50.6&SC=SY3JY8GE&AR=07900
 * example: http://kokomail.mapfan.com/receive.cgi?MAP=E135.43.15.6N34.31.53.0&ZM=9
   ================================================== */

class Ktai_Location_MapFan extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*URL:\s*)?http://i\.mapfan\.com/m\.cgi\?([^\s<]+)#m', $url, $m) 
	&& ! preg_match('#^(\S*URL:\s*)?http://kokomail\.mapfan\.com/receive\.cgi\?([^\s<]+)\s*(---.*?MBK/iPC)?#ms', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_MapFan;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('M', 'MAP', 'N'));
	$map = isset($params['M']) ? $params['M'] : $params['MAP'];
	if (preg_match('/^([EW])([.0-9]+)([NS])([.0-9]+)$/', $map, $n)) {
		$loc->lat = ($n[3] == 'S' ? -1: 1) * $loc->dms2deg($n[4], LATITUDE_MAX);
		$loc->lon = ($n[1] == 'W' ? -1: 1) * $loc->dms2deg($n[2], LONGTITUDE_MAX);
	}
	$loc->place = mb_convert_encoding($params['N'], get_bloginfo('charset'), 'EUC-JP');
	if (! $loc->check_values()) {
		return NULL;
	}
	if (empty($loc->geometory) || $loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_MapFan class
 * example: http://v.mapfan.com/sgps.cgi/jskycmi/.../sgps.cgi?pos=N35.36.59.74E139.34.27.8&geo=wgs84&x-acr=1
   ================================================== */

class Ktai_Location_vMapFan extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*URL:\s*)?http://v\.mapfan\.com/sgps\.cgi' . JSKYCMI_PAT . 'sgps\.cgi\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_vMapFan;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('pos', 'geo', 'x-acr'));
	$map = $params['pos'];
	if (preg_match('/^([NS])([.0-9]+)([EW])([.0-9]+)$/', $map, $n)) {
		$loc->lat = ($n[1] == 'S' ? -1: 1) * $loc->dms2deg($n[2], LATITUDE_MAX);
		$loc->lon = ($n[3] == 'W' ? -1: 1) * $loc->dms2deg($n[4], LONGTITUDE_MAX);
	}
	$loc->geometory = $params['geo'];
	$loc->accuracy  = $params['x-acr'];
	if (! $loc->check_values()) {
		return NULL;
	}
	if (empty($loc->geometory) || $loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_iEkitan class
 * example: http://imode.ekitan.com/norikae/g_map/M1?uid=NULLGWDOCOMO&lat=%2b35.36.49.033&lon=%2b139.34.6.945&geo=wgs84&x-acc=3&address=
   ================================================== */

class Ktai_Location_iEkitan extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^http://imode\.ekitan\.com/norikae/g_map/M1\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_iEkitan;
	$loc->url = $m[0];
	$params = $loc->get_params($m[1], array('lat', 'lon', 'geo', 'x-acc'));
	$loc->lat       = $loc->dms2deg($params['lat'], LATITUDE_MAX);
	$loc->lon       = $loc->dms2deg($params['lon'], LONGTITUDE_MAX);
	$loc->geometory = $params['geo'];
	$loc->accuracy  = $params['x-acc'];
	if (! $loc->check_values()) {
		return NULL;
	}
	if ($loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_Mapion class
 * example: http://i.mapion.co.jp/c/f?uc=1&nl=35/36/02.038&el=139/30/38.558&grp=mall&scl=625000&R=1&uid=NULLGWDOCOMO
 * example: http://v.mapion.co.jp/c/f/jskycmi/.../f?uc=1&grp=station&ln=35/37/2.0008&el=139/34/23.000&R=1&BT=...
   ================================================== */

class Ktai_Location_Mapion extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*URL:\s*)?http://[iv]\.mapion\.co\.jp/c/(f' . JSKYCMI_PAT . ')?f\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_Mapion;
	$loc->url = $m[0];
	$params = $loc->get_params($m[3], array('nl', 'ln', 'el'));
	$lat = str_replace('/', '.', isset($params['ln']) ? $params['ln'] : $params['nl']);
	$lon = str_replace('/', '.', $params['el']);
	$loc->lat = $loc->dms2deg($lat, LATITUDE_MAX);
	$loc->lon = $loc->dms2deg($lon, LONGTITUDE_MAX);
	if (! $loc->check_values()) {
		return NULL;
	}
	$loc->tokyo2wgs84();
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_iMappuru class
 * example: http://imode.tmw.mti.ne.jp/iarea/action.go?uid=NULLGWDOCOMO&action=12000&lon=%2b139.34.6.945&x-acc=3&geo=wgs84&lat=%2b35.36.49.033&address=
   ================================================== */

class Ktai_Location_iMappuru extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^http://imode\.tmw\.mti\.ne\.jp/iarea/action\.go\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_iMappuru;
	$loc->url = $m[0];
	$params = $loc->get_params($m[1], array('lat', 'lon', 'geo', 'x-acc'));
	$loc->lat       = $loc->dms2deg($params['lat'], LATITUDE_MAX);
	$loc->lon       = $loc->dms2deg($params['lon'], LONGTITUDE_MAX);
	$loc->geometory = $params['geo'];
	$loc->accuracy  = $params['x-acc'];
	if (! $loc->check_values()) {
		return NULL;
	}
	if ($loc->geometory == 'tokyo') {
		$loc->tokyo2wgs84();
	}
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_sMappuru class
 * example: http://s.mti.ne.jp/mapple/gps/ShowMap2.asp?LAT=128224.547&LON=502428.207&SEFlg=&NAME=%90...
   ================================================== */

class Ktai_Location_sMappuru extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^(\S*URL:\s*)?http://s\.mti\.ne\.jp/mapple/gps/ShowMap2\.asp\?([^\s<]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_sMappuru;
	$loc->url = $m[0];
	$params = $loc->get_params($m[2], array('LAT', 'LON', 'SEFlg', 'NAME'));
	$loc->lat   = $loc->sec2deg($params['LAT'], LATITUDE_MAX);
	$loc->lon   = $loc->sec2deg($params['LON'], LONGTITUDE_MAX);
	$loc->place = mb_convert_encoding($params['name'], get_bloginfo('charset'), 'Shift_JIS');
	if (! $loc->check_values()) {
		return NULL;
	}
	$loc->tokyo2wgs84();
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_iChizumaru class
 * example: http://imode.chizumaru.com/czi/dsp/dsp.aspx?T1=Ca502458.518-128197.363
   ================================================== */

class Ktai_Location_iChizumaru extends Ktai_Location_Info {

//static
function factory($url) {
	if (! preg_match('#^http://imode\.chizumaru\.com/czi/dsp/dsp\.aspx\?T1=Ca([.\d]+)-([.\d]+)#m', $url, $m)) {
		return NULL;
	}
	$loc = new Ktai_Location_iChizumaru;
	$loc->url = $m[0];
	$loc->lat = $loc->sec2deg($m[2], LATITUDE_MAX);
	$loc->lon = $loc->sec2deg($m[1], LONGTITUDE_MAX);
	if (! $loc->check_values()) {
		return NULL;
	}
	$loc->tokyo2wgs84();
	return $loc;
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_EXIF class
 * Document: http://www.kanzaki.com/docs/sw/geoinfo.html#geo-exif
   ================================================== */

class Ktai_Location_EXIF extends Ktai_Location_Info {

//static
function factory($content) {
	if (! function_exists('exif_read_data') 
	|| ! preg_match_all('/(<a [^>]*?href=([\'"])([^\\\\]*?(\\\\.[^\\2\\\\]*?)*)\\2[^>]*>)?\s*<img [^>]*?src=([\'"])([^\\\\]*?(\\\\.[^\\5\\\\]*?)*)\\5/', $content, $images, PREG_SET_ORDER)) {
		return NULL;
	}
	$locations = array();
	foreach ($images as $i) {
		$file = NULL;
		if ($i[1]) {
			$file = Ktai_Location_EXIF::decide_file_path($i[3]);
		}
		if (! $file) {
			$file = Ktai_Location_EXIF::decide_file_path($i[6]);
		}
		if ($file && ($size = getimagesize($file)) && 
		   ($size[2] == IMAGETYPE_JPEG || $size[2] == IMAGETYPE_TIFF_II || $size[2] == IMAGETYPE_TIFF_MM)) {
			$exif = exif_read_data($file, 'GPS', true);
			if ($exif) {
				$loc = new Ktai_Location_EXIF;
				$loc->lat = $loc->decode_exif_location($exif['GPS']['GPSLatitude'], $exif['GPS']['GPSLatitudeRef']);
				$loc->lon = $loc->decode_exif_location($exif['GPS']['GPSLongitude'], $exif['GPS']['GPSLongitudeRef']);
				if (isset($exif['GPS']['GPSAltitude'])) {
					$loc->alt = (isset($exif['GPS']['GPSAltitudeRef']) && $exif['GPS']['GPSAltitudeRef'] == '1' ? -1 : 1) * $exif['GPS']['GPSAltitude'];
				}
				$loc->geometory = isset($exif['GPS']['GPSMapDatum']) ? $exif['GPS']['GPSMapDatum'] : NULL;
				if ($loc->lat && $loc->lon) {
					if (strtolower($loc->geometory) == 'tokyo') {
						$loc->tokyo2wgs84();
					}
					$locations[] = $loc;
				}
			}
		}
	}
	return $locations;
}

/* ==================================================
 * @param	string   $src
 * @return	string   $path
 */

//private
function decide_file_path($src) {
	foreach (array(get_bloginfo('wpurl'), get_bloginfo('url')) as $url) {
		if (! $url) {
			continue;
		}
		$path = ABSPATH . preg_replace('#^' . preg_quote($url) . '/?#', '', $src, 1);
		if (file_exists($path)) {
			return $path;
		}
		$short_url = preg_replace('#^https?://[^/]*/?#', '/', $url, 1);
		$path = ABSPATH . preg_replace('#^' . preg_quote($short_url) . '/?#', '', $src, 1);
		if (file_exists($path)) {
			return $path;
		}
	}
	$path = ABSPATH . preg_replace('#^https?://[^/]*/#', '', $src, 1);
	if (file_exists($path)) {
		return $path;
	}
	$path = ABSPATH . $src;
	if (file_exists($path)) {
		return $path;
	}
	return NULL; // failed to decide file path...
}

/* ==================================================
 * @param	array    $dms
 * @param   string   $ref
 * @return	string   $degree
 */

//private
function decode_exif_location($dms, $ref) {
	if (count($dms) != 3) {
		return NULL;
	}
    $deg = array_map('intval', explode('/', $dms[0]));
    $min = array_map('intval', explode('/', $dms[1]));
    $sec = array_map('intval', explode('/', $dms[2]));
	return ($ref == 'S' || $ref == 'W' ? -1 : 1) *
	  ( ($deg[1] != 0 ? $deg[0] / $deg[1] : 0)
	  + ($min[1] != 0 ? $min[0] / ($min[1] * 60) : 0)
	  + ($sec[1] != 0 ? $sec[0] / ($sec[1] * 3600) : 0));
}

// ===== End of class ==============================
}

/* ==================================================
 *   Ktai_Location_Geocoding class
 * API: http://www.google.com/apis/maps/documentation/#Geocoding_HTTP_Request
   ================================================== */

class Ktai_Location_Geocoding extends Ktai_Location_Info {

//static 
function factory($content) {
	if (! preg_match_all('#\[geo\](.*?)\[/geo\]#', $content, $matches, PREG_SET_ORDER) 
	||  ! ini_get('allow_url_fopen')) {
		return NULL;
	}
	$api_key = get_option('googlemaps_api_key');
	if (! $api_key) {
		if (! defined('GOOGLEMAPS_API_KEY')) {
			return NULL;
		} else {
			$api_key = GOOGLEMAPS_API_KEY;
		}
	}
	$locations = array();
	$charset = strtoupper(get_option('blog_charset'));
	$format = function_exists('simplexml_load_string') ? 'xml' : 'csv';
	foreach ($matches as $m) {
		$loc = new Ktai_Location_Geocoding;
		$loc->geotag = $m[0];
		$loc->place  = $m[1];
		$place_utf8  = ($charset != 'UTF-8') ? mb_convert_encoding($m[1], 'UTF-8', $charset) : $m[1];
		$response = @file_get_contents(sprintf(GEOCODING_URL, urlencode($place_utf8), $api_key, $format));
		if (! $response) {
			$loc->place .= GEOCODING_ERROR_NORESPONSE;
		} elseif ($format == 'xml') {
			$geoxml = simplexml_load_string($response);
			$status = $geoxml->Response->Status->code;
			if ($status != HTTP_OK) {
				$loc->place .= sprintf(GEOCODING_ERROR_STATUS, $status);
			} else {
				$coords = explode(',', $geoxml->Response->Placemark->Point->coordinates);
				$loc->lat = isset($coords[1]) ? $coords[1] : NULL;
				$loc->lon = isset($coords[0]) ? $coords[0] : NULL;
				$loc->alt = isset($coords[2]) ? $coords[2] : NULL;
			}
		} elseif ($format == 'csv') {
			list($status, $accuracy, $lat, $lon) = explode(',', $response);
			if ($status != HTTP_OK) {
				$loc->place .= "(Geocoding error: $status) ";
			} else {
				$loc->lat = isset($lat) ? $lat : NULL;
				$loc->lon = isset($lon) ? $lon : NULL;
			}
		} else {	// Invalid format
			continue;
		}
		$locations[] = $loc;
	}
	return $locations;
}

// ===== End of class ==============================
}

?>