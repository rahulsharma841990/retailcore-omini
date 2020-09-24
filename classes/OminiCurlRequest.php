<?php
if(!class_exists('OminiCurlRequest')) {
    class OminiCurlRequest{

        private static $CURL = '';

        private static $ProductImageBasePath = 'https://aiinnovation.in/retailcore/public/uploads/products_images/';

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

        public static function syncProduct(){
            $products = CurlRequest::getProducts();
            $products = json_decode($products,true);
            $count = 0;
            foreach($products['Products'] as $key => $product){
                $productImages = $product['product_images'];
                $galleryImages = [];
                foreach($productImages as $k => $image){
                    $galleryImages[] = self::uploadMedia(self::$ProductImageBasePath.$image['product_image']);
                }
                $objProduct = new WC_Product();
                $objProduct->set_image_id($galleryImages[0]);
                $objProduct->set_name($product['product_name']);
                $objProduct->set_status("publish");
                $objProduct->set_catalog_visibility('visible');
                $objProduct->set_description($product['product_description']);
                $objProduct->set_sku(rand(111111,999999)."-sku");
                $objProduct->set_price($product['product_price_master'][0]['offer_price']);
//                $objProduct->set_sale_price($product['cost_price']);
                $objProduct->set_regular_price($product['product_price_master'][0]['offer_price']);
                $objProduct->set_manage_stock(true);
                $objProduct->set_stock_quantity($product['product_price_master'][0]['product_qty']);
                $objProduct->set_stock_status('instock');
                $objProduct->set_backorders('no');
                $objProduct->set_reviews_allowed(true);
                $objProduct->set_sold_individually(false);
                $objProduct->set_category_ids(array(1,2,3));
                $objProduct->set_gallery_image_ids($galleryImages);
                $objProduct->save();
                $count++;
            }
            return $count;
        }
    }
}