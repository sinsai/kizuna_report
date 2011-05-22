<?php

function mobile_get_archives($before = '<li>', $after= '') {
	global $wpdb, $tableposts;

	$result = $wpdb->get_results(
					"SELECT DISTINCT YEAR(post_date), MONTH(post_date) ".
					"FROM ".$tableposts." ".
					"WHERE post_status = 'publish' ".
					"AND post_date < '".date('Y-m-d H:i:s')."' ".
					((mobile_get_bloginfo('version') > 2) ? "AND post_type = 'post' " : '').
					"ORDER BY post_date DESC;"
			, ARRAY_A);

	if (is_array($result)) {
		foreach ($result as $row) {
			$year  = $row['YEAR(post_date)'];
			$month = substr('0'.$row['MONTH(post_date)'], -2);
			echo $before
				.'<a href="'.mobile_get_bloginfo('home').'?view=menu&mode=4&m='.$year.$month.'#archive">'
				.$year.'-'.$month.'</a>'
				.$after;
		}
	}

}

function mobile_get_monthArchives($before = '<li>', $after= '') {
	global $wpdb, $tableposts;

	$m = (int)$_GET['m'];
	if (strlen($m) !== 6) return;
	$year = substr($m, 0, 4);
	$month = substr($m, 4, 2);

	$result = $wpdb->get_results(
					"SELECT ID, post_title, post_date, comment_count ".
					"FROM ".$tableposts." ".
					"WHERE MONTH(post_date) = '".$month."' ".
					"AND YEAR(post_date) = '".$year."' ".
					"AND post_status = 'publish' ".
					"AND post_date < '".date('Y-m-d H:i:s')."' ".
					((mobile_get_bloginfo('version') > 2) ? "AND post_type = 'post' " : '').
					"ORDER BY post_date DESC;"
			);

	if (is_array($result)) {
		foreach ($result as $row) {
			echo $before.substr($row->post_date, 5, 5).
				' <a href="'.mobile_get_bloginfo('home').'?p='.$row->ID.'">'.
				stripslashes($row->post_title).
				'</a>('.$row->comment_count.')';
		}
	}

}


function mobile_get_recentPosts($num = 10, $before = '<li>', $after= '') {
	global $wpdb, $tableposts;

	$result = $wpdb->get_results(
					"SELECT ID, post_title, post_date, comment_count ".
					"FROM ".$tableposts." ".
					"WHERE post_status = 'publish' ".
					"AND post_date < '".date('Y-m-d H:i:s')."' ".
					((mobile_get_bloginfo('version') > 2) ? "AND post_type = 'post' " : '').
					"ORDER BY post_date DESC ".
					"LIMIT ".(int)$num.";"
			);

	if (is_array($result)) {
		foreach ($result as $row) {
			echo $before.substr($row->post_date, 5, 5).
				' <a href="'.mobile_get_bloginfo('home').'?p='.$row->ID.'">'.
				stripslashes($row->post_title).
				'</a>('.$row->comment_count.')';
		}
	}

}

?>