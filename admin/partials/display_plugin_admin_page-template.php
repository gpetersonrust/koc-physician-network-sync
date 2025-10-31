
<?php

 $admin_manager = new Physician_Network_Sync_Admin_Manager();
 

class Physician_Network_Sync_Admin_Manager  {

    public function __construct() {
      if( isset($_POST['action']) && $_POST['action']  == 'save_koc_physician_sync_settings'){
        $this->save_settings();
      }
        
    }

public function dynamic_post_button($action, $class_function, $nonce_label, $button_text){ ?>
   <form method="post" action="/wp-admin/admin.php?page=koc-physician-network-sync">
        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>" />
        <input type="hidden" name="class_function" value="<?php echo esc_attr($class_function); ?>" />
        <?php wp_nonce_field("{$nonce_label}_action", "{$nonce_label}_nonce"); ?>
        <input type="submit" value="<?php echo esc_attr($button_text); ?>" class="button button-primary" />
 </form>
<?php 

return $this->proccess_dynamic_post_button($action, $class_function, $nonce_label, $button_text);
}


public function dynamic_input_field($name, $label, $type='text', $default_value=''){ ?>
    <label for="<?php echo esc_attr($name); ?>"><?php echo esc_html($label); ?></label>
    <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($name); ?>" id="<?php echo esc_attr($name); ?>" value="<?php echo esc_attr($default_value); ?>" />
<?php
}

public function proccess_dynamic_post_button($action, $class_function, $nonce_label, $button_text){
    if ( ! isset( $_POST['action'] ) ) {
        return;
    }

    if ( isset( $_POST['action'] ) && $_POST['action'] === $action
        && isset( $_POST['class_function'] ) && $_POST['class_function'] === $class_function
    ) {
        // Verify nonce for security
        if ( isset( $_POST["{$nonce_label}_nonce"] ) && wp_verify_nonce( $_POST["{$nonce_label}_nonce"], "{$nonce_label}_action" ) ) {
            // Here you would add the logic to generate unique IDs
            $method_name = $_POST['class_function'];
            if(method_exists($this, $method_name)){
                $this->$method_name();
            }
             
            
            echo '<div class="notice notice-success is-dismissible"><p> 
Action "' . esc_html($button_text) . '" completed successfully.
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
        echo '<div class="notice notice-error is-dismissible"><p>Security check failed. Settings not saved.</p></div>';
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
    }

    echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>';
}
}
?>

<style>
.action-buttons {
   display: flex;
   gap:12px;
}


.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
  margin-bottom: 24px;
}
.form-group.form-group-check {
  flex-direction: row;
  align-items: center;
  gap: 4px;
}
 
.form-group.form-group-check input[type="checkbox"] {
  transform: translateY(2px);
}
.form-group label {
  font-size: 18px;
}

.koc-ortho-sync-form {
  margin: 3rem 0;
  max-width: 600px;
}
</style>

<div class="wrap">
    <h1>Hello World.  sdsd </h1>

    <?php

    $child_site_option = get_option( 'koc_physician_sync_is_child_site', false );
    $domain_allow_list = get_option( 'koc_physician_sync_domain_allow_list', array() );
    ?>

    <form class="action-form koc-ortho-sync-form" method="post" action="/wp-admin/admin.php?page=koc-physician-network-sync">
       <div class="form-group form-group-check ">
        <label for="is_child_site">Is Child Site:</label>
        <input type="checkbox" name="is_child_site" id="is_child_site" value="1" <?php checked( $child_site_option, true ); ?> />
    </div>

       <div class="form-group">
         <!-- if child site and sitekey is present display in password. If missing then have add secret key field -->
          <?php
           
                $secret_key = get_option( 'koc_physician_sync_secret_key', '' );
                if ( $secret_key ) {
                    $admin_manager->dynamic_input_field( 'secret_key', 'Secret Key:', 'password', $secret_key );
                } else {
                    $admin_manager->dynamic_input_field( 'secret_key', 'Secret Key (Please generate one):', 'text', '' );
                }
      
            ?>
       </div>

       <!-- if not child site display allowlist separated by commas  -->
        <?php
       if ( ! $child_site_option ) {
           ?>
       <div class="form-group">
           <label for="domain_allow_list">Domain Allow List:</label>
           <input type="text" name="domain_allow_list" id="domain_allow_list" value="<?php echo esc_attr( implode( ', ', $domain_allow_list ) ); ?>" />
       </div>

   
    <?php
         }
         ?>
           <input type="hidden" name="action" value="save_koc_physician_sync_settings" />

     <!-- nonce -->
        <?php wp_nonce_field( 'save_koc_physician_sync_settings_action', 'save_koc_physician_sync_settings_nonce' ); ?>
        <input type="submit" value="Save Settings" class="button button-primary" />
   </form>
  <div class="action-buttons"> 
    <?php
    // Example of using the dynamic_post_button method
    $admin_manager->dynamic_post_button(
        'generate_unique_ids',
        'generate_unique_ids_for_physician_post_type',
        'generate_unique_ids',
        'Generate Unique IDs'
    );

    // export button
    $admin_manager->dynamic_post_button(
        'export_physician_data',
        'export_physician_data',
        'export_physician_data',
        'Export Physician Data'
    );

    // Generate Secret Key Button if not present or if not a child site
    if( !$child_site_option || empty($secret_key) ): 
    $admin_manager->dynamic_post_button(
        'generate_secret_key',
        'generate_secret_key',
        'generate_secret_key',
        'Copy Secret Key'
    );
    endif;
 ?>
 </div>


</div>