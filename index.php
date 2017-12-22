<?php
/**
 * The main template file
 *
 * @package kiliframework
 */

?><!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js no-svg">
	<head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="profile" href="http://gmpg.org/xfn/11">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<?php if ( ! have_posts() ) : ?>
			<div class="kili-container">
				<div class="kili-missing-block kili-soft">
					Sorry, no results were found.
				</div>
			</div>
		<?php
			endif;
			while ( have_posts() ) : the_post();
				get_template_part( 'app/views/templates/content', get_post_type() !== 'post' ? get_post_type() : get_post_format() );
			endwhile;
			the_posts_navigation();
			wp_footer();
		?>
	</body>
</html>
