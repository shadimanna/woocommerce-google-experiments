<div class="wrap woocommerce">
	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>
	<h2><?php _e('Add Experiment', 'wc_google_experiments'); ?></h2>

	<form class="add" method="post">

		<h3><?php _e( 'Experiment', 'wc_google_experiments' ); ?></h3>
		<p><?php _e( 'These fields determine where to run a Google Experiment.', 'wc_google_experiments' ); ?></p>
		<table class="form-table">
			<tr>
				<th>
					<label for="experiment_name"><?php _e( 'Experiment Name', 'wc_google_experiments' ); ?></label>
				</th>
				<td>
					<input type="text" name="experiment_name" id="experiment_name" class="input-text regular-text" value="<?php echo $admin->field_value( 'experiment_name' ); ?>" />
					<p class="description"><?php _e( 'Enter the experiment ID.', 'wc_google_experiments' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="experiment_description"><?php _e( 'Experiment Description', 'wc_google_experiments' ); ?></label>
				</th>
				<td>
					<textarea name="experiment_description" id="experiment_description" class="input-text regular-text" cols="25" rows="3" style="width: 25em;"><?php echo $admin->field_value( 'experiment_description' ); ?></textarea>
				</td>
			</tr>
			<tr>
				<th>
					<label for="experiment_id"><?php _e( 'Experiment ID', 'wc_google_experiments' ); ?></label>
				</th>
				<td>
					<input type="text" name="experiment_id" id="experiment_id" class="input-text regular-text" value="<?php echo $admin->field_value( 'experiment_id' ); ?>" />
					<p class="description"><?php _e( 'Enter the experiment ID.', 'wc_google_experiments' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="experiment_key"><?php _e( 'Experiment Key', 'wc_google_experiments' ); ?></label>
				</th>
				<td>
					<input type="text" name="experiment_key" id="experiment_key" class="input-text regular-text" value="<?php echo $admin->field_value( 'experiment_key' ); ?>" />
					<p class="description"><?php _e( 'Enter the experiment key.', 'wc_google_experiments' ); ?></p>
				</td>
			</tr>
		</table>

		<h3><?php _e( 'Experiment Placement', 'wc_google_experiments' ); ?></h3>
		<p><?php _e( 'You can choose what WooCommerce page, product(s) or endpoints to run the experiment on.', 'wc_google_experiments' ); ?></p>
		<table class="form-table">
			<tr>
				<th>
					<label for="experiment_triggers"><?php _e( 'Experiment on', 'wc_google_experiments' ); ?></label>
				</th>
				<td>
					<?php
						$_triggers = (array) $admin->field_value( 'experiment_triggers' );
						$triggers  = array();

						foreach ( $_triggers as $key => $trigger ) {
							$triggers[] = str_replace( array( 'page_id:', 'product:' ), '', str_replace( 'all', '0', $trigger ) );
						}
					?>
					<select id="experiment_triggers" name="experiment_triggers[]" style="width:450px;" data-placeholder="<?php _e('Choose Page&hellip;', 'wc_table_rate'); ?>" class="wc-enhanced-select chosen_select">
						<option value="none" <?php selected( in_array( '-1', $triggers ), true ); ?>><?php _e( '- none -', 'wc_google_experiments' ); ?></option>
						<optgroup label="<?php _e( 'WooCommerce Pages:', 'wc_google_experiments' ); ?>">
							<?php
								/*
								$pages = get_pages( );

								foreach ( $pages as $page ) {
									echo '<option value="page_id:' . $page->ID . '" ' . selected( in_array( $page->ID, $triggers ), true, false ) . '>' . __( 'Page:', 'wc_google_experiments' ) . ' ' . $page->post_title . '</option>';
								}
								*/
								
								$shop_page_id = get_option( 'woocommerce_shop_page_id' );
								echo '<option value="page_id:' . $shop_page_id . '" ' . selected( in_array( $shop_page_id, $triggers ), true, false ) . '>' . __( 'Page: Shop', 'wc_google_experiments' ) . '</option>';

								$cart_page_id = get_option( 'woocommerce_cart_page_id' );
								echo '<option value="page_id:' . $cart_page_id . '" ' . selected( in_array( $cart_page_id, $triggers ), true, false ) . '>' . __( 'Page: Cart', 'wc_google_experiments' ) . '</option>';
							
								$checkout_page_id = get_option( 'woocommerce_checkout_page_id' );
								echo '<option value="page_id:' . $checkout_page_id . '" ' . selected( in_array( $checkout_page_id, $triggers ), true, false ) . '>' . __( 'Page: Checkout', 'wc_google_experiments' ) . '</option>';
							
								$myaccount_page_id = get_option( 'woocommerce_myaccount_page_id' );							
								echo '<option value="page_id:' . $myaccount_page_id . '" ' . selected( in_array( $myaccount_page_id, $triggers ), true, false ) . '>' . __( 'Page: My Account', 'wc_google_experiments' ) . '</option>';
							
							?>
						</optgroup>
						<option value="all" <?php selected( in_array( '0', $triggers ), true ); ?>><?php _e( 'All Products', 'wc_google_experiments' ); ?></option>
						<optgroup label="<?php _e( 'WooCommerce Products:', 'wc_google_experiments' ); ?>">
							<?php
								$loop = new WP_Query( array( 'post_type' => array('product'), 'posts_per_page' => -1 ) );

								while ( $loop->have_posts() ) : $loop->the_post();
									$theid = get_the_ID();
									$thetitle = get_the_title();
									echo '<option value="product:' . $theid . '" ' . selected( in_array( $theid, $triggers ), true, false ) . '>' . __( 'Product:', 'wc_google_experiments' ) . ' ' . $thetitle . '</option>';
								endwhile; wp_reset_query();
							?>
						</optgroup>
						<optgroup label="<?php _e( 'WooCommerce Endpoints:', 'wc_google_experiments' ); ?>">
							<?php
								// $terms = get_terms( 'product_shipping_class', array( 'hide_empty' => 0 ) );
								foreach ( WC()->query->query_vars as $key => $value ) {
									echo '<option value="wc_endpoint:' . $value . '" ' . selected( in_array( $value, $triggers ), true, false ) . '>' . __( 'Endpoint:', 'wc_google_experiments' ) . ' ' . $value . '</option>';
								}
							?>
						</optgroup>
					</select>
					<p class="description"><?php echo __( 'Select a WooCommerce page, product(s), or endpoint to add the experiment code on.', 'wc_google_experiments' ); ?></p>
				</td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button button-primary" name="save_recipient" value="<?php _e('Save changes', 'wc_google_experiments'); ?>" />
			<?php wp_nonce_field( 'woocommerce_save_recipient' ); ?>
		</p>

	</form>

	<?php if ( version_compare( WOOCOMMERCE_VERSION, '2.3.0', '<' ) ) : ?>
		<script type="text/javascript">
			jQuery(function() {
				jQuery( 'select.chosen_select' ).chosen();
			});
		</script>
	<?php endif; ?>
</div>