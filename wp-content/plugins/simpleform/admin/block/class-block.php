<?php

 /**
 * Defines the block of the plugin.
 *
 * @since      2.0
 */

class SimpleForm_Block {

	/**
	 * The ID of this plugin.
	 *
	 */
	
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 */
	
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 */
	
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the block.
     *
	 * @since    2.0
	 */
	 
	public function register_block() {
		
      $asset_file = include( plugin_dir_path( __FILE__ ) . 'build/index.asset.php');
      $metadata = (array)json_decode(file_get_contents(__DIR__ . '/block.json'), true);
    
      wp_register_script(
        'sform-editor-script',
        plugins_url( 'build/index.js', __FILE__ ),
        $asset_file['dependencies'],
        $asset_file['version']  
      );	

	  global $wpdb; 
	  $forms = $wpdb->get_results( "SELECT id, name FROM {$wpdb->prefix}sform_shortcodes WHERE widget = '0'", 'ARRAY_A' );
      $empty_value = array( 'id' => '', 'name' => __( 'Select an existing form', 'simpleform' ) );
      array_unshift($forms , $empty_value);
      $id_list = array_column($forms, 'id');           
      $above_ids = array();
      $below_ids = array();
      $default_ids = array();
      $basic_ids = array();
      $rounded_ids = array();
      $minimal_ids = array();
      $transparent_ids = array();
      $highlighted_ids = array();
              
      foreach ($id_list as $id) {
	    if ($id) { 
		  switch ($id) {
          case $id > '1':
          $form_attributes = get_option('sform_'.$id.'_attributes');
          $form_settings = get_option('sform_'.$id.'_settings');
          break;
          default:
          $form_attributes = get_option('sform_attributes');
          $form_settings = get_option('sform_settings');
          }
	    if ( ! empty($form_attributes['introduction_text']) ) { array_push($above_ids, $id); }	
	    if ( ! empty($form_attributes['bottom_text']) ) { array_push($below_ids, $id); }
 	    if ( ! empty($form_settings['form_template']) ) { 
	       if ( $form_settings['form_template'] == 'default' ) { array_push($default_ids, $id); }	    
  	       if ( $form_settings['form_template'] == 'basic' ) { array_push($basic_ids, $id); }	    
 	       if ( $form_settings['form_template'] == 'rounded' ) { array_push($rounded_ids, $id); }	    
 	       if ( $form_settings['form_template'] == 'minimal' ) { array_push($minimal_ids, $id); }	    
 	       if ( $form_settings['form_template'] == 'transparent' ) { array_push($transparent_ids, $id); }	    
 	       if ( $form_settings['form_template'] == 'highlighted' ) { array_push($highlighted_ids, $id); }	    
		}	    
        }	
      }
	
      wp_localize_script('sform-editor-script', 'sformblockData', array(
	    'forms' => $forms,
        'cover_url' => plugins_url( 'img/block-preview.png', __FILE__ ),
        'logo_url' => plugins_url( 'img/simpleform-icon.png', __FILE__ ),
		'above' => $above_ids,
		'below' =>	$below_ids,
        'default_style' => $default_ids,
        'basic_style' => $basic_ids,
        'rounded_style' => $rounded_ids,
        'minimal_style' => $minimal_ids,
        'transparent_style' => $transparent_ids,
        'highlighted_style' => $highlighted_ids,
      ));
      
      wp_register_style('sform-editor-style', plugins_url( 'build/index.css', __FILE__ ),[], filemtime( plugin_dir_path( __FILE__ ) . 'build/index.css' ) );	

      register_block_type('simpleform/form-selector', 
        array_merge($metadata,
        array(
	    'title' => __( 'SimpleForm', 'simpleform' ),
	    'description' => __( 'Display a contact form', 'simpleform' ),
        'render_callback' => array( $this, 'sform_render_block' ),
        'editor_script' => 'sform-editor-script',
        'editor_style' => 'sform-editor-style'
        )
        )
      );
      
      wp_set_script_translations( 'sform-editor-script', 'simpleform' );
    
	}

	/**
	 * Render a form given the specified attributes
     *
	 * @since    2.0
	 */

	public function sform_render_block($attributes) {
      
  	  $form_id = ! empty( $attributes['formId'] ) && absint($attributes['formId']) ? $attributes['formId'] : '';
        
	  if ( empty( $form_id ) ) {
			return '';
	  }

      $css_settings = '';
  	  $bgcolor = ! empty( $attributes['bgColor'] ) ? $attributes['bgColor'] : '';
  	  $labelcolor = ! empty( $attributes['labelColor'] ) ? $attributes['labelColor'] : '';
  	  $borderradius = ! empty( $attributes['borderRadius'] ) ? $attributes['borderRadius'] : '';
      $css_settings .= ! empty($bgcolor) ? '#form-wrap-'.$form_id.' {background-color: '.$bgcolor.';}' : '';
      $css_settings .= ! empty($labelcolor) ? '#form-wrap-'.$form_id.' label.sform {color: '.$labelcolor.';}' : '';
      $css_settings .= ! empty($borderradius) ? '#form-wrap-'.$form_id.' {border-radius: '.$borderradius.'px;}' : '';
   	  $buttoncolor = ! empty( $attributes['buttonColor'] ) ? $attributes['buttonColor'] : '';
  	  $buttonbordercolor = ! empty( $attributes['buttonBorderColor'] ) ? $attributes['buttonBorderColor'] : '';
  	  $buttontextcolor = ! empty( $attributes['buttonTextColor'] ) ? $attributes['buttonTextColor'] : '';
  	  $hoverbuttoncolor = ! empty( $attributes['hoverButtonColor'] ) ? $attributes['hoverButtonColor'] : '';
  	  $hoverbuttonbordercolor = ! empty( $attributes['hoverButtonBorderColor'] ) ? $attributes['hoverButtonBorderColor'] : '';
  	  $hoverbuttontextcolor = ! empty( $attributes['hoverButtonTextColor'] ) ? $attributes['hoverButtonTextColor'] : '';
      $css_settings .= ! empty($buttoncolor) ? '#submission-'.$form_id.' {background-color: '.$buttoncolor.';}' : '';
      $css_settings .= ! empty($buttonbordercolor) ? '#submission-'.$form_id.' {border-color: '.$buttonbordercolor.';}' : '';
      $css_settings .= ! empty($buttontextcolor) ? '#submission-'.$form_id.' {color: '.$buttontextcolor.';}' : '';
      $css_settings .= ! empty($hoverbuttoncolor) ? '#submission-'.$form_id.':hover {background-color: '.$hoverbuttoncolor.';}' : '';
      $css_settings .= ! empty($hoverbuttonbordercolor) ? '#submission-'.$form_id.':hover {border-color: '.$hoverbuttonbordercolor.';}' : '';
      $css_settings .= ! empty($hoverbuttontextcolor) ? '#submission-'.$form_id.':hover {color: '.$hoverbuttontextcolor.';}' : '';
     if ( $css_settings ) { wp_add_inline_style( 'sform-public-style', $css_settings ); }
	  $anchor = ! empty( $attributes['formAnchor'] ) ? 'id="' . $attributes['formAnchor'] . '"' : '';
      $topmargin = ! empty( $attributes['topMargin'] ) && absint( $attributes['topMargin'] ) ? 'margin-top:'. $attributes['topMargin'] .'px;' : '';
      $rightmargin = ! empty( $attributes['rightMargin'] ) && absint( $attributes['rightMargin'] ) ? 'margin-right:'. $attributes['rightMargin'] .'px;' : '';
      $bottommargin = ! empty( $attributes['bottomMargin'] ) && absint( $attributes['bottomMargin'] ) ? 'margin-bottom:'. $attributes['bottomMargin'] .'px;' : '';
      $leftmargin = ! empty( $attributes['leftMargin'] ) && absint( $attributes['leftMargin'] ) ? 'margin-left:'. $attributes['leftMargin'] .'px;' : '';
  	  $toppadding = ! empty( $attributes['topPadding'] ) && absint( $attributes['topPadding'] ) ? 'padding-top:'. $attributes['topPadding'] .'px;' : '';
      $rightpadding = ! empty( $attributes['rightPadding'] ) && absint( $attributes['rightPadding'] ) ? 'padding-right:'. $attributes['rightPadding'] .'px;' : '';
      $bottompadding = ! empty( $attributes['bottomPadding'] ) && absint( $attributes['bottomPadding'] ) ? 'padding-bottom:'. $attributes['bottomPadding'] .'px;' : '';
      $leftpadding = ! empty( $attributes['leftPadding'] ) && absint( $attributes['leftPadding'] ) ? 'padding-left:'. $attributes['leftPadding'] .'px;' : '';
      $spacing = ! empty($topmargin) || ! empty($rightmargin) || ! empty($bottommargin) || ! empty($leftmargin) || ! empty($toppadding) || ! empty($rightpadding) || ! empty($bottompadding) || ! empty($leftpadding) ? true : false;
	  $anchor_tag = $spacing ? '' : $anchor;
      $form_attributes = $form_id != '' && $form_id != '1' && get_option('sform_'.$form_id.'_attributes') != false ? get_option('sform_'.$form_id.'_attributes') : get_option('sform_attributes');
      $settings = $form_id != '' && $form_id != '1' && get_option('sform_'.$form_id.'_settings') != false ? get_option('sform_'.$form_id.'_settings') : get_option('sform_settings');
      $custom_css = ! empty( $form_attributes['additional_css'] ) ? esc_attr($form_attributes['additional_css']) : ''; 	    
      $form_template = ! empty( $settings['form_template'] ) ? esc_attr($settings['form_template']) : 'default'; 
      $stylesheet = ! empty( $settings['stylesheet'] ) ? esc_attr($settings['stylesheet']) : 'false';
      $cssfile = ! empty( $settings['stylesheet_file'] ) ? esc_attr($settings['stylesheet_file']) : 'false';
      $form_direction = ! empty( $form_attributes['form_direction'] ) ? esc_attr($form_attributes['form_direction']) : 'ltr';
      $class_direction = $form_direction == 'rtl' ? 'rtl' : '';
      $shortcode = $form_id != '1' ? '[simpleform id="'.$form_id.'" type="block"]' : '[simpleform type="block"]';         
      $title  = ! empty( $attributes['displayTitle'] ) ? true : false;
      $heading = $title == true && ! empty( $attributes['titleHeading'] ) && in_array($attributes['titleHeading'], array('h1','h2','h3','h4','h5','h6' )) ? esc_attr( $attributes['titleHeading'] ) : '';
      $alignment = $title == true && ! empty( $attributes['titleAlignment'] ) && in_array($attributes['titleAlignment'], array('left','center','right' )) ? esc_attr( $attributes['titleAlignment'] ) : '';
      $title_alignment = ! empty($alignment) ? 'class="sform align-'. $alignment .'"' : 'class="sform"';
      $start_tag = ! empty($heading) ? '<'. $heading .' '.$anchor_tag.' '.$title_alignment.'>' : '';
      $end_tag = ! empty($heading) ? '</'. $heading .'>' : '';
      $form_title = $title == true && ! empty( $form_attributes['form_name'] ) ? $start_tag . esc_attr($form_attributes['form_name'] ) . $end_tag : '';
      $success_class = isset( $_GET['sending'] ) && $_GET['sending'] == 'success' && isset( $_GET['form'] ) && $_GET['form'] == $form_id ? 'success' : '';
      $start_wrap = $css_settings || $spacing || ! empty($anchor) ? '<div id="form-wrap-'.$form_id.'" ' .$anchor.' style="'.$topmargin.$rightmargin.$bottommargin.$leftmargin.$toppadding.$rightpadding.$bottompadding.$leftpadding.'" class="form-wrap '.$success_class.'">' : '';
      $end_wrap = $css_settings || $spacing || ! empty($anchor) ? '</div>' : '';
      $description  = ! empty( $attributes['formDescription'] ) ? true : false;
      $ending  = ! empty( $attributes['formEnding'] ) ? true : false;
      $form_description = $description == true && ! empty( $form_attributes['introduction_text'] ) ? '<div id="sform-introduction-'.$form_id.'" class="sform-introduction '.$class_direction.'">'.stripslashes(wp_kses_post($form_attributes['introduction_text'])).'</div>' : '';
      $bottom_text = $ending == true && ! empty( $form_attributes['bottom_text'] ) ? '<div id="sform-bottom-'.$form_id.'" class="sform-bottom '.$class_direction.'">'.stripslashes(wp_kses_post($form_attributes['bottom_text'])).'</div>' : '';
      $is_gb_editor = defined( 'REST_REQUEST' ) && REST_REQUEST;
      $form_display = ! empty( $attributes['formDisplay'] ) && $attributes['formDisplay'] == true ? true : false;
      $form_widget = ! empty( $attributes['formWidget'] ) && $attributes['formWidget'] == true ? true : false;
      $form_shortcode = ! empty( $attributes['formShortcode'] ) && $attributes['formShortcode'] == true ? true : false;

		if ( $is_gb_editor ) {
		  if ( $stylesheet == 'false') { $css_content = file_get_contents(SIMPLEFORM_URL . 'public/css/public-min.css'); }
		  else { 
            $css_content = 'input, select, textarea {width: 100%;} #captcha-field-'.$form_id.' { width: 150px; height: intrinsic; } #captcha-question-'.$form_id.' { width: 80px; height: inherit; cursor: text; border: none !important; outline: none; display: inline-block; background-color: transparent; padding-right: 0; padding-left: 0; box-shadow: none; } #sform-captcha-'.$form_id.' { width: 50px; border: none !important; outline: none; display: inline-block; background-color: transparent; padding-left: 5px ; box-shadow: none; } input::-webkit-outer-spin-button, input::-webkit-inner-spin-button { -webkit-appearance: none; margin: 0; } input[type=number] { -moz-appearance: textfield; } .carrots { opacity: 0; position: absolute; top: 0; left: 0; height: 0; width: 0; z-index: -1; } .error-des { line-height: 1; color: #dc3545; font-size: 14px; height: 14px; margin-top: 5px; margin-bottom: 5px; } .error-des span { display: none; } .sform-field.is-invalid + .error-des span { display: block; } #errors-'.$form_id.' { padding: 5px 0 25px 0; position: relative; outline: none; } #errors-'.$form_id.' span { display: block; visibility: hidden; text-align: center; } .captcha-error { display: none !important; } .noscript { position: absolute; top: 0; width: 100%; } #errors-'.$form_id.'.top { margin-bottom: 20px; } .form.confirmation { outline: none; } .form.confirmation { text-align: center; padding-top: 50px; } .form.confirmation > img, .form.confirmation > p > img { margin: 30px auto; width: 250px; } #sform-confirmation-'.$form_id.' { outline: 0; } .d-block { display: inline-block; } .d-none { display: none !important; } .v-visible { visibility: visible !important; } .v-invisible { visibility: hidden !important; opacity: 0; } .align-left { text-align: left; } .align-center { text-align: center; } .align-right { text-align: right; } h1.sform,h2.sform,h3.sform,h4.sform,h5.sform,h6.sform { color: inherit; }';
            if ( $cssfile == 'true' ) {
                if (is_child_theme() ) {
	               if ( file_exists( get_stylesheet_directory()  . '/simpleform/custom-style.css' ) ) {
                      $css_content = file_get_contents(get_stylesheet_directory_uri() . '/simpleform/custom-style.css' );
	               }
                }
                else { 
	               if ( file_exists( get_template_directory()  . '/simpleform/custom-style.css' ) ) {
                      $css_content = file_get_contents(get_template_directory_uri() . '/simpleform/custom-style.css' );
	               }
                }
            }
          }
 		  if ( $form_display == false ) {
	 	     global $wpdb;
             $table_name = "{$wpdb->prefix}sform_shortcodes"; 
             $query = "SELECT name FROM `$table_name` WHERE id = %s";
             $name = '"'. $wpdb->get_var( $wpdb->prepare( $query, $form_id ) ) .'"'; 
        	 if ( $form_widget == false ) {
        	   $message =  $form_shortcode == false ? __( 'The block cannot be displayed on this page since the same block has been used.', 'simpleform' ) . '<br>' . sprintf( __( 'You may display the form %s only once to make it work properly.', 'simpleform' ), $name ) . '&nbsp;' . __( 'Remove the block or select a different one.', 'simpleform' ) : sprintf( __( 'The block cannot be displayed on this page since the shortcode %s has been used.', 'simpleform' ), $shortcode ) . '<br>' . sprintf( __( 'You may display the form %s only once to make it work properly.', 'simpleform' ), $name ) . '&nbsp;' . __( 'Remove the block or select a different one.', 'simpleform' );
	         } 
        	 else {
	           $message =  $form_shortcode == false ? __( 'The block cannot be displayed on this widget area since the same block has been used.', 'simpleform' ) . '<br>' . sprintf( __( 'You may display the form %s only once to make it work properly.', 'simpleform' ), $name ) . '&nbsp;' . __( 'Remove the block or select a different one.', 'simpleform' ) : sprintf( __( 'The block cannot be displayed on this widget area since the shortcode %s has been used.', 'simpleform' ), $shortcode ) . '<br>' . sprintf( __( 'You may display the form %s only once to make it work properly.', 'simpleform' ), $name ) . '&nbsp;' . __( 'Remove the block or select a different one.', 'simpleform' );
             }
			$return_html = '<div id="duplication-notice"><label class="components-placeholder__label"><svg viewBox="0 0 180 180" xmlns="http://www.w3.org/2000/svg" width="24" height="24" role="img" aria-hidden="true" focusable="false"><path d="M96.326,111.597c0-18.193-0.167-36.391,0.053-54.58 c0.188-15.525,3.512-29.949,12.957-41.421c9.567-11.622,21.017-11.457,30.737-0.01c7.372,8.682,10.607,19.568,12.215,31.381 c0.732,5.379,0.851,10.786,0.849,16.214c-0.011,29.197-0.002,58.396-0.007,87.595c-0.002,6.48-4.014,10.405-9.378,9.323 c-1.924-0.389-1.816-2.022-1.926-3.624c-0.695-10.047-0.688-10.011-8.982-7.314c-6.804,2.212-13.586,4.543-20.463,6.387 c-3.582,0.962-5.123,2.99-4.787,7.271c0.146,1.889,0.034,3.815-0.05,5.717c-0.121,2.802-1.362,4.579-3.627,5.479 c-6.666,2.648-7.592,1.872-7.592-6.516C96.325,148.864,96.325,130.23,96.326,111.597z" fill="currentColor"></path><path d="M27.769,107.198c0-15.412-0.03-30.824,0.006-46.234 c0.066-28.643,17.508-50.748,41.681-53.416c10.049-1.108,20.08-0.48,30.118-0.75c0.936-0.025,2.139-0.439,2.631,0.961 c0.478,1.368-0.575,2.092-1.229,2.922c-0.76,0.967-1.845,1.741-2.281,2.873c-2.752,7.121-7.72,7.832-13.544,7.427 c-6.419-0.445-12.871-0.373-19.217,1.558C49.624,27.498,38.989,43.42,39.058,63.261c0.029,8.499,0.51,16.996,0.485,25.493 c-0.039,13.634-0.362,27.268-0.496,40.901c-0.065,6.679,1.043,7.76,6.557,8.476c12.062,1.562,24.085,3.49,36.146,5.019 c3.442,0.438,4.282,2.441,4.271,6.104c-0.025,9.025-0.132,8.982-7.748,7.741c-11.527-1.878-23.107-3.308-34.656-5.002 c-3.365-0.496-4.713,0.846-4.562,5.06c0.346,9.731,0.213,8.388-7.725,7.188c-2.969-0.446-3.621-2.725-3.603-5.963 C27.816,141.25,27.769,124.225,27.769,107.198z" fill="currentColor"></path><path d="M75.697,51.212c-5.191-0.897-10.416-0.479-15.628-0.553 c-2.054-0.029-2.659-0.985-2.13-3.342c1.504-6.724,6.782-12.072,12.691-12.477c3.083-0.211,6.184-0.019,9.271-0.12 c1.641-0.054,1.945,0.99,1.602,2.487c-0.899,3.906-1.4,7.864-1.404,11.914c-0.002,1.369-0.648,2.056-1.787,2.086 C77.44,51.23,76.568,51.212,75.697,51.212z" fill="currentColor"></path><path d="M73.535,48.245c-3.321-0.574-6.665-0.307-10.001-0.354 c-1.313-0.019-1.702-0.63-1.362-2.139c0.963-4.303,4.34-7.726,8.121-7.986c1.975-0.135,3.959-0.012,5.936-0.076 c1.049-0.035,1.244,0.633,1.024,1.592c-0.577,2.5-0.897,5.033-0.899,7.625c0,0.875-0.414,1.316-1.144,1.335 C74.651,48.256,74.094,48.245,73.535,48.245z" fill="transparent"></path></svg>'. __( 'SimpleForm', 'simpleform' ) .'</label>' . $message . '</div>';
	      }
	      else {
		    $css = '<style>' . $css_content . $custom_css . $css_settings . '</style>';
      	    $return_html = $start_wrap . $form_title . $form_description . '<fieldset disabled>' . do_shortcode($shortcode) .'</fieldset>' . $bottom_text . $end_wrap . $css;	
	      }
        } 

        else {
            $above_form = isset( $_GET['sending'] ) && $_GET['sending'] == 'success' && isset( $_GET['form'] ) && $_GET['form'] == $form_id ? '' : $form_description;
            $below_form = isset( $_GET['sending'] ) && $_GET['sending'] == 'success' && isset( $_GET['form'] ) && $_GET['form'] == $form_id ? '' : $bottom_text;
	        $return_html = $start_wrap . $form_title . $above_form . do_shortcode($shortcode) . $below_form . $end_wrap; 
        }
        
		return $return_html;      
    
    }

	/**
	 * Extract the SimpleForm block from found blocks (To be used when editing a page)
     *
	 * @since    2.0
	 */
	 
    public function get_simpleform_block($block) {
      
      if ($block['blockName'] === 'simpleform/form-selector') {
        return $block;
      }
      
      if ($block['innerBlocks']) { 
        foreach ($block['innerBlocks'] as $innerblock) {
          if ($innerblock['blockName'] === 'simpleform/form-selector') {
	          return $innerblock;
          }
          if ($innerblock['innerBlocks']) {
            foreach ($innerblock['innerBlocks'] as $innerblock2) {
              if ($innerblock2['blockName'] === 'simpleform/form-selector') {
	              return $innerblock2;
              }
              if ($innerblock2['innerBlocks']) {
                foreach ($innerblock2['innerBlocks'] as $innerblock3) {
                  if ($innerblock3['blockName'] === 'simpleform/form-selector') {
	                  return $innerblock3;
                  }
                  if ($innerblock3['innerBlocks']) {
                    foreach ($innerblock3['innerBlocks'] as $innerblock4) {
                      if ($innerblock4['blockName'] === 'simpleform/form-selector') {
	                      return $innerblock4;
                      }
                      if ($innerblock4['innerBlocks']) {
                        foreach ($innerblock4['innerBlocks'] as $innerblock5) {
                          if ($innerblock5['blockName'] === 'simpleform/form-selector') {
	                          return $innerblock5;
                          }
                          if ($innerblock5['innerBlocks']) {
                            foreach ($innerblock5['innerBlocks'] as $innerblock6) {
                              if ($innerblock6['blockName'] === 'simpleform/form-selector') {
	                            return $innerblock6;
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      
    }

	/**
	 * Update pages list containing a block form when a page is edited.
     *
	 * @since    2.0
	 */
	
    public function sform_block_pages( $post_id, $post ) {
 
      // Return if this is just a revision
      if ( wp_is_post_revision( $post_id ) ) {
        return;
      }
      
      // List of all forms IDs that have been created
      $util = new SimpleForm_Util();      
      $form_ids = $util->sform_ids();
      // Retrieve all forms used in the post content
      $used_forms = $util->used_forms($post->post_content,$type = 'all');
      global $wpdb;
      $table_name = "{$wpdb->prefix}sform_shortcodes";
      $id = array($post_id);
      
      // If the post content contains a block    
      if ( has_blocks( $post->post_content ) ) {
        $blocks = parse_blocks( $post->post_content );
        $used_forms = array();
        foreach ( $blocks as $block ) {
          $sform_block = $this->get_simpleform_block($block);
          // If the post content contains the simpleform block create a list of used forms
          if ( $sform_block && isset($sform_block['attrs']['formId']) && ! empty($sform_block['attrs']['formId']) ) {
             $used_forms[] = $sform_block['attrs']['formId'];
          }
        }
        foreach ($form_ids as $form_id) {
	      // Check if the form is used and include the post ID in the list if not yet inserted
	      if ( ! empty($used_forms) && in_array($form_id,$used_forms) ) {	
             $form_pages = $wpdb->get_var( "SELECT block_pages FROM $table_name WHERE id = {$form_id}" );
             $pages = ! empty($form_pages) ? explode(',',$form_pages) : array();
             if ( ! in_array($post_id,$pages) ) {
	            $new_pages = implode(",", array_unique(array_merge($id,$pages)));
                $wpdb->update($table_name, array('block_pages' => $new_pages), array('id' => $form_id ));
	         } 
		  }
	      // Update the lists for the unused forms
		  else {	    
             $form_pages = $wpdb->get_var( "SELECT block_pages FROM $table_name WHERE id = {$form_id}" );
             $pages = ! empty($form_pages) ? explode(",", $form_pages) : array();
             if ( in_array($post_id,$pages) ) {
                $new_pages = implode(",", array_diff($pages,$id)); 
                $wpdb->update($table_name, array('block_pages' => $new_pages), array('id' => $form_id ));
	         }
 	      }
        }
      } 
      
      // If the post content does not contain any block    
      else {
       foreach ($form_ids as $form_id) {	       
         $form_pages = $wpdb->get_var( "SELECT block_pages FROM $table_name WHERE id = {$form_id}" );
         $pages = ! empty($form_pages) ? explode(",", $form_pages) : array();
         if ( in_array($post_id,$pages) ) {
            $new_pages = implode(",", array_diff($pages,$id)); 
            $wpdb->update($table_name, array('block_pages' => $new_pages), array('id' => $form_id ));
	     }
	   }  
      }
                
    }
    
	/**
	 * Clean up the widget areas of any non-existent or already used form when the widgets page is loaded.
	 *
	 * @since    2.0.4
	 */
    
     public function clean_up_widget_areas(){
	     
       global $pagenow;
       if ( $pagenow == 'widgets.php' ) {
	    $widget_block = get_option("widget_block") != false ? get_option("widget_block") : array();
        $used_ids = array();
        global $wpdb;
        $table_name = "{$wpdb->prefix}sform_shortcodes";
        $widget_forms = $wpdb->get_col( "SELECT id FROM $table_name WHERE widget_id != ''" );
        $util = new SimpleForm_Util();      
        $form_ids = $util->sform_ids();
        if ( !empty($widget_block) ) {
          foreach ($widget_block as $key => $value ) {
	        $widget_id = 'block-'.$key;
            $array_widget_id = array($widget_id);
            if ( is_array($value) ) {   
	           $string = implode('',$value);
               if ( strpos($string, 'wp:simpleform/form-selector') !== false ) {
	             $split_display = ! empty($string) ? explode('formDisplay":', $string) : '';
	             $formDisplay = isset($split_display[1]) ? explode(',"', $split_display[1])[0] : '';
	             $split_id = ! empty($string) ? explode('formId":"', $string) : '';
	             $id = isset($split_id[1]) ? explode('"', $split_id[1])[0] : '';
	             if ( $formDisplay == 'false' || in_array($id, $used_ids) ) {
			      unset($widget_block[$key]);
			      update_option('widget_block', $widget_block);
                  $widget_used_in = $wpdb->get_var( "SELECT widget_id FROM $table_name WHERE id = {$id}" );
                  $widget_ids = ! empty($widget_used_in) ? explode(',',$widget_used_in) : array();
                  if ( in_array($widget_id,$widget_ids) ) {
                    $updated_used_ids = array_diff($widget_ids,$array_widget_id);
                    $new_used_in = implode(",", $updated_used_ids); 
                    $wpdb->query( $wpdb->prepare("UPDATE $table_name SET widget_id = '$new_used_in' WHERE id = %d", $id) );
                  }
                 }
                 else {
	              if ( !empty($id)) {                             
                  $used_ids[] = $id;
                  $widget_ids = $wpdb->get_var( "SELECT widget_id FROM $table_name WHERE id = {$id}" );
                  $ids = ! empty($widget_ids) ? explode(',',$widget_ids) : array();
                  if ( ! in_array($widget_id,$ids) ) {
	              $new_ids = implode(",", array_unique(array_merge($array_widget_id,$ids)));
                  $wpdb->update($table_name, array('widget_id' => $new_ids), array('id' => $id ));
	              }
                  // Remove widget id from lists where not used
                  $widget_ids_list = $wpdb->get_results( "SELECT id, widget_id FROM $table_name WHERE id != {$id}", 'ARRAY_A' );
                  foreach($widget_ids_list as $list) { 
	                  $form_id = $list['id']; 
	                  $form_widget_ids = ! empty($list['widget_id']) ? explode(',',$list['widget_id']) : array();
                     if ( in_array($widget_id,$form_widget_ids) ) {
	                 $new_ids = implode(",", array_diff($form_widget_ids,$array_widget_id));
                     $wpdb->update($table_name, array('widget_id' => $new_ids), array('id' => $form_id ));
	                 }
	              }
                  }
	              else {
		            // Remove blocks where form id has not been selected
			        unset($widget_block[$key]);
			        update_option('widget_block', $widget_block);
                 }
                 }
                 if ( !empty($id) && ($key = array_search($id, $widget_forms)) !== false ) {
                   unset($widget_forms[$key]);
                 }
               }
               if ( strpos($string,'[simpleform') !== false ) {
	             $split_shortcode = ! empty($string) ? explode('[simpleform', $string) : '';
	             $split_id = isset($split_shortcode[1]) ? explode(']', $split_shortcode[1])[0] : '';
	             $id = empty($split_id) ? '1' : filter_var($split_id, FILTER_SANITIZE_NUMBER_INT);
	             if (in_array($id, $used_ids) || ! in_array($id, $form_ids) ) { 		             
			      unset($widget_block[$key]);
			      update_option('widget_block', $widget_block);			      
                  $widget_used_in = $wpdb->get_var( "SELECT widget_id FROM $table_name WHERE id = {$id}" );
                  $widget_ids = ! empty($widget_used_in) ? explode(',',$widget_used_in) : array();
                  if ( in_array($widget_id,$widget_ids) ) {
                    $updated_used_ids = array_diff($widget_ids,$array_widget_id);
                    $new_used_in = implode(",", $updated_used_ids); 
                    $wpdb->query( $wpdb->prepare("UPDATE $table_name SET widget_id = '$new_used_in' WHERE id = %d", $id) );
                  }
	             }
                 else {                              
	              if ($id) {                             
                  $used_ids[] = $id;
                  $widget_id = 'block-'.$key;
                  $array_widget_id = array($widget_id);
                  $widget_ids = $wpdb->get_var( "SELECT widget_id FROM $table_name WHERE id = {$id}" );
                  $ids = ! empty($widget_ids) ? explode(',',$widget_ids) : array();
                  if ( ! in_array($widget_id,$ids) ) {
	              $new_ids = implode(",", array_unique(array_merge($array_widget_id,$ids)));
                  $wpdb->update($table_name, array('widget_id' => $new_ids), array('id' => $id ));
	              }
                  $widget_ids_list = $wpdb->get_results( "SELECT id, widget_id FROM $table_name WHERE id != {$id}", 'ARRAY_A' );
                  foreach($widget_ids_list as $list) { 
	                  $form_id = $list['id']; 
	                  $form_widget_ids = ! empty($list['widget_id']) ? explode(',',$list['widget_id']) : array();
                     if ( in_array($widget_id,$form_widget_ids) ) {
	                 $new_ids = implode(",", array_diff($form_widget_ids,$array_widget_id));
                     $wpdb->update($table_name, array('widget_id' => $new_ids), array('id' => $form_id ));
	                 }
	              }
                  }
                 }
                 if ( !empty($id) && ($key = array_search($id, $widget_forms)) !== false ) {
                   unset($widget_forms[$key]);
                 }
               }
            }
          }
          if ( !empty($widget_forms) ) {
	        foreach ($form_ids as $form_id) {
		      if ( in_array($form_id,$widget_forms) ) {  
                $wpdb->query( $wpdb->prepare("UPDATE $table_name SET widget_id = '' WHERE id = %d", $form_id) );
              }
            }
          }
        } 
        else {                 
	      foreach ($form_ids as $form_id) {	       
            $wpdb->query( $wpdb->prepare("UPDATE $table_name SET widget_id = '' WHERE id = %d", $form_id) );
          }
        }
                         
       }
       
     }      
          
	/**
	 * Hide widget blocks if the form already appears in the post content.
	 *
	 * @since    2.0.4
	 */
    
    public function hide_widgets( $sidebars_widgets ) {
	 
       if ( is_admin() )
       return $sidebars_widgets;
       
       $post_id = get_the_ID(); //  return int or false
       $post_content = get_the_content();       
       $util = new SimpleForm_Util();   
       $used_ids = $post_id ? $util->used_forms($post_content,$type = 'all') : array();
       global $wpdb;
       $table_name = "{$wpdb->prefix}sform_shortcodes";       
       $sform_pages = get_option('sform_pages') != false ? get_option('sform_pages') : array();
       
       // one time
       if ( empty($sform_pages) ) { 
         $pages = $wpdb->get_col( "SELECT pages FROM $table_name" );
         $shortcode_pages = $wpdb->get_col( "SELECT shortcode_pages FROM $table_name" );
         $block_pages = $wpdb->get_col( "SELECT block_pages FROM $table_name" );
         if ($pages) { 
         foreach ($pages as $list) { 
	       if ( ! empty($list) ) { 
	          $sform_pages = array_unique(array_merge($sform_pages,explode(',',$list)));
		   }
         }
         }
         foreach ($shortcode_pages as $list) { 
	       if ( ! empty($list) ) { 
	          $sform_pages = array_unique(array_merge($sform_pages,explode(',',$list)));
		   }
         }
         foreach ($block_pages as $list) { 
	       if ( ! empty($list) ) { 
	          $sform_pages = array_unique(array_merge($sform_pages,explode(',',$list)));
	  	   }
         }
         update_option('sform_pages',$sform_pages);
       }
	   
	   if ( empty($sform_pages) || ( ! is_page($sform_pages) && ! is_single($sform_pages) ) )
	   return $sidebars_widgets;
	   
  	   $widget_block = get_option("widget_block") != false ? get_option("widget_block") : array();
	   	   
  	   foreach ( $sidebars_widgets as $widget_area => $widget_list ) {
	     if ( $widget_area != 'wp_inactive_widgets' ) {   
           foreach ( $widget_list as $pos => $widget_id ) { 
	         if ( ( $search = strpos($widget_id, 'block-' )) !== false ) {
		        $block_id = substr($widget_id, 6); 
		        foreach ($widget_block as $key => $value ) {	
			      if ( $key == $block_id )  {
                    $string = implode('',$value);
  				    if ( strpos($string, 'wp:simpleform/form-selector' ) !== false ) { 
	                  $split_display = ! empty($string) ? explode('formDisplay":', $string) : '';
	                  $formDisplay = isset($split_display[1]) ? explode(',"', $split_display[1])[0] : '';
                 	  $split_id = ! empty($string) ? explode('formId":"', $string) : '';
	                  $id = isset($split_id[1]) ? explode('"', $split_id[1])[0] : '';
	                  if (in_array($id, $used_ids) ) { 		           
		                unset( $sidebars_widgets[$widget_area][$pos] ); 
	                  }
                    }
                    else {
                      if ( strpos($string,'[simpleform') !== false ) { 
	                    $split_shortcode = ! empty($string) ? explode('[simpleform', $string) : '';
	                    $split_id = isset($split_shortcode[1]) ? explode(']', $split_shortcode[1])[0] : '';
	                    $id = empty($split_id) ? '1' : filter_var($split_id, FILTER_SANITIZE_NUMBER_INT);
	                    if (in_array($id, $used_ids) ) { 		           
		                  unset( $sidebars_widgets[$widget_area][$pos] ); 
	                    }
                      }    
 		            }
                  }
		        }
		     }
           }
         }
       }

       return $sidebars_widgets;
       
    }
     
	/**
	 * Extract the SimpleForm block ID from found blocks (To be used when editing a page)
     *
	 * @since    2.0
	 */
	 
    public function get_sform_block_ids($content) {
	    
	  $ids = array();
	  $blocks = parse_blocks( $content );
	 
	  foreach ( $blocks as $block ) {
      
      if ($block['blockName'] === 'simpleform/form-selector' && $block['attrs']['formId'] != '' ) {
	    $id = $block['attrs']['formId'];  
	    $ids[] = $block['attrs']['formId'];
      }
      if ($block['innerBlocks']) { 
        foreach ($block['innerBlocks'] as $innerblock) {
          if ($innerblock['blockName'] === 'simpleform/form-selector' && $innerblock['attrs']['formId'] != '' ) {
	          $ids[] = $innerblock['attrs']['formId'];
          }
          if ($innerblock['innerBlocks']) {
            foreach ($innerblock['innerBlocks'] as $innerblock2) {
              if ($innerblock2['blockName'] === 'simpleform/form-selector' && $innerblock2['attrs']['formId'] != '' ) {
	              $ids[] = $innerblock2['attrs']['formId'];
              }
              if ($innerblock2['innerBlocks']) {
                foreach ($innerblock2['innerBlocks'] as $innerblock3) {
                  if ($innerblock3['blockName'] === 'simpleform/form-selector' && $innerblock3['attrs']['formId'] != '' ) {
	                  $ids[] = $innerblock3['attrs']['formId'];
                  }
                  if ($innerblock3['innerBlocks']) {
                    foreach ($innerblock3['innerBlocks'] as $innerblock4) {
                      if ($innerblock4['blockName'] === 'simpleform/form-selector' && $innerblock4['attrs']['formId'] != '' ) {
	                      $ids[] = $innerblock4['attrs']['formId'];
                      }
                      if ($innerblock4['innerBlocks']) {
                        foreach ($innerblock4['innerBlocks'] as $innerblock5) {
                          if ($innerblock5['blockName'] === 'simpleform/form-selector' && $innerblock5['attrs']['formId'] != '' ) {
	                          $ids[] = $innerblock5['attrs']['formId'];
                          }
                          if ($innerblock5['innerBlocks']) {
                            foreach ($innerblock5['innerBlocks'] as $innerblock6) {
                              if ($innerblock6['blockName'] === 'simpleform/form-selector' && $innerblock6['attrs']['formId'] != '' ) {
	                            $ids[] = $innerblock6['attrs']['formId'];
                              }
                            }
                          }
                        }
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
      
      }
      
      return $ids;
      
    }
	
}