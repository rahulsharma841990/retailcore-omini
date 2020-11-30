<div class="wrap">
    <h1 class="wp-heading-inline">Order Sync Logs</h1>
    <div class="row mt-1">
        <div class="col-md-12">
            <table class="wp-list-table widefat fixed striped table-view-list pages">
                <thead>
                <tr>
                    <th>#</th>
                    <th width="30%">Order No</th>
                    <th width="30%">Sync Response</th>
                    <th width="30%">Action</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $index = 1;

                foreach($logsData as $key => $log):
                    $orderDetails = wc_get_order($log->order_id);
                    $user = $orderDetails->get_user();
                    ?>
                    <tr height="40">
                        <td><?php echo $index; ?></td>
                        <td>
                            <a href="<?php echo admin_url().'post.php?post='.$log->order_id.'&action=edit'; ?>" target="_blank"><b><?php echo '#'.$log->order_id.' ('.$orderDetails->get_billing_first_name().' '.$orderDetails->get_billing_last_name().')'; ?></b></a>
                        </td>
                        <td>
                            <code>
                                <?php echo json_decode($log->response)->Message; ?>
                            </code>
                        </td>
                        <td>
                            <span class="spinner sync-spin log-spin"></span>
                            <a href="javascript:void(0)" class="page-title-action re-sync-order" data-order-id="<?php echo $log->order_id;?>">Re-Sync Order</a>
                            <a href="javascript:void(0)" class="page-title-action delete-log-order" data-order-id="<?php echo $log->order_id;?>">Delete</a>
                        </td>
                    </tr>
                    <?php
                    $index++;
                endforeach;
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>