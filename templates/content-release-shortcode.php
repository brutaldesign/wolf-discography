<?php
global $more;
$more = 0;

$post_id = get_the_ID();

/* Metaboxes and Taxonomy */
$release_title = get_post_meta( $post_id, '_wolf_release_title', true );
$release_itunes = get_post_meta( $post_id, '_wolf_release_itunes', true );
$release_amazon = get_post_meta( $post_id, '_wolf_release_amazon', true );
$release_buy = get_post_meta( $post_id, '_wolf_release_buy', true );
?>
<li id="post-<?php the_ID(); ?>" <?php post_class( array( 'wolf-release', 'clearfix' ) ); ?>>
	<div class="entry-thumbnail">
		<?php if ( has_post_thumbnail() ) : ?>
		<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'wolf' ), the_title_attribute( 'echo=0' ) ) ); ?>">
			<?php the_post_thumbnail( 'CD' ); ?>
		</a>
		<?php endif; ?>
		<h3 class="entry-title">
			<a class="entry-link" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( __( 'Permalink to %s', 'wolf' ), the_title_attribute( 'echo=0' ) ) ); ?>">
				<?php the_title(); ?>
			</a>
		</h3>
		<div class="wolf-release-buttons">
			<?php if ( $release_itunes ) : ?>
			<div class="wolf-release-button">
				<a class="wolf-release-itunes" href="<?php echo $release_itunes; ?>">iTunes</a>
			</div>
			<?php endif; ?>
			<?php if ( $release_amazon ) : ?>
			<div class="wolf-release-button">
				<a class="wolf-release-amazon" href="<?php echo $release_amazon; ?>">Amazon</a>
			</div>
			<?php endif; ?>
			<?php if ( $release_buy ) : ?>
			<div class="wolf-release-button">
				<a class="wolf-release-buy" href="<?php echo $release_buy; ?>"><?php _e( 'Buy', 'wolf' ); ?></a>
			</div>
			<?php endif; ?>
		</div>
	</div>			
</li><!-- .wolf-release -->