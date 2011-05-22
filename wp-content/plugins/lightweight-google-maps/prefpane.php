<?php
/* ==================================================
 *   Lightweight_Google_Maps_Prefs Class
   ================================================== */

if (! defined('GEO_META_FIELD_NAME')) {
	define('GEO_META_FIELD_NAME', 'Lat_Long');
}

class Lightweight_Google_Maps_Prefs extends Lightweight_Google_Maps {
	var $nonce = -1;

// public
function Lightweight_Google_Maps_Prefs() {
	$this->__construct();
}

// public
function __construct() {
	add_action('admin_menu',  array($this, 'add_admin_page'));
}

// ==================================================
// public
function add_admin_page() {
	add_options_page('Lightweight Google Maps Configuration', 'Google Maps', 'manage_options', 'lwgm-prefpane.php', array($this, 'option_page'));
	if ( !function_exists('wp_nonce_field') ) {
		$this->nonce = -1;
	} else {
		$this->nonce = 'lwgm-config';
	}
}

// ==================================================
// private
function make_nonce_field($action = -1) {
	if ( !function_exists('wp_nonce_field') ) {
		return;
	} else {
		return wp_nonce_field($action);
	}
}

// ==================================================
// private 
function output_zoom_select($zoom_level) {
	$options = array();
	$options[$zoom_level] = ' selected="selected"';
	for ($lv = GOOGLE_MAPS_MAX_ZOOM; $lv >= 1; $lv--) {
		echo '<option value="' . $lv . '"' . $options[$lv] . ">$lv</option>\n";
	}
	return;
}

// ==================================================
// private 
function map_type_checkbox($map_type) {
	$check_boxes = array('', '', '', '');
	switch ($map_type) {
	case 'LWGM_LINK_TO_MAP':
		$check_boxes[0] = ' checked="checked"';
		break;
	case 'G_NORMAL_MAP':
		$check_boxes[1] = ' checked="checked"';
		break;
	case 'G_SATELLITE_MAP':
	case 'G_HYBRID_MAP':
		$check_boxes[2] = ' checked="checked"';
		break;
	case 'G_PHYSICAL_MAP':
		$check_boxes[3] = ' checked="checked"';
		break;
	}
	return $check_boxes;
}

// ==================================================
function option_page() {
	if (isset($_POST['update_option'])) {
		check_admin_referer ($this->nonce);
		$this->upate_options();
	}
	if (isset($_POST['delete_option'])) {
		check_admin_referer($this->nonce);
		$this->delete_options();
	}
	$gmap_api_key     = $this->get_option('googlemaps_api_key', false, 'yf_google_api_key');
	$page_id          = $this->get_option('lw_fixed_map_page_id', false, 'yf_google_page_id');
	$fixed_map_width  = $this->get_option('lw_fixed_map_width', false, 'yf_google_width');
	$fixed_map_height = $this->get_option('lw_fixed_map_height', false, 'yf_google_height');
	$fixed_map_type   = $this->get_option('lw_fixed_map_type', false, 'yf_map_type');
	$recent_markers   = $this->get_option('lw_num_recent_markers');
	$each_map_width   = $this->get_option('lw_each_map_width');
	$each_map_height  = $this->get_option('lw_each_map_height');
	$mobile_map_width   = $this->get_option('lw_mobile_map_width');
	$mobile_map_height  = $this->get_option('lw_mobile_map_height');
	$fixed_map_type_check = $this->map_type_checkbox($fixed_map_type);
	$recent_map_zoom = $this->get_zoom_level('lw_recent_map_zoom', 0);
	if (! $recent_map_zoom) {
		$recent_map_zoom = $this->get_zoom_level('yf_zoom_level', LWGM_ZOOM_OF_RECENT_LOCATIONS);
	}
	$addressed_map_zoom = $this->get_zoom_level('lw_addressed_map_zoom', LWGM_ZOOM_OF_ADDRESSED_MAP);
	$each_map_type_check = $this->map_type_checkbox(get_option('lw_each_map_type'));
	$each_map_zoom = $this->get_zoom_level('lw_each_map_zoom', LWGM_ZOOM_OF_EACH_MAP);
?>
<div class="wrap">
<h2>Lightweight Google Maps</h2>
<form method="post">
<?php Lightweight_Google_Maps_Prefs::make_nonce_field($this->nonce); ?>
<h3 id="apikey"><?php _e('Your Googlemap API Key', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr>
  <th width="20%" scope="row"><label for="googlemaps_api_key"><?php _e('API Key:', 'lw_googlemaps'); ?></label></th>
  <td><input type="text" name="googlemaps_api_key" id="googlemaps_api_key" size="90" /><br />
  <?php echo sprintf(__("(don't have one? get one <a href=%s>here</a>)", 'lw_googlemaps'), '"http://www.google.com/apis/maps/signup.html"'); ?></td>
</tr><tr>
  <th><?php _e('Current API Key:', 'lw_googlemaps'); ?></th>
  <td><em><?php echo wp_specialchars($gmap_api_key); ?></em></td>
</tr>
</tbody></table>
<h3 id="fixedpage"><?php _e('Map on fixed pages', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr> 
  <th width="20%" scope="row"><label for="fixed_map_page_id"><?php _e('Page ID:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($page_id ? intval($page_id) : ''); ?>" name="fixed_map_page_id" id="fixed_map_page_id" /> <?php _e('(Leave empty no to use the fixed map)', 'lw_googlemaps'); ?>
    <div><?php _e('This is a numeric id of the map page. You can find the id number from <a href="edit-pages.php">the Manage/Pages screen</a>.', 'lw_googlemaps'); ?></div>
  </td>
</tr><tr> 
  <th width="20%" scope="row"><label for="fixed_map_type"><?php _e('Type:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <label><input type="radio" name="fixed_map_type" id="fixed_map_type" value="G_NORMAL_MAP"<?php echo $fixed_map_type_check[1]; ?> /> <?php _e('Graphic', 'lw_googlemaps'); ?></label>
    <label><input type="radio" name="fixed_map_type" id="fixed_map_type" value="G_HYBRID_MAP"<?php echo $fixed_map_type_check[2]; ?> /> <?php _e('Hybrid', 'lw_googlemaps'); ?></label>
    <label><input type="radio" name="fixed_map_type" id="fixed_map_type" value="G_PHYSICAL_MAP"<?php echo $fixed_map_type_check[3]; ?> /> <?php _e('Terrain', 'lw_googlemaps'); ?></label>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="fixed_map_width"><?php _e('Width:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($fixed_map_width ? intval($fixed_map_width) : ''); ?>" name="fixed_map_width" id="fixed_map_width" /> px <?php _e('(Default: Follow window size)', 'lw_googlemaps'); ?>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="fixed_map_height"><?php _e('Height:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($fixed_map_height ? intval($fixed_map_height) : ''); ?>" name="fixed_map_height" id="fixed_map_height" /> px <?php printf(__('(Default: %dpx)', 'lw_googlemaps'), $this->get_option('lw_fixed_map_height', true)); ?>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="recent_map_zoom"><?php _e('Zoom level of recent locations:', 'lw_googlemaps'); ?></label></th>
  <td>
    <select name="recent_map_zoom" id="recent_map_zoom">
    <?php $this->output_zoom_select($recent_map_zoom); ?>
    </select>
    <span><?php _e('(1: Whole globe, Bigger: Narrower area)', 'lw_googlemaps'); ?></span>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="num_recent_markers"><?php _e('Number of recent location markers:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($recent_markers ? intval($recent_markers) : ''); ?>" name="num_recent_markers" id="num_recent_markers" /> <?php _e('points', 'lw_googlemaps'); ?>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="addressed_map_zoom"><?php _e('Zoom level of addressed map:', 'lw_googlemaps'); ?></label></th>
  <td>
    <select name="addressed_map_zoom" id="addressed_map_zoom">
    <?php $this->output_zoom_select($addressed_map_zoom); ?>
    </select>
    <span><?php _e('(1: Whole globe, Bigger: Narrower area)', 'lw_googlemaps'); ?></span>
  </td>
</tr>
</tbody></table>
<h3 id="eachmap"><?php _e('Map for each entries', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr> 
  <th width="20%" scope="row"><label for="each_map_type"><?php _e('Type:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <label><input type="radio" name="each_map_type" id="each_map_type" value="LWGM_LINK_TO_MAP"<?php echo $each_map_type_check[0]; ?> /> <?php _e('Link to my fixed map', 'lw_googlemaps'); ?></label>
    <label><input type="radio" name="each_map_type" id="each_map_type" value="G_NORMAL_MAP"<?php echo $each_map_type_check[1]; ?> /> <?php _e('Graphic', 'lw_googlemaps'); ?></label>
    <label><input type="radio" name="each_map_type" id="each_map_type" value="G_HYBRID_MAP"<?php echo $each_map_type_check[2]; ?> /> <?php _e('Hybrid', 'lw_googlemaps'); ?></label>
    <label><input type="radio" name="each_map_type" id="each_map_type" value="G_PHYSICAL_MAP"<?php echo $each_map_type_check[3]; ?> /> <?php _e('Terrain', 'lw_googlemaps'); ?></label>
  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="each_map_width"><?php _e('Width:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($each_map_width ? intval($each_map_width) : ''); ?>" name="each_map_width" id="each_map_width" /> px <?php printf(__('(Default: %dpx)', 'lw_googlemaps'), $this->get_option('lw_each_map_width', true)); ?>

    </td>
</tr><tr>
  <th width="20%" scope="row"><label for="each_map_height"><?php _e('Height:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo ($each_map_height ? intval($each_map_height) : ''); ?>" name="each_map_height" id="each_map_height" /> px <?php printf(__('(Default: %dpx)', 'lw_googlemaps'), $this->get_option('lw_each_map_height', true)); ?>

  </td>
</tr><tr>
  <th width="20%" scope="row"><label for="each_map_zoom"><?php _e('Zoom level:', 'lw_googlemaps'); ?></label></th>
  <td>
    <select name="each_map_zoom" id="each_map_zoom">
    <?php $this->output_zoom_select($each_map_zoom); ?>
    </select>
    <span><?php _e('(1: Whole globe, Bigger: Narrower area)', 'lw_googlemaps'); ?></span>
  </td>
</tr>
</tbody></table>
<h3 id="mobile"><?php _e('Map for mobile', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr>
  <th width="20%" scope="row"><label for="mobile_map_width"><?php _e('Width:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo $this->get_option('lw_mobile_map_width'); ?>" name="mobile_map_width" id="mobile_map_width" /> px <?php printf(__('(Default: %dpx, Max: %dpx)', 'lw_googlemaps'), $this->get_option('lw_mobile_map_width', true), LWGM_MOBILE_WIDTH_MAX); ?>

    </td>
</tr><tr>
  <th width="20%" scope="row"><label for="mobile_map_height"><?php _e('Height:', 'lw_googlemaps'); ?></label></th> 
  <td>
    <input type="text" value="<?php echo $this->get_option('lw_mobile_map_height'); ?>" name="mobile_map_height" id="mobile_map_height" /> px <?php printf(__('(Default: %dpx, Max: %dpx)', 'lw_googlemaps'), $this->get_option('lw_mobile_map_height', true), LWGM_MOBILE_HEIGHT_MAX); ?>

  </td>
</tr>
</tbody></table>
<h3 id="convertgeo"><?php _e('Convert geo locations', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr><td><label>
  <input type="checkbox" name="convert_geo_locations" id="convert_geo_locations" value="1"/> <?php _e('Convert location data from Geo plugin.', 'lw_googlemaps'); ?>
</label><br />
<?php _e('Notice: Please de-activate Geo plugin and wp-eznavi plugin BEFORE checking this option.', 'lw_googlemaps'); ?></td></tr>
</tbody></table>
<div class="submit">
<input type="hidden" name="action" value="update" />
<input type="submit" name="update_option" id="update_option" value="<?php 
if ($this->check_wp_version('2.5', '>=')) {
	_e('Save Changes');
} elseif ($this->check_wp_version('2.1', '>=')) {
	_e('Update Options &raquo;');
} else {
	echo __('Update Options') . " &raquo;";
} ?>" />
</div>
<hr />
<h3 id="delete_options"><?php _e('Delete Options', 'lw_googlemaps'); ?></h3>
<table class="optiontable form-table"><tbody>
<tr><td><label>
  <input type="checkbox" name="delete_latlong" id="delete_latlong" value="1" /> <?php _e('Delete all locations (Lat_Long custom field) from posts and pages.', 'lw_googlemaps'); ?>
</label></td></tr>
</tbody></table>
<div class="submit">
<input type="submit" name="delete_option" value="<?php _e('Delete option values and revert them to default &raquo;', 'lw_googlemaps'); ?>" onclick="return confirm('<?php _e('Do you really delete option values and revert them to default?', 'lw_googlemaps'); ?>')" />
</div>
</form>
</div>
<?php
} 

// ==================================================
// private 
function upate_options() {
	if (isset($_POST['convert_geo_locations']) && $_POST['convert_geo_locations']) {
		$this->convert_geo_locations();
	}
	if (isset($_POST['googlemaps_api_key']) && trim($_POST['googlemaps_api_key'])) {
		update_option('googlemaps_api_key', trim($_POST['googlemaps_api_key']));
		delete_option('yf_google_api_key');
	} elseif ($gmap_api_key = get_option('yf_google_api_key')) {
		update_option('googlemaps_api_key', $gmap_api_key);
		delete_option('yf_google_api_key');
	}
	$this->update_int_option('fixed_map_page_id', 'yf_google_page_id');
	$this->update_sel_option('fixed_map_type', 'yf_map_type');
	$this->update_int_option('fixed_map_width', 'yf_google_width');
	$this->update_int_option('fixed_map_height', 'yf_google_height');
	$this->update_sel_option('recent_map_zoom', 'yf_zoom_level');
	$this->update_int_option('num_recent_markers');
	$this->update_sel_option('addressed_map_zoom');
	$this->update_sel_option('each_map_type');
	$this->update_sel_option('each_map_zoom');
	$this->update_int_option('each_map_width');
	$this->update_int_option('each_map_height');
	$this->update_int_option('mobile_map_width', NULL, LWGM_MOBILE_WIDTH_MAX);
	$this->update_int_option('mobile_map_height', NULL, LWGM_MOBILE_HEIGHT_MAX);
	delete_option('yf_default_latitude');
	delete_option('yf_default_longitude');
	delete_option('yf_use_thumbs');
	delete_option('yf_thumb_width');
	delete_option('yf_thumb_height');
	delete_option('yf_thumb_url');
	delete_option('yf_thumb_css');
?>
<div class="updated fade"><p><strong><?php _e('Options saved.'); ?></strong></p></div>
<?php
	return;
}

// ==================================================
// private 
function update_int_option($key, $old_key = NULL, $max = NULL) {
	$value = @$_POST[$key];
	if ($max && $value > $max) {
		update_option('lw_' . $key, intval($max));	
	} elseif ($value > 0) {
		update_option('lw_' . $key, intval($value));
	} else {
		delete_option('lw_' . $key);
	}
	if ($old_key) {
		delete_option($old_key);
	}
}

// ==================================================
// private 
function update_sel_option($key, $old_key = NULL) {
	$value = @$_POST[$key];
	if ($value) {
		update_option('lw_' . $key, $value);
		if ($old_key) {
			delete_option($old_key);
		}
	}
}

// ==================================================
// private 
function convert_geo_locations() {
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '_geo_location' AND meta_value = ','");
	$wpdb->query("UPDATE {$wpdb->postmeta} SET meta_key = '" . GEO_META_FIELD_NAME . "' WHERE meta_key = '_geo_location'");
	return;
}

/* ==================================================
 * @param	none
 * @return	none
 */
private function delete_options() {
	delete_option('googlemaps_api_key');
	delete_option('lw_fixed_map_page_id');
	delete_option('lw_fixed_map_type');
	delete_option('lw_fixed_map_width');
	delete_option('lw_fixed_map_height');
	delete_option('lw_recent_map_zoom');
	delete_option('lw_num_recent_markers');
	delete_option('lw_addressed_map_zoom');
	delete_option('lw_each_map_type');
	delete_option('lw_each_map_zoom');
	delete_option('lw_each_map_width');
	delete_option('lw_each_map_height');
	delete_option('lw_mobile_map_width');
	delete_option('lw_mobile_map_height');
	delete_option('yf_google_api_key');
	delete_option('yf_google_page_id');
	delete_option('yf_map_type');
	delete_option('yf_google_width');
	delete_option('yf_google_height');
	delete_option('yf_zoom_level');
	delete_option('yf_default_latitude');
	delete_option('yf_default_longitude');
	delete_option('yf_use_thumbs');
	delete_option('yf_thumb_width');
	delete_option('yf_thumb_height');
	delete_option('yf_thumb_url');
	delete_option('yf_thumb_css');
	if (isset($_POST['delete_latlong']) && $_POST['delete_latlong']) {
		$this->delete_latlong();
	}
?>
<div class="updated fade"><p><strong><?php _e('Options Deleted.', 'lw_googlemaps'); ?></strong></p></div>
<?php
	return;
}

// ==================================================
// private 
function delete_latlong() {
	global $wpdb;
	$wpdb->query("DELETE FROM {$wpdb->postmeta} WHERE meta_key = '" . GEO_META_FIELD_NAME . "'");
	return;
}


// ===== End of class ====================
}

?>