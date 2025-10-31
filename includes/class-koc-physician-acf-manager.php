<?php
/**
 * Manages ACF fields for the Physician post type.
 *
 * @package    Koc_Physician_Network_Sync
 * @subpackage Koc_Physician_Network_Sync/utils
 */
class KOC_Physician_ACF_Manager {

    /**
     * The meta key for the unique physician ID.
     *
     * @since    1.0.0
     * @access   public
     * @var      string
     */
    public static $unique_id_meta_key = 'unique_physician_id';

  
    /**
     * Registers the field groups and fields with ACF.
     *
     * @since 1.0.0
     */
    public function register_field_groups() {
        if( function_exists('acf_add_local_field_group') ) {
            acf_add_local_field_group(array(
                'key' => 'group_koc_physician_sync',
                'title' => 'KOC Physician Sync',
                'fields' => array(
                    array(
                        'key' => 'field_koc_global_uuid',
                        'label' => 'Global Unique ID',
                        'name' => self::$unique_id_meta_key,
                        'type' => 'text',
                        'instructions' => 'A unique identifier for the physician across all sites.',
                        'required' => 1,
                        'readonly' => 1,
                        'default_value' => '',
                        'placeholder' => '',
                        'prepend' => '',
                        'append' => '',
                        'maxlength' => '',
                    ),
                ),
                'location' => array(
                    array(
                        array(
                            'param' => 'post_type',
                            'operator' => '==',
                            'value' => 'physician',
                        ),
                    ),
                ),
                'menu_order' => 0,
                'position' => 'side',
                'style' => 'default',
                'label_placement' => 'top',
                'instruction_placement' => 'label',
                'hide_on_screen' => '',
                'active' => true,
                'description' => '',
            ));
        }
    }
}
