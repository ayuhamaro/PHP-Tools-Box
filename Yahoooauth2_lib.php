<?php

class Yahoooauth2_lib
{
    const AUTHORIZATION_ENDPOINT  = 'https://api.login.yahoo.com/oauth2/request_auth';
    const TOKEN_ENDPOINT = 'https://api.login.yahoo.com/oauth2/get_token';
    const USER_INFO_ENDPOINT = 'https://api.login.yahoo.com/openid/v1/userinfo';

    public function fetch($url, $postdata = array(), $auth = false, $headers = array()) {
        $curl = curl_init($url);
        if(count($postdata) > 0){
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postdata));
        }else{
            curl_setopt($curl, CURLOPT_POST, false);
        }
        if($auth !== false){
            curl_setopt($curl, CURLOPT_USERPWD, $auth);
        }
        if(count($headers) > 0){
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec( $curl );

        if(empty($response)){
            // some kind of an error happened
            die(curl_error($curl));
            curl_close($curl); // close cURL handler
        }else{
            $info = curl_getinfo($curl);
            curl_close($curl); // close cURL handler

            if($info['http_code'] != 200 && $info['http_code'] != 201 ) {
                echo "Received error: " . $info['http_code']. "\n";
                echo "Raw response:".$response."\n";
                die();
            }
        }
        return $response;
    }

    public function get_authorization_url($client_id, $redirect_uri, $language="en-us") {
        $url = self::AUTHORIZATION_ENDPOINT;
        $authorization_url = $url.'?'.'client_id='.$client_id.'&redirect_uri='.$redirect_uri.
            '&language='.$language.'&response_type=code';
        return $authorization_url;
    }

    public function get_access_token($clientId, $clientSecret,$redirect_uri,$code) {
        $url = self::TOKEN_ENDPOINT;
        $postdata = array("redirect_uri" => $redirect_uri, "code" => $code, "grant_type" => "authorization_code");
        $auth = $clientId.":".$clientSecret;
        $response = self::fetch($url, $postdata, $auth);

        // Convert the result from JSON format to a PHP array
        $jsonResponse = json_decode($response);
        return $jsonResponse;
    }

    public function get_user_info($access_token) {
        $url=self::USER_INFO_ENDPOINT;
        $header = array('Authorization: Bearer '.$access_token);
        $response = self::fetch($url, false, false, $header);

        // Convert the result from JSON format to a PHP array
        $jsonResponse = json_decode( $response );
        return $jsonResponse;
    }
}
