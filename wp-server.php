<?php
/*
Plugin Name: WP Server
Plugin URI: http://nabtron.com/wp-server-plugin/6950/
Description: Show average server load and uptime of your linux server on top in admin panel 
Version: 1.0
Author: Nabeel Khan
Author URI: http://nabtron.com
*/
?>
<?php
add_action('admin_head', 'wp_server_status_css');
function wp_server_status_css() {
	echo "<style type='text/css'>#wp_server_status {position: absolute;top: 8px;margin: 0;padding: 0;right: 200px;font-size: 12px;color: #d54e21;}</style>";
}

add_action('admin_footer', 'wp_server_status' );
function wp_server_status() {
	$serverresult = @exec('uptime');
		preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$serverresult,$average);

	$uptime = explode(' up ', $serverresult);
	$uptime = explode(',', $uptime[1]);
	$uptime = $uptime[0].', '.$uptime[1];
	echo "<p id='wp_server_status'><span>Server Load Averages</b>&nbsp;: $average[1], $average[2], $average[3] . Running for last $uptime hours </p>";
}
?>