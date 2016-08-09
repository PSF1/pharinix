<?php

/* 
 * Pharinix Copyright (C) 2015 Pedro Pelaez <aaaaa976@gmail.com>
 * Sources https://github.com/PSF1/pharinix
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */
if (!defined("CMS_VERSION")) { header("HTTP/1.0 404 Not Found"); die(""); }

if (!class_exists("commandRemoteAPICall")) {
    class commandRemoteAPICall extends driverCommand {

        public static function runMe(&$params, $debug = true) {
            $remoteParams = array_merge(array(
                'host' => '',
                'rcmd' => '',
                'iface' => '',
            ), $params);
            
            $host = $remoteParams['host'];
            unset($remoteParams['host']);
            $remoteParams['command'] = $remoteParams['rcmd'];
            unset($remoteParams['rcmd']);
            $remoteParams['interface'] = $remoteParams['iface'];
            unset($remoteParams['iface']);
            
            $resp = self::apiCall($host, $remoteParams);
            if($_POST['interface'] == 'echoHtml') {
                echo $resp['body'];
            } else if($_POST['interface'] == 'echoJson') {
                return json_decode($resp['body'], true);
            } else {
                return $resp['body'];
            }
        }
        
        /**
         *
         * @param string $url URL a la que llamar
         * @param array $params Lista de parametros a enviar por POST, si no presente se realiza una llamada GET.
         * @param boolean $parseParams Si TRUE se trata de convertir el array $params, si FALSE se considera que $params viene preparado para la llamada.
         * @param boolean $binary Si TRUE realiza la llamada con el parametro --data-binary.
         * @param array $headers 
         * @param integer $timeoutsec Seconds before timeout
         * @return array array ( "header" => Cabeceras de la peticion, "body" => Cuerpo de la respuesta, "error" => Mensaje de error );
         * @link http://hayageek.com/php-curl-post-get
         */
        public static function apiCall($url, $params = null, $parseParams = true, $binary = false, $headers = null, $timeoutsec = 30) {
            $postData = '';
            if ($parseParams && $params != null) {
                //create name value pairs seperated by &
                foreach ($params as $k => $v) {
                    $postData .= $k . '=' . $v . '&';
                }
                rtrim($postData, '&');
            } else {
                $postData = $params;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($binary)
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, TRUE); // --data-binary
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_VERBOSE, 0);
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
            if ($postData != "") {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            }
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, $timeoutsec); //timeout in seconds
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //not verify certificate
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow location headers
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
//        curl_setopt($ch, CURLOPT_REFERER, self::API_URL.'dashboard');
            if ($headers != null) {
                $h = array();
                foreach ($headers as $key => $value) {
                    $h[] = $key . ': ' . $value;
                }
                curl_setopt($ch, CURLOPT_HTTPHEADER, $h);
            }
            $response = curl_exec($ch);

            // Then, after your curl_exec call:
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $header_size);
            $body = substr($response, $header_size);
            $lastError = curl_error($ch);

            $aux = explode("\n", $header);
            $rHeaders = array();
            foreach ($aux as $head) {
                $rHeaders[] = trim($head);
            }

            $resp = array(
                "header" => $rHeaders,
                "request" => curl_getinfo($ch),
                "request_body" => $postData,
                "body" => $body,
                "error" => $lastError
            );
            curl_close($ch);
            return $resp;
        }

        public static function getHelp() {
            return array(
                "package" => 'core',
                "description" => __("Call a remote API method"), 
                "parameters" => array(
                    'host' => __('Remote host to call.'),
                    'rcmd' => __('Command to execute.'),
                    'iface' => __('Remote interface type.'),
                    'any' => __('Other parameters.'),
                ), 
                "response" => array(),
                "type" => array(
                    "parameters" => array(
                        'host' => 'string',
                        'rcmd' => 'string',
                        'iface' => 'string',
                        'any' => 'args',
                    ), 
                    "response" => array(),
                ),
                "echo" => true,
                "interface" => false,
//                "hooks" => array(
//                        array(
//                            "name" => "nothingHook",
//                            "description" => "sadasdaAllow rewrite a HTML alert message.",
//                            "parameters" => array(
//                                "alert" => "asdasdResponse to be echoed to the client.",
//                                "msg" => "asdasdThe message to show."
//                            )
//                        )
//                )
            );
        }
        
        public static function getAccess($ignore = "") {
            $me = __FILE__;
            return parent::getAccess($me);
        }
        
//        public static function getAccessFlags() {
//            return driverUser::PERMISSION_FILE_ALL_EXECUTE;
//        }
    }
}
return new commandRemoteAPICall();