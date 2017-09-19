<?php
/**
 * The main template file
 *
 * @package kiliframework
 */

get_template_part( 'app/views/templates/page', 'header' );
if ( ! have_posts() ) : ?>
  <div class="alert alert-warning">
    <?php __( 'Sorry, no results were found.', 'kiliframework' ); ?>
  </div>
<?php get_search_form();
endif;
while (have_posts()) : the_post();
	get_template_part( 'app/views/templates/content', get_post_type() !== 'post' ? get_post_type() : get_post_format() );
endwhile;
the_posts_navigation();