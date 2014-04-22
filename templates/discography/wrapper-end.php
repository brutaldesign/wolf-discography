<?php
/**
 * Content wrappers
 *
 * @author WpWolf
 * @package WolfDiscography/Templates
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$template = get_option( 'template' );

switch( $template ) {

	case 'twentyfourteen' :
		echo '</div><!-- #content --></div><!-- #primary --></div><!-- #main-content -->';
		break;
	default :
		echo '</div></div>';
		break;
}