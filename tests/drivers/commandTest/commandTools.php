<?php

/* 
 * @author Pedro Peláez <aaaaa976@gmail.com>
 * @since 2014.11.24
 */
echo "\n".getcwd();
while (!is_file("config/config.php")) {
    chdir("../");
    echo "\n".getcwd();
}
echo "\n=> ".getcwd()."\n";
include_once 'config/config.php';

class commandTools {
    
    public static function getURL($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = curl_exec($ch);
        
        // Then, after your curl_exec call:
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $header_size);
        $body = substr($response, $header_size);
        $lastError = curl_error($ch);
        
        $resp = array (
            "header" => explode("\n", $header),
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