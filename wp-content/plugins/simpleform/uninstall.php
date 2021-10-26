<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0
 */

// Prevent direct access. Exit if file is not called by WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Confirm user has decided to remove all data, otherwise stop.
$settings = get_option('sform_settings');

if ( isset( $settings['deletion_data'] ) && esc_attr($settings['deletion_data']) == 'false' ) {
	return;
}

if ( !is_multisite() )  {
global $wpdb;
// Drop shortcodes table.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' );
// Drop submissions table.
$wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' );
// Delete pre-built pages for contact form and thank you message
$form_page_ID = ! empty( $settings['form_pageid'] ) ? esc_attr($settings['form_pageid']) : '';  
$confirmation_page_ID = ! empty( $settings['confirmation_pageid'] ) ? esc_attr($settings['confirmation_pageid']) : '';	  
if ( ! empty($form_page_ID) && get_post_status($form_page_ID) ) { wp_delete_post( $form_page_ID, true); }
if ( ! empty($confirmation_page_ID) && get_post_status($confirmation_page_ID) ) { wp_delete_post( $confirmation_page_ID, true); }
// Search shortcode and remove it from content of any page or post
global $wpdb;
$table_post = $wpdb->prefix . 'posts';
$table_name = $wpdb->prefix . 'sform_shortcodes';
$shortcode_list = $wpdb->get_col("SELECT shortcode FROM `$table_name` WHERE area = 'page'");
foreach ($shortcode_list as $pattern) {
  $search = '%['.$pattern.']%';	
  $sql = $wpdb->prepare("SELECT ID,post_content FROM $table_post WHERE post_content LIKE %s AND post_type != 'revision'", $search);
  $results = $wpdb->get_results($sql);        
  if ( $results){
   foreach ($results as $post) { 
   $new_content = preg_replace ( $pattern, '' , $post->post_content );
   $post_id = $post->ID;	
   $wpdb->update( $table_post, array( 'post_content' => $new_content ), array( 'ID' => $post_id ) );
   }
  }
}
// Delete plugin options
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" );
// Remove any transients we've left behind.
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ('%\_transient\_sform\_%')" );
} 
else {
    global $wpdb;
    $blog_ids = $wpdb->get_col( "SELECT blog_id FROM {$wpdb->blogs}" );
    $original_blog_id = get_current_blog_id();
    foreach ( $blog_ids as $blog_id ) {
      switch_to_blog( $blog_id );
      // Drop shortcodes table.
      $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_shortcodes' );
      // Drop submissions table.
      $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . 'sform_submissions' );
      // Delete pre-built pages for contact form and thank you message
      $form_page_ID = ! empty( $settings['form_pageid'] ) ? esc_attr($settings['form_pageid']) : '';  
      $confirmation_page_ID = ! empty( $settings['confirmation_pageid'] ) ? esc_attr($settings['confirmation_pageid']) : '';	  
      if ( ! empty($form_page_ID) && get_post_status($form_page_ID) ) { wp_delete_post( $form_page_ID, true); }
      if ( ! empty($confirmation_page_ID) && get_post_status($confirmation_page_ID) ) { wp_delete_post( $confirmation_page_ID, true); }
      // Search shortcode and remove it from content of any page or post
      global $wpdb;
      $table_post = $wpdb->prefix . 'posts';
      $table_name = $wpdb->prefix . 'sform_shortcodes';
      $shortcode_list = $wpdb->get_col("SELECT shortcode FROM `$table_name` WHERE area = 'page'");
      foreach ($shortcode_list as $pattern) { 
        $search = '%['.$pattern.']%';	
        $sql = $wpdb->prepare("SELECT ID,post_content FROM $table_post WHERE post_content LIKE %s AND post_type != 'revision'", $search);
        $results = $wpdb->get_results($sql);        
        if ( $results){
        foreach ($results as $post) { 
        $new_content = preg_replace ( $pattern, '' , $post->post_content );
        $post_id = $post->ID;	
        $wpdb->update( $table_post, array( 'post_content' => $new_content ), array( 'ID' => $post_id ) );
        }
        }
      }
      // Delete plugin options
      $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\_%'" );
      $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'sform\-%'" );
      $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '%\_sform\_%'" );
      // Remove any transients we've left behind.
      $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE ('%\_transient\_sform\_%')" );
    }
    switch_to_blog( $original_blog_id );
}