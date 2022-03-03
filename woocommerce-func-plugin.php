<?php

if ( ! class_exists( 'WC_my_custom_plugin' ) ) :
class WC_func_plugin {
  /**
  * Construct the plugin.
  */
  public function __construct() {
    add_action( 'plugins_loaded', array( $this, 'init' ) );
  }
  /**
  * Initialize the plugin.
  */
  public function init() {
    // Checks if WooCommerce is installed.
    if ( class_exists( 'WC_Integration' ) ) {
      // Include our integration class.
      include_once 'class-wc-integration-demo-integration.php';
      // Register the integration.
      add_filter( 'woocommerce_integrations', array( $this, 'add_integration' ) );
    }
  }
  /**
   * Add a new integration to WooCommerce.
   */
  public function add_integration( $integrations ) {
    $integrations[] = 'WC_My_plugin_Integration';
    return $integrations;
  }
}
$WC_my_custom_plugin = new WC_func_plugin( __FILE__ );
endif;