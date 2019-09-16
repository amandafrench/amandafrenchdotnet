<?php
/**
 * NS Minimal Child Amanda functions and definitions
 *
 * 
 */

/* Enqueue parent stylesheet */

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );
function theme_enqueue_styles() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

/* Remove sidebar */ 


function remove_side_sidebar(){

	// Unregister the side sidebar, leave the footer widget areas
	unregister_sidebar( 'sidebar-1' );

}
add_action( 'widgets_init', 'remove_side_sidebar', 11 );


