<?php
/**
 * The API functionality of the plugin.
 *
 * @link       https://moxcar.com
 * @since      1.0.0
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 */

/**
 * The API functionality of the plugin.
 *
 * Defines the API routes for the plugin.
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/includes
 * @author     Gino Peterson <gpeterson@moxcar.com>
 */
class Koc_Physician_Network_Sync_Api {

	/**
	 * The namespace for the API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $namespace    The namespace for the API.
	 */
	private $namespace;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->namespace = 'koc-sync/v1';
	}

	/**
	 * Register the routes for the objects of the controller.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/physicians', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_physicians' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );

		register_rest_route( $this->namespace, '/physicians/(?P<uuid>[a-zA-Z0-9-]+)', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'get_physician' ),
			'permission_callback' => array( $this, 'permissions_check' ),
		) );
	}

	/**
	 * Check if a given request has access to get items
	 *
	 * @param  WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function permissions_check( $request ) {
		$secret_key = get_option( 'koc_physician_sync_secret_key' ); // Get the stored secret key
		$request_secret = $request->get_header( 'x_auth_secret' ); // Get the secret key from request header 

	 

		if ( ! $secret_key || ! $request_secret || ! hash_equals( $secret_key, $request_secret ) ) { // Use hash_equals to prevent timing attacks
			return new WP_Error( 'rest_forbidden', esc_html__( 'Invalid secret key.', 'koc-physician-network-sync' ), array( 'status' => 401 ) );
		}

		$domain_allow_list = get_option( 'koc_physician_sync_domain_allow_list', array() ); // Get the allowed domains
		$domain_allow_list[] = parse_url(get_site_url(), PHP_URL_HOST); // Always allow own domain

		$referrer = $request->get_header( 'referer' ); // Get the referrer header
		$referrer_host = parse_url( $referrer, PHP_URL_HOST ); // Parse the host from the referrer

		if ( ! in_array( $referrer_host, $domain_allow_list ) ) { // Check if the referrer host is in the allowed list
			return new WP_Error( 'rest_forbidden', esc_html__( 'Domain not allowed.', 'koc-physician-network-sync' ), array( 'status' => 403 ) );
		}

		return true;
	}

	/**
	 * Get a collection of physicians
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_physicians( $request ) {
		$params = $request->get_params();
		$per_page = isset( $params['per_page'] ) ? (int) $params['per_page'] : -1;
		$page = isset( $params['page'] ) ? (int) $params['page'] : 1;

		$args = array(
			'post_type'      => 'physician',
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'post_status'    => 'publish',
		);

		if ( isset( $params['updated_since'] ) ) {
			$args['date_query'] = array(
				array(
					'column' => 'post_modified_gmt',
					'after'  => $params['updated_since'],
				),
			);
		}

		$query = new WP_Query( $args );
		$physician_data = array();

		while ( $query->have_posts() ) {
			$query->the_post();
			$physician_data[] = $this->get_physician_data( get_the_ID() );
		}
		wp_reset_postdata();

		$response = new WP_REST_Response( array( 'data' => $physician_data ) );
		$response->header( 'X-WP-Total', $query->found_posts );
		$response->header( 'X-WP-TotalPages', $query->max_num_pages );

		return $response;
	}

	/**
	 * Get one physician from the collection
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_physician( $request ) {
		$uuid = $request['uuid'];

		$args = array(
			'post_type'  => 'physician',
			'meta_query' => array(
				array(
					'key'   => 'unique_physician_id',
					'value' => $uuid,
				),
			),
		);

		$query = new WP_Query( $args );

		if ( ! $query->have_posts() ) {
			return new WP_Error( 'not_found', esc_html__( 'Physician not found.', 'koc-physician-network-sync' ), array( 'status' => 404 ) );
		}

		$query->the_post();
		$physician_data = $this->get_physician_data( get_the_ID() );
		wp_reset_postdata();

		return new WP_REST_Response( array( 'data' => $physician_data ) );
	}

	/**
	 * Get physician data for the API response
	 *
	 * @param int $post_id The post ID.
	 * @return array
	 */
	private function get_physician_data( $post_id ) {
		$fields_to_export = array(
            'personal_information', 'last_name', 'title', 'social_climb_id', 'specialty',
            'secondary_specialty', 'education', 'intership', 'residency', 'felloswhip',
            'began_practice_at_koc', 'board_certification', 'professional_distinctions',
            'orthopaedic_specialty', 'office_info', 'office_location', 'appointments_number',
            'administrative_assistant', 'nurse', 'professional_interests', 'teaching_appointments',
            'medical_associations', 'educational_links', 'patient_forms', 'procedures_performed',
            'conditions_treated', 'schedule_an_appointment_link', 'appointment_button_text',
            'affiliation', 'affiliation_link', 'Surgery', 'api_name', 'uos_embed'
        );

		$data = array(
			'post_id'             => $post_id,
			'unique_physician_id' => get_post_meta( $post_id, 'unique_physician_id', true ),
			'post_title'          => get_the_title( $post_id ),
			'post_content'        => get_the_content( null, false, $post_id ),
			'updated_at'          => get_the_modified_time( 'c', $post_id ),
		);

		foreach ( $fields_to_export as $field_name ) {
			$data[$field_name] = get_field( $field_name, $post_id );
		}

		return $data;
	}
}
