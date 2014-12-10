<?php
/*
Plugin Name: WP Server
Plugin URI: http://nabtron.com/wp-server-plugin/6950/
Description: Show average server load and uptime for last 1, 5 and 15 minutes of your linux server on top in admin panel with 2 modes to select from. 
Version: 2.0
Author: Nabtron
Author URI: http://nabtron.com
*/
?>
<?php

// Update routines
if ('insert' == $_POST['action_nabserver']) {
        update_option("nabserver_show",$_POST['nabserver_show']);
}

if (!class_exists('nabserver_main')) {
	class nabserver_main {
		// PHP 4 Compatible Constructor
		function nabserver_main(){$this->__construct();}

		// PHP 5 Constructor
		function __construct(){
			add_action('admin_menu', 'nabserver_description_add_menu');
		}
	}


	function nabserver_description_option_page() {
	$nabserver_urltosubmit = str_replace('&updated=true', '', $_SERVER["REQUEST_URI"]);
	?>

	<!-- Start Options Admin area -->
	<div class="wrap">
		<h2>WP Server Options</h2>
		<div style="margin-top:20px;">
			<div style="width:45%;float:left;padding-left:10px;">
			<form method="post" action="<?php echo $nabserver_urltosubmit; ?>&amp;updated=true">
				<p><strong>Settings</strong></p>
				<br>
				<input type="radio" name="nabserver_show" value="0" id="0" <?php checked(0,get_option( 'nabserver_show' ));?>>
				<label for="0">Turn Off</label>
				<br /><br />
				<input type="radio" name="nabserver_show" value="1" id="1" <?php checked(1,get_option( 'nabserver_show' ));?>>
				<label for="1">Turn On</label>
				<br /><br />
				<input type="radio" name="nabserver_show" value="2" id="2" <?php checked(2,get_option( 'nabserver_show' ));?>>
				<label for="2">Legacy mode</label>
				<br /><br />
				<br>
				<p class="submit_nabserver">
					<input name="submit_nabserver" type="submit" id="submit_nabserver" value="Save changes &raquo;">
					<input class="submit" name="action_nabserver" value="insert" type="hidden" />
				</p>
			</form>
			</div>
			<div style="width: 48%;float:right;text-align:right;padding-top: 50px;">
				<p>
									<span style="color: #F00;font-weight:bold;font-size:1.2em;">Need a WordPress Developer?</span>
<br /><br /><br /><br />
					<a href="http://nabtron.com/hire-me/"><span style="padding:40px;font-weight:bold;color:white;background-color:#06C;font-size: 2em;">Hire Me</span></a>
				</p>
			</div>
		</div>
		<div style="clear:both;"></div>
		<br />
		<br />
		<hr />
		<center>
			<h4>Developed by <a href="http://nabtron.com/" target="_blank">Nabtron</a>.</h4>
		</center>
	</div>

<?php
	} // End function nabserver_description_option_page()

	// Admin menu Option
	function nabserver_description_add_menu() {
		add_options_page('WP Server Options', 'WP Server', 'manage_options', __FILE__, 'nabserver_description_option_page');
	}
}
//instantiate the class
if (class_exists('nabserver_main')) {
	$nabserver_main = new nabserver_main();
}

$show_wp_server_status = get_option( 'nabserver_show' );

// 0 means off
if($show_wp_server_status == '0'){

}

// 1 means on
if($show_wp_server_status == '1'){
add_action('admin_head', 'wp_server_status_css');
add_action('admin_footer', 'wp_server_status' );
}

// 2 means legacy, output without beautifying
if($show_wp_server_status == '2'){
add_action('admin_head', 'wp_server_status_css');
add_action('admin_footer', 'wp_server_status_legacy' );
}


function wp_server_status_css() {
	echo "<style type='text/css'>#wp_server_status {position: absolute;top: 30px;margin: 0;padding: 0;right: 80px;font-size: 12px;color: #d54e21;font-family: lucida grande;}</style>";
}


function wp_server_status() {
	$serverresult = @exec('uptime');

		preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$serverresult,$average);

	$uptime = explode(' up ', $serverresult);
	$uptime = explode(',', $uptime[1]);
	$uptime = $uptime[0].', '.$uptime[1];
	echo "<p id='wp_server_status'><span> Server Load Averages</b>&nbsp;: $average[1], $average[2], $average[3] . Running for last $uptime hours </span></p>";
}

function wp_server_status_legacy() {
	$serverresult = @exec('uptime');
	echo "<p id='wp_server_status'><span> $serverresult </span></p>";
}
?>