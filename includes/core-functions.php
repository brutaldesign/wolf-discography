<?php
/**
 * WolfDiscography Core Functions
 *
 * Functions available on both the front-end and admin.
 *
 * @author WpWolf
 * @category Core
 * @package WolfDiscography/Functions
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! function_exists( 'wolf_discography_get_page_id' ) ) {

	/**
	 * wolf_discography page IDs
	 *
	 * retrieve page ids - used for the main discography page
	 *
	 * returns -1 if no page is found
	 *
	 * @param string $page
	 * @return int
	 */
	function wolf_discography_get_page_id() {
		
		$page_id = -1;

		if ( -1 != get_option( '_wolf_discography_page_id' ) && get_option( '_wolf_discography_page_id' ) ) {
			
			$page_id = get_option( '_wolf_discography_page_id' );
		
		}
		return $page_id;
	}
}

// --------------------------------------------------------------------------

if ( ! function_exists( 'wolf_discography_get_page_link' ) ) {
	/**
	 * wolf_discography page link
	 *
	 * retrieve discography page permalink
	 *
	 *
	 * @access public
	 * @param string $page
	 * @return string
	 */
	function wolf_discography_get_page_link() {

		$page_id = wolf_discography_get_page_id();

		if ( $page_id != -1 )
			return get_permalink( $page_id );
	}

}

/**
 * Get template part (for templates like the release-loop).
 *
 * @access public
 * @param mixed $slug
 * @param string $name (default: '')
 * @return void
 */
function wolf_discography_get_template_part( $slug, $name = '' ) {
	global $wolf_discography;
	$template = '';

	// Look in yourtheme/slug-name.php and yourtheme/wolf_discography/slug-name.php
	if ( $name )
		$template = locate_template( array( "{$slug}-{$name}.php", "{$wolf_discography->template_url}{$slug}-{$name}.php" ) );

	// Get default slug-name.php
	if ( ! $template && $name && file_exists( $wolf_discography->plugin_path() . "/templates/{$slug}-{$name}.php" ) )
		$template = $wolf_discography->plugin_path() . "/templates/{$slug}-{$name}.php";

	// If template file doesn't exist, look in yourtheme/slug.php and yourtheme/wolf_discography/slug.php
	if ( ! $template )
		$template = locate_template( array( "{$slug}.php", "{$wolf_discography->template_url}{$slug}.php" ) );

	if ( $template )
		load_template( $template, false );
}


/**
 * Get other templates (e.g. ticket attributes) passing attributes and including the file.
 *
 * @access public
 * @param mixed $template_name
 * @param array $args (default: array())
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return void
 */
function wolf_discography_get_template( $template_name, $args = array(), $template_path = '', $default_path = '' ) {
	global $wolf_discography;

	if ( $args && is_array($args) )
		extract( $args );

	$located = wolf_discography_locate_template( $template_name, $template_path, $default_path );

	do_action( 'wolf_discography_before_template_part', $template_name, $template_path, $located, $args );

	include( $located );

	do_action( 'wolf_discography_after_template_part', $template_name, $template_path, $located, $args );
}


/**
 * Locate a template and return the path for inclusion.
 *
 * This is the load order:
 *
 * yourtheme/$template_path/$template_name
 * yourtheme/$template_name
 * $default_path/$template_name
 *
 * @access public
 * @param mixed $template_name
 * @param string $template_path (default: '')
 * @param string $default_path (default: '')
 * @return string
 */
function wolf_discography_locate_template( $template_name, $template_path = '', $default_path = '' ) {
	global $wolf_discography;

	if ( ! $template_path ) $template_path = $wolf_discography->template_url;
	if ( ! $default_path ) $default_path = $wolf_discography->plugin_path() . '/templates/';

	// Look within passed path within the theme - this is priority
	$template = locate_template(
		array(
			trailingslashit( $template_path ) . $template_name,
			$template_name
		)
	);

	// Get default template
	if ( ! $template )
		$template = $default_path . $template_name;

	// Return what we found
	return apply_filters( 'wolf_discography_locate_template', $template, $template_name, $template_path );
}