<?php
/**
 * WolfDiscography Functions
 *
 * Hooked-in functions for WolfDiscography related events on the front-end.
 *
 * @author WpWolf
 * @category Core
 * @package WolfDiscography/Functions
 * @since 1.0.2
 */

/**
 * Handle redirects before content is output - hooked into template_redirect so is_page works.
 *
 * @access public
 * @return void
 */
function wolf_discography_template_redirect() {

	if ( is_page( wolf_discography_get_page_id() ) ) {

		wolf_discography_get_template( 'discography-template.php' );
		exit();

	}
	
}


if ( ! function_exists( 'wolf_release_nav' ) ) {
	/**
	 * Displays release navigation
	 *
	 * @return string
	 */
	function wolf_release_nav() {
		
		global $wolf_discography;
		$wolf_discography->navigation();

	}
}


if ( ! function_exists( 'wolf_release_page_nav' ) ) {
	/**
	 * Displays release page navigation
	 *
	 * @return string
	 */
	function wolf_release_page_nav( $loop = null ) {
		
		global $wolf_discography;
		$wolf_discography->paging_nav( $loop );

	}
}


if ( ! function_exists( 'wolf_widget_discography' ) ) {
	/**
	 * Discography Widget function
	 *
	 * Displays the discography widget
	 *
	 * @param int $count
	 * @return string
	 */
	function wolf_widget_discography( $count = 3 ) {
		global $wolf_discography;
		$wolf_discography->widget_discography( $count );
	}
}


if ( ! function_exists( 'wolf_widget_last_release' ) ) {
	/**
	 * Last Release Widget function
	 *
	 * Displays the last release widget
	 *
	 * @return string
	 */
	function wolf_widget_last_release() {
		global $wolf_discography;
		$wolf_discography->widget_last_release();
	}
}


if ( ! function_exists( 'wolf_get_release_option' ) ) {
	/**
	 * Widget function
	 *
	 * Displays the show list in the widget
	 *
	 * @param int $count, string $url, bool $link
	 * @return string
	 */
	function wolf_get_release_option( $o ) {
		global $wolf_discography;
		return $wolf_discography->get_option( $o );
	}
}