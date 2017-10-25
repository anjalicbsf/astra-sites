<?php
/**
 * Astra Sites Compatibility for 3rd party plugins.
 *
 * @package Astra Sites
 * @since 1.0.11
 */

if ( ! class_exists( 'Astra_Sites_Compatibility' ) ) :

	/**
	 * Astra Sites Compatibility
	 *
	 * @since 1.0.11
	 */
	class Astra_Sites_Compatibility {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since 1.0.11
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since 1.0.11
		 * @return object initialized object of class.
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.11
		 */
		public function __construct() {

			// Vendor: Image Downloader.
			// require_once ASTRA_SITES_DIR . 'inc/classes/vendor/class-astra-image-downloader.php';

			// Vendor: Background Processing.
			require_once ASTRA_SITES_DIR . 'inc/classes/vendor/wp-async-request.php';
			require_once ASTRA_SITES_DIR . 'inc/classes/vendor/wp-background-process.php';

			// Plugin - Astra Pro.
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/astra-pro/class-astra-sites-compatibility-astra-pro.php';

			// Plugin - Site Origin Widgets.
			require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/so-widgets-bundle/class-astra-sites-compatibility-so-widgets.php';

			// Plugin - Elementor.
			// require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/elementor/class-astra-sites-compatibility-elementor.php';

			// // Plugin - Beaver Builder.
			// require_once ASTRA_SITES_DIR . 'inc/classes/compatibility/beaver-builder/class-astra-sites-compatibility-beaver-builder.php';
		}

		/**
		 * Debugging Log.
		 * 
		 * @param  [type] $log [description]
		 * @return [type]      [description]
		 */
		public static function log( $log )  {
	  		if ( is_array( $log ) || is_object( $log ) ) {
				error_log( print_r( $log, true ) );
	  		} else {
				error_log( $log );
	  		}
	   	}

	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Astra_Sites_Compatibility::instance();

endif;


