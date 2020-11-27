<?php
if(!class_exists('OminiCurlRequest')) {
    class OminiCurlRequest{

        private static $CURL = '';

        private static $ProductImageBasePath = 'https://aiinnovation.in/retailcore/public/uploads/products_images/';
        private static $ProductCategoryImageBasePath = 'https://retailcore.biz/taazasabzi_test/public/uploads/dynamic_product_properties_image/';

        public function __construct(){
            self::init();
        }

        public static function init(){


        }

        private static function uploadMedia($image_url){
            require_once(ABSPATH.'wp-admin/includes/image.php');
            require_once(ABSPATH.'wp-admin/includes/file.php');
            require_once(ABSPATH.'wp-admin/includes/media.php');
            $media = media_sideload_image($image_url,0);
            $attachments = get_posts(array(
                'post_type' => 'attachment',
                'post_status' => null,
                'post_parent' => 0,
                'orderby' => 'post_date',
                'order' => 'DESC'
            ));
            return $attachments[0]->ID;
        }

        protected static function getExistingProduct($product){

            $args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'posts_per_page' => 1,
                'meta_key' => '_product_barcode',
                'meta_value' => $product['product_system_barcode']
            );
            $dbResult = new WP_Query($args);
            $existingProduct = $dbResult->get_posts();
            if(!empty($existingProduct)){
                $featured_image_id = get_post_meta($existingProduct[0]->ID,'_thumbnail_id',true);
                $image_galleries_id = get_post_meta($existingProduct[0]->ID,'_product_image_gallery',true);
                if(!empty($featured_image_id)) {
                    wp_delete_post($featured_image_id);
                }
                if(!empty($image_galleries_id))
                {
                    $image_galleries_array = explode(',',$image_galleries_id);

                    foreach($image_galleries_array as $single_image_id) {
                        wp_delete_post($single_image_id);
                    }
                }
            }
            return $existingProduct;
        }

        public static function syncProduct(){
            $products = CurlRequest::getProducts();
            $products = json_decode($products,true);
            $count = 0;
            foreach($products['Products'] as $key => $product){
                $existingProduct = self::getExistingProduct($product);
                if($product['deleted_at'] == ''){
                    $productImages = $product['product_images'];
                    $galleryImages = [];
                    foreach($productImages as $k => $image){
                        $galleryImages[] = self::uploadMedia(self::$ProductImageBasePath.$image['product_image']);
                    }

                    $categoriesArray = self::putCategories($product);

                    if(!empty($existingProduct)){
                        $objProduct = new WC_Product($existingProduct[0]->ID);
                    }else{
                        $objProduct = new WC_Product();
                    }
                    $objProduct->set_image_id($galleryImages[0]);
                    unset($galleryImages[0]);
                    $objProduct->set_name($product['product_name']);
                    $objProduct->set_status("publish");
                    $objProduct->set_catalog_visibility('visible');
                    $objProduct->set_description($product['product_description']);
                    $objProduct->set_sku(rand(111111,999999)."-sku");
                    $objProduct->set_price($product['product_price_master'][0]['offer_price']);
                    $objProduct->set_regular_price($product['product_price_master'][0]['offer_price']);
                    $objProduct->set_manage_stock(true);
                    $objProduct->set_stock_quantity($product['product_price_master'][0]['product_qty']);
                    $objProduct->set_stock_status('instock');
                    $objProduct->set_backorders('no');
                    $objProduct->set_reviews_allowed(true);
                    $objProduct->set_sold_individually(false);
                    $objProduct->set_category_ids($categoriesArray);
                    $objProduct->set_gallery_image_ids($galleryImages);
                    $productID = $objProduct->save();
                    $productFeatures = $product['product_features'];
                    unset($productFeatures['dynamic_category']);
                    unset($productFeatures['dynamic_subcategory']);
                    $uqc_name = $product['uqc_id']!=''?$product['uqc']['uqc_shortname']:'';
                    $attr_array = array();
                    foreach($productFeatures as $key => $attribute){
                        $attr_array[$key] = array(
                            'name' => str_replace('dynamic_','',$key),
                            'value' => $key=='dynamic_size'?$attribute.' '.$uqc_name:$attribute,
                            'position' => 0,
                            'is_visible' => 1,
                            'is_taxonomy' => 0
                        );
                    }
                    update_post_meta( $productID, '_product_attributes', $attr_array);
                    update_post_meta($productID,'_product_barcode',$product['product_system_barcode']);
                    $count++;
                }else{
                    wp_delete_post($existingProduct[0]->ID,true);
                }
            }
            return $count;
        }



        public static function putCategories($product){
            $categoriesArray = [];
            $parentTerm = wp_insert_term(
                $product['product_features']['dynamic_category'],
                'product_cat',
                array(
                    'description'=> $product['product_features']['dynamic_category'],
                    'slug' => strtolower(str_replace(' ','_',$product['product_features']['dynamic_category'])),
                    'parent'=> 0
                )
            );

            if(!is_array($parentTerm)){
                $existingTerm = $parentTerm->error_data['term_exists'];
                $parentTerm = [];
                $parentTerm['term_id'] = $existingTerm;
            }else{
                $existingTermData = get_term_meta($parentTerm['term_id']);
                wp_delete_post($existingTermData['thumbnail_id'][0]);
                delete_term_meta($parentTerm['term_id'],'thumbnail_id');
                $categoryImage = self::uploadMedia(self::$ProductCategoryImageBasePath.$product['product_features']['dynamic_category_image']);
                add_term_meta($parentTerm['term_id'], 'thumbnail_id', $categoryImage );
            }

            $categoriesArray[] = $parentTerm['term_id'];
            $childTerm = wp_insert_term(
                $product['product_features']['dynamic_subcategory'],
                'product_cat',
                array(
                    'description'=> $product['product_features']['dynamic_subcategory'],
                    'slug' => strtolower(str_replace(' ','_',$product['product_features']['dynamic_subcategory'])),
                    'parent'=> $parentTerm['term_id']
                )
            );
            if(!is_array($childTerm)){
                $existingTerm = $childTerm->error_data['term_exists'];
                $childTerm = [];
                $childTerm['term_id'] = $existingTerm;
            }else{
                $existingTermData = get_term_meta($childTerm['term_id']);
                wp_delete_post($existingTermData['thumbnail_id'][0]);
                delete_term_meta($childTerm['term_id'],'thumbnail_id');
                $categoryImage = self::uploadMedia(self::$ProductCategoryImageBasePath.$product['product_features']['dynamic_subcategory_image']);
                add_term_meta($childTerm['term_id'], 'thumbnail_id', $categoryImage );
            }
            $categoriesArray[] = $childTerm['term_id'];

            return $categoriesArray;
        }



        public static function placeOrder($order_id, $isManual = false){
            global $wpdb;
            $tblname = 'retailcore_place_order_logs';
            $wp_track_table = $wpdb->prefix . "$tblname";

            $orderDetails = wc_get_order($order_id);
            $userDetails = $orderDetails->get_customer_id();
            $userDetails = get_userdata($userDetails);
            $orderAmount = $orderDetails->get_total();
            $paymentMethod = $orderDetails->get_payment_method();

            $orderPaymentMethodId = 0;
            if($paymentMethod == 'cod'){
                $orderPaymentMethodId = 10;
            }
            $productItems = [];
            $totalQty = 0;

            foreach($orderDetails->get_items() as $k => $item) {
                $product = $item->get_data();
                $productMeta = get_post_meta($product['product_id'], '_product_barcode');
                $totalQty += $product['quantity'];
                $productItems[] = [
                    'Barcode' => $productMeta[0],
                    'MRP' => 1,
                    'Selling Price' => ($product['total']/$product['quantity']),
                    'Order Qty' => $product['quantity'],
                    'Discount Percent' => '',
                    'Discount Amount' => '',
                    'GST_percent' => 0,
                    'Total Price' => $product['total'],
                ];
            }

            $orderArray = [
                'Order Details' => [
                    'Company ID' => 7,
                    'Order ID/PO NO' => $order_id,
                    'Date' => date('d'),
                    'Month' => date('m'),
                    'Year' => date('Y'),
                    'Customer Name' => $orderDetails->get_billing_first_name(),
                    'CONTACT NO' => $orderDetails->get_billing_phone(),
                    'EMAIL ID' => $orderDetails->get_billing_email(),
                    'City' => $orderDetails->get_billing_city(),
                    'State' => $orderDetails->get_billing_state(),
                    'Discount Percent' => 0,
                    'Discount Amount' => '0.00',
                    'Total Price' => $orderAmount,
                    'Order Payment Details' => [
                        [
                            'Payment_method_id' => $orderPaymentMethodId,
                            'Payment_method_amount' => $orderAmount,
                            'Remarks' => ''
                        ]
                    ],
                    'Total Qty' => $totalQty,
                    'Order Product Details' => $productItems
                ]
            ];
            $response = CurlRequest::placeOrder($orderArray);
            $response = json_decode($response,true);
            if($isManual == false){
                $data = array('order_id' => $order_id, 'response' => json_encode($response),'date'=>date('Y-m-d h:i:s'),'status'=>$response['Success']);
                $format = array('%s','%s','%s');
                $wpdb->insert($wp_track_table,$data,$format);
            }
            return $response;
        }
    }
}