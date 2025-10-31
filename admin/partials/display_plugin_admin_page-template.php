<?php
$admin_manager = new Physician_Network_Sync_Admin_Manager();
$admin_manager->load_settings();
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
    <?php settings_errors( 'koc_sync_settings' ); ?>

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

             
               <!-- only use on child sites -->
               <?php if ( $admin_manager->child_site_option ) : ?>
            <div class="form-group">
                <label for="parent_url">Parent Site URL:</label>
                <input type="text" name="parent_url" id="parent_url" value="<?php echo esc_attr( $admin_manager->parent_url ); ?>" />
            </div>

             <div class="form-group">
              <label for="last_update_date">Last Update Date:</label>
              <input type="date" name="last_update_date" id="last_update_date" value="<?php echo esc_attr( $admin_manager->last_update_date ); ?>" />
             </div>

        <div class="form-group">
            <label for="sync_interval">Sync Interval:</label>
            <select name="sync_interval" id="sync_interval">
                <option value="1_hour" <?php selected( $admin_manager->sync_interval, '1_hour' ); ?>>1 hour</option>
                <option value="4_hours" <?php selected( $admin_manager->sync_interval, '4_hours' ); ?>>4 hours</option>
                <option value="8_hours" <?php selected( $admin_manager->sync_interval, '8_hours' ); ?>>8 hours</option>
                <option value="1_day" <?php selected( $admin_manager->sync_interval, '1_day' ); ?>>1 day</option>
            </select>
        </div>
        <?php endif; ?>

        <input type="hidden" name="action" value="save_koc_physician_sync_settings" />

     <!-- nonce -->
        <?php wp_nonce_field( 'save_koc_physician_sync_settings_action', 'save_koc_physician_sync_settings_nonce' ); ?>
        <input type="submit" value="Save Settings" class="button button-primary" />
   </form>

    <?php if ( $admin_manager->child_site_option ) : ?>
        <div class="action-buttons">
            <?php
            $admin_manager->dynamic_post_button(
                'sync_from_queue',
                'sync_from_queue',
                'sync_from_queue',
                'Sync from Queue'
            );
            ?>
        </div>
    <?php endif; ?>

    <?php
      // Generate Secret Key Button if not present or if not a child site
    if ( ! $admin_manager->child_site_option || empty( $admin_manager->secret_key ) ) : ?>
  <div class="action-buttons">
    <?php
    // Generate Unique IDs
    $admin_manager->dynamic_post_button(
      'generate_unique_ids',
      'generate_unique_ids_for_physician_post_type',
      'generate_unique_ids',
      'Generate Unique IDs'
    );

    // Export Physician Data
    $admin_manager->dynamic_post_button(
      'export_physician_data',
      'export_physician_data',
      'export_physician_data',
      'Export Physician Data'
    );

    // Copy Secret Key
    $admin_manager->dynamic_post_button(
      'generate_secret_key',
      'generate_secret_key',
      'generate_secret_key',
      'Copy Secret Key'
    );

    // Purge Sync Queue
    $admin_manager->dynamic_post_button(
      'purge_sync_queue',
      'purge_sync_queue',
      'purge_sync_queue',
      'Purge Sync Queue'
    );
    ?>
  </div>
<?php endif; ?>



</div>