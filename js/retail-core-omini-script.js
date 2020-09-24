jQuery(document).ready(function() {
    console.log(ajaxurl);
    jQuery('.syncProduct').click(function(){
        if(confirm('Are you sure to sync the products')){
            jQuery('.syncProduct').hide();
            jQuery('.wait-text').show();
            jQuery('.spinner').addClass('is-active');
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data:{
                    action: 'retail_core_sync_products'
                },
                success: function(result){
                    result = JSON.parse(result);
                    console.log(result);
                    jQuery('.wait-text').hide();
                    jQuery('.spinner').removeClass('is-active');
                    jQuery('.syncProduct').show();
                    alert(result.inserted_count+' Products successfully inserted');
                }
            })
        }
    });
});