<?php
	
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines the general utilities class.

 *
 * @since      2.0.2
 */

class SimpleForm_Util {

	/**
	 * Search all shortcodes ids
     *
	 * @since    2.0.2
	 */
	
	public static function sform_ids() {
	   
       global $wpdb;
       $table_name = "{$wpdb->prefix}sform_shortcodes"; 
       $form_ids = $wpdb->get_col( "SELECT id FROM $table_name" );
       
       return $form_ids;
  
	}

	/**
	 * Search for all forms used in the post content
     *
	 * @since    2.0.5
	 */

	public static function used_forms($content,$type) {
	
	   $used_forms = array();
	   
	   if ($type == 'shortcode') {
	     
	     // Search for any type of simpleform shortcode
         $lastPos = 0;
         $positions = array();
         while ( ( $lastPos = strpos($content, '[simpleform', $lastPos)) !== false ) {
           $positions[] = $lastPos;
           $lastPos = $lastPos + strlen('[simpleform');
         }
         foreach ($positions as $value) {
	       $split = substr($content, $value);
           $shortcode = explode(']', $split)[0];
           if ( $shortcode == '[simpleform' ) { 
	         $form_id = '1'; 
	       } 
	       else { 
		     $form_id = strpos($shortcode, 'id') !== false && isset(explode('id', $shortcode)[1]) && trim(str_replace(array( '=', '"' ), '', explode('id', $shortcode)[1])) != '' ? str_replace(array( '=', '"' ), '', explode('id', $shortcode)[1]) : ''; 
		   }
           $used_forms[] = $form_id;
         }
         
       }

	   if ($type == 'all') {
		   	   
	     // Search for any type of simpleform shortcode
         $lastPos = 0;
         $positions = array();
         while ( ( $lastPos = strpos($content, '[simpleform', $lastPos)) !== false ) {
           $positions[] = $lastPos;
           $lastPos = $lastPos + strlen('[simpleform');
         }
         foreach ($positions as $value) {
	       $split = substr($content, $value);
           $shortcode = explode(']', $split)[0];
           if ( $shortcode == '[simpleform' ) { 
	         $form_id = '1'; 
	       } 
	       else { 
		     $form_id = strpos($shortcode, 'id') !== false && isset(explode('id', $shortcode)[1]) && trim(str_replace(array( '=', '"' ), '', explode('id', $shortcode)[1])) != '' ? str_replace(array( '=', '"' ), '', explode('id', $shortcode)[1]) : ''; 
		   }
           $used_forms[] = $form_id;
         }
       
	     // Search for the simpleform blocks
         if ( class_exists('SimpleForm_Block') ) {
         if ( has_blocks( $content ) ) {
	       $block_class = new SimpleForm_Block(SIMPLEFORM_NAME,SIMPLEFORM_VERSION);
           $ids = $block_class->get_sform_block_ids($content);
           $used_forms = array_merge($used_forms, $ids);
         }
         }

	   }

	   return $used_forms;
	   
	}
	
	/**
	 * Get a pages list that use simpleform in the post content
     *
	 * @since    2.0.2
	 */
    
	public static function form_pages($form_id) {
		
       global $wpdb;
       $table_name = "{$wpdb->prefix}sform_shortcodes";
     
       if ( $form_id != '0' ) {
	     $id = explode(':',$form_id)[0];
	     $type = isset(explode(':',$form_id)[1]) ? explode(':',$form_id)[1] : '';
	     $where = ! empty($type) && $type == 'widget' ? 'widget = %d' : 'id = %d';
         $query_shortcode = "SELECT shortcode_pages FROM `$table_name` WHERE $where";
         $result_shortcode_pages = $wpdb->get_var( $wpdb->prepare( $query_shortcode, $id ) ); 
         $query_block = "SELECT block_pages FROM `$table_name` WHERE $where";
         $result_block_pages = $wpdb->get_var( $wpdb->prepare( $query_block, $id ) ); 
         $shortcode_pages = $result_shortcode_pages ? explode(',',$result_shortcode_pages) : array();
         $block_pages = $result_block_pages ? explode(',',$result_block_pages) : array();
         $form_pages = array_merge($shortcode_pages, $block_pages);
       }
       
       if ( $form_id == '0' ) {
	      $form_pages = array();
          $shortcode_pages = $wpdb->get_col( "SELECT shortcode_pages FROM $table_name" );
          $block_pages = $wpdb->get_col( "SELECT block_pages FROM $table_name" );
          foreach ($shortcode_pages as $list) { 
	        if ( ! empty($list) ) { 
	           $form_pages = array_unique(array_merge($form_pages,explode(',',$list)));
		    }
          }
          foreach ($block_pages as $list) { 
	        if ( ! empty($list) ) { 
	           $form_pages = array_unique(array_merge($form_pages,explode(',',$list)));
	        }
          }
          update_option('sform_pages',$form_pages);
       }
              
       return $form_pages;
  
	}
		
	/**
	 * Get widget area name
     *
	 * @since    2.0.2
	 */

	public static function widget_area_name($key) {
		
	   $widget_area = '';
	   $sidebars_widgets = get_option('sidebars_widgets');
       global $wp_registered_sidebars;

	   foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	     if ( is_array( $widgets ) ) {
            $search = 'block-'.$key;
            if ( in_array($search, $widgets) ) {
                 $widget_area = isset($wp_registered_sidebars[$sidebar]['name']) ? $wp_registered_sidebars[$sidebar]['name'] : ''; 
            }
         }
       }

       return $widget_area;
  
	}
	
}

new SimpleForm_Util();