<?php

/* 
 * @author Pedro Peláez <aaaaa976@gmail.com>
 * @since 2014.11.24
 */
//while (!is_file("etc/pharinix.config.php")) {
//    chdir("../");
//}
//include_once 'tests/drivers/etc/bootstrap.php';
        
class commandTools {
    
    public static function getSessionObject($auth = "") {
        $resp = self::getURL(CMS_DEFAULT_URL_BASE, array(
            "command" => "getSession",
            "auth_token" => $auth,
            "interface" => "echoJson",
        ));
        return json_decode($resp["body"]);
    }
    
    /**
     * Do a HTTP query
     * @param string $url
     * @param array $params
     * @return array
     * @link http://hayageek.com/php-curl-post-get
     */
    public static function getURL($url, $params = null) {
        $postData = '';
        if ($params != null) {
            //create name value pairs seperated by &
            foreach($params as $k => $v) 
            { 
               $postData .= $k . '='.$v.'&'; 
            }
            rtrim($postData, '&');
        }
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 0);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($postData != "") {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $response = curl_exec($ch);
        
        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $lastError = curl_error($ch);
        
        $aux = explode("\n", $header);
        $rHeaders = array();
        foreach($aux as $head) {
            $rHeaders[] = trim($head);
        }
        
        $resp = array (
            "header" => $rHeaders,
            "body" => $body,
            "error" => $lastError
        );
        curl_close($ch);
        return $resp;
    }
    
    public static function getRequestState($response) {
        $parts = explode(" ", $response["header"][0]);
        return $parts[1];
    }
}

print_r("\$_SERVER:");
print_r($_SERVER);

phpInfo();