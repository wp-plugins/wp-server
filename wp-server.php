<?php
/*
Plugin Name: WP Server
Plugin URI: http://nabtron.com/wp-server-plugin/
Description: Show average server load and uptime for last 1, 5 and 15 minutes of your linux server on top in admin panel with 2 modes to select from. 
Tags: show, server, load, average, wordpress, processes, website
Version: 2.1
Author: Nabtron
Author URI: http://nabtron.com
Min WP Version: 4.4
Max WP Version: 4.5
*/

/* registering activation and uninstall hooks */
register_activation_hook( __FILE__, array( 'nabserver_main', 'activation' ) );
register_uninstall_hook( __FILE__, array('nabserver_main', 'uninstall') );

if (!class_exists('nabserver_main')) {
	class nabserver_main {

		private $show_wp_server_status;

		// PHP 4 Compatible Constructor
		public function nabserver_main(){$this->__construct();}

		// PHP 5 Constructor
		public function __construct(){
			add_action( 'admin_init', array( $this, 'page_init' ) );
			add_action( 'admin_menu', array( $this, 'nabserver_add_menu' ) );
		}

		public function page_init() {
			// to show the server load or not
			$show_wp_server_status = get_option( 'nabserver_show' );
			if( 0 != $show_wp_server_status){
				add_action('admin_head', array( $this, 'wp_server_status_css' ) );

				switch ( $show_wp_server_status ){
					case '1':
						add_action('admin_footer', array( $this, 'wp_server_status' ) );
					break;
					case '2':
						add_action('admin_footer', array( $this, 'wp_server_status_legacy' ) );
					break;
					default:
						// nothing by default, yeah
				}
			}

            if ( !wp_verify_nonce( $_POST['nabserver_noncename'], plugin_basename(__FILE__) )) {
                return;
            }
            if ( !current_user_can( 'manage_options' )){
                return;
            }
			// Update routines
			if ('insert' == $_POST['action_nabserver']) {
				update_option( 'nabserver_show', $_POST['nabserver_show'] );
			}
		}

		function wp_server_status_css() {
			echo '<style type="text/css">
				#wp_server_status {
					position: absolute;
					top: 30px;
					margin: 0;
					padding: 0;
					right: 80px;
					font-size: 12px;
					color: #d54e21;
					font-family: lucida grande;
				}
			</style>';
		}

		static function activation() {
			if(!get_option( 'nabserver_show' )) {
				update_option( 'nabserver_show' , '1' );
			}
		}

		function wp_server_status() {
			$serverresult = @exec('uptime');

			preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/",$serverresult,$average);

			$uptime = explode(' up ', $serverresult);
			$uptime = explode(',', $uptime[1]);
			$uptime = $uptime[0].', '.$uptime[1];

			$autolegacy = 0;
			if('' == $average[1]){
				$autolegacy = 1;
			}

			if('0' == $autolegacy){
				$server_load_notice = "Server Load Averages</b>&nbsp;: $average[1], $average[2], $average[3] . Running for last <b>$uptime</b> hours";
			}else{
				$server_load_notice = $serverresult;
			}

			$memory_usage = $this->get_server_memory_usage;
			$memory_usage_notice = '';
			if('' != $memory_usage){
				$memory_usage_notice = 'RAM used: <b>'.$memory_usage.'</b> .';
			}

			echo "<p id='wp_server_status'><span>$server_load_notice $memory_usage_notice</span></p>";
		}

		function wp_server_status_legacy() {
			$serverresult = @exec('uptime');

			$memory_usage = $this->get_server_memory_usage;
			$memory_usage_notice = '';
			if('' != $memory_usage){
				$memory_usage_notice = '- RAM used: <b>'.$memory_usage.'</b> .';
			}

			echo "<p id='wp_server_status'><span> $serverresult $memory_usage_notice</span></p>";
		}

		/**
		 * returns the memory used by server at the time of execution
		 * @return string memory usage percentage
		 */
		function get_server_memory_usage(){

		    $free = shell_exec('free');
		    $free = (string)trim($free);
		    $free_arr = explode("\n", $free);
		    $mem = explode(" ", $free_arr[1]);
		    $mem = array_filter($mem);
		    $mem = array_merge($mem);
		    $memory_usage = $mem[2]/$mem[1]*100;
		    $memory_usage = $memory_usage . '%';

		    return $memory_usage;
		}

		/**
		 * not being used right now, test more to find difference from @exec
		 * @return string returns the servers cpu load for last 1 min, 5 min and 15 min
		 */
		function get_server_cpu_usage(){

		    $load = sys_getloadavg();
		    return $load[0];

		}

		// Admin menu page
		public function nabserver_add_menu() {
			add_options_page('WP Server Options', 'WP Server', 'manage_options', __FILE__, array( $this, 'nabserver_option_page') );
		}

		public function nabserver_option_page() {
	//	$nabserver_urltosubmit = str_replace('&updated=true', '', $_SERVER["REQUEST_URI"]);
		$nabserver_urltosubmit = $_SERVER["REQUEST_URI"];
		?>
		<!-- Start Options Admin area -->
		<style>
		.nabserver_main_options_section{
			vertical-align:top;
			display:inline-block;
			padding: 0px 10px;
		}
		@media only screen and (min-width: 800px){
			.nabserver_main_options_section{
				width: 30%;
			}
		}
		@media only screen and (max-width: 799px){
			.nabserver_main_options_section{
				width: 100%;
			}
		}
		</style>
		<div class="wrap">
			<h2>WP Server Options</h2>
			<div style="margin-top:20px;">
				<div class="nabserver_main_options_section" style="">
				<form method="post" action="<?php echo $nabserver_urltosubmit; ?>&amp;updated=true">
					<h3>Settings</h3>
					<p>
					<input type="radio" name="nabserver_show" value="0" id="0" <?php checked(0,get_option( 'nabserver_show' ));?>>
					<label for="0">Turn Off</label>
					</p>
					<p>
					<input type="radio" name="nabserver_show" value="1" id="1" <?php checked(1,get_option( 'nabserver_show' ));?>>
					<label for="1">Turn On</label> <i>(try this first)</i>
					</p>
					<p>
					<input type="radio" name="nabserver_show" value="2" id="2" <?php checked(2,get_option( 'nabserver_show' ));?>>
					<label for="2">Legacy mode</label> <i>(try this if "Turn On" doesn't)</i>
					</p>
					<p>
					<p class="submit_nabserver">
                        <input type="hidden" name="nabserver_noncename" id="nabserver_noncename" value="<?php echo wp_create_nonce( plugin_basename(__FILE__) ); ?>" />
						<input name="submit_nabserver" type="submit" class="button-primary" id="submit_nabserver" value="Save changes">
						<input class="submit" name="action_nabserver" value="insert" type="hidden" />
					</p>
				</form>
				</div>
				<!-- explanation block starts -->
				<div class="nabserver_main_options_section" style="">
					<h3>Explanation</h3>
					<p>The three values of server load tell the server load averages for last: </p>
					<p><strong>"1 minute, 5 minutes, 15 minutes"</strong>, in same sequence.</p>
					<h4>What does each value mean</h4>
					<p>1.0 means that the server core is at 100% cpu load.</p>
					<p>This means that if you have a single core server, 1.0 means that your server is completely loaded.</p>
					<p>Any value above 1.0 means that your server is now overloaded</p>
					<p>Occasional loads above 1.0 are fine. You should make sure that the last one, 15 minutes average, remains well under 1.0 under normal conditions (if you're on a single core cpu).</p>
					<p>On multicore, you should divide value by two. Means if you're on dual core cpu with load of 2.0, it means that total load is 100%, meaning both cores are at their maximum.</p>
					<p>Our personal recommendation is to upgrade your server if your load remains above 0.7. But before upgrading, check if your server and website is setup properly, including cache and cdn, etc.</p>
					<p>Huge number and size of files can affect the server load time too. Don't forget bruteforce attacks too!</p>
				</div>
				<!-- explanation block ends -->
				<!-- hire me block starts -->
				<div class="nabserver_main_options_section" style="">
					<h3>Hire me!</h3>
					<p>Hire me if you need any services related to:</p>
					<ul>
						<li>WordPress development (including themes and plugins development)</li>
						<li>Optimizing website load speed (configuring server, site modifications, etc.)</li>
						<li>Setting up CDN (amazon, cloudflare, any other)</li>
						<li>Setting up cache plugins to their best optimum settings (wp super cache, w3 total cache, etc)</li>
					</ul>
					<p>
						<span style="color: #F00;font-weight:bold;font-size:1.2em;">Need a WordPress Developer?</span>
						<br /><br /><br /><br />
						<a href="http://nabtron.com/hire-me/">
							<span style="padding:40px;font-weight:bold;color:white;background-color:#06C;font-size: 2em;">
								Hire Me
							</span>
						</a>
					</p>
				</div>
				<!-- hire me block ends -->
			</div>
			<div style="clear:both;"></div>
			<p>&nbsp;</p>
			<hr />
			<center>
				<h4>Developed by <a href="http://nabtron.com/" target="_blank">Nabtron</a>.</h4>
			</center>
		</div>
	<?php
		} // End method nabserver_option_page()

		static function uninstall() {
			delete_option( 'nabserver_show');
		}
	}
}

//instantiate the class
if (class_exists('nabserver_main')) {
	$nabserver_main = new nabserver_main();
}

//reference:
//https://codex.wordpress.org/Creating_Options_Pages
?>