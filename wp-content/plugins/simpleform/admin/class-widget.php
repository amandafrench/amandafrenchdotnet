<?php
	
 /**
 * Defines the widget of the plugin.
 *
 * @since      1.10
 */

class SimpleForm_Widget extends WP_Widget {

	/**
	 * Widget constructor
	 *
	 * @since    1.10
	 */
	 
	public function __construct() {
		
		$widget_options = array ('classname' => __FUNCTION__, 'description' => __( 'Displays a contact form with SimpleForm', 'simpleform' ) );
		parent::__construct('sform_widget', __( 'SimpleForm Contact Form', 'simpleform'), $widget_options );
    	add_filter ('pre_update_option_sidebars_widgets',array($this, 'update_sform_shortcodes'), 10, 1);
    	add_action('delete_widget', array($this, 'cleanup_sform_shortcodes'), 10, 3 );    	
	    add_filter('get_sform_shortcode_id', array($this, 'get_sform_shortcode_id') );
	    add_filter( 'widget_types_to_hide_from_legacy_widget_block', array( $this, 'hide_sform_widget' ));

	}
	
	/**
	 * Output the widget admin form
	 *
	 * @since    1.10
	 */
	
	public function form( $instance ) {
				
		$title   = ! empty( $instance['sform_widget_title'] ) ? $instance['sform_widget_title'] : '';
        $audience = ! empty( $instance['sform_widget_audience'] ) ? $instance['sform_widget_audience'] : 'all';
        $role = ! empty( $instance['sform_widget_role'] ) ? $instance['sform_widget_role'] : 'any';
        $shortcode_id = ! empty( $instance['shortcode_id'] ) ? $instance['shortcode_id'] : apply_filters( 'get_sform_shortcode_id', $this->number );
		$widget_id = ! empty( $instance['sform_widget_id'] ) ? $instance['sform_widget_id'] : '';
		$class   = ! empty( $instance['sform_widget_class'] ) ? $instance['sform_widget_class'] : '';
        $visibility = ! empty( $instance['sform_widget_visibility'] ) ? $instance['sform_widget_visibility'] : 'all';
        $hidden_pages = ! empty( $instance['sform_widget_hidden_pages'] ) ? $instance['sform_widget_hidden_pages'] : '';        
        $visible_pages = ! empty( $instance['sform_widget_visible_pages'] ) ? $instance['sform_widget_visible_pages'] : '';
        $id = is_int($this->number) ? $this->number : '0';       
        $util = new SimpleForm_Util();
        $placeholder = $id . ':widget';     
        $pages = $util->form_pages($placeholder);
        $hidden_pages_array = ! empty($hidden_pages) ? explode(',',$hidden_pages) : array();
        $visible_pages_array = ! empty($visible_pages) ? explode(',',$visible_pages) : array();
	    $pages_list = '';
	    $counter = 0;
	    
		switch ($visibility) {
          case 'all':
	      if ( ! empty($pages) ) { $total = count($pages); foreach ( $pages as $page ) { ++$counter; $separator = $counter < $total ? ',' : '' ; $pages_list .= '<a href="' . get_page_link($page) . '" target="_blank" style="text-decoration: none; color:#b12938;">'. $page .'</a>' . $separator; } }
          break;
          case 'visible':
          if ( ! empty($pages) && ! empty(array_intersect($pages, $visible_pages_array) ) ) { $total = count(array_intersect($pages, $visible_pages_array)); foreach ( array_intersect($pages, $visible_pages_array) as $page ) { ++$counter; $separator = $counter < $total ? ',' : '' ; $pages_list .= '<a href="' . get_page_link($page) . '" target="_blank" style="text-decoration: none; color:#b12938;">'. $page .'</a>' . $separator; } }
          break;  
          case 'hidden':
          if ( ! empty($pages) ) { foreach ( $pages as $page ) { if ( ! in_array($page,$hidden_pages_array) ) { ++$counter; $pages_list .= '<a href="' . get_page_link($page) . '" target="_blank" style="text-decoration: none; color:#b12938;">'. $page .'</a>,'; } } $total = $counter; if (!empty($pages_list)) { $pages_list = substr($pages_list, 0, -1); } }
        }
		    
		if ( ! empty($pages_list) )  {  
	       $alert = '<div>' . _n( 'The widget cannot be displayed on this page', 'The widget cannot be displayed on these pages', $total, 'simpleform' ) . ': ' . $pages_list . '<br>' . __( 'You may display the form only once to make it work properly.', 'simpleform' ) . '</div>';
        }
        else {
	       $alert = '';	        
        }

        echo '<div class="widget-alert">'.$alert.'</div>';
   	    ?>
						
		<p><label for="<?php echo $this->get_field_id( 'sform_widget_title' ); ?>"><?php _e( 'Title:', 'simpleform' ); ?></label><input type="text" name="<?php echo $this->get_field_name( 'sform_widget_title' ); ?>" id="<?php echo $this->get_field_id('sform_widget_title') ?>" class="widefat" value="<?php echo esc_attr( $title ); ?>"></p>
		
        <p><label for="<?php echo $this->get_field_id( 'sform_widget_audience' ); ?>"><?php _e( 'Show for:', 'simpleform' ) ?></label><select name="<?php echo $this->get_field_name( 'sform_widget_audience' ); ?>" id="<?php echo $this->get_field_id('sform_widget_audience') ?>" class="widefat sform-target" field="<?php echo $this->number;?>" ><option value="all" <?php selected( $audience, 'all') ?>><?php _e( 'Everyone', 'simpleform' ) ?></option><option value="out" <?php echo selected( $audience, 'out' ) ?>><?php _e( 'Logged-out users', 'simpleform' ) ?></option><option value="in" <?php echo selected( $audience, 'in' ) ?>><?php _e( 'Logged-in users', 'simpleform' ) ?></option></select></p>
        
        <p id="usertype" class="role-<?php echo $this->number; if ( $audience !='in' ) {echo ' unseen'; } ?>"><label for="<?php echo $this->get_field_id( 'sform_widget_role' ); ?>"><?php _e( 'Role', 'simpleform' ) ?>:</label><select name="<?php echo $this->get_field_name( 'sform_widget_role' ); ?>" id="<?php echo $this->get_field_id( 'sform_widget_role' ); ?>" class="widefat"><option value="any" <?php selected( $role, 'any') ?>><?php _e('Any','simpleform') ?></option><?php wp_dropdown_roles($role) ?></select></p>    
                
        <?php
        $settings = get_option('sform_settings');
        $color = ! empty( $settings['admin_color'] ) ? esc_attr($settings['admin_color']) : 'default';
        $widget_options = ! empty( $settings['widget'] ) ? esc_attr($settings['widget']) : 'true';
        if ( $widget_options == 'true' ) {
        ?>   
        
        <p id="visibility" class="<?php if ( $visibility =='all' ) {echo 'visibility'; } ?>">
        <label for="<?php echo $this->get_field_id( 'sform_widget_visibility' ) ?>"><?php _e( 'Show/Hide on:', 'simpleform' ) ?></label>
    	<select name="<?php echo $this->get_field_name( 'sform_widget_visibility' ) ?>" id="<?php echo $this->get_field_id( 'sform_widget_visibility' ) ?>" class="widefat sfwidget" box="visibility-<?php echo $this->number; ?>">
        <option value="all" <?php selected( $visibility, 'all') ?>><?php _e( 'Show anywhere', 'simpleform' ) ?></option>
        <option value="hidden" <?php selected( $visibility, 'hidden') ?>><?php _e( 'Hide on selected', 'simpleform' ) ?></option>
        <option value="visible" <?php selected( $visibility, 'visible') ?>><?php _e( 'Show on selected', 'simpleform' ) ?></option>
        </select>
        </p>    
    
 		<p id="visibility-notes" class="<?php if ( $visibility !='all' ) {echo ' unseen';}?>"><?php _e( 'Pages including shortcode or block are excluded by default', 'simpleform' ) ?></p>
   
		<div id="sform-widget-hidden-pages" class="widget-pages visibility-<?php echo $this->number; if ( $visibility !='hidden' ) {echo ' unseen';}?>">
		<p class="first">
		<label for="<?php echo $this->get_field_id( 'sform_widget_hidden_pages' ) ?>"><?php _e( 'Selected pages', 'simpleform' ) ?>:</label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'sform_widget_hidden_pages' ); ?>" name="<?php echo $this->get_field_name( 'sform_widget_hidden_pages' ) ?>" type="text" value="<?php echo $hidden_pages; ?>" placeholder="<?php esc_attr_e('List includes pages with a shortcode or block','simpleform') ?>"></p>
		<p class="sform-widget-description"><?php _e( 'Use a comma-separated list of IDs for more than one page', 'simpleform' ) ?></p>
		</div>

		<div id="sform-widget-visible-pages" class="widget-pages visibility-<?php echo $this->number; if ( $visibility !='visible' ) {echo ' unseen';}?>">
		<p class="first">
		<label for="<?php echo $this->get_field_id( 'sform_widget_visible_pages' ); ?>"><?php _e( 'Selected pages', 'simpleform' ) ?>:</label>
		<input type="text" name="<?php echo $this->get_field_name('sform_widget_visible_pages') ?>" id="<?php echo $this->get_field_id( 'sform_widget_visible_pages' ) ?>" class="widefat" value="<?php echo $visible_pages ?>" placeholder="<?php esc_attr_e('List excludes pages with a shortcode or block','simpleform') ?>"></p>
		<p class="sform-widget-description"><?php _e( 'Use a comma-separated list of IDs for more than one page', 'simpleform' ) ?></p>
		</div>
        
        <?php
        }        
        ?>
       
       <div class="sform-widget-boxes">
        <p><b><?php _e( 'Add CSS selectors to customize the widget:', 'simpleform' ) ?></b></p>
        
        <p><label for="<?php echo $this->get_field_id( 'sform_widget_id' ) ?>"><?php _e( 'Custom ID:', 'simpleform' ); ?></label><input type="text" name="<?php echo $this->get_field_name( 'sform_widget_id' ) ?>" id="<?php echo $this->get_field_id( 'sform_widget_id' ) ?>" class="widefat" value="<?php echo esc_attr( $widget_id ) ?>"></p>

		<p class="last"><label for="<?php echo $this->get_field_id( 'sform_widget_class' ) ?>"><?php _e( 'Custom Class:', 'simpleform' ) ?></label><input type="text" name="<?php echo $this->get_field_name( 'sform_widget_class' ) ?>" id="<?php echo $this->get_field_id( 'sform_widget_class' ) ?>"   class="widefat" value="<?php echo esc_attr( $class ) ?>"></p>
		<p class="sform-widget-notes"><?php _e( 'Separate each class with a space', 'simpleform' ) ?></p></div>
        		
		<?php if ( $shortcode_id) { ?>
	    
	  	<div class="sform-widget-boxes buttons"><p><b><?php _e( 'Change how the contact form is displayed and works:', 'simpleform' ) ?></b></p><p id="widget-buttons"><a href="<?php echo admin_url('admin.php?page=sform-editor') . '&form='. $shortcode_id; ?>" target="_blank"><span id="widget-button-editor" class="wp-core-ui button <?php echo $color ?>"><?php _e( 'Open Editor', 'simpleform' ) ?></span></a><a href="<?php echo admin_url('admin.php?page=sform-settings') . '&form='. $shortcode_id; ?>" target="_blank"><span id="widget-button-settings" class="wp-core-ui button <?php echo $color ?>"><?php _e( 'Open Settings', 'simpleform' ) ?></span></a></p></div> 
	  	
	  	<?php
     	}
				 
	}
	
	/**
	 * Update the widget settings
	 *
	 * @since    1.10
	 */
	 
	public function update( $new_instance, $old_instance ) {
		
        $instance = array();
        $instance['sform_widget_title'] = isset($new_instance['sform_widget_title']) ? sanitize_text_field($new_instance['sform_widget_title']) : '';
        $instance['sform_widget_audience'] = isset($new_instance['sform_widget_audience']) && in_array($new_instance['sform_widget_audience'], array('all', 'out', 'in')) ? $new_instance['sform_widget_audience'] : 'all';
        global $wp_roles;
	    $roles = $wp_roles->roles;
	    $all_roles = array('any');
	    $role_values = array_merge(array_keys($roles),$all_roles);
        $instance['sform_widget_role'] = $instance['sform_widget_audience'] == 'in' && isset($new_instance['sform_widget_role']) && in_array($new_instance['sform_widget_role'], $role_values) ? $new_instance['sform_widget_role'] : 'any';

        global $wpdb;
        $table_name = $wpdb->prefix . 'sform_shortcodes';
        $sql = "SELECT id FROM `$table_name` WHERE widget = %s";
        $form_id = $wpdb->get_var( $wpdb->prepare( $sql, $this->number ) ); 
        if ( $form_id  ) {
        $instance['shortcode_id'] = $form_id;
        $form_attributes = get_option('sform_'.$form_id.'_attributes');
		$form_attributes['show_for'] = $instance['sform_widget_audience'];
		$form_attributes['user_role'] = $instance['sform_widget_role'];
        update_option('sform_'.$form_id.'_attributes', $form_attributes);
        }
        
        $settings = get_option('sform_settings');
        $widget_options = ! empty( $settings['widget'] ) ? esc_attr($settings['widget']) : 'true';       
        if ( $widget_options == 'true' ) {
        $instance['sform_widget_visibility'] = isset($new_instance['sform_widget_visibility']) && in_array($new_instance['sform_widget_visibility'], array('all', 'hidden', 'visible')) ? $new_instance['sform_widget_visibility'] : 'all';
        
        $checked_hidden_pages = preg_match('/^[0-9, ]+$/', $new_instance['sform_widget_hidden_pages']) ? str_replace(' ', '', $new_instance['sform_widget_hidden_pages']) : '';
        if ( ! empty($checked_hidden_pages) ) {    
        // Remove first and last comma, empty id and fake page id
        $hidden_pages_array = explode(',',$checked_hidden_pages);
          foreach ($hidden_pages_array as $key => $post) { 
	        if ( empty($post) || get_post_status($post) === FALSE ) { 
            unset($hidden_pages_array[$key]);
		    }
		  }
 		$instance_hidden_pages = ! empty($hidden_pages_array) ? implode(",", array_unique($hidden_pages_array)) : '';
       }
        else { 
        $instance_hidden_pages = '';
        }
        
        $instance['sform_widget_hidden_pages'] = $instance_hidden_pages;
        
        $checked_visible_pages = preg_match('/^[0-9, ]+$/', $new_instance['sform_widget_visible_pages']) ? str_replace(' ', '', $new_instance['sform_widget_visible_pages']) : '';
        if ( ! empty($checked_visible_pages) ) {    
        // Remove first and last comma, empty id and fake page id
        $visible_pages_array = explode(',',$checked_visible_pages);
        foreach ($visible_pages_array as $key => $post) { 
	      if ( empty($post) || get_post_status($post) === FALSE ) { 
            unset($visible_pages_array[$key]);
		    }
		 }		                      
		 $instance_visible_pages = ! empty($visible_pages_array) ? implode(",", array_unique($visible_pages_array)) : '';
		} 
        else { 
          $instance_visible_pages = '';
        }

        $instance['sform_widget_visible_pages'] = $instance_visible_pages;
        }
        
        $instance['sform_widget_id'] = isset( $new_instance['sform_widget_id'] ) ? sanitize_text_field($new_instance['sform_widget_id']) : '';
        $instance['sform_widget_class'] = isset( $new_instance['sform_widget_class'] ) ? sanitize_text_field($new_instance['sform_widget_class']) : '';
 
		return $instance;
		
	}	
	
	/**
	 * Display the widget on the site
	 *
	 * @since    1.10
	 */
	
	public function widget( $args, $instance ) {
		
        $title = isset( $instance['sform_widget_title'] ) ? $instance['sform_widget_title'] : '';
        $widget_audience = isset( $instance['sform_widget_audience']) ? $instance['sform_widget_audience'] : 'all';
        $role = isset( $instance['sform_widget_role'] ) ? $instance['sform_widget_role'] : 'any';
        $id = isset( $instance['sform_widget_id'] ) ? $instance['sform_widget_id'] : '';
        $class = isset( $instance['sform_widget_class'] ) ? $instance['sform_widget_class'] : '';
        $shortcode_id = isset( $instance['shortcode_id'] ) ? $instance['shortcode_id'] : '';
	    global $wpdb;
        $table_name = "{$wpdb->prefix}sform_shortcodes";
        
        if ( empty($shortcode_id) ) {
        $widget_id = $this->number;
        $sql = "SELECT id FROM `$table_name` WHERE widget = %s";
        $shortcode_id = $wpdb->get_var( $wpdb->prepare( $sql, $widget_id ) ); 
        }
        $settings = get_option("sform_{$shortcode_id}_settings") != false ? get_option("sform_{$shortcode_id}_settings") : get_option("sform_settings");
        $widget_options = ! empty( $settings['widget'] ) ? esc_attr($settings['widget']) : 'true';
        $admin_limits = ! empty( $settings['admin_limits'] ) ? esc_attr($settings['admin_limits']) : 'false';        
        global $post;

        if ( ( current_user_can('manage_options') && $admin_limits != 'true' && $widget_audience == 'out' && is_user_logged_in() ) || ( !current_user_can('manage_options') && $widget_audience == 'out' && is_user_logged_in() ) || ( $widget_audience == 'in' && ! is_user_logged_in() ) )
        return;
        
        $current_user = wp_get_current_user();
        if ( ( current_user_can('manage_options') && $admin_limits != 'true' || !current_user_can('manage_options') ) && $widget_audience != 'all' && $role != 'any' && ! in_array( $role, (array) $current_user->roles ) )
        return;

        if ( $widget_options == 'true' ) {
	       
        $widget_visibility = isset( $instance['sform_widget_visibility']) ? $instance['sform_widget_visibility'] : 'all';          
        $hidden_pages = isset( $instance['sform_widget_hidden_pages'] ) ? explode(',',$instance['sform_widget_hidden_pages']) : array();        
        $visible_pages = isset( $instance['sform_widget_visible_pages'] ) ? explode(',',$instance['sform_widget_visible_pages']) : array();

        $util = new SimpleForm_Util();      
        $pages = $util->form_pages($shortcode_id);
        
        If ( ! empty($pages) ) {
	       if ( is_admin() || ( $widget_visibility == 'all' && in_array($post->ID,$pages) ) || ( $widget_visibility == 'hidden' && in_array($post->ID,$pages) && ! in_array($post->ID,$hidden_pages) ) || ( $widget_visibility == 'visible' && in_array($post->ID,$pages) && in_array($post->ID,$visible_pages) ) )
           return;
        }
        else {
           if ( is_admin() || ( $widget_visibility == 'hidden' && in_array($post->ID,$hidden_pages) ) || ( $widget_visibility == 'visible' && ! in_array($post->ID,$visible_pages) ) )
           return;
        }
                
       }
        
	   echo $args['before_widget'] . '<div id="'.$id.'" class="sforms-widget '.$class.'">';
		
	   if ( $title ) { echo $args['before_title'] .  $title . $args['after_title']; }
        
	   $shortcode = '[simpleform id="'.$shortcode_id.'"]';
	    
	   echo do_shortcode( $shortcode );
	   
	   echo '</div>' . $args['after_widget'];
		
       $cssfile = ! empty( $settings['stylesheet_file'] ) ? esc_attr($settings['stylesheet_file']) : 'false';
       $form_template = ! empty( $settings['form_template'] ) ? esc_attr($settings['form_template']) : 'default'; 
       $stylesheet = ! empty( $settings['stylesheet'] ) ? esc_attr($settings['stylesheet']) : 'false';

       if ( $stylesheet == 'false' ) { 	       
	          wp_enqueue_style( 'sform-public-style' );
       } else {
	          if( $cssfile == 'true' ) {
              wp_enqueue_style( 'sform-custom-style' );
	          }   
       }	 

       $ajax = ! empty( $settings['ajax_submission'] ) ? esc_attr($settings['ajax_submission']) : 'false'; 
       $javascript = ! empty( $settings['javascript'] ) ? esc_attr($settings['javascript']) : 'false';
       $ajax_error = ! empty( $settings['ajax_error'] ) ? stripslashes(esc_attr($settings['ajax_error'])) : __( 'Error occurred during AJAX request. Please contact support!', 'simpleform' );
       $outside_error = ! empty( $settings['outside_error'] ) ? esc_attr($settings['outside_error']) : 'bottom';
       $outside = $outside_error == 'top' || $outside_error == 'bottom' ? 'true' : 'false';

       wp_localize_script('sform_public_script', 'ajax_sform_processing', array('ajaxurl' => admin_url('admin-ajax.php'), 'ajax_error' => $ajax_error, 'outside' => $outside ));	
       wp_enqueue_script( 'sform_form_script');
       if( $ajax == 'true' ) {
         wp_enqueue_script( 'sform_public_script');
       }        
       if ( $javascript == 'true' ) { 
         if (is_child_theme() ) { 
	        wp_enqueue_script( 'sform-custom-script',  get_stylesheet_directory_uri() . '/simpleform/custom-script.js',  array( 'jquery' ), '', true );
         } else { 
	        wp_enqueue_script( 'sform-custom-script',  get_template_directory_uri() . '/simpleform/custom-script.js',  array( 'jquery' ), '', true );
         }
	   }
		
	}
	
	/**
	 * Add/Edit shortcode related to simpleform widget
	 *
	 * @since    1.10
	 */
	
	public function update_sform_shortcodes($sidebars_widgets) {
		
      foreach ( $sidebars_widgets as $sidebar => $widgets ) {
	    if ( is_array( $widgets ) ) {
		  foreach ( $widgets as $key => $widget_id ) {
			if ( strpos($widget_id, 'sform_widget-' ) !== false ) {
                 $id =  explode("sform_widget-", $widget_id)[1];
                 global $wp_registered_sidebars;
	             $widget_area = isset($wp_registered_sidebars[$sidebar]['name']) ? $wp_registered_sidebars[$sidebar]['name'] : ''; 	             
	             global $wpdb;
                 $table_name = $wpdb->prefix . 'sform_shortcodes';
                 $sql = "SELECT id FROM `$table_name` WHERE widget = %s";
                 $shortcode = $wpdb->get_var( $wpdb->prepare( $sql, $id ) ); 
                 // Check if exists the shortcode for this widget                   
                 if ( ! $shortcode ) {
                    $rows = $wpdb->get_row(" SHOW TABLE STATUS LIKE '$table_name' ");
                    $shortcode_id = $rows->Auto_increment;
                    $shortcode_name = 'simpleform id="'.$shortcode_id.'"';
 	                $search_name = '%'. __( 'Contact Form','simpleform') . '%'; 
                    $sql = $wpdb->prepare("SELECT name FROM $table_name WHERE name LIKE %s AND widget != 0", $search_name);
	                $names = $wpdb->get_col($sql);
 	                $number =array();
	                if ($names) {
		            foreach ( $names as $name ) {
			         $suffix = filter_var($name, FILTER_SANITIZE_NUMBER_INT);
			         if ($suffix) { array_push($number,$suffix); } else { array_push($number,'0'); }
			        }
			        }
			        $new_suffix = !empty($number)? max($number) + 1 : '';
			        $name_suffix = $new_suffix == 1 ? '2' : $new_suffix;
			        $form_name = __( 'Contact Form','simpleform') . ' ' . $name_suffix; 
                    $wpdb->insert($table_name, array('shortcode' => $shortcode_name, 'area' => $widget_area, 'name' => $form_name, 'widget' => $this->number ));
                    // Set shortcode id without saving widget (It allows to show setting buttons on reload)
                    $sform_widget = get_option('widget_sform_widget');
                    $sform_widget[$id]['shortcode_id'] = $shortcode_id;
                    update_option('widget_sform_widget', $sform_widget);
                    $default_attributes = get_option("sform_attributes");                   
		            $default_attributes['form_name'] = $form_name;
                    add_option("sform_{$shortcode_id}_attributes", $default_attributes);
                    $default_settings = get_option("sform_settings");                   
                    add_option("sform_{$shortcode_id}_settings", $default_settings);
                 }
                 else {
                    $sql = "SELECT area FROM `$table_name` WHERE widget = %s";
                    $area = $wpdb->get_var( $wpdb->prepare( $sql, $id ) ); 
                    if ( isset($area) && $area != $widget_area ) { 
                    $wpdb->update($table_name, array('area' => $widget_area), array('widget' => $id ));
                    }                    
                 }
            }
	      }
	    }
	  }
	      
      return $sidebars_widgets;

	}
	
	/**
	 * Delete the shortcode after a widget has been marked for deletion and update the widget option
	 *
	 * @since    1.10
	 */
	
	public function cleanup_sform_shortcodes( $widget_id, $sidebar_id, $id_base ) { 
    
      if ($id_base == 'sform_widget') { 
         $id =  explode("sform_widget-", $widget_id)[1];
	     global $wpdb;         
         $table_name = $wpdb->prefix . 'sform_shortcodes';
         $sql = "SELECT id FROM `$table_name` WHERE widget = %s";
         $form_id = $wpdb->get_var( $wpdb->prepare( $sql, $id ) ); 
         $wpdb->delete($table_name, array('widget' => $id ));
         $table_submissions = "{$wpdb->prefix}sform_submissions";
         // Use a new option to ask if the submissions should be saved and should be included in the main form or deleted
         $wpdb->update($table_submissions, array('form' => '1'), array( 'form' => $form_id));
         $attributes_option = 'sform_'.$form_id.'_attributes';
         $attributes = get_option($attributes_option);
         if ( $attributes != false ) { $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = '%s'", $attributes_option) ); }
         $settings_option = 'sform_'.$form_id.'_settings';
         $settings = get_option('sform_'.$form_id.'_settings');
         if ( $settings != false ) { $wpdb->query( $wpdb->prepare("DELETE FROM {$wpdb->options} WHERE option_name = '%s'", $settings_option) ); }
         
         // DELETE LAST MESSAGE TRANSIENT
         $sform_widget = get_option('widget_sform_widget');         
         unset($sform_widget[$id]);
         update_option('widget_sform_widget', $sform_widget);
      }
      
    }
    
	/**
	 * Get the shortcode id when the widget is activated for the first time
	 *
	 * @since    1.10.6
	 */
	
 	public function get_sform_shortcode_id() { 
            
      global $wpdb;
      $table_name = $wpdb->prefix . 'sform_shortcodes';
      $rows = $wpdb->get_row(" SHOW TABLE STATUS LIKE '$table_name' ");
      $shortcode_id = $rows->Auto_increment;

      return $shortcode_id;

    }
	/**
	 * Hide the simpleform widget from the "select widget" dropdown and from the block inserter.
	 *
	 * @since    2.0.3
	 */
	
	public function hide_sform_widget( $widget_types ) {
      
       $widget_types[] = 'sform_widget';
       
       return $widget_types;
    
    }
    	    
}

 /**
 * Register and load the widget.
 *
 * @since      1.10
 */

function register_sform_widget() {
	 
  register_widget( 'SimpleForm_Widget' );
	
}

add_action( 'widgets_init', 'register_sform_widget' );