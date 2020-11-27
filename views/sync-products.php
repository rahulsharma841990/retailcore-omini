<div class="wrap">
    <!--    <h1 class="wp-heading-inline">Activation</h1>-->
    <div class="row">
        <div class="col-md-12 text-center mt-2">
            <img src="<?=RETAILCORE__PLUGIN_DIR_URL.'assets/retailcore-software-logo.png'?>" />
            <h4 class="mt-1">Click to to sync your product with your Retailcore Account..</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <small class="wait-text">Please wait we are sync your products...</small>
        </div>
        <div class="col-md-6">
            <span class="spinner custom-spin"></span>
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-12 text-center">
            <input type="submit" name="syncProducts" class="button button-primary syncProduct" value="Sync Your Product">
        </div>
    </div>
    <div class="row mt-1">
        <div class="col-md-12 text-center">
            <h4>Cron/Webhook URL</h4>
            <code class="cron-url mt-1">
                <?php echo get_site_url();?>?product_sync=AwtWrlly3SEY4UW3jB82dRBEhNkv1xh6
            </code>
        </div>
    </div>

    <div class="row mt-1">
        <div class="col-md-12 text-center">
            <h4>Last Sync On:</h4>
            <p><?php echo get_option( '_retailcore_omini_timestamp' ); ?></p>
        </div>
    </div>
</div>