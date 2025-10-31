<?php
require_once KOC_PHYSICIAN_NETWORK_SYNC_PLUGIN_DIR . 'utils/class-koc-physician-network-sync-admin-manager.php';
$admin_manager = new Physician_Network_Sync_Admin_Manager();
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
    <h1>  KOC Physician Network Sync Settings</h1>

    <form class="action-form koc-ortho-sync-form" method="post" action="/wp-admin/admin.php?page=koc-physician-network-sync">
       <div class="form-group form-group-check ">
        <label for="is_child_site">Is Child Site:</label>
        <input type="checkbox" name="is_child_site" id="is_child_site" value="1" <?php checked( $admin_manager->child_site_option, true ); ?> />
    </div>

       <div class="form-group">
         <!-- if child site and sitekey is present display in password. If missing then have add secret key field -->
          <?php
           
                if ( $admin_manager->secret_key ) {
                    $admin_manager->dynamic_input_field( 'secret_key', 'Secret Key:', 'password', $admin_manager->secret_key );
                } else {
                    $admin_manager->dynamic_input_field( 'secret_key', 'Secret Key (Please generate one):', 'text', '' );
                }
      
            ?>
       </div>

       <!-- if not child site display allowlist separated by commas  -->
        <?php
       if ( ! $admin_manager->child_site_option ) {
           ?>
       <div class="form-group">
           <label for="domain_allow_list">Domain Allow List:</label>
           <input type="text" name="domain_allow_list" id="domain_allow_list" value="<?php echo esc_attr( implode( ', ', $admin_manager->domain_allow_list ) ); ?>" />
       </div>

      
   
    <?php
         }
         ?>
           <div class="form-group">
              <label for="last_update_date">Last Update Date:</label>
              <input type="date" name="last_update_date" id="last_update_date" value="<?php echo esc_attr( $admin_manager->last_update_date ); ?>" />
        </div>
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
    if( !$admin_manager->child_site_option || empty($admin_manager->secret_key) ): 
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