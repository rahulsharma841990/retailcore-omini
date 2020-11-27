jQuery(document).ready(function() {
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
                },
                error: function(result){
                    jQuery('.wait-text').hide();
                    jQuery('.spinner').removeClass('is-active');
                    jQuery('.syncProduct').show();
                    alert('Something went wrong, please try again!');
                }
            })
        }
    });

    jQuery('.re-sync-order').click(function(){
        if(confirm('Are you sure to sync this order?')){
            let currentRow = jQuery(this).parents('tr');
            let elem = jQuery(this);
            jQuery(this).parent('td').find('.sync-spin').addClass('is-active');
            jQuery(this).hide();
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data:{
                    action: 'retail_core_sync_single_order',
                    order_id: jQuery(this).data('order-id')
                },
                success: function(result){
                    let response = JSON.parse(result);
                    if(response.status == false){
                        alert(response.response.Message);
                        elem.show();
                    }else{
                        alert('Order Sync Successfully!');
                        currentRow.remove();
                    }
                    elem.parent('td').find('.sync-spin').removeClass('is-active')
                }
            });
        }
    });

    jQuery('.delete-log-order').click(function(){
        if(confirm('Are you sure to delete this order log?')){
            let currentRow = jQuery(this).parents('tr');
            let elem = jQuery(this);
            jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data:{
                    action: 'retail_core_delete_order_log',
                    order_id: jQuery(this).data('order-id')
                },
                success: function(result){
                    currentRow.remove();
                }
            });
        }
    });
});