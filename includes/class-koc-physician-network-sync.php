<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Koc_Physician_Network_Sync {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Koc_Physician_Network_Sync_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'KOC_PHYSICIAN_NETWORK_SYNC_VERSION' ) ) {
			$this->version = KOC_PHYSICIAN_NETWORK_SYNC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'koc-physician-network-sync';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
				$this->define_public_hooks();
				$this->define_acf_hooks();
				$this->define_api_hooks();
				$this->define_post_save_hooks();
				$this->define_cron_hooks();
		
			}

			private function define_cron_hooks() {
				$this->loader->add_filter( 'cron_schedules', $this, 'add_custom_cron_schedules' );
				$this->loader->add_action( 'koc_scheduled_queue_sync_event', $this, 'run_scheduled_queue_sync' );
			}

			public function add_custom_cron_schedules( $schedules ) {
				$schedules['one_hour'] = array(
					'interval' => 3600,
					'display'  => esc_html__( 'Every Hour' ),
				);
				$schedules['four_hours'] = array(
					'interval' => 14400,
					'display'  => esc_html__( 'Every Four Hours' ),
				);
				$schedules['eight_hours'] = array(
					'interval' => 28800,
					'display'  => esc_html__( 'Every Eight Hours' ),
				);
				return $schedules;
			}

			public function run_scheduled_queue_sync() {
				$is_child = get_option( 'koc_physician_sync_is_child_site', false );
				if ( ! $is_child ) return;

				$parent_url = get_option( 'koc_physician_sync_parent_url' );
		        $secret_key = get_option( 'koc_physician_sync_secret_key' );

		        if ( ! $parent_url || ! $secret_key ) return;

		        $api_url = trailingslashit( $parent_url ) . 'wp-json/koc-sync/v1/physicians/queue';

		        $response = wp_remote_get( $api_url, array(
		            'headers' => array(
		                'X-Auth-Secret' => $secret_key,
		                'Referer' => site_url(),
		            ),
		        ));

		        if ( is_wp_error( $response ) ) return;

		        $body = wp_remote_retrieve_body( $response );
		        $data = json_decode( $body, true );

		        if ( ! isset( $data['data'] ) ) return;

		        $physicians = $data['data'];

		        foreach ( $physicians as $physician_data ) {
		            $args = array(
		                'post_type' => 'physician',
		                'meta_query' => array(
		                    array(
		                        'key'   => 'unique_physician_id',
		                        'value' => $physician_data['unique_physician_id'],
		                    ),
		                ),
		                'posts_per_page' => 1,
		            );
		            $query = new WP_Query( $args );

		            if ( $query->have_posts() ) {
		                $query->the_post();
		                $post_id = get_the_ID();

		                wp_update_post( array(
		                    'ID'           => $post_id,
		                    'post_title'   => $physician_data['post_title'],
		                    'post_content' => $physician_data['post_content'],
		                ) );

		                foreach ( $physician_data as $key => $value ) {
		                    if ( strpos($key, 'post_') !== 0 && $key !== 'unique_physician_id' ) {
		                        update_field( $key, $value, $post_id );
		                    }
		                }
		            }
		            wp_reset_postdata();
		        }
			}

			/**
			 * Register hooks related to saving posts.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function define_post_save_hooks() {
				$this->loader->add_action( 'save_post_physician', $this, 'add_physician_to_sync_queue', 10, 2 );
			}

			/**
			 * Add a physician post ID to the sync queue.
			 *
			 * @since    1.0.0
			 * @param    int    $post_id    The ID of the post being saved.
			 * @param    WP_Post $post      The post object.
			 */
			public function add_physician_to_sync_queue( $post_id, $post ) {
			    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			        return;
			    }
			    if ( wp_is_post_revision( $post_id ) ) {
			        return;
			    }
			    if ( $post->post_status !== 'publish' ) {
			        return;
			    }

			    $queue = get_option( 'koc_sync_physician_queue', array() );
			    if ( ! in_array( $post_id, $queue ) ) {
			        $queue[] = $post_id;
			        update_option( 'koc_sync_physician_queue', $queue );
			    }
			}
		
			/**
			 * Load the required dependencies for this plugin.
			 *
			 * Include the following files that make up the plugin:
			 *
			 * - Koc_Physician_Network_Sync_Loader. Orchestrates the hooks of the plugin.
			 * - Koc_Physician_Network_Sync_i18n. Defines internationalization functionality.
			 * - Koc_Physician_Network_Sync_Admin. Defines all hooks for the admin area.
			 * - Koc_Physician_Network_Sync_Public. Defines all hooks for the public side of the site.
			 *
			 * Create an instance of the loader which will be used to register the hooks
			 * with WordPress.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function load_dependencies() {
		
				/**
				 * The class responsible for orchestrating the actions and filters of the
				 * core plugin.
				 */
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-koc-physician-network-sync-loader.php';
		
				/**
				 * The class responsible for defining internationalization functionality
				 * of the plugin.
				 */		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-koc-physician-network-sync-i18n.php';
		
				/**
				 * The class responsible for defining all actions that occur in the admin area.
				 */
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-koc-physician-network-sync-admin.php';
		
				/**
				 * The class responsible for defining all actions that occur in the public-facing
				 * side of the site.
				 */
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-koc-physician-network-sync-public.php';
		        // require includes/class-koc-physician-network-sync-source.php 
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-koc-physician-network-sync-source.php';

				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'utils/class-koc-physician-network-sync-admin-manager.php';
		
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-koc-physician-acf-manager.php';
		
				/**
				 * The class responsible for defining all API routes.
				 */
				require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-koc-physician-network-sync-api.php';
		
		
		
				$sync_source = new KOC_Physician_Network_Sync_Source();
		
		
				
		
				$this->loader = new Koc_Physician_Network_Sync_Loader();
		
				$this->loader->add_action('admin_menu', $sync_source, 'add_plugin_admin_menu');
		
			}
		
			/**
			 * Define the locale for this plugin for internationalization.
			 *
			 * Uses the Koc_Physician_Network_Sync_i18n class in order to set the domain and to register the hook
			 * with WordPress.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function set_locale() {
		
				$plugin_i18n = new Koc_Physician_Network_Sync_i18n();
		
				$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
		
			}
		
			/**
			 * Register all of the hooks related to the admin area functionality
			 * of the plugin.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function define_admin_hooks() {
		
				$plugin_admin = new Koc_Physician_Network_Sync_Admin( $this->get_plugin_name(), $this->get_version() );
				$admin_manager = new Physician_Network_Sync_Admin_Manager();

				$this->loader->add_action( 'admin_init', $admin_manager, 'handle_form_submissions' );
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
				$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		
			}
		
			/**
			 * Register all of the hooks related to the public-facing functionality
			 * of the plugin.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function define_public_hooks() {
		
				$plugin_public = new Koc_Physician_Network_Sync_Public( $this->get_plugin_name(), $this->get_version() );
		
				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
				$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
			}
		
			/**
			 * Register all of the hooks related to ACF functionality.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function define_acf_hooks() {
				$acf_manager = new KOC_Physician_ACF_Manager();
				$this->loader->add_action( 'acf/init', $acf_manager, 'register_field_groups' );
			}
		
			/**
			 * Register all of the hooks related to the API functionality.
			 *
			 * @since    1.0.0
			 * @access   private
			 */
			private function define_api_hooks() {
				$api = new Koc_Physician_Network_Sync_Api();
				$this->loader->add_action( 'rest_api_init', $api, 'register_routes' );
			}



	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Koc_Physician_Network_Sync_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
