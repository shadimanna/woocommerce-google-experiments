<?php
/**
 * WC_Google_Experiments_Table class.
 *
 * @extends WP_List_Table
 */
class WC_Google_Experiments_Table extends WP_List_Table {

	/**
	 * __construct function.
	 */
	function __construct(){
		parent::__construct( array(
			'singular'  => 'experiment',
			'plural'    => 'experiments',
			'ajax'      => false
		) );
	}

	/**
	 * column_default function.
	 *
	 * @access public
	 * @param mixed $item
	 * @param mixed $column_name
	 * @return void
	 */
	function column_default( $item, $column_name ) {
		global $woocommerce, $wpdb;

		switch( $column_name ) {
			case 'experiment_name' :
				$return = $item->experiment_name;

				$return = wpautop( $return );

				$return .= '
				<div class="row-actions">
					<span class="edit"><a href="' . admin_url( 'admin.php?page=google-experiments&edit=' . $item->ge_id ) . '">' . __( 'Edit', 'wc_google_experiments' ) . '</a> | </span><span class="trash"><a class="submitdelete" href="' . wp_nonce_url( admin_url( 'admin.php?page=google-experiments&delete=' . $item->ge_id ), 'delete_notification' ) . '">' . __( 'Delete', 'wc_google_experiments' ) . '</a></span>
				</div>';

				return $return;
			break;
			case 'experiment_description' :
				$return = $item->experiment_description;
				return $return;
			break;
			case 'experiment_id' :
				$return = $item->experiment_id;
				return $return;
			break;
			case 'experiment_key' :
				$return = $item->experiment_key;
				return $return;
			break;
			case 'notification_triggers' :

				$return 	= '';
				$pages = array();
				$products 	= array();
				$endpoints	= array();

				$triggers = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE ge_id = " . absint( $item->ge_id ) . ";" );

				foreach ( $triggers as $trigger ) {

					if ( '0' === $trigger->object_id )
						return '<strong> ' . __( 'All Products', 'wc_google_experiments' ) . '</strong>';

					switch ( $trigger->object_type ) {
						case 'page_id' :
							$page_title = get_the_title( $trigger->object_id);

							if ( ! $page_title ) {
								continue;
							}

							$pages[] = $page_title;
						break;
						case 'product' :
							$product_title = get_the_title( $trigger->object_id);

							if ( ! $product_title ) {
								continue;
							}

							$products[] = $product_title;
						break;
						case 'wc_endpoint' :
							$endpoints[] = $trigger->object_id;
						break;
					}
				}

				if ( sizeof( $pages ) > 0 ) {
					$return .= '<p><strong>' . __( 'Page:', 'wc_google_experiments' ) . '</strong> ' . implode( ', ', $pages ) . '</p>';
				}

				if ( sizeof( $products ) > 0 ) {
					$return .= '<p><strong>' . __( 'Product:', 'wc_google_experiments' ) . '</strong> ' . implode( ', ', $products ) . '</p>';
				}

				if ( sizeof( $endpoints ) > 0 ) {
					$return .= '<p><strong>' . __( 'Endpoint:', 'wc_google_experiments' ) . '</strong> ' . implode( ', ', $endpoints ) . '</p>';
				}

				if ( ! $return ) {
					$return = '-';
				}

				return $return;
			break;
		}
	}

	/**
	 * column_cb function.
	 *
	 * @access public
	 * @param mixed $item
	 * @return void
	 */
	function column_cb( $item ){
		return sprintf(
			'<input type="checkbox" name="id[]" value="%s" />',
			/*$2%s*/ $item->ge_id
		);
	}

	/**
	 * get_columns function.
	 *
	 * @access public
	 * @return void
	 */
	function get_columns(){
		$columns = array(
			'cb'        		=> '<input type="checkbox" />',
			'experiment_name'    => __( 'Experiment Name', 'wc_google_experiments' ),
			'experiment_description' => __( 'Experiment Description', 'wc_google_experiments' ),
			'experiment_id' => __( 'Experiment ID', 'wc_google_experiments' ),
			'experiment_key' => __( 'Experiment Key', 'wc_google_experiments' ),
			'notification_triggers' => __( 'Experiment Placement', 'wc_google_experiments' )
		);
		return $columns;
	}

	 /**
	 * Get bulk actions
	 */
	function get_bulk_actions() {
		$actions = array(
			'delete'    => __( 'Delete', 'wc_google_experiments' )
		);
		return $actions;
	}

	/**
	 * Process bulk actions
	 */
	function process_bulk_action() {
		global $wpdb;

		if ( ! $this->current_action() )
			return;

		$experiment_ids = array_map( 'intval', $_POST['id'] );

		if ( $experiment_ids ) {

			if ( 'delete' === $this->current_action() ) {

			   foreach ( $experiment_ids as $experiment_id ) {

				   $experiment_id = absint( $experiment_id );

				   $wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_google_experiments WHERE ge_id = {$experiment_id};" );
				   $wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE ge_id = {$experiment_id};" );
			   }

			}

			echo '<div class="updated"><p>' . __( 'Experiments updated', 'wc_google_experiments' ) . '</p></div>';
		}
	}


	/**
	 * prepare_items function.
	 *
	 * @access public
	 * @return void
	 */
	function prepare_items() {
		global $wpdb;

		/**
		 * Init column headers
		 */
		$this->_column_headers = array( $this->get_columns(), array(), array() );

		/**
		 * Process bulk actions
		 */
		$this->process_bulk_action();

		/**
		 * Get experiements
		 */
		$count = $wpdb->get_var( "SELECT COUNT(ge_id) FROM {$wpdb->prefix}woocommerce_google_experiments;" );

		$this->items = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_google_experiments LIMIT " . ( 25 * ( $this->get_pagenum() - 1 ) ) . ", 25;" );

		/**
		 * Handle pagination
		 */
		$this->set_pagination_args( array(
			'total_items' => $count,
			'per_page'    => 25,
			'total_pages' => ceil( $count / 25 )
		) );
	}

}