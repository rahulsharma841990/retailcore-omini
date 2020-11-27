<?php
if(!class_exists('OminiCurlRequest')) {

    class CurlRequest{

//        private static $baseUrl = 'https://aiinnovation.in/retailcore/website_software_api/';
        private static $baseUrl = 'https://retailcore.biz/taazasabzi_test/website_software_api/';

        private static $APP_ID = '4495A05377C6A1C6690C46CE4FE721B8';

        private static $APP_SECRET = '2433220B9E11E449145D0311BEADAD6B';

        public function __construct(){

        }

        public static function getProducts(){
            $lastTimestamp = null;
            if(get_option( '_retailcore_omini_timestamp' ) !== false){
                $lastTimestamp = get_option('_retailcore_omini_timestamp');
                update_option('_retailcore_omini_timestamp',get_date_from_gmt('now'));
            }else{
                $lastTimestamp = date('Y-m-d H:i:s');
                add_option('_retailcore_omini_timestamp',get_date_from_gmt('now'));
            }
            $params = [
                'company_id' => 1,
                'timestamp' => $lastTimestamp
//                'timestamp' => '2020-11-24 13:00:00'
            ];
            return self::request($params);
        }

        public static function request($params){
            $curl = curl_init();
            curl_setopt_array($curl, array( CURLOPT_URL => self::$baseUrl."product_listing", CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>json_encode($params), CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "App-Id: ".self::$APP_ID, "App-Secret: ".self::$APP_SECRET
                ), ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }

        public static function placeOrder($params){
            $curl = curl_init();
            curl_setopt_array($curl, array( CURLOPT_URL => self::$baseUrl."billing_requestdata", CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1, CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS =>json_encode($params), CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "App-Id: ".self::$APP_ID, "App-Secret: ".self::$APP_SECRET
                ), ));
            $response = curl_exec($curl);
            curl_close($curl);
            return $response;
        }
    }
}