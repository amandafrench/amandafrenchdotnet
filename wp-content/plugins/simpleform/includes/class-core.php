<?php

/**
 * The core plugin class
 *
 * @since      1.0
 */

class SimpleForm {

	/**
	 * The loader responsible for maintaining and registering all hooks.
	 *
	 * @since    1.0
	 */
	 
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0
	 */

	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0
	 */

	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0
	 */

	public function __construct() {
		
		if ( defined( 'SIMPLEFORM_VERSION' ) ) { $this->version = SIMPLEFORM_VERSION; } 
		else { $this->version = '2.0.8'; }
		$this->plugin_name = 'simpleform';
		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		if ( version_compare(get_bloginfo('version'),'5.6', '>=') ) { 	 
		$this->define_block_hooks();
		}

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0
	 */
	 
	private function load_dependencies() {

		// The class responsible for orchestrating the actions and filters of the core plugin.
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-loader.php';
		// The class responsible for defining all actions that occur in the admin area.		 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-admin.php';
		// The class responsible for defining all actions that occur in the public-facing side of the site.		 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-public.php';
		// The class responsible for defining the widget
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-widget.php';	
		// The class responsible for defining the block
		if ( version_compare(get_bloginfo('version'),'5.6', '>=') ) { 	 
		// require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-block.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/block/class-block.php';
		}
		// The class responsible for defining the general utilities	 
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-utilities.php';
		// The base class for displaying a list of forms in an ajaxified HTML table.
        // if ( ! class_exists( 'WP_List_Table' ) ) {
	    // require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
        // }
        // The customized forms class that extends the base class
        // require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-forms.php';

		$this->loader = new SimpleForm_Loader();

	}

	/**
	 * Register all hooks related to the admin area functionality of the plugin.
	 *
	 * @since    1.0
	 */
	
	private function define_admin_hooks() {

		$plugin_admin = new SimpleForm_Admin( $this->get_plugin_name(), $this->get_version() );

		// Register the stylesheets for the admin area
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles', 1 );
		// Register the scripts for the admin area
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts', 1 );
		// Register the administration menu for this plugin into the WordPress Dashboard menu
	    $this->loader->add_action('admin_menu', $plugin_admin, 'sform_admin_menu' );
		// Register callback for returning of the shortcode properties
		$this->loader->add_filter( 'sform_form', $plugin_admin, 'sform_form_filter' );
	    // Register ajax callback for shortcode attributes editing
	    $this->loader->add_action('wp_ajax_shortcode_costruction', $plugin_admin, 'shortcode_costruction');
	    // Register ajax callback for settings editing
	    $this->loader->add_action('wp_ajax_sform_edit_options', $plugin_admin, 'sform_edit_options');
 	    // Register callback for enabling smtp server
 		$this->loader->add_action( 'check_smtp', $plugin_admin, 'check_smtp_server' );
	    // Filter the tables to drop when a site into a network is deleted
        $this->loader->add_filter( 'wpmu_drop_tables', $plugin_admin, 'on_delete_blog' );
	    // Register ajax callback for include privacy page into label
	    $this->loader->add_action('wp_ajax_setting_privacy', $plugin_admin, 'setting_privacy');
        // Display an update message if there's a new release waiting
	    $this->loader->add_filter('sform_update', $plugin_admin, 'update_message');
		// Display additional action links in the plugins list table  
        $this->loader->add_filter( 'plugin_action_links', $plugin_admin, 'plugin_links', 10, 2 );
		// Add support links in the plugin meta row	    
		$this->loader->add_filter( 'plugin_row_meta', $plugin_admin, 'plugin_meta', 10, 2 );
	    if ( is_admin() ) {
		// Fallback for database table updating if plugin is already active 
		$this->loader->add_action( 'plugins_loaded', $plugin_admin, 'db_version_check' );
        }	
        // Retrieve pages list containing the SimpleForm shortcode
        $this->loader->add_action( 'save_post', $plugin_admin, 'sform_pages_list', 10, 2 ); 
	    // Clean up the post content of any non-existent and redundant form
	    // $this->loader->add_filter('content_save_pre', $plugin_admin, 'clean_up_post_content', 10, 1 );
	    // Register ajax callback for change admin color scheme
	    $this->loader->add_action('wp_ajax_admin_color_scheme', $plugin_admin, 'admin_color_scheme');
        // Display an admin notice in case there are any SimpleForm widgets running on WordPress 5.8 
	    if ( version_compare(get_bloginfo('version'),'5.8', '>=') ) { 	 
	    $this->loader->add_action('admin_notices', $plugin_admin, 'general_admin_notice');
        }	    
	    // Register ajax callback for form deleting
	    $this->loader->add_action('wp_ajax_sform_delete_form', $plugin_admin, 'sform_delete_form');
        // Save screen options
		// $this->loader->add_filter( 'set-screen-option', $plugin_admin, 'forms_screen_option', 10, 3 );		
		// Register a post type for change the pagination in Screen Options tab
		// $this->loader->add_action( 'init', $plugin_admin, 'form_post_type' );
		// Show the parent menu active for hidden sub-menu item
		// $this->loader->add_filter( 'parent_file', $plugin_admin, 'contacts_menu_open', 1, 2 );

	}

	/**
	 * Register all hooks related to the public-facing functionality of the plugin.
	 *
	 * @since    1.0
	 */
	
	private function define_public_hooks() {

		$plugin_public = new SimpleForm_Public( $this->get_plugin_name(), $this->get_version() );

		// Register the stylesheets for the public-facing side of the site
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		// Register the scripts for the public-facing side of the site
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        // Modify header information
		$this->loader->add_action( 'template_redirect', $plugin_public, 'ob_start_cache' );	
		// Register shortcode via loader
        $this->loader->add_shortcode( "simpleform", $plugin_public, "sform_shortcode" );
	    // Register ajax callback for submitting form
	    $this->loader->add_action('wp_ajax_formdata_ajax_processing', $plugin_public, 'formdata_ajax_processing');
	    $this->loader->add_action('wp_ajax_nopriv_formdata_ajax_processing', $plugin_public, 'formdata_ajax_processing'); 
	    // Register callback for form data validation
		$this->loader->add_filter( 'sform_validation', $plugin_public, 'formdata_validation', 12, 1 ); 
	    // Register callback for form data processing
		$this->loader->add_filter( 'sform_send_email', $plugin_public, 'formdata_processing', 12, 2 );

	}

	/**
	 * Register all hooks related to the block functionality of the plugin.
	 *
	 * @since    1.0
	 */
	
	private function define_block_hooks() {

		$plugin_block = new SimpleForm_Block( $this->get_plugin_name(), $this->get_version() );

		// Register the block
		$this->loader->add_action( 'init', $plugin_block, 'register_block' );
		// Clean up the widget areas of any non-existent and redundant form
		$this->loader->add_action( 'widgets_init', $plugin_block, 'clean_up_widget_areas' );
	    // Hide widget blocks if the form already appears in the post content
		$this->loader->add_filter( 'sidebars_widgets', $plugin_block, 'hide_widgets' );

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0
	 */
	 
	public function run() {
		
		$this->loader->run();
		
	}

	/**
	 * Retrieve the name of the plugin.
	 *
	 * @since     1.0
	 */
	 
	public function get_plugin_name() {
		
		return $this->plugin_name;
		
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0
	 */
	 
	public function get_loader() {
		
		return $this->loader;
		
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0
	 */
	 
	public function get_version() {
		
		return $this->version;
		
	}

}