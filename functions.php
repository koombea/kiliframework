<?php
/**
 * Load Kili Framework functions
 *
 * @package kiliframework
 */

/**
 * Load the class instance
 */
include_once( 'config/load.php' );

function ksource_widgets_init() {
	register_sidebar( array(
	  'name' => __( 'Main Sidebar', 'ksource' ),
	  'id' => 'sidebar-1',
	  'description' => __( 'Appears on posts and pages except the optional Front Page template, which has its own widgets', 'ksource' ),
	  'before_widget' => '<section id="%1$s" class="push--top %2$s">',
	  'after_widget' => '</section>',
	  'before_title' => '<h1 class="epsilon  weight--semibold">',
	  'after_title' => '</h1>',
	) );
  }

  add_action( 'widgets_init', 'ksource_widgets_init' );
