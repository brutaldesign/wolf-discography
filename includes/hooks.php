<?php
/**
 * WolfDiscography Hooks
 *
 * Action/filter hooks used for WolfDiscography functions/templates
 *
 * @author WpWolf
 * @category Core
 * @package WolfDiscography/Templates
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/** Template Hooks ********************************************************/

if ( ! is_admin() || defined('DOING_AJAX') ) {

	/**
	 * Content Wrappers
	 *
	 * @see wolf_discography_output_content_wrapper()
	 * @see wolf_discography_output_content_wrapper_end()
	 */
	add_action( 'wolf_discography_before_main_content', 'wolf_discography_output_content_wrapper', 10 );
	add_action( 'wolf_discography_after_main_content', 'wolf_discography_output_content_wrapper_end', 10 );

}

/** Event Hooks *****************************************************/

add_action( 'template_redirect', 'wolf_discography_template_redirect' );