<?php
/*
Plugin Name: Lightweight Google Maps
Plugin URI: http://wppluginsj.sourceforge.jp/lightweight-google-maps/
Description: Show google maps on your post and/or pages. Map and marker locations are read from "Lat_Long" custom fields (does not use Geo plugin.) At a static page, you can refine location markers by category or location name/address.
Version: 1.40
Author: IKEDA Yuriko
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

define('LWGM_API_FILENAME', 'locations.php');
define('LWGM_FIXED_MAP_ID', 'google_maps');
define('LWGM_EACH_MAP_CLASS', 'each_map');
define('LWGM_FIXED_MAP_HEIGHT', 480);
define('LWGM_NUM_RECENT_MARKERS', 100);
define('LWGM_EACH_MAP_WIDTH', 300);
define('LWGM_EACH_MAP_HEIGHT', 150);
define('LWGM_MOBILE_WIDTH', 128);
define('LWGM_MOBILE_HEIGHT', 80);
define('LWGM_MOBILE_WIDTH_MAX', 240);
define('LWGM_MOBILE_HEIGHT_MAX', 320);
define('LWGM_MAP_SIZE_MINIMUM', 16);
define('LWGM_INFOWINDOW_WIDTH', 250);
define('LWGM_INFOWINDOW_LINEHEIGHT', '1.40em');
define('LWGM_INFOWINDOW_FONTSIZE', '.85em');
define('LWGM_INFOWINDOW_TITLE_FONTSIZE', '1em');
define('LWGM_ZOOM_OF_RECENT_LOCATIONS', 8);
define('LWGM_ZOOM_OF_ADDRESSED_MAP', 13);
define('LWGM_ZOOM_OF_EACH_MAP', 14);
define('GOOGLE_MAPS_MAX_ZOOM', 19);
define('GOOGLE_STATIC_MAP_API', 'http://maps.google.com/staticmap?markers=%f,%f,red&amp;zoom=%d&amp;size=%dx%d&amp;maptype=mobile&amp;key=%s');

add_action('init', array('Lightweight_Google_Maps', 'factory'));

/* ==================================================
 *   Lightweight_Google_Maps Class
   ================================================== */

class Lightweight_Google_Maps {
	var $plugin_dir;
	var $plugin_url;

function Lightweight_Google_Maps() {
	$this->__construct();
}

function __construct() {
	$this->set_plugin_dir();
	$this->load_textdomain('lw_googlemaps', 'lang');
}

// ==================================================
//static
function factory() {
	global $Lw_GoogleMaps;
	if (function_exists('is_ktai') && is_ktai()) {
		if (is_ktai('flat_rate')) {
			$Lw_GoogleMaps = new LWGM_Mobile();
		}
	} elseif (function_exists('is_mobile') && is_mobile()) {
		$Lw_GoogleMaps = new LWGM_Mobile();
	} else {
		$Lw_GoogleMaps = new LWGM_PC();
	}
	return;
}

/* ==================================================
 * @param	none
 * @return	none
 */
//private 
function set_plugin_dir() {
	$this->plugin_dir = basename(dirname(__FILE__));
	if (defined('WP_PLUGIN_URL')) {
		$url = WP_PLUGIN_URL . '/';
	} else {
		$url = get_bloginfo('wpurl') . '/' . (defined('PLUGINDIR') ? PLUGINDIR . '/': 'wp-content/plugins/');
	}
	$this->plugin_url = $url . $this->plugin_dir . '/';
}

/* ==================================================
 * @param	string   $domain
 * @param	string   $subdir
 * @return	none
 */
//private 
function load_textdomain($domain, $subdir = '') {
	if ($this->check_wp_version('2.6', '>=') && defined('WP_PLUGIN_DIR')) {
		load_plugin_textdomain($domain, false, $this->get('plugin_dir') . ($subdir ? '/' . $subdir : ''));
	} else {
		$plugin_path = defined('PLUGINDIR') ? PLUGINDIR . '/': 'wp-content/plugins/';
		load_plugin_textdomain($domain, $plugin_path . $this->get('plugin_dir') . ($subdir ? '/' . $subdir : ''));
	}
}

/* ==================================================
 * @param	string   $version
 * @param	string   $operator
 * @return	boolean  $result
 */
//public 
function check_wp_version($version, $operator = '>=') {
	$wp_vers = get_bloginfo('version');
	if (! is_numeric($wp_vers)) {
		$wp_vers = preg_replace('/[^.0-9]/', '', $wp_vers);
	}
	return version_compare($wp_vers, $version, $operator);
}

/* ==================================================
 * @param	string  $key
 * @return	boolean $charset
 */
//public 
function get($key) {
	return isset($this->$key) ? $this->$key : NULL;
}

/* ==================================================
 * @param	string   $name
 * @param	boolean  $return_default
 * @param	string   $old_name
 * @return	mix      $value
 */
//public 
function get_option($name, $return_default = false, $old_name = NULL) {
	if (! $return_default) {
		$value = get_option($name);
		if (false !== $value) {
			return $this->verify_value($name, $value);
		} elseif ($old_name) {
			$value = get_option($name);
			if (false !== $value) {
				return $this->verify_value($name, $value);
			}
		}
	}
	// default values 
	switch ($name) {
	case 'lw_fixed_map_type':
		return 'G_HYBRID_MAP';
	case 'lw_fixed_map_height':
		return LWGM_FIXED_MAP_HEIGHT;
	case 'lw_each_map_type':
		return 'G_NORMAL_MAP';
	case 'lw_each_map_width':
		return LWGM_EACH_MAP_WIDTH;
	case 'lw_each_map_height':
		return LWGM_EACH_MAP_HEIGHT;
	case 'lw_num_recent_markers':
		return LWGM_NUM_RECENT_MARKERS;
	case 'lw_mobile_map_width':
		return LWGM_MOBILE_WIDTH;
	case 'lw_mobile_map_height':
		return LWGM_MOBILE_HEIGHT;
	default:
		return NULL;
	}
}

/* ==================================================
 * @param	string   $name
 * @param	mix      $value
 */
// private
function verify_value($name, $value) {
	switch ($name) {
	case 'lw_num_recent_markers':
		$value = intval($value);
		if ($value < 0) {
			$value = 0;
		}
	case 'lw_fixed_map_width':
		$value = intval($value);
		break;
	case 'lw_fixed_map_height':
		$value = intval($value);
		if ($value < LWGM_MAP_SIZE_MINIMUM) {
			$value = LWGM_FIXED_MAP_HEIGHT;
		}
		break;
	case 'lw_each_map_width':
		$value = intval($value);
		if ($value < LWGM_MAP_SIZE_MINIMUM) {
			$value = LWGM_EACH_MAP_WIDTH;
		}
		break;
	case 'lw_each_map_height':
		$value = intval($value);
		if ($value < LWGM_MAP_SIZE_MINIMUM) {
			$value = LWGM_EACH_MAP_HEIGHT;
		}
		break;
	case 'lw_mobile_map_width':
		$value = intval($value);
		if ($value < LWGM_MAP_SIZE_MINIMUM) {
			$value = LWGM_MOBILE_WIDTH;
		} elseif ($value > LWGM_MOBILE_WIDTH_MAX) {
			$value = LWGM_MOBILE_WIDTH_MAX;
		}
		break;
	case 'lw_mobile_map_height':
		$value = intval($value);
		if ($value < LWGM_MAP_SIZE_MINIMUM) {
			$value = LWGM_MOBILE_HEIGHT;
		} elseif ($value > LWGM_MOBILE_HEIGHT_MAX) {
			$value = LWGM_MOBILE_HEIGHT_MAX;
		}
		break;
	}
	return $value;
}

// ==================================================
//protected 
function get_zoom_level($field_name, $default_zoom) {
	$zoom = intval(get_option($field_name));
	if ($zoom < 1 || $zoom > GOOGLE_MAPS_MAX_ZOOM) {
		$zoom = $default_zoom;
	}
	return $zoom;
}

// ===== End of class ====================
}

/* ==================================================
 *   LWGM_Mobile class
   ================================================== */

class LWGM_Mobile extends Lightweight_Google_Maps {
	var $key;
	var $width;
	var $height;

function LWGM_Mobile() {
	return $this->__construct();
}

function __construct() {
	$this->key = $this->get_option('googlemaps_api_key');
	$map_type = $this->get_option('lw_each_map_type');
	if ($map_type != 'LWGM_LINK_TO_MAP') {
		$this->width  = $this->get_option('lw_mobile_map_width');
		$this->height = $this->get_option('lw_mobile_map_height');
		add_filter('the_content', array($this, 'each_map'), 91);
	}
	parent::__construct();
}

// ==================================================
//public 
function each_map($content) {
	$inline = function_exists('ks_is_image_inline') && ks_is_image_inline();
	$is_ktaistyle = class_exists('Ktai_Style');
	require_once dirname(__FILE__) . '/Lat_Long.php';
	$latlongs = Lat_Long::get_LatLon();
	if ($latlongs) {
		global $post;
		$maps = array();
		foreach ($latlongs as $i => $l) {
			$link = sprintf(GOOGLE_STATIC_MAP_API, floatval($l['lat']), floatval($l['lon']), LWGM_ZOOM_OF_EACH_MAP, $this->width, $this->height, $this->key);
			if ($inline) {
				$maps[] = '<img src="' . $link . '" />';
			} else {
				$maps[] = '[' . ($is_ktaistyle ? '<img localsrc="94" alt="' . __('IMAGE:', 'ktai_style') . '" />' : '')
					 . '<a href="' . $link . '">' . __('Map of this location', 'lw_googlemaps') . '</a>]';
			}
		}
		$content .= '<div align="center">' . implode('<br />', $maps) . '</div>';
		if (function_exists('ks_added_image')) {
			ks_added_image();
		}
	}
	return $content;
} 
 
// ===== End of class ====================
}

/* ==================================================
 *   LWGM_PC class
   ================================================== */

class LWGM_PC extends Lightweight_Google_Maps {
	var $fixed_map;
	var $each_maps;

function LWGM_PC() {
	return $this->__construct();
}

function __construct() {
	require_once dirname(__FILE__) . '/prefpane.php';
	$admin = new Lightweight_Google_Maps_Prefs;
	add_action('wp_head', array($this, 'output_style'));
	parent::__construct();
}

// ==================================================
//public 
function output_style() {
	$page_id = $this->get_option('lw_fixed_map_page_id');
	if ($page_id && is_page($page_id)) {
		$this->fixed_map = $page_id;
		add_filter('the_content', array($this, 'fixed_map_menu'));
		add_action('wp_footer',   array($this, 'fixed_map_script'));
		$map_width  = $this->get_option('lw_fixed_map_width');
		$map_height = $this->get_option('lw_fixed_map_height');
		$element = '#' . LWGM_FIXED_MAP_ID;
	} elseif ($this->get_option('lw_each_map_type') != 'LWGM_LINK_TO_MAP') {
		add_filter('the_content', array($this, 'each_map'));
		add_action('wp_footer',   array($this, 'each_map_script'));
		$map_width  = $this->get_option('lw_each_map_width');
		$map_height = $this->get_option('lw_each_map_height');
		$element = '.' . LWGM_EACH_MAP_CLASS;
	} else {
		add_filter('the_content', array($this, 'each_map'));
		return;
	}
?>
<style type="text/css" media="screen,tv,print,handheld">
<?php echo $element; ?> {
<?php if ($map_width) {echo "	width:{$map_width}px;\n";} ?>
	height:<?php echo $map_height; ?>px;
	line-height:105%;
	clear:both;
	margin:1em auto;
	padding:0;
	border:1px solid #999;
	text-align:left;
	font-size:100%;
}
<?php echo $element; ?> img {
	margin:0;
	padding:0;
	border:0 none;
}
<?php echo $element; ?> .infowindow strong {
	font-size:<?php echo LWGM_INFOWINDOW_TITLE_FONTSIZE; ?>;
}
<?php echo $element; ?> .infowindow p {
	line-height:<?php echo LWGM_INFOWINDOW_LINEHEIGHT; ?>;
	margin:1em 0 0;
	padding:0;
	text-indent:0;
	font-size:<?php echo LWGM_INFOWINDOW_FONTSIZE; ?>;
}
</style>
<?php 
	$gmap_api_key = $this->get_option('googlemaps_api_key');
	if ($gmap_api_key) {
?>
<script src="http://maps.google.com/maps?file=api&amp;v=2&amp;key=<?php echo attribute_escape($gmap_api_key); ?>" type="text/javascript" charset="utf-8"></script>
<?php }
}

// ==================================================
//public 
function fixed_map_menu($content) {
	$menu = '<div id="gmap_menu">
<form id="cat_refine" action="#"><div>
<label>' . __('Refine by category:', 'lw_googlemaps');
	if (function_exists('wp_dropdown_categories')) {
		$cats = wp_dropdown_categories('orderby=name&show_count=1&echo=0&show_option_all=' . __('None') . '&selected=' . intval($_GET['cat']));
	} else {
		ob_start();
		dropdown_cats(false,'all','name','asc',false,false,true,false,intval($_GET['cat']));
		$cats = ob_get_contents();
		ob_end_clean();
	}
	$menu .= preg_replace('/<select /', '<select onmouseup="refine_by_category(this.value); return false;" ', $cats)
	. '</label>
</div></form>' . "\n";
	if ($this->check_wp_version('2.3', '>=')) {
		$menu .= '<form onsubmit="refine_by_tag(this.tag.value); return false;" id="tag_refine" action="#"><div>
<label>' . __('Refine by tag:', 'lw_googlemaps') . '<input type="text" size="32" id="tag" /> </label>
<input type="submit" value="' . __('Show', 'lw_googlemaps') . '" />
</div></form>' . "\n";
	}
	$menu .= '<form onsubmit="move_to_place(this.place.value); return false;" id="geocoding" action="#"><div>'
	. __('Move to an address or a landmark:', 'lw_googlemaps') 
	. '<input type="text" size="32" id="place" /> <input type="submit" value="' 
	. __('Show', 'lw_googlemaps') . '" />
</div></form>
</div>';
	return str_replace('<div id="gmap_menu"></div>', $menu, $content);
}

// ==================================================
//public 
function each_map($content) {
	require_once dirname(__FILE__) . '/Lat_Long.php';
	$latlongs = Lat_Long::get_LatLon();
	if (! $latlongs) {
		return $content;
	}
	$map_type = $this->get_option('lw_each_map_type');
	$page_id  = $this->get_option('lw_fixed_map_page_id');
	if ($page_id && is_numeric($page_id)) {
		$page_link = get_permalink($page_id);
		if (strpos($page_link, '?') === false) {
			$page_link .= '?';
		} else {
			$page_link .= '&';
		}
	} else {
		$page_link = '';
	}
	if ($page_link && $map_type == 'LWGM_LINK_TO_MAP') {
		foreach ($latlongs as $l) {
			$content .= '<p class="map_link"><a href="' 
			. "{$page_link}lat={$l['lat']}&amp;lon={$l['lon']}" . '">' 
			. __('View this location on my large map &raquo;', 'lw_googlemaps') 
			. "</a></p>\n";
		}
	} elseif ($map_type != 'LWGM_LINK_TO_MAP') {
		global $post;
		foreach ($latlongs as $i => $l) {
			$this->each_maps[$post->ID][$i + 1] = array($l['lat'], $l['lon'], ($page_link ? "{$page_link}lat={$l['lat']}&lon={$l['lon']}" : ''));
			$seq = count($latlongs) >= 2 ?  '_' . ($i + 1) : '';
			$content .= '<div class="each_map" id="map_' . $post->ID . $seq  . '"></div>' . "\n";
		}
	}
	return $content;
}

// ==================================================
//public
function fixed_map_script() {
	$addressed_zoom = $this->get_zoom_level('lw_addressed_map_zoom', LWGM_ZOOM_OF_ADDRESSED_MAP);
	$recent_zoom = $this->get_zoom_level('lw_recent_map_zoom', LWGM_ZOOM_OF_RECENT_LOCATIONS);
	$map_type = $this->get_option('lw_fixed_map_type');
	$url = $this->get('plugin_url') . LWGM_API_FILENAME . '?format=xml&';
?>
<script type="text/javascript">
//<![CDATA[
function parse_loc(response, to_open) {
	var xmldoc  = GXml.parse(response);
	var lat     = xmldoc.documentElement.getElementsByTagName('lat');
	var lon     = xmldoc.documentElement.getElementsByTagName('lon');
	var title   = xmldoc.documentElement.getElementsByTagName('title');
	var link    = xmldoc.documentElement.getElementsByTagName('link');
	var date    = xmldoc.documentElement.getElementsByTagName('date');
	var excerpt = xmldoc.documentElement.getElementsByTagName('excerpt');
	var n = lat.length;
	if (n < 1) {
		return null;
	}
	var points = new Array();
	for (var i = 0 ; i < n ; i++) {
		var latlng = new GLatLng(GXml.value(lat[i]), GXml.value(lon[i]));
		if (to_open && latlng.equals(to_open)) {
			var opened = true;
		} else {
			var opened = false;
		}
		var desc = {
			'title'  : GXml.value(title[i]),
			'link'   : GXml.value(link[i]),
			'date'   : GXml.value(date[i]),
			'excerpt': GXml.value(excerpt[i])
		};
		var has_same = false;
		if (i > 0) {
			var last = Math.min(i, points.length);
			for (var j = 0 ; j < last ; j++) {
				if (latlng.equals(points[j][0])) {
					has_same = true;
					points[j].push(desc);
					break;
				}
			}
		}
		if (! has_same) {
			points.push(new Array(latlng, opened, desc));
		}
	}
	return points;
}
// --------------------
function window_content(desc) {
	return '<div class="infowindow"><strong><a href="'+desc.link+'">'+desc.title+'</a></strong><br />'+desc.date+'<p>'+desc.excerpt+'</p></div>';
}

// --------------------
function create_marker(map, loc) {
	var marker = new GMarker(loc[0]);
	map.addOverlay(marker);
	var n = loc.length;
	if (n <= 3) {
		marker.bindInfoWindowHtml(window_content(loc[2]), {'maxWidth':<?php echo LWGM_INFOWINDOW_WIDTH; ?>});
		if (loc[1]) {
			marker.openInfoWindowHtml(window_content(loc[2]), {'maxWidth':<?php echo LWGM_INFOWINDOW_WIDTH; ?>});
		}
	} else {
		var tabs = new Array(n - 2);
		for (var i = 2 ; i < n ; i++) {
			tabs[i - 2] = new GInfoWindowTab(i - 1, window_content(loc[i]));
		}
		marker.bindInfoWindowTabsHtml(tabs, {'maxWidth':<?php echo LWGM_INFOWINDOW_WIDTH; ?>});
		if (loc[1]) {
			marker.openInfoWindowTabsHtml(tabs, {'maxWidth':<?php echo LWGM_INFOWINDOW_WIDTH; ?>});
		}
	}
	return marker;
}
// --------------------
function put_markers(map, locs) {
	if (locs) {
		map.clearOverlays();
		var n = locs.length;
		for (var i = 0 ; i < n ; i++) {
			create_marker(map, locs[i]);
		}
	}
}
// --------------------
function recent_locations(num) {
	var query = 'recent=' + num;
	GDownloadUrl(baseurl + query, function(response) {
		var locs = parse_loc(response, null);
		if (locs) {
			locs[0][1] = true; // open a window of the recent marker
			put_markers(map, locs);
		}
	});
}
// --------------------
function move_to_latlong(latlng) {
	map.setCenter(latlng, <?php echo "$addressed_zoom, $map_type"; ?>);
	var bounds = map.getBounds();
	var query = 'bounds=' + bounds.getSouthWest().lat() + ',' + bounds.getSouthWest().lng() + ',' + bounds.getNorthEast().lat() + ',' + bounds.getNorthEast().lng();
	GDownloadUrl(baseurl + query, function(response) {
		var locs = parse_loc(response, latlng);
		put_markers(map, locs);
	});
}
// --------------------
function move_to_place(place) {
	geocoder.getLatLng(place, function(latlng) {
		if (latlng) {
			move_to_latlong(latlng);
		} else {
			alert(place + '<?php _e(': Could not find the latitude/longitude of this place.', 'lw_googlemaps'); ?>');
		}
	});
}
// --------------------
function get_outline(locs) {
	var outline = new GLatLngBounds(locs[0].latlng, locs[0].latlng);
	var n = locs.length;
	for (var i = 0 ; i < n ; i++) {
		outline.extend(locs[i][0]);
	}
	return outline;
}
// --------------------
function refine_by_category(cat_id) {
	var query = 'category=' + cat_id;
	GDownloadUrl(baseurl + query, function(response) {
		var locs  = parse_loc(response, null);
		if (! locs) {
			map.setCenter(new GLatLng(38.0,137.5), 4, <?php echo $map_type; ?>);
			map.clearOverlays();
			return;	
		}
		var outline = get_outline(locs);
		map.setCenter(outline.getCenter(), map.getBoundsZoomLevel(outline), <?php echo $map_type; ?>);
		put_markers(map, locs);
	});
}
// --------------------
function refine_by_tag(tag) {
	var query = 'tag=' + encodeURI(tag);
	GDownloadUrl(baseurl + query, function(response) {
		var locs  = parse_loc(response, null);
		if (! locs) {
			map.setCenter(new GLatLng(38.0,137.5), 4, <?php echo $map_type; ?>);
			map.clearOverlays();
			return;	
		}
		var outline = get_outline(locs);
		map.setCenter(outline.getCenter(), map.getBoundsZoomLevel(outline), <?php echo $map_type; ?>);
		put_markers(map, locs);
	});
}
// --------------------
function main() {
	map = new GMap2(document.getElementById('<?php echo LWGM_FIXED_MAP_ID; ?>'));
	map.removeMapType(G_SATELLITE_MAP);
	map.addMapType(G_PHYSICAL_MAP);
	map.addControl(new GLargeMapControl());
	map.addControl(new GMapTypeControl());
	map.addControl(new GScaleControl());
	map.addControl(new GOverviewMapControl());
	geocoder = new GClientGeocoder();
<?php
	if (isset($_GET['lat']) && isset($_GET['lon'])) {
?>
	move_to_latlong(new GLatLng(<?php echo floatval($_GET['lat']) . ',' . floatval($_GET['lon']); ?>));
<?php
	} elseif (isset($_GET['place'])) {
?>
	move_to_place('<?php echo wp_specialchars($_GET['place']); ?>');
<?php
	} elseif (isset($_GET['cat'])) {
?>
	refine_by_category(<?php echo intval($_GET['cat']); ?>);
<?php
	} elseif (isset($_GET['keyword'])) {
?>
	refine_by_tag("<?php echo urlencode($_GET['keyword']); ?>");
<?php
	} else {
		$last = $this->last_location();
		if ($last) {
			$num = $this->get_option('lw_num_recent_markers');
			echo <<< E__O__T
	map.setCenter(new GLatLng({$last['lat']}, {$last['lon']}), $recent_zoom, $map_type);
	recent_locations($num);

E__O__T;
		} else {
			echo <<< E__O__T
	map.setCenter(new GLatLng(38.0,137.5), 4, $map_type);

E__O__T;
		}
	}
?>
}
// --------------------
if (GBrowserIsCompatible()) {
	var map;
	var geocoder;
	var baseurl = '<?php echo $url; ?>';
	onload = main;
	onunload = GUnload;
}
//]]>
</script>
<?php
}

// ==================================================
// private
function last_location() {
	require_once dirname(__FILE__) . '/Lat_Long.php';
	$loc = NULL;
	for ($page = 1 ; $page <= 100000 ; $page++) {
		$posts = new WP_Query("paged=$page");
		if (! $posts->have_posts() || $page > 100000) {
			break;
		}
		while ($posts->have_posts()) : $posts->the_post();
			$latlongs = Lat_Long::get_LatLon();
			if ($latlongs) {
				$loc = $latlongs[0];
				break 2;
			}
		endwhile;
	}
	return $loc;
}

// ==================================================
// public
function each_map_script() {
	if (! isset($this->each_maps) || count($this->each_maps) < 1) {
		return;
	}
	$page_id  = $this->get_option('lw_fixed_map_page_id');
	$map_type = $this->get_option('lw_each_map_type');
	$zoom = $this->get_zoom_level('lw_each_map_zoom', LWGM_ZOOM_OF_EACH_MAP);
?>
<script type="text/javascript" charset="utf-8">
	//<![CDATA[
	function main() {
		var markeropt = new Object();
		markeropt.title = "<?php echo ($page_id ? __('Click to view my large map.', 'lw_googlemaps') : ''); ?>";
<?php
	foreach ($this->each_maps as $id => $points) {
		foreach ($points as $c => $latlng) {
			$count   = count($points) >= 2 ? "_$c" : '';
			echo <<<E__O__T
		var map$id$count = new GMap2(document.getElementById('map_$id$count'));
		map$id$count.addControl(new GSmallZoomControl());
		map$id$count.setCenter(new GLatLng($latlng[0], $latlng[1]), $zoom, $map_type);
		var marker$id$count = new GMarker(map$id$count.getCenter(), markeropt);

E__O__T;
			if ($latlng[2]) {
				echo <<<E__O__T
		GEvent.addListener(marker$id$count, 'click', function() {location.href = '$latlng[2]'; });

E__O__T;
			}
			echo <<<E__O__T
		map$id$count.addOverlay(marker$id$count);

E__O__T;
		}
	}
?>
	}
	if (GBrowserIsCompatible()) {
		onload = main;
		onunload = GUnload;
	}
//]]>
</script>
<?php
}

// ===== End of class ====================
}
?>