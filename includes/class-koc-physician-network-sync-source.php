<?php

class KOC_Physician_Network_Sync_Source {

    public function __construct() {
        // add_action('admin_menu', array($this, 'add_plugin_admin_menu'));
    }

    public function add_plugin_admin_menu() {
        add_menu_page(
            'KOC Physician Network Sync',
            'KOC Physician Sync',
            'manage_options',
            'koc-physician-network-sync',
            array($this, 'display_plugin_admin_page'),
            'dashicons-admin-users',
            6
        );
    }


    public function display_plugin_admin_page() {
        // require display_plugin_admin_page-template.php 
        include_once KOC_PHYSICIAN_NETWORK_SYNC_PLUGIN_DIR . 'admin/partials/display_plugin_admin_page-template.php';
     }
}