<?php
/**
 * The Template for displaying releases in a release category. Simply includes the archive template.
 *
 * Override this template by copying it to yourtheme/wolf-discography/taxonomy-band.php
 *
 * @author WpWolf
 * @package WolfDiscography/Templates
 * @since 1.0.2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

wolf_discography_get_template( 'archive-release.php' );