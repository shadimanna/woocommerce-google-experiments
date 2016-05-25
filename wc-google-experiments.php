<?php
/**
 * Plugin Name: WooCommerce Google Experiments
 * Plugin URI: https://github.com/shadimanna/woocommerce-google-experiments
 * Description: Add Google Experiements on WooCommerce pages, products and endpoints
 * Version: 1.0.0
 * Author: Shadi Manna
 * Author URI: http://progressusmarketing.com/
 * License: GPLv3
 */

/**
 * Required functions
 */
if ( ! class_exists( 'WC_Dependencies' ) )
  require_once ( 'woo-includes/class-wc-dependencies.php' );

/**
 * WC Detection
 */
if ( ! function_exists( 'is_woocommerce_active' ) ) {
  function is_woocommerce_active() {
    return WC_Dependencies::woocommerce_active_check();
  }
}

/**
 * Localisation
 **/
load_plugin_textdomain( 'wc_google_experiments', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );


/**
 * init_Google_Experiments function.
 */
function init_google_experiments() {
	if ( is_woocommerce_active() ) {
		include_once( 'classes/class-wc-google-experiments.php' );
	}
}

add_action( 'plugins_loaded', 'init_google_experiments', 0 );


/**
 * Activation
 */
register_activation_hook( __FILE__, 'activate_google_experiments' );

function activate_google_experiments() {
	global $wpdb;

	$wpdb->hide_errors();

	$collate = '';
    if ( $wpdb->has_cap( 'collation' ) ) {
		if ( ! empty($wpdb->charset ) ) {
			$collate .= "DEFAULT CHARACTER SET $wpdb->charset";
		}
		if ( ! empty($wpdb->collate ) ) {
			$collate .= " COLLATE $wpdb->collate";
		}
    }

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    /**
     * Table for experiments
     */
    $sql = "
CREATE TABLE {$wpdb->prefix}woocommerce_google_experiments (
  ge_id bigint(20) NOT NULL auto_increment,
  experiment_name LONGTEXT NULL,
  experiment_description LONGTEXT NULL,
  experiment_id varchar(240) NULL,
  experiment_key varchar(240) NULL,
  PRIMARY KEY  (ge_id)
) $collate;
";
    dbDelta( $sql );

    $sql = "
CREATE TABLE {$wpdb->prefix}woocommerce_google_experiments_triggers (
  ge_id bigint(20) NOT NULL,
  object_id varchar(200) NOT NULL,
  object_type varchar(200) NOT NULL,
  PRIMARY KEY  (ge_id)
) $collate;
";
    dbDelta( $sql );
}
