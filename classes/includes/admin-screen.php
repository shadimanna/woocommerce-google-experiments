<div class="wrap woocommerce Google_Experiments">
	<div id="icon-woocommerce" class="icon32 icon32-woocommerce-email"></div>
	<h2>
    	<?php _e('Experiments', 'wc_google_experiments'); ?>

    	<a href="<?php echo admin_url( 'admin.php?page=google-experiments&amp;add=true' ); ?>" class="add-new-h2"><?php _e('Add experiment', 'wc_google_experiments'); ?></a>
    </h2><br/>
    
    <form method="post">
    <?php
	    $table = new WC_Google_Experiments_Table();
	    $table->prepare_items();
	    $table->display()
    ?>
    </form>
</div>
<script type="text/javascript">
	
	jQuery('a.submitdelete').live('click', function(){
		var answer = confirm('<?php _e( 'Are you sure you want to delete this experiment?', 'wc_google_experiments' ); ?>');
		if (answer){
			return true;
		}
		return false;
	});
	
</script>