<?php

class Physician_Network_Sync_Admin_Manager  {

    public $child_site_option;
    public $domain_allow_list;
    public $secret_key;
    public $last_update_date;
    public $sync_interval;

    public function __construct() {
        // Constructor is empty, form handling is hooked into admin_init.
    }

    public function handle_form_submissions() {
        if ( ! isset( $_POST['action'] ) || ! isset( $_GET['page'] ) || $_GET['page'] !== 'koc-physician-network-sync' ) {
            return;
        }

        

        if ( $_POST['action'] == 'save_koc_physician_sync_settings' ) {
            
            $this->save_settings();
        } else {
            $this->proccess_dynamic_post_button();
        }
    }

    public function load_settings() {
        $this->child_site_option = get_option('koc_physician_sync_is_child_site', false);
        $this->domain_allow_list = get_option('koc_physician_sync_domain_allow_list', array());
        $this->secret_key = get_option('koc_physician_sync_secret_key', '');
        $this->last_update_date = get_option('koc_physician_sync_last_update_date', '');
        $this->sync_interval = get_option('koc_physician_sync_interval', '8 hours');
    }

public function dynamic_post_button($action, $class_function, $nonce_label, $button_text){ ?>
   <form method="post" action="/wp-admin/admin.php?page=koc-physician-network-sync">
        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>" />
        <input type="hidden" name="class_function" value="<?php echo esc_attr($class_function); ?>" />
        <?php wp_nonce_field("{$nonce_label}_action", "{$nonce_label}_nonce"); ?>
        <input type="submit" value="<?php echo esc_attr($button_text); ?>" class="button button-primary" />
 </form>
<?php 

}


public function dynamic_input_field($name, $label, $type='text', $default_value=''){ ?>
    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
    <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($default_value); ?>" />
<?php
}

public function proccess_dynamic_post_button(){
    if ( ! isset( $_POST['action'] ) ) {
        return;
    }

    $action = $_POST['action'];
    $class_function = $_POST['class_function'];
    $nonce_label = $_POST['action'];

    if ( isset( $_POST['action'] ) 
        && isset( $_POST['class_function'] ) 
    ) {
        // Verify nonce for security
        if ( isset( $_POST["{$nonce_label}_nonce"] ) && wp_verify_nonce( $_POST["{$nonce_label}_nonce"], "{$nonce_label}_action" ) ) {
            // Here you would add the logic to generate unique IDs
            $method_name = $_POST['class_function'];
            if(method_exists($this, $method_name)){
                $this->$method_name();
            }
             
            
            echo '<div class="notice notice-success is-dismissible"><p> 
Action completed successfully.
            </p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>Security check failed. Please try again.</p></div>';
        }
    }
}



    // This class can be expanded as needed
    public function generate_unique_ids_for_physician_post_type() {
    $query = new WP_Query( array(
        'post_type' => 'physician',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ) );

    $html = '';

    while ( $query->have_posts() ) {
        $query->the_post();
        $post_id = get_the_ID();
        $unique_id = 'physician-' . uniqid();
        update_post_meta( $post_id, 'unique_physician_id', $unique_id );

        $html .= '<p>Generated Unique ID for Physician Post ID ' . $post_id . ': ' . $unique_id . '</p>';
    }
    wp_reset_postdata();
}


    public function export_physician_data() {


        $query = new WP_Query( array(
            'post_type' => 'physician',
            'posts_per_page' => -1,
            'post_status' => 'any',
        ) );

        $physician_data = array();

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

        while ( $query->have_posts() ) {
            $query->the_post();
            $post_id = get_the_ID();
            
            $data = array(
                'post_id' => $post_id,
                'unique_physician_id' => get_post_meta( $post_id, 'unique_physician_id', true ),
                
                'post_title' => get_the_title(),
                'post_content' => get_the_content(),
            );

            foreach ( $fields_to_export as $field_name ) {
                $data[$field_name] = get_field( $field_name, $post_id );
            }

            $physician_data[] = $data;
        }
        wp_reset_postdata();

        ob_clean();

        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="physician_data.json"');
        echo json_encode( $physician_data, JSON_PRETTY_PRINT );
        exit;
    }

    // Generate (if not in options) and return copy of secret key
    public function generate_secret_key() {
        $secret_key = get_option( 'koc_physician_sync_secret_key' );
        if ( ! $secret_key ) {
            $secret_key = bin2hex( random_bytes( 16 ) );
            update_option( 'koc_physician_sync_secret_key', $secret_key );
        } 
        
    
        ?>

        <p>Generated Secret Key: <?php echo esc_html( $secret_key ); ?></p>
        
   <?php  }


 public function save_settings() {
    // Verify nonce
    if ( ! isset( $_POST['save_koc_physician_sync_settings_nonce'] ) || ! wp_verify_nonce( $_POST['save_koc_physician_sync_settings_nonce'], 'save_koc_physician_sync_settings_action' ) ) {
        add_settings_error('koc_sync_settings', 'nonce_fail', 'Security check failed. Settings not saved.', 'error');
        return;
    }

    // Save "Is Child Site" setting
    $is_child_site = isset( $_POST['is_child_site'] ) ? true : false;
    update_option( 'koc_physician_sync_is_child_site', $is_child_site );

    // Save Secret Key
    if ( isset( $_POST['secret_key'] ) && ! empty( $_POST['secret_key'] ) ) {
        $secret_key = sanitize_text_field( $_POST['secret_key'] );
        update_option( 'koc_physician_sync_secret_key', $secret_key );
    }

    // Save Domain Allow List if not a child site
    if ( ! $is_child_site && isset( $_POST['domain_allow_list'] ) ) {
        $domains = array_map( 'trim', explode( ',', sanitize_text_field( $_POST['domain_allow_list'] ) ) );
        update_option( 'koc_physician_sync_domain_allow_list', $domains );
    } else if ( $is_child_site ) {
        update_option( 'koc_physician_sync_domain_allow_list', array() );
    }

    // Save Last Update Date if not a child site
    if ( ! $is_child_site && isset( $_POST['last_update_date'] ) ) {
        $last_update_date = sanitize_text_field( $_POST['last_update_date'] );
        update_option( 'koc_physician_sync_last_update_date', $last_update_date );
    }

    // Save Sync Interval if not a child site
    if ( ! $is_child_site && isset( $_POST['sync_interval'] ) ) {
        $sync_interval = sanitize_text_field( $_POST['sync_interval'] );
        update_option( 'koc_physician_sync_interval', $sync_interval );
    }

    add_settings_error('koc_sync_settings', 'settings_saved', 'Settings saved successfully.', 'success');
}
}
?>