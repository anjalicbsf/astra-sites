<?php
/**
 * Astra Sites Compatibility for 'Elementor'
 *
 * @package Astra Sites
 * @since x.x.x
 */

namespace AstraSites\Elementor;

use Elementor\Core\Base\Module;
use Elementor\Controls_Manager;
use Elementor\Controls_Stack;
use Elementor\Scheme_Typography;
use Elementor\Group_Control_Typography;
use Elementor\Core\Settings\Manager;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Astra_Sites_Compatibility_Elementor' ) ) :

	/**
	 * WooCommerce Compatibility
	 *
	 * @since x.x.x
	 */
	class Astra_Sites_Compatibility_Elementor {

		/**
		 * Instance
		 *
		 * @access private
		 * @var object Class object.
		 * @since x.x.x
		 */
		private static $instance;

		/**
		 * Initiator
		 *
		 * @since x.x.x
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
		 * @since x.x.x
		 */
		public function __construct() {
			add_action( 'elementor/element/after_section_end', array( $this, 'register_page_typography' ), 10, 2 );
			add_action( 'elementor/preview/enqueue_styles', array( $this, 'enqueue_preview_scripts' ) );
			add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'enqueue_editor_scripts' ), 999 );
		}

		/**
		 * Get public name for control.
		 *
		 * @return string
		 */
		public function get_name() {
			return 'astra-sites-controls';
		}

		/**
		 * Register Body and Paragraph typography controls.
		 *
		 * @param Controls_Stack $element Controls object.
		 * @param string         $section_id Section ID.
		 */
		public function register_page_typography( Controls_Stack $element, $section_id ) {

			if ( 'section_page_style' !== $section_id ) {
				return;
			}

			$element->start_controls_section(
				'astra_sites_body_and_paragraph_typography',
				array(
					'label' => __( 'Astra Site Settings', 'astra-sites' ),
					'tab'   => Controls_Manager::TAB_STYLE,
				)
			);

			$element->add_control(
				'astra_sites_page_setting_enable',
				array(
					'label'        => __( 'Enable', 'uael' ),
					'type'         => Controls_Manager::SWITCHER,
					'label_on'     => __( 'Yes', 'uael' ),
					'label_off'    => __( 'No', 'uael' ),
					'description'  => __( 'Enables the page settings. You can still override individual values for each element.', 'uael' ),
					'return_value' => 'yes',
					'default'      => '',
				)
			);

			$element->add_control(
				'astra_sites_color_label',
				array(
					'label'     => __( 'Colors', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_control(
				'astra_sites_main_color',
				array(
					'label'     => __( 'Main Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body'        => 'color: {{VALUE}};',
						'::selection' => 'background: {{VALUE}};',
					),
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_control(
				'astra_sites_text_color',
				array(
					'label'     => __( 'Text Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'body, h1, .entry-title a, .entry-content h1, .entry-content h1 a, h2, .entry-content h2, .entry-content h2 a, h3, .entry-content h3, .entry-content h3 a, h4, .entry-content h4, .entry-content h4 a, h5, .entry-content h5, .entry-content h5 a, h6, .entry-content h6, .entry-content h6 a' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_control(
				'astra_sites_link_color',
				array(
					'label'     => __( 'Link Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'a, .page-title' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_control(
				'astra_sites_link_hover_color',
				array(
					'label'     => __( 'Link Hover Color', 'uael' ),
					'type'      => Controls_Manager::COLOR,
					'selectors' => array(
						'a:hover, a:focus' => 'color: {{VALUE}};',
					),
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_control(
				'astra_sites_typography',
				array(
					'label'     => __( 'Typography', 'uael' ),
					'type'      => Controls_Manager::HEADING,
					'separator' => 'before',
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$element->add_group_control(
				Group_Control_Typography::get_type(),
				array(
					'name'      => 'astra_sites_body',
					'label'     => __( 'Body Typography', 'astra-sites' ),
					'selector'  => 'body',
					'scheme'    => Scheme_Typography::TYPOGRAPHY_3,
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			$default_fonts = Manager::get_settings_managers( 'general' )->get_model()->get_settings( 'elementor_default_generic_fonts' );

			if ( $default_fonts ) {
				$default_fonts = ', ' . $default_fonts;
			}

			$element->add_control(
				'astra_sites_default_heading_font_family',
				array(
					'label'     => __( 'Default Headings Font', 'astra-sites' ),
					'type'      => Controls_Manager::FONT,
					'default'   => '',
					'selectors' => array(
						'h1, h2, h3, h4, h5, h6' => 'font-family: "{{VALUE}}"' . $default_fonts . ';',
					),
					'condition' => array(
						'astra_sites_page_setting_enable' => 'yes',
					),
				)
			);

			for ( $i = 1; $i < 7; $i++ ) {
				$element->add_group_control(
					Group_Control_Typography::get_type(),
					array(
						'name'      => 'astra_sites_heading_' . $i,
						/* translators: %s: Heading 1-6 type */
						'label'     => sprintf( __( 'Heading %s', 'astra-sites' ), $i ),
						'selector'  => "body h{$i}, body .elementor-widget-heading h{$i}.elementor-heading-title",
						'scheme'    => Scheme_Typography::TYPOGRAPHY_1,
						'condition' => array(
							'astra_sites_page_setting_enable' => 'yes',
						),
					)
				);
			}

			$element->end_controls_section();
		}

		/**
		 * Enqueue Google fonts.
		 *
		 * @return void
		 */
		public function enqueue_preview_scripts() {
			$post_id = get_the_ID();

			// Get the page settings manager.
			$page_settings_manager = Manager::get_settings_managers( 'page' );
			$page_settings_model   = $page_settings_manager->get_model( $post_id );

			$keys = apply_filters(
				'astra_sites_elementor_typography_keys',
				array(
					'astra_sites_page_setting_enable',
					'astra_sites_heading_1',
					'astra_sites_heading_2',
					'astra_sites_heading_3',
					'astra_sites_heading_4',
					'astra_sites_heading_5',
					'astra_sites_heading_6',
					'astra_sites_default_heading',
					'astra_sites_body',
					'astra_sites_main_color',
					'astra_sites_text_color',
					'astra_sites_link_color',
					'astra_sites_link_hover_color',
				)
			);

			$font_families = array();

			foreach ( $keys as $key ) {
				$font_families[] = $page_settings_model->get_settings( $key . '_font_family' );
			}

			// Remove duplicate and null values.
			$font_families = \array_unique( \array_filter( $font_families ) );

			if ( count( $font_families ) ) {
				wp_enqueue_style(
					'astra_sites_typography_fonts',
					'https://fonts.googleapis.com/css?family=' . implode( '|', $font_families ),
					array(),
					get_the_modified_time( 'U', $post_id )
				);
			}
		}

		/**
		 * Enqueue preview script.
		 *
		 * @return void
		 */
		public function enqueue_editor_scripts() {
			wp_enqueue_script(
				'astra_sites_typography_script',
				ASTRA_SITES_URI . 'inc/classes/compatibility/elementor/astra-sites-typography.js',
				array(
					'jquery',
					'editor',
				),
				ASTRA_SITES_VER,
				true
			);
		}
	}

	/**
	 * Kicking this off by calling 'instance()' method
	 */
	Astra_Sites_Compatibility_Elementor::instance();

endif;
