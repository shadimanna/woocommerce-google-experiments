<?php

/**
 * WC_Google_Experiments_Admin class.
 */
class WC_Google_Experiments_Admin {

	private $editing;
	private $editing_id;

	/**
	 * __construct function.
	 */
	function __construct() {
		// Admin menu
		add_action( 'admin_menu', array( $this, 'admin_menu' ) );
		add_action( 'woocommerce_screen_ids', array( $this, 'screen_ids' ) );
	}

	/**
	 * Screen ids
	 */
	public function screen_ids( $ids ) {
		$wc_screen_id = strtolower( __( 'WooCommerce', 'woocommerce' ) );

		$ids[] = $wc_screen_id . '_page_google-experiments';

		return $ids;
	}

	/**
	 * admin_menu function.
	 */
	function admin_menu() {
		$page = add_submenu_page( 'woocommerce', __( 'Google Experiments', 'wc_google_experiments' ), __( 'Google Experiments', 'wc_google_experiments' ), 'manage_woocommerce', 'google-experiments', array( $this, 'admin_screen' ) );

		if ( function_exists( 'woocommerce_admin_css' ) )
			add_action( 'admin_print_styles-'. $page, 'woocommerce_admin_css' );
		add_action( 'admin_print_styles-'. $page, array( $this, 'admin_enqueue' ) );
	}

	/**
	 * admin_enqueue function.
	 */
	function admin_enqueue() {
		if ( version_compare( WOOCOMMERCE_VERSION, '2.3.0', '<' ) ) {
			wp_enqueue_script( 'woocommerce_admin' );
			wp_enqueue_script( 'chosen' );
		}

		wp_enqueue_style( 'notifications_css', plugins_url( 'assets/css/admin.css' , dirname( __FILE__ ) ) );
	}

	/**
	 * admin_screen function.
	 */
	function admin_screen() {
		global $wpdb;

		$admin = $this;

		if ( ! empty( $_GET['delete'] ) ) {

			check_admin_referer( 'delete_notification' );

			$delete = absint( $_GET['delete'] );

			$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_google_experiments WHERE ge_id = {$delete};" );
			$wpdb->query( "DELETE FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE ge_id = {$delete};" );

			echo '<div class="updated fade"><p>' . __( 'Experiment deleted successfully', 'wc_google_experiments' ) . '</p></div>';

		} elseif ( ! empty( $_GET['add'] ) ) {

			if ( ! empty( $_POST['save_recipient'] ) ) {

				check_admin_referer( 'woocommerce_save_recipient' );

				$result = $this->add_recipient();

				if ( is_wp_error( $result ) ) {
					echo '<div class="error"><p>' . $result->get_error_message() . '</p></div>';
				} elseif ( $result ) {

					echo '<div class="updated fade"><p>' . __( 'Experiment saved successfully', 'wc_google_experiments' ) . '</p></div>';

				}

			}

			include_once( 'includes/admin-screen-edit.php' );
			return;

		} elseif ( ! empty( $_GET['edit'] ) ) {

			$this->editing_id = absint( $_GET['edit'] );
			$this->editing = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_google_experiments WHERE ge_id = " . $this->editing_id . ";" );

			if ( ! empty( $_POST['save_recipient'] ) ) {

				check_admin_referer( 'woocommerce_save_recipient' );

				$result = $this->save_recipient();

				if ( is_wp_error( $result ) ) {
					echo '<div class="error"><p>' . $result->get_error_message() . '</p></div>';
				} elseif ( $result ) {

					echo '<div class="updated fade"><p>' . __( 'Experiment saved successfully', 'wc_google_experiments' ) . '</p></div>';

				}

			}

			include_once( 'includes/admin-screen-edit.php' );
			return;
		}

		if ( ! empty( $_GET['success'] ) ) {
			echo '<div class="updated fade"><p>' . __( 'Experiment saved successfully', 'wc_google_experiments' ) . '</p></div>';
		}

		if ( ! empty( $_GET['deleted'] ) ) {
			echo '<div class="updated fade"><p>' . __( 'Experiment deleted successfully', 'wc_google_experiments' ) . '</p></div>';
		}

		if ( ! class_exists( 'WP_List_Table' ) ) {
			include_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
		}
		include_once( 'class-wc-google-experiments-table.php' );
		include_once( 'includes/admin-screen.php' );
	}


	/**
	 * field_value function.
	 *
	 * @param string $name
	 */
	function field_value( $name ) {
		global $wpdb;

		$value = '';

		if ( isset( $this->editing->$name ) ) {

			$value = $this->editing->$name;

		} elseif ( $name == 'experiment_triggers' ) {

			$value = $wpdb->get_col( "SELECT object_id FROM {$wpdb->prefix}woocommerce_google_experiments_triggers WHERE ge_id = " . absint( $this->editing_id ) . ";" );

		}

		$value = maybe_unserialize( $value );

		if ( isset( $_POST[ $name ] ) ) {
			$value = $_POST[ $name ];
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'trim', array_map( 'esc_attr', array_map( 'stripslashes', $value ) ) );
		} else {
			$value = trim( esc_attr( stripslashes( $value ) ) );
		}

		return $value;
	}

	/**
	 * add_recipient function.
	 */
	function add_recipient() {
		global $wpdb;

		$experiment_name 			= sanitize_text_field( stripslashes( $_POST['experiment_name'] ) );
		$experiment_description	= sanitize_text_field( stripslashes( $_POST['experiment_description'] ) );
		$experiment_id 			= sanitize_text_field( stripslashes( $_POST['experiment_id'] ) );
		$experiment_key 			= sanitize_text_field( stripslashes( $_POST['experiment_key'] ) );
		
		// Validate
		if ( empty( $experiment_name ) ) {
			return new WP_Error( 'input', __( 'Experiment name is a required field', 'wc_google_experiments' ) );
		}

		if ( empty( $experiment_id ) ) {
			return new WP_Error( 'input', __( 'Experiment id is a required field', 'wc_google_experiments' ) );
		}

		if ( empty( $experiment_key ) ) {
			return new WP_Error( 'input', __( 'Experiment key is a required field', 'wc_google_experiments' ) );
		}

		// Insert recipient
		$result = $wpdb->insert(
			"{$wpdb->prefix}woocommerce_google_experiments",
			array(
				'experiment_name' 			=> $experiment_name,
				'experiment_description' 	=> $experiment_description,
				'experiment_id' 			=> $experiment_id,
				'experiment_key' 			=> $experiment_key,
			),
			array(
				'%s', '%s', '%s', '%s'
			)
		);

		$id = $wpdb->insert_id;

		if ( $result && $id ) {

			$triggers = array();

			// Store triggers
			$posted_triggers = isset( $_POST['experiment_triggers'] ) ? array_filter( array_map( 'esc_attr', array_map( 'trim', (array) $_POST['experiment_triggers'] ) ) ) : array();

			foreach ( $posted_triggers as $trigger ) {
				if ( $trigger == 'none' ) {

					$triggers[] = "( {$id}, -1, '' )";

				} elseif ( $trigger == 'all' ) {

					$triggers[] = "( {$id}, 0, '' )";

				} else {
					$trigger = explode( ':', $trigger );

					$term 	= esc_attr( $trigger[0] );
					// $tid 	= absint( $trigger[1] );
					$tid 	= esc_attr( $trigger[1] );

					$triggers[] = "( {$id}, '{$tid}', '{$term}' )";
				}
			}

			if ( sizeof( $triggers ) > 0 ) {
				$wpdb->query( "
					INSERT INTO {$wpdb->prefix}woocommerce_google_experiments_triggers ( ge_id, object_id, object_type )
					VALUES " . implode( ',', $triggers ) . ";
				" );
			}

			return true;
		}

		return false;
	}

	/**
	 * save_recipient function.
	 */
	function save_recipient() {
		global $wpdb;

		$experiment_name 			= sanitize_text_field( stripslashes( $_POST['experiment_name'] ) );
		$experiment_description	= sanitize_text_field( stripslashes( $_POST['experiment_description'] ) );
		$experiment_id 			= sanitize_text_field( stripslashes( $_POST['experiment_id'] ) );
		$experiment_key 			= sanitize_text_field( stripslashes( $_POST['experiment_key'] ) );
		
		// Validate
		if ( empty( $experiment_name ) ) {
			return new WP_Error( 'input', __( 'Experiment name is a required field', 'wc_google_experiments' ) );
		}

		if ( empty( $experiment_id ) ) {
			return new WP_Error( 'input', __( 'Experiment id is a required field', 'wc_google_experiments' ) );
		}

		if ( empty( $experiment_key ) ) {
			return new WP_Error( 'input', __( 'Experiment key is a required field', 'wc_google_experiments' ) );
		}

		// Insert recipient
		$wpdb->update(
			"{$wpdb->prefix}woocommerce_google_experiments",
			array(
				'experiment_name' 			=> $experiment_name,
				'experiment_description' 	=> $experiment_description,
				'experiment_id' 			=> $experiment_id,
				'experiment_key' 			=> $experiment_key,
			),
			array( 'ge_id' => absint( $this->editing_id ) ),
			array(
				'%s', '%s', '%s', '%s'
			),
			array( '%d' )
		);

		// Delete old triggers
		$wpdb->query( "
			DELETE FROM {$wpdb->prefix}woocommerce_google_experiments_triggers
			WHERE ge_id = " . absint( $this->editing_id ) . ";
		" );

		$triggers = array();

		// Store triggers
		$posted_triggers = isset( $_POST['experiment_triggers'] ) ? array_filter( array_map( 'esc_attr', array_map( 'trim', (array) $_POST['experiment_triggers'] ) ) ) : array();

		foreach ( $posted_triggers as $trigger ) {
			if ( $trigger == 'none' ) {

				$triggers[] = "( " . absint( $this->editing_id ) . ", -1, '' )";

			} elseif ( $trigger == 'all' ) {

				$triggers[] = "( " . absint( $this->editing_id ) . ", 0, '' )";

			} else {
				$trigger = explode( ':', $trigger );

				$term 	= esc_attr( $trigger[0] );
				// $tid 	= absint( $trigger[1] );
				$tid 	= esc_attr( $trigger[1] );

				$triggers[] = "( " . absint( $this->editing_id ) . ", '{$tid}', '{$term}' )";
			}
		}

		if ( sizeof( $triggers ) > 0 ) {
			$wpdb->query( "
				INSERT INTO {$wpdb->prefix}woocommerce_google_experiments_triggers ( ge_id, object_id, object_type )
				VALUES " . implode( ',', $triggers ) . ";
			" );
		}

		return true;
	}
}
