<?php
/**
 * Plugin Name: Wolf Discography
 * Plugin URI: http://wpwolf.com/plugin/wolf-discography
 * Description: A plugin to display your releases.
 * Version: 1.0.7
 * Author: WpWolf
 * Author URI: http://wpwolf.com
 * Requires at least: 3.5
 * Tested up to: 3.8.1
 *
 * Text Domain: wolf
 * Domain Path: /lang/
 *
 * @package WolfDiscography
 * @author WpWolf
 *
 * Being a free product, this plugin is distributed as-is without official support. 
 * Verified customers however, who have purchased a premium theme
 * at http://themeforest.net/user/BrutalDesign/portfolio?ref=BrutalDesign
 * will have access to support for this plugin in the forums
 * http://help.wpwolf.com/
 *
 * Copyright (C) 2014 Constantin Saguin
 * This WordPress Plugin is a free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * It is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * See http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Wolf_Discography' ) ) {

	/**
	 * Main Wolf_Discography Class
	 *
	 * Contains the main functions for Wolf_Discography
	 *
	 * @class Wolf_Discography
	 * @version 1.0.7
	 * @since 1.0.0
	 * @package WolfDiscography
	 * @author WpWolf
	 */
	class Wolf_Discography {

		/**
		 * @var string
		 */
		public $version = '1.0.7';

		/**
		 * @var string
		 */
		private $update_url = 'http://plugins.wpwolf.com/update';

		/**
		 * @var string
		 */
		public $plugin_url;

		/**
		 * @var string
		 */
		public $plugin_path;

		/**
		 * @var string
		 */
		public $template_url;

		/**
		 * Wolf_Discography Constructor.
		 */
		public function __construct() {
			
			// Flush rewrite rules on activation
			register_activation_hook( __FILE__, array( $this, 'activate' ) );

			// plugin update notification
			add_action( 'admin_init', array( $this, 'update' ), 5 );

			// register settings
			add_action( 'admin_init', array( $this, 'register_settings' ) );

			// add option sub-menu
			add_action( 'admin_menu', array( $this, 'add_settings_menu' ) );

			// check if discography page exists
			add_action( 'admin_notices', array( $this, 'check_page' ) );
			add_action( 'admin_notices', array( $this, 'create_page' ) );

			// add discography image sizes
			add_image_size( 'CD', 400, 400, true );
			add_image_size( 'DVD', 400, 570, true );

			// Include required files
			$this->includes();

			add_action( 'init', array( $this, 'init' ), 0 );
			add_action( 'init', array( $this, 'include_template_functions' ), 25 );

			// register shortcode
			add_shortcode( 'wolf_last_releases', array( $this, 'shortcode' ) );

			// set default options
			add_action( 'after_setup_theme', array( $this, 'default_options' ) );
			
			// Hooks
			add_action( 'widgets_init', array( $this, 'register_widgets' ) );

			// Enqueue Stylesheet
			add_action( 'wp_print_styles', array( $this, 'print_styles' ) );
		}

		/**
		 * Activation function
		 */
		public function activate( $network_wide ) {

			// do stuff

		}

		/**
		 * plugin update notification.
		 */
		public function update() {
			
			$plugin_data = get_plugin_data( __FILE__ );
			$current_version = $plugin_data['Version'];
			$plugin_slug = plugin_basename( dirname( __FILE__ ) );
			$plugin_path = plugin_basename( __FILE__ );
			$remote_path = $this->update_url . '/' . $plugin_slug;
			
			if ( ! class_exists( 'Wolf_WP_Update' ) )
				include_once( 'classes/class-wp-update.php' );
			
			new Wolf_WP_Update( $current_version, $remote_path, $plugin_path );
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 */
		public function includes() {

			if ( ! is_admin() || defined('DOING_AJAX') )
				$this->frontend_includes();

			// Metabox class
			include_once( 'classes/class-metabox.php' );

			// Core functions
			include_once( 'includes/core-functions.php' );
		}

		/**
		 * Include required frontend files.
		 *
		 */
		public function frontend_includes() {
			
			// Functions
			include_once( 'includes/hooks.php' ); // Template hooks used on the front-end
			include_once( 'includes/functions.php' ); // Contains functions for various front-end events
			
		}

		/**
		 * Function used to Init WolfDiscography Template Functions - This makes them pluggable by plugins and themes.
		 *
		 */
		public function include_template_functions() {
			
			include_once( 'includes/template.php' );

		}

		/**
		 * Check portfolio page
		 *
		 * Display a notification if we can't get the portfolio page id
		 *
		 */
		public function check_page() {

			// delete_option( '_wolf_discography_page_id' );
			
			$output    = '';
			$theme_dir = get_template_directory();

			if ( -1 == wolf_discography_get_page_id() && ! isset( $_GET['wolf_discography_create_page'] ) ) {

				$message = '<strong>Wolf Discography</strong> ' . sprintf(
					__( 'says : <em>Almost done! you need to <a href="%1$s">create a page</a> for your discography or <a href="%2$s">select an existing page</a> in the plugin settings</em>', 'wolf' ), 
						esc_url( admin_url( '?wolf_discography_create_page=true' ) ),
						esc_url( admin_url( 'edit.php?post_type=release&page=wolf-discography-settings' ) )
				);

				$output = '<div class="updated"><p>';

				$output .= $message;

				$output .= '</p></div>';

				echo $output;

			}

			return false;
		}

		/**
		 * Create discography page
		 */
		public function create_page() {

			if ( isset( $_GET['wolf_discography_create_page'] ) && $_GET['wolf_discography_create_page'] == 'true' ) {
				
				$output = '';

				// Create post object
				$post = array(
					'post_title'  => 'Discography',
					'post_type'   => 'page',
					'post_status' => 'publish',
				);

				// Insert the post into the database
				$post_id = wp_insert_post( $post );

				if ( $post_id ) {
					
					update_option( '_wolf_discography_page_id', $post_id );
					
					$message = __( 'Your discography page has been created succesfully', 'wolf' );

					$output = '<div class="updated"><p>';

					$output .= $message;

					$output .= '</p></div>';

					echo $output;
				}

			}

			return false;
		}

		/**
		 * register_widgets function.
		 *
		 */
		public function register_widgets() {
			
			// Include
			include_once( 'classes/widgets/class-wd-widget-discography.php' );
			include_once( 'classes/widgets/class-wd-widget-last-release.php' );

			// Register widgets
			register_widget( 'WD_Widget_Discography' );
			register_widget( 'WD_Widget_Last_Release' );
		}

		/**
		 * Init WolfDiscography when WordPress Initialises.
		 */
		public function init() {

			// Set up localisation
			$this->load_plugin_textdomain();

			// Variables
			$this->template_url = apply_filters( 'wolf_discography_template_url', 'wolf-discography/' );

			// Classes/actions loaded for the frontend and for ajax requests
			if ( ! is_admin() || defined( 'DOING_AJAX' ) ) {

				// Hooks
				add_filter( 'template_include', array( $this, 'template_loader' ) );

				// Body class
				add_filter( 'body_class', array( $this, 'body_class' ) );

			}

			// register post type
			$this->register_post_type();

			// register post type
			$this->register_taxonomy();

			// add metaboxes
			$this->metaboxes();
		}

		/**
		 * Load Localisation files.
		 *
		 */
		public function load_plugin_textdomain() {

			$domain = 'wolf';
			$locale = apply_filters( 'wolf', get_locale(), $domain );
			load_textdomain( $domain, WP_LANG_DIR.'/'.$domain.'/'.$domain.'-'.$locale.'.mo' );
			load_plugin_textdomain( $domain, FALSE, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
		}

		/**
		 * Load a template.
		 *
		 * Handles template usage so that we can use our own templates instead of the themes.
		 *
		 * Templates are in the 'templates' folder. Wolf Discography looks for theme
		 * overrides in /theme/wolf-tour_dates/ by default
		 *
		 *
		 * @param mixed $template
		 * @return string
		 */
		public function template_loader( $template ) {

			$find = array( 'wolf-discography.php' ); // nope! not used
			$file = '';

			if ( is_single() && get_post_type() == 'release' ) {

				$file    = 'single-release.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;

			} elseif ( is_tax( 'band' ) || is_tax( 'label' ) ) {

				$term = get_queried_object();

				$file 	= 'taxonomy-' . $term->taxonomy . '.php';
				$find[] 	= 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] 	= $this->template_url . 'taxonomy-' . $term->taxonomy . '-' . $term->slug . '.php';
				$find[] 	= $file;
				$find[] 	= $this->template_url . $file;

			} elseif ( is_post_type_archive( 'release' ) ) {

				$file = 'archive-release.php';
				$find[] = $file;
				$find[] = $this->template_url . $file;

			}

			if ( $file ) {
				$template = locate_template( $find );
				if ( ! $template ) $template = $this->plugin_path() . '/templates/' . $file;
			}

			return $template;
		}

		/**
		 * Print CSS styles
		 */
		public function print_styles() {

			wp_enqueue_style( 'wolf-discography', $this->plugin_url() . '/assets/css/discography.min.css', array(), $this->version, 'all' );
		}

		/**
		 * Register post type
		 */
		public function register_post_type() {

			$admin_skin = get_user_option('admin_color');
			if ( $admin_skin == 'light' )
				$icon_url = $this->plugin_url() . '/assets/img/admin/vynil-dark.png';
			else
				$icon_url = $this->plugin_url() . '/assets/img/admin/vynil.png';

			$labels = array( 
				'name' => __( 'Releases', 'wolf' ),
				'singular_name' => __( 'Release', 'wolf' ),
				'add_new' => __( 'Add New', 'wolf' ),
				'add_new_item' => __( 'Add New Release', 'wolf' ),
				'all_items'  =>  __( 'All Releases', 'wolf' ),
				'edit_item' => __( 'Edit Release', 'wolf' ),
				'new_item' => __( 'New Release', 'wolf' ),
				'view_item' => __( 'View Release', 'wolf' ),
				'search_items' => __( 'Search Releases', 'wolf' ),
				'not_found' => __( 'No releases found', 'wolf' ),
				'not_found_in_trash' => __( 'No releases found in Trash', 'wolf' ),
				'parent_item_colon' => '',
				'menu_name' => __( 'Releases', 'wolf' ),
			);

			$args = array( 

				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => false,
				'rewrite' => array( 'slug' => 'release' ),
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => 5,
				'taxonomies' => array(),
				'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'comments' ),
				'exclude_from_search' => false,

				'menu_icon' => $icon_url
			);

			register_post_type( 'release', $args );
		}

		/**
		 * Register taxonomy
		 */
		public function register_taxonomy() {

			$labels = array( 
				'name' => __( 'Bands', 'wolf' ),
				'singular_name' => __( 'Band', 'wolf' ),
				'search_items' => __( 'Search Bands', 'wolf' ),
				'popular_items' => __( 'Popular Bands', 'wolf' ),
				'all_items' => __( 'All Bands', 'wolf' ),
				'parent_item' => __( 'Parent Band', 'wolf' ),
				'parent_item_colon' => __( 'Parent Band:', 'wolf' ),
				'edit_item' => __( 'Edit Band', 'wolf' ),
				'update_item' => __( 'Update Band', 'wolf' ),
				'add_new_item' => __( 'Add New Band', 'wolf' ),
				'new_item_name' => __( 'New Band', 'wolf' ),
				'separate_items_with_commas' => __( 'Separate bands with commas', 'wolf' ),
				'add_or_remove_items' => __( 'Add or remove bands', 'wolf' ),
				'choose_from_most_used' => __( 'Choose from the most used bands', 'wolf' ),
				'menu_name' => __( 'Bands', 'wolf' ),
			);

			$args = array( 
				
				'labels' => $labels,
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'query_var' => true,
				'update_count_callback' => '_update_post_term_count',
				'rewrite' => array( 'slug' => 'band', 'with_front' => false),
			);

			register_taxonomy( 'band', array( 'release' ), $args );

			$labels = array( 
				'name' => __( 'Labels', 'wolf' ),
				'singular_name' => __( 'Label', 'wolf' ),
				'search_items' => __( 'Search Labels', 'wolf' ),
				'popular_items' => __( 'Popular Labels', 'wolf' ),
				'all_items' => __( 'All Labels', 'wolf' ),
				'parent_item' => __( 'Parent Label', 'wolf' ),
				'parent_item_colon' => __( 'Parent Label:', 'wolf' ),
				'edit_item' => __( 'Edit Label', 'wolf' ),
				'update_item' => __( 'Update Label', 'wolf' ),
				'add_new_item' => __( 'Add New Label', 'wolf' ),
				'new_item_name' => __( 'New Label', 'wolf' ),
				'separate_items_with_commas' => __( 'Separate labels with commas', 'wolf' ),
				'add_or_remove_items' => __( 'Add or remove labels', 'wolf' ),
				'choose_from_most_used' => __( 'Choose from the most used labels', 'wolf' ),
				'menu_name' => __( 'Labels', 'wolf' ),
			);

			$args = array( 
				
				'labels' => $labels,
				'hierarchical' => false,
				'public' => true,
				'show_ui' => true,
				'query_var' => true,
				'update_count_callback' => '_update_post_term_count',
				'rewrite' => array( 'slug' => 'label', 'with_front' => false),
			);

			register_taxonomy( 'label', array( 'release' ), $args );
		}

		/**
		 * Add options menu
		 */
		public function add_settings_menu() {

			add_submenu_page( 'edit.php?post_type=release', __( 'Settings', 'wolf' ), __( 'Settings', 'wolf' ), 'edit_plugins', 'wolf-discography-settings', array( $this, 'options_form' ) );
			add_submenu_page( 'edit.php?post_type=release', __( 'Shortcode', 'wolf' ), __( 'Shortcode', 'wolf' ), 'edit_plugins', 'wolf-discography-shortcode', array( $this, 'help' ) );
		}

		/**
		 * Add metaboxes
		 */
		public function metaboxes() {
			
			$release_metabox = array(
				
				'Release Details' => array(

					'title' => __( 'Release Details', 'wolf' ),
					'page' => array( 'release' ),
					'metafields' => array(

					
						array(
							'label'	=> __( 'Title', 'wolf' ),
							'id'	=> '_wolf_release_title',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Release date', 'wolf' ),
							'id'	=> '_wolf_release_date',
							'type'	=> 'datepicker',
						),

						array(
							'label'	=> __( 'Catalog Number', 'wolf' ),
							'id'	=> '_wolf_release_catalog_number',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Type', 'wolf' ),
							'id'	=> '_wolf_release_type',
							'desc'   => sprintf( __( 'You can choose to not display the format in the <a href="%s">plugin options</a>', 'wolf' ), esc_url( admin_url( '/edit.php?post_type=release&page=wolf-release-options' ) ) ),
	 						'type'	=> 'select',
							'options' => array(
								__( 'CD', 'wolf' ), 
								__( 'Digital Download', 'wolf' ),
								__( 'DVD', 'wolf' ),
								__( 'Vinyl', 'wolf' ),
								 __( 'Tape', 'wolf' ),
							),
						),

						array(
							'label'	=> __( 'iTunes', 'wolf' ),
							'id'	=> '_wolf_release_itunes',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Amazon', 'wolf' ),
							'id'	=> '_wolf_release_amazon',
							'type'	=> 'text',
						),

						array(
							'label'	=> __( 'Buy (any link where the release can be purchased)', 'wolf' ),
							'id'	=> '_wolf_release_buy',
							'type'	=> 'text',
						),

					)

				),
			);

			if ( class_exists( 'Wolf_Discography_Metabox' ) ) {
				new Wolf_Discography_Metabox( $release_metabox );
			}
		}

		/**
		 * Set default options
		 */
		public function default_options() {
			
			global $options;

			if ( false ===  get_option( 'wolf_release_settings' )  ) {

				$default = array(

					'use_band_tax' => 1,
					'use_label_tax' => 1,
					'display_format' => 1

				);

				add_option( 'wolf_release_settings', $default );
			}
		}

		/**
		 * Get option
		 *
		 * @return string
		 */
		public function get_option( $value = null ) {
			
			global $options;

	 		$wolf_release_settings = get_option( 'wolf_release_settings' );
			if ( isset( $wolf_release_settings[ $value ] ) ) {

				return $wolf_release_settings[ $value ];
			}	
		}

		/**
		 * Register options
		 */
		public function register_settings() {

			$theme_dir = get_template_directory();
			$template_name = 'discography-template-php';

			register_setting( 'wolf-release-settings', 'wolf_release_settings', array( $this, 'settings_validate' ) );
			add_settings_section( 'wolf-release-settings', '', array( $this, 'section_intro' ), 'wolf-release-settings' );
			add_settings_field( 'page_id', __( 'Discography Page', 'wolf' ), array( $this, 'setting_page_id' ), 'wolf-release-settings', 'wolf-release-settings' );
			add_settings_field( 'use_band_tax', __( 'Link Band Name to category page', 'wolf' ), array( $this, 'setting_use_band_tax' ), 'wolf-release-settings', 'wolf-release-settings' );
			add_settings_field( 'use_label_tax', __( 'Link Label Name to category page', 'wolf' ), array( $this, 'setting_use_label_tax' ), 'wolf-release-settings', 'wolf-release-settings' );
			add_settings_field( 'display_format', __( 'Display format (like CD, digital download etc...)', 'wolf' ), array( $this, 'setting_display_format' ), 'wolf-release-settings', 'wolf-release-settings' );
			add_settings_field( 'info', __( 'Info', 'wolf' ), array( $this, 'setting_info' ), 'wolf-release-settings', 'wolf-release-settings' );
		}

		/**
		 * Validate options
		 *
		 * @param array $input
		 * @return array $input
		 */
		public function settings_validate( $input ) {
			
			if ( isset( $input['page_id'] ) ) {
				update_option( '_wolf_discography_page_id', intval( $input['page_id'] ) );
				unset( $input['page_id'] );
			}

			$input['use_band_tax'] = intval( $input['use_band_tax'] );
			$input['use_label_tax'] = intval( $input['use_label_tax'] );
			$input['display_format'] = intval( $input['display_format'] );
			return $input;
		}

		/**
		 * Debug section
		 *
		 * @return string
		 */
		public function section_intro() {
			// debug
			// global $options;
			// var_dump(get_option('wolf_release_settings'));
		}

		/**
		 * Page settings
		 *
		 * @access public
		 * @return string
		 */
		public function setting_page_id() {
			$pages = get_pages();
			?>
			<select name="wolf_release_settings[page_id]">
				<option value="-1"><?php _e( 'Select a page...', 'wolf' ); ?></option>
				<?php foreach ( $pages as $page ) : ?>
					<option <?php if ( intval( $page->ID ) == get_option( '_wolf_discography_page_id' ) ) echo 'selected="selected"'; ?> value="<?php echo intval( $page->ID ); ?>"><?php echo sanitize_text_field( $page->post_title ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php
		}

		/**
		 * Use Band Taxonomy option
		 *
		 * @return string
		 */
		public function setting_use_band_tax() {
			?>
			<input type="hidden" name="wolf_release_settings[use_band_tax]" value="0">
			<label><input type="checkbox" name="wolf_release_settings[use_band_tax]" value="1" <?php echo ( ( $this->get_option( 'use_band_tax' ) == 1) ? ' checked="checked"' : '' ); ?>>
			</label>
			<?php
		}

		/**
		 * Use Label Taxonomy option
		 *
		 * @return string
		 */
		public function setting_use_label_tax() {
			?>
			<input type="hidden" name="wolf_release_settings[use_label_tax]" value="0">
			<label><input type="checkbox" name="wolf_release_settings[use_label_tax]" value="1" <?php echo ( ( $this->get_option( 'use_label_tax' ) == 1) ? ' checked="checked"' : '' ); ?>>
			</label>
			<?php
		}

		/**
		 * Display release format option
		 *
		 * @return string
		 */
		public function setting_display_format() {
			?>
			<input type="hidden" name="wolf_release_settings[display_format]" value="0">
			<label><input type="checkbox" name="wolf_release_settings[display_format]" value="1" <?php echo ( ( $this->get_option( 'display_format' ) == 1) ? ' checked="checked"' : '' ); ?>>
			</label>
			<?php
		}

		/**
		 * Display additional instructions
		 *
		 * @return string
		 */
		public function setting_info() {
			
			echo "<p><em>";
			printf( __( 'If a discography page returns a 404 error, refresh your <a href="%s">permalink structure</a>', 'wolf' ), 
				esc_url( admin_url( '/options-permalink.php' ) 
				) 
			);
			echo "</p></em>";
		}

		/**
		 * Displays Shortcode help
		 */
		public function help() {
			?>
			<div class="wrap">
				<h2><?php _e( 'Discography Shortcode', 'wolf' ) ?></h2>
				<p><?php _e( 'To display your last releases in your post or page you can use the following shortcode.', 'wolf' ); ?></p>
				<p><code>[wolf_last_releases]</code></p>
				<p><?php _e( 'Additionally, you can add a count and/or categories attributes.', 'wolf' ); ?></p>
				<p><code>[wolf_last_releases count="6" label="my-label" band="this-band"]</code></p>

				<p><?php _e( 'You can also add a column count attribute.', 'wolf' ); ?></p>
				<p><code>[wolf_last_releases col="2|3|4" category="my-category"]</code></p>
			</div>
			<?php
		}

		/**
		 * Options form
		 *
		 * @return string
		 */
		public function options_form() {
			?>
			<div class="wrap">
				<div id="icon-options-general" class="icon32"></div>
				<h2><?php _e( 'Discography Options', 'wolf' ); ?></h2>
				<?php if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] ) { ?>
				<div id="setting-error-settings_updated" class="updated settings-error"> 
					<p><strong><?php _e( 'Settings saved.', 'wolf' ); ?></strong></p>
				</div>
				<?php } ?>
				<form action="options.php" method="post">
					<?php settings_fields( 'wolf-release-settings' ); ?>
					<?php do_settings_sections( 'wolf-release-settings' ); ?>
					<p class="submit"><input name="save" type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'wolf' ); ?>" /></p>
				</form>
			</div>
			<?php
		}

		/**
		 * Add specific class to the body when we're on the discography page
		 *
		 * @param array $classes
		 * @return array $classes
		 */
		public function body_class( $classes ) {

			if ( is_page( wolf_discography_get_page_id() ) ) {
				$classes[] = 'discography-page';
			}

			// if ( 'release' == get_post_type() ) {
			// 	$classes[] = 'discography';
			// }

			return $classes;
		}

		/**
		 * Shortcode
		 *
		 * @param array $atts
		 * @return string
		 */
		public function shortcode( $atts ) {

			extract(
				shortcode_atts(
					array(
						'count' => 4,
						'band' => null,
						'label' => null,
						'col' => 4,
					), $atts
				)
			);

			ob_start();

			$args = array(
				'post_type' => array( 'release' ),
				'posts_per_page' => absint( $count ),
			);

			if ( $band ) {
				$args['band'] = $band;
			}

			if ( $label ) {
				$args['label'] = $label;
			}

			$loop = new WP_Query( $args );
			if ( $loop->have_posts() ) : ?>
				<ul class="shortcode-release-grid release-grid-col-<?php echo absint( $col ); ?>">
					<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>

						<?php wolf_discography_get_template_part( 'content', 'release-shortcode' ); ?>

					<?php endwhile; ?>
				</ul><!-- .shortcode-release-grid -->
			<?php else : // no release ?>
				<?php wolf_discography_get_template( 'loop/no-releases-found.php' ); ?>
			<?php endif;
			wp_reset_postdata();

			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		}

		/**
		 * Displays release navigation
		 *
		 * @return string
		 */
		public function navigation() {
			global $post;

			// Don't print empty markup if there's nowhere to navigate.
			$previous = get_adjacent_post( false, '', true );
			$next = get_adjacent_post( false, '', false );

			if ( ! $next && ! $previous )
				return;
			?>
			<nav class="release-navigation" role="navigation">
				<?php previous_post_link( '%link', _x( '<span class="meta-nav">&larr;</span> %title', 'Previous post link', 'wolf' ) ); ?>
				<?php next_post_link( '%link', _x( '%title <span class="meta-nav">&rarr;</span>', 'Next post link', 'wolf' ) ); ?>
			</nav><!-- .navigation -->
			<?php
		}

		/**
		 * Displays release page navigation
		 *
		 * @param object $loop
		 * @return string
		 */
		function paging_nav( $loop ) {
		
			if ( ! $loop ){
				global $wp_query;
				$max = $wp_query->max_num_pages;
			} else {
				$max = $loop->max_num_pages;
			}

			// Don't print empty markup if there's only one page.
			if ( $max < 2 )
				return;
			
			?>
			<nav class="navigation release-paging-navigation" role="navigation">
				<div class="nav-links clearfix">

					<?php if ( get_next_posts_link( '', $max ) ) : ?>
					<div class="nav-previous"><?php next_posts_link( __( '<span class="meta-nav">&larr;</span> Older releases', 'wolf' ), $max ); ?></div>
					<?php endif; ?>

					<?php if ( get_previous_posts_link( '', $max ) ) : ?>
					<div class="nav-next"><?php previous_posts_link( __( 'Newer releases <span class="meta-nav">&rarr;</span>', 'wolf' ), $max ); ?></div>
					<?php endif; ?>

				</div><!-- .nav-links -->
			</nav><!-- .navigation -->
			<?php
		}

		/**
		 * Widget discography function
		 *
		 * Displays the discography widget
		 *
		 * @return string
		 */
		public function widget_discography() {
			global $wpdb;
			$query = new WP_Query( array( 
				'post_type' => 'release', 
				'posts_per_page' => 3 ) 
			);


			if ( $query->have_posts() ) {
				$i = 0;
				while ( $query->have_posts() ) {
					$query->the_post();
					$i ++;
					$post_id = get_the_ID();
					$class = $i == 1 ? ' class="release-widget-first-child"' : '';
					$thumb = $i == 1 ? 'CD' : 'thumbnail';
					?><a<?php echo $class; ?> href="<?php echo the_permalink() ?>"><?php the_post_thumbnail( 'CD' ); ?></a><?php
				}
				echo '<div style="clear:both"></div>';
			} else {
				echo "<p>";
				_e( 'No release to display yet.', 'wolf' );
				echo "</p>";
			}
			wp_reset_postdata();
		}

		/**
		 * Widget last release function
		 *
		 * Displays the last release widget
		 *
		 * @return string
		 */
		public function widget_last_release() {
			global $wpdb;
			$query = new WP_Query( array( 
				'post_type' => 'release', 
				'posts_per_page' => 1 ) 
			);

			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					$post_id = get_the_ID();
					$thumbnail_size = get_post_meta( $post_id, '_wolf_release_type', true ) == 'DVD' || get_post_meta( $post_id, '_wolf_release_type', true ) == 'K7' ? 'DVD' : 'CD';
					?>
					<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( $thumbnail_size ); ?></a>
					<h4 class="entry-title"><a title="<?php _e( 'View Details', 'wolf' ); ?>" class="entry-link" href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
					<?php
				}
			} else {
				echo "<p>";
				_e( 'No release to display yet.', 'wolf' );
				echo "</p>";
			}
			wp_reset_postdata();
		}

		/**
		 * Get the plugin url.
		 *
		 * @return string
		 */
		public function plugin_url() {
			if ( $this->plugin_url ) return $this->plugin_url;
			return $this->plugin_url = untrailingslashit( plugins_url( '/', __FILE__ ) );
		}

		/**
		 * Get the plugin path.
		 *
		 * @return string
		 */
		public function plugin_path() {
			if ( $this->plugin_path ) return $this->plugin_path;
			return $this->plugin_path = untrailingslashit( plugin_dir_path( __FILE__ ) );
		}
	} // end class

	/**
	 * Init Wolf_Discography class
	 */
	$GLOBALS['wolf_discography'] = new Wolf_Discography();

} // end class exists check