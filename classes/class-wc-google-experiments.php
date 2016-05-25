<?php

/**
 * WC_Google_Experiments class.
 */
class WC_Google_Experiments {

	var $mailer;
	var $admin;
	var $plugin_path;

	/**
	 * __construct function.
	 *
	 * @access public
	 * @return void
	 */
	function __construct() {

		// Admin section
		if ( is_admin() ) {
			include_once( 'class-wc-google-experiments-admin.php' );
			$this->admin = new WC_Google_Experiments_Admin();
		}

		// Hook experiments
		add_action( 'wp_head', array( $this, 'embed_experiments_tracking' ) );
	}

	private function experiment_tracking_code($experiment_key)
	{
		?>
			<!-- Google Analytics Content Experiment code -->
			<script>function utmx_section(){}function utmx(){}(function(){var
			k='<?php echo $experiment_key; ?>',d=document,l=d.location,c=d.cookie;
			if(l.search.indexOf('utm_expid='+k)>0)return;
			function f(n){if(c){var i=c.indexOf(n+'=');if(i>-1){var j=c.
			indexOf(';',i);return escape(c.substring(i+n.length+1,j<0?c.
			length:j))}}}var x=f('__utmx'),xx=f('__utmxx'),h=l.hash;d.write(
			'<sc'+'ript src="'+'http'+(l.protocol=='https:'?'s://ssl':
			'://www')+'.google-analytics.com/ga_exp.js?'+'utmxkey='+k+
			'&utmx='+(x?x:'')+'&utmxx='+(xx?xx:'')+'&utmxtime='+new Date().
			valueOf()+(h?'&utmxhash='+escape(h.substr(1)):'')+
			'" type="text/javascript" charset="utf-8"><\/sc'+'ript>')})();
			</script><script>utmx('url','A/B');</script>
			<!-- End of Google Analytics Content Experiment code -->
		<?
	}

	/**
	 * Get the plugin path
	 */
	function plugin_path() {
		if ( $this->plugin_path ) return $this->plugin_path;

		return $this->plugin_path = untrailingslashit( plugin_dir_path( dirname( __FILE__ ) ) );
	}

	/**
	 * embed_experiments_tracking function.
	 *
	 * @access public
	 * @return void
	 */
	function embed_experiments_tracking( ) {
		global $woocommerce, $product, $wpdb;

		$object_id = '';
		$object_id = is_shop() ? get_option( 'woocommerce_shop_page_id' ) : $object_id;
		$object_id = is_cart() ? get_option( 'woocommerce_cart_page_id' ) : $object_id;
		$object_id = is_checkout() ? get_option( 'woocommerce_checkout_page_id' ) : $object_id;
		$object_id = is_account_page() ? get_option( 'woocommerce_myaccount_page_id' ) : $object_id;
		$object_id = is_product() ? $product->id : $object_id;

		if( is_wc_endpoint_url() ) {
			$end_points = WC()->query->query_vars;
			//print_r($end_points);
			$object_id = is_wc_endpoint_url( 'order-pay' ) ? $end_points['order-pay'] : $object_id;
			$object_id = is_wc_endpoint_url( 'order-received' ) ? $end_points['order-received']  : $object_id;
			$object_id = is_wc_endpoint_url( 'view-order' ) ? $end_points['view-order'] : $object_id;
			$object_id = is_wc_endpoint_url( 'edit-account' ) ? $end_points['edit-account'] : $object_id;
			$object_id = is_wc_endpoint_url( 'edit-address' ) ? $end_points['edit-address'] : $object_id;
			$object_id = is_wc_endpoint_url( 'lost-password' ) ? $end_points['lost-password'] : $object_id;
			$object_id = is_wc_endpoint_url( 'customer-logout' ) ? $end_points['customer-logout'] : $object_id;
			$object_id = is_wc_endpoint_url( 'add-payment-method' ) ? $end_points['$end_pointsadd-payment-method'] : $object_id;
		}

		echo "object_id: ".$object_id;
			
		if (!empty($object_id)) {
			$trigger_ge = $wpdb->get_var( "SELECT ge_id FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE object_id = '" . $object_id . "' order by ge_id desc;" );

			echo "trigger_ge: ".$trigger_ge;
			if (!empty($trigger_ge)) {
				$experiment_key = $wpdb->get_var( "SELECT experiment_key FROM {$wpdb->prefix}woocommerce_google_experiments WHERE ge_id = " . $trigger_ge . ";" );

					$this->experiment_tracking_code($experiment_key);
			} elseif ( is_product() ) { // check if there is an "All Products" trigger
				$trigger_ge = $wpdb->get_var( "SELECT ge_id FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE object_id = '0' order by ge_id desc;" );

				echo "trigger_ge 2: ".$trigger_ge;
				if (!empty($trigger_ge)) {
					$experiment_key = $wpdb->get_var( "SELECT experiment_key FROM {$wpdb->prefix}woocommerce_google_experiments WHERE ge_id = " . $trigger_ge . ";" );

					$this->experiment_tracking_code($experiment_key);
				}
			}
		}
		

	}
}

$GLOBALS['wc_google_experiments'] = new WC_Google_Experiments();