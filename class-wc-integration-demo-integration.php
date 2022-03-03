<?php
/**
 * Integration Demo.
 *
 * @package   Woocommerce My plugin Integration
 * @category Integration
 * @author   Addweb Solution Pvt. Ltd.
 */
if ( ! class_exists( 'WC_My_plugin_Integration' ) ) :
class WC_My_plugin_Integration extends WC_Integration {
  /**
   * Init and hook in the integration.
   */
  public function __construct() {
    global $woocommerce;
    $this->id                 = 'my-plugin-integration';
    $this->method_title       = __( 'My Plugin Integration');
    $this->method_description = __( 'My Plugin Integration to show you how easy it is to extend WooCommerce.');
    // Load the settings.
    $this->init_form_fields();
    $this->init_settings();
    // Define user set variables.
    $this->custom_name          = $this->get_option( 'custom_name' );
    // Actions.
    add_action( 'woocommerce_update_options_integration_' .  $this->id, array( $this, 'process_admin_options' ) );
  }
  /**
   * Initialize integration settings form fields.
   */
  public function init_form_fields() {
    $this->form_fields = array(
      'custom_name' => array(
        'title'             => __( 'Custom Name'),
        'type'              => 'text',
        'description'       => __( 'Enter Custom Name'),
        'desc_tip'          => true,
        'default'           => '',
        'css'      => 'width:170px;',
      ),
    );
  }
}
endif; 