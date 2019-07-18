<?php
/**
 * Astra Sites Tracker
 *
 * @since  x.x.x
 * @package Astra Sites
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'Astra_Sites_Tracker' ) ) :

	/**
	 * Astra_Sites_Tracker
	 */
	class Astra_Sites_Tracker {

		/**
		 * API URL which is used to get the response from.
		 *
		 * @since  x.x.x
		 * @var (String) URL
		 */
		public static $api_url;

		/**
		 * Instance of Astra_Sites_Tracker
		 *
		 * @since  x.x.x
		 * @var (Object) Astra_Sites_Tracker
		 */
		private static $_instance = null;

		/**
		 * Instance of Astra_Sites_Tracker.
		 *
		 * @since  x.x.x
		 *
		 * @return object Class object.
		 */
		public static function get_instance() {
			if ( ! isset( self::$_instance ) ) {
				self::$_instance = new self;
			}

			return self::$_instance;
		}

		/**
		 * Constructor.
		 *
		 * @since  x.x.x
		 */
		private function __construct() {

			self::set_api_url();

			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ) );

			// AJAX.
			add_action( 'wp_ajax_push_to_ga', array( $this, 'push' ) );
		}

		/**
		 * Send View Count to the Server.
		 *
		 * @since  x.x.x
		 */
		public function push() {

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( 'You can\'t access this action.' );
			}

			// Control Logic goes here.

			$response = wp_remote_post(
				self::get_api_url(),
				array(
					'body' => $_POST['params'],
				)
			);

			wp_send_json_success(
				array(
					'response' => wp_remote_retrieve_body( $response ),
				)
			);
		}

		/**
		 * Setter for $api_url
		 *
		 * @since  x.x.x
		 */
		public static function set_api_url() {

			self::$api_url = apply_filters( 'astra_sites_tracking_api_url', 'https://www.google-analytics.com/collect' );

		}

		/**
		 * Getter for $api_url
		 *
		 * @since  x.x.x
		 */
		public static function get_api_url() {
			return self::$api_url;
		}

		/**
		 * Enqueue admin scripts.
		 *
		 * @since  x.x.x
		 *
		 * @param  string $hook Current hook name.
		 * @return void
		 */
		public function admin_enqueue( $hook = '' ) {

			$params = self::get_tracking_data();

			$tracking_data = array(
				'params'   => $params,
				'url'      => self::get_api_url(),
				'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
			);

			wp_enqueue_script( 'astra-sites-tracking', ASTRA_SITES_URI . 'inc/assets/js/tracking.js', array( 'jquery' ), ASTRA_SITES_VER, 'all' );
			wp_localize_script( 'astra-sites-tracking', 'trackingData', $tracking_data );

		}

		/**
		 * Get all the tracking data.
		 *
		 * @return array
		 */
		private static function get_tracking_data() {

			$data = array(
				'tid' => 'UA-29853075-4', // "Client ID" for "WP-CLI Usage".
				'cid' => self::gen_uuid(), // "User ID".
				't'   => 'pageview',
				'v'   => 1, // API v1.
				'aip' => 1, // Anon user IP.
				'an'  => 'Astra Sites', // "Plugin Name" => "Application name" => ga:appName
			);

			// 'sr'  => ini_get( 'memory_limit' ), // "Memory Limit" => Screen Resolution => ga:screenResolution
			// 'cc'  => phpversion(), // "PHP Version" => "Campaign Content" => ga:adContent

			// Domain => Document Host => ga:hostname.
			$data['dh'] = site_url();

			// Admin Email -> cc -> "Campaign Content".
			$data['cc'] = apply_filters( 'astra_sites_tracker_admin_email', get_option( 'admin_email' ) );

			// WordPress.
				// Version -> cd -> "Campaign Name".
				$data['cd'] = get_bloginfo( 'version' );
				// User Locale -> ul -> "User Language" -> ga:language.
				$data['ul'] = get_locale();
				// Multisite -> je -> "Java Enabled" -> ga:javaEnabled.
				$data['je'] = is_multisite();
				// Debug Mode -> ck -> "Campaign Keyword" -> ga:keyword.
				$data['ck'] = ( defined( 'WP_DEBUG' ) && WP_DEBUG ) ? true : false;
				// Memory Limit -> sr -> "Screen Resolution".
				$memory     = self::get_memory_info();
				$data['sr'] = size_format( $memory );

			// @codingStandardsIgnoreStart

			// Theme.
				// "Parent Theme" => Document Path" => ga:pagePath
				$data['dp'] = get_template();
				// "Child Theme" => "Campaign Medium" => ga:medium
				$data['cm'] = get_stylesheet();
				// Viewport size => ga:browserSize
				$data['vp'] = self::get_curl_version( 'version' );
				// "SSL Version" => Screen Colors => ga:screenColors
				$data['sd'] = self::get_curl_version( 'ssl_version' );
				// "Server Software" => Flash Version => ga:flashVersion
				$data['fl'] = ( isset( $_SERVER['SERVER_SOFTWARE'] ) && ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) ? $_SERVER['SERVER_SOFTWARE'] : 'Not Available';

			// @codingStandardsIgnoreEnd

			// Plugin.
				$all_plugins = self::get_all_plugins();
				// Plugin info -> cd -> "Screen Name".
				$data['cd'] = json_encode( $all_plugins['active_plugins'] );

			// Astra Site Data.
				$astra_site_data = array(
					'astra-sites-settings'  => get_option( 'astra_sites_settings' ),
					'astra-sites-favorites' => get_option( 'astra-sites-favorites' ),
				);
				// Plugin info -> dl -> "Document Location URL".
				$data['dl'] = json_encode( $astra_site_data );

				// Astra Site Version -> Application Version" => ga:appVersion.
				$data['av'] = ASTRA_SITES_VER;

				return apply_filters( 'astra_sites_tracker_data', $data );
		}

		/**
		 * Get User ID.
		 *
		 * @return string
		 */
		public static function gen_uuid() {
			return sprintf(
				'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				// 16 bits for "time_mid"
				mt_rand( 0, 0xffff ),
				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				mt_rand( 0, 0x0fff ) | 0x4000,
				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				mt_rand( 0, 0x3fff ) | 0x8000,
				// 48 bits for "node"
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff ),
				mt_rand( 0, 0xffff )
			);
		}

		/**
		 * Get CURL version.
		 *
		 * @param  string $key CURL version key.
		 * @return string
		 */
		public static function get_curl_version( $key ) {
			if ( function_exists( 'curl_version' ) ) {
				$curl = curl_version();

				return $curl[ $key ];
				return sprintf( '%s %s', $curl['version'], $curl['ssl_version'] );
			} else {
				return 'Not Available';
			}
		}

		/**
		 * Get the current theme info, theme name and version.
		 *
		 * @return array
		 */
		public static function get_memory_info() {

			$memory = self::astra_let_to_num( WP_MEMORY_LIMIT );

			if ( function_exists( 'memory_get_usage' ) ) {
				// @codingStandardsIgnoreStart
				$system_memory = self::astra_let_to_num( @ini_get( 'memory_limit' ) );
				// @codingStandardsIgnoreEnd
				$memory = max( $memory, $system_memory );
			}

			return $memory;
		}

		/**
		 * Get all plugins grouped into activated or not.
		 *
		 * @return array
		 */
		private static function get_all_plugins() {

			// Ensure get_plugins function is loaded.
			if ( ! function_exists( 'get_plugins' ) ) {
				include ABSPATH . '/wp-admin/includes/plugin.php';
			}

			$plugins             = get_plugins();
			$active_plugins_keys = get_option( 'active_plugins', array() );
			$active_plugins      = array();

			foreach ( $plugins as $k => $v ) {
				// Take care of formatting the data how we want it.
				$formatted         = array();
				$formatted['name'] = strip_tags( $v['Name'] );
				if ( isset( $v['Version'] ) ) {
					$formatted['version'] = strip_tags( $v['Version'] );
				}
				if ( isset( $v['Author'] ) ) {
					$formatted['author'] = strip_tags( $v['Author'] );
				}
				if ( isset( $v['Network'] ) ) {
					$formatted['network'] = strip_tags( $v['Network'] );
				}
				if ( isset( $v['PluginURI'] ) ) {
					$formatted['plugin_uri'] = strip_tags( $v['PluginURI'] );
				}
				if ( in_array( $k, $active_plugins_keys, true ) ) {
					// Remove active plugins from list so we can show active and inactive separately.
					unset( $plugins[ $k ] );
					$active_plugins[ $k ] = $formatted;
				} else {
					$plugins[ $k ] = $formatted;
				}
			}

			return array(
				'active_plugins'   => $active_plugins,
				'inactive_plugins' => $plugins,
			);
		}

		/**
		 * Notation to numbers.
		 *
		 * This function transforms the php.ini notation for numbers (like '2M') to an integer.
		 *
		 * @param  string $size Size value.
		 * @return int
		 */
		public static function astra_let_to_num( $size ) {
			$l   = substr( $size, -1 );
			$ret = (int) substr( $size, 0, -1 );
			switch ( strtoupper( $l ) ) {
				case 'P':
					$ret *= 1024;
					// No break.
				case 'T':
					$ret *= 1024;
					// No break.
				case 'G':
					$ret *= 1024;
					// No break.
				case 'M':
					$ret *= 1024;
					// No break.
				case 'K':
					$ret *= 1024;
					// No break.
			}
			return $ret;
		}
	}


	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Astra_Sites_Tracker::get_instance();

endif;
